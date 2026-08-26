import './consent.js';

// Hallazgo real de Fase 9 (QA, docs/11-qa-testing.md §6), encontrado con
// Playwright: la premisa original de este archivo ("Alpine.js llega
// empaquetado con Livewire, no hace falta una copia propia") era falsa en la
// práctica. Livewire es opt-in por plantilla desde la Fase 7
// (docs/09-rendimiento.md §5, `x-layouts.app` con prop `livewire`) — home,
// páginas institucionales, noticias, contacto, calendario, comunicados y
// documentos NO cargan Livewire, así que tampoco cargaban Alpine. Resultado:
// el botón de menú móvil (`x-data`/`@click` en site-header.blade.php) y el
// banner de cookies (`alpine:init` en consent.js) quedaban completamente
// inertes en casi todo el sitio público — el menú no abría en móvil en
// ninguna plantilla salvo el buscador. Se agrega Alpine standalone (mucho
// más liviano que el bundle completo de Livewire) y se arranca solo si
// Livewire no lo hizo ya (evita una segunda instancia/doble inicialización
// en la página del buscador, que sí carga Livewire).
import Alpine from 'alpinejs';

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}

// Motion real (docs/04-ui-design-system.md §5). Nada de esto anima nada si
// `prefers-reduced-motion: reduce` está activo — el CSS ya lo desactiva a
// nivel global (app.css), acá solo evitamos correr el conteo de cifras.
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Scroll reveal — revela `.reveal` cuando entra un 20% en el viewport, una
// sola vez (no vuelve a ocultarse al salir de pantalla). El CSS de app.css
// solo oculta `.reveal` cuando <html> tiene la clase `.js` (agregada de forma
// síncrona en el <head>), así que si este script no llega a correr por algún
// motivo, el contenido nunca queda oculto.
if ('IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver(
        (entries, observer) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            }
        },
        { threshold: 0.2 },
    );

    document.querySelectorAll('.reveal').forEach((el) => revealObserver.observe(el));
} else {
    // Sin soporte de IntersectionObserver: mostrar todo directo, no bloquear contenido.
    document.querySelectorAll('.reveal').forEach((el) => el.classList.add('is-visible'));
}

// Contador de cifras — anima de 0 al valor real cuando el bloque entra en
// viewport. Solo cuenta números enteros (con sufijo opcional, ej. "129°");
// cualquier otro texto ("Afiliados") se muestra directo, sin animar.
if ('IntersectionObserver' in window) {
    const counterObserver = new IntersectionObserver(
        (entries, observer) => {
            for (const entry of entries) {
                if (!entry.isIntersecting) continue;

                observer.unobserve(entry.target);
                animateCounter(entry.target);
            }
        },
        { threshold: 0.4 },
    );

    document.querySelectorAll('.stat-number').forEach((el) => counterObserver.observe(el));
}

function animateCounter(el) {
    const target = el.textContent.trim();
    const match = target.match(/^(\d+)(.*)$/);

    if (!match || prefersReducedMotion) {
        return;
    }

    const [, digits, suffix] = match;
    const end = parseInt(digits, 10);
    const duration = 800;
    const start = performance.now();

    const step = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // ease-out cúbico
        const current = Math.round(end * eased);

        el.textContent = current + suffix;

        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };

    requestAnimationFrame(step);
}
