<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Action\Word;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\ListItem;

class ExecutiveToWordDocX
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
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);
        $this->registerStyles($phpWord);

        $section = $phpWord->addSection([
            'pageSizeW' => 11906,
            'pageSizeH' => 16838,
            'marginTop' => 850,
            'marginBottom' => 794,
            'marginLeft' => 794,
            'marginRight' => 794,
        ]);

        $basics = (array) ($resume['basics'] ?? []);

        $name = $this->safeText(data_get($basics, 'name')) ?: 'Your Name';
        $label = $this->safeText(data_get($basics, 'label'));
        $email = $this->safeText(data_get($basics, 'email'));
        $phone = $this->safeText(data_get($basics, 'phone'));
        $url = $this->safeText(data_get($basics, 'url'));
        $city = $this->safeText(data_get($basics, 'location.city'));
        $region = $this->safeText(data_get($basics, 'location.region'));
        $displayLocation = $this->compactLocation($city, $region);

        $profiles = (array) data_get($basics, 'profiles', []);
        $profileUrl = '';
        foreach ($profiles as $profile) {
            $candidate = $this->safeText(data_get($profile, 'url')) ?: $this->safeText(data_get($profile, 'username'));
            if ($candidate !== '') {
                $profileUrl = $candidate;
                break;
            }
        }

        $summaryHtml = (string) data_get($basics, 'summary', '');
        $skillsHtml = (string) data_get($resume, 'skills.body', '');

        $workItems = (array) data_get($resume, 'work', []);
        $eduItems = (array) data_get($resume, 'education', []);
        $jsonProjects = (array) data_get($resume, 'projects', []);
        $refItems = (array) data_get($resume, 'references', []);
        if (empty($refItems)) {
            $refItems = (array) data_get($resume, 'reference', []);
        }

        $certificateActive = (bool) data_get($resume, 'certificate.is_active');
        $accomplishmentActive = (bool) data_get($resume, 'accomplishment.is_active');
        $languagesActive = (bool) data_get($resume, 'languages.is_active');

        $certificateBody = (string) data_get($resume, 'certificate.body', '');
        $accomplishmentBody = (string) data_get($resume, 'accomplishment.body', '');
        $sidebarLanguages = (array) data_get($resume, 'languages.languages', []);

        $hasCertificate = $certificateActive && $this->textHasVisibleContent($certificateBody);
        $hasAccomplishment = $accomplishmentActive && $this->textHasVisibleContent($accomplishmentBody);
        $hasLanguages = $languagesActive && !empty($sidebarLanguages);
        $hasAdditionalInformationSection = $hasCertificate || $hasAccomplishment || $hasLanguages;

        $projectActive = (bool) data_get($resume, 'project.is_active');
        $projectBody = (string) data_get($resume, 'project.body', '');
        $hasProjectBody = $projectActive && $this->textHasVisibleContent($projectBody);

        $competencies = $this->buildCompetencies($resume);

        $section->addText($name, [
            'name' => 'Georgia',
            'size' => 24,
            'bold' => true,
            'color' => '111111',
        ], 'exec.name.par');

        if ($label !== '') {
            $section->addText($label, [
                'size' => 15,
                'color' => '666666',
            ], 'exec.title.par');
        }

        $primaryContactItems = [];
        if ($displayLocation !== '') {
            $primaryContactItems[] = ['text' => $displayLocation];
        }
        if ($email !== '') {
            $primaryContactItems[] = ['text' => $email, 'url' => 'mailto:' . $email];
        }
        if ($phone !== '') {
            $primaryContactItems[] = ['text' => $phone];
        }

        if (!empty($primaryContactItems)) {
            $this->addCenteredContactLine($section, $primaryContactItems);
        }

        if ($profileUrl !== '' || ($profileUrl === '' && $url !== '')) {
            $linkText = $profileUrl !== '' ? $profileUrl : $url;
            $this->addCenteredContactLine($section, [
                ['text' => $linkText, 'url' => $linkText],
            ]);
        }

        if (!empty($primaryContactItems) || $profileUrl !== '' || $url !== '') {
            $section->addText(' ', ['size' => 1, 'color' => 'FFFFFF'], 'exec.after.contact');
        }

        if ($this->textHasVisibleContent($summaryHtml)) {
            $this->addSectionDivider($section);
            $this->addSectionTitle($section, 'Profile');
            $this->addHtmlBlock($section, $summaryHtml, ['size' => 11, 'color' => '363636']);
        }

        if (!empty($workItems)) {
            $this->addSectionDivider($section);
            $this->addSectionTitle($section, 'Experience');

            foreach ($workItems as $work) {
                if (!is_array($work)) {
                    continue;
                }

                $position = $this->safeText(data_get($work, 'position'));
                $company = $this->safeText(data_get($work, 'name'));

                $wCity = $this->safeText(data_get($work, 'location.city'));
                $wRegion = $this->safeText(data_get($work, 'location.region'));
                $wLocation = $this->compactLocation($wCity, $wRegion);
                if ($wLocation === '') {
                    $wLocation = $this->safeText(data_get($work, 'location')) ?: $this->safeText(data_get($work, 'locationName'));
                }

                $range = $this->formatDateRange(data_get($work, 'startDate'), data_get($work, 'endDate'));
                $range = str_replace(' - ', ' — ', $range);
                $metaText = implode(' | ', array_values(array_filter([$company, $wLocation], static fn ($v) => $v !== '')));

                if ($position === '' && $metaText === '' && $range === '') {
                    continue;
                }

                $this->addEntryHeader(
                    $section,
                    $position !== '' ? $position : 'Role Title',
                    $range,
                    true
                );

                if ($metaText !== '') {
                    $section->addText($metaText, [
                        'size' => 11,
                        'bold' => true,
                        'color' => '3F3F3F',
                    ], 'exec.entry.meta');
                }

                $workSummary = (string) data_get($work, 'summary', '');
                if ($this->textHasVisibleContent($workSummary)) {
                    $this->addHtmlBlock($section, $workSummary, ['size' => 11, 'color' => '363636']);
                }

                $highlights = (array) data_get($work, 'highlights', []);
                foreach ($highlights as $highlight) {
                    if (!is_string($highlight) || trim($highlight) === '') {
                        continue;
                    }

                    $section->addListItem(
                        $this->plain($highlight),
                        0,
                        ['size' => 11, 'color' => '363636'],
                        ListItem::TYPE_BULLET_FILLED,
                        'exec.list.item'
                    );
                }

                $section->addText(' ', ['size' => 1, 'color' => 'FFFFFF'], 'exec.entry.after');
            }
        }

        if (!empty($eduItems)) {
            $this->addSectionDivider($section);
            $this->addSectionTitle($section, 'Education');

            foreach ($eduItems as $edu) {
                if (!is_array($edu)) {
                    continue;
                }

                $institution = $this->safeText(data_get($edu, 'institution'));
                $studyType = $this->safeText(data_get($edu, 'studyType'));
                $area = $this->safeText(data_get($edu, 'area'));
                $score = $this->safeText(data_get($edu, 'score'));

                $eCity = $this->safeText(data_get($edu, 'location.city'));
                $eRegion = $this->safeText(data_get($edu, 'location.region'));
                $eLocation = $this->compactLocation($eCity, $eRegion);
                if ($eLocation === '') {
                    $eLocation = $this->safeText(data_get($edu, 'location')) ?: $this->safeText(data_get($edu, 'locationName'));
                }

                $range = $this->formatDateRange(data_get($edu, 'startDate'), data_get($edu, 'endDate'));
                $range = str_replace(' - ', ' — ', $range);

                $metaText = implode(' | ', array_values(array_filter([$institution, $eLocation], static fn ($v) => $v !== '')));
                $degree = trim($studyType . ($area !== '' ? ' in ' . $area : ''));

                if ($degree === '' && $metaText === '' && $range === '' && $score === '') {
                    continue;
                }

                $this->addEntryHeader(
                    $section,
                    $degree !== '' ? $degree : ($institution !== '' ? $institution : 'Education'),
                    $range,
                    true
                );

                if ($metaText !== '') {
                    $section->addText($metaText, [
                        'size' => 11,
                        'color' => '3F3F3F',
                    ], 'exec.entry.meta');
                }

                if ($score !== '') {
                    $section->addText($score, [
                        'size' => 10,
                        'color' => '6F6F6F',
                    ], 'exec.entry.score');
                }

                $section->addText(' ', ['size' => 1, 'color' => 'FFFFFF'], 'exec.entry.after');
            }
        }

        if (!empty($competencies) || $this->textHasVisibleContent($skillsHtml)) {
            $this->addSectionDivider($section);
            $this->addSectionTitle($section, 'Competencies');

            if (!empty($competencies)) {
                $this->renderCompetencyColumns($section, $competencies);
            } else {
                $this->addHtmlBlock($section, $skillsHtml, ['size' => 11, 'color' => '363636']);
            }
        }

        if (!empty($jsonProjects) || $hasProjectBody) {
            $this->addSectionDivider($section);
            $this->addSectionTitle($section, 'Projects');

            if (!empty($jsonProjects)) {
                foreach ($jsonProjects as $project) {
                    if (!is_array($project)) {
                        continue;
                    }

                    $projectName = $this->safeText(data_get($project, 'name'));
                    $projectUrl = $this->safeText(data_get($project, 'url'));
                    $projectDescription = (string) data_get($project, 'description', '');
                    $projectHighlights = (array) data_get($project, 'highlights', []);

                    if (
                        $projectName === ''
                        && !$this->textHasVisibleContent($projectDescription)
                        && empty($projectHighlights)
                    ) {
                        continue;
                    }

                    $this->addEntryHeader(
                        $section,
                        $projectName !== '' ? $projectName : 'Project',
                        $projectUrl,
                        false
                    );

                    if ($this->textHasVisibleContent($projectDescription)) {
                        $this->addHtmlBlock($section, $projectDescription, ['size' => 11, 'color' => '363636']);
                    }

                    foreach ($projectHighlights as $highlight) {
                        if (!is_string($highlight) || trim($highlight) === '') {
                            continue;
                        }

                        $section->addListItem(
                            $this->plain($highlight),
                            0,
                            ['size' => 11, 'color' => '363636'],
                            ListItem::TYPE_BULLET_FILLED,
                            'exec.list.item'
                        );
                    }

                    $section->addText(' ', ['size' => 1, 'color' => 'FFFFFF'], 'exec.entry.after');
                }
            } elseif ($hasProjectBody) {
                $this->addHtmlBlock($section, $projectBody, ['size' => 11, 'color' => '363636']);
            }
        }

        if ($hasAdditionalInformationSection) {
            $this->addSectionDivider($section);
            $this->addSectionTitle($section, 'Additional Information');

            if ($hasCertificate) {
                $section->addText('Certificates', [
                    'size' => 11,
                    'bold' => true,
                    'color' => '1F1F1F',
                ], 'exec.subheading');
                $this->addHtmlBlock($section, $certificateBody, ['size' => 11, 'color' => '363636']);
                $section->addText(' ', ['size' => 1, 'color' => 'FFFFFF'], 'exec.entry.after');
            }

            if ($hasAccomplishment) {
                $section->addText('Accomplishments', [
                    'size' => 11,
                    'bold' => true,
                    'color' => '1F1F1F',
                ], 'exec.subheading');
                $this->addHtmlBlock($section, $accomplishmentBody, ['size' => 11, 'color' => '363636']);
                $section->addText(' ', ['size' => 1, 'color' => 'FFFFFF'], 'exec.entry.after');
            }

            if ($hasLanguages) {
                $section->addText('Languages', [
                    'size' => 11,
                    'bold' => true,
                    'color' => '1F1F1F',
                ], 'exec.subheading');

                foreach ($sidebarLanguages as $language) {
                    $languageName = $this->safeText(data_get($language, 'language')) ?: $this->safeText(data_get($language, 'name'));
                    $languageLevel = $this->safeText(data_get($language, 'fluency')) ?: $this->safeText(data_get($language, 'level'));

                    if ($languageName === '') {
                        continue;
                    }

                    $line = $languageName . ($languageLevel !== '' ? ' (' . $languageLevel . ')' : '');
                    $section->addListItem(
                        $line,
                        0,
                        ['size' => 11, 'color' => '363636'],
                        ListItem::TYPE_BULLET_FILLED,
                        'exec.list.item'
                    );
                }
            }
        }

        if (!empty($refItems)) {
            $this->addSectionDivider($section);
            $this->addSectionTitle($section, 'References');

            foreach ($refItems as $reference) {
                if (!is_array($reference)) {
                    continue;
                }

                $refName = $this->safeText(data_get($reference, 'name'));
                $refBody = (string) data_get($reference, 'reference', '');
                $refTitle = $this->safeText(data_get($reference, 'title')) ?: $this->safeText(data_get($reference, 'position'));
                $refCompany = $this->safeText(data_get($reference, 'company')) ?: $this->safeText(data_get($reference, 'organization'));
                $refEmail = $this->safeText(data_get($reference, 'email'));
                $refPhone = $this->safeText(data_get($reference, 'phone'));

                if (
                    $refName === ''
                    && !$this->textHasVisibleContent($refBody)
                    && $refTitle === ''
                    && $refCompany === ''
                    && $refEmail === ''
                    && $refPhone === ''
                ) {
                    continue;
                }

                $section->addText($refName !== '' ? $refName : 'Reference', [
                    'size' => 12,
                    'bold' => true,
                    'color' => '1A1A1A',
                ], 'exec.reference.name');

                if ($refTitle !== '' || $refCompany !== '') {
                    $section->addText(
                        trim($refTitle . ($refTitle !== '' && $refCompany !== '' ? ', ' : '') . $refCompany),
                        ['size' => 11, 'color' => '3F3F3F'],
                        'exec.entry.meta'
                    );
                }

                if ($refEmail !== '') {
                    $section->addText($refEmail, ['size' => 10, 'color' => '6F6F6F'], 'exec.entry.score');
                }

                if ($refPhone !== '') {
                    $section->addText($refPhone, ['size' => 10, 'color' => '6F6F6F'], 'exec.entry.score');
                }

                if ($this->textHasVisibleContent($refBody)) {
                    $this->addHtmlBlock($section, $refBody, ['size' => 11, 'color' => '363636']);
                }

                $section->addText(' ', ['size' => 1, 'color' => 'FFFFFF'], 'exec.entry.after');
            }
        }

        $path = $forcedPath ?: ('resume_docs/' . now()->format('Y/n') . '/executive_' . Str::random(20) . '.docx');

        $tmp = tempnam(sys_get_temp_dir(), 'executive_resume_');
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

    private function registerStyles(PhpWord $phpWord): void
    {
        $phpWord->addParagraphStyle('exec.name.par', [
            'alignment' => Jc::CENTER,
            'spaceBefore' => 0,
            'spaceAfter' => 40,
            'spacing' => 240,
        ]);

        $phpWord->addParagraphStyle('exec.title.par', [
            'alignment' => Jc::CENTER,
            'spaceBefore' => 0,
            'spaceAfter' => 100,
            'spacing' => 240,
        ]);

        $phpWord->addParagraphStyle('exec.contact.par', [
            'alignment' => Jc::CENTER,
            'spaceBefore' => 0,
            'spaceAfter' => 40,
            'spacing' => 220,
        ]);

        $phpWord->addParagraphStyle('exec.after.contact', [
            'spaceBefore' => 0,
            'spaceAfter' => 120,
        ]);

        $phpWord->addParagraphStyle('exec.section.divider', [
            'spaceBefore' => 180,
            'spaceAfter' => 180,
            'borderTopSize' => 6,
            'borderTopColor' => 'D9D9D9',
        ]);

        $phpWord->addParagraphStyle('exec.section.title', [
            'alignment' => Jc::CENTER,
            'spaceBefore' => 0,
            'spaceAfter' => 140,
            'spacing' => 240,
        ]);

        $phpWord->addParagraphStyle('exec.entry.meta', [
            'spaceBefore' => 0,
            'spaceAfter' => 40,
            'spacing' => 240,
        ]);

        $phpWord->addParagraphStyle('exec.entry.score', [
            'spaceBefore' => 0,
            'spaceAfter' => 30,
            'spacing' => 220,
        ]);

        $phpWord->addParagraphStyle('exec.subheading', [
            'spaceBefore' => 40,
            'spaceAfter' => 60,
            'spacing' => 240,
        ]);

        $phpWord->addParagraphStyle('exec.reference.name', [
            'spaceBefore' => 40,
            'spaceAfter' => 50,
            'spacing' => 240,
        ]);

        $phpWord->addParagraphStyle('exec.paragraph', [
            'spaceBefore' => 0,
            'spaceAfter' => 70,
            'spacing' => 240,
        ]);

        $phpWord->addParagraphStyle('exec.entry.after', [
            'spaceBefore' => 0,
            'spaceAfter' => 130,
        ]);

        $phpWord->addParagraphStyle('exec.list.item', [
            'spaceBefore' => 0,
            'spaceAfter' => 40,
            'spacing' => 240,
        ]);
    }

    private function addSectionDivider(Section $section): void
    {
        $section->addText(' ', ['size' => 1, 'color' => 'FFFFFF'], 'exec.section.divider');
    }

    private function addSectionTitle(Section $section, string $title): void
    {
        $section->addText($title, [
            'name' => 'Georgia',
            'size' => 15,
            'bold' => true,
            'color' => '1F1F1F',
        ], 'exec.section.title');
    }

    private function addEntryHeader(
        Section $section,
        string $title,
        string $rightText,
        bool $rightItalic = true
    ): void {
        $table = $section->addTable([
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 0,
            'alignment' => Jc::START,
        ]);

        $table->addRow();

        $leftCell = $table->addCell(7000, ['valign' => 'bottom']);
        $leftCell->addText($title, [
            'size' => 12,
            'bold' => true,
            'color' => '1A1A1A',
        ], [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 240,
        ]);

        $rightCell = $table->addCell(2300, ['valign' => 'bottom']);
        if ($rightText !== '') {
            $style = [
                'size' => 11,
                'color' => '666666',
            ];
            if ($rightItalic) {
                $style['italic'] = true;
            }

            $rightCell->addText($rightText, $style, [
                'alignment' => Jc::END,
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 240,
            ]);
        }
    }

    /**
     * @param array<int, array{text:string, url?:string}> $items
     */
    private function addCenteredContactLine(Section $section, array $items): void
    {
        $run = $section->addTextRun('exec.contact.par');
        $plainStyle = ['size' => 11, 'color' => '666666'];
        $linkStyle = ['size' => 11, 'color' => '666666', 'underline' => 'none'];

        $index = 0;
        foreach ($items as $item) {
            $text = $this->safeText($item['text'] ?? '');
            if ($text === '') {
                continue;
            }

            if ($index > 0) {
                $run->addText(' | ', $plainStyle);
            }

            $url = $this->normalizeLink($this->safeText($item['url'] ?? ''));
            if ($url !== null) {
                $run->addLink($url, $text, $linkStyle);
            } else {
                $run->addText($text, $plainStyle);
            }

            $index++;
        }
    }

    private function normalizeLink(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (str_starts_with(strtolower($value), 'mailto:')) {
            return $value;
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'mailto:' . $value;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (preg_match('/^[^\s\/]+\.[^\s]+$/', $value) === 1) {
            return 'https://' . $value;
        }

        return null;
    }

    /**
     * @return array<int, array{title:string,text:string}>
     */
    private function buildCompetencies(array $resume): array
    {
        $skillsNode = data_get($resume, 'skills', []);
        $skillItems = [];
        if (is_array($skillsNode)) {
            $nestedSkills = data_get($skillsNode, 'skills', []);
            $nestedItems = data_get($skillsNode, 'items', []);

            if (is_array($nestedSkills) && !empty($nestedSkills)) {
                $skillItems = $nestedSkills;
            } elseif (is_array($nestedItems) && !empty($nestedItems)) {
                $skillItems = $nestedItems;
            } elseif ($this->isListArray($skillsNode)) {
                $skillItems = $skillsNode;
            }
        }

        $competencies = [];
        foreach ($skillItems as $skill) {
            if (!is_array($skill)) {
                continue;
            }

            $title = $this->safeText(data_get($skill, 'name'))
                ?: $this->safeText(data_get($skill, 'title'))
                ?: $this->safeText(data_get($skill, 'category'))
                ?: $this->safeText(data_get($skill, 'skill'));

            $keywords = (array) data_get($skill, 'keywords', []);
            $keywordText = implode(', ', array_values(array_filter(
                array_map(fn ($v) => $this->safeText($v), $keywords),
                static fn ($v) => $v !== ''
            )));

            $levelText = $this->safeText(data_get($skill, 'level'));
            $text = $this->safeText(data_get($skill, 'description')) ?: $this->safeText(data_get($skill, 'summary'));
            if ($text === '') {
                $parts = array_values(array_filter([$keywordText, $levelText], static fn ($v) => $v !== ''));
                $text = implode(', ', $parts);
            }

            if ($title !== '' || $text !== '') {
                $competencies[] = [
                    'title' => $title,
                    'text' => $text,
                ];
            }
        }

        return $competencies;
    }

    /**
     * @param array<int, array{title:string,text:string}> $competencies
     */
    private function renderCompetencyColumns(Section $section, array $competencies): void
    {
        if (empty($competencies)) {
            return;
        }

        $splitAt = (int) ceil(count($competencies) / 2);
        $leftColumn = array_slice($competencies, 0, $splitAt);
        $rightColumn = array_slice($competencies, $splitAt);

        $table = $section->addTable([
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 0,
        ]);
        $table->addRow();

        $leftCell = $table->addCell(4600, ['valign' => 'top']);
        $rightCell = $table->addCell(4600, ['valign' => 'top']);

        foreach ($leftColumn as $competency) {
            if (($competency['title'] ?? '') !== '') {
                $leftCell->addText($competency['title'], [
                    'size' => 11,
                    'bold' => true,
                    'color' => '1F1F1F',
                ], [
                    'spaceBefore' => 0,
                    'spaceAfter' => 40,
                    'spacing' => 240,
                ]);
            }

            if (($competency['text'] ?? '') !== '') {
                $leftCell->addText($competency['text'], [
                    'size' => 11,
                    'color' => '363636',
                ], [
                    'spaceBefore' => 0,
                    'spaceAfter' => 90,
                    'spacing' => 240,
                ]);
            }
        }

        foreach ($rightColumn as $competency) {
            if (($competency['title'] ?? '') !== '') {
                $rightCell->addText($competency['title'], [
                    'size' => 11,
                    'bold' => true,
                    'color' => '1F1F1F',
                ], [
                    'spaceBefore' => 0,
                    'spaceAfter' => 40,
                    'spacing' => 240,
                ]);
            }

            if (($competency['text'] ?? '') !== '') {
                $rightCell->addText($competency['text'], [
                    'size' => 11,
                    'color' => '363636',
                ], [
                    'spaceBefore' => 0,
                    'spaceAfter' => 90,
                    'spacing' => 240,
                ]);
            }
        }
    }

    /**
     * Convert a HTML-ish string into Word content.
     * Supports: <p>, <br>, <b>/<strong>, <i>/<em>, <u>, <ul>/<ol>/<li>.
     */
    private function addHtmlBlock(
        Section $section,
        string $html,
        array $baseTextStyle = ['size' => 11],
        string $paragraphStyle = 'exec.paragraph'
    ): void {
        $html = trim($html);
        if ($html === '') {
            return;
        }

        $html = str_replace(["\r\n", "\r"], "\n", $html);
        $wrapped = '<div>' . $html . '</div>';

        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            mb_convert_encoding($wrapped, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $root = $dom->getElementsByTagName('div')->item(0);
        if (!$root) {
            $section->addText($this->plain($html), $baseTextStyle, $paragraphStyle);
            return;
        }

        if (!$this->hasBlockLevelChildren($root)) {
            $run = $section->addTextRun($paragraphStyle);
            $this->renderInlineIntoRun($run, $root, $baseTextStyle, [
                'bold' => false,
                'italic' => false,
                'underline' => false,
            ]);
            return;
        }

        $this->renderNodeChildren($section, $root, $baseTextStyle, $paragraphStyle, 0, null);
    }

    private function hasBlockLevelChildren(\DOMNode $node): bool
    {
        foreach ($node->childNodes as $child) {
            if (!($child instanceof \DOMElement)) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, ['p', 'div', 'ul', 'ol', 'li'], true)) {
                return true;
            }

            if ($this->hasBlockLevelChildren($child)) {
                return true;
            }
        }

        return false;
    }

    private function renderNodeChildren(
        Section $section,
        \DOMNode $node,
        array $baseTextStyle,
        string $paragraphStyle,
        int $listLevel = 0,
        ?string $listType = null
    ): void {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $this->plain($child->nodeValue ?? '');
                if ($text !== '') {
                    $section->addText($text, $baseTextStyle, $paragraphStyle);
                }
                continue;
            }

            if (!($child instanceof \DOMElement)) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if ($tag === 'br') {
                $section->addTextBreak(1);
                continue;
            }

            if ($tag === 'p' || $tag === 'div') {
                $run = $section->addTextRun($paragraphStyle);
                $this->renderInlineIntoRun($run, $child, $baseTextStyle, [
                    'bold' => false,
                    'italic' => false,
                    'underline' => false,
                ]);
                continue;
            }

            if ($tag === 'ul' || $tag === 'ol') {
                $this->renderList($section, $child, $baseTextStyle, $paragraphStyle, $listLevel, $tag);
                continue;
            }

            if ($tag === 'li') {
                $this->renderListItem($section, $child, $baseTextStyle, $paragraphStyle, $listLevel, $listType ?: 'ul');
                continue;
            }

            $run = $section->addTextRun($paragraphStyle);
            $this->renderInlineIntoRun($run, $child, $baseTextStyle, ['bold' => false, 'italic' => false, 'underline' => false]);
        }
    }

    private function renderList(
        Section $section,
        \DOMElement $listElement,
        array $baseTextStyle,
        string $paragraphStyle,
        int $level,
        string $type
    ): void {
        foreach ($listElement->childNodes as $child) {
            if (!($child instanceof \DOMElement)) {
                continue;
            }
            if (strtolower($child->tagName) !== 'li') {
                continue;
            }

            $this->renderListItem($section, $child, $baseTextStyle, $paragraphStyle, $level, $type);
        }
    }

    private function renderListItem(
        Section $section,
        \DOMElement $listItem,
        array $baseTextStyle,
        string $paragraphStyle,
        int $level,
        string $type
    ): void {
        $inlineHtml = '';
        foreach ($listItem->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $tag = strtolower($child->tagName);
                if ($tag === 'ul' || $tag === 'ol') {
                    continue;
                }
            }
            $inlineHtml .= $listItem->ownerDocument->saveHTML($child);
        }

        $inlineHtml = trim($inlineHtml);
        $listStyleType = $type === 'ol' ? ListItem::TYPE_NUMBER : ListItem::TYPE_BULLET_FILLED;

        if ($inlineHtml !== '') {
            $run = $section->addListItemRun($level, $listStyleType, 'exec.list.item');
            $this->renderInlineHtmlStringIntoRun($run, $inlineHtml, $baseTextStyle);
        } else {
            $plainText = $this->plain($listItem->textContent ?? '');
            if ($plainText !== '') {
                $section->addListItem($plainText, $level, $baseTextStyle, $listStyleType, 'exec.list.item');
            }
        }

        foreach ($listItem->childNodes as $child) {
            if (!($child instanceof \DOMElement)) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if ($tag === 'ul' || $tag === 'ol') {
                $this->renderList($section, $child, $baseTextStyle, $paragraphStyle, $level + 1, $tag);
            }
        }
    }

    private function renderInlineHtmlStringIntoRun(TextRun $run, string $html, array $baseTextStyle): void
    {
        $wrapped = '<span>' . $html . '</span>';

        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            mb_convert_encoding($wrapped, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $root = $dom->getElementsByTagName('span')->item(0);
        if (!$root) {
            $run->addText($this->plain($html), $baseTextStyle);
            return;
        }

        $this->renderInlineIntoRun($run, $root, $baseTextStyle, ['bold' => false, 'italic' => false, 'underline' => false]);
    }

    private function renderInlineIntoRun(
        TextRun $run,
        \DOMNode $node,
        array $baseTextStyle,
        array $flags
    ): void {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $this->plain($child->nodeValue ?? '');
                if ($text === '') {
                    continue;
                }

                $style = $baseTextStyle + [
                    'bold' => $flags['bold'] ?? false,
                    'italic' => $flags['italic'] ?? false,
                    'underline' => ($flags['underline'] ?? false) ? 'single' : null,
                ];

                if (empty($style['underline'])) {
                    unset($style['underline']);
                }

                $run->addText($text, $style);
                continue;
            }

            if (!($child instanceof \DOMElement)) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if ($tag === 'br') {
                $run->addTextBreak(1);
                continue;
            }

            if ($tag === 'ul' || $tag === 'ol') {
                $text = $this->plain($child->textContent ?? '');
                if ($text !== '') {
                    $run->addText($text, $baseTextStyle);
                }
                continue;
            }

            $nextFlags = $flags;
            if ($tag === 'b' || $tag === 'strong') {
                $nextFlags['bold'] = true;
            }
            if ($tag === 'i' || $tag === 'em') {
                $nextFlags['italic'] = true;
            }
            if ($tag === 'u') {
                $nextFlags['underline'] = true;
            }

            if ($tag === 'p' || $tag === 'div') {
                $this->renderInlineIntoRun($run, $child, $baseTextStyle, $nextFlags);
                $run->addTextBreak(1);
                continue;
            }

            $this->renderInlineIntoRun($run, $child, $baseTextStyle, $nextFlags);
        }
    }

    private function textHasVisibleContent(string $html): bool
    {
        $plain = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = str_replace("\xC2\xA0", ' ', $plain);
        return trim($plain) !== '';
    }

    private function safeText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (!is_string($value) && !is_numeric($value)) {
            return '';
        }

        return trim((string) $value);
    }

    private function isListArray(array $array): bool
    {
        $index = 0;
        foreach (array_keys($array) as $key) {
            if ($key !== $index++) {
                return false;
            }
        }
        return true;
    }

    private function formatDateRange(mixed $start, mixed $end): string
    {
        $startText = $this->safeText($start);
        $endText = $this->safeText($end);

        if ($startText === '' && $endText === '') {
            return '';
        }

        if ($startText !== '' && $endText === '') {
            return $startText . ' - Present';
        }

        if ($startText === '' && $endText !== '') {
            return $endText;
        }

        return $startText . ' - ' . $endText;
    }

    private function compactLocation(mixed $city, mixed $region): string
    {
        $cityText = $this->safeText($city);
        $regionText = $this->safeText($region);

        if ($cityText === '' && $regionText === '') {
            return '';
        }

        if ($cityText !== '' && $regionText !== '') {
            return $cityText . ', ' . $regionText;
        }

        return $cityText !== '' ? $cityText : $regionText;
    }

    private function plain(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return trim($value);
    }
}
