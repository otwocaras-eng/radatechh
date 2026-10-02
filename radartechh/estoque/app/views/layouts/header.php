<?php
/**
 * header.php — Cabeçalho HTML completo (abertura da página)
 * Incluir no início de toda view interna.
 *
 * Variáveis esperadas (definidas no controller antes do include):
 *   $tituloPagina string  — Título da aba do navegador
 *   $paginaAtiva  string  — Slug da seção ativa na sidebar (ex: 'produtos')
 */

// Garante que as variáveis existam
$tituloPagina = $tituloPagina ?? APP_NAME;
$paginaAtiva  = $paginaAtiva  ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Sistema de controle de estoque para papelaria — <?= e($tituloPagina) ?>">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($tituloPagina) ?> — <?= e(APP_NAME) ?></title>

  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- CSS personalizado do sistema -->
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/estoque.css">

  <!-- Expõe APP_URL para o JavaScript -->
  <script>const APP_URL = '<?= APP_URL ?>';</script>
</head>
<body>
<div class="app-wrapper">

  <!-- ============================================================
       SIDEBAR
       ============================================================ -->
  <aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-header">
      <div class="sidebar-logo-icon">
        <i class="bi bi-boxes"></i>
      </div>
      <div class="sidebar-logo-text">
        <strong><?= e(APP_NAME) ?></strong>
        <small>v<?= APP_VERSION ?></small>
      </div>
    </div>

    <!-- Navegação -->
    <nav class="sidebar-nav">

      <!-- Principal -->
      <span class="sidebar-section-label">Principal</span>

      <a href="<?= APP_URL ?>/dashboard"
         class="nav-link <?= $paginaAtiva === 'dashboard' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>

      <a href="<?= APP_URL ?>/busca"
         class="nav-link <?= $paginaAtiva === 'busca' ? 'active' : '' ?>">
        <i class="bi bi-upc-scan"></i> Busca Rápida
      </a>

      <!-- Estoque -->
      <span class="sidebar-section-label">Estoque</span>

      <a href="<?= APP_URL ?>/produtos"
         class="nav-link <?= $paginaAtiva === 'produtos' ? 'active' : '' ?>">
        <i class="bi bi-box-seam"></i> Produtos
      </a>

      <a href="<?= APP_URL ?>/movimentacoes/entrada"
         class="nav-link <?= $paginaAtiva === 'entrada' ? 'active' : '' ?>">
        <i class="bi bi-box-arrow-in-down"></i> Entrada de Estoque
      </a>

      <a href="<?= APP_URL ?>/movimentacoes/saida"
         class="nav-link <?= $paginaAtiva === 'saida' ? 'active' : '' ?>">
        <i class="bi bi-box-arrow-up"></i> Saída de Estoque
      </a>

      <a href="<?= APP_URL ?>/movimentacoes"
         class="nav-link <?= $paginaAtiva === 'movimentacoes' ? 'active' : '' ?>">
        <i class="bi bi-arrow-left-right"></i> Movimentações
      </a>

      <!-- Cadastros -->
      <span class="sidebar-section-label">Cadastros</span>

      <a href="<?= APP_URL ?>/categorias"
         class="nav-link <?= $paginaAtiva === 'categorias' ? 'active' : '' ?>">
        <i class="bi bi-tag"></i> Categorias
      </a>

      <a href="<?= APP_URL ?>/fornecedores"
         class="nav-link <?= $paginaAtiva === 'fornecedores' ? 'active' : '' ?>">
        <i class="bi bi-truck"></i> Fornecedores
      </a>

      <!-- Relatórios — apenas admin -->
      <?php if (ehAdmin()): ?>
      <span class="sidebar-section-label">Relatórios</span>

      <a href="<?= APP_URL ?>/relatorios/mais-vendidos"
         class="nav-link <?= $paginaAtiva === 'rel-vendidos' ? 'active' : '' ?>">
        <i class="bi bi-bar-chart-line"></i> Mais Vendidos
      </a>

      <a href="<?= APP_URL ?>/relatorios/parados"
         class="nav-link <?= $paginaAtiva === 'rel-parados' ? 'active' : '' ?>">
        <i class="bi bi-hourglass-split"></i> Produtos Parados
      </a>

      <a href="<?= APP_URL ?>/relatorios/lista-compras"
         class="nav-link <?= $paginaAtiva === 'rel-compras' ? 'active' : '' ?>">
        <i class="bi bi-cart-plus"></i> Lista de Compras
      </a>

      <a href="<?= APP_URL ?>/relatorios/valor-estoque"
         class="nav-link <?= $paginaAtiva === 'rel-valor' ? 'active' : '' ?>">
        <i class="bi bi-currency-dollar"></i> Valor do Estoque
      </a>

      <!-- Administração -->
      <span class="sidebar-section-label">Administração</span>

      <a href="<?= APP_URL ?>/importacao"
         class="nav-link <?= $paginaAtiva === 'importacao' ? 'active' : '' ?>">
        <i class="bi bi-file-earmark-spreadsheet"></i> Importar CSV
      </a>

      <a href="<?= APP_URL ?>/usuarios"
         class="nav-link <?= $paginaAtiva === 'usuarios' ? 'active' : '' ?>">
        <i class="bi bi-people"></i> Usuários
      </a>
      <?php endif; ?>

    </nav><!-- /sidebar-nav -->

    <!-- Usuário logado + logout -->
    <div class="sidebar-footer">
      <div class="sidebar-user-avatar">
        <?= strtoupper(substr($_SESSION['usuario_nome'] ?? 'U', 0, 1)) ?>
      </div>
      <div class="sidebar-user-info">
        <strong><?= e($_SESSION['usuario_nome'] ?? '') ?></strong>
        <small><?= e($_SESSION['usuario_perfil'] ?? '') ?></small>
      </div>
      <a href="<?= APP_URL ?>/logout"
         class="sidebar-logout"
         title="Sair do sistema"
         data-confirmar="Deseja sair do sistema?">
        <i class="bi bi-box-arrow-right"></i>
      </a>
    </div>

  </aside><!-- /sidebar -->

  <!-- Overlay para fechar sidebar em mobile -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ============================================================
       CONTEÚDO PRINCIPAL
       ============================================================ -->
  <div class="main-content">

    <!-- Topbar -->
    <header class="topbar">
      <button class="topbar-toggle" id="sidebarToggle" aria-label="Abrir menu">
        <i class="bi bi-list"></i>
      </button>

      <span class="topbar-title">
        <?= e($tituloPagina) ?>
        <small><?= date('d/m/Y') ?></small>
      </span>

      <!-- Busca rápida na topbar -->
      <div class="busca-rapida-wrapper d-none d-md-block" style="width:300px; position:relative;">
        <i class="bi bi-upc-scan busca-icon"></i>
        <input type="text"
               id="busca-rapida"
               class="form-control"
               placeholder="Buscar produto ou código de barras…"
               autocomplete="off">
        <div id="resultado-busca"></div>
      </div>

      <!-- Botão de busca mobile -->
      <a href="<?= APP_URL ?>/busca" class="btn btn-sm btn-outline-secondary d-md-none ms-auto">
        <i class="bi bi-search"></i>
      </a>
    </header>

    <!-- Área de conteúdo da página -->
    <main class="page-content fade-in">
      <!-- Mensagens flash (sucesso / erro) -->
      <?= exibirFlash() ?>
