<?php
/**
 * CategoriasController.php
 * CRUD de categorias — acessível apenas por administradores.
 */

require_once dirname(__DIR__) . '/models/CategoriaModel.php';

class CategoriasController
{
    private CategoriaModel $model;

    public function __construct()
    {
        $this->model = new CategoriaModel();
        exigirAdmin();
    }

    // GET /categorias
    public function index(?int $id = null): void
    {
        $categorias   = $this->model->listar();
        $tituloPagina = 'Categorias';
        $paginaAtiva  = 'categorias';
        require_once dirname(__DIR__) . '/views/categorias/index.php';
    }

    // GET /categorias/criar
    public function criar(?int $id = null): void
    {
        $categoria    = null;
        $tituloPagina = 'Nova Categoria';
        $paginaAtiva  = 'categorias';
        require_once dirname(__DIR__) . '/views/categorias/form.php';
    }

    // POST /categorias/salvar
    public function salvar(?int $id = null): void
    {
        validarCsrf();
        $nome  = limpar($_POST['nome'] ?? '');
        $erros = $this->validar($nome);

        if (empty($erros) && $this->model->nomeEmUso($nome)) {
            $erros[] = 'Já existe uma categoria com este nome.';
        }

        if (!empty($erros)) {
            $_SESSION['form_erros'] = $erros;
            $_SESSION['form_dados'] = $_POST;
            redirecionarRota('/categorias/criar');
        }

        $this->model->criar($nome);
        flashSucesso('Categoria "' . $nome . '" criada com sucesso!');
        redirecionarRota('/categorias');
    }

    // GET /categorias/editar/ID
    public function editar(?int $id = null): void
    {
        $categoria = $this->model->buscarPorId($id);
        if (!$categoria) {
            flashErro('Categoria não encontrada.');
            redirecionarRota('/categorias');
        }
        $tituloPagina = 'Editar Categoria';
        $paginaAtiva  = 'categorias';
        require_once dirname(__DIR__) . '/views/categorias/form.php';
    }

    // POST /categorias/atualizar
    public function atualizar(?int $id = null): void
    {
        validarCsrf();
        $id    = (int) ($_POST['id'] ?? 0);
        $nome  = limpar($_POST['nome'] ?? '');
        $erros = $this->validar($nome);

        if (empty($erros) && $this->model->nomeEmUso($nome, $id)) {
            $erros[] = 'Já existe outra categoria com este nome.';
        }

        if (!empty($erros)) {
            $_SESSION['form_erros'] = $erros;
            $_SESSION['form_dados'] = $_POST;
            redirecionarRota('/categorias/editar/' . $id);
        }

        $this->model->atualizar($id, $nome);
        flashSucesso('Categoria atualizada com sucesso!');
        redirecionarRota('/categorias');
    }

    // POST /categorias/excluir/ID
    public function excluir(?int $id = null): void
    {
        validarCsrf();
        $resultado = $this->model->excluir($id);
        if ($resultado === true) {
            flashSucesso('Categoria excluída com sucesso!');
        } else {
            flashErro($resultado);
        }
        redirecionarRota('/categorias');
    }

    // Valida nome da categoria
    private function validar(string $nome): array
    {
        $erros = [];
        if (strlen($nome) < 2)   $erros[] = 'O nome deve ter pelo menos 2 caracteres.';
        if (strlen($nome) > 100) $erros[] = 'O nome pode ter no máximo 100 caracteres.';
        return $erros;
    }
}
