<?php
require_once 'config.php'; require_once 'includes/perfis.php';
require_admin();
$pdo = db();
$mentores = $pdo->query("SELECT id,name FROM users WHERE role='mentor' ORDER BY name")->fetchAll();
$ids_mentores = array_map('intval', array_column($mentores, 'id'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    [$d, $erro] = servico_ler_post();
    $mid = (int)($_POST['mentor_id'] ?? 0);
    if ($mid !== 0 && !in_array($mid, $ids_mentores, true)) $erro = $erro ?: 'Mentor inválido.';
    if ($erro === '') {
        $args = [$mid ?: null, $d['title'], $d['category'], $d['description'], $d['price'], $d['duration_minutes'], $d['delivery'], $d['status']];
        if ($id > 0) {
            $args[] = $id;
            $pdo->prepare('UPDATE mentorship_services SET mentor_id=?,title=?,category=?,description=?,price=?,duration_minutes=?,delivery=?,status=? WHERE id=?')->execute($args);
            flash('Serviço actualizado.');
        } else {
            $pdo->prepare('INSERT INTO mentorship_services(mentor_id,title,category,description,price,duration_minutes,delivery,status) VALUES(?,?,?,?,?,?,?,?)')->execute($args);
            flash('Serviço criado.');
        }
        header('Location: admin_servicos.php'); exit;
    }
    $form = $d; $form['id'] = $id; $form['mentor_id'] = $mid;
}
if (!isset($form)) {
    $form = ['status' => 'published', 'delivery' => 'Online', 'duration_minutes' => 60];
    if (isset($_GET['editar'])) {
        $st = $pdo->prepare('SELECT * FROM mentorship_services WHERE id=?'); $st->execute([(int)$_GET['editar']]);
        $form = $st->fetch() ?: $form;
    }
}
$lista = $pdo->query('SELECT m.*,u.name AS mentor FROM mentorship_services m LEFT JOIN users u ON u.id=m.mentor_id ORDER BY m.created_at DESC')->fetchAll();
$page_title = 'Serviços de mentoria | Administração';
require 'includes/header.php'; ?>
<div class="page-heading"><span class="eyebrow" style="color:var(--green)">Administração</span><h1>Serviços de mentoria</h1><p>Cria serviços e atribui-os a mentores. Um serviço sem mentor fica à responsabilidade da equipa.</p></div>
<?php perfil_nav('admin', 'admin_servicos.php'); ?>
<section class="card form-wide">
    <h3 style="margin-top:0"><?=!empty($form['id']) ? 'Editar serviço' : 'Novo serviço'?></h3>
    <?php if (!empty($erro)): ?><div class="alert error"><?=e($erro)?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=(int)($form['id'] ?? 0)?>">
        <?php servico_campos($form); ?>
        <div class="field"><label for="mentor_id">Mentor responsável</label><select id="mentor_id" name="mentor_id"><option value="0">Sem mentor atribuído</option><?php foreach ($mentores as $m): ?><option value="<?=(int)$m['id']?>" <?=(int)($form['mentor_id'] ?? 0) === (int)$m['id'] ? 'selected' : ''?>><?=e($m['name'])?></option><?php endforeach; ?></select><?php if (!$mentores): ?><span class="muted">Ainda não há mentores. Define um utilizador como mentor em Utilizadores.</span><?php endif; ?></div>
        <div class="form-actions"><button class="btn-primary" type="submit">Guardar serviço</button><?php if (!empty($form['id'])): ?><a class="btn-small" href="admin_servicos.php">Cancelar edição</a><?php endif; ?></div>
    </form>
</section>
<section class="section" style="padding-top:0"><div class="section-head"><div><h2>Todos os serviços</h2></div></div>
<?php if ($lista): ?><div class="table-wrap"><table><thead><tr><th>Serviço</th><th>Mentor</th><th>Preço</th><th>Duração</th><th>Estado</th><th></th></tr></thead><tbody>
<?php foreach ($lista as $s): ?><tr><td><?=e($s['title'])?></td><td><?=e($s['mentor'] ?? 'Sem mentor')?></td><td><?=e(kz($s['price']))?></td><td><?=e($s['duration_minutes'])?> min</td><td><span class="status <?=$s['status'] === 'published' ? 'approved' : 'pending'?>"><?=$s['status'] === 'published' ? 'Publicado' : 'Rascunho'?></span></td><td><a class="text-link" href="admin_servicos.php?editar=<?=(int)$s['id']?>#title">Editar</a></td></tr><?php endforeach; ?>
</tbody></table></div><?php else: ?><div class="empty">Ainda não existem serviços.</div><?php endif; ?></section>
<?php require 'includes/footer.php'; ?>
