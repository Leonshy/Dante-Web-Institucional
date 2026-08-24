// Punto de entrada JS del sitio público. Alpine.js llega empaquetado con
// Livewire (@livewireScripts en resources/views/layouts/app.blade.php) — no
// se suma una copia propia de Alpine para no duplicar el bundle (presupuesto
// de <150KB JS comprimido en carga inicial, CLAUDE.md/docs/06-frontend.md).
//
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
