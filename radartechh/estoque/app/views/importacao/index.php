<?php
/**
 * importacao/index.php — Tela de importação de CSV
 */
$tituloPagina = 'Importar CSV';
$paginaAtiva  = 'importacao';
require_once dirname(__DIR__) . '/layouts/header.php';

$resultado = $_SESSION['import_resultado'] ?? null;
unset($_SESSION['import_resultado']);
?>

<h2 class="h5 fw-700 mb-3">
  <i class="bi bi-file-earmark-spreadsheet me-2"></i>Importar Produtos via CSV
</h2>

<!-- Resultado da importação anterior -->
<?php if ($resultado): ?>
  <div class="alert <?= $resultado['importados'] > 0 ? 'alert-success' : 'alert-warning' ?> mb-3">
    <i class="bi bi-check-circle me-2"></i>
    <strong><?= $resultado['importados'] ?> produto(s) importado(s)</strong>
    de <?= $resultado['total'] ?> linha(s) processada(s).
    <?php if (!empty($resultado['erros'])): ?>
      <span class="ms-2"><?= count($resultado['erros']) ?> erro(s) encontrado(s).</span>
    <?php endif; ?>
  </div>

  <?php if (!empty($resultado['erros'])): ?>
  <div class="card mb-3 border-warning">
    <div class="card-header text-warning">
      <i class="bi bi-exclamation-triangle me-2"></i>Linhas com erro
    </div>
    <div class="card-body p-0">
      <ul class="list-group list-group-flush" style="font-size:.83rem;">
        <?php foreach ($resultado['erros'] as $erro): ?>
          <li class="list-group-item list-group-item-warning">
            <i class="bi bi-x-circle me-2"></i><?= e($erro) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endif; ?>
<?php endif; ?>

<div class="row g-3">

  <!-- Formulário de upload -->
  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-upload me-2"></i>Enviar Arquivo
      </div>
      <div class="card-body">
        <form method="POST"
              action="<?= APP_URL ?>/importacao/processar"
              enctype="multipart/form-data"
              id="form-importacao"
              novalidate>
          <?= camposCsrf() ?>

          <div class="mb-3">
            <label for="arquivo" class="form-label">Selecione o arquivo CSV *</label>
            <input type="file"
                   id="arquivo"
                   name="arquivo"
                   class="form-control"
                   accept=".csv"
                   required>
            <small class="text-secondary">Tamanho máximo: 5 MB. Separador: ponto-e-vírgula (;) ou vírgula (,).</small>
          </div>

          <button type="submit" class="btn btn-primary" id="btn-importar">
            <i class="bi bi-upload me-1"></i>Importar Produtos
          </button>

          <a href="<?= APP_URL ?>/assets/modelo_importacao.csv"
             download="modelo_importacao.csv"
             class="btn btn-outline-secondary ms-2">
            <i class="bi bi-download me-1"></i>Baixar Modelo
          </a>
        </form>
      </div>
    </div>
  </div>

  <!-- Instruções -->
  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-info-circle me-2"></i>Instruções
      </div>
      <div class="card-body" style="font-size:.85rem;">
        <p class="mb-2"><strong>Formato do CSV:</strong></p>
        <code class="d-block p-2 rounded mb-3" style="background:#f4f5f8; font-size:.75rem; word-break:break-all;">
          nome;codigo_barras;categoria;marca;preco_custo;preco_venda;estoque_atual;estoque_minimo
        </code>

        <ul class="mb-0 ps-3">
          <li class="mb-1">A <strong>primeira linha</strong> é o cabeçalho (será ignorada).</li>
          <li class="mb-1">O separador pode ser <code>;</code> ou <code>,</code> — detectado automaticamente.</li>
          <li class="mb-1"><code>codigo_barras</code> deve ser único. Duplicados geram erro na linha.</li>
          <li class="mb-1"><code>categoria</code>: se não existir, é criada automaticamente.</li>
          <li class="mb-1">Preços: use vírgula como decimal (ex: <code>1,50</code> ou <code>1.50</code>).</li>
          <li class="mb-1">Linhas com erros são ignoradas; as demais são importadas normalmente.</li>
        </ul>

        <div class="alert alert-info mt-3 py-2 mb-0" style="font-size:.8rem;">
          <i class="bi bi-lightbulb me-2"></i>
          Baixe o <strong>modelo de exemplo</strong> para começar com o formato correto.
        </div>
      </div>
    </div>
  </div>

</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
