<?php
// Caminho único de entrada: encaminha cada perfil para a sua tela inicial.
require_once 'config.php';
require_login();
$destinos = ['admin' => 'admin_inicio.php', 'mentor' => 'mentor.php', 'student' => 'painel.php'];
header('Location: ' . ($destinos[$_SESSION['user']['role'] ?? ''] ?? 'painel.php'));
exit;
