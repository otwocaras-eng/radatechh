<?php
/**
 * fornecedores/form.php — Formulário criar/editar fornecedor
 */
$editando    = !empty($fornecedor);
$dadosSalvos = $_SESSION['form_dados'] ?? [];
unset($_SESSION['form_dados']);

$nome     = e($dadosSalvos['nome']     ?? ($fornecedor['nome']     ?? ''));
$telefone = e($dadosSalvos['telefone'] ?? ($fornecedor['telefone'] ?? ''));
$email    = e($dadosSalvos['email']    ?? ($fornecedor['email']    ?? ''));
$ativo    = isset($dadosSalvos['ativo']) ? (bool)$dadosSalvos['ativo'] : (bool)($fornecedor['ativo'] ?? true);

$tituloPagina = $tituloPagina ?? ($editando ? 'Editar Fornecedor' : 'Novo Fornecedor');
$paginaAtiva  = 'fornecedores';
require_once dirname(__DIR__) . '/layouts/header.php';

$erros = $_SESSION['form_erros'] ?? [];
unset($_SESSION['form_erros']);
?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= APP_URL ?>/fornecedores" class="btn btn-sm btn-outline-secondary">
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

<div class="card" style="max-width:550px;">
  <div class="card-body">
    <form method="POST"
          action="<?= APP_URL . ($editando ? '/fornecedores/atualizar' : '/fornecedores/salvar') ?>"
          id="form-fornecedor"
          novalidate>
      <?= camposCsrf() ?>
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= $fornecedor['id'] ?>">
      <?php endif; ?>

      <div class="mb-3">
        <label for="nome" class="form-label">Nome do Fornecedor *</label>
        <input type="text" id="nome" name="nome" class="form-control"
               value="<?= $nome ?>" maxlength="150" required autofocus>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
          <label for="telefone" class="form-label">Telefone</label>
          <input type="tel" id="telefone" name="telefone" class="form-control"
                 value="<?= $telefone ?>" maxlength="20"
                 placeholder="(00) 00000-0000">
        </div>
        <div class="col-12 col-md-6">
          <label for="email" class="form-label">E-mail</label>
          <input type="email" id="email" name="email" class="form-control"
                 value="<?= $email ?>" maxlength="150">
        </div>
      </div>

      <?php if ($editando): ?>
      <div class="mb-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox"
                 id="ativo" name="ativo" value="1"
                 <?= $ativo ? 'checked' : '' ?>>
          <label class="form-check-label" for="ativo">Fornecedor ativo</label>
        </div>
      </div>
      <?php endif; ?>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary" id="btn-salvar-fornecedor">
          <i class="bi bi-check-lg me-1"></i>
          <?= $editando ? 'Salvar Alterações' : 'Cadastrar Fornecedor' ?>
        </button>
        <a href="<?= APP_URL ?>/fornecedores" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
