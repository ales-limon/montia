<?php
/**
 * Script de Migración - LinkViewer
 * Ejecuta el schema.sql y aplica parches necesarios
 */

require_once __DIR__ . '/../config/config.php';

echo "<h2>Iniciando Migración...</h2>";

try {
    // 1. Ejecutar el archivo schema.sql
    $sqlFile = __DIR__ . '/../database/schema.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Archivo schema.sql ejecutado correctamente (Tablas creadas/verificadas).</p>";
    } else {
        echo "<p style='color: red;'>❌ No se encontró el archivo database/schema.sql</p>";
    }

    // 2. Parche específico para la columna titulo (VARCHAR -> TEXT)
    // Esto es necesario porque CREATE TABLE IF NOT EXISTS no modifica tablas existentes
    echo "<p>Aplicando parches de estructura...</p>";
    $pdo->exec("ALTER TABLE enlaces MODIFY COLUMN titulo TEXT");
    echo "<p style='color: green;'>✅ Columna 'titulo' actualizada a TEXT con éxito.</p>";

    // Parche para notas en compartidos
    try {
        $pdo->exec("ALTER TABLE enlaces_compartidos ADD COLUMN notas_emisor TEXT AFTER id_receptor");
        echo "<p style='color: green;'>✅ Columna 'notas_emisor' añadida con éxito.</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>ℹ️ La columna 'notas_emisor' ya existe (sin cambios).</p>";
    }

    // 3. Registrar ejecución en la configuración para el panel
    $stmt = $pdo->prepare("INSERT INTO configuracion (c_key, c_value, c_label) VALUES (?, ?, ?) 
                          ON DUPLICATE KEY UPDATE c_value = VALUES(c_value)");
    $stmt->execute(['last_run_migrar.php', date('Y-m-d H:i:s'), 'Última ejecución de migración']);

    echo "<h3>Migración completada con éxito.</h3>";
    echo "<p><b>IMPORTANTE:</b> Por seguridad, elimina este archivo (public/migrar.php) después de usarlo.</p>";
    echo "<a href='index.php'>Volver al Inicio</a>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error durante la migración: " . $e->getMessage() . "</p>";
}
