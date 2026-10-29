<?php
require_once 'config.php';
if (logged_in()) { header('Location: acesso.php'); exit; }
$page_title = 'Criar conta | Kuntima';
// O perfil de administrador nunca pode ser escolhido no registo público.
$perfis = ['student' => ['Aluno', '🎓'], 'mentor' => ['Mentor', '🤝']];
$perfil = $_POST['perfil'] ?? $_GET['perfil'] ?? 'student';
if (!isset($perfis[$perfil])) $perfil = 'student';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Preenche os campos correctamente. A palavra-passe deve ter pelo menos 8 caracteres.';
    } else {
        try {
            $estado = $perfil === 'mentor' ? 'pending' : 'approved';
            $st = db()->prepare('INSERT INTO users(name,email,password_hash,phone,role,account_status) VALUES(?,?,?,?,?,?)');
            $st->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $phone ?: null, $perfil, $estado]);
            if ($estado === 'pending') {
                flash('Conta de mentor criada. Podes entrar assim que a administração aprovar o teu pedido.');
                header('Location: login.php?perfil=mentor'); exit;
            }
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int)db()->lastInsertId(), 'name' => $name, 'email' => $email, 'role' => $perfil];
            flash('Conta criada com sucesso. Bem-vindo/a ao Kuntima!');
            header('Location: acesso.php'); exit;
        } catch (PDOException $ex) {
            $error = $ex->getCode() === '23000' ? 'Este email já está registado.' : 'Não foi possível criar a conta. Verifica a base de dados.';
        }
    }
}
require_once __DIR__ . '/header.php'; ?>
<style>.perfil-opcoes{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin:18px 0 4px}.perfil-opcoes label{cursor:pointer}.perfil-opcoes input{position:absolute;opacity:0;pointer-events:none}.perfil-opcoes span{display:flex;flex-direction:column;align-items:center;gap:4px;padding:14px 8px;border:1px solid #d8e2e8;border-radius:12px;background:#fff;font-size:13px;font-weight:700;text-align:center;color:var(--muted)}.perfil-opcoes span b{font-size:22px;font-weight:400}.perfil-opcoes span small{font-weight:500;font-size:12px}.perfil-opcoes input:checked+span{border-color:var(--green);background:var(--mint);color:var(--green2);box-shadow:0 0 0 3px #13856b18}.perfil-opcoes input:focus-visible+span{outline:2px solid var(--green);outline-offset:2px}@media(max-width:420px){.perfil-opcoes{grid-template-columns:1fr}}</style>
<section class="form-card"><h1>Criar a tua conta</h1><p class="muted">Escolhe o tipo de conta e regista-te.</p>
<?php if ($error): ?><div class="alert error"><?=e($error)?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<fieldset style="border:0;padding:0;margin:0"><legend class="muted" style="padding:0">Quero registar-me como</legend>
<div class="perfil-opcoes">
<label><input type="radio" name="perfil" value="student" <?=$perfil === 'student' ? 'checked' : ''?>><span><b>🎓</b>Aluno<small>Candidatar-me a bolsas e pedir mentoria</small></span></label>
<label><input type="radio" name="perfil" value="mentor" <?=$perfil === 'mentor' ? 'checked' : ''?>><span><b>🤝</b>Mentor<small>Oferecer serviços (requer aprovação)</small></span></label>
</div></fieldset>
<div class="field"><label for="name">Nome completo</label><input id="name" name="name" required maxlength="120" value="<?=e($_POST['name'] ?? '')?>"></div>
<div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required value="<?=e($_POST['email'] ?? '')?>"></div>
<div class="field"><label for="phone">Telefone (opcional)</label><input id="phone" name="phone" value="<?=e($_POST['phone'] ?? '')?>"></div>
<div class="field"><label for="password">Palavra-passe</label><input id="password" name="password" type="password" minlength="8" required autocomplete="new-password"></div>
<button class="btn-primary" type="submit">Criar conta gratuita</button></form>
<div class="form-foot">Já tens conta? <a href="login.php">Entrar</a></div></section>
<?php require 'includes/footer.php'; ?>
