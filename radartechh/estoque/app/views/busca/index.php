<?php
/**
 * busca/index.php — Página de busca dedicada (mobile)
 * Versão completa na Etapa 5.
 */
$tituloPagina = 'Busca Rápida';
$paginaAtiva  = 'busca';
require_once dirname(__DIR__) . '/layouts/header.php';
?>

<div class="card">
  <div class="card-header">
    <i class="bi bi-upc-scan me-2"></i>Busca de Produto
  </div>
  <div class="card-body">
    <p class="text-secondary mb-3">
      Digite o nome do produto ou aponte o leitor de código de barras para buscar.
    </p>
    <div class="busca-rapida-wrapper" style="max-width:500px; position:relative;">
      <i class="bi bi-upc-scan busca-icon"></i>
      <input type="text"
             id="busca-rapida"
             class="form-control form-control-lg"
             placeholder="Nome ou código de barras…"
             autocomplete="off"
             autofocus>
      <div id="resultado-busca"></div>
    </div>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
