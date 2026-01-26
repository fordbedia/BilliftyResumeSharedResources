<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Action;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class GenerateResumeDocxAction
{
    /**
     * @return array{path:string}
     */
    public function handle(
        array $resume,
        string $disk = 'public',
        ?string $forcedPath = null
    ): array {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $basics = $resume['basics'] ?? [];

        // Header
        $section->addText($basics['name'] ?? 'Your Name', ['bold' => true, 'size' => 18]);

        if (!empty($basics['label'])) {
            $section->addText($basics['label'], ['italic' => true, 'size' => 11]);
        }

        $section->addTextBreak(1);

        // Contact row
        $contact = [];
        if (!empty($basics['email'])) $contact[] = $basics['email'];
        if (!empty($basics['url'])) $contact[] = $basics['url'];
        if (!empty($contact)) {
            $section->addText(implode(' • ', $contact), ['size' => 10]);
            $section->addTextBreak(1);
        }

        // Summary
        if (!empty($basics['summary'])) {
            $section->addText($basics['summary'], ['size' => 11]);
            $section->addTextBreak(1);
        }

        // Skills
        if (!empty($resume['skills'])) {
            $section->addText('Skills', ['bold' => true, 'size' => 14]);

            foreach ($resume['skills'] as $skill) {
                $name = is_array($skill) ? ($skill['name'] ?? null) : $skill;
                if ($name) $section->addText("• {$name}", ['size' => 11]);
            }

            $section->addTextBreak(1);
        }

        // Education
        if (!empty($resume['education'])) {
            $section->addText('Education', ['bold' => true, 'size' => 14]);

            foreach ($resume['education'] as $edu) {
                $institution = $edu['institution'] ?? '';
                $studyType = $edu['studyType'] ?? '';
                $area = $edu['area'] ?? '';

                $line = trim($institution . ' — ' . trim($studyType . ' ' . $area), " —");
                if ($line !== '') $section->addText($line, ['bold' => true, 'size' => 11]);

                $start = $edu['startDate'] ?? '';
                $end = $edu['endDate'] ?? 'Present';
                $dates = trim($start . ' - ' . ($end ?: 'Present'), ' -');
                if ($dates !== '') $section->addText($dates, ['italic' => true, 'size' => 10]);

                $section->addTextBreak(1);
            }
        }

        // Work
        if (!empty($resume['work'])) {
            $section->addText('Experience', ['bold' => true, 'size' => 14]);

            foreach ($resume['work'] as $work) {
                $position = $work['position'] ?? '';
                $company = $work['name'] ?? '';
                $header = trim($position . ($company ? " — {$company}" : ''));

                if ($header !== '') $section->addText($header, ['bold' => true, 'size' => 11]);

                $start = $work['startDate'] ?? '';
                $end = $work['endDate'] ?? 'Present';
                $dates = trim($start . ' - ' . ($end ?: 'Present'), ' -');
                if ($dates !== '') $section->addText($dates, ['italic' => true, 'size' => 10]);

                if (!empty($work['summary'])) {
                    $section->addText($work['summary'], ['size' => 11]);
                }

                $section->addTextBreak(1);
            }
        }

        // References
        if (!empty($resume['references'])) {
            $section->addText('References', ['bold' => true, 'size' => 14]);

            foreach ($resume['references'] as $r) {
                $name = $r['name'] ?? '';
                $ref = $r['reference'] ?? '';
                $section->addText(trim($name), ['bold' => true, 'size' => 11]);
                if ($ref !== '') $section->addText($ref, ['size' => 11]);
                $section->addTextBreak(1);
            }
        }

        // Save to storage disk
        $path = $forcedPath ?: ('resume_docs/' . now()->format('Y/n') . '/resume_' . Str::random(20) . '.docx');

        $tmp = tempnam(sys_get_temp_dir(), 'resume_');
        $tmpDocx = $tmp . '.docx';

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tmpDocx);

        Storage::disk($disk)->put($path, file_get_contents($tmpDocx), [
            'visibility' => 'public',
            'ContentType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        @unlink($tmp);
        @unlink($tmpDocx);

        return ['path' => $path];
    }
}
