<?php
/**
 * produtos/form.php — Formulário criar/editar produto
 * $produto: array (edição) ou null (criação)
 * $categorias: array de todas as categorias
 */
$editando    = !empty($produto);
$dadosSalvos = $_SESSION['form_dados'] ?? [];
unset($_SESSION['form_dados']);

// Obtém valores com fallback: dados do POST (erro) > produto existente > padrão
function val(string $chave, array $post, ?array $produto, $default = ''): string {
    if (isset($post[$chave]) && $post[$chave] !== '') return $post[$chave];
    if (isset($produto[$chave]))                       return (string) $produto[$chave];
    return (string) $default;
}

$nome           = e(val('nome',           $dadosSalvos, $produto));
$codigoBarras   = e(val('codigo_barras',  $dadosSalvos, $produto));
$categoriaId    =   val('categoria_id',   $dadosSalvos, $produto, 0);
$marca          = e(val('marca',          $dadosSalvos, $produto));
$precoCusto     = e(val('preco_custo',    $dadosSalvos, $produto, '0,00'));
$precoVenda     = e(val('preco_venda',    $dadosSalvos, $produto, '0,00'));
$estoqueAtual   =   val('estoque_atual',  $dadosSalvos, $produto, 0);
$estoqueMinimo  =   val('estoque_minimo', $dadosSalvos, $produto, 5);
$ativo          = isset($dadosSalvos['ativo']) ? (bool)$dadosSalvos['ativo'] : (bool)($produto['ativo'] ?? true);

// Formata preços para exibição no formato brasileiro
if ($editando && !isset($dadosSalvos['preco_custo'])) {
    $precoCusto = number_format((float)$produto['preco_custo'], 2, ',', '.');
    $precoVenda = number_format((float)$produto['preco_venda'], 2, ',', '.');
}

$tituloPagina = $tituloPagina ?? ($editando ? 'Editar Produto' : 'Novo Produto');
$paginaAtiva  = 'produtos';
require_once dirname(__DIR__) . '/layouts/header.php';

$erros = $_SESSION['form_erros'] ?? [];
unset($_SESSION['form_erros']);
?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= $editando ? APP_URL . '/produtos/ver/' . $produto['id'] : APP_URL . '/produtos' ?>"
     class="btn btn-sm btn-outline-secondary">
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

<div class="card">
  <div class="card-body">
    <form method="POST"
          action="<?= APP_URL . ($editando ? '/produtos/atualizar' : '/produtos/salvar') ?>"
          id="form-produto"
          novalidate>
      <?= camposCsrf() ?>
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= $produto['id'] ?>">
      <?php endif; ?>

      <div class="row g-3">

        <!-- Nome do produto -->
        <div class="col-12 col-md-8">
          <label for="nome" class="form-label">Nome do Produto *</label>
          <input type="text" id="nome" name="nome" class="form-control"
                 value="<?= $nome ?>" maxlength="200" required autofocus
                 placeholder="Ex.: Caneta Esferográfica Azul 1.0mm">
        </div>

        <!-- Código de barras -->
        <div class="col-12 col-md-4">
          <label for="codigo_barras" class="form-label">Código de Barras</label>
          <input type="text" id="codigo_barras" name="codigo_barras" class="form-control"
                 value="<?= $codigoBarras ?>" maxlength="50"
                 placeholder="Ex.: 7891010000001">
          <small class="text-secondary">Aponte o leitor para preencher automaticamente</small>
        </div>

        <!-- Categoria -->
        <div class="col-12 col-md-4">
          <label for="categoria_id" class="form-label">Categoria</label>
          <select id="categoria_id" name="categoria_id" class="form-select">
            <option value="">— Sem categoria —</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat['id'] ?>"
                <?= $categoriaId == $cat['id'] ? 'selected' : '' ?>>
                <?= e($cat['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Marca -->
        <div class="col-12 col-md-4">
          <label for="marca" class="form-label">Marca</label>
          <input type="text" id="marca" name="marca" class="form-control"
                 value="<?= $marca ?>" maxlength="100"
                 placeholder="Ex.: BIC, Faber-Castell">
        </div>

        <!-- Preço de custo (apenas admin vê) -->
        <?php if (ehAdmin()): ?>
        <div class="col-6 col-md-2">
          <label for="preco_custo" class="form-label">Preço Custo *</label>
          <div class="input-group">
            <span class="input-group-text" style="font-size:.8rem;">R$</span>
            <input type="text" id="preco_custo" name="preco_custo"
                   class="form-control mascara-moeda"
                   value="<?= $precoCusto ?>"
                   required placeholder="0,00">
          </div>
        </div>
        <?php endif; ?>

        <!-- Preço de venda -->
        <div class="col-6 col-md-2">
          <label for="preco_venda" class="form-label">Preço Venda *</label>
          <div class="input-group">
            <span class="input-group-text" style="font-size:.8rem;">R$</span>
            <input type="text" id="preco_venda" name="preco_venda"
                   class="form-control mascara-moeda"
                   value="<?= $precoVenda ?>"
                   required placeholder="0,00">
          </div>
        </div>

        <!-- Estoque atual (só na criação; após isso é atualizado pelas movimentações) -->
        <?php if (!$editando): ?>
        <div class="col-6 col-md-2">
          <label for="estoque_atual" class="form-label">Estoque Inicial</label>
          <input type="number" id="estoque_atual" name="estoque_atual"
                 class="form-control" value="<?= $estoqueAtual ?>"
                 min="0" step="1">
        </div>
        <?php endif; ?>

        <!-- Estoque mínimo -->
        <div class="col-6 col-md-2">
          <label for="estoque_minimo" class="form-label">Estoque Mínimo *</label>
          <input type="number" id="estoque_minimo" name="estoque_minimo"
                 class="form-control" value="<?= $estoqueMinimo ?>"
                 min="0" step="1" required>
          <small class="text-secondary">Alerta abaixo deste valor</small>
        </div>

        <!-- Status (apenas edição) -->
        <?php if ($editando): ?>
        <div class="col-12">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox"
                   id="ativo" name="ativo" value="1"
                   <?= $ativo ? 'checked' : '' ?>>
            <label class="form-check-label" for="ativo">Produto ativo</label>
          </div>
        </div>
        <?php endif; ?>

      </div><!-- /row -->

      <hr class="my-4">

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary" id="btn-salvar-produto">
          <i class="bi bi-check-lg me-1"></i>
          <?= $editando ? 'Salvar Alterações' : 'Cadastrar Produto' ?>
        </button>
        <a href="<?= APP_URL ?>/produtos" class="btn btn-outline-secondary">Cancelar</a>
      </div>

    </form>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
