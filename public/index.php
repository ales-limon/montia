<?php
/**
 * LinkViewer - Entry Point
 * Estándar FORJIATO
 */

require_once __DIR__ . '/../config/config.php';

if (getTenantId()) {
    header("Location: panel.php");
} else {
    header("Location: login.php");
}
exit();
