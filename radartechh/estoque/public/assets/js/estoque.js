/**
 * estoque.js
 * Scripts globais do sistema de controle de estoque.
 * Sem dependências além do Bootstrap 5.
 */

/* ================================================================
   1. Toggle da sidebar em dispositivos móveis
   ================================================================ */
document.addEventListener('DOMContentLoaded', function () {
  const sidebar       = document.getElementById('sidebar');
  const overlay       = document.getElementById('sidebarOverlay');
  const toggleBtns    = document.querySelectorAll('.topbar-toggle, .sidebar-toggle');

  function abrirSidebar() {
    sidebar?.classList.add('aberta');
    overlay?.classList.add('visivel');
    document.body.style.overflow = 'hidden';
  }

  function fecharSidebar() {
    sidebar?.classList.remove('aberta');
    overlay?.classList.remove('visivel');
    document.body.style.overflow = '';
  }

  toggleBtns.forEach(btn => btn.addEventListener('click', function () {
    sidebar?.classList.contains('aberta') ? fecharSidebar() : abrirSidebar();
  }));

  overlay?.addEventListener('click', fecharSidebar);

  // Fecha sidebar ao clicar em um link (mobile)
  sidebar?.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function () {
      if (window.innerWidth < 992) fecharSidebar();
    });
  });
});

/* ================================================================
   2. Marcar link ativo na sidebar
   ================================================================ */
document.addEventListener('DOMContentLoaded', function () {
  const caminho = window.location.pathname;
  document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
    const href = link.getAttribute('href') || '';
    // Remove o link "dashboard" de ficar ativo em outras páginas
    const segmentos = caminho.split('/').filter(Boolean);
    const modulo    = segmentos[segmentos.length - 1] || 'dashboard';

    if (href.includes(modulo) && modulo !== '') {
      link.classList.add('active');
    }
  });
});

/* ================================================================
   3. Busca rápida via AJAX (código de barras ou nome)
   ================================================================ */
(function () {
  const inputBusca   = document.getElementById('busca-rapida');
  const resultado    = document.getElementById('resultado-busca');

  if (!inputBusca || !resultado) return;

  // Foco automático no campo de busca
  inputBusca.focus();

  let timeoutId = null;

  inputBusca.addEventListener('input', function () {
    clearTimeout(timeoutId);
    const termo = this.value.trim();

    if (termo.length < 2) {
      fecharResultado();
      return;
    }

    timeoutId = setTimeout(() => executarBusca(termo), 250);
  });

  // Leitor de código de barras envia Enter automaticamente
  inputBusca.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      clearTimeout(timeoutId);
      const termo = this.value.trim();
      if (termo.length >= 2) executarBusca(termo, true);
    }
    if (e.key === 'Escape') fecharResultado();
  });

  // Fecha ao clicar fora
  document.addEventListener('click', function (e) {
    if (!inputBusca.contains(e.target) && !resultado.contains(e.target)) {
      fecharResultado();
    }
  });

  function executarBusca(termo, focoUnico = false) {
    const url = APP_URL + '/busca/ajax?q=' + encodeURIComponent(termo);

    resultado.innerHTML = '<div class="resultado-item text-secondary"><i class="bi bi-hourglass-split me-2"></i>Buscando...</div>';
    resultado.classList.add('visivel');

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(dados => renderizarResultado(dados, focoUnico))
      .catch(() => {
        resultado.innerHTML = '<div class="resultado-item text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Erro ao buscar.</div>';
      });
  }

  function renderizarResultado(dados, focoUnico) {
    if (!dados.length) {
      resultado.innerHTML = '<div class="resultado-item text-secondary"><i class="bi bi-search me-2"></i>Nenhum produto encontrado.</div>';
      return;
    }

    // Se leitor de barras encontrou exatamente 1 produto, abre a página dele
    if (focoUnico && dados.length === 1) {
      window.location.href = APP_URL + '/produtos/ver/' + dados[0].id;
      return;
    }

    resultado.innerHTML = dados.map(p => {
      const estoqueClass = p.estoque_atual === 0
        ? 'text-danger'
        : (p.estoque_atual <= p.estoque_minimo ? 'text-warning' : 'text-success');

      const estoqueIcon = p.estoque_atual === 0
        ? 'bi-x-circle'
        : (p.estoque_atual <= p.estoque_minimo ? 'bi-exclamation-triangle' : 'bi-check-circle');

      return `
        <div class="resultado-item" onclick="window.location='${APP_URL}/produtos/ver/${p.id}'">
          <div>
            <div class="resultado-item-nome">${escaparHtml(p.nome)}</div>
            <div class="resultado-item-marca">${escaparHtml(p.marca || '')} ${escaparHtml(p.categoria || '')}</div>
          </div>
          <div class="text-end">
            <div class="resultado-item-preco">${p.preco_venda_fmt}</div>
            <div class="resultado-item-estoque ${estoqueClass}">
              <i class="bi ${estoqueIcon}"></i> ${p.estoque_atual} un.
            </div>
          </div>
        </div>`;
    }).join('');
  }

  function fecharResultado() {
    resultado.classList.remove('visivel');
    resultado.innerHTML = '';
  }

  function escaparHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
  }
})();

/* ================================================================
   4. Confirmação antes de excluir / inativar
   ================================================================ */
document.addEventListener('click', function (e) {
  const btn = e.target.closest('[data-confirmar]');
  if (!btn) return;
  const msg = btn.dataset.confirmar || 'Tem certeza que deseja realizar esta ação?';
  if (!confirm(msg)) e.preventDefault();
});

/* ================================================================
   5. Máscaras de entrada para valores monetários
   ================================================================ */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.mascara-moeda').forEach(input => {
    input.addEventListener('input', function () {
      let v = this.value.replace(/\D/g, '');
      v = (parseInt(v, 10) / 100).toFixed(2);
      v = v.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      this.value = v;
    });

    input.addEventListener('focus', function () {
      if (this.value === '' || this.value === '0,00') this.value = '';
    });
  });
});

/* ================================================================
   6. Auto-dismiss de alertas após 5 segundos
   ================================================================ */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.alert.alert-success, .alert.alert-info').forEach(el => {
    setTimeout(() => {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
      bsAlert?.close();
    }, 5000);
  });
});

/* ================================================================
   7. Feedback visual no botão de submit (previne duplo envio)
   ================================================================ */
document.addEventListener('submit', function (e) {
  const form = e.target;
  const btn  = form.querySelector('button[type="submit"]');
  if (!btn) return;

  // Só bloqueia se o formulário for válido
  if (form.checkValidity()) {
    btn.disabled = true;
    const textoOriginal = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Aguarde...';

    // Restaura após 8 segundos (caso haja erro de rede)
    setTimeout(() => {
      btn.disabled  = false;
      btn.innerHTML = textoOriginal;
    }, 8000);
  }
});
