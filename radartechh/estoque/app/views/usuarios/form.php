<?php
/**
 * usuarios/form.php — Formulário de criação e edição de usuário
 * Variável $usuario: array com dados (edição) ou null (criação)
 */
$editando    = !empty($usuario);
$dadosSalvos = $_SESSION['form_dados'] ?? [];
unset($_SESSION['form_dados']);

$nome   = e($dadosSalvos['nome']   ?? ($usuario['nome']   ?? ''));
$login  = e($dadosSalvos['login']  ?? ($usuario['login']  ?? ''));
$perfil =    $dadosSalvos['perfil'] ?? ($usuario['perfil'] ?? 'vendedor');
$ativo  = isset($dadosSalvos['ativo']) ? (bool)$dadosSalvos['ativo'] : (bool)($usuario['ativo'] ?? true);

$tituloPagina = $tituloPagina ?? ($editando ? 'Editar Usuário' : 'Novo Usuário');
$paginaAtiva  = 'usuarios';
require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= APP_URL ?>/usuarios" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h2 class="h5 fw-700 mb-0"><?= $tituloPagina ?></h2>
</div>

<?php
$erros = $_SESSION['form_erros'] ?? [];
unset($_SESSION['form_erros']);
if (!empty($erros)):
?>
<div class="alert alert-danger">
  <i class="bi bi-exclamation-triangle me-2"></i>
  <?php foreach ($erros as $er): ?><?= e($er) ?><br><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card" style="max-width:550px;">
  <div class="card-body">
    <form method="POST"
          action="<?= APP_URL . ($editando ? '/usuarios/atualizar' : '/usuarios/salvar') ?>"
          id="form-usuario"
          novalidate>
      <?= camposCsrf() ?>
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
      <?php endif; ?>

      <div class="mb-3">
        <label for="nome" class="form-label">Nome completo *</label>
        <input type="text" id="nome" name="nome" class="form-control"
               value="<?= $nome ?>" maxlength="100" required autofocus>
      </div>

      <div class="mb-3">
        <label for="login" class="form-label">Login *</label>
        <input type="text" id="login" name="login" class="form-control"
               value="<?= $login ?>" maxlength="50" required
               pattern="[a-zA-Z0-9._-]+"
               title="Apenas letras, números, ponto, hífen e sublinhado">
      </div>

      <div class="mb-3">
        <label for="<?= $editando ? 'nova_senha' : 'senha' ?>" class="form-label">
          <?= $editando ? 'Nova Senha <small class="text-secondary">(deixe em branco para manter)</small>' : 'Senha *' ?>
        </label>
        <input type="password"
               id="<?= $editando ? 'nova_senha' : 'senha' ?>"
               name="<?= $editando ? 'nova_senha' : 'senha' ?>"
               class="form-control"
               <?= $editando ? '' : 'required' ?>
               minlength="8"
               maxlength="128"
               placeholder="Mínimo 8 caracteres">
      </div>

      <div class="mb-3">
        <label for="perfil" class="form-label">Perfil *</label>
        <select id="perfil" name="perfil" class="form-select" required>
          <option value="vendedor" <?= $perfil === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
          <option value="admin"    <?= $perfil === 'admin'    ? 'selected' : '' ?>>Administrador</option>
        </select>
      </div>

      <?php if ($editando): ?>
      <div class="mb-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox"
                 id="ativo" name="ativo" value="1"
                 <?= $ativo ? 'checked' : '' ?>>
          <label class="form-check-label" for="ativo">Usuário ativo</label>
        </div>
      </div>
      <?php endif; ?>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary" id="btn-salvar-usuario">
          <i class="bi bi-check-lg me-1"></i>
          <?= $editando ? 'Salvar Alterações' : 'Criar Usuário' ?>
        </button>
        <a href="<?= APP_URL ?>/usuarios" class="btn btn-outline-secondary">Cancelar</a>
      </div>

    </form>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
