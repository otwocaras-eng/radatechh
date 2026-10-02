<?php
/**
 * AuthController.php
 * Gerencia autenticação: exibir login, processar login e logout.
 */

require_once dirname(__DIR__) . '/models/UsuarioModel.php';

class AuthController
{
    private UsuarioModel $model;

    public function __construct()
    {
        $this->model = new UsuarioModel();
    }

    // -----------------------------------------------------------------
    // GET /login — Exibe o formulário de login
    // -----------------------------------------------------------------
    public function index(?int $id = null): void
    {
        // Se já está logado, redireciona para o dashboard
        if (!empty($_SESSION['usuario_id'])) {
            redirecionarRota('/dashboard');
        }
        require_once dirname(__DIR__) . '/views/auth/login.php';
    }

    // Alias para a rota /login
    public function login(?int $id = null): void
    {
        $this->index($id);
    }

    // -----------------------------------------------------------------
    // POST /login/processar — Valida credenciais e inicia sessão
    // -----------------------------------------------------------------
    public function processar(?int $id = null): void
    {
        validarCsrf();

        $loginInput = limpar($_POST['login']  ?? '');
        $senhaInput = limpar($_POST['senha']  ?? '');
        $erros      = [];

        // Validação básica dos campos
        if (empty($loginInput)) {
            $erros[] = 'Informe o login.';
        }
        if (empty($senhaInput)) {
            $erros[] = 'Informe a senha.';
        }

        if (empty($erros)) {
            $usuario = $this->model->buscarPorLogin($loginInput);

            // Verifica credenciais e status ativo
            if (
                $usuario === false
                || !password_verify($senhaInput, $usuario['senha_hash'])
                || !(bool) $usuario['ativo']
            ) {
                // Mensagem genérica para não revelar qual campo está errado
                $erros[] = 'Login ou senha inválidos. Verifique e tente novamente.';

                // Pequeno atraso para dificultar brute-force
                sleep(1);
            }
        }

        if (!empty($erros)) {
            $_SESSION['login_erros']  = $erros;
            $_SESSION['login_ultimo'] = $loginInput;
            redirecionar(APP_URL . '/login');
        }

        // ----- Sessão segura -----
        // Regenera o ID de sessão para prevenir fixação de sessão
        session_regenerate_id(true);

        $_SESSION['usuario_id']     = (int) $usuario['id'];
        $_SESSION['usuario_nome']   = $usuario['nome'];
        $_SESSION['usuario_login']  = $usuario['login'];
        $_SESSION['usuario_perfil'] = $usuario['perfil'];

        flashSucesso('Bem-vindo(a), ' . $usuario['nome'] . '!');
        redirecionarRota('/dashboard');
    }

    // -----------------------------------------------------------------
    // GET /logout — Encerra a sessão
    // -----------------------------------------------------------------
    public function logout(?int $id = null): void
    {
        // Limpa todas as variáveis de sessão
        $_SESSION = [];

        // Apaga o cookie de sessão
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        redirecionar(APP_URL . '/login');
    }
}
