<?php
/**
 * movimentacoes/entrada.php — Formulário de entrada de estoque
 * Suporta leitor de código de barras: foco automático no campo de busca,
 * Enter preenche o produto automaticamente via AJAX.
 */
$paginaAtiva = 'entrada';
require_once dirname(__DIR__) . '/layouts/header.php';

$erros      = $_SESSION['form_erros'] ?? [];
$dadosSalvos= $_SESSION['form_dados'] ?? [];
unset($_SESSION['form_erros'], $_SESSION['form_dados']);

// Produto pré-selecionado (ao vir da tela de detalhes do produto)
$produtoIdPre   = (int)($dadosSalvos['produto_id']   ?? ($produtoPre['id']   ?? 0));
$produtoNomePre = e($dadosSalvos['produto_nome']     ?? ($produtoPre['nome'] ?? ''));
?>

<?php if (!empty($erros)): ?>
<div class="alert alert-danger">
  <i class="bi bi-exclamation-triangle me-2"></i>
  <?php foreach ($erros as $e): ?><?= e($e) ?><br><?php endforeach; ?>
</div>
<?php endif; ?>

<h2 class="h5 fw-700 mb-3">
  <i class="bi bi-box-arrow-in-down text-success me-2"></i>
  Entrada de Estoque
</h2>

<div class="card" style="max-width:650px;">
  <div class="card-body">
    <form method="POST"
          action="<?= APP_URL ?>/movimentacoes/processarEntrada"
          id="form-entrada"
          novalidate>
      <?= camposCsrf() ?>

      <!-- Busca do produto via código de barras ou nome -->
      <div class="mb-3">
        <label for="busca-produto-entrada" class="form-label">
          <i class="bi bi-upc-scan me-1"></i>Produto *
          <small class="text-secondary fw-400">(nome ou código de barras)</small>
        </label>
        <div class="busca-rapida-wrapper" style="position:relative;">
          <i class="bi bi-upc-scan busca-icon"></i>
          <input type="text"
                 id="busca-produto-entrada"
                 class="form-control"
                 placeholder="Digite o nome ou aponte o leitor de código de barras…"
                 value="<?= $produtoNomePre ?>"
                 autocomplete="off"
                 autofocus>
          <div id="resultado-busca-entrada" class="resultado-busca-form"></div>
        </div>
        <!-- Campo hidden com o ID real do produto selecionado -->
        <input type="hidden" id="produto_id" name="produto_id" value="<?= $produtoIdPre ?>">
        <input type="hidden" id="produto_nome_hidden" name="produto_nome" value="<?= $produtoNomePre ?>">
      </div>

      <!-- Resumo do produto selecionado -->
      <div id="resumo-produto" class="alert alert-info py-2 d-none mb-3" style="font-size:.85rem;">
        <i class="bi bi-info-circle me-2"></i>
        <span id="resumo-produto-texto"></span>
      </div>

      <div class="row g-3">
        <div class="col-6 col-md-3">
          <label for="quantidade" class="form-label">Quantidade *</label>
          <input type="number" id="quantidade" name="quantidade" class="form-control"
                 value="<?= e($dadosSalvos['quantidade'] ?? '') ?>"
                 min="1" step="1" required placeholder="0">
        </div>

        <div class="col-6 col-md-3">
          <label for="custo_unitario" class="form-label">Custo Unitário</label>
          <div class="input-group">
            <span class="input-group-text" style="font-size:.8rem;">R$</span>
            <input type="text" id="custo_unitario" name="custo_unitario"
                   class="form-control mascara-moeda"
                   value="<?= e($dadosSalvos['custo_unitario'] ?? '') ?>"
                   placeholder="0,00">
          </div>
        </div>

        <div class="col-12 col-md-6">
          <label for="fornecedor_id" class="form-label">Fornecedor</label>
          <select id="fornecedor_id" name="fornecedor_id" class="form-select">
            <option value="">— Selecione —</option>
            <?php foreach ($fornecedores as $f): ?>
              <option value="<?= $f['id'] ?>"
                <?= ($dadosSalvos['fornecedor_id'] ?? '') == $f['id'] ? 'selected' : '' ?>>
                <?= e($f['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 col-md-6">
          <label for="motivo" class="form-label">Motivo</label>
          <select id="motivo" name="motivo" class="form-select">
            <option value="Compra"      <?= ($dadosSalvos['motivo'] ?? 'Compra') === 'Compra'      ? 'selected' : '' ?>>Compra</option>
            <option value="Devolução"   <?= ($dadosSalvos['motivo'] ?? '')        === 'Devolução'  ? 'selected' : '' ?>>Devolução de cliente</option>
            <option value="Ajuste"      <?= ($dadosSalvos['motivo'] ?? '')        === 'Ajuste'     ? 'selected' : '' ?>>Ajuste de inventário</option>
            <option value="Transferência"<?= ($dadosSalvos['motivo'] ?? '')       === 'Transferência' ? 'selected' : '' ?>>Transferência</option>
          </select>
        </div>

        <div class="col-12 col-md-6">
          <label for="observacao" class="form-label">Observação</label>
          <input type="text" id="observacao" name="observacao" class="form-control"
                 value="<?= e($dadosSalvos['observacao'] ?? '') ?>"
                 maxlength="255" placeholder="Opcional">
        </div>
      </div>

      <hr class="my-4">

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success" id="btn-salvar-entrada">
          <i class="bi bi-check-lg me-1"></i>Confirmar Entrada
        </button>
        <a href="<?= APP_URL ?>/movimentacoes" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<style>
.resultado-busca-form {
  position: absolute; top: calc(100% + 4px); left: 0; right: 0;
  background: #fff; border: 1px solid var(--border-color);
  border-radius: 12px; box-shadow: var(--shadow-lg);
  z-index: 1000; max-height: 280px; overflow-y: auto; display: none;
}
.resultado-busca-form.visivel { display: block; }
</style>

<script>
(function () {
  const inputBusca = document.getElementById('busca-produto-entrada');
  const resultado  = document.getElementById('resultado-busca-entrada');
  const hiddenId   = document.getElementById('produto_id');
  const resumoBox  = document.getElementById('resumo-produto');
  const resumoTxt  = document.getElementById('resumo-produto-texto');
  let   timeoutId  = null;

  // Se já tiver produto pré-selecionado, mostra o resumo
  if (hiddenId.value > 0) carregarResumoProduto(hiddenId.value);

  inputBusca.addEventListener('input', function () {
    clearTimeout(timeoutId);
    hiddenId.value = 0;
    esconderResumo();
    const termo = this.value.trim();
    if (termo.length < 2) { fechar(); return; }
    timeoutId = setTimeout(() => buscar(termo), 250);
  });

  inputBusca.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); clearTimeout(timeoutId); buscar(this.value.trim(), true); }
    if (e.key === 'Escape') fechar();
  });

  document.addEventListener('click', e => {
    if (!inputBusca.contains(e.target) && !resultado.contains(e.target)) fechar();
  });

  function buscar(termo, focoUnico = false) {
    if (termo.length < 2) return;
    resultado.innerHTML = '<div class="resultado-item text-secondary"><i class="bi bi-hourglass-split me-2"></i>Buscando...</div>';
    resultado.classList.add('visivel');

    fetch(APP_URL + '/busca/ajax?q=' + encodeURIComponent(termo), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(r => r.json()).then(dados => {
      if (!dados.length) {
        resultado.innerHTML = '<div class="resultado-item text-secondary">Nenhum produto encontrado.</div>';
        return;
      }
      if (focoUnico && dados.length === 1) { selecionarProduto(dados[0]); return; }
      resultado.innerHTML = dados.map(p => `
        <div class="resultado-item" onclick="selecionarProdutoGlobal(${p.id}, '${escapar(p.nome)}', ${p.estoque_atual}, '${p.preco_venda_fmt}')">
          <div>
            <div class="resultado-item-nome">${escaparHtml(p.nome)}</div>
            <div class="resultado-item-marca">${escaparHtml(p.marca||'')} | Estoque: ${p.estoque_atual}</div>
          </div>
          <div class="resultado-item-preco">${p.preco_venda_fmt}</div>
        </div>`).join('');
    }).catch(() => {
      resultado.innerHTML = '<div class="resultado-item text-danger">Erro ao buscar.</div>';
    });
  }

  window.selecionarProdutoGlobal = function(id, nome, estoque, preco) {
    hiddenId.value      = id;
    inputBusca.value    = nome;
    resumoTxt.textContent = `Estoque atual: ${estoque} un. | Preço de venda: ${preco}`;
    resumoBox.classList.remove('d-none');
    fechar();
    document.getElementById('quantidade').focus();
  };

  function selecionarProduto(p) {
    selecionarProdutoGlobal(p.id, p.nome, p.estoque_atual, p.preco_venda_fmt);
  }

  function fechar() { resultado.classList.remove('visivel'); resultado.innerHTML = ''; }
  function esconderResumo() { resumoBox.classList.add('d-none'); }

  function carregarResumoProduto(id) {
    fetch(APP_URL + '/movimentacoes/buscarProduto?q=' + id, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(r => r.json()).then(p => {
      if (p) {
        resumoTxt.textContent = `Estoque atual: ${p.estoque_atual} un.`;
        resumoBox.classList.remove('d-none');
      }
    });
  }

  function escapar(s) { return s.replace(/'/g, "\\'"); }
  function escaparHtml(s) { const d=document.createElement('div'); d.appendChild(document.createTextNode(s)); return d.innerHTML; }
})();
</script>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
