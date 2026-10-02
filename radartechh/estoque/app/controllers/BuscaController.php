<?php
/**
 * BuscaController.php
 * Busca rápida de produtos (AJAX e página dedicada).
 * Versão completa implementada na Etapa 5.
 */

class BuscaController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    // GET /busca — Página de busca dedicada (para mobile)
    public function index(?int $id = null): void
    {
        exigirLogin();
        $tituloPagina = 'Busca Rápida';
        $paginaAtiva  = 'busca';
        require_once dirname(__DIR__) . '/views/busca/index.php';
    }

    // GET /busca/ajax?q=termo — Endpoint AJAX para a busca instantânea
    public function ajax(?int $id = null): void
    {
        exigirLogin();

        // Apenas aceita requisições AJAX
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
            http_response_code(403);
            exit;
        }

        $termo = limpar($_GET['q'] ?? '');

        if (strlen($termo) < 2) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            exit;
        }

        $like = '%' . $termo . '%';

        $stmt = $this->db->prepare(
            'SELECT p.id, p.nome, p.marca, p.codigo_barras,
                    p.preco_venda, p.estoque_atual, p.estoque_minimo,
                    c.nome AS categoria
               FROM produtos p
               LEFT JOIN categorias c ON c.id = p.categoria_id
              WHERE p.ativo = 1
                AND (p.nome LIKE :like OR p.codigo_barras = :codigo OR p.marca LIKE :like2)
              ORDER BY p.nome ASC
              LIMIT 15'
        );

        $stmt->execute([
            ':like'   => $like,
            ':codigo' => $termo,   // Busca exata por código de barras
            ':like2'  => $like,
        ]);

        $produtos = $stmt->fetchAll();

        // Adiciona o preço formatado para exibição
        foreach ($produtos as &$p) {
            $p['preco_venda_fmt'] = moeda((float) $p['preco_venda']);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($produtos, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
