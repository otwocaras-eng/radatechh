<?php
/**
 * UsuariosController.php
 * Gestão de usuários do sistema (somente admin).
 * Implementação completa na Etapa 2 / Etapa 9.
 */

require_once dirname(__DIR__) . '/models/UsuarioModel.php';

class UsuariosController
{
    private UsuarioModel $model;

    public function __construct()
    {
        $this->model = new UsuarioModel();
        exigirAdmin(); // Somente administradores
    }

    // GET /usuarios
    public function index(?int $id = null): void
    {
        $usuarios     = $this->model->listarTodos();
        $tituloPagina = 'Usuários';
        $paginaAtiva  = 'usuarios';
        require_once dirname(__DIR__) . '/views/usuarios/index.php';
    }

    // GET /usuarios/criar
    public function criar(?int $id = null): void
    {
        $tituloPagina = 'Novo Usuário';
        $paginaAtiva  = 'usuarios';
        require_once dirname(__DIR__) . '/views/usuarios/form.php';
    }

    // POST /usuarios/salvar
    public function salvar(?int $id = null): void
    {
        validarCsrf();

        $nome   = limpar($_POST['nome']   ?? '');
        $login  = limpar($_POST['login']  ?? '');
        $senha  = $_POST['senha']          ?? '';
        $perfil = limpar($_POST['perfil'] ?? 'vendedor');
        $erros  = [];

        if (strlen($nome)  < 3)  $erros[] = 'O nome deve ter pelo menos 3 caracteres.';
        if (strlen($login) < 3)  $erros[] = 'O login deve ter pelo menos 3 caracteres.';
        if (strlen($senha) < 8)  $erros[] = 'A senha deve ter pelo menos 8 caracteres.';
        if (!in_array($perfil, ['admin', 'vendedor'])) $erros[] = 'Perfil inválido.';
        if (empty($erros) && $this->model->loginEmUso($login)) {
            $erros[] = 'Este login já está em uso. Escolha outro.';
        }

        if (!empty($erros)) {
            $_SESSION['form_erros'] = $erros;
            $_SESSION['form_dados'] = $_POST;
            redirecionarRota('/usuarios/criar');
        }

        $this->model->criar($nome, $login, $senha, $perfil);
        flashSucesso('Usuário "' . $nome . '" criado com sucesso!');
        redirecionarRota('/usuarios');
    }

    // GET /usuarios/editar/ID
    public function editar(?int $id = null): void
    {
        $usuario = $this->model->buscarPorId($id);
        if (!$usuario) {
            flashErro('Usuário não encontrado.');
            redirecionarRota('/usuarios');
        }
        $tituloPagina = 'Editar Usuário';
        $paginaAtiva  = 'usuarios';
        require_once dirname(__DIR__) . '/views/usuarios/form.php';
    }

    // POST /usuarios/atualizar
    public function atualizar(?int $id = null): void
    {
        validarCsrf();

        $id     = (int) ($_POST['id'] ?? 0);
        $nome   = limpar($_POST['nome']   ?? '');
        $login  = limpar($_POST['login']  ?? '');
        $perfil = limpar($_POST['perfil'] ?? 'vendedor');
        $ativo  = isset($_POST['ativo']) ? 1 : 0;
        $erros  = [];

        if (strlen($nome)  < 3) $erros[] = 'O nome deve ter pelo menos 3 caracteres.';
        if (strlen($login) < 3) $erros[] = 'O login deve ter pelo menos 3 caracteres.';
        if (!in_array($perfil, ['admin','vendedor'])) $erros[] = 'Perfil inválido.';
        if (empty($erros) && $this->model->loginEmUso($login, $id)) {
            $erros[] = 'Este login já está em uso por outro usuário.';
        }

        if (!empty($erros)) {
            $_SESSION['form_erros'] = $erros;
            $_SESSION['form_dados'] = $_POST;
            redirecionarRota('/usuarios/editar/' . $id);
        }

        $this->model->atualizar($id, $nome, $login, $perfil, $ativo);

        // Altera senha se fornecida
        $novaSenha = $_POST['nova_senha'] ?? '';
        if (!empty($novaSenha)) {
            if (strlen($novaSenha) < 8) {
                flashErro('A senha deve ter pelo menos 8 caracteres.');
                redirecionarRota('/usuarios/editar/' . $id);
            }
            $this->model->alterarSenha($id, $novaSenha);
        }

        flashSucesso('Usuário atualizado com sucesso!');
        redirecionarRota('/usuarios');
    }
}
