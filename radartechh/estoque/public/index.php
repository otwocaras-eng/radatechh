<?php
/**
 * index.php — Front Controller
 * Ponto de entrada único da aplicação.
 * Lê a URL, determina o controller/action e despacha a requisição.
 *
 * URL amigável esperada: /public/modulo/acao/id
 * Exemplo: /public/produtos/editar/3
 */

// -----------------------------------------------------------------
// 1. Bootstrap: configurações, conexão e helpers
// -----------------------------------------------------------------
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/helpers/helpers.php';

// -----------------------------------------------------------------
// 2. Inicia a sessão segura
// -----------------------------------------------------------------
iniciarSessao();

// -----------------------------------------------------------------
// 3. Roteamento simples baseado em segmentos da URL
// -----------------------------------------------------------------

// Remove o prefixo da APP_URL para obter apenas o caminho da rota
$caminhoBase = parse_url(APP_URL, PHP_URL_PATH); // ex: /radartechh/estoque/public
$uri         = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rota        = '/' . trim(substr($uri, strlen($caminhoBase)), '/');

// Divide a rota em segmentos: [modulo, acao, id]
$segmentos = explode('/', trim($rota, '/'));
$modulo    = $segmentos[0] ?? 'dashboard';
$acao      = $segmentos[1] ?? 'index';
$id        = isset($segmentos[2]) ? (int) $segmentos[2] : null;

// -----------------------------------------------------------------
// 4. Mapa de rotas: modulo => arquivo do controller
// -----------------------------------------------------------------
$controllers = [
    ''               => 'DashboardController',
    'dashboard'      => 'DashboardController',
    'login'          => 'AuthController',
    'logout'         => 'AuthController',
    'google'         => 'GoogleAuthController',   // OAuth 2.0 Google
    'categorias'     => 'CategoriasController',
    'fornecedores'   => 'FornecedoresController',
    'produtos'       => 'ProdutosController',
    'movimentacoes'  => 'MovimentacoesController',
    'busca'          => 'BuscaController',
    'relatorios'     => 'RelatoriosController',
    'importacao'     => 'ImportacaoController',
    'usuarios'       => 'UsuariosController',
];

if (!isset($controllers[$modulo])) {
    http_response_code(404);
    die('<h1>404 — Página não encontrada</h1>');
}

$nomeController = $controllers[$modulo];
$arquivoController = dirname(__DIR__) . '/app/controllers/' . $nomeController . '.php';

if (!file_exists($arquivoController)) {
    http_response_code(404);
    die('<h1>Controller não encontrado: ' . htmlspecialchars($nomeController) . '</h1>');
}

require_once $arquivoController;

// -----------------------------------------------------------------
// 5. Instancia o controller e chama a ação correspondente
// -----------------------------------------------------------------
$controller = new $nomeController();

// Rotas especiais: login, logout e google OAuth não precisam de autenticação
if (in_array($modulo, ['login', 'logout', 'google', ''])) {
    if (method_exists($controller, $acao)) {
        $controller->$acao($id);
    } elseif (method_exists($controller, 'index')) {
        $controller->index($id);
    }
} else {
    // Demais rotas exigem login
    exigirLogin();

    if (method_exists($controller, $acao)) {
        $controller->$acao($id);
    } elseif (method_exists($controller, 'index')) {
        $controller->index($id);
    } else {
        http_response_code(404);
        die('<h1>Ação não encontrada: ' . htmlspecialchars($acao) . '</h1>');
    }
}
