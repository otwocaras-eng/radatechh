<?php
/**
 * DashboardController.php
 * Exibe o painel inicial com totais, alertas e últimas movimentações.
 * (Dados reais serão populados na Etapa 6 — aqui é o placeholder funcional)
 */

class DashboardController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    // GET /dashboard
    public function index(?int $id = null): void
    {
        exigirLogin();

        // Totais rápidos para o dashboard
        $totalProdutos = $this->db
            ->query('SELECT COUNT(*) FROM produtos WHERE ativo = 1')
            ->fetchColumn();

        $abaixoMinimo = $this->db
            ->query('SELECT COUNT(*) FROM produtos WHERE ativo = 1 AND estoque_atual <= estoque_minimo')
            ->fetchColumn();

        $valorEstoque = $this->db
            ->query('SELECT COALESCE(SUM(preco_custo * estoque_atual), 0) FROM produtos WHERE ativo = 1')
            ->fetchColumn();

        $valorVenda = $this->db
            ->query('SELECT COALESCE(SUM(preco_venda * estoque_atual), 0) FROM produtos WHERE ativo = 1')
            ->fetchColumn();

        // Últimas 8 movimentações
        $ultimasMovimentacoes = $this->db->query(
            'SELECT m.tipo, m.quantidade, m.motivo, m.criado_em,
                    p.nome AS produto_nome,
                    u.nome AS usuario_nome
               FROM movimentacoes m
               JOIN produtos p ON p.id = m.produto_id
               JOIN usuarios  u ON u.id = m.usuario_id
              ORDER BY m.criado_em DESC
              LIMIT 8'
        )->fetchAll();

        // Produtos abaixo do mínimo (para o painel de alertas)
        $alertasEstoque = $this->db->query(
            'SELECT p.id, p.nome, p.estoque_atual, p.estoque_minimo, c.nome AS categoria
               FROM produtos p
               LEFT JOIN categorias c ON c.id = p.categoria_id
              WHERE p.ativo = 1 AND p.estoque_atual <= p.estoque_minimo
              ORDER BY p.estoque_atual ASC
              LIMIT 10'
        )->fetchAll();

        require_once dirname(__DIR__) . '/views/dashboard/index.php';
    }
}
