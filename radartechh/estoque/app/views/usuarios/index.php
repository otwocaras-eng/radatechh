<?php
/**
 * usuarios/index.php — Lista de usuários do sistema
 */
$tituloPagina = $tituloPagina ?? 'Usuários';
$paginaAtiva  = 'usuarios';
require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h5 fw-700 mb-0">Usuários do Sistema</h2>
    <small class="text-secondary"><?= count($usuarios) ?> usuário(s) cadastrado(s)</small>
  </div>
  <a href="<?= APP_URL ?>/usuarios/criar" class="btn btn-primary" id="btn-novo-usuario">
    <i class="bi bi-plus-lg me-1"></i> Novo Usuário
  </a>
</div>

<?php
$erros = $_SESSION['form_erros'] ?? [];
unset($_SESSION['form_erros']);
if (!empty($erros)):
?>
<div class="alert alert-danger">
  <i class="bi bi-exclamation-triangle me-2"></i>
  <?php foreach ($erros as $e): ?><?= e($e) ?><br><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
  <div class="table-responsive">
    <table class="table" aria-label="Lista de usuários">
      <thead>
        <tr>
          <th>#</th>
          <th>Nome</th>
          <th>Login</th>
          <th>Perfil</th>
          <th>Status</th>
          <th>Criado em</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($usuarios)): ?>
          <tr>
            <td colspan="7" class="text-center py-4 text-secondary">Nenhum usuário encontrado.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($usuarios as $u): ?>
            <tr>
              <td class="text-secondary"><?= $u['id'] ?></td>
              <td class="fw-600"><?= e($u['nome']) ?></td>
              <td><code><?= e($u['login']) ?></code></td>
              <td>
                <?php if ($u['perfil'] === 'admin'): ?>
                  <span class="badge bg-primary">Admin</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Vendedor</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($u['ativo']): ?>
                  <span class="badge bg-success">Ativo</span>
                <?php else: ?>
                  <span class="badge bg-danger">Inativo</span>
                <?php endif; ?>
              </td>
              <td class="text-secondary" style="font-size:.8rem;"><?= dataHora($u['criado_em']) ?></td>
              <td class="text-end">
                <a href="<?= APP_URL ?>/usuarios/editar/<?= $u['id'] ?>"
                   class="btn btn-sm btn-outline-primary"
                   id="btn-editar-usuario-<?= $u['id'] ?>">
                  <i class="bi bi-pencil"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
