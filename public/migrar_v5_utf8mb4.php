<?php
/**
 * Migración v5 - Convertir tablas a utf8mb4
 * Arregla el error "Incorrect string value" al guardar emojis
 * o caracteres especiales de redes sociales (4 bytes UTF-8)
 */

require_once __DIR__ . '/../config/config.php';

echo "<h2>Migración v5: Conversión a utf8mb4</h2>";

$tablas = ['enlaces', 'categorias', 'usuarios', 'contactos', 'enlaces_compartidos', 'compartidos_externos'];

try {
    foreach ($tablas as $tabla) {
        $pdo->exec("ALTER TABLE `{$tabla}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p style='color:green;'>✅ Tabla <strong>{$tabla}</strong> convertida a utf8mb4.</p>";
    }

    $stmt = $pdo->prepare("INSERT INTO configuracion (c_key, c_value, c_label) VALUES (?, ?, ?)
                          ON DUPLICATE KEY UPDATE c_value = VALUES(c_value)");
    $stmt->execute(['last_run_migrar_v5_utf8mb4.php', date('Y-m-d H:i:s'), 'Conversión a utf8mb4']);

    echo "<h3>Proceso completado. Ya puedes guardar emojis sin errores.</h3>";
    echo "<a href='panel.php'>Volver al Panel</a>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<a href='panel.php'>Volver al Panel</a>";
}
