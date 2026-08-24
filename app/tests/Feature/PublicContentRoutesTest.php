<?php

use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\Document;
use App\Models\Gallery;
use App\Models\Post;

it('la home responde 200', function () {
    $this->get('/')->assertOk();
});

it('la página de contacto responde 200 con el formulario', function () {
    $this->get('/contacto')->assertOk()->assertSee('Contacto');
});

it('lista solo noticias publicadas', function () {
    $published = Post::factory()->create(['status' => 'published', 'title' => ['es' => 'Noticia publicada']]);
    Post::factory()->create(['status' => 'draft', 'title' => ['es' => 'Noticia borrador']]);

    $this->get('/noticias')
        ->assertOk()
        ->assertSee('Noticia publicada')
        ->assertDontSee('Noticia borrador');
});

it('muestra el detalle de una noticia publicada y 404 en una en borrador', function () {
    $published = Post::factory()->create(['status' => 'published', 'slug' => 'noticia-real']);
    $draft = Post::factory()->create(['status' => 'draft', 'slug' => 'noticia-borrador']);

    $this->get('/noticias/'.$published->slug)->assertOk();
    $this->get('/noticias/'.$draft->slug)->assertNotFound();
});

it('lista solo documentos publicados y vigentes', function () {
    Document::factory()->create(['status' => 'published', 'is_current' => true, 'title' => ['es' => 'Documento vigente']]);
    Document::factory()->create(['status' => 'published', 'is_current' => false, 'title' => ['es' => 'Documento vencido']]);

    $this->get('/documentos')
        ->assertOk()
        ->assertSee('Documento vigente')
        ->assertDontSee('Documento vencido');
});

it('lista solo comunicados publicados', function () {
    Announcement::factory()->create(['status' => 'published', 'title' => ['es' => 'Comunicado real']]);
    Announcement::factory()->create(['status' => 'draft', 'title' => ['es' => 'Comunicado borrador']]);

    $this->get('/vida-escolar/comunicados')
        ->assertOk()
        ->assertSee('Comunicado real')
        ->assertDontSee('Comunicado borrador');
});

it('lista solo eventos de calendario publicados', function () {
    CalendarEvent::factory()->create(['status' => 'published', 'title' => ['es' => 'Evento real']]);
    CalendarEvent::factory()->create(['status' => 'draft', 'title' => ['es' => 'Evento borrador']]);

    $this->get('/vida-escolar/calendario')
        ->assertOk()
        ->assertSee('Evento real')
        ->assertDontSee('Evento borrador');
});

it('lista solo galerías publicadas', function () {
    Gallery::factory()->create(['status' => 'published', 'title' => ['es' => 'Galería real']]);
    Gallery::factory()->create(['status' => 'draft', 'title' => ['es' => 'Galería borrador']]);

    $this->get('/vida-escolar/galeria')
        ->assertOk()
        ->assertSee('Galería real')
        ->assertDontSee('Galería borrador');
});
