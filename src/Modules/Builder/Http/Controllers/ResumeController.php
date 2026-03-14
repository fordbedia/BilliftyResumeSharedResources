<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources\ResumeResource;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Jobs\GenerateResumeExportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResumeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ResumeRepository $resume)
    {
		$search = trim((string) $request->query('search', ''));
		$perPage = (int) $request->query('per_page', 10);
		$perPage = $perPage > 0 ? $perPage : 10;

		return ResumeResource::collection(
			$resume->paginated($search ?: null, $perPage)
		);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, ResumeRepository $resume)
    {
        return $resume->destroy($id);
    }

	public function recent(ResumeRepository $resume)
	{
		return ResumeResource::collection($resume->allLatest());
	}

	public function startExport(int $id, Request $request, ResumeRepository $repo)
	{
		$format = $request->input('fileFormat', 'pdf');
		abort_unless(in_array($format, ['pdf','docx']), 422, 'Invalid format');

		$resume = $repo->find($id);
		abort_if(!$resume, 404, 'Resume not found.');
		$requestedAt = now();

		// Reset export fields (so FE knows it's regenerating)
		$updates = [
			'export_status' => 'queued',
			'export_format' => $format,
			'export_disk' => 'public',
			'export_path' => null,
			'export_error' => null,
			'export_requested_at' => $requestedAt,
			'export_ready_at' => null,
		];

		$resume->update($updates);

		// Run from the current web process after response to avoid stale long-lived worker state.
		if (method_exists(GenerateResumeExportJob::class, 'dispatchAfterResponse')) {
			GenerateResumeExportJob::dispatchAfterResponse($resume->id, $requestedAt->toIso8601String());
		} else {
			GenerateResumeExportJob::dispatchSync($resume->id, $requestedAt->toIso8601String());
		}

		return response()->json(['queued' => true], 202);
	}

	public function exportStatus(int $id, ResumeRepository $repo)
	{
		$resume = $repo->find($id);

		return response()->json([
			'status' => $resume->export_status,
			'format' => $resume->export_format,
			'error' => $resume->export_error,
		]);
	}

	public function exportDownload(int $id, ResumeRepository $resume)
	{
		$r = $resume->find($id);

		if (!$r->export_path || $r->export_status !== 'ready') {
			return response()->json(['message' => 'Not ready'], 409);
		}

		return Storage::disk($r->export_disk ?? 'public')
			->download($r->export_path, "resume_{$id}.pdf");
	}

}
