<?php
require_once 'config.php'; require_once 'includes/perfis.php';
require_admin();
$pdo = db();
$tipos = ['Integral', 'Parcial', 'Propina', 'Investigação'];
$estados = ['published' => 'Publicada', 'draft' => 'Rascunho', 'closed' => 'Encerrada'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $acao = $_POST['acao'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($acao === 'estado' && isset($estados[$_POST['status'] ?? ''])) {
        $pdo->prepare('UPDATE bolsa SET status=? WHERE id=?')->execute([$_POST['status'], $id]);
        flash('Estado da bolsa actualizado.');
        header('Location: admin_bolsas.php'); exit;
    }
    if ($acao === 'guardar') {
        $d = [
            'title' => trim($_POST['title'] ?? ''), 'organization' => trim($_POST['organization'] ?? ''),
            'country' => trim($_POST['country'] ?? '') ?: 'Angola', 'study_level' => trim($_POST['study_level'] ?? ''),
            'field' => trim($_POST['field'] ?? ''), 'funding_type' => $_POST['funding_type'] ?? '',
            'deadline' => trim($_POST['deadline'] ?? ''), 'description' => trim($_POST['description'] ?? ''),
            'requirements' => trim($_POST['requirements'] ?? ''), 'application_url' => trim($_POST['application_url'] ?? ''),
            'status' => $_POST['status'] ?? '',
        ];
        $erro = '';
        $dt = $d['deadline'] !== '' ? DateTime::createFromFormat('Y-m-d', $d['deadline']) : null;
        if ($d['title'] === '' || $d['organization'] === '' || $d['study_level'] === '' || $d['field'] === '' || $d['description'] === '') $erro = 'Preenche título, instituição, nível, área e descrição.';
        elseif (!in_array($d['funding_type'], $tipos, true) || !isset($estados[$d['status']])) $erro = 'Tipo de financiamento ou estado inválido.';
        elseif ($d['deadline'] !== '' && !$dt) $erro = 'Prazo inválido.';
        elseif ($d['application_url'] !== '' && !preg_match('#^https?://#i', $d['application_url'])) $erro = 'O link da candidatura deve começar por http:// ou https://.';
        if ($erro === '') {
            $args = [$d['title'], $d['organization'], $d['country'], $d['study_level'], $d['field'], $d['funding_type'], $d['deadline'] ?: null, $d['description'], $d['requirements'] ?: null, $d['application_url'] ?: null, $d['status']];
            if ($id > 0) {
                $args[] = $id;
                $pdo->prepare('UPDATE bolsa SET title=?,organization=?,country=?,study_level=?,field=?,funding_type=?,deadline=?,description=?,requirements=?,application_url=?,status=? WHERE id=?')->execute($args);
                flash('Bolsa actualizada.');
            } else {
                $pdo->prepare('INSERT INTO bolsa(title,organization,country,study_level,field,funding_type,deadline,description,requirements,application_url,status) VALUES(?,?,?,?,?,?,?,?,?,?,?)')->execute($args);
                flash('Bolsa criada.');
            }
            header('Location: admin_bolsas.php'); exit;
        }
        $form = $d; $form['id'] = $id;
    }
}

if (!isset($form)) {
    $form = ['status' => 'published', 'funding_type' => 'Integral', 'country' => 'Angola'];
    if (isset($_GET['editar'])) {
        $st = $pdo->prepare('SELECT * FROM bolsa WHERE id=?'); $st->execute([(int)$_GET['editar']]);
        $form = $st->fetch() ?: $form;
    }
}
$lista = $pdo->query('SELECT id,title,organization,deadline,status FROM bolsa ORDER BY created_at DESC')->fetchAll();
$page_title = 'Bolsas | Administração';
require 'includes/header.php'; ?>
<div class="page-heading"><span class="eyebrow" style="color:var(--green)">Administração</span><h1>Bolsas de estudo</h1><p>Publica novas oportunidades e controla o que fica visível para os estudantes.</p></div>
<?php perfil_nav('admin', 'admin_bolsas.php'); ?>
<section class="card form-wide">
    <h3 style="margin-top:0"><?=!empty($form['id']) ? 'Editar bolsa' : 'Nova bolsa'?></h3>
    <?php if (!empty($erro)): ?><div class="alert error"><?=e($erro)?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="acao" value="guardar"><input type="hidden" name="id" value="<?=(int)($form['id'] ?? 0)?>">
        <div class="form-grid">
            <div class="field full"><label for="title">Título</label><input id="title" name="title" required maxlength="180" value="<?=e($form['title'] ?? '')?>"></div>
            <div class="field"><label for="organization">Instituição</label><input id="organization" name="organization" required maxlength="160" value="<?=e($form['organization'] ?? '')?>"></div>
            <div class="field"><label for="country">País</label><input id="country" name="country" maxlength="100" value="<?=e($form['country'] ?? 'Angola')?>"></div>
            <div class="field"><label for="study_level">Nível de estudo</label><input id="study_level" name="study_level" required maxlength="100" placeholder="Ex.: Licenciatura" value="<?=e($form['study_level'] ?? '')?>"></div>
            <div class="field"><label for="field">Área</label><input id="field" name="field" required maxlength="120" value="<?=e($form['field'] ?? '')?>"></div>
            <div class="field"><label for="funding_type">Financiamento</label><select id="funding_type" name="funding_type"><?php foreach ($tipos as $t): ?><option <?=($form['funding_type'] ?? '') === $t ? 'selected' : ''?>><?=e($t)?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="deadline">Prazo (opcional)</label><input id="deadline" name="deadline" type="date" value="<?=e($form['deadline'] ?? '')?>"></div>
            <div class="field full"><label for="description">Descrição</label><textarea id="description" name="description" required><?=e($form['description'] ?? '')?></textarea></div>
            <div class="field full"><label for="requirements">Requisitos</label><textarea id="requirements" name="requirements"><?=e($form['requirements'] ?? '')?></textarea></div>
            <div class="field full"><label for="application_url">Link oficial da candidatura (opcional)</label><input id="application_url" name="application_url" maxlength="500" placeholder="https://" value="<?=e($form['application_url'] ?? '')?>"></div>
            <div class="field"><label for="status">Estado</label><select id="status" name="status"><?php foreach ($estados as $k => $r): ?><option value="<?=e($k)?>" <?=($form['status'] ?? '') === $k ? 'selected' : ''?>><?=e($r)?></option><?php endforeach; ?></select></div>
        </div>
        <div class="form-actions"><button class="btn-primary" type="submit">Guardar bolsa</button><?php if (!empty($form['id'])): ?><a class="btn-small" href="admin_bolsas.php">Cancelar edição</a><?php endif; ?></div>
    </form>
</section>
<section class="section" style="padding-top:0"><div class="section-head"><div><h2>Todas as bolsas</h2><p>Bolsas em rascunho ou encerradas não aparecem para os estudantes.</p></div></div>
<?php if ($lista): ?><div class="table-wrap"><table><thead><tr><th>Bolsa</th><th>Instituição</th><th>Prazo</th><th>Estado</th><th>Acções</th></tr></thead><tbody>
<?php foreach ($lista as $b): ?><tr><td><?=e($b['title'])?></td><td><?=e($b['organization'])?></td><td><?=$b['deadline'] ? e(date('d/m/Y', strtotime($b['deadline']))) : 'A confirmar'?></td>
<td><form method="post" class="inline-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="acao" value="estado"><input type="hidden" name="id" value="<?=(int)$b['id']?>"><select name="status" aria-label="Estado da bolsa"><?php foreach ($estados as $k => $r): ?><option value="<?=e($k)?>" <?=$b['status'] === $k ? 'selected' : ''?>><?=e($r)?></option><?php endforeach; ?></select> <button class="btn-small">Guardar</button></form></td>
<td><a class="text-link" href="admin_bolsas.php?editar=<?=(int)$b['id']?>#title">Editar</a> <?php if ($b["status"] === "published"): ?>· <a class="text-link" href="bolsa.php?id=<?=(int)$b["id"]?>">Ver</a><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php else: ?><div class="empty">Ainda não existem bolsas.</div><?php endif; ?></section>
<?php require 'includes/footer.php'; ?>
