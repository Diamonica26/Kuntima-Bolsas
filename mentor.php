<?php
require_once 'config.php'; require_once 'includes/perfis.php';
require_mentor();
$pdo = db(); $uid = (int)$_SESSION['user']['id'];
$q = function (string $sql) use ($pdo, $uid) { $st = $pdo->prepare($sql); $st->execute([$uid]); return $st->fetchColumn(); };
$servicos = $q("SELECT COUNT(*) FROM mentorship_services WHERE mentor_id=?");
$pendentes = $q("SELECT COUNT(*) FROM mentorship_bookings b JOIN mentorship_services m ON m.id=b.service_id WHERE m.mentor_id=? AND b.status='requested'");
$confirmadas = $q("SELECT COUNT(*) FROM mentorship_bookings b JOIN mentorship_services m ON m.id=b.service_id WHERE m.mentor_id=? AND b.status='confirmed'");
$page_title = 'Painel do mentor | Kuntima';
require 'includes/header.php'; ?>
<div class="page-heading"><span class="eyebrow" style="color:var(--green)">Área do mentor</span><h1>Olá, <?=e(explode(' ', $_SESSION['user']['name'])[0])?></h1><p>Gere os teus serviços e os pedidos dos estudantes.</p></div>
<?php perfil_nav('mentor', 'mentor.php'); ?>
<div class="dashboard-stats"><div class="stat"><span>Os meus serviços</span><strong><?=$servicos?></strong></div><div class="stat"><span>Pedidos pendentes</span><strong><?=$pendentes?></strong></div><div class="stat"><span>Sessões confirmadas</span><strong><?=$confirmadas?></strong></div></div>
<div class="caminhos">
    <div class="feature"><div class="feature-icon">🧰</div><h3>Os meus serviços</h3><p>Cria e edita os serviços de orientação que ofereces e define o preço.</p><a class="text-link" href="mentor_servicos.php">Gerir serviços →</a></div>
    <div class="feature"><div class="feature-icon">📥</div><h3>Pedidos recebidos</h3><p>Confirma, conclui ou cancela as sessões pedidas pelos estudantes.</p><a class="text-link" href="mentor_pedidos.php">Ver pedidos →</a></div>
    <div class="feature"><div class="feature-icon">🌐</div><h3>Vista pública</h3><p>Vê como os teus serviços aparecem aos estudantes.</p><a class="text-link" href="mentoria.php">Abrir mentoria →</a></div>
</div>
<?php require 'includes/footer.php'; ?>
