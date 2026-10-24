<?php
require_once 'config.php'; require_once 'includes/perfis.php';
require_admin();
$pdo = db();
$n = [
    'pend' => $pdo->query("SELECT COUNT(*) FROM candidatura WHERE status IN ('submitted','reviewing')")->fetchColumn(),
    'ped' => $pdo->query("SELECT COUNT(*) FROM marcacao_mentoria WHERE status='requested'")->fetchColumn(),
    'bolsas' => $pdo->query("SELECT COUNT(*) FROM bolsa WHERE status='published'")->fetchColumn(),
    'serv' => $pdo->query("SELECT COUNT(*) FROM servico_mentoria WHERE status='published'")->fetchColumn(),
    'ment' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='mentor' AND account_status='approved'")->fetchColumn(),
    'ment_pend' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='mentor' AND account_status='pending'")->fetchColumn(),
];
$page_title = 'Administração | Kuntima';
require 'includes/header.php'; ?>
<div class="page-heading"><span class="eyebrow" style="color:var(--green)">Gestão da plataforma</span><h1>Painel do administrador</h1><p>Escolhe para onde queres ir.</p></div>
<?php perfil_nav('admin', 'admin_inicio.php'); ?>
<?php if ($n['ment_pend'] > 0): ?><div class="alert error" style="background:#fff4dc;color:#92600b"><?=$n['ment_pend']?> mentor(es) a aguardar aprovação. <a class="text-link" style="color:inherit;text-decoration:underline" href="admin_utilizadores.php?f=pendentes">Ver pedidos →</a></div><?php endif; ?>
<div class="caminhos">
    <div class="feature"><div class="feature-icon">📄</div><h3>Candidaturas e pedidos</h3><p><?=$n['pend']?> candidatura(s) por tratar e <?=$n['ped']?> pedido(s) de mentoria pendente(s).</p><a class="text-link" href="admin.php">Abrir →</a></div>
    <div class="feature"><div class="feature-icon">🎓</div><h3>Bolsas</h3><p><?=$n['bolsas']?> bolsa(s) publicada(s). Cria, edita e fecha oportunidades.</p><a class="text-link" href="admin_bolsas.php">Gerir bolsas →</a></div>
    <div class="feature"><div class="feature-icon">🤝</div><h3>Serviços de mentoria</h3><p><?=$n['serv']?> serviço(s) publicado(s). Atribui serviços a mentores.</p><a class="text-link" href="admin_servicos.php">Gerir serviços →</a></div>
    <div class="feature"><div class="feature-icon">👥</div><h3>Utilizadores</h3><p><?=$n['ment']?> mentor(es) aprovado(s) e <?=$n['ment_pend']?> a aguardar aprovação.</p><a class="text-link" href="admin_utilizadores.php">Gerir utilizadores →</a></div>
</div>
<?php require 'includes/footer.php'; ?>
