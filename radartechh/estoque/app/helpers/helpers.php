<?php
/**
 * helpers.php
 * Funções utilitárias globais usadas em todo o sistema.
 * Inclui formatação, segurança, CSRF e controle de acesso.
 */

// =================================================================
// SEGURANÇA E SAÍDA
// =================================================================

/**
 * Escapa string para exibição segura em HTML (previne XSS).
 */
function e(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Redireciona para uma URL e encerra a execução.
 */
function redirecionar(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Redireciona para uma rota interna da aplicação.
 */
function redirecionarRota(string $rota): void
{
    redirecionar(APP_URL . '/' . ltrim($rota, '/'));
}

// =================================================================
// SESSÃO E CSRF
// =================================================================

/**
 * Inicia a sessão com configurações seguras.
 * Deve ser chamado no front controller (index.php).
 */
function iniciarSessao(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => false, // true em produção com HTTPS
            'httponly' => true,   // Impede acesso via JavaScript
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

/**
 * Gera ou retorna o token CSRF da sessão atual.
 */
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida o token CSRF enviado no POST.
 * Encerra com erro 403 se inválido.
 */
function validarCsrf(): void
{
    $tokenEnviado  = $_POST['csrf_token'] ?? '';
    $tokenSessao   = $_SESSION['csrf_token'] ?? '';

    if (!hash_equals($tokenSessao, $tokenEnviado)) {
        http_response_code(403);
        die('Requisição inválida (token CSRF ausente ou incorreto).');
    }
}

/**
 * Retorna o campo hidden com o token CSRF pronto para usar em formulários.
 */
function camposCsrf(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(tokenCsrf()) . '">';
}

// =================================================================
// CONTROLE DE ACESSO
// =================================================================

/**
 * Verifica se há um usuário logado. Redireciona para login se não.
 */
function exigirLogin(): void
{
    if (empty($_SESSION['usuario_id'])) {
        redirecionar(APP_URL . '/login');
    }
}

/**
 * Exige que o usuário logado seja admin. Redireciona se não for.
 */
function exigirAdmin(): void
{
    exigirLogin();
    if (($_SESSION['usuario_perfil'] ?? '') !== 'admin') {
        $_SESSION['flash_erro'] = 'Acesso negado. Apenas administradores podem acessar esta área.';
        redirecionarRota('/dashboard');
    }
}

/**
 * Retorna true se o usuário logado é admin.
 */
function ehAdmin(): bool
{
    return ($_SESSION['usuario_perfil'] ?? '') === 'admin';
}

/**
 * Retorna o ID do usuário logado.
 */
function usuarioId(): int
{
    return (int) ($_SESSION['usuario_id'] ?? 0);
}

// =================================================================
// MENSAGENS FLASH
// =================================================================

/**
 * Define uma mensagem flash de sucesso.
 */
function flashSucesso(string $msg): void
{
    $_SESSION['flash_sucesso'] = $msg;
}

/**
 * Define uma mensagem flash de erro.
 */
function flashErro(string $msg): void
{
    $_SESSION['flash_erro'] = $msg;
}

/**
 * Retorna e limpa as mensagens flash existentes.
 * Retorna string HTML pronta para exibir.
 */
function exibirFlash(): string
{
    $html = '';
    if (!empty($_SESSION['flash_sucesso'])) {
        $html .= '<div class="alert alert-success alert-dismissible fade show" role="alert">'
               . '<i class="bi bi-check-circle me-2"></i>'
               . e($_SESSION['flash_sucesso'])
               . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['flash_sucesso']);
    }
    if (!empty($_SESSION['flash_erro'])) {
        $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
               . '<i class="bi bi-exclamation-triangle me-2"></i>'
               . e($_SESSION['flash_erro'])
               . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['flash_erro']);
    }
    return $html;
}

// =================================================================
// FORMATAÇÃO
// =================================================================

/**
 * Formata valor decimal como moeda brasileira: R$ 1.234,56
 */
function moeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Formata data/hora do banco (Y-m-d H:i:s) para dd/mm/aaaa HH:MM.
 */
function dataHora(?string $data): string
{
    if (empty($data)) return '-';
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $data);
    return $dt ? $dt->format('d/m/Y H:i') : '-';
}

/**
 * Formata data do banco (Y-m-d) para dd/mm/aaaa.
 */
function data(?string $data): string
{
    if (empty($data)) return '-';
    $dt = DateTime::createFromFormat('Y-m-d', $data);
    return $dt ? $dt->format('d/m/Y') : '-';
}

/**
 * Sanitiza string de entrada removendo espaços extras.
 */
function limpar(string $valor): string
{
    return trim(strip_tags($valor));
}

/**
 * Converte valor monetário do formato brasileiro (1.234,56) para float.
 */
function moedaParaFloat(string $valor): float
{
    $valor = str_replace('.', '', $valor);   // Remove separador de milhar
    $valor = str_replace(',', '.', $valor);  // Troca vírgula por ponto
    return (float) $valor;
}

/**
 * Gera um badge colorido para o status do estoque.
 */
function badgeEstoque(int $atual, int $minimo): string
{
    if ($atual === 0) {
        return '<span class="badge bg-danger">Sem estoque</span>';
    } elseif ($atual <= $minimo) {
        return '<span class="badge bg-warning text-dark">Abaixo do mínimo</span>';
    }
    return '<span class="badge bg-success">OK</span>';
}
