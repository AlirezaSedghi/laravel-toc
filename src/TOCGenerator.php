<?php

namespace Alirezasedghi\LaravelTOC;

use Illuminate\Support\Str;

class TOCGenerator
{
    protected string $html;

    protected array $toc = [];

    protected array $used = [];

    protected \DOMDocument $dom;

    protected array $options;

    private int $minFoundLevel = 6;

    private array $headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    /**
     * Default options
     */
    protected array $defaultOptions = [
        'list_type' => 'ul',            // Type of the lists (e.g., ul, ol)
        'toc_class' => 'toc',           // Class for lists that contains the TOC
        'internal_list_class' => '',    // Class for internal lists
        'toc_item_class' => '',         // Class for each <li> in the TOC
        'toc_link_class' => '',         // Class for each <a> in the TOC
        'heading_class' => '',          // Class for each heading (h1-h6) in the original HTML
        'min_level' => 1,               // Minimum heading level to include (e.g., h1)
        'max_level' => 6,               // Maximum heading level to include (e.g., h6)
    ];

    /**
     * Construction
     */
    public function __construct(?string $html, array $options = [])
    {
        $this->html = $html;
        $this->dom = new \DOMDocument;
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * Parse HTML to find heading tags (h1, h2, ... h6)
     */
    public function generateTOC(): string
    {
        // Remove non-breaking space entities from input HTML
        $html = str_replace('&nbsp;', ' ', $this->html);

        if (empty($html)) {
            return $this->html;
        }

        libxml_use_internal_errors(true);
        @$this->dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'."\n".mb_convert_encoding($this->html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD); // Suppress warnings

        $minLevel = $this->options['min_level'];
        $maxLevel = $this->options['max_level'];

        // Get the minimum level
        $this->minFoundLevel = $this->findMinHeadingLevel();

        foreach ($this->headings as $index => $heading) {
            $level = $index + 1;
            if ($level >= $minLevel && $level <= $maxLevel) {
                $nodes = $this->dom->getElementsByTagName($heading);
                foreach ($nodes as $node) {
                    $this->processHeading($node, $heading);
                }
            }
        }

        return $this->renderTOC();
    }

    /**
     * Process each heading and add ID attribute for the link
     */
    protected function processHeading(\DOMElement $node, $heading): void
    {
        // Gent node id
        $anchor = $node->getAttribute('id');

        // Get heading text and build anchor
        $heading_text = trim(strip_tags($node->textContent));

        // if tag doesn't have a defined attribute "id"
        if (empty($anchor)) {
            $anchor = $this->slugify($heading_text);
            $node->setAttribute('id', $anchor);
        }

        // Optionally, add a class to each heading if specified
        if (! empty($this->options['heading_class'])) {
            $current_class = $node->getAttribute('class');
            $node->setAttribute('class', trim($current_class.' '.$this->options['heading_class']));
        }

        // Get the original level from the heading tag (h1 -> 1, h2 -> 2, etc.)
        $level = intval(substr($heading, 1));

        // Normalize the level so the lowest heading starts at level 1
        $normalized_level = $level - $this->minFoundLevel + 1;

        // Add the heading to the TOC array
        $this->toc[] = [
            'level' => $normalized_level,
            'text' => $heading_text,
            'anchor' => $anchor,
        ];
    }

    /**
     * Slugify
     */
    protected function slugify(string $string): string
    {
        // Remove punctuation
        $string = preg_replace("/[^\p{L}\p{N} ]+/u", '', trim($string));

        // Remove non-breaking spaces
        $string = str_replace(['&nbsp;', '‌'], ' ', $string);

        // Generate slug
        $slugged = Str::slug($string, '-', null);

        // Check if slug is used before
        $count = 1;
        $original = $slugged;
        while (in_array($slugged, $this->used)) {
            $slugged = $original.'-'.$count;
            $count++;
        }

        $this->used[] = $slugged;

        return $slugged;
    }

    // Find the minimum heading level in the HTML
    protected function findMinHeadingLevel(): int
    {
        $minLevel = 6; // Start with the maximum possible heading level

        foreach ($this->headings as $heading) {
            $matches = $this->dom->getElementsByTagName($heading);

            if ($matches->length > 0) {
                // Get the level from the heading tag (h1 -> 1, h2 -> 2, etc.)
                $level = intval(substr($heading, 1));
                if ($level < $minLevel) {
                    $minLevel = $level; // Update the minimum level
                }
            }
        }

        return $minLevel;
    }

    /**
     * Render the TOC as nested <ul><li> elements
     */
    protected function renderTOC(): string
    {
        // Set list type by options
        $list_type = $this->options['list_type'];
        if ($list_type !== 'ul' && $list_type !== 'ol') {
            $list_type = 'ul';
        }

        // Return empty if no any nod exists
        if (empty($this->toc)) {
            return '';
        }

        $output = '<'.$list_type.' class="'.$this->options['toc_class'].'">';
        $previousLevel = 1;
        $internalListClass = ! empty($this->options['internal_list_class']) ? ' class="'.$this->options['internal_list_class'].'"' : '';

        // Loop items to output
        foreach ($this->toc as $item) {
            $level = $item['level'];

            if ($level > $previousLevel) {
                $output .= "<{$list_type}{$internalListClass}>";
            } elseif ($level < $previousLevel) {
                $output .= str_repeat('</li></'.$list_type.'>', $previousLevel - $level);
            }

            // Add user-defined or default class for each TOC item (li and a)
            $itemClass = ! empty($this->options['toc_item_class']) ? ' class="'.$this->options['toc_item_class'].'"' : '';
            $linkClass = ! empty($this->options['toc_link_class']) ? ' class="'.$this->options['toc_link_class'].'"' : '';
            $output .= "<li{$itemClass}><a href=\"#{$item['anchor']}\"{$linkClass}>{$item['text']}</a>";

            $previousLevel = $level;
        }

        $output .= str_repeat('</li></'.$list_type.'>', $previousLevel - 1);
        $output .= '</'.$list_type.'>';

        return $output;
    }

    /**
     * Get the processed HTML with heading IDs
     */
    public function getProcessedHtml(): string
    {
        return $this->dom->saveHTML();
    }
}
