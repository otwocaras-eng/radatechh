<?php
/**
 * produtos/ver.php — Detalhe de um produto com histórico de movimentações
 */
$paginaAtiva = 'produtos';
require_once dirname(__DIR__) . '/layouts/header.php';

$estaAbaixo = (int)$produto['estoque_atual'] <= (int)$produto['estoque_minimo'];
?>

<!-- Breadcrumb -->
<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= APP_URL ?>/produtos" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <span class="text-secondary" style="font-size:.83rem;">Produtos /</span>
  <span class="fw-600" style="font-size:.83rem;"><?= e($produto['nome']) ?></span>
</div>

<!-- Alerta de estoque baixo -->
<?php if ($estaAbaixo): ?>
<div class="alert alert-warning">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>
  <strong>Atenção:</strong> este produto está com estoque abaixo do mínimo definido.
  <a href="<?= APP_URL ?>/movimentacoes/entrada" class="alert-link ms-2">Registrar entrada</a>
</div>
<?php endif; ?>

<div class="row g-3">

  <!-- Dados do produto -->
  <div class="col-12 col-lg-5">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-box-seam me-2"></i>Dados do Produto</span>
        <?php if (ehAdmin()): ?>
        <a href="<?= APP_URL ?>/produtos/editar/<?= $produto['id'] ?>"
           class="btn btn-sm btn-outline-primary"
           id="btn-editar-produto">
          <i class="bi bi-pencil me-1"></i>Editar
        </a>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <table class="table table-borderless mb-0" style="font-size:.87rem;">
          <tbody>
            <tr>
              <td class="text-secondary fw-600" style="width:40%;">Nome</td>
              <td class="fw-700"><?= e($produto['nome']) ?></td>
            </tr>
            <?php if ($produto['codigo_barras']): ?>
            <tr>
              <td class="text-secondary fw-600">Código de Barras</td>
              <td><code><?= e($produto['codigo_barras']) ?></code></td>
            </tr>
            <?php endif; ?>
            <tr>
              <td class="text-secondary fw-600">Categoria</td>
              <td><?= e($produto['categoria_nome'] ?? '—') ?></td>
            </tr>
            <tr>
              <td class="text-secondary fw-600">Marca</td>
              <td><?= e($produto['marca'] ?? '—') ?></td>
            </tr>
            <?php if (ehAdmin()): ?>
            <tr>
              <td class="text-secondary fw-600">Preço de Custo</td>
              <td><?= moeda((float)$produto['preco_custo']) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
              <td class="text-secondary fw-600">Preço de Venda</td>
              <td class="fw-700 text-primary"><?= moeda((float)$produto['preco_venda']) ?></td>
            </tr>
            <tr>
              <td class="text-secondary fw-600">Estoque Atual</td>
              <td>
                <span class="fw-700 fs-5 <?= $estaAbaixo ? 'text-danger' : 'text-success' ?>">
                  <?= $produto['estoque_atual'] ?>
                </span>
                <small class="text-secondary"> un.</small>
              </td>
            </tr>
            <tr>
              <td class="text-secondary fw-600">Estoque Mínimo</td>
              <td><?= $produto['estoque_minimo'] ?> un.</td>
            </tr>
            <tr>
              <td class="text-secondary fw-600">Status</td>
              <td>
                <?= $produto['ativo']
                  ? '<span class="badge bg-success">Ativo</span>'
                  : '<span class="badge bg-secondary">Inativo</span>' ?>
              </td>
            </tr>
            <tr>
              <td class="text-secondary fw-600">Cadastrado em</td>
              <td style="font-size:.8rem;"><?= dataHora($produto['criado_em']) ?></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Botões de ação rápida -->
      <div class="card-footer bg-transparent d-flex gap-2 flex-wrap">
        <a href="<?= APP_URL ?>/movimentacoes/entrada?produto_id=<?= $produto['id'] ?>"
           class="btn btn-success btn-sm" id="btn-entrada-produto">
          <i class="bi bi-box-arrow-in-down me-1"></i>Entrada
        </a>
        <a href="<?= APP_URL ?>/movimentacoes/saida?produto_id=<?= $produto['id'] ?>"
           class="btn btn-danger btn-sm" id="btn-saida-produto">
          <i class="bi bi-box-arrow-up me-1"></i>Saída
        </a>
        <?php if (ehAdmin() && $produto['ativo']): ?>
        <form method="POST" action="<?= APP_URL ?>/produtos/inativar/<?= $produto['id'] ?>" style="display:inline;">
          <?= camposCsrf() ?>
          <button type="submit" class="btn btn-outline-danger btn-sm"
                  data-confirmar="Inativar este produto? O histórico será preservado.">
            <i class="bi bi-slash-circle me-1"></i>Inativar
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Últimas movimentações -->
  <div class="col-12 col-lg-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i>Últimas Movimentações</span>
        <a href="<?= APP_URL ?>/movimentacoes?produto_id=<?= $produto['id'] ?>"
           class="btn btn-sm btn-outline-primary">Ver tudo</a>
      </div>
      <div class="table-responsive">
        <table class="table" aria-label="Movimentações do produto">
          <thead>
            <tr>
              <th>Tipo</th>
              <th class="text-end">Qtd.</th>
              <th>Motivo</th>
              <th>Usuário</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($movimentacoes)): ?>
              <tr>
                <td colspan="5" class="text-center py-4 text-secondary">
                  Nenhuma movimentação registrada.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($movimentacoes as $mov): ?>
              <tr>
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
                    <i class="bi bi-info-circle ms-1 text-info"
                       title="<?= e($mov['observacao']) ?>"
                       data-bs-toggle="tooltip"></i>
                  <?php endif; ?>
                </td>
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

</div>

<script>
// Inicializa tooltips do Bootstrap
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
  new bootstrap.Tooltip(el);
});
</script>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
