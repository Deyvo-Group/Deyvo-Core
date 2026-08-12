<?php

declare(strict_types=1);

namespace Deyvo\Core\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

final class HtmlSanitizer
{
    private const AllowedElements = [
        'a',
        'article',
        'aside',
        'b',
        'blockquote',
        'br',
        'code',
        'div',
        'em',
        'figcaption',
        'figure',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'hr',
        'i',
        'li',
        'main',
        'ol',
        'p',
        'pre',
        'section',
        'small',
        'span',
        'strong',
        'sub',
        'sup',
        'table',
        'tbody',
        'td',
        'tfoot',
        'th',
        'thead',
        'tr',
        'ul',
    ];

    private const GlobalAttributes = [
        'class',
        'id',
        'title',
    ];

    private const RemovedElements = [
        'base',
        'embed',
        'form',
        'iframe',
        'object',
        'script',
        'style',
        'svg',
    ];

    public function clean(mixed $html): string
    {
        if (! is_string($html) || $html === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<div id="deyvo-html-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $root = $document->getElementById('deyvo-html-root');

        if (! $root instanceof DOMElement) {
            return '';
        }

        $this->cleanChildren($root);
        $result = '';

        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child) ?: '';
        }

        return trim($result);
    }

    private function cleanChildren(DOMElement $element): void
    {
        $child = $element->firstChild;

        while ($child instanceof DOMNode) {
            $next = $child->nextSibling;

            if (! $child instanceof DOMElement) {
                $child = $next;

                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::RemovedElements, true)) {
                $child->parentNode?->removeChild($child);
                $child = $next;

                continue;
            }

            if (! in_array($tag, self::AllowedElements, true)) {
                $this->cleanChildren($child);
                $this->unwrap($child);
                $child = $next;

                continue;
            }

            $this->cleanAttributes($child, $tag);
            $this->cleanChildren($child);
            $child = $next;
        }
    }

    private function cleanAttributes(DOMElement $element, string $tag): void
    {
        $attributes = [];

        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute->name;
        }

        foreach ($attributes as $attribute) {
            $name = strtolower($attribute);
            $value = $element->getAttribute($attribute);

            if (in_array($name, self::GlobalAttributes, true)) {
                continue;
            }

            if ($tag === 'a' && $name === 'href' && $this->safeUrl($value)) {
                continue;
            }

            if ($tag === 'a' && $name === 'target' && in_array($value, ['_blank', '_self'], true)) {
                if ($value === '_blank') {
                    $element->setAttribute('rel', 'noreferrer noopener');
                }

                continue;
            }

            if ($tag === 'a' && $name === 'rel') {
                $element->setAttribute('rel', 'noreferrer noopener');

                continue;
            }

            $element->removeAttribute($attribute);
        }
    }

    private function safeUrl(string $url): bool
    {
        $value = trim($url);

        return $value === ''
            || str_starts_with($value, '#')
            || str_starts_with($value, '/')
            || preg_match('/^(https?:|mailto:|tel:)/i', $value) === 1;
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent instanceof DOMNode) {
            return;
        }

        while ($element->firstChild instanceof DOMNode) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
