<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWhatsAppService
{
    protected string $token;
    protected string $phoneNumberId;
    protected string $apiVersion;
    protected string $baseUrl;

    public function __construct()
    {
        $this->token = (string) config('services.meta_whatsapp.token');
        $this->phoneNumberId = (string) config('services.meta_whatsapp.phone_number_id');
        $this->apiVersion = (string) config('services.meta_whatsapp.version', 'v22.0');
        $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}";
    }

    /**
     * Format phone number to international format (E.164 without plus, e.g. 919876543210).
     */
    public function formatPhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        // If 10 digits Indian mobile, prepend 91
        if (strlen($clean) === 10) {
            $clean = '91' . $clean;
        }
        return $clean;
    }

    /**
     * Upload media (e.g. PDF) to WhatsApp Cloud API Media endpoint.
     * Returns media_id string or null on failure.
     */
    public function uploadMedia(string $fileContents, string $filename, string $mimeType = 'application/pdf'): ?string
    {
        try {
            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->attach('file', $fileContents, $filename, ['Content-Type' => $mimeType])
                ->post("{$this->baseUrl}/media", [
                    'messaging_product' => 'whatsapp',
                    'type' => $mimeType,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("Meta WhatsApp Media Uploaded successfully. Media ID: " . ($data['id'] ?? 'unknown'));
                return $data['id'] ?? null;
            }

            Log::error("Meta WhatsApp Media Upload Error: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("Meta WhatsApp Media Upload Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send Document (PDF) message via WhatsApp Cloud API.
     */
    public function sendDocument(string $to, string $mediaId, string $filename, string $caption = ''): array
    {
        $formattedTo = $this->formatPhoneNumber($to);

        Log::info("Meta WhatsApp Dispatching Document to: {$formattedTo} with Media ID: {$mediaId}");

        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $formattedTo,
                'type' => 'document',
                'document' => [
                    'id' => $mediaId,
                    'filename' => $filename,
                    'caption' => $caption,
                ],
            ];

            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->asJson()
                ->post("{$this->baseUrl}/messages", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'recipient' => $formattedTo,
                ];
            }

            Log::error("Meta WhatsApp Send Document Error: " . $response->body());
            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? $response->body(),
                'details' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error("Meta WhatsApp Send Document Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send standard Text message via WhatsApp Cloud API.
     */
    public function sendTextMessage(string $to, string $message): array
    {
        $formattedTo = $this->formatPhoneNumber($to);

        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $formattedTo,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $message,
                ],
            ];

            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->asJson()
                ->post("{$this->baseUrl}/messages", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'recipient' => $formattedTo,
                ];
            }

            Log::error("Meta WhatsApp Send Text Error: " . $response->body());
            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? $response->body(),
                'details' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error("Meta WhatsApp Send Text Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send Template Message (e.g. Jaspers Market order confirmation or hello_world).
     */
    public function sendTemplateMessage(string $to, string $templateName, string $languageCode = 'en_US', array $components = []): array
    {
        $formattedTo = $this->formatPhoneNumber($to);

        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $formattedTo,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => $languageCode,
                    ],
                ],
            ];

            if (!empty($components)) {
                $payload['template']['components'] = $components;
            }

            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->asJson()
                ->post("{$this->baseUrl}/messages", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'recipient' => $formattedTo,
                ];
            }

            Log::error("Meta WhatsApp Template Error: " . $response->body());
            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? $response->body(),
                'details' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error("Meta WhatsApp Template Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send Invoice Document using a Meta Approved Template with Header Document and Body Parameters.
     * This bypasses the 24-hour window requirement completely and delivers directly to customers!
     */
    public function sendInvoiceDocumentTemplate(
        string $to,
        ?string $mediaId,
        string $pdfFilename,
        string $customerName,
        string $invoiceNo,
        string $totalAmount,
        string $invoiceDate,
        ?string $templateName = null,
        string $languageCode = 'en_US'
    ): array {
        $formattedTo = $this->formatPhoneNumber($to);
        $tplName = $templateName ?: config('services.meta_whatsapp.template_name');

        if (!$tplName) {
            // Direct document delivery
            if ($mediaId) {
                return $this->sendDocument($to, $mediaId, $pdfFilename, "📄 Tax Invoice #{$invoiceNo} for {$customerName} (₹ {$totalAmount})");
            }
            return $this->sendTextMessage($to, "📄 Tax Invoice #{$invoiceNo} for {$customerName} (₹ {$totalAmount})");
        }

        // Built-in Meta testing template
        if ($tplName === 'hello_world') {
            return $this->sendTemplateMessage($formattedTo, 'hello_world', 'en_US');
        }

        try {
            $components = [];

            if ($mediaId) {
                $components[] = [
                    'type' => 'header',
                    'parameters' => [
                        [
                            'type' => 'document',
                            'document' => [
                                'id' => $mediaId,
                                'filename' => $pdfFilename,
                            ],
                        ],
                    ],
                ];
            }

            $components[] = [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $customerName],
                    ['type' => 'text', 'text' => $invoiceNo],
                    ['type' => 'text', 'text' => $totalAmount],
                    ['type' => 'text', 'text' => $invoiceDate],
                ],
            ];

            return $this->sendTemplateMessage($formattedTo, $tplName, $languageCode, $components);
        } catch (\Exception $e) {
            Log::error("Meta WhatsApp Document Template Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

