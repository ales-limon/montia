<?php
/**
 * Migración v4 - Limpieza de Títulos con Stats en Ruso/Extranjero
 * Elimina el prefijo de engagement de Facebook/Meta del og:title
 * Ej: "1 млн просмотров - 28 тыс. реакций | Título real" → "Título real"
 */

require_once __DIR__ . '/../config/config.php';

echo "<h2>Migración v4: Limpieza de títulos con texto extranjero</h2>";

try {
    // Obtener todos los enlaces con " | " en el título
    $stmt = $pdo->query("SELECT id, titulo FROM enlaces WHERE titulo LIKE '%|%'");
    $enlaces = $stmt->fetchAll();

    $total     = count($enlaces);
    $actualizados = 0;

    echo "<p>Encontrados <strong>{$total}</strong> enlaces con ' | ' en el título. Procesando...</p>";
    echo "<ul>";

    foreach ($enlaces as $enlace) {
        $tituloOriginal = $enlace['titulo'];
        $tituloParts    = explode(' | ', $tituloOriginal);
        $tituloLimpio   = trim(end($tituloParts));

        // Solo actualizar si hay diferencia real y el resultado tiene sentido
        if ($tituloLimpio !== $tituloOriginal && strlen($tituloLimpio) > 3) {
            $upd = $pdo->prepare("UPDATE enlaces SET titulo = ? WHERE id = ?");
            $upd->execute([$tituloLimpio, $enlace['id']]);
            echo "<li style='color:green;'>✅ #{$enlace['id']}: <em>" . htmlspecialchars($tituloOriginal) . "</em> → <strong>" . htmlspecialchars($tituloLimpio) . "</strong></li>";
            $actualizados++;
        }
    }

    echo "</ul>";
    echo "<p><strong>{$actualizados}</strong> de {$total} títulos actualizados.</p>";

    $stmt = $pdo->prepare("INSERT INTO configuracion (c_key, c_value, c_label) VALUES (?, ?, ?)
                          ON DUPLICATE KEY UPDATE c_value = VALUES(c_value)");
    $stmt->execute(['last_run_migrar_v4_limpiar_titulos.php', date('Y-m-d H:i:s'), 'Limpieza de títulos con texto extranjero']);

    echo "<h3>Proceso completado.</h3>";
    echo "<a href='panel.php'>Volver al Panel</a>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<a href='panel.php'>Volver al Panel</a>";
}
