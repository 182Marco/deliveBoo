<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Your Foodsy order is confirmed</title>
</head>
{{-- inline styles only, table layout: what every mail client understands. Colours from resources/css/tokens.css --}}
<body style="margin:0; padding:0; background-color:#faf8f4;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#faf8f4;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px; background-color:#ffffff; border:1px solid #e8e1d6; border-radius:18px;">
                    <tr>
                        <td style="padding:32px 40px 0; font-family:Arial, Helvetica, sans-serif; font-size:26px; font-weight:900; letter-spacing:-0.5px; color:#1c1a17;">
                            <img src="{{ url('/images/foodsy-mark.png') }}" width="36" height="36" alt="" style="vertical-align:middle; border:0;">
                            <span style="vertical-align:middle;">Foodsy</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 40px 8px; font-family:Arial, Helvetica, sans-serif; font-size:30px; font-weight:900; letter-spacing:-0.8px; line-height:1.15; color:#1c1a17;">
                            Thank you, {{ $contact['customer_name'] }}!
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 40px 24px; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:1.55; color:#4a453e;">
                            Your order is confirmed and the kitchen is getting it ready. Your favourite food is on its way.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f1ece3; border-radius:12px;">
                                <tr>
                                    <td style="padding:18px 20px 6px; font-family:Arial, Helvetica, sans-serif; font-size:12px; font-weight:bold; letter-spacing:1px; text-transform:uppercase; color:#6e675e;">Delivering to</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 20px 16px; font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#1c1a17;">{{ $contact['customer_address'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 20px 18px; border-top:1px dashed #d5cbbb; font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#1c1a17;">
                                        <strong>Total paid</strong>
                                        <span style="float:right; font-family:Arial, Helvetica, sans-serif; font-size:22px; font-weight:900; color:#a8501c;">€{{ number_format((float) $contact['price'], 2) }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" style="padding:28px 40px 36px;">
                            <a href="{{ url('/') }}" style="display:inline-block; padding:14px 26px; border-radius:999px; background-color:#a8501c; color:#ffffff; font-family:Arial, Helvetica, sans-serif; font-size:16px; font-weight:bold; text-decoration:none;">Order again on Foodsy</a>
                        </td>
                    </tr>
                </table>
                <p style="margin:20px 0 0; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#6e675e;">© {{ date('Y') }} Foodsy · Good food, beautifully delivered.</p>
            </td>
        </tr>
    </table>
</body>
</html>
