<?php
require_once __DIR__ . '/config/database.php';
 
$contrasena = 'Admin1234';
$hash = password_hash($contrasena, PASSWORD_BCRYPT);
 
$pdo  = Database::getInstance()->getConnection();
$stmt = $pdo->prepare("UPDATE administrador SET contrasena = :hash WHERE email = 'admin@taller.com'");
$stmt->execute([':hash' => $hash]);
 
echo "<h2 style='color:green'>✅ Contraseña actualizada</h2>";
echo "<p><strong>Email:</strong> admin@taller.com</p>";
echo "<p><strong>Contraseña:</strong> {$contrasena}</p>";
echo "<p><strong>Hash generado:</strong> {$hash}</p>";
echo "<p style='color:red'>⚠️ Eliminá este archivo ahora.</p>";
?>