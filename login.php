<?php
require_once 'config.php';
if (logged_in()) { header('Location: acesso.php'); exit; }
$page_title = 'Entrar | Kuntima';
$perfis = ['student' => ['Aluno', '🎓'], 'mentor' => ['Mentor', '🤝'], 'admin' => ['Administrador', '🛠️']];
$perfil = $_POST['perfil'] ?? $_GET['perfil'] ?? 'student';
if (!isset($perfis[$perfil])) $perfil = 'student';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $st = db()->prepare('SELECT id,name,email,password_hash,role,account_status FROM users WHERE email=?');
    $st->execute([$email]);
    $u = $st->fetch();
    if (!$u || !password_verify($password, $u['password_hash'])) {
        $error = 'Email ou palavra-passe incorrectos.';
    } elseif ($u['role'] !== $perfil) {
        $error = 'Esta conta não é de ' . mb_strtolower($perfis[$perfil][0]) . '. Escolhe o perfil correcto e tenta de novo.';
    } elseif ($u['account_status'] === 'pending') {
        $error = 'A tua conta de mentor aguarda aprovação da administração. Voltas a poder entrar assim que for aprovada.';
    } elseif ($u['account_status'] === 'rejected') {
        $error = 'O pedido de conta de mentor não foi aprovado. Contacta apoio@kuntima.ao para mais informações.';
    } else {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role']];
        header('Location: acesso.php'); exit;
    }
}
require 'includes/header.php'; ?>
<style>.perfil-opcoes{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin:18px 0 4px}.perfil-opcoes label{cursor:pointer}.perfil-opcoes input{position:absolute;opacity:0;pointer-events:none}.perfil-opcoes span{display:flex;flex-direction:column;align-items:center;gap:4px;padding:14px 8px;border:1px solid #d8e2e8;border-radius:12px;background:#fff;font-size:13px;font-weight:700;text-align:center;color:var(--muted)}.perfil-opcoes span b{font-size:22px;font-weight:400}.perfil-opcoes input:checked+span{border-color:var(--green);background:var(--mint);color:var(--green2);box-shadow:0 0 0 3px #13856b18}.perfil-opcoes input:focus-visible+span{outline:2px solid var(--green);outline-offset:2px}@media(max-width:420px){.perfil-opcoes{grid-template-columns:1fr}}</style>
<section class="form-card"><h1>Bem-vindo de volta</h1><p class="muted">Escolhe como queres entrar.</p>
<?php if ($error): ?><div class="alert error"><?=e($error)?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<fieldset style="border:0;padding:0;margin:0"><legend class="muted" style="padding:0">Entrar como</legend>
<div class="perfil-opcoes"><?php foreach ($perfis as $k => [$rotulo, $icone]): ?><label><input type="radio" name="perfil" value="<?=e($k)?>" <?=$perfil === $k ? 'checked' : ''?>><span><b><?=$icone?></b><?=e($rotulo)?></span></label><?php endforeach; ?></div></fieldset>
<div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required autocomplete="username" value="<?=e($_POST['email'] ?? '')?>"></div>
<div class="field"><label for="password">Palavra-passe</label><input id="password" name="password" type="password" required autocomplete="current-password"></div>
<button class="btn-primary" type="submit">Entrar</button></form>
<div class="form-foot">Ainda não tens conta? <a href="registar.php">Criar conta</a></div></section>
<?php require 'includes/footer.php'; ?>
