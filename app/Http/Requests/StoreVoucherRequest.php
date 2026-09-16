<?php

namespace App\Http\Requests;

use App\Models\Voucher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare the data for validation.
     * Ensures party ledger is only resolved/created at submission time, never on page load.
     */
    protected function prepareForValidation(): void
    {
        $partyId = $this->input('party_id');
        $partyName = $this->input('party_name');

        if (!empty($partyName)) {
            $type = $this->input('party_type', ($this->input('transaction_mode') === 'payment' ? 'supplier' : 'customer'));
            $partyLedger = \App\Models\Ledger::firstOrCreatePartyLedger($partyId, $partyName, $type);

            $entries = $this->input('entries', []);
            if (is_array($entries)) {
                foreach ($entries as &$entry) {
                    if (isset($entry['ledger_id'])) {
                        if ($entry['ledger_id'] == $partyId || !\App\Models\Ledger::where('id', $entry['ledger_id'])->exists()) {
                            $entry['ledger_id'] = $partyLedger->id;
                        }
                    }
                }
                unset($entry);
                $this->merge([
                    'entries' => $entries,
                    'party_id' => $partyLedger->id,
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'voucher_type' => ['required', Rule::in(Voucher::TYPES)],
            'voucher_date' => ['required', 'date'],
            'party_id' => ['nullable'],
            'party_name' => ['nullable', 'string', 'max:255'],
            'party_type' => ['nullable', 'string', 'in:customer,supplier'],
            'invoice_id' => ['nullable'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'narration' => ['nullable', 'string'],
            'transaction_mode' => ['nullable', Rule::in(['receipt', 'payment'])],
            'payment_method' => ['nullable', Rule::in(['cash', 'cheque', 'neft', 'rtgs', 'imps', 'upi', 'bank_transfer', 'other', 'general'])],
            'instrument_no' => ['nullable', 'string', 'max:100'],
            'instrument_date' => ['nullable', 'date'],
            'bank_account_id' => ['nullable', 'exists:ledgers,id'],
            'status' => ['nullable', Rule::in(['posted', 'draft'])],

            // Double-entry rows
            'entries' => ['required', 'array', 'min:2'],
            'entries.*.ledger_id' => ['required', 'exists:ledgers,id'],
            'entries.*.debit' => ['required', 'numeric', 'min:0'],
            'entries.*.credit' => ['required', 'numeric', 'min:0'],
            'entries.*.description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'voucher_type.required' => 'Please select a valid voucher type.',
            'voucher_date.required' => 'Voucher date is mandatory.',
            'entries.required' => 'Voucher must have accounting entries.',
            'entries.min' => 'An accounting voucher requires at least two lines (one Debit and one Credit).',
            'entries.*.ledger_id.required' => 'Each entry line must have an account selected.',
            'entries.*.ledger_id.exists' => 'Selected ledger account does not exist in chart of accounts.',
        ];
    }
}
