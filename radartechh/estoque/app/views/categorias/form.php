<?php
/**
 * categorias/form.php — Formulário criar/editar categoria
 * $categoria: array (edição) ou null (criação)
 */
$editando    = !empty($categoria);
$dadosSalvos = $_SESSION['form_dados'] ?? [];
unset($_SESSION['form_dados']);

$nome = e($dadosSalvos['nome'] ?? ($categoria['nome'] ?? ''));

$tituloPagina = $tituloPagina ?? ($editando ? 'Editar Categoria' : 'Nova Categoria');
$paginaAtiva  = 'categorias';
require_once dirname(__DIR__) . '/layouts/header.php';

$erros = $_SESSION['form_erros'] ?? [];
unset($_SESSION['form_erros']);
?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= APP_URL ?>/categorias" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h2 class="h5 fw-700 mb-0"><?= $tituloPagina ?></h2>
</div>

<?php if (!empty($erros)): ?>
<div class="alert alert-danger">
  <i class="bi bi-exclamation-triangle me-2"></i>
  <?php foreach ($erros as $e): ?><?= e($e) ?><br><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card" style="max-width:450px;">
  <div class="card-body">
    <form method="POST"
          action="<?= APP_URL . ($editando ? '/categorias/atualizar' : '/categorias/salvar') ?>"
          id="form-categoria"
          novalidate>
      <?= camposCsrf() ?>
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
      <?php endif; ?>

      <div class="mb-4">
        <label for="nome" class="form-label">Nome da Categoria *</label>
        <input type="text"
               id="nome"
               name="nome"
               class="form-control"
               value="<?= $nome ?>"
               maxlength="100"
               required
               autofocus
               placeholder="Ex.: Canetas e Marcadores">
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary" id="btn-salvar-categoria">
          <i class="bi bi-check-lg me-1"></i>
          <?= $editando ? 'Salvar Alterações' : 'Criar Categoria' ?>
        </button>
        <a href="<?= APP_URL ?>/categorias" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
