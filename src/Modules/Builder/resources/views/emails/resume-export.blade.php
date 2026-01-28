<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Your Resume Export</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<div style="max-width:640px;margin:0 auto;padding:24px;">
    <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
        <div style="padding:20px 22px;background:linear-gradient(135deg,#111827,#374151);color:#fff;">
            <div style="font-size:14px;opacity:.9;">{{ $appName }}</div>
            <div style="font-size:22px;font-weight:700;margin-top:6px;">Your resume is ready ✅</div>
            <div style="font-size:14px;opacity:.9;margin-top:4px;">Format: <strong>{{ $formatUpper }}</strong></div>
        </div>

        <div style="padding:22px;">
            <p style="margin:0 0 12px 0;font-size:15px;line-height:1.6;">
                Hi <strong>{{ $recipientName }}</strong>,
            </p>

            <p style="margin:0 0 14px 0;font-size:15px;line-height:1.6;">
                Attached is your exported resume in <strong>{{ $formatUpper }}</strong> format.
                You can download it directly from this email and share it anywhere you like.
            </p>

            <div style="padding:14px 14px;border:1px solid #e5e7eb;border-radius:12px;background:#f9fafb;margin:18px 0;">
                <div style="font-size:13px;color:#6b7280;margin-bottom:6px;">Resume details</div>
                <div style="font-size:15px;line-height:1.5;">
                    <div><strong>Name:</strong> {{ data_get($resume, 'basics.name') ?? data_get($resume, 'name', '—') }}</div>
                    <div><strong>Title:</strong> {{ data_get($resume, 'basics.label') ?? '—' }}</div>
                    <div><strong>Email:</strong> {{ data_get($resume, 'basics.email') ?? '—' }}</div>
                </div>
            </div>

            <p style="margin:0 0 10px 0;font-size:14px;line-height:1.6;color:#374151;">
                If you didn’t request this export, you can ignore this email.
            </p>

            <p style="margin:18px 0 0 0;font-size:14px;color:#6b7280;">
                — {{ $appName }} Team
            </p>
        </div>
    </div>

    <div style="text-align:center;color:#9ca3af;font-size:12px;margin-top:14px;">
        © {{ date('Y') }} {{ $appName }}. All rights reserved.
    </div>
</div>
</body>
</html>
