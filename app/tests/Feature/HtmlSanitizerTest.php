<?php

use App\Services\Html\HtmlSanitizer;

it('elimina etiquetas de script y atributos on*', function () {
    $sanitizer = app(HtmlSanitizer::class);

    $clean = $sanitizer->clean('<p onclick="alert(1)">Hola</p><script>alert(1)</script>');

    expect($clean)
        ->toContain('<p>Hola</p>')
        ->not->toContain('<script')
        ->not->toContain('onclick');
});

it('elimina iframe, object, embed y form', function () {
    $sanitizer = app(HtmlSanitizer::class);

    $clean = $sanitizer->clean('<iframe src="x"></iframe><object></object><embed><form></form>');

    expect($clean)
        ->not->toContain('<iframe')
        ->not->toContain('<object')
        ->not->toContain('<embed')
        ->not->toContain('<form');
});

it('elimina javascript: en enlaces', function () {
    $sanitizer = app(HtmlSanitizer::class);

    $clean = $sanitizer->clean('<a href="javascript:alert(1)">click</a>');

    expect($clean)->not->toContain('javascript:');
});

it('permite las etiquetas de la lista blanca', function () {
    $sanitizer = app(HtmlSanitizer::class);

    $html = '<p>Texto <strong>fuerte</strong> y <a href="https://dante.edu.py">enlace</a></p>';
    $clean = $sanitizer->clean($html);

    expect($clean)
        ->toContain('<strong>fuerte</strong>')
        ->toContain('href="https://dante.edu.py"');
});
