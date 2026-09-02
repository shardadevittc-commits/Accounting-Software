<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice {{ $invoiceNo }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width: 600px; width: 100%; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 24px 30px; text-align: left;">
                            <table width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <h2 style="margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: -0.5px;">Devine Accounting ERP</h2>
                                        <p style="margin: 4px 0 0 0; color: #94a3b8; font-size: 13px;">Official Tax Invoice & Billing Notification</p>
                                    </td>
                                    <td align="right">
                                        <span style="background: rgba(37, 99, 235, 0.2); border: 1px solid #3b82f6; color: #60a5fa; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                            TAX INVOICE
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Main Content Body -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.5; color: #334155;">
                                Dear <strong>{{ $customerName }}</strong>,
                            </p>
                            
                            @if(!empty($customMessage))
                            <div style="background-color: #f8fafc; border-left: 4px solid #2563eb; padding: 14px 18px; border-radius: 6px; margin: 0 0 24px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                                {!! nl2br(e($customMessage)) !!}
                            </div>
                            @endif

                            <!-- Invoice Summary Box -->
                            <table width="100%" cellspacing="0" cellpadding="0" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 16px;">
                                        <table width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 500;">Invoice Number:</td>
                                                <td align="right" style="padding: 6px 0; font-size: 14px; font-weight: 700; color: #1e293b;">{{ $invoiceNo }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 500;">Invoice Date:</td>
                                                <td align="right" style="padding: 6px 0; font-size: 14px; font-weight: 600; color: #1e293b;">{{ date('d-m-Y', strtotime($invoiceDate)) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 500;">Total Amount:</td>
                                                <td align="right" style="padding: 6px 0; font-size: 16px; font-weight: 800; color: #2563eb;">₹ {{ $totalAmount }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Attachment Notice -->
                            <table width="100%" cellspacing="0" cellpadding="0" style="background: #eff6ff; border: 1px dashed #3b82f6; border-radius: 8px; margin-bottom: 10px;">
                                <tr>
                                    <td style="padding: 14px 18px; font-size: 13px; color: #1e40af; font-weight: 600;">
                                        📄 Attached File: &nbsp;<span style="color: #1e293b; font-weight: 700;">{{ $pdfFilename }}</span> (Official Tax Invoice PDF)
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 30px; text-align: center; font-size: 12px; color: #64748b;">
                            This is an automated tax invoice notification from Devine Accounting ERP.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
