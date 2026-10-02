<?php
/**
 * fornecedores/index.php — Lista de fornecedores
 */
$tituloPagina = $tituloPagina ?? 'Fornecedores';
$paginaAtiva  = 'fornecedores';
require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h5 fw-700 mb-0">Fornecedores</h2>
    <small class="text-secondary"><?= count($fornecedores) ?> fornecedor(es)</small>
  </div>
  <a href="<?= APP_URL ?>/fornecedores/criar" class="btn btn-primary" id="btn-novo-fornecedor">
    <i class="bi bi-plus-lg me-1"></i> Novo Fornecedor
  </a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table" aria-label="Lista de fornecedores">
      <thead>
        <tr>
          <th>#</th>
          <th>Nome</th>
          <th>Telefone</th>
          <th>E-mail</th>
          <th>Status</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($fornecedores)): ?>
          <tr>
            <td colspan="6" class="text-center py-4 text-secondary">
              Nenhum fornecedor cadastrado.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($fornecedores as $f): ?>
          <tr>
            <td class="text-secondary" style="font-size:.8rem;"><?= $f['id'] ?></td>
            <td class="fw-600"><?= e($f['nome']) ?></td>
            <td><?= e($f['telefone'] ?? '—') ?></td>
            <td>
              <?php if ($f['email']): ?>
                <a href="mailto:<?= e($f['email']) ?>"><?= e($f['email']) ?></a>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td>
              <?= $f['ativo']
                ? '<span class="badge bg-success">Ativo</span>'
                : '<span class="badge bg-secondary">Inativo</span>' ?>
            </td>
            <td class="text-end">
              <a href="<?= APP_URL ?>/fornecedores/editar/<?= $f['id'] ?>"
                 class="btn btn-sm btn-outline-primary me-1"
                 id="btn-editar-forn-<?= $f['id'] ?>">
                <i class="bi bi-pencil"></i>
              </a>
              <form method="POST"
                    action="<?= APP_URL ?>/fornecedores/excluir/<?= $f['id'] ?>"
                    style="display:inline;">
                <?= camposCsrf() ?>
                <button type="submit"
                        class="btn btn-sm btn-outline-danger"
                        id="btn-excluir-forn-<?= $f['id'] ?>"
                        data-confirmar="Excluir o fornecedor «<?= e($f['nome']) ?>»?">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
