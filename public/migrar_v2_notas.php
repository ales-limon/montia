<?php
/**
 * Migración v2 - Notas en Compartidos
 * Añade la columna notas_emisor a la tabla enlaces_compartidos
 */

require_once __DIR__ . '/../config/config.php';

// Para registrar la ejecución en el panel
function registrarEjecucion($pdo, $file) {
    $stmt = $pdo->prepare("INSERT INTO configuracion (c_key, c_value, c_label) VALUES (?, ?, ?) 
                          ON DUPLICATE KEY UPDATE c_value = VALUES(c_value)");
    $stmt->execute(['last_run_' . $file, date('Y-m-d H:i:s'), 'Ejecución de ' . $file]);
}

echo "<h2>Ejecutando Migración v2: Notas en Compartidos</h2>";

try {
    // Aplicar el parche de la columna
    $pdo->exec("ALTER TABLE enlaces_compartidos ADD COLUMN notas_emisor TEXT AFTER id_receptor");
    echo "<p style='color: green;'>✅ ÉXITO: Columna 'notas_emisor' añadida correctamente.</p>";

    registrarEjecucion($pdo, basename(__FILE__));

    echo "<h3>Proceso completado.</h3>";
    echo "<p>Ya puedes borrar este archivo desde el panel de control.</p>";
    echo "<a href='admin.php'>Volver al Dashboard</a>";

} catch (PDOException $e) {
    if ($e->getCode() == '42S21') { // Columna ya existe
        echo "<p style='color: orange;'>ℹ️ La migración ya había sido aplicada anteriormente.</p>";
        registrarEjecucion($pdo, basename(__FILE__));
    } else {
        echo "<p style='color: red;'>❌ ERROR: " . $e->getMessage() . "</p>";
    }
    echo "<a href='admin.php'>Volver al Dashboard</a>";
}
