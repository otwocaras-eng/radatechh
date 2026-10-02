<?php
/**
 * MovimentacoesController.php
 * Gerencia entradas, saídas e listagem de movimentações.
 */

require_once dirname(__DIR__) . '/models/MovimentacaoModel.php';
require_once dirname(__DIR__) . '/models/ProdutoModel.php';
require_once dirname(__DIR__) . '/models/FornecedorModel.php';

class MovimentacoesController
{
    private MovimentacaoModel $model;
    private ProdutoModel      $modelProduto;
    private FornecedorModel   $modelFornecedor;
    private const POR_PAGINA = 25;

    public function __construct()
    {
        $this->model           = new MovimentacaoModel();
        $this->modelProduto    = new ProdutoModel();
        $this->modelFornecedor = new FornecedorModel();
        exigirLogin();
    }

    // GET /movimentacoes — Histórico completo
    public function index(?int $id = null): void
    {
        $pagina  = max(1, (int) ($_GET['pagina'] ?? 1));
        $filtros = [
            'produto_id'  => (int) ($_GET['produto_id']  ?? 0),
            'tipo'        => limpar($_GET['tipo']         ?? ''),
            'data_inicio' => limpar($_GET['data_inicio']  ?? ''),
            'data_fim'    => limpar($_GET['data_fim']     ?? ''),
        ];

        $resultado    = $this->model->listar($filtros, $pagina, self::POR_PAGINA);
        $movimentacoes= $resultado['itens'];
        $total        = $resultado['total'];
        $totalPaginas = (int) ceil($total / self::POR_PAGINA);

        // Produto específico (para o breadcrumb quando vindo de ver produto)
        $produtoFiltro = null;
        if ($filtros['produto_id']) {
            $produtoFiltro = $this->modelProduto->buscarPorId($filtros['produto_id']);
        }

        $tituloPagina = 'Movimentações';
        $paginaAtiva  = 'movimentacoes';
        require_once dirname(__DIR__) . '/views/movimentacoes/index.php';
    }

    // GET /movimentacoes/entrada — Formulário de entrada
    public function entrada(?int $id = null): void
    {
        $fornecedores = $this->modelFornecedor->listar(true);

        // Pré-seleciona produto se vier por parâmetro (ex: da tela de detalhes)
        $produtoPre = null;
        if (!empty($_GET['produto_id'])) {
            $produtoPre = $this->modelProduto->buscarPorId((int) $_GET['produto_id']);
        }

        $tituloPagina = 'Registrar Entrada de Estoque';
        $paginaAtiva  = 'entrada';
        require_once dirname(__DIR__) . '/views/movimentacoes/entrada.php';
    }

    // POST /movimentacoes/processarEntrada — Salva entrada
    public function processarEntrada(?int $id = null): void
    {
        validarCsrf();

        $produtoId    = (int)   ($_POST['produto_id']    ?? 0);
        $quantidade   = (int)   ($_POST['quantidade']    ?? 0);
        $custoUnit    = moedaParaFloat($_POST['custo_unitario'] ?? '0');
        $fornecedorId = (int)   ($_POST['fornecedor_id'] ?? 0);
        $motivo       = limpar($_POST['motivo']          ?? 'Compra');
        $observacao   = limpar($_POST['observacao']      ?? '');
        $erros        = [];

        if ($produtoId <= 0)   $erros[] = 'Selecione ou informe um produto válido.';
        if ($quantidade <= 0)  $erros[] = 'A quantidade deve ser maior que zero.';
        if ($custoUnit < 0)    $erros[] = 'O custo unitário não pode ser negativo.';

        if (!empty($erros)) {
            $_SESSION['form_erros'] = $erros;
            $_SESSION['form_dados'] = $_POST;
            redirecionarRota('/movimentacoes/entrada');
        }

        try {
            $this->model->registrarEntrada([
                'produto_id'    => $produtoId,
                'quantidade'    => $quantidade,
                'custo_unitario'=> $custoUnit,
                'fornecedor_id' => $fornecedorId ?: null,
                'motivo'        => $motivo,
                'observacao'    => $observacao,
                'usuario_id'    => usuarioId(),
            ]);
            flashSucesso("Entrada de {$quantidade} unidade(s) registrada com sucesso!");
            redirecionarRota('/produtos/ver/' . $produtoId);
        } catch (RuntimeException $e) {
            flashErro($e->getMessage());
            redirecionarRota('/movimentacoes/entrada');
        }
    }

    // GET /movimentacoes/saida — Formulário de saída
    public function saida(?int $id = null): void
    {
        $produtoPre = null;
        if (!empty($_GET['produto_id'])) {
            $produtoPre = $this->modelProduto->buscarPorId((int) $_GET['produto_id']);
        }

        $tituloPagina = 'Registrar Saída de Estoque';
        $paginaAtiva  = 'saida';
        require_once dirname(__DIR__) . '/views/movimentacoes/saida.php';
    }

    // POST /movimentacoes/processarSaida — Salva saída
    public function processarSaida(?int $id = null): void
    {
        validarCsrf();

        $produtoId  = (int) ($_POST['produto_id'] ?? 0);
        $quantidade = (int) ($_POST['quantidade']  ?? 0);
        $motivo     = limpar($_POST['motivo']      ?? 'Venda');
        $observacao = limpar($_POST['observacao']  ?? '');
        $erros      = [];

        if ($produtoId <= 0)  $erros[] = 'Selecione um produto válido.';
        if ($quantidade <= 0) $erros[] = 'A quantidade deve ser maior que zero.';
        if (empty($motivo))   $erros[] = 'Informe o motivo da saída.';

        if (!empty($erros)) {
            $_SESSION['form_erros'] = $erros;
            $_SESSION['form_dados'] = $_POST;
            redirecionarRota('/movimentacoes/saida');
        }

        try {
            $this->model->registrarSaida([
                'produto_id' => $produtoId,
                'quantidade' => $quantidade,
                'motivo'     => $motivo,
                'observacao' => $observacao,
                'usuario_id' => usuarioId(),
            ]);
            flashSucesso("Saída de {$quantidade} unidade(s) registrada com sucesso!");
            redirecionarRota('/produtos/ver/' . $produtoId);
        } catch (RuntimeException $e) {
            flashErro($e->getMessage());
            redirecionarRota('/movimentacoes/saida');
        }
    }

    /**
     * AJAX: Retorna dados de um produto pelo ID ou código de barras.
     * Usado nos formulários de entrada/saída para preencher campos automaticamente.
     * GET /movimentacoes/buscarProduto?q=7891010000001
     */
    public function buscarProduto(?int $id = null): void
    {
        exigirLogin();
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
            http_response_code(403); exit;
        }

        $q    = limpar($_GET['q'] ?? '');
        $db   = getDB();
        $stmt = $db->prepare(
            'SELECT id, nome, codigo_barras, marca, estoque_atual, preco_custo, preco_venda
               FROM produtos
              WHERE ativo = 1 AND (codigo_barras = :q OR id = :id)
              LIMIT 1'
        );
        $stmt->execute([':q' => $q, ':id' => (int)$q]);
        $produto = $stmt->fetch();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($produto ?: null, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
