<?php
/**
 * relatorios/parados.php — Produtos sem saída há X dias
 */
$paginaAtiva = 'rel-parados';
require_once dirname(__DIR__) . '/layouts/header.php';

$totalValor = array_sum(array_column($produtos, 'valor_parado'));
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h2 class="h5 fw-700 mb-0">
    <i class="bi bi-hourglass-split me-2"></i>Produtos Parados
  </h2>
</div>

<!-- Filtro de dias -->
<div class="card mb-3">
  <div class="card-body py-2">
    <form method="GET" action="<?= APP_URL ?>/relatorios/parados" class="row g-2 align-items-end">
      <div class="col-8 col-md-3">
        <label class="form-label mb-1" style="font-size:.73rem;">Parado há mais de (dias)</label>
        <input type="number" name="dias" class="form-control form-control-sm"
               value="<?= $dias ?>" min="1" max="3650">
      </div>
      <div class="col-4 col-md-3 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-fill">
          <i class="bi bi-search"></i> Gerar
        </button>
        <a href="<?= APP_URL ?>/relatorios/parados?dias=<?= $dias ?>&exportar=1"
           class="btn btn-outline-success btn-sm">
          <i class="bi bi-download"></i> CSV
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Card resumo -->
<?php if (!empty($produtos)): ?>
<div class="alert alert-warning mb-3">
  <i class="bi bi-exclamation-triangle me-2"></i>
  <strong><?= count($produtos) ?> produto(s)</strong> sem saída há mais de
  <strong><?= $dias ?> dias</strong> —
  valor parado estimado: <strong><?= moeda($totalValor) ?></strong>
</div>
<?php endif; ?>

<div class="card">
  <div class="table-responsive">
    <table class="table" aria-label="Produtos parados">
      <thead>
        <tr>
          <th>Produto</th>
          <th>Categoria</th>
          <th class="text-end">Estoque</th>
          <th class="text-end">Custo Unit.</th>
          <th class="text-end">Valor Parado</th>
          <th>Última Saída</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($produtos)): ?>
          <tr>
            <td colspan="6" class="text-center py-5 text-success">
              <i class="bi bi-check-circle" style="font-size:2rem;"></i>
              <p class="mt-2 mb-0">Nenhum produto parado no período selecionado.</p>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($produtos as $p): ?>
          <tr>
            <td>
              <a href="<?= APP_URL ?>/produtos/ver/<?= $p['id'] ?>"
                 class="fw-600 text-decoration-none"><?= e($p['nome']) ?></a>
              <?php if ($p['marca']): ?>
                <br><small class="text-secondary"><?= e($p['marca']) ?></small>
              <?php endif; ?>
            </td>
            <td class="text-secondary" style="font-size:.82rem;"><?= e($p['categoria'] ?? '—') ?></td>
            <td class="text-end fw-700"><?= $p['estoque_atual'] ?></td>
            <td class="text-end"><?= moeda((float)$p['preco_custo']) ?></td>
            <td class="text-end fw-700 text-warning"><?= moeda((float)$p['valor_parado']) ?></td>
            <td class="text-secondary" style="font-size:.82rem;">
              <?= $p['ultima_saida'] ? dataHora($p['ultima_saida']) : '<span class="text-danger fw-600">Nunca</span>' ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
