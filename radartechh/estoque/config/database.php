<?php
/**
 * database.php
 * Conexão PDO singleton com o banco de dados MySQL/MariaDB.
 * Usa Prepared Statements em todas as queries para segurança.
 */

require_once __DIR__ . '/config.php';

/**
 * Retorna a instância única (singleton) da conexão PDO.
 * Lança PDOException em caso de falha de conexão.
 *
 * @return PDO
 */
function getDB(): PDO
{
    static $pdo = null; // Conexão reutilizada durante a requisição

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $opcoes = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Lança exceções em erros
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Retorna arrays associativos
            PDO::ATTR_EMULATE_PREPARES   => false,                     // Prepared statements reais
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
        } catch (PDOException $e) {
            // Em produção, loga o erro mas não expõe detalhes ao usuário
            if (APP_DEBUG) {
                throw $e;
            }
            // Mensagem genérica para o usuário
            die('Não foi possível conectar ao banco de dados. Contate o administrador.');
        }
    }

    return $pdo;
}
