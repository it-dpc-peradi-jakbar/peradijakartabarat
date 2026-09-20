<?php

namespace App\Support;

final class HtmlSanitizer
{
    public static function reportBody(?string $html): string
    {
        $html = html_entity_decode((string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/i', '', $html) ?? $html;
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? $html;

        return trim(strip_tags($html, '<p><br><strong><em><ul><ol><li><a><h3><blockquote>'));
    }
}
