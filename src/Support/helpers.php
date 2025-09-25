<?php

use FewFar\Sitekit\ViewModels\Values;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

if (! function_exists('attrs')) {
    /**
     * Creates an attribute bag for use in blade templates.
     *
     * @return \Illuminate\View\ComponentAttributeBag
     */
    function attrs(?iterable $attrs = null) {
        return app(\Illuminate\View\ComponentAttributeBag::class, [
            'attributes' => collect($attrs)->all(),
        ]);
    }
}

if (! function_exists('text_content')) {
    /**
     * Strips all HTML tags from the given html. Unlike strip_tags, which is more like a filter,
     * text_content is intended to remove get the text values of the given content instead.
     *
     * @example '<p>Paragraph</p>' => 'Paragraph'
     *
     * @return string|null  will not return an empty string, but null instead.
     */
    function text_content(Htmlable|string|null $value) {
        $dom = new DOMDocument();
        $dom->loadHTML(match (true) {
            $value instanceof Htmlable => $value->toHtml(),
            default => $value,
        });

        return trim($dom->textContent) ?: null;
    }
}

if (! function_exists('values')) {
    function values($iterable = null) {
        if ($iterable instanceof Values) {
            return $iterable;
        }

        return app(Values::class)->setInstance($iterable);
    }
}
