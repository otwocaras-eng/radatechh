<?php
/**
 * produtos/index.php — Lista de produtos com filtros e paginação
 */
$tituloPagina = $tituloPagina ?? 'Produtos';
$paginaAtiva  = 'produtos';
require_once dirname(__DIR__) . '/layouts/header.php';
?>

<!-- Cabeçalho + botão novo -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h5 fw-700 mb-0">Produtos</h2>
    <small class="text-secondary"><?= number_format($total) ?> produto(s) encontrado(s)</small>
  </div>
  <?php if (ehAdmin()): ?>
  <div class="d-flex gap-2">
    <a href="<?= APP_URL ?>/importacao" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-file-earmark-arrow-up me-1"></i> Importar CSV
    </a>
    <a href="<?= APP_URL ?>/produtos/criar" class="btn btn-primary" id="btn-novo-produto">
      <i class="bi bi-plus-lg me-1"></i> Novo Produto
    </a>
  </div>
  <?php endif; ?>
</div>

<!-- Filtros -->
<div class="card mb-3">
  <div class="card-body py-2">
    <form method="GET" action="<?= APP_URL ?>/produtos" class="row g-2 align-items-end" id="form-filtro-produtos">
      <div class="col-12 col-md-5">
        <input type="text" name="busca" class="form-control form-control-sm"
               placeholder="🔍 Buscar por nome, código de barras ou marca…"
               value="<?= e($filtros['busca']) ?>">
      </div>
      <div class="col-6 col-md-3">
        <select name="categoria_id" class="form-select form-select-sm">
          <option value="">Todas as categorias</option>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['id'] ?>"
              <?= $filtros['categoria_id'] == $cat['id'] ? 'selected' : '' ?>>
              <?= e($cat['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="ativo"   <?= ($filtros['status'] ?? 'ativo') === 'ativo'   ? 'selected' : '' ?>>Ativos</option>
          <option value="inativo" <?= ($filtros['status'] ?? '')       === 'inativo' ? 'selected' : '' ?>>Inativos</option>
          <option value="todos"   <?= ($filtros['status'] ?? '')       === 'todos'   ? 'selected' : '' ?>>Todos</option>
        </select>
      </div>
      <div class="col-12 col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-fill" id="btn-filtrar-produtos">
          <i class="bi bi-search"></i> Filtrar
        </button>
        <a href="<?= APP_URL ?>/produtos" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-x"></i>
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Tabela de produtos -->
<div class="card">
  <div class="table-responsive">
    <table class="table" aria-label="Lista de produtos">
      <thead>
        <tr>
          <th>Produto</th>
          <th>Código</th>
          <th>Categoria</th>
          <th class="text-end">Preço Venda</th>
          <th class="text-center">Estoque</th>
          <th class="text-center">Status</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($produtos)): ?>
          <tr>
            <td colspan="7" class="text-center py-5 text-secondary">
              <i class="bi bi-inbox" style="font-size:2rem;"></i>
              <p class="mt-2 mb-0">Nenhum produto encontrado.</p>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($produtos as $p): ?>
          <tr>
            <td>
              <a href="<?= APP_URL ?>/produtos/ver/<?= $p['id'] ?>"
                 class="fw-600 text-decoration-none text-primary">
                <?= e($p['nome']) ?>
              </a>
              <?php if ($p['marca']): ?>
                <br><small class="text-secondary"><?= e($p['marca']) ?></small>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($p['codigo_barras']): ?>
                <code style="font-size:.78rem;"><?= e($p['codigo_barras']) ?></code>
              <?php else: ?>
                <span class="text-secondary">—</span>
              <?php endif; ?>
            </td>
            <td class="text-secondary" style="font-size:.83rem;">
              <?= e($p['categoria_nome'] ?? '—') ?>
            </td>
            <td class="text-end fw-700">
              <?= moeda((float)$p['preco_venda']) ?>
            </td>
            <td class="text-center">
              <span class="fw-700 <?= $p['estoque_atual'] == 0 ? 'text-danger' : ($p['estoque_atual'] <= $p['estoque_minimo'] ? 'text-warning' : 'text-success') ?>">
                <?= $p['estoque_atual'] ?>
              </span>
              <small class="text-secondary d-block" style="font-size:.7rem;">mín. <?= $p['estoque_minimo'] ?></small>
            </td>
            <td class="text-center">
              <?= badgeEstoque((int)$p['estoque_atual'], (int)$p['estoque_minimo']) ?>
            </td>
            <td class="text-end">
              <a href="<?= APP_URL ?>/produtos/ver/<?= $p['id'] ?>"
                 class="btn btn-sm btn-outline-secondary me-1"
                 title="Ver detalhes" id="btn-ver-prod-<?= $p['id'] ?>">
                <i class="bi bi-eye"></i>
              </a>
              <?php if (ehAdmin()): ?>
              <a href="<?= APP_URL ?>/produtos/editar/<?= $p['id'] ?>"
                 class="btn btn-sm btn-outline-primary me-1"
                 title="Editar" id="btn-editar-prod-<?= $p['id'] ?>">
                <i class="bi bi-pencil"></i>
              </a>
              <?php if ($p['ativo']): ?>
              <form method="POST" action="<?= APP_URL ?>/produtos/inativar/<?= $p['id'] ?>" style="display:inline;">
                <?= camposCsrf() ?>
                <button type="submit"
                        class="btn btn-sm btn-outline-danger"
                        title="Inativar produto"
                        id="btn-inativar-prod-<?= $p['id'] ?>"
                        data-confirmar="Inativar o produto «<?= e($p['nome']) ?>»? O histórico será preservado.">
                  <i class="bi bi-slash-circle"></i>
                </button>
              </form>
              <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Paginação -->
<?php if ($totalPaginas > 1): ?>
<nav class="mt-3" aria-label="Paginação de produtos">
  <ul class="pagination justify-content-center flex-wrap">
    <?php for ($pg = 1; $pg <= $totalPaginas; $pg++): ?>
      <?php
        $params = array_merge($filtros, ['pagina' => $pg]);
        $qs     = http_build_query(array_filter($params));
      ?>
      <li class="page-item <?= $pg === $pagina ? 'active' : '' ?>">
        <a class="page-link" href="<?= APP_URL ?>/produtos?<?= $qs ?>"><?= $pg ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
