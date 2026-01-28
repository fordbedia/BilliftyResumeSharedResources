<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Mail;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResumeExportMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Resume $resume,
        public string $fileFormat,                 // pdf | docx
        public string $absoluteAttachmentPath,     // full path
        public string $attachmentName,             // resume_123.pdf
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Resume Export (' . strtoupper($this->fileFormat) . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'builder::emails.resume-export',
            with: [
                'resume'      => $this->resume,
                'fileFormat'  => $this->fileFormat,
                'formatUpper' => strtoupper($this->fileFormat),
                'recipientName' => data_get($this->resume, 'basic.name')
                    ?? data_get($this->resume, 'name')
                    ?? 'there',
                'appName' => config('app.name', 'Resume Builder'),
            ]
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->absoluteAttachmentPath)
                ->as($this->attachmentName)
                ->withMime(
                    $this->fileFormat === 'pdf'
                        ? 'application/pdf'
                        : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ),
        ];
    }
}
