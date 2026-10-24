<?php
require_once 'config.php'; require_once 'includes/perfis.php';
require_admin();
$pdo = db();
$perfis = ['student' => 'Estudante', 'mentor' => 'Mentor', 'admin' => 'Administrador'];
$estados = ['approved' => ['Aprovado', 'approved'], 'pending' => ['A aguardar', 'pending'], 'rejected' => ['Rejeitado', 'rejected']];
$eu = (int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $acao = $_POST['acao'] ?? 'perfil';
    $id = (int)($_POST['id'] ?? 0);
    if ($acao === 'aprovacao') {
        $decisao = $_POST['decisao'] ?? '';
        if (!in_array($decisao, ['approved', 'rejected'], true)) flash('Decisão inválida.', 'error');
        else {
            $st = $pdo->prepare("UPDATE users SET account_status=? WHERE id=? AND role='mentor'");
            $st->execute([$decisao, $id]);
            if ($decisao === 'rejected') $pdo->prepare("UPDATE servico_mentoria SET status='draft' WHERE mentor_id=?")->execute([$id]);
            flash($decisao === 'approved' ? 'Mentor aprovado. Já pode entrar e gerir os seus serviços.' : 'Mentor rejeitado. Os serviços dele deixaram de estar publicados.');
        }
    } else {
        $role = $_POST['role'] ?? '';
        if ($id === $eu) flash('Não podes alterar o teu próprio perfil.', 'error');
        elseif (!isset($perfis[$role])) flash('Perfil inválido.', 'error');
        else {
            $pdo->prepare("UPDATE users SET role=?, account_status='approved' WHERE id=?")->execute([$role, $id]);
            flash('Perfil actualizado. A pessoa deve terminar a sessão e entrar de novo para ver o novo menu.');
        }
    }
    header('Location: admin_utilizadores.php' . (!empty($_POST['f']) ? '?f=' . urlencode($_POST['f']) : '')); exit;
}
$filtro = $_GET['f'] ?? '';
$sql = 'SELECT id,name,email,phone,role,account_status,created_at FROM users';
$args = [];
if ($filtro === 'pendentes') { $sql .= " WHERE role='mentor' AND account_status='pending'"; }
elseif (isset($perfis[$filtro])) { $sql .= ' WHERE role=?'; $args[] = $filtro; }
else { $filtro = ''; }
$st = $pdo->prepare($sql . ' ORDER BY (account_status=\'pending\') DESC, created_at DESC'); $st->execute($args);
$lista = $st->fetchAll();
$page_title = 'Utilizadores | Administração';
require 'includes/header.php'; ?>
<div class="page-heading"><span class="eyebrow" style="color:var(--green)">Administração</span><h1>Utilizadores</h1><p>Os mentores registam-se sozinhos mas só podem entrar depois de aprovados por ti.</p></div>
<?php perfil_nav('admin', 'admin_utilizadores.php'); ?>
<form class="filters" method="get"><select name="f" aria-label="Filtrar"><option value="">Todos os perfis</option><option value="pendentes" <?=$filtro === 'pendentes' ? 'selected' : ''?>>Mentores a aguardar aprovação</option><?php foreach ($perfis as $k => $r): ?><option value="<?=e($k)?>" <?=$filtro === $k ? 'selected' : ''?>><?=e($r)?></option><?php endforeach; ?></select><button class="btn-primary" type="submit">Filtrar</button></form>
<?php if ($lista): ?><div class="table-wrap"><table><thead><tr><th>Nome</th><th>Email</th><th>Telefone</th><th>Registo</th><th>Perfil</th><th>Aprovação</th></tr></thead><tbody>
<?php foreach ($lista as $u): $e = $estados[$u['account_status']] ?? $estados['approved']; ?><tr><td><?=e($u['name'])?></td><td><?=e($u['email'])?></td><td><?=e($u['phone'] ?? '—')?></td><td><?=e(date('d/m/Y', strtotime($u['created_at'])))?></td>
<td><?php if ((int)$u['id'] === $eu): ?><span class="status approved">Tu · <?=e($perfis[$u['role']])?></span><?php else: ?><form method="post" class="inline-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="acao" value="perfil"><input type="hidden" name="id" value="<?=(int)$u['id']?>"><input type="hidden" name="f" value="<?=e($filtro)?>"><select name="role" aria-label="Perfil de <?=e($u['name'])?>"><?php foreach ($perfis as $k => $r): ?><option value="<?=e($k)?>" <?=$u['role'] === $k ? 'selected' : ''?>><?=e($r)?></option><?php endforeach; ?></select> <button class="btn-small">Guardar</button></form><?php endif; ?></td>
<td><?php if ($u['role'] !== 'mentor'): ?>—<?php else: ?><span class="status <?=e($e[1])?>"><?=e($e[0])?></span>
<?php if ($u['account_status'] !== 'approved'): ?><form method="post" class="inline-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="acao" value="aprovacao"><input type="hidden" name="id" value="<?=(int)$u['id']?>"><input type="hidden" name="f" value="<?=e($filtro)?>"><button class="btn-small" name="decisao" value="approved">Aprovar</button></form><?php endif; ?>
<?php if ($u['account_status'] !== 'rejected'): ?><form method="post" class="inline-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="acao" value="aprovacao"><input type="hidden" name="id" value="<?=(int)$u['id']?>"><input type="hidden" name="f" value="<?=e($filtro)?>"><button class="btn-small" name="decisao" value="rejected" data-confirm="Rejeitar este mentor? Os serviços dele deixam de estar publicados.">Rejeitar</button></form><?php endif; ?><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php else: ?><div class="empty"><?=$filtro === 'pendentes' ? 'Não há mentores a aguardar aprovação.' : 'Nenhum utilizador encontrado.'?></div><?php endif; ?>
<?php require 'includes/footer.php'; ?>
