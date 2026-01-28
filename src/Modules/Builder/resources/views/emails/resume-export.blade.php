<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Resume Export</title>
</head>
<body style="margin:0;padding:0;background:#f4f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
@php
    $brandName = $appName ?? 'Billifty';
    $format = $formatUpper ?? strtoupper($fileFormat ?? 'PDF');

    $resumeName  = data_get($resume, 'basics.name') ?? data_get($resume, 'name', '—');
    $resumeEmail = data_get($resume, 'basics.email') ?? '—';

    $rawTitle = data_get($resume, 'basics.label') ?? '—';
    $safeTitle = trim(preg_replace('/\blaravel\b/i', '', (string) $rawTitle) ?? (string) $rawTitle);
    $safeTitle = $safeTitle !== '' ? $safeTitle : '—';

    $recipient = $recipientName ?? 'there';

    // Small helper for a friendly filename label (optional)
    $fileLabel = 'resume.' . strtolower($format);
@endphp

<div style="max-width:680px;margin:0 auto;padding:28px 18px;">
    {{-- Outer frame (unique: left accent rail) --}}
    <div style="border-radius:18px;overflow:hidden;box-shadow:0 14px 40px rgba(17,24,39,.10);">
        <div style="display:flex;align-items:stretch;background:#ffffff;">
            {{-- Accent rail --}}
            <div style="width:10px;background:linear-gradient(180deg,#2563eb,#0ea5e9,#22c55e);"></div>

            {{-- Content --}}
            <div style="flex:1;padding:22px 22px 18px 22px;">
                {{-- Tiny brand row (no header) --}}
                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                    <div style="font-weight:800;letter-spacing:.25px;font-size:14px;color:#0f172a;">
                        {{ $brandName }}
                    </div>

                    <div style="font-size:12px;color:#6b7280;">
                        {{ date('M d, Y') }}
                    </div>
                </div>

                {{-- Title --}}
                <div style="margin-top:14px;">
                    <div style="font-size:22px;line-height:1.25;font-weight:900;color:#0f172a;">
                        Resume export ready
                    </div>
                    <div style="margin-top:6px;font-size:14px;line-height:1.6;color:#334155;">
                        Hi <strong style="color:#0f172a;">{{ $recipient }}</strong> — your <strong>{{ $format }}</strong> file is attached to this email.
                        Download it and share it anytime.
                    </div>
                </div>

                {{-- “File chip” row --}}
                <div style="margin-top:16px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
                    <div style="display:inline-flex;align-items:center;gap:8px;padding:9px 12px;border:1px solid #e5e7eb;border-radius:999px;background:#f8fafc;">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:999px;background:#22c55e;"></span>
                        <span style="font-size:12px;color:#0f172a;font-weight:700;">Attached</span>
                        <span style="font-size:12px;color:#64748b;">({{ $fileLabel }})</span>
                    </div>

                    <div style="display:inline-flex;align-items:center;gap:8px;padding:9px 12px;border:1px dashed #cbd5e1;border-radius:999px;background:#ffffff;">
                        <span style="font-size:12px;color:#0f172a;font-weight:700;">Format</span>
                        <span style="font-size:12px;color:#64748b;">{{ $format }}</span>
                    </div>
                </div>

                {{-- Details panel (grid look) --}}
                <div style="margin-top:18px;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
                    <div style="padding:12px 14px;background:#fbfdff;border-bottom:1px solid #e5e7eb;">
                        <div style="font-size:12px;letter-spacing:.18em;text-transform:uppercase;color:#64748b;">
                            Export details
                        </div>
                    </div>

                    <div style="padding:14px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;">
                            <tr>
                                <td style="width:120px;padding:8px 0;color:#64748b;font-size:12px;">Name</td>
                                <td style="padding:8px 0;color:#0f172a;font-size:13px;font-weight:700;">{{ $resumeName }}</td>
                            </tr>
                            <tr>
                                <td style="width:120px;padding:8px 0;color:#64748b;font-size:12px;">Title</td>
                                <td style="padding:8px 0;color:#0f172a;font-size:13px;font-weight:700;">{{ $safeTitle }}</td>
                            </tr>
                            <tr>
                                <td style="width:120px;padding:8px 0;color:#64748b;font-size:12px;">Email</td>
                                <td style="padding:8px 0;color:#0f172a;font-size:13px;font-weight:700;">{{ $resumeEmail }}</td>
                            </tr>
                        </table>

                        <div style="margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;">
                            <div style="font-size:12px;line-height:1.6;color:#475569;">
                                If you didn’t request this export, you can ignore this email.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Signature --}}
                <div style="margin-top:16px;font-size:13px;color:#64748b;">
                    — The {{ $brandName }} Team
                </div>
            </div>
        </div>
    </div>

    {{-- Footer (kept) --}}
    <div style="text-align:center;color:#9ca3af;font-size:12px;margin-top:14px;line-height:1.6;">
        © {{ date('Y') }} {{ $brandName }}. All rights reserved.
        <div style="margin-top:6px;color:#c0c6d4;">This is an automated message.</div>
    </div>
</div>
</body>
</html>
