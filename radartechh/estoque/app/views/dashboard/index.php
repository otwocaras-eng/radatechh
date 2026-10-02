<?php
/**
 * dashboard/index.php
 * View do painel inicial.
 * Variáveis injetadas pelo DashboardController.
 */

$tituloPagina = 'Dashboard';
$paginaAtiva  = 'dashboard';
require_once dirname(__DIR__) . '/layouts/header.php';
?>

<!-- ── Cards de estatísticas ───────────────────────────────── -->
<div class="row g-3 mb-4">

  <div class="col-6 col-lg-3">
    <div class="stat-card primary">
      <div class="stat-card-icon"><i class="bi bi-box-seam"></i></div>
      <div class="stat-card-value"><?= number_format($totalProdutos) ?></div>
      <div class="stat-card-label">Produtos Ativos</div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="stat-card danger">
      <div class="stat-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
      <div class="stat-card-value"><?= number_format($abaixoMinimo) ?></div>
      <div class="stat-card-label">Abaixo do Mínimo</div>
    </div>
  </div>

  <?php if (ehAdmin()): ?>
  <div class="col-6 col-lg-3">
    <div class="stat-card success">
      <div class="stat-card-icon"><i class="bi bi-tag"></i></div>
      <div class="stat-card-value" style="font-size:1.3rem;"><?= moeda($valorEstoque) ?></div>
      <div class="stat-card-label">Valor a Custo</div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="stat-card purple">
      <div class="stat-card-icon"><i class="bi bi-currency-dollar"></i></div>
      <div class="stat-card-value" style="font-size:1.3rem;"><?= moeda($valorVenda) ?></div>
      <div class="stat-card-label">Valor a Venda</div>
    </div>
  </div>
  <?php endif; ?>

</div>

<!-- ── Linha com alertas + últimas movimentações ───────────── -->
<div class="row g-3">

  <!-- Alertas de estoque mínimo -->
  <div class="col-12 col-xl-5">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-exclamation-triangle text-warning me-2"></i>Alertas de Estoque</span>
        <a href="<?= APP_URL ?>/relatorios/lista-compras" class="btn btn-sm btn-outline-primary">
          Ver todos
        </a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($alertasEstoque)): ?>
          <div class="text-center py-4 text-success">
            <i class="bi bi-check-circle" style="font-size:2rem;"></i>
            <p class="mt-2 mb-0 fw-600">Todos os produtos OK!</p>
          </div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($alertasEstoque as $alerta): ?>
              <?php
                $semEstoque = (int) $alerta['estoque_atual'] === 0;
                $corBg = $semEstoque ? 'rgba(230,57,70,.06)' : 'rgba(244,162,97,.06)';
                $corBadge = $semEstoque ? 'danger' : 'warning';
                $textoBadge = $semEstoque ? 'Sem estoque' : 'Baixo';
              ?>
              <a href="<?= APP_URL ?>/produtos/ver/<?= $alerta['id'] ?>"
                 class="list-group-item list-group-item-action d-flex align-items-center gap-3"
                 style="background:<?= $corBg ?>;">
                <div class="flex-grow-1 min-width-0">
                  <div class="fw-600 text-truncate" style="font-size:.87rem;">
                    <?= e($alerta['nome']) ?>
                  </div>
                  <small class="text-secondary"><?= e($alerta['categoria'] ?? '—') ?></small>
                </div>
                <div class="text-end flex-shrink-0">
                  <div class="fw-700 <?= $semEstoque ? 'text-danger' : 'text-warning' ?>">
                    <?= $alerta['estoque_atual'] ?> un.
                  </div>
                  <span class="badge bg-<?= $corBadge ?>"><?= $textoBadge ?></span>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Últimas movimentações -->
  <div class="col-12 col-xl-7">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-arrow-left-right me-2"></i>Últimas Movimentações</span>
        <a href="<?= APP_URL ?>/movimentacoes" class="btn btn-sm btn-outline-primary">
          Ver histórico
        </a>
      </div>
      <div class="table-responsive">
        <table class="table" aria-label="Últimas movimentações">
          <thead>
            <tr>
              <th>Produto</th>
              <th>Tipo</th>
              <th class="text-end">Qtd.</th>
              <th>Usuário</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($ultimasMovimentacoes)): ?>
              <tr>
                <td colspan="5" class="text-center text-secondary py-4">
                  Nenhuma movimentação registrada.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($ultimasMovimentacoes as $mov): ?>
                <tr>
                  <td>
                    <span class="fw-500" style="font-size:.87rem;"><?= e($mov['produto_nome']) ?></span>
                  </td>
                  <td>
                    <?php if ($mov['tipo'] === 'entrada'): ?>
                      <span class="badge bg-success">
                        <i class="bi bi-arrow-down-circle me-1"></i>Entrada
                      </span>
                    <?php else: ?>
                      <span class="badge bg-danger">
                        <i class="bi bi-arrow-up-circle me-1"></i>Saída
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end fw-700"><?= $mov['quantidade'] ?></td>
                  <td class="text-secondary" style="font-size:.8rem;">
                    <?= e($mov['usuario_nome']) ?>
                  </td>
                  <td class="text-secondary" style="font-size:.8rem;">
                    <?= dataHora($mov['criado_em']) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div><!-- /row -->

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
