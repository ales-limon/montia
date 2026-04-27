<?php
/**
 * Llave de Acceso - Blindaje FORJIATO
 */
$key_secreta = "montia2026"; // CAMBIA ESTO POR TU CLAVE

if (isset($_GET['key']) && $_GET['key'] === $key_secreta) {
    // Poner cookie por 30 días
    setcookie('montia_access', hash('sha256', $key_secreta), time() + (86400 * 30), "/");
    header("Location: index.php");
    exit;
}

if (isset($_GET['salir'])) {
    setcookie('montia_access', '', time() - 3600, "/");
    echo "<h1>Acceso cerrado.</h1><a href='index.php'>Volver</a>";
    exit;
}

echo "<h1>Blindaje Activo</h1><p>Usa la URL con tu clave para entrar.</p>";
