<?php
/**
 * login.php — Tela de login
 * Design: glassmorphism sobre fundo escuro com gradiente animado.
 * Não usa o layout padrão (header/footer) pois é página standalone.
 */

// Recupera dados da tentativa anterior (se houver)
$erros       = $_SESSION['login_erros']  ?? [];
$ultimoLogin = $_SESSION['login_ultimo'] ?? '';
unset($_SESSION['login_erros'], $_SESSION['login_ultimo']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Acesso ao sistema de controle de estoque">
  <meta name="robots" content="noindex, nofollow">
  <title>Login — <?= e(APP_NAME) ?></title>

  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- CSS do sistema -->
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/estoque.css">
</head>
<body>

<div class="login-page">

  <div class="login-card">

    <!-- Ícone / logo -->
    <div class="login-logo">
      <i class="bi bi-boxes"></i>
    </div>

    <h1><?= e(APP_NAME) ?></h1>
    <p class="subtitulo">Controle de estoque simplificado para sua papelaria</p>

    <!-- Erros de login -->
    <?php if (!empty($erros)): ?>
      <div class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php foreach ($erros as $erro): ?>
          <?= e($erro) ?><br>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- ── Botão Google ──────────────────────────────── -->
    <a href="<?= APP_URL ?>/google/auth"
       class="btn-google"
       id="btn-login-google">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20">
        <path fill="#EA4335" d="M24 9.5c3.1 0 5.8 1.1 7.9 2.9l5.9-5.9C34.4 3.4 29.5 1.5 24 1.5 14.9 1.5 7.2 6.9 3.6 14.7l6.9 5.3C12.3 13.5 17.7 9.5 24 9.5z"/>
        <path fill="#4285F4" d="M46.5 24c0-1.6-.1-3.1-.4-4.5H24v8.5h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.3-4 6.8-10 6.8-17z"/>
        <path fill="#FBBC05" d="M10.5 28.7c-.5-1.4-.8-2.9-.8-4.5s.3-3.1.8-4.5L3.6 14.4C1.3 18.3 0 22.8 0 27.5s1.3 9.2 3.6 13.1l6.9-5.3c-.5-1.5-.8-2.9-.8-4.4-.1-.7-.2-1.5-.2-2.2z"/>
        <path fill="#34A853" d="M24 46.5c5.5 0 10.1-1.8 13.5-4.9l-7.5-5.8c-2 1.3-4.4 2.1-6.9 2.1-6.3 0-11.7-4.2-13.5-10l-6.9 5.3C7.1 41 15 46.5 24 46.5z"/>
      </svg>
      Entrar com Google
    </a>

    <!-- Separador -->
    <div class="separador-ou">
      <span>ou entre com login e senha</span>
    </div>

    <!-- Formulário de login -->
    <form method="POST"
          action="<?= APP_URL ?>/login/processar"
          id="form-login"
          novalidate>

      <!-- Token CSRF -->
      <?= camposCsrf() ?>

      <!-- Campo: login -->
      <div class="mb-3">
        <label for="login" class="form-label">
          <i class="bi bi-person me-1"></i>Login
        </label>
        <input type="text"
               id="login"
               name="login"
               class="form-control"
               value="<?= e($ultimoLogin) ?>"
               placeholder="Seu usuário de acesso"
               autocomplete="username"
               autofocus
               required
               maxlength="50">
      </div>

      <!-- Campo: senha -->
      <div class="mb-4">
        <label for="senha" class="form-label">
          <i class="bi bi-lock me-1"></i>Senha
        </label>
        <div class="position-relative">
          <input type="password"
                 id="senha"
                 name="senha"
                 class="form-control"
                 placeholder="Sua senha"
                 autocomplete="current-password"
                 required
                 maxlength="128"
                 style="padding-right: 3rem;">
          <!-- Botão mostrar/esconder senha -->
          <button type="button"
                  id="btn-toggle-senha"
                  class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2"
                  style="background:none;border:none;color:rgba(255,255,255,0.4);"
                  aria-label="Mostrar senha">
            <i class="bi bi-eye" id="icone-senha"></i>
          </button>
        </div>
      </div>

      <!-- Botão entrar -->
      <button type="submit" class="btn btn-primary" id="btn-entrar">
        <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
      </button>

    </form>

    <!-- Rodapé da tela de login -->
    <div class="login-rodape">
      <i class="bi bi-shield-lock me-1"></i>
      Acesso restrito — somente usuários cadastrados
    </div>

    <!-- ── Janela de Acesso Rápido (Clique e Entre) ──────── -->
    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 text-start">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="text-white-50 small fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">
          <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Acesso Rápido (Clique para Logar)
        </span>
      </div>

      <div class="d-grid gap-2">
        <!-- Botão Admin -->
        <button type="button"
                class="btn btn-outline-light d-flex align-items-center justify-content-between p-2 text-start quick-login-btn"
                onclick="autoLogin('admin', 'Admin@1234')"
                style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.15); border-radius: 10px;">
          <div>
            <div class="fw-bold text-white small d-flex align-items-center gap-2">
              <i class="bi bi-shield-lock-fill text-primary"></i> Administrador
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.65rem;">Total</span>
            </div>
            <div class="text-white-50" style="font-size: 0.72rem;">
              Usuário: <code>admin</code> | Senha: <code>Admin@1234</code>
            </div>
          </div>
          <span class="badge bg-primary text-white px-2 py-1" style="font-size: 0.75rem;">
            Entrar <i class="bi bi-arrow-right-short"></i>
          </span>
        </button>

        <!-- Botão Vendedor -->
        <button type="button"
                class="btn btn-outline-light d-flex align-items-center justify-content-between p-2 text-start quick-login-btn"
                onclick="autoLogin('vendedor', 'Vendedor@1')"
                style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.15); border-radius: 10px;">
          <div>
            <div class="fw-bold text-white small d-flex align-items-center gap-2">
              <i class="bi bi-person-badge-fill text-info"></i> Vendedor
              <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 0.65rem;">Balcão</span>
            </div>
            <div class="text-white-50" style="font-size: 0.72rem;">
              Usuário: <code>vendedor</code> | Senha: <code>Vendedor@1</code>
            </div>
          </div>
          <span class="badge bg-info text-dark px-2 py-1 fw-bold" style="font-size: 0.75rem;">
            Entrar <i class="bi bi-arrow-right-short"></i>
          </span>
        </button>
      </div>
    </div>

  </div><!-- /login-card -->

</div><!-- /login-page -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmn1rBE9WiGp7wK42fByQOQn/E8u"
        crossorigin="anonymous"></script>

<script>
  // Toggle mostrar/esconder senha
  document.getElementById('btn-toggle-senha').addEventListener('click', function () {
    const input  = document.getElementById('senha');
    const icone  = document.getElementById('icone-senha');
    const visivel = input.type === 'password';
    input.type   = visivel ? 'text' : 'password';
    icone.className = visivel ? 'bi bi-eye-slash' : 'bi bi-eye';
  });

  // Previne duplo envio
  document.getElementById('form-login').addEventListener('submit', function () {
    const btn = document.getElementById('btn-entrar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Verificando...';
  });

  // Acesso rápido com 1 clique
  function autoLogin(usuario, senha) {
    const inputLogin = document.getElementById('login');
    const inputSenha = document.getElementById('senha');
    const form = document.getElementById('form-login');

    inputLogin.value = usuario;
    inputSenha.value = senha;

    // Dispara animação e submissão
    const btn = document.getElementById('btn-entrar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Entrando como ' + usuario + '...';
    form.submit();
  }
</script>

</body>
</html>
