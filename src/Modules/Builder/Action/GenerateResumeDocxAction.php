<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Action;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\ListItem;

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

        // ---- Styles to remove the huge gaps around bullets ----
        $phpWord->addParagraphStyle('resume.list.item', [
            'spaceBefore' => 0,
            'spaceAfter'  => 0,
            'spacing'     => 120, // line spacing (twips). ~1.0–1.15 line
        ]);

        $phpWord->addParagraphStyle('resume.section.title', [
            'spaceBefore' => 120,
            'spaceAfter'  => 60,
        ]);

        // Used as a "blank line" spacer after a section
        $phpWord->addParagraphStyle('resume.block.after', [
            'spaceBefore' => 0,
            'spaceAfter'  => 120,
        ]);

        // NEW: paragraph style for normal text blocks (summary, paragraphs)
        $phpWord->addParagraphStyle('resume.paragraph', [
            'spaceBefore' => 0,
            'spaceAfter'  => 120,
            'spacing'     => 240, // slightly looser than list items; tweak if you want
        ]);

        $basics = $resume['basics'] ?? [];

        // Header
        $section->addText($basics['name'] ?? 'Your Name', ['bold' => true, 'size' => 18]);

        if (!empty($basics['label'])) {
            $section->addText($this->plain($basics['label']), ['italic' => true, 'size' => 11]);
        }

        $section->addTextBreak(1);

        // Contact row
        $contact = [];
        if (!empty($basics['email'])) $contact[] = $this->plain($basics['email']);
        if (!empty($basics['url'])) $contact[] = $this->plain($basics['url']);

        if (!empty($contact)) {
            $section->addText(implode(' • ', $contact), ['size' => 10]);
            $section->addTextBreak(1);
        }

        // Summary (supports basic HTML)
        if (!empty($basics['summary'])) {
            // FIX: inline-only HTML is now rendered as ONE paragraph
            $this->addHtmlBlock($section, (string) $basics['summary'], ['size' => 11]);
        }

        // Skills
        if (!empty($resume['skills']) && is_array($resume['skills'])) {
            $section->addText('Skills', ['bold' => true, 'size' => 14], 'resume.section.title');

            foreach ($resume['skills'] as $skill) {
                $name  = is_array($skill) ? ($skill['name'] ?? null) : $skill;
                $level = is_array($skill) ? ($skill['level'] ?? null) : null;

                $line = trim((string) $name);
                if ($line === '') continue;

                if ($level) $line .= " ({$level})";

                $section->addListItem(
                    $this->plain($line),
                    0,
                    ['size' => 11],
                    ListItem::TYPE_BULLET_FILLED,
                    'resume.list.item'
                );
            }

            // spacer after section
            $section->addText('', [], 'resume.block.after');
        }

        // Education
        if (!empty($resume['education']) && is_array($resume['education'])) {
            $section->addText('Education', ['bold' => true, 'size' => 14], 'resume.section.title');

            foreach ($resume['education'] as $edu) {
                if (!is_array($edu)) continue;

                $institution = $this->plain($edu['institution'] ?? '');
                $studyType   = $this->plain($edu['studyType'] ?? '');
                $area        = $this->plain($edu['area'] ?? '');

                $line = trim($institution . ' — ' . trim($studyType . ' ' . $area), " —");
                if ($line !== '') {
                    $section->addText($line, ['bold' => true, 'size' => 11]);
                }

                $start = $this->plain($edu['startDate'] ?? '');
                $end   = $this->plain($edu['endDate'] ?? '');
                $dates = trim($start . ' - ' . ($end ?: 'Present'), ' -');
                if ($dates !== '') {
                    $section->addText($dates, ['italic' => true, 'size' => 10]);
                }

                if (!empty($edu['score'])) {
                    $section->addText('Score: ' . $this->plain((string) $edu['score']), ['size' => 10]);
                }

                $section->addTextBreak(1);
            }
        }

        // Work (supports basic HTML: p/br/strong/em/u/ul/ol/li)
        if (!empty($resume['work']) && is_array($resume['work'])) {
            $section->addText('Experience', ['bold' => true, 'size' => 14], 'resume.section.title');

            foreach ($resume['work'] as $work) {
                if (!is_array($work)) continue;

                $position = $this->plain($work['position'] ?? '');
                $company  = $this->plain($work['name'] ?? '');
                $header   = trim($position . ($company ? " — {$company}" : ''));

                if ($header !== '') {
                    $section->addText($header, ['bold' => true, 'size' => 11]);
                }

                $start = $this->plain($work['startDate'] ?? '');
                $end   = $this->plain($work['endDate'] ?? '');
                $dates = trim($start . ' - ' . ($end ?: 'Present'), ' -');
                if ($dates !== '') {
                    $section->addText($dates, ['italic' => true, 'size' => 10]);
                }

                if (!empty($work['summary'])) {
                    $this->addHtmlBlock($section, (string) $work['summary'], ['size' => 11]);
                }

                if (!empty($work['highlights']) && is_array($work['highlights'])) {
                    foreach ($work['highlights'] as $h) {
                        $hText = $this->plain((string) $h);
                        if ($hText === '') continue;

                        $section->addListItem(
                            $hText,
                            0,
                            ['size' => 11],
                            ListItem::TYPE_BULLET_FILLED,
                            'resume.list.item'
                        );
                    }
                }

                $section->addTextBreak(1);
            }
        }

        // References
        if (!empty($resume['references']) && is_array($resume['references'])) {
            $section->addText('References', ['bold' => true, 'size' => 14], 'resume.section.title');

            foreach ($resume['references'] as $r) {
                if (!is_array($r)) continue;

                $name = $this->plain($r['name'] ?? '');
                $ref  = (string) ($r['reference'] ?? '');

                if ($name !== '') $section->addText($name, ['bold' => true, 'size' => 11]);

                if (trim($ref) !== '') {
                    $this->addHtmlBlock($section, $ref, ['size' => 11]);
                }

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

    /**
     * Convert a HTML-ish string into Word content:
     * Supports: <p>, <br>, <b>/<strong>, <i>/<em>, <u>, <ul>/<ol>/<li>
     *
     * IMPORTANT FIX:
     * If the HTML is inline-only (no p/div/ul/ol/li), render it as ONE paragraph
     * so it doesn't break into random lines.
     */
    private function addHtmlBlock(Section $section, string $html, array $baseTextStyle = ['size' => 11]): void
    {
        $html = trim($html);
        if ($html === '') return;

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
            $section->addText($this->plain($html), $baseTextStyle);
            return;
        }

        // If there are no block-level tags, render it as one paragraph TextRun
        if (!$this->hasBlockLevelChildren($root)) {
            $run = $section->addTextRun('resume.paragraph');
            $this->renderInlineIntoRun($run, $root, $baseTextStyle, [
                'bold' => false,
                'italic' => false,
                'underline' => false,
            ]);
            return;
        }

        // Otherwise use normal block rendering
        $this->renderNodeChildren($section, $root, $baseTextStyle, 0, null);

        // Add consistent spacing after the block
        $section->addText('', [], 'resume.block.after');
    }

    /**
     * Detect block-level tags anywhere inside a node.
     * If present, we should render with paragraph/list logic instead of a single TextRun.
     */
    private function hasBlockLevelChildren(\DOMNode $node): bool
    {
        foreach ($node->childNodes as $child) {
            if (!($child instanceof \DOMElement)) continue;

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
        int $listLevel = 0,
        ?string $listType = null
    ): void {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $this->plain($child->nodeValue ?? '');
                if ($text !== '') {
                    // IMPORTANT: keep block rendering behavior for real block content
                    $section->addText($text, $baseTextStyle, 'resume.paragraph');
                }
                continue;
            }

            if (!($child instanceof \DOMElement)) continue;

            $tag = strtolower($child->tagName);

            if ($tag === 'br') {
                $section->addTextBreak(1);
                continue;
            }

            if ($tag === 'p' || $tag === 'div') {
                $run = $section->addTextRun('resume.paragraph');
                $this->renderInlineIntoRun($run, $child, $baseTextStyle, ['bold' => false, 'italic' => false, 'underline' => false]);
                continue;
            }

            if ($tag === 'ul' || $tag === 'ol') {
                $this->renderList($section, $child, $baseTextStyle, $listLevel, $tag);
                continue;
            }

            if ($tag === 'li') {
                $this->renderListItem($section, $child, $baseTextStyle, $listLevel, $listType ?: 'ul');
                continue;
            }

            $run = $section->addTextRun('resume.paragraph');
            $this->renderInlineIntoRun($run, $child, $baseTextStyle, ['bold' => false, 'italic' => false, 'underline' => false]);
        }
    }

    private function renderList(
        Section $section,
        \DOMElement $listEl,
        array $baseTextStyle,
        int $level,
        string $type
    ): void {
        foreach ($listEl->childNodes as $child) {
            if (!($child instanceof \DOMElement)) continue;
            if (strtolower($child->tagName) !== 'li') continue;

            $this->renderListItem($section, $child, $baseTextStyle, $level, $type);
        }
    }

    private function renderListItem(
        Section $section,
        \DOMElement $li,
        array $baseTextStyle,
        int $level,
        string $type
    ): void {
        $inlineHtml = '';

        foreach ($li->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $t = strtolower($child->tagName);
                if ($t === 'ul' || $t === 'ol') {
                    continue;
                }
            }

            $inlineHtml .= $li->ownerDocument->saveHTML($child);
        }

        $inlineHtml = trim($inlineHtml);

        $listStyleType = $type === 'ol'
            ? ListItem::TYPE_NUMBER
            : ListItem::TYPE_BULLET_FILLED;

        if ($inlineHtml !== '') {
            $run = $section->addListItemRun($level, $listStyleType, 'resume.list.item');
            $this->renderInlineHtmlStringIntoRun($run, $inlineHtml, $baseTextStyle);
        } else {
            $plainText = $this->plain($li->textContent ?? '');
            if ($plainText !== '') {
                $section->addListItem($plainText, $level, $baseTextStyle, $listStyleType, 'resume.list.item');
            }
        }

        foreach ($li->childNodes as $child) {
            if (!($child instanceof \DOMElement)) continue;

            $t = strtolower($child->tagName);
            if ($t === 'ul' || $t === 'ol') {
                $this->renderList($section, $child, $baseTextStyle, $level + 1, $t);
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
                if ($text === '') continue;

                $style = $baseTextStyle + [
                    'bold' => $flags['bold'] ?? false,
                    'italic' => $flags['italic'] ?? false,
                    'underline' => ($flags['underline'] ?? false) ? 'single' : null,
                ];

                if (empty($style['underline'])) unset($style['underline']);

                $run->addText($text, $style);
                continue;
            }

            if (!($child instanceof \DOMElement)) continue;

            $tag = strtolower($child->tagName);

            if ($tag === 'br') {
                $run->addTextBreak(1);
                continue;
            }

            if ($tag === 'ul' || $tag === 'ol') {
                $txt = $this->plain($child->textContent ?? '');
                if ($txt !== '') {
                    $run->addText($txt, $baseTextStyle);
                }
                continue;
            }

            $nextFlags = $flags;

            if ($tag === 'b' || $tag === 'strong') $nextFlags['bold'] = true;
            if ($tag === 'i' || $tag === 'em') $nextFlags['italic'] = true;
            if ($tag === 'u') $nextFlags['underline'] = true;

            if ($tag === 'p' || $tag === 'div') {
                $this->renderInlineIntoRun($run, $child, $baseTextStyle, $nextFlags);
                $run->addTextBreak(1);
                continue;
            }

            $this->renderInlineIntoRun($run, $child, $baseTextStyle, $nextFlags);
        }
    }

    private function plain(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return trim($value);
    }
}
