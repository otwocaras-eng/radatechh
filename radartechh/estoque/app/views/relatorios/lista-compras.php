<?php
/**
 * relatorios/lista-compras.php — Lista de compras sugerida
 */
$paginaAtiva = 'rel-compras';
require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h2 class="h5 fw-700 mb-0">
    <i class="bi bi-cart-plus me-2"></i>Lista de Compras Sugerida
  </h2>
  <a href="<?= APP_URL ?>/relatorios/lista-compras?exportar=1"
     class="btn btn-outline-success btn-sm">
    <i class="bi bi-download me-1"></i>Exportar CSV
  </a>
</div>

<?php if (!empty($produtos)): ?>
<div class="alert alert-info mb-3">
  <i class="bi bi-info-circle me-2"></i>
  <strong><?= count($produtos) ?> produto(s)</strong> abaixo do estoque mínimo —
  custo estimado para reposição: <strong><?= moeda($totalCusto) ?></strong>
</div>
<?php endif; ?>

<div class="card">
  <div class="table-responsive">
    <table class="table" aria-label="Lista de compras sugerida">
      <thead>
        <tr>
          <th>Produto</th>
          <th>Categoria</th>
          <th class="text-end">Estoque Atual</th>
          <th class="text-end">Estoque Mínimo</th>
          <th class="text-end">Qtd. Sugerida</th>
          <th class="text-end">Custo Unit.</th>
          <th class="text-end">Custo Repor</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($produtos)): ?>
          <tr>
            <td colspan="7" class="text-center py-5 text-success">
              <i class="bi bi-check-circle" style="font-size:2rem;"></i>
              <p class="mt-2 mb-0">Todos os produtos estão acima do estoque mínimo!</p>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($produtos as $p): ?>
          <tr <?= (int)$p['estoque_atual'] === 0 ? 'class="table-danger"' : '' ?>>
            <td>
              <a href="<?= APP_URL ?>/produtos/ver/<?= $p['id'] ?>"
                 class="fw-600 text-decoration-none"><?= e($p['nome']) ?></a>
              <?php if ($p['marca']): ?>
                <br><small class="text-secondary"><?= e($p['marca']) ?></small>
              <?php endif; ?>
            </td>
            <td class="text-secondary" style="font-size:.82rem;"><?= e($p['categoria'] ?? '—') ?></td>
            <td class="text-end fw-700 <?= (int)$p['estoque_atual'] === 0 ? 'text-danger' : 'text-warning' ?>">
              <?= $p['estoque_atual'] ?>
            </td>
            <td class="text-end"><?= $p['estoque_minimo'] ?></td>
            <td class="text-end fw-700 text-primary"><?= $p['qtd_sugerida'] ?></td>
            <td class="text-end"><?= moeda((float)$p['preco_custo']) ?></td>
            <td class="text-end fw-700"><?= moeda((float)$p['custo_repor']) ?></td>
          </tr>
          <?php endforeach; ?>

          <!-- Total -->
          <tr class="table-light fw-700">
            <td colspan="6" class="text-end">TOTAL ESTIMADO:</td>
            <td class="text-end text-primary fs-6"><?= moeda($totalCusto) ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
