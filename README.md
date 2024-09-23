<h1 align="center">Laravel TOC Generator</h1>

<p align="center">
  <a href="" rel="nofollow"><img alt="Required PHP Version" src="https://img.shields.io/badge/php->=8.0.0-blue?style=flat-square"></a>
  <a href="https://packagist.org/packages/alirezasedghi/laravel-toc"><img alt="Total Downloads" src="https://poser.pugx.org/alirezasedghi/laravel-toc/downloads?style=flat-square"></a>
  <a href="https://packagist.org/packages/alirezasedghi/laravel-toc"><img alt="Latest Stable Version" src="https://poser.pugx.org/alirezasedghi/laravel-toc/v/stable?style=flat-square"></a>
  <a href="https://github.com/AlirezaSedghi/laravel-toc/releases"><img alt="Latest Stable Version" src="https://img.shields.io/github/v/release/AlirezaSedghi/laravel-toc?style=flat-square"></a>
  <a href="https://raw.githubusercontent.com/AlirezaSedghi/laravel-toc/master/LICENSE"><img alt="License" src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square"></a>
  <a href="https://github.com/AlirezaSedghi/laravel-toc/issues"><img alt="GitHub issues" src="https://img.shields.io/github/issues/AlirezaSedghi/laravel-toc.svg?style=flat-square"></a>
</p>

A Laravel package to generate a Table of Contents (TOC) from HTML headings (`h1, h2, ... h6`). This package allows you to create a dynamic TOC with customizable options, including the list type (ul or ol), HTML classes, and level filtering.

## Features

- **Automatic TOC generation** from HTML headings.
- **Configurable heading levels** to include (`h1` to `h6`).
- **Customizable list types (ul, ol)** and HTML classes for TOC structure.
- **Customizable HTML classes** for the TOC list and items.
- **Adds unique `id` attributes** to headings for internal linking.

## Installation

Install the package via Composer:

```bash
composer require alirezasedghi/laravel-toc
```

## Usage

### Basic Usage

Here’s how to use the package to generate a Table of Contents and return both the TOC and the processed HTML content.

```php
use Alirezasedghi\LaravelTOC\TOCGenerator;

class PageController extends Controller
{
    public function show(Request $request)
    {
        // Example HTML content
        $html = "<h1>Main Title</h1><h2>Section 1</h2><p>Content...</p>";

        // Initialize TOC generator
        $tocGenerator = new TOCGenerator($html);

        // Generate the TOC and get processed HTML
        $toc = $tocGenerator->generateTOC();
        $processedHtml = $tocGenerator->getProcessedHtml();

        // Return to view
        return view('page', [
            'toc' => $toc,
            'content' => $processedHtml
        ]);
    }
}
```

In your Blade template (`resources/views/page.blade.php`):

```html
<div class="toc-container">
    {!! $toc !!}
</div>

<div class="content">
    {!! $content !!}
</div>
```

### Custom Options

You can customize the TOC generation by passing an options array. The following options are available:

- `list_type`: The type of list to use for the TOC (`ul` for unordered, `ol` for ordered).
- `toc_class`: The CSS class for the main TOC list (`<ul>` or `<ol>`).
- `internal_list_class`: The CSS class for internal nested lists.
- `toc_item_class`: The CSS class for each item (`<li>`) in the TOC.
- `toc_link_class`: The CSS class for each link (`<a>`) in the TOC.
- `heading_class`: A CSS class to add to each heading (`h1`, `h2`, etc.) in the HTML content.
- `min_level`: The minimum heading level to include in the TOC (e.g., `1` for `h1`).
- `max_level`: The maximum heading level to include in the TOC (e.g., `6` for `h6`).

#### Example with Custom Options

```php
use Alirezasedghi\LaravelTOC\TOCGenerator;

class PageController extends Controller
{
    public function show(Request $request)
    {
        // Example HTML content
        $html = "<h1>Main Title</h1><h2>Section 1</h2><h3>Subsection</h3><p>Content...</p>";

        // Define custom options
        $options = [
            'list_type' => 'ol',             // Use an ordered list for the TOC
            'toc_class' => 'custom-toc',     // Class for TOC <ul>/<ol>
            'internal_list_class' => 'nested-toc', // Class for nested <ul>/<ol>
            'toc_item_class' => 'toc-item',  // Class for each <li>
            'toc_link_class' => 'toc-link',  // Class for each <a>
            'heading_class' => 'heading',    // Class for headings
            'min_level' => 2,                // Include only h2-h6
            'max_level' => 3                 // Include up to h3
        ];

        // Initialize TOC generator with custom options
        $tocGenerator = new TOCGenerator($html, $options);

        // Generate the TOC and get processed HTML
        $toc = $tocGenerator->generateTOC();
        $processedHtml = $tocGenerator->getProcessedHtml();

        // Return to view
        return view('page', [
            'toc' => $toc,
            'content' => $processedHtml
        ]);
    }
}
```

### Available Options

| Option              | Type   | Default | Description                                                                |
|---------------------|--------|---------|----------------------------------------------------------------------------|
| `list_type`         | string | `'ul'`  | Type of list for TOC (`ul` for unordered, `ol` for ordered)                 |
| `toc_class`         | string | `'toc'` | CSS class for the main TOC list                                             |
| `internal_list_class` | string | `''`    | CSS class for internal nested lists                                         |
| `toc_item_class`    | string | `''`    | CSS class for each `<li>` inside the TOC                                    |
| `toc_link_class`    | string | `''`    | CSS class for each link (`<a>`) inside the TOC                              |
| `heading_class`     | string | `''`    | CSS class added to each heading (`h1`-`h6`)                                 |
| `min_level`         | int    | `1`     | Minimum heading level to include (e.g., `1` for `h1`)                       |
| `max_level`         | int    | `6`     | Maximum heading level to include (e.g., `6` for `h6`)                       |

### Example Blade Template

Here’s how you can render both the TOC and the processed HTML content in a Blade view:

```html
<div class="toc-container">
    {!! $toc !!}
</div>

<div class="content">
    {!! $content !!}
</div>
```

### Output Example

#### Given the HTML:
```html
<h1>Main Title</h1>
<h2>Section 1</h2>
<h3>Subsection 1.1</h3>
<h2>Section 2</h2>
<h3>Subsection 2.1</h3>
```

#### Generated TOC:
```html
<ul class="toc">
    <li><a href="#toc-1">Main Title</a>
        <ul>
            <li><a href="#toc-2">Section 1</a>
                <ul>
                    <li><a href="#toc-3">Subsection 1.1</a></li>
                </ul>
            </li>
            <li><a href="#toc-4">Section 2</a>
                <ul>
                    <li><a href="#toc-5">Subsection 2.1</a></li>
                </ul>
            </li>
        </ul>
    </li>
</ul>
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
