<?php
// Funções partilhadas pelas áreas de administrador e mentor (ficheiro novo, não altera o config.php).
require_once __DIR__ . '/../config.php';

function require_mentor(): void {
    require_login();
    if (($_SESSION['user']['role'] ?? '') !== 'mentor') { http_response_code(403); exit('Acesso reservado a mentores.'); }
    $st = db()->prepare('SELECT account_status FROM users WHERE id=?');
    $st->execute([(int)$_SESSION['user']['id']]);
    if ($st->fetchColumn() !== 'approved') {
        unset($_SESSION['user']);
        flash('A tua conta de mentor não está aprovada pela administração.', 'error');
        header('Location: login.php'); exit;
    }
}

function kz($valor): string { return number_format((float)$valor, 0, ',', '.') . ' Kz'; }

// Barra de caminhos (separadores) de cada perfil.
function perfil_nav(string $perfil, string $ativo): void {
    static $css = false;
    $menus = [
        'admin' => [
            ['admin_inicio.php', 'Início'],
            ['admin.php', 'Candidaturas e pedidos'],
            ['admin_bolsas.php', 'Bolsas'],
            ['admin_servicos.php', 'Serviços de mentoria'],
            ['admin_utilizadores.php', 'Utilizadores'],
        ],
        'mentor' => [
            ['mentor.php', 'Início'],
            ['mentor_servicos.php', 'Os meus serviços'],
            ['mentor_pedidos.php', 'Pedidos recebidos'],
        ],
    ];
    if (!$css) {
        echo '<style>.perfil-nav{display:flex;gap:6px;flex-wrap:wrap;margin:4px 0 24px;padding:6px;background:#fff;border:1px solid var(--line);border-radius:14px}.perfil-nav a{padding:9px 14px;border-radius:9px;font-size:14px;font-weight:700;color:var(--muted)}.perfil-nav a:hover{color:var(--green)}.perfil-nav a.ativo{background:var(--mint);color:var(--green2)}.caminhos{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;margin:8px 0 28px}.caminhos .feature .text-link{display:inline-block;margin-top:12px}.form-grid .field.full{grid-column:1/-1}.form-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}.form-actions .btn-primary{width:auto}.card.form-wide{margin-bottom:28px}</style>';
        $css = true;
    }
    echo '<nav class="perfil-nav" aria-label="Caminhos de ' . e($perfil) . '">';
    foreach ($menus[$perfil] as [$url, $rotulo]) {
        echo '<a href="' . e($url) . '"' . ($url === $ativo ? ' class="ativo" aria-current="page"' : '') . '>' . e($rotulo) . '</a>';
    }
    echo '</nav>';
}

// Lê e valida os campos de um serviço de mentoria enviados por POST.
function servico_ler_post(): array {
    $d = [
        'title' => trim($_POST['title'] ?? ''),
        'category' => trim($_POST['category'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'price' => str_replace(',', '.', trim($_POST['price'] ?? '')),
        'duration_minutes' => (int)($_POST['duration_minutes'] ?? 0),
        'delivery' => $_POST['delivery'] ?? '',
        'status' => $_POST['status'] ?? '',
    ];
    $erro = '';
    if ($d['title'] === '' || $d['category'] === '' || $d['description'] === '') $erro = 'Preenche o título, a categoria e a descrição.';
    elseif (!is_numeric($d['price']) || (float)$d['price'] < 0) $erro = 'Indica um preço válido em Kz.';
    elseif ($d['duration_minutes'] < 15 || $d['duration_minutes'] > 480) $erro = 'A duração deve estar entre 15 e 480 minutos.';
    elseif (!in_array($d['delivery'], ['Online', 'Presencial', 'Online e presencial'], true)) $erro = 'Escolhe a modalidade.';
    elseif (!in_array($d['status'], ['published', 'draft'], true)) $erro = 'Escolhe o estado do serviço.';
    return [$d, $erro];
}

// Campos do formulário de serviço (reutilizado por mentor e administrador).
function servico_campos(array $v): void { ?>
    <div class="form-grid">
        <div class="field full"><label for="title">Título</label><input id="title" name="title" required maxlength="180" value="<?=e($v['title'] ?? '')?>"></div>
        <div class="field"><label for="category">Categoria</label><input id="category" name="category" required maxlength="100" placeholder="Ex.: Escrita académica" value="<?=e($v['category'] ?? '')?>"></div>
        <div class="field"><label for="delivery">Modalidade</label><select id="delivery" name="delivery"><?php foreach (['Online', 'Presencial', 'Online e presencial'] as $o): ?><option <?=($v['delivery'] ?? 'Online') === $o ? 'selected' : ''?>><?=e($o)?></option><?php endforeach; ?></select></div>
        <div class="field"><label for="price">Preço (Kz)</label><input id="price" name="price" required inputmode="decimal" value="<?=e($v['price'] ?? '')?>"></div>
        <div class="field"><label for="duration_minutes">Duração (minutos)</label><input id="duration_minutes" name="duration_minutes" type="number" min="15" max="480" required value="<?=e($v['duration_minutes'] ?? 60)?>"></div>
        <div class="field full"><label for="description">Descrição</label><textarea id="description" name="description" required><?=e($v['description'] ?? '')?></textarea></div>
        <div class="field"><label for="status">Estado</label><select id="status" name="status"><option value="published" <?=($v['status'] ?? 'published') === 'published' ? 'selected' : ''?>>Publicado</option><option value="draft" <?=($v['status'] ?? '') === 'draft' ? 'selected' : ''?>>Rascunho</option></select></div>
    </div>
<?php }
