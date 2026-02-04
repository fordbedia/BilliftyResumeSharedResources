<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Password Changed</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Inter,Arial,Helvetica,sans-serif;color:#0f172a;">
  <div style="max-width:680px;margin:0 auto;padding:28px;">
    <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:18px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,0.08);">
      <!-- Top accent -->
      <div style="height:8px;background:linear-gradient(90deg,#6366f1,#22c55e,#06b6d4);"></div>

      <div style="padding:26px 26px 10px;">
        <div style="display:flex;align-items:center;gap:12px;">
          <div style="width:44px;height:44px;border-radius:12px;background:#eef2ff;display:flex;align-items:center;justify-content:center;">
            <span style="font-size:20px;">🔐</span>
          </div>
          <div style="line-height:1.1;">
            <div style="font-size:14px;color:#475569;">{{ $appName }}</div>
            <div style="font-size:18px;font-weight:800;">Password Changed</div>
          </div>
        </div>

        <div style="margin-top:18px;font-size:15px;line-height:1.6;color:#0f172a;">
          <p style="margin:0 0 10px;">
            Hi{{ !empty($name) ? ' ' . e($name) : '' }},
          </p>

          <p style="margin:0 0 12px;">
            You have successfully changed your password. Your new password will take effect now.
          </p>

          <div style="margin:16px 0;padding:14px 14px;border:1px solid #e2e8f0;border-radius:14px;background:#f8fafc;">
            <div style="font-weight:700;margin-bottom:6px;">Wasn’t you?</div>
            <div style="color:#334155;">
              If you didn’t make this change, please contact us as soon as possible so we can help secure your account.
            </div>
          </div>

          <p style="margin:0;color:#475569;font-size:13px;">
            For your security, we recommend using a unique password and enabling any available account protections.
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div style="padding:18px 26px;border-top:1px solid #e5e7eb;background:#fbfcfe;">
        <div style="font-size:12px;color:#64748b;line-height:1.6;">
          Need help? Reply to this email or reach out to <span<small style="color:#0ea5e9;">{{ $supportEmail }}</small>.
          <br />
          <span style="color:#94a3b8;">© {{ date('Y') }} {{ $appName }}. All rights reserved.</span>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
