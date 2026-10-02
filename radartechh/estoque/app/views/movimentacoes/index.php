<?php
/**
 * movimentacoes/index.php — Histórico completo de movimentações
 */
$paginaAtiva  = 'movimentacoes';
$tituloPagina = $tituloPagina ?? 'Movimentações';
require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h5 fw-700 mb-0">Histórico de Movimentações</h2>
    <small class="text-secondary"><?= number_format($total) ?> registro(s)</small>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= APP_URL ?>/movimentacoes/entrada" class="btn btn-success btn-sm">
      <i class="bi bi-box-arrow-in-down me-1"></i>Entrada
    </a>
    <a href="<?= APP_URL ?>/movimentacoes/saida" class="btn btn-danger btn-sm">
      <i class="bi bi-box-arrow-up me-1"></i>Saída
    </a>
  </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
  <div class="card-body py-2">
    <form method="GET" action="<?= APP_URL ?>/movimentacoes" class="row g-2 align-items-end">
      <div class="col-6 col-md-2">
        <label class="form-label mb-1" style="font-size:.73rem;">Tipo</label>
        <select name="tipo" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="entrada" <?= ($filtros['tipo'] ?? '') === 'entrada' ? 'selected' : '' ?>>Entradas</option>
          <option value="saida"   <?= ($filtros['tipo'] ?? '') === 'saida'   ? 'selected' : '' ?>>Saídas</option>
        </select>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label mb-1" style="font-size:.73rem;">Data início</label>
        <input type="date" name="data_inicio" class="form-control form-control-sm"
               value="<?= e($filtros['data_inicio'] ?? '') ?>">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label mb-1" style="font-size:.73rem;">Data fim</label>
        <input type="date" name="data_fim" class="form-control form-control-sm"
               value="<?= e($filtros['data_fim'] ?? '') ?>">
      </div>
      <div class="col-6 col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-fill">
          <i class="bi bi-search"></i> Filtrar
        </button>
        <a href="<?= APP_URL ?>/movimentacoes" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-x"></i>
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Tabela -->
<div class="card">
  <div class="table-responsive">
    <table class="table" aria-label="Histórico de movimentações">
      <thead>
        <tr>
          <th>Data</th>
          <th>Produto</th>
          <th>Tipo</th>
          <th class="text-end">Qtd.</th>
          <th>Motivo</th>
          <th>Fornecedor</th>
          <th>Usuário</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($movimentacoes)): ?>
          <tr>
            <td colspan="7" class="text-center py-5 text-secondary">
              <i class="bi bi-inbox" style="font-size:2rem;"></i>
              <p class="mt-2 mb-0">Nenhuma movimentação encontrada.</p>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($movimentacoes as $mov): ?>
          <tr>
            <td class="text-secondary" style="font-size:.8rem; white-space:nowrap;">
              <?= dataHora($mov['criado_em']) ?>
            </td>
            <td>
              <a href="<?= APP_URL ?>/produtos/ver/<?= $mov['produto_id'] ?>"
                 class="fw-600 text-decoration-none" style="font-size:.87rem;">
                <?= e($mov['produto_nome']) ?>
              </a>
              <?php if ($mov['codigo_barras']): ?>
                <br><code style="font-size:.72rem;"><?= e($mov['codigo_barras']) ?></code>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($mov['tipo'] === 'entrada'): ?>
                <span class="badge bg-success">
                  <i class="bi bi-arrow-down me-1"></i>Entrada
                </span>
              <?php else: ?>
                <span class="badge bg-danger">
                  <i class="bi bi-arrow-up me-1"></i>Saída
                </span>
              <?php endif; ?>
            </td>
            <td class="text-end fw-700"><?= $mov['quantidade'] ?></td>
            <td class="text-secondary" style="font-size:.82rem;">
              <?= e($mov['motivo'] ?? '—') ?>
              <?php if ($mov['observacao']): ?>
                <br><small class="text-muted fst-italic"><?= e(substr($mov['observacao'], 0, 40)) ?></small>
              <?php endif; ?>
            </td>
            <td class="text-secondary" style="font-size:.8rem;">
              <?= e($mov['fornecedor_nome'] ?? '—') ?>
            </td>
            <td class="text-secondary" style="font-size:.8rem;">
              <?= e($mov['usuario_nome']) ?>
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
<nav class="mt-3" aria-label="Paginação">
  <ul class="pagination justify-content-center flex-wrap">
    <?php for ($pg = 1; $pg <= $totalPaginas; $pg++): ?>
      <?php
        $params = array_merge($filtros, ['pagina' => $pg]);
        $qs     = http_build_query(array_filter($params));
      ?>
      <li class="page-item <?= $pg === $pagina ? 'active' : '' ?>">
        <a class="page-link" href="<?= APP_URL ?>/movimentacoes?<?= $qs ?>"><?= $pg ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
