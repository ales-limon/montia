<?php
/**
 * Migración v3 - Rastreo de Compartidos Externos
 * Añade la tabla para trackear envíos a WhatsApp/Redes Sociales
 */

require_once __DIR__ . '/../config/config.php';

echo "<h2>Ejecutando Migración v3: Rastreo de Compartidos</h2>";

try {
    $sql = "CREATE TABLE IF NOT EXISTS compartidos_externos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_tenant INT NOT NULL,
        id_enlace INT NOT NULL,
        plataforma VARCHAR(50) DEFAULT 'whatsapp',
        destinatario VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_tenant) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (id_enlace) REFERENCES enlaces(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";

    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ ÉXITO: Tabla 'compartidos_externos' creada correctamente.</p>";

    // Registrar ejecución para el panel
    $stmt = $pdo->prepare("INSERT INTO configuracion (c_key, c_value, c_label) VALUES (?, ?, ?) 
                          ON DUPLICATE KEY UPDATE c_value = VALUES(c_value)");
    $stmt->execute(['last_run_migrar_v3_whatsapp.php', date('Y-m-d H:i:s'), 'Ejecución de Rastreo WhatsApp']);

    echo "<h3>Proceso completado.</h3>";
    echo "<a href='admin.php'>Volver al Dashboard</a>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ ERROR: " . $e->getMessage() . "</p>";
    echo "<a href='admin.php'>Volver al Dashboard</a>";
}
