<?php
require_once 'config.php'; require_once 'includes/perfis.php';
require_mentor();
$pdo = db(); $uid = (int)$_SESSION['user']['id'];
$estados = ['requested' => 'Pendente', 'confirmed' => 'Confirmada', 'completed' => 'Concluída', 'cancelled' => 'Cancelada'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0); $status = $_POST['status'] ?? '';
    if (isset($estados[$status])) {
        $st = $pdo->prepare('UPDATE marcacao_mentoria SET status=? WHERE id=? AND service_id IN (SELECT id FROM servico_mentoria WHERE mentor_id=?)');
        $st->execute([$status, $id, $uid]);
        flash('Estado do pedido actualizado.');
    }
    header('Location: mentor_pedidos.php'); exit;
}
$st = $pdo->prepare('SELECT b.id,b.status,b.session_date,b.message,u.name AS aluno,u.email,u.phone,m.title FROM marcacao_mentoria b JOIN servico_mentoria m ON m.id=b.service_id JOIN users u ON u.id=b.student_id WHERE m.mentor_id=? ORDER BY b.session_date DESC');
$st->execute([$uid]);
$lista = $st->fetchAll();
$page_title = 'Pedidos recebidos | Mentor';
require_once __DIR__ . '/header.php'; ?>
<div class="page-heading"><span class="eyebrow" style="color:var(--green)">Área do mentor</span><h1>Pedidos recebidos</h1><p>Sessões pedidas pelos estudantes para os teus serviços.</p></div>
<?php perfil_nav('mentor', 'mentor_pedidos.php'); ?>
<?php if ($lista): ?><div class="table-wrap"><table><thead><tr><th>Estudante</th><th>Contacto</th><th>Serviço</th><th>Data pretendida</th><th>Mensagem</th><th>Estado</th></tr></thead><tbody>
<?php foreach ($lista as $b): ?><tr><td><?=e($b['aluno'])?></td><td><?=e($b['email'])?><?=$b['phone'] ? '<br>' . e($b['phone']) : ''?></td><td><?=e($b['title'])?></td><td><?=e(date('d/m/Y H:i', strtotime($b['session_date'])))?></td><td><?=e($b['message'] ?: '—')?></td>
<td><form method="post" class="inline-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=(int)$b['id']?>"><select name="status" aria-label="Estado do pedido"><?php foreach ($estados as $k => $r): ?><option value="<?=e($k)?>" <?=$b['status'] === $k ? 'selected' : ''?>><?=e($r)?></option><?php endforeach; ?></select> <button class="btn-small">Guardar</button></form></td></tr><?php endforeach; ?>
</tbody></table></div><?php else: ?><div class="empty">Ainda não recebeste pedidos. Quando um estudante solicitar um dos teus serviços, aparece aqui.</div><?php endif; ?>
<?php require 'includes/footer.php'; ?>
