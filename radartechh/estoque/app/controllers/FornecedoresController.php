<?php
/**
 * FornecedoresController.php
 * CRUD de fornecedores — acessível por admin.
 */

require_once dirname(__DIR__) . '/models/FornecedorModel.php';

class FornecedoresController
{
    private FornecedorModel $model;

    public function __construct()
    {
        $this->model = new FornecedorModel();
        exigirAdmin();
    }

    // GET /fornecedores
    public function index(?int $id = null): void
    {
        $fornecedores = $this->model->listar();
        $tituloPagina = 'Fornecedores';
        $paginaAtiva  = 'fornecedores';
        require_once dirname(__DIR__) . '/views/fornecedores/index.php';
    }

    // GET /fornecedores/criar
    public function criar(?int $id = null): void
    {
        $fornecedor   = null;
        $tituloPagina = 'Novo Fornecedor';
        $paginaAtiva  = 'fornecedores';
        require_once dirname(__DIR__) . '/views/fornecedores/form.php';
    }

    // POST /fornecedores/salvar
    public function salvar(?int $id = null): void
    {
        validarCsrf();
        [$nome, $telefone, $email, $erros] = $this->extrairDados($_POST);

        if (!empty($erros)) {
            $_SESSION['form_erros'] = $erros;
            $_SESSION['form_dados'] = $_POST;
            redirecionarRota('/fornecedores/criar');
        }

        $this->model->criar($nome, $telefone, $email);
        flashSucesso('Fornecedor "' . $nome . '" cadastrado com sucesso!');
        redirecionarRota('/fornecedores');
    }

    // GET /fornecedores/editar/ID
    public function editar(?int $id = null): void
    {
        $fornecedor = $this->model->buscarPorId($id);
        if (!$fornecedor) {
            flashErro('Fornecedor não encontrado.');
            redirecionarRota('/fornecedores');
        }
        $tituloPagina = 'Editar Fornecedor';
        $paginaAtiva  = 'fornecedores';
        require_once dirname(__DIR__) . '/views/fornecedores/form.php';
    }

    // POST /fornecedores/atualizar
    public function atualizar(?int $id = null): void
    {
        validarCsrf();
        $id    = (int) ($_POST['id'] ?? 0);
        [$nome, $telefone, $email, $erros] = $this->extrairDados($_POST);

        if (!empty($erros)) {
            $_SESSION['form_erros'] = $erros;
            $_SESSION['form_dados'] = $_POST;
            redirecionarRota('/fornecedores/editar/' . $id);
        }

        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $this->model->atualizar($id, $nome, $telefone, $email, $ativo);
        flashSucesso('Fornecedor atualizado com sucesso!');
        redirecionarRota('/fornecedores');
    }

    // POST /fornecedores/excluir/ID
    public function excluir(?int $id = null): void
    {
        validarCsrf();
        $resultado = $this->model->excluir($id);
        if ($resultado === true) {
            flashSucesso('Fornecedor excluído com sucesso!');
        } else {
            flashErro($resultado);
        }
        redirecionarRota('/fornecedores');
    }

    /** Extrai e valida dados do formulário. */
    private function extrairDados(array $post): array
    {
        $nome     = limpar($post['nome']     ?? '');
        $telefone = limpar($post['telefone'] ?? '');
        $email    = limpar($post['email']    ?? '');
        $erros    = [];

        if (strlen($nome) < 3)   $erros[] = 'O nome deve ter pelo menos 3 caracteres.';
        if (strlen($nome) > 150) $erros[] = 'O nome pode ter no máximo 150 caracteres.';
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'E-mail inválido.';
        }

        return [$nome, $telefone, $email, $erros];
    }
}
