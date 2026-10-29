<?php
require_once 'config.php'; require_once 'includes/perfis.php';
require_mentor();
$pdo = db(); $uid = (int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    [$d, $erro] = servico_ler_post();
    if ($erro === '') {
        if ($id > 0) {
            $st = $pdo->prepare('UPDATE servico_mentoria SET title=?,category=?,description=?,price=?,duration_minutes=?,delivery=?,status=? WHERE id=? AND mentor_id=?');
            $st->execute([$d['title'], $d['category'], $d['description'], $d['price'], $d['duration_minutes'], $d['delivery'], $d['status'], $id, $uid]);
            flash('Serviço actualizado.');
        } else {
            $pdo->prepare('INSERT INTO servico_mentoria(mentor_id,title,category,description,price,duration_minutes,delivery,status) VALUES(?,?,?,?,?,?,?,?)')
                ->execute([$uid, $d['title'], $d['category'], $d['description'], $d['price'], $d['duration_minutes'], $d['delivery'], $d['status']]);
            flash('Serviço criado.');
        }
        header('Location: mentor_servicos.php'); exit;
    }
    $form = $d; $form['id'] = $id;
}
if (!isset($form)) {
    $form = ['status' => 'published', 'delivery' => 'Online', 'duration_minutes' => 60];
    if (isset($_GET['editar'])) {
        $st = $pdo->prepare('SELECT * FROM servico_mentoria WHERE id=? AND mentor_id=?'); $st->execute([(int)$_GET['editar'], $uid]);
        $form = $st->fetch() ?: $form;
    }
}
$st = $pdo->prepare('SELECT * FROM servico_mentoria WHERE mentor_id=? ORDER BY created_at DESC'); $st->execute([$uid]);
$lista = $st->fetchAll();
$page_title = 'Os meus serviços | Mentor';
require_once __DIR__ . '/header.php'; ?>
<div class="page-heading"><span class="eyebrow" style="color:var(--green)">Área do mentor</span><h1>Os meus serviços</h1><p>Só os serviços publicados aparecem para os estudantes.</p></div>
<?php perfil_nav('mentor', 'mentor_servicos.php'); ?>
<section class="card form-wide">
    <h3 style="margin-top:0"><?=!empty($form['id']) ? 'Editar serviço' : 'Novo serviço'?></h3>
    <?php if (!empty($erro)): ?><div class="alert error"><?=e($erro)?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=(int)($form['id'] ?? 0)?>">
        <?php servico_campos($form); ?>
        <div class="form-actions"><button class="btn-primary" type="submit">Guardar serviço</button><?php if (!empty($form['id'])): ?><a class="btn-small" href="mentor_servicos.php">Cancelar edição</a><?php endif; ?></div>
    </form>
</section>
<section class="section" style="padding-top:0"><div class="section-head"><div><h2>Serviços criados</h2></div></div>
<?php if ($lista): ?><div class="table-wrap"><table><thead><tr><th>Serviço</th><th>Categoria</th><th>Preço</th><th>Duração</th><th>Estado</th><th></th></tr></thead><tbody>
<?php foreach ($lista as $s): ?><tr><td><?=e($s['title'])?></td><td><?=e($s['category'])?></td><td><?=e(kz($s['price']))?></td><td><?=e($s['duration_minutes'])?> min</td><td><span class="status <?=$s['status'] === 'published' ? 'approved' : 'pending'?>"><?=$s['status'] === 'published' ? 'Publicado' : 'Rascunho'?></span></td><td><a class="text-link" href="mentor_servicos.php?editar=<?=(int)$s['id']?>#title">Editar</a></td></tr><?php endforeach; ?>
</tbody></table></div><?php else: ?><div class="empty">Ainda não criaste serviços. Usa o formulário acima.</div><?php endif; ?></section>
<?php <?php require_once __DIR__ . '/footer.php'; ?>
