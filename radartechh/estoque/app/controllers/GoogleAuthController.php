<?php
/**
 * GoogleAuthController.php
 * Implementa o fluxo OAuth 2.0 com Google (sem dependências externas).
 *
 * Fluxo:
 * 1. /google/auth      → Redireciona o usuário para o Google
 * 2. /google/callback  → Google retorna com ?code=xxx, troca pelo token,
 *                        obtém dados do usuário e inicia a sessão
 */

class GoogleAuthController
{
    // ----------------------------------------------------------------
    // Passo 1: redireciona para a tela de seleção de conta do Google
    // GET /google/auth
    // ----------------------------------------------------------------
    public function auth(?int $id = null): void
    {
        // Verifica se as credenciais foram configuradas
        if (
            GOOGLE_CLIENT_ID     === 'COLE_SEU_CLIENT_ID_AQUI' ||
            GOOGLE_CLIENT_SECRET === 'COLE_SEU_CLIENT_SECRET_AQUI'
        ) {
            flashErro('Login com Google não configurado. Consulte o administrador.');
            redirecionar(APP_URL . '/login');
        }

        // State aleatório para prevenir CSRF no OAuth
        $state = bin2hex(random_bytes(16));
        $_SESSION['google_oauth_state'] = $state;

        // Monta a URL de autorização do Google
        $params = http_build_query([
            'client_id'     => GOOGLE_CLIENT_ID,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'access_type'   => 'online',
            'state'         => $state,
            'prompt'        => 'select_account', // Sempre mostra a tela de seleção de conta
        ]);

        redirecionar('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    }

    // ----------------------------------------------------------------
    // Passo 2: Google redireciona de volta com o código de autorização
    // GET /google/callback
    // ----------------------------------------------------------------
    public function callback(?int $id = null): void
    {
        // Verifica parâmetros obrigatórios
        if (isset($_GET['error'])) {
            flashErro('Login com Google cancelado pelo usuário.');
            redirecionar(APP_URL . '/login');
        }

        $code  = $_GET['code']  ?? '';
        $state = $_GET['state'] ?? '';

        // Valida o state para prevenir CSRF
        if (!hash_equals($_SESSION['google_oauth_state'] ?? '', $state)) {
            flashErro('Falha de segurança no login com Google. Tente novamente.');
            redirecionar(APP_URL . '/login');
        }
        unset($_SESSION['google_oauth_state']);

        if (empty($code)) {
            flashErro('Código de autorização não recebido do Google.');
            redirecionar(APP_URL . '/login');
        }

        // ---- Troca o código pelo token de acesso ----
        $tokenData = $this->obterToken($code);

        if (empty($tokenData['access_token'])) {
            flashErro('Não foi possível autenticar com o Google. Tente novamente.');
            redirecionar(APP_URL . '/login');
        }

        // ---- Obtém os dados do usuário Google ----
        $googleUser = $this->obterDadosUsuario($tokenData['access_token']);

        if (empty($googleUser['email'])) {
            flashErro('Não foi possível obter o e-mail da conta Google.');
            redirecionar(APP_URL . '/login');
        }

        $googleSub   = $googleUser['sub']            ?? '';
        $googleEmail = $googleUser['email']          ?? '';
        $googleNome  = $googleUser['name']           ?? $googleEmail;
        $emailVerif  = $googleUser['email_verified'] ?? false;

        // Exige que o e-mail do Google esteja verificado
        if (!$emailVerif) {
            flashErro('Sua conta Google possui e-mail não verificado. Verifique e tente novamente.');
            redirecionar(APP_URL . '/login');
        }

        // ---- Busca ou cria o usuário no banco ----
        $db      = getDB();
        $usuario = $this->buscarPorGoogleSub($db, $googleSub);

        if (!$usuario) {
            // Tenta encontrar pelo e-mail (login = e-mail)
            $usuario = $this->buscarPorEmail($db, $googleEmail);

            if ($usuario) {
                // Vincula o Google Sub ao usuário existente
                $stmt = $db->prepare(
                    'UPDATE usuarios SET google_sub = :sub, google_email = :email WHERE id = :id'
                );
                $stmt->execute([':sub' => $googleSub, ':email' => $googleEmail, ':id' => $usuario['id']]);
            } else {
                // Cria um novo usuário com perfil "vendedor" automaticamente
                $stmt = $db->prepare(
                    'INSERT INTO usuarios (nome, login, senha_hash, perfil, google_sub, google_email)
                     VALUES (:nome, :login, :senha_hash, "vendedor", :sub, :email)'
                );
                // Senha aleatória — o usuário só pode entrar pelo Google
                $senhaHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT);
                $stmt->execute([
                    ':nome'       => $googleNome,
                    ':login'      => $googleEmail,
                    ':senha_hash' => $senhaHash,
                    ':sub'        => $googleSub,
                    ':email'      => $googleEmail,
                ]);
                $novoId  = (int) $db->lastInsertId();
                $usuario = $this->buscarPorId($db, $novoId);
            }
        }

        // Verifica se o usuário está ativo
        if (!$usuario || !(bool) $usuario['ativo']) {
            flashErro('Sua conta está inativa. Contate o administrador.');
            redirecionar(APP_URL . '/login');
        }

        // ---- Inicia sessão segura ----
        session_regenerate_id(true);
        $_SESSION['usuario_id']     = (int)  $usuario['id'];
        $_SESSION['usuario_nome']   =        $usuario['nome'];
        $_SESSION['usuario_login']  =        $usuario['login'];
        $_SESSION['usuario_perfil'] =        $usuario['perfil'];

        flashSucesso('Bem-vindo(a), ' . $usuario['nome'] . '! Acesso via Google.');
        redirecionarRota('/dashboard');
    }

    // ----------------------------------------------------------------
    // Troca o code pelo access_token via cURL
    // ----------------------------------------------------------------
    private function obterToken(string $code): array
    {
        $url  = 'https://oauth2.googleapis.com/token';
        $body = http_build_query([
            'code'          => $code,
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $resposta = curl_exec($ch);
        curl_close($ch);

        return json_decode($resposta, true) ?? [];
    }

    // ----------------------------------------------------------------
    // Obtém perfil do usuário com o access_token
    // ----------------------------------------------------------------
    private function obterDadosUsuario(string $accessToken): array
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $resposta = curl_exec($ch);
        curl_close($ch);

        return json_decode($resposta, true) ?? [];
    }

    // ---- Helpers de banco ----
    private function buscarPorGoogleSub(PDO $db, string $sub): array|false
    {
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE google_sub = :sub LIMIT 1');
        $stmt->execute([':sub' => $sub]);
        return $stmt->fetch();
    }

    private function buscarPorEmail(PDO $db, string $email): array|false
    {
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE login = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    private function buscarPorId(PDO $db, int $id): array|false
    {
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
