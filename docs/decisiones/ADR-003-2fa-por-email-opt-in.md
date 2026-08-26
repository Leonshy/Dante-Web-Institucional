# ADR-003 — 2FA por email, opt-in por usuario

**Estado:** aceptada
**Fecha:** 2026-08-25
**Fase:** 8 (Seguridad), revisada en Fase 9
**Decide:** Leonardo Chi (webparaguay)

## Contexto

El panel arrancó con 2FA obligatorio vía app autenticadora (TOTP, `Filament\Auth\MultiFactor\App`),
forzado para todo usuario (`isRequired`). El cliente pidió que cada usuario del panel decida si
activa el 2FA o no, y que el segundo factor sea un código enviado por email en vez de una app
autenticadora — el personal del colegio no tiene el hábito de usar ese tipo de apps.

## Opciones consideradas

| Opción | A favor | En contra |
|---|---|---|
| TOTP obligatorio (estado anterior) | Más seguro (independiente del correo, resiste phishing en tiempo real) | Fricción alta, requiere una app que el personal no tiene instalada |
| Email OTP opt-in (elegida) | Sin fricción para quien no lo quiera, código nativo de Filament 5 (`EmailAuthentication`), no depende de una app externa | Más débil que TOTP: si se compromete la casilla de correo, se compromete el 2FA |
| Ofrecer ambos (TOTP o email) a elección | Máxima flexibilidad | Más superficie de código y de soporte para un panel de 4 roles, no se justifica todavía |

## Decisión

2FA por código de un solo uso enviado por email (`Filament\Auth\MultiFactor\Email\EmailAuthentication`,
nativo de Filament 5), **opt-in por usuario** — nadie lo tiene forzado. Mientras un usuario no lo
activó, ve una alerta de seguridad persistente en el panel (`AdminPanelProvider`, render hook
`CONTENT_START`) con un enlace directo a activarlo desde su perfil.

## Motivo

Prioriza la adopción real: un 2FA obligatorio que el personal no sabe configurar termina
generando tickets de soporte o gente bloqueada fuera del panel. Email OTP es sensiblemente mejor
que no tener nada, y es la opción de menor fricción para un equipo no técnico. La alerta
persistente sustituye la obligatoriedad por presión visible y constante, sin bloquear a nadie.

## Consecuencias

Se eliminaron las columnas `app_authentication_secret`/`app_authentication_recovery_codes` de
`users` y se agregó `has_email_authentication` (migración
`2026_08_25_040000_switch_to_email_two_factor_authentication.php`, reversible). El código de un
solo uso vive en sesión (no en la base), expira en 4 minutos (default de Filament), con límite de
2 reenvíos por usuario. **Trade-off aceptado explícitamente:** el 2FA deja de ser independiente
de la casilla de correo del usuario — si esa casilla se compromete, el segundo factor también.
Revertir esto (volver a TOTP obligatorio) es barato: es la misma migración a la inversa y un
cambio de una línea en `AdminPanelProvider`.
