-- =====================================================================
-- Inventario del WordPress de Dante
-- Se corre contra la base LEGACY (dante_wp_legacy), con usuario de SOLO LECTURA.
-- Ajustar el prefijo `wp_` si la instalación usa otro.
--
-- Uso:
--   mysql -u lector -p dante_wp_legacy < scripts/wp-inventario.sql > docs/inventario-crudo.txt
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Resumen por tipo y estado
-- ---------------------------------------------------------------------
SELECT '=== 1. CONTENIDO POR TIPO Y ESTADO ===' AS seccion;

SELECT post_type, post_status, COUNT(*) AS cantidad
FROM wp_posts
GROUP BY post_type, post_status
ORDER BY cantidad DESC;

-- ---------------------------------------------------------------------
-- 2. Páginas publicadas, con volumen de texto
-- ---------------------------------------------------------------------
SELECT '=== 2. PAGINAS PUBLICADAS ===' AS seccion;

SELECT
    ID,
    post_name AS slug,
    post_title AS titulo,
    post_parent AS padre,
    ROUND(LENGTH(post_content) / 6) AS palabras_aprox,
    LENGTH(post_content) AS bytes,
    post_modified AS ultima_edicion
FROM wp_posts
WHERE post_type = 'page' AND post_status = 'publish'
ORDER BY post_parent, post_title;

-- ---------------------------------------------------------------------
-- 3. Entradas publicadas
-- ---------------------------------------------------------------------
SELECT '=== 3. ENTRADAS PUBLICADAS ===' AS seccion;

SELECT
    ID,
    post_name AS slug,
    post_title AS titulo,
    post_date AS fecha,
    ROUND(LENGTH(post_content) / 6) AS palabras_aprox
FROM wp_posts
WHERE post_type = 'post' AND post_status = 'publish'
ORDER BY post_date DESC;

-- ---------------------------------------------------------------------
-- 4. Tipos de contenido personalizados
-- ---------------------------------------------------------------------
SELECT '=== 4. TIPOS DE CONTENIDO PERSONALIZADOS ===' AS seccion;

SELECT post_type, COUNT(*) AS cantidad
FROM wp_posts
WHERE post_type NOT IN ('post','page','attachment','revision','nav_menu_item',
                        'customize_changeset','oembed_cache','user_request',
                        'wp_block','wp_template','wp_global_styles')
GROUP BY post_type;

-- ---------------------------------------------------------------------
-- 5. Taxonomías y términos
-- ---------------------------------------------------------------------
SELECT '=== 5. TAXONOMIAS ===' AS seccion;

SELECT tt.taxonomy, COUNT(*) AS terminos
FROM wp_term_taxonomy tt
GROUP BY tt.taxonomy;

SELECT '=== 5b. TERMINOS CON MAS CONTENIDO ===' AS seccion;

SELECT t.name AS termino, tt.taxonomy, tt.count AS contenidos
FROM wp_terms t
JOIN wp_term_taxonomy tt ON tt.term_id = t.term_id
WHERE tt.count > 0
ORDER BY tt.count DESC;

-- ---------------------------------------------------------------------
-- 6. Medios: cantidad y tipos
-- ---------------------------------------------------------------------
SELECT '=== 6. MEDIOS POR TIPO MIME ===' AS seccion;

SELECT post_mime_type AS tipo, COUNT(*) AS cantidad
FROM wp_posts
WHERE post_type = 'attachment'
GROUP BY post_mime_type
ORDER BY cantidad DESC;

-- ⚠️ Todo tipo MIME que no sea imagen, PDF, documento ofimático o video
--    es sospechoso. Revisarlo antes de migrar.

-- ---------------------------------------------------------------------
-- 7. Menús
-- ---------------------------------------------------------------------
SELECT '=== 7. MENUS ===' AS seccion;

SELECT t.name AS menu, tt.count AS elementos
FROM wp_terms t
JOIN wp_term_taxonomy tt ON tt.term_id = t.term_id
WHERE tt.taxonomy = 'nav_menu';

-- ---------------------------------------------------------------------
-- 8. Usuarios y roles  ⚠️ REVISAR: administradores inesperados
-- ---------------------------------------------------------------------
SELECT '=== 8. USUARIOS (revisar administradores desconocidos) ===' AS seccion;

SELECT
    u.ID,
    u.user_login,
    u.user_email,
    u.user_registered,
    m.meta_value AS capacidades
FROM wp_users u
LEFT JOIN wp_usermeta m
       ON m.user_id = u.ID AND m.meta_key = 'wp_capabilities'
ORDER BY u.user_registered DESC;

-- ---------------------------------------------------------------------
-- 9. Plugins y tema
-- ---------------------------------------------------------------------
SELECT '=== 9. PLUGINS ACTIVOS Y TEMA ===' AS seccion;

SELECT option_name, option_value
FROM wp_options
WHERE option_name IN ('active_plugins','template','stylesheet',
                      'blogname','blogdescription','home','siteurl',
                      'permalink_structure','admin_email');

-- ---------------------------------------------------------------------
-- 10. Rastros de integraciones (analytics, pixel, captcha)
-- ---------------------------------------------------------------------
SELECT '=== 10. INTEGRACIONES DETECTADAS EN OPTIONS ===' AS seccion;

SELECT option_name, LEFT(option_value, 300) AS valor
FROM wp_options
WHERE option_name LIKE '%analytic%'
   OR option_name LIKE '%gtag%'
   OR option_name LIKE '%gtm%'
   OR option_name LIKE '%pixel%'
   OR option_name LIKE '%facebook%'
   OR option_name LIKE '%recaptcha%'
   OR option_name LIKE '%captcha%'
   OR option_name LIKE '%tracking%'
   OR option_name LIKE '%seo%';

-- ---------------------------------------------------------------------
-- 11. INDICADORES DE COMPROMISO  ⚠️
-- ---------------------------------------------------------------------
SELECT '=== 11. CONTENIDO CON SCRIPTS O IFRAMES (revisar uno por uno) ===' AS seccion;

SELECT ID, post_type, post_title, post_modified
FROM wp_posts
WHERE post_status = 'publish'
  AND (post_content LIKE '%<script%'
    OR post_content LIKE '%<iframe%'
    OR post_content LIKE '%eval(%'
    OR post_content LIKE '%base64_decode%'
    OR post_content LIKE '%document.write%');

SELECT '=== 11b. POSIBLE SPAM INYECTADO ===' AS seccion;

SELECT ID, post_type, post_title
FROM wp_posts
WHERE post_status = 'publish'
  AND (post_content LIKE '%viagra%'
    OR post_content LIKE '%casino%'
    OR post_content LIKE '%porn%'
    OR post_content LIKE '%replica%'
    OR post_content LIKE '%display:none%'
    OR post_content LIKE '%position:absolute;left:-9999%');

SELECT '=== 11c. CONTENIDO MODIFICADO EN MASA (posible inyección automatizada) ===' AS seccion;

SELECT DATE(post_modified) AS dia, COUNT(*) AS modificados
FROM wp_posts
WHERE post_status = 'publish'
GROUP BY DATE(post_modified)
HAVING modificados > 10
ORDER BY dia DESC;

-- ---------------------------------------------------------------------
-- 12. Metadatos SEO existentes (Yoast / RankMath)
-- ---------------------------------------------------------------------
SELECT '=== 12. METADATOS SEO ===' AS seccion;

SELECT meta_key, COUNT(*) AS cantidad
FROM wp_postmeta
WHERE meta_key LIKE '_yoast%' OR meta_key LIKE 'rank_math%'
GROUP BY meta_key
ORDER BY cantidad DESC;

-- ---------------------------------------------------------------------
-- 13. Campos personalizados en uso (para no perder datos en la migración)
-- ---------------------------------------------------------------------
SELECT '=== 13. CAMPOS PERSONALIZADOS ===' AS seccion;

SELECT meta_key, COUNT(*) AS usos
FROM wp_postmeta
WHERE meta_key NOT LIKE '\_%'
GROUP BY meta_key
HAVING usos > 3
ORDER BY usos DESC
LIMIT 60;

-- =====================================================================
-- Fin. Volcar los resultados en docs/01-analisis-descubrimiento.md §C.
-- =====================================================================
