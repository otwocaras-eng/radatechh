<?php
/**
 * config.php
 * Configurações globais da aplicação.
 * Ajuste as constantes conforme seu ambiente.
 */

// -----------------------------------------------------------------
// Configurações do banco de dados
// -----------------------------------------------------------------
define('DB_HOST',    'localhost');
define('DB_NAME',    'estoque_papelaria');
define('DB_USER',    'root');       // Trocar em produção!
define('DB_PASS',    '');           // Trocar em produção!
define('DB_CHARSET', 'utf8mb4');

// -----------------------------------------------------------------
// Configurações da aplicação
// -----------------------------------------------------------------
define('APP_NAME',    'Estoque Papelaria');
define('APP_URL',     'http://localhost/radartechh/estoque/public'); // Sem barra no final
define('APP_VERSION', '1.0.0');

// -----------------------------------------------------------------
// Configurações de sessão (segurança)
// -----------------------------------------------------------------
define('SESSION_NAME',     'estoque_sess');
define('SESSION_LIFETIME', 7200);  // 2 horas em segundos

// -----------------------------------------------------------------
// Google OAuth 2.0
// Crie as credenciais em: https://console.cloud.google.com
// → APIs e Serviços → Credenciais → Criar ID do cliente OAuth 2.0
// URI de redirecionamento autorizado: APP_URL . '/google/callback'
// -----------------------------------------------------------------
define('GOOGLE_CLIENT_ID',     'COLE_SEU_CLIENT_ID_AQUI');     // ex: 123456789-abc.apps.googleusercontent.com
define('GOOGLE_CLIENT_SECRET', 'COLE_SEU_CLIENT_SECRET_AQUI'); // ex: GOCSPX-xxxxxx
define('GOOGLE_REDIRECT_URI',  APP_URL . '/google/callback');

// -----------------------------------------------------------------
// Configurações de upload de CSV
// -----------------------------------------------------------------
define('UPLOAD_DIR',      dirname(__DIR__) . '/uploads/');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5 MB

// -----------------------------------------------------------------
// Fuso horário
// -----------------------------------------------------------------
date_default_timezone_set('America/Sao_Paulo');

// -----------------------------------------------------------------
// Modo de debug (false em produção!)
// -----------------------------------------------------------------
define('APP_DEBUG', true);

// -----------------------------------------------------------------
// Configuração de exibição de erros (baseada no debug)
// -----------------------------------------------------------------
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
