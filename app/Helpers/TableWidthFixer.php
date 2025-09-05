<?php

namespace App\Helpers;

class TableWidthFixer
{
    /**
     * Ubah width di <colgroup> menjadi width px per <td>/<th> untuk SEMUA baris,
     * lalu hapus <colgroup> dan paksa table-layout: fixed.
     */
    public static function colgroupToCellPx(string $html, ?int $defaultBaseWidthPx = 800): string
    {
        if (trim($html) === '') return $html;

        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $dom  = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>');
        libxml_clear_errors();

        $xpath  = new \DOMXPath($dom);
        $tables = $xpath->query('//table[colgroup]');

        foreach ($tables as $table) {
            /** @var \DOMElement $table */
            $colgroup = null;
            foreach ($table->childNodes as $child) {
                if ($child instanceof \DOMElement && strtolower($child->nodeName) === 'colgroup') {
                    $colgroup = $child;
                    break;
                }
            }
            if (!$colgroup) continue;

            // 1) baseline lebar tabel (px)
            $base = self::extractElementWidthPx($table);
            if ($base === null) {
                $sumPx = 0; $hasPx = false;
                foreach ($colgroup->getElementsByTagName('col') as $col) {
                    $w = self::extractStyleOrAttrWidth($col);
                    if ($w !== null && str_ends_with($w, 'px')) {
                        $hasPx = true;
                        $sumPx += (int) round((float) $w);
                    }
                }
                if ($hasPx && $sumPx > 0) $base = $sumPx;
            }
            if ($base === null) $base = $defaultBaseWidthPx ?: 800;

            // 2) daftar width per <col> → px
            $colWidthsPx = [];
            foreach ($colgroup->getElementsByTagName('col') as $col) {
                $w = self::extractStyleOrAttrWidth($col);
                $colWidthsPx[] = self::normalizeToPx($w, $base);
            }

            // 3) terapkan ke semua baris, perhatikan colspan
            $rows = $table->getElementsByTagName('tr');
            foreach ($rows as $tr) {
                self::applyWidthsToRow($tr, $colWidthsPx);
            }

            // 4) bereskan
            $table->removeChild($colgroup);
            // self::appendOrReplaceStyle($table, 'table-layout', 'fixed');
            self::appendOrReplaceStyle($table, 'border-collapse', 'collapse');
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        $out = '';
        foreach ($body->childNodes as $child) $out .= $dom->saveHTML($child);
        return $out;
    }

    /**
     * Ubah width di <colgroup> menjadi width px per <td>/<th> untuk BARIS PERTAMA SAJA,
     * lalu hapus <colgroup> dan paksa table-layout: fixed.
     */
    public static function colgroupToFirstRowCellPx(string $html, ?int $defaultBaseWidthPx = 800): string
    {
        if (trim($html) === '') return $html;

        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $dom  = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>');
        libxml_clear_errors();

        $xpath  = new \DOMXPath($dom);
        $tables = $xpath->query('//table[colgroup]');

        foreach ($tables as $table) {
            /** @var \DOMElement $table */
            $colgroup = null;
            foreach ($table->childNodes as $child) {
                if ($child instanceof \DOMElement && strtolower($child->nodeName) === 'colgroup') {
                    $colgroup = $child;
                    break;
                }
            }
            if (!$colgroup) continue;

            // 1) baseline lebar tabel (px)
            $base = self::extractElementWidthPx($table);
            if ($base === null) {
                $sumPx = 0; $hasPx = false;
                foreach ($colgroup->getElementsByTagName('col') as $col) {
                    $w = self::extractStyleOrAttrWidth($col);
                    if ($w !== null && str_ends_with($w, 'px')) {
                        $hasPx = true;
                        $sumPx += (int) round((float) $w);
                    }
                }
                if ($hasPx && $sumPx > 0) $base = $sumPx;
            }
            if ($base === null) $base = $defaultBaseWidthPx ?: 800;

            // 2) daftar width per <col> → px
            $colWidthsPx = [];
            foreach ($colgroup->getElementsByTagName('col') as $col) {
                $w = self::extractStyleOrAttrWidth($col);
                $colWidthsPx[] = self::normalizeToPx($w, $base);
            }

            // 3) hanya baris pertama
            $firstTr = null;
            foreach ($table->getElementsByTagName('tr') as $tr) { $firstTr = $tr; break; }
            if ($firstTr) {
                self::applyWidthsToRow($firstTr, $colWidthsPx);
            }

            // 4) bereskan
            $table->removeChild($colgroup);
            // self::appendOrReplaceStyle($table, 'table-layout', 'fixed');
            self::appendOrReplaceStyle($table, 'border-collapse', 'collapse');
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        $out = '';
        foreach ($body->childNodes as $child) $out .= $dom->saveHTML($child);
        return $out;
    }

    /* ====================== Helpers internal ====================== */

    /** Kembalikan "120px" dari "30%" / "120px" / "120" (dengan baseline px jika perlu). */
    protected static function normalizeToPx(?string $width, int $basePx): ?int
    {
        if ($width === null) return null;

        $w = trim($width);
        if ($w === '') return null;

        if (str_ends_with($w, '%')) {
            return (int) round(((float) $w) * $basePx / 100);
        }
        if (str_ends_with($w, 'px')) {
            return (int) round((float) $w);
        }
        if (is_numeric($w)) {
            return (int) round((float) $w);
        }
        return null;
    }

    /** Terapkan daftar width px ke satu baris (<tr>), perhatikan colspan. */
    protected static function applyWidthsToRow(\DOMElement $tr, array $colWidthsPx): void
    {
        $colIndex = 0;

        // kumpulkan cell langsung (td/th) saja
        $cells = [];
        foreach ($tr->childNodes as $node) {
            if ($node instanceof \DOMElement) {
                $tag = strtolower($node->tagName);
                if ($tag === 'td' || $tag === 'th') $cells[] = $node;
            }
        }

        foreach ($cells as $cell) {
            $colspan = (int) ($cell->getAttribute('colspan') ?: 1);
            if ($colspan < 1) $colspan = 1;

            $sumWidth = 0; $hasAny = false;
            for ($i = 0; $i < $colspan; $i++) {
                $w = $colWidthsPx[$colIndex + $i] ?? null;
                if ($w !== null) { $sumWidth += $w; $hasAny = true; }
            }
            if ($hasAny && $sumWidth > 0) {
                self::appendOrReplaceStyle($cell, 'width', $sumWidth . 'px');
            }

            $colIndex += $colspan;
        }
    }

    /** Parse width dari style/attr element. */
    protected static function extractStyleOrAttrWidth(\DOMElement $el): ?string
    {
        $style = $el->getAttribute('style');
        if ($style) {
            foreach (explode(';', $style) as $decl) {
                if (stripos($decl, 'width') !== false) {
                    [$prop, $val] = array_pad(explode(':', $decl, 2), 2, null);
                    if ($prop && strtolower(trim($prop)) === 'width' && $val) {
                        $v = trim($val);
                        if ($v !== '') return rtrim($v, ';');
                    }
                }
            }
        }
        $attr = $el->getAttribute('width');
        if ($attr !== '') return trim($attr);
        return null;
    }

    /** Ambil lebar px dari <table> (style/attr). */
    protected static function extractElementWidthPx(\DOMElement $table): ?int
    {
        $w = self::extractStyleOrAttrWidth($table);
        if ($w === null) return null;

        if (str_ends_with($w, 'px')) return (int) round((float) $w);
        if (str_ends_with($w, '%')) return null; // tanpa konteks parent
        if (is_numeric($w))   return (int) round((float) $w);
        return null;
    }

    /** Tambah/replace property style inline. */
    protected static function appendOrReplaceStyle(\DOMElement $el, string $prop, string $value): void
    {
        $style = trim($el->getAttribute('style'));
        $new = []; $found = false;
        if ($style !== '') {
            foreach (explode(';', $style) as $decl) {
                $decl = trim($decl);
                if ($decl === '') continue;
                [$p, $v] = array_map('trim', array_pad(explode(':', $decl, 2), 2, ''));
                if ($p === '') continue;
                if (strtolower($p) === strtolower($prop)) {
                    $new[] = $prop . ': ' . $value;
                    $found = true;
                } else {
                    $new[] = $p . ': ' . $v;
                }
            }
        }
        if (!$found) $new[] = $prop . ': ' . $value;
        $el->setAttribute('style', implode('; ', $new) . ';');
    }
}
