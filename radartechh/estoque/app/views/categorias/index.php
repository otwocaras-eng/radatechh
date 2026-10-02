<?php
/**
 * categorias/index.php — Lista de categorias
 */
$tituloPagina = $tituloPagina ?? 'Categorias';
$paginaAtiva  = 'categorias';
require_once dirname(__DIR__) . '/layouts/header.php';

$erros = $_SESSION['form_erros'] ?? [];
unset($_SESSION['form_erros']);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h5 fw-700 mb-0">Categorias</h2>
    <small class="text-secondary"><?= count($categorias) ?> categoria(s)</small>
  </div>
  <a href="<?= APP_URL ?>/categorias/criar" class="btn btn-primary" id="btn-nova-categoria">
    <i class="bi bi-plus-lg me-1"></i> Nova Categoria
  </a>
</div>

<?php if (!empty($erros)): ?>
<div class="alert alert-danger">
  <i class="bi bi-exclamation-triangle me-2"></i>
  <?php foreach ($erros as $e): ?><?= e($e) ?><br><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
  <div class="table-responsive">
    <table class="table" aria-label="Lista de categorias">
      <thead>
        <tr>
          <th>#</th>
          <th>Nome</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($categorias)): ?>
          <tr>
            <td colspan="3" class="text-center py-4 text-secondary">
              Nenhuma categoria cadastrada.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($categorias as $cat): ?>
          <tr>
            <td class="text-secondary" style="font-size:.8rem;"><?= $cat['id'] ?></td>
            <td class="fw-600"><?= e($cat['nome']) ?></td>
            <td class="text-end">
              <a href="<?= APP_URL ?>/categorias/editar/<?= $cat['id'] ?>"
                 class="btn btn-sm btn-outline-primary me-1"
                 id="btn-editar-cat-<?= $cat['id'] ?>">
                <i class="bi bi-pencil"></i>
              </a>
              <form method="POST"
                    action="<?= APP_URL ?>/categorias/excluir/<?= $cat['id'] ?>"
                    style="display:inline;">
                <?= camposCsrf() ?>
                <button type="submit"
                        class="btn btn-sm btn-outline-danger"
                        id="btn-excluir-cat-<?= $cat['id'] ?>"
                        data-confirmar="Excluir a categoria «<?= e($cat['nome']) ?>»? Esta ação não pode ser desfeita.">
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
