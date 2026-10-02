<?php
/**
 * relatorios/valor-estoque.php — Valor total do estoque
 */
$paginaAtiva = 'rel-valor';
require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h2 class="h5 fw-700 mb-0">
    <i class="bi bi-currency-dollar me-2"></i>Valor Total do Estoque
  </h2>
  <a href="<?= APP_URL ?>/relatorios/valor-estoque?exportar=1"
     class="btn btn-outline-success btn-sm">
    <i class="bi bi-download me-1"></i>Exportar CSV
  </a>
</div>

<!-- Cards de totais -->
<div class="row g-3 mb-4">
  <div class="col-12 col-md-4">
    <div class="stat-card primary">
      <div class="stat-card-icon"><i class="bi bi-box-seam"></i></div>
      <div class="stat-card-value"><?= count($produtos) ?></div>
      <div class="stat-card-label">Produtos com Estoque</div>
    </div>
  </div>
  <div class="stat-card success col-12 col-md-4" style="margin:0;">
    <div class="stat-card" style="background:linear-gradient(135deg,#2ec4b6,#1d9e92);border-radius:16px;padding:1.4rem;">
      <div class="stat-card-icon"><i class="bi bi-tag"></i></div>
      <div class="stat-card-value" style="font-size:1.3rem;"><?= moeda($totalCusto) ?></div>
      <div class="stat-card-label">Valor Total a Custo</div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="stat-card purple">
      <div class="stat-card-icon"><i class="bi bi-graph-up-arrow"></i></div>
      <div class="stat-card-value" style="font-size:1.3rem;"><?= moeda($totalVenda) ?></div>
      <div class="stat-card-label">Valor Total a Venda</div>
    </div>
  </div>
</div>

<!-- Margem estimada -->
<div class="alert alert-<?= $lucroEstimado >= 0 ? 'success' : 'danger' ?> mb-3">
  <i class="bi bi-graph-up me-2"></i>
  Margem estimada de lucro no estoque:
  <strong><?= moeda($lucroEstimado) ?></strong>
  (<?= $totalCusto > 0 ? number_format(($lucroEstimado / $totalCusto) * 100, 1) : 0 ?>%)
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table" aria-label="Valor do estoque por produto">
      <thead>
        <tr>
          <th>Produto</th>
          <th>Categoria</th>
          <th class="text-end">Estoque</th>
          <th class="text-end">Custo Unit.</th>
          <th class="text-end">Venda Unit.</th>
          <th class="text-end">Valor Custo</th>
          <th class="text-end">Valor Venda</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($produtos)): ?>
          <tr>
            <td colspan="7" class="text-center py-4 text-secondary">
              Nenhum produto com estoque disponível.
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
            <td class="text-end"><?= $p['estoque_atual'] ?></td>
            <td class="text-end"><?= moeda((float)$p['preco_custo']) ?></td>
            <td class="text-end"><?= moeda((float)$p['preco_venda']) ?></td>
            <td class="text-end fw-600"><?= moeda((float)$p['valor_custo']) ?></td>
            <td class="text-end fw-600 text-success"><?= moeda((float)$p['valor_venda']) ?></td>
          </tr>
          <?php endforeach; ?>

          <!-- Totais -->
          <tr class="table-light fw-700">
            <td colspan="5" class="text-end">TOTAIS:</td>
            <td class="text-end text-primary"><?= moeda($totalCusto) ?></td>
            <td class="text-end text-success"><?= moeda($totalVenda) ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
