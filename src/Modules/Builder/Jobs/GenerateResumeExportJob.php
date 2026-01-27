<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Jobs;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Action\GenerateResumeDocxAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Action\GenerateResumePdfAction;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources\ResumeJsonResource;

class GenerateResumeExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $resumeId) {}

    public function handle(
        ResumeRepository $repo,
        GenerateResumePdfAction $pdfAction,
		GenerateResumeDocxAction $docxAction
    ): void {
        $resumeModel = $repo->find($this->resumeId);

        $resumeModel->update(['export_status' => 'processing']);

        try {
            $format = $resumeModel->export_format ?? 'pdf';
            $disk   = $resumeModel->export_disk ?? 'public';

            // Always produce JSON-Resume-shaped array from relations
           	$resumeArray = (new ResumeJsonResource($resumeModel))->resolve();

            if ($format === 'pdf') {

                // Render a SIMPLE, standalone HTML template first
                $result = $pdfAction->handle(
					resume: $resumeArray,
					templateView: 'builder::resume',
					disk: $disk
				);

				$resumeModel->update([
					'export_status' => 'ready',
					'export_path' => $result['path'],
					'export_error' => null,
					'export_ready_at' => now(),
				]);

                return;
            }

			if ($format === 'docx') {
				$forcedPath = "resume_docs/{$this->resumeId}.docx";

				$result = $docxAction->handle(
					resume: $resumeArray,
					disk: $disk,
					forcedPath: $forcedPath,
				);

				$resumeModel->update([
					'export_status'   => 'ready',
					'export_path'     => $result['path'],
					'export_error'    => null,
					'export_ready_at' => now(),
				]);

				return;
			}

            throw new \RuntimeException("DOCX not implemented yet.");
        } catch (Throwable $e) {
            Log::error('Resume export failed', [
                'resume_id' => $this->resumeId,
                'error' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 2000),
            ]);

            $resumeModel->update([
                'export_status' => 'failed',
                'export_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
