<?php
/**
 * movimentacoes/saida.php — Formulário de saída de estoque
 */
$paginaAtiva = 'saida';
require_once dirname(__DIR__) . '/layouts/header.php';

$erros      = $_SESSION['form_erros'] ?? [];
$dadosSalvos= $_SESSION['form_dados'] ?? [];
unset($_SESSION['form_erros'], $_SESSION['form_dados']);

$produtoIdPre   = (int)(  $dadosSalvos['produto_id']   ?? ($produtoPre['id']    ?? 0));
$produtoNomePre = e($dadosSalvos['produto_nome']         ?? ($produtoPre['nome']  ?? ''));
$estoqueAtualPre= (int)(  $dadosSalvos['estoque_atual'] ?? ($produtoPre['estoque_atual'] ?? 0));
?>

<?php if (!empty($erros)): ?>
<div class="alert alert-danger">
  <i class="bi bi-exclamation-triangle me-2"></i>
  <?php foreach ($erros as $e): ?><?= e($e) ?><br><?php endforeach; ?>
</div>
<?php endif; ?>

<h2 class="h5 fw-700 mb-3">
  <i class="bi bi-box-arrow-up text-danger me-2"></i>
  Saída de Estoque
</h2>

<div class="card" style="max-width:600px;">
  <div class="card-body">
    <form method="POST"
          action="<?= APP_URL ?>/movimentacoes/processarSaida"
          id="form-saida"
          novalidate>
      <?= camposCsrf() ?>

      <!-- Busca do produto -->
      <div class="mb-3">
        <label for="busca-produto-saida" class="form-label">
          <i class="bi bi-upc-scan me-1"></i>Produto *
        </label>
        <div class="busca-rapida-wrapper" style="position:relative;">
          <i class="bi bi-upc-scan busca-icon"></i>
          <input type="text"
                 id="busca-produto-saida"
                 class="form-control"
                 placeholder="Digite o nome ou aponte o leitor de código de barras…"
                 value="<?= $produtoNomePre ?>"
                 autocomplete="off"
                 autofocus>
          <div id="resultado-busca-saida" class="resultado-busca-form"></div>
        </div>
        <input type="hidden" id="produto_id"   name="produto_id"   value="<?= $produtoIdPre ?>">
        <input type="hidden" id="estoque_atual" name="estoque_atual" value="<?= $estoqueAtualPre ?>">
      </div>

      <!-- Resumo do estoque disponível -->
      <div id="resumo-saida" class="alert py-2 mb-3 <?= $produtoIdPre ? '' : 'd-none' ?>"
           style="font-size:.85rem;"
           id="resumo-saida-box">
        <i class="bi bi-info-circle me-2"></i>
        Estoque disponível: <strong id="estoque-disp"><?= $estoqueAtualPre ?></strong> unidade(s)
      </div>

      <div class="row g-3">
        <div class="col-6 col-md-4">
          <label for="quantidade" class="form-label">Quantidade *</label>
          <input type="number" id="quantidade" name="quantidade" class="form-control"
                 value="<?= e($dadosSalvos['quantidade'] ?? '') ?>"
                 min="1" step="1" required placeholder="0">
        </div>

        <div class="col-12 col-md-8">
          <label for="motivo" class="form-label">Motivo *</label>
          <select id="motivo" name="motivo" class="form-select" required>
            <option value="Venda"         <?= ($dadosSalvos['motivo'] ?? 'Venda') === 'Venda'         ? 'selected' : '' ?>>Venda</option>
            <option value="Perda"         <?= ($dadosSalvos['motivo'] ?? '')       === 'Perda'         ? 'selected' : '' ?>>Perda / Avaria</option>
            <option value="Devolução"     <?= ($dadosSalvos['motivo'] ?? '')       === 'Devolução'     ? 'selected' : '' ?>>Devolução ao fornecedor</option>
            <option value="Uso interno"   <?= ($dadosSalvos['motivo'] ?? '')       === 'Uso interno'   ? 'selected' : '' ?>>Uso interno</option>
            <option value="Ajuste"        <?= ($dadosSalvos['motivo'] ?? '')       === 'Ajuste'        ? 'selected' : '' ?>>Ajuste de inventário</option>
            <option value="Transferência" <?= ($dadosSalvos['motivo'] ?? '')       === 'Transferência' ? 'selected' : '' ?>>Transferência</option>
          </select>
        </div>

        <div class="col-12">
          <label for="observacao" class="form-label">Observação</label>
          <input type="text" id="observacao" name="observacao" class="form-control"
                 value="<?= e($dadosSalvos['observacao'] ?? '') ?>"
                 maxlength="255" placeholder="Opcional">
        </div>
      </div>

      <hr class="my-4">

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-danger" id="btn-salvar-saida">
          <i class="bi bi-check-lg me-1"></i>Confirmar Saída
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
  const inputBusca  = document.getElementById('busca-produto-saida');
  const resultado   = document.getElementById('resultado-busca-saida');
  const hiddenId    = document.getElementById('produto_id');
  const hiddenEst   = document.getElementById('estoque_atual');
  const resumoBox   = document.getElementById('resumo-saida');
  const estDisp     = document.getElementById('estoque-disp');
  const inputQtd    = document.getElementById('quantidade');
  let   timeoutId   = null;

  inputBusca.addEventListener('input', function () {
    clearTimeout(timeoutId);
    hiddenId.value = 0;
    resumoBox.classList.add('d-none');
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

  // Valida quantidade contra o estoque disponível em tempo real
  inputQtd.addEventListener('input', function () {
    const disp = parseInt(hiddenEst.value || '0');
    const qtd  = parseInt(this.value || '0');
    if (disp > 0 && qtd > disp) {
      this.setCustomValidity('Quantidade maior que o estoque disponível (' + disp + ').');
    } else {
      this.setCustomValidity('');
    }
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
      if (focoUnico && dados.length === 1) { selecionar(dados[0]); return; }
      resultado.innerHTML = dados.map(p => {
        const cls = p.estoque_atual === 0 ? 'text-danger' : (p.estoque_atual <= p.estoque_minimo ? 'text-warning' : 'text-success');
        return `<div class="resultado-item" onclick="selecionarSaida(${p.id}, '${esc(p.nome)}', ${p.estoque_atual})">
          <div>
            <div class="resultado-item-nome">${escHtml(p.nome)}</div>
            <div class="resultado-item-marca">${escHtml(p.marca||'')}</div>
          </div>
          <div class="text-end">
            <div class="${cls} fw-700">${p.estoque_atual} un.</div>
          </div>
        </div>`;
      }).join('');
    }).catch(() => {
      resultado.innerHTML = '<div class="resultado-item text-danger">Erro ao buscar.</div>';
    });
  }

  window.selecionarSaida = function(id, nome, estoque) {
    hiddenId.value  = id;
    hiddenEst.value = estoque;
    inputBusca.value = nome;
    estDisp.textContent = estoque;
    resumoBox.className = 'alert py-2 mb-3 ' + (estoque === 0 ? 'alert-danger' : (estoque <= 5 ? 'alert-warning' : 'alert-info'));
    resumoBox.classList.remove('d-none');
    fechar();
    inputQtd.focus();
  };

  function selecionar(p) { selecionarSaida(p.id, p.nome, p.estoque_atual); }
  function fechar() { resultado.classList.remove('visivel'); resultado.innerHTML = ''; }
  function esc(s) { return s.replace(/'/g, "\\'"); }
  function escHtml(s) { const d=document.createElement('div'); d.appendChild(document.createTextNode(s)); return d.innerHTML; }
})();
</script>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>
