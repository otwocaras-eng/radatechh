<?php
/**
 * ProdutosController.php
 * CRUD de produtos com filtros, paginação e exclusão lógica.
 */

require_once dirname(__DIR__) . '/models/ProdutoModel.php';
require_once dirname(__DIR__) . '/models/CategoriaModel.php';

class ProdutosController
{
    private ProdutoModel   $model;
    private CategoriaModel $modelCategoria;
    private const POR_PAGINA = 20;

    public function __construct()
    {
        $this->model          = new ProdutoModel();
        $this->modelCategoria = new CategoriaModel();
        exigirLogin();
    }

    // GET /produtos
    public function index(?int $id = null): void
    {
        $pagina   = max(1, (int) ($_GET['pagina'] ?? 1));
        $filtros  = [
            'busca'        => limpar($_GET['busca']        ?? ''),
            'categoria_id' => (int) ($_GET['categoria_id'] ?? 0),
            'status'       => limpar($_GET['status']       ?? 'ativo'),
        ];

        $resultado   = $this->model->listar($filtros, $pagina, self::POR_PAGINA);
        $produtos    = $resultado['itens'];
        $total       = $resultado['total'];
        $totalPaginas= (int) ceil($total / self::POR_PAGINA);
        $categorias  = $this->modelCategoria->listar();

        $tituloPagina = 'Produtos';
        $paginaAtiva  = 'produtos';
        require_once dirname(__DIR__) . '/views/produtos/index.php';
    }

    // GET /produtos/ver/ID
    public function ver(?int $id = null): void
    {
        $produto = $this->model->buscarPorId($id);
        if (!$produto) {
            flashErro('Produto não encontrado.');
            redirecionarRota('/produtos');
        }

        // Últimas 10 movimentações do produto
        $db   = getDB();
        $stmt = $db->prepare(
            'SELECT m.tipo, m.quantidade, m.motivo, m.observacao,
                    m.custo_unitario, m.criado_em, u.nome AS usuario_nome,
                    f.nome AS fornecedor_nome
               FROM movimentacoes m
               JOIN usuarios u ON u.id = m.usuario_id
               LEFT JOIN fornecedores f ON f.id = m.fornecedor_id
              WHERE m.produto_id = :pid
              ORDER BY m.criado_em DESC
              LIMIT 10'
        );
        $stmt->execute([':pid' => $id]);
        $movimentacoes = $stmt->fetchAll();

        $tituloPagina = e($produto['nome']);
        $paginaAtiva  = 'produtos';
        require_once dirname(__DIR__) . '/views/produtos/ver.php';
    }

    // GET /produtos/criar
    public function criar(?int $id = null): void
    {
        exigirAdmin();
        $produto    = null;
        $categorias = $this->modelCategoria->listar();
        $tituloPagina = 'Novo Produto';
        $paginaAtiva  = 'produtos';
        require_once dirname(__DIR__) . '/views/produtos/form.php';
    }

    // POST /produtos/salvar
    public function salvar(?int $id = null): void
    {
        exigirAdmin();
        validarCsrf();
        [$dados, $erros] = $this->extrairDados($_POST);

        if (empty($erros) && $dados['codigo_barras'] && $this->model->codigoBarrasEmUso($dados['codigo_barras'])) {
            $erros[] = 'Este código de barras já está cadastrado para outro produto.';
        }

        if (!empty($erros)) {
            $_SESSION['form_erros'] = $erros;
            $_SESSION['form_dados'] = $_POST;
            redirecionarRota('/produtos/criar');
        }

        $novoId = $this->model->criar($dados);
        flashSucesso('Produto cadastrado com sucesso!');
        redirecionarRota('/produtos/ver/' . $novoId);
    }

    // GET /produtos/editar/ID
    public function editar(?int $id = null): void
    {
        exigirAdmin();
        $produto = $this->model->buscarPorId($id);
        if (!$produto) {
            flashErro('Produto não encontrado.');
            redirecionarRota('/produtos');
        }
        $categorias   = $this->modelCategoria->listar();
        $tituloPagina = 'Editar Produto';
        $paginaAtiva  = 'produtos';
        require_once dirname(__DIR__) . '/views/produtos/form.php';
    }

    // POST /produtos/atualizar
    public function atualizar(?int $id = null): void
    {
        exigirAdmin();
        validarCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        [$dados, $erros] = $this->extrairDados($_POST);

        if (empty($erros) && $dados['codigo_barras'] && $this->model->codigoBarrasEmUso($dados['codigo_barras'], $id)) {
            $erros[] = 'Este código de barras já está cadastrado para outro produto.';
        }

        if (!empty($erros)) {
            $_SESSION['form_erros'] = $erros;
            $_SESSION['form_dados'] = $_POST;
            redirecionarRota('/produtos/editar/' . $id);
        }

        $dados['ativo'] = isset($_POST['ativo']) ? 1 : 0;
        $this->model->atualizar($id, $dados);
        flashSucesso('Produto atualizado com sucesso!');
        redirecionarRota('/produtos/ver/' . $id);
    }

    // POST /produtos/inativar/ID — Exclusão lógica
    public function inativar(?int $id = null): void
    {
        exigirAdmin();
        validarCsrf();
        $this->model->inativar($id);
        flashSucesso('Produto inativado com sucesso. O histórico foi preservado.');
        redirecionarRota('/produtos');
    }

    /** Extrai, sanitiza e valida dados do formulário de produto. */
    private function extrairDados(array $post): array
    {
        $dados = [
            'nome'           => limpar($post['nome']           ?? ''),
            'codigo_barras'  => limpar($post['codigo_barras']  ?? ''),
            'categoria_id'   => (int) ($post['categoria_id']   ?? 0),
            'marca'          => limpar($post['marca']           ?? ''),
            'preco_custo'    => moedaParaFloat($post['preco_custo']  ?? '0'),
            'preco_venda'    => moedaParaFloat($post['preco_venda']  ?? '0'),
            'estoque_atual'  => (int) ($post['estoque_atual']  ?? 0),
            'estoque_minimo' => (int) ($post['estoque_minimo'] ?? 0),
            'ativo'          => 1,
        ];

        $erros = [];
        if (strlen($dados['nome']) < 2)      $erros[] = 'O nome deve ter pelo menos 2 caracteres.';
        if (strlen($dados['nome']) > 200)    $erros[] = 'O nome pode ter no máximo 200 caracteres.';
        if ($dados['preco_custo'] < 0)       $erros[] = 'O preço de custo não pode ser negativo.';
        if ($dados['preco_venda'] < 0)       $erros[] = 'O preço de venda não pode ser negativo.';
        if ($dados['estoque_atual']  < 0)    $erros[] = 'O estoque atual não pode ser negativo.';
        if ($dados['estoque_minimo'] < 0)    $erros[] = 'O estoque mínimo não pode ser negativo.';

        return [$dados, $erros];
    }
}
