<?php
/**
 * relatorios/mais-vendidos.php
 */
$paginaAtiva = 'rel-vendidos';
require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h2 class="h5 fw-700 mb-0">
    <i class="bi bi-bar-chart-line me-2"></i>Produtos Mais Vendidos
  </h2>
</div>

<!-- Filtros -->
<div class="card mb-3">
  <div class="card-body py-2">
    <form method="GET" action="<?= APP_URL ?>/relatorios/mais-vendidos" class="row g-2 align-items-end">
      <div class="col-6 col-md-3">
        <label class="form-label mb-1" style="font-size:.73rem;">Data início</label>
        <input type="date" name="data_inicio" class="form-control form-control-sm"
               value="<?= e($dataInicio) ?>">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label mb-1" style="font-size:.73rem;">Data fim</label>
        <input type="date" name="data_fim" class="form-control form-control-sm"
               value="<?= e($dataFim) ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label mb-1" style="font-size:.73rem;">Top N</label>
        <select name="limite" class="form-select form-select-sm">
          <?php foreach ([10, 20, 50, 100] as $op): ?>
            <option value="<?= $op ?>" <?= $limite == $op ? 'selected' : '' ?>>Top <?= $op ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-4 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-fill">
          <i class="bi bi-search"></i> Gerar
        </button>
        <a href="<?= APP_URL ?>/relatorios/mais-vendidos?data_inicio=<?= $dataInicio ?>&data_fim=<?= $dataFim ?>&limite=<?= $limite ?>&exportar=1"
           class="btn btn-outline-success btn-sm">
          <i class="bi bi-download"></i> CSV
        </a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table" aria-label="Produtos mais vendidos">
      <thead>
        <tr>
          <th>#</th>
          <th>Produto</th>
          <th>Categoria</th>
          <th class="text-end">Total Saídas</th>
          <th class="text-end">Nº Transações</th>
          <th class="text-end">Preço Venda</th>
          <th class="text-end">Receita Estimada</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($produtos)): ?>
          <tr>
            <td colspan="7" class="text-center py-5 text-secondary">
              Nenhum dado encontrado para o período selecionado.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($produtos as $i => $p): ?>
          <tr>
            <td class="text-secondary fw-700">
              <?php if ($i === 0): ?>🥇<?php elseif ($i === 1): ?>🥈<?php elseif ($i === 2): ?>🥉<?php else: ?><?= $i+1 ?><?php endif; ?>
            </td>
            <td>
              <a href="<?= APP_URL ?>/produtos/ver/<?= $p['id'] ?>"
                 class="fw-600 text-decoration-none"><?= e($p['nome']) ?></a>
              <?php if ($p['marca']): ?>
                <br><small class="text-secondary"><?= e($p['marca']) ?></small>
              <?php endif; ?>
            </td>
            <td class="text-secondary" style="font-size:.82rem;"><?= e($p['categoria'] ?? '—') ?></td>
            <td class="text-end fw-700 text-primary"><?= number_format($p['total_saida']) ?></td>
            <td class="text-end"><?= $p['num_saidas'] ?></td>
            <td class="text-end"><?= moeda((float)$p['preco_venda']) ?></td>
            <td class="text-end fw-700 text-success"><?= moeda((float)$p['receita_estimada']) ?></td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
