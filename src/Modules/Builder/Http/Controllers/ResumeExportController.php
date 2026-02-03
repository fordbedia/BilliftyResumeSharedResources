<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Jobs\SendResumeExportEmailJob;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume;

class ResumeExportController extends Controller
{
	public function sendEmail(Request $request, Resume $resume)
    {
        $validator = Validator::make($request->all(), [
            'fileFormat' => ['required', 'in:pdf,docx'],
            'email'      => ['nullable', 'email'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid request',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $fileFormat = $request->string('fileFormat')->toString();
        $toEmail = $request->string('email')->toString();

        // Default to the resume owner's email if not provided
        // Adjust this depending on your relationships (e.g. $resume->user->email)
        $defaultEmail = data_get($resume, 'user.email') ?? data_get($resume, 'email');
        $toEmail = $toEmail ?: $defaultEmail;

        if (!$toEmail) {
            return response()->json([
                'message' => 'No email available. Provide an email or ensure the resume has an owner email.',
            ], 422);
        }

        // (Optional) store status on the resume (recommended)
        $resume->forceFill([
            'email_export_status' => 'queued',
            'email_export_error'  => null,
        ])->save();

        SendResumeExportEmailJob::dispatch(
            resumeId: (int) $resume->getKey(),
            toEmail: $toEmail,
            fileFormat: $fileFormat
        );

        return response()->json([
            'status' => 'queued',
            'message' => 'Resume export has been queued for email delivery.',
        ]);
    }

    public function emailStatus(Resume $resume)
    {
        return response()->json([
            'status' => $resume->email_export_status ?? null,
            'error'  => $resume->email_export_error ?? null,
        ]);
    }

	public function cleanUpDrive(int $resumeId, ResumeRepository $resume)
	{
		$model = $resume->find($resumeId);
		$disk = $model->export_disk ?? 'public';

		Storage::disk($disk)->delete($model->export_path);

		return response()->json(['message' => 'Deleted'], 200);
	}
}
