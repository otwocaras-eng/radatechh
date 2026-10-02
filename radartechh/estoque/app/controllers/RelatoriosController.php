<?php
/**
 * RelatoriosController.php
 * Quatro relatórios + exportação CSV.
 * Acesso restrito a administradores.
 */

class RelatoriosController
{
    private PDO $db;

    public function __construct()
    {
        exigirAdmin();
        $this->db = getDB();
    }

    // ----------------------------------------------------------------
    // GET /relatorios/mais-vendidos
    // ----------------------------------------------------------------
    public function maisVendidos(?int $id = null): void
    {
        $dataInicio = limpar($_GET['data_inicio'] ?? date('Y-m-01'));
        $dataFim    = limpar($_GET['data_fim']    ?? date('Y-m-d'));
        $limite     = min((int) ($_GET['limite'] ?? 20), 100);

        $stmt = $this->db->prepare(
            'SELECT p.id, p.nome, p.marca, c.nome AS categoria,
                    SUM(m.quantidade)          AS total_saida,
                    COUNT(m.id)                AS num_saidas,
                    p.preco_venda,
                    SUM(m.quantidade * p.preco_venda) AS receita_estimada
               FROM movimentacoes m
               JOIN produtos p   ON p.id = m.produto_id
               LEFT JOIN categorias c ON c.id = p.categoria_id
              WHERE m.tipo = "saida"
                AND DATE(m.criado_em) BETWEEN :di AND :df
              GROUP BY p.id, p.nome, p.marca, c.nome, p.preco_venda
              ORDER BY total_saida DESC
              LIMIT :lim'
        );
        $stmt->bindValue(':di',  $dataInicio);
        $stmt->bindValue(':df',  $dataFim);
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $produtos = $stmt->fetchAll();

        if (!empty($_GET['exportar'])) {
            $this->exportarCsv($produtos, 'mais_vendidos', [
                'Produto', 'Marca', 'Categoria', 'Total Saída', 'Nº Saídas', 'Preço Venda', 'Receita Estimada'
            ], fn($r) => [
                $r['nome'], $r['marca'], $r['categoria'],
                $r['total_saida'], $r['num_saidas'],
                number_format($r['preco_venda'], 2, ',', '.'),
                number_format($r['receita_estimada'], 2, ',', '.'),
            ]);
        }

        $tituloPagina = 'Mais Vendidos';
        $paginaAtiva  = 'rel-vendidos';
        require_once dirname(__DIR__) . '/views/relatorios/mais-vendidos.php';
    }

    // ----------------------------------------------------------------
    // GET /relatorios/parados
    // ----------------------------------------------------------------
    public function parados(?int $id = null): void
    {
        $dias = max(1, (int) ($_GET['dias'] ?? 30));

        $stmt = $this->db->prepare(
            'SELECT p.id, p.nome, p.marca, c.nome AS categoria,
                    p.estoque_atual, p.preco_custo, p.preco_venda,
                    (p.estoque_atual * p.preco_custo) AS valor_parado,
                    MAX(m.criado_em) AS ultima_saida
               FROM produtos p
               LEFT JOIN categorias c ON c.id = p.categoria_id
               LEFT JOIN movimentacoes m ON m.produto_id = p.id AND m.tipo = "saida"
              WHERE p.ativo = 1 AND p.estoque_atual > 0
              GROUP BY p.id, p.nome, p.marca, c.nome, p.estoque_atual, p.preco_custo, p.preco_venda
             HAVING ultima_saida IS NULL OR ultima_saida < DATE_SUB(NOW(), INTERVAL :dias DAY)
              ORDER BY ultima_saida ASC, p.nome ASC'
        );
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        $produtos = $stmt->fetchAll();

        if (!empty($_GET['exportar'])) {
            $this->exportarCsv($produtos, 'produtos_parados', [
                'Produto', 'Marca', 'Categoria', 'Estoque', 'Custo Unit.', 'Valor Parado', 'Última Saída'
            ], fn($r) => [
                $r['nome'], $r['marca'], $r['categoria'],
                $r['estoque_atual'],
                number_format($r['preco_custo'], 2, ',', '.'),
                number_format($r['valor_parado'], 2, ',', '.'),
                $r['ultima_saida'] ? dataHora($r['ultima_saida']) : 'Nunca',
            ]);
        }

        $tituloPagina = 'Produtos Parados';
        $paginaAtiva  = 'rel-parados';
        require_once dirname(__DIR__) . '/views/relatorios/parados.php';
    }

    // ----------------------------------------------------------------
    // GET /relatorios/lista-compras
    // ----------------------------------------------------------------
    public function listaCompras(?int $id = null): void
    {
        $stmt = $this->db->query(
            'SELECT p.id, p.nome, p.marca, c.nome AS categoria,
                    p.estoque_atual, p.estoque_minimo,
                    p.preco_custo,
                    GREATEST(0, p.estoque_minimo - p.estoque_atual) AS qtd_sugerida,
                    GREATEST(0, p.estoque_minimo - p.estoque_atual) * p.preco_custo AS custo_repor
               FROM produtos p
               LEFT JOIN categorias c ON c.id = p.categoria_id
              WHERE p.ativo = 1 AND p.estoque_atual <= p.estoque_minimo
              ORDER BY p.estoque_atual ASC, p.nome ASC'
        );
        $produtos = $stmt->fetchAll();

        $totalCusto = array_sum(array_column($produtos, 'custo_repor'));

        if (!empty($_GET['exportar'])) {
            $this->exportarCsv($produtos, 'lista_compras', [
                'Produto', 'Marca', 'Categoria', 'Estoque Atual', 'Estoque Mínimo', 'Qtd. Sugerida', 'Custo Unit.', 'Custo Total'
            ], fn($r) => [
                $r['nome'], $r['marca'], $r['categoria'],
                $r['estoque_atual'], $r['estoque_minimo'], $r['qtd_sugerida'],
                number_format($r['preco_custo'], 2, ',', '.'),
                number_format($r['custo_repor'], 2, ',', '.'),
            ]);
        }

        $tituloPagina = 'Lista de Compras';
        $paginaAtiva  = 'rel-compras';
        require_once dirname(__DIR__) . '/views/relatorios/lista-compras.php';
    }

    // ----------------------------------------------------------------
    // GET /relatorios/valor-estoque
    // ----------------------------------------------------------------
    public function valorEstoque(?int $id = null): void
    {
        $stmt = $this->db->query(
            'SELECT p.id, p.nome, p.marca, c.nome AS categoria,
                    p.estoque_atual, p.preco_custo, p.preco_venda,
                    (p.estoque_atual * p.preco_custo) AS valor_custo,
                    (p.estoque_atual * p.preco_venda) AS valor_venda
               FROM produtos p
               LEFT JOIN categorias c ON c.id = p.categoria_id
              WHERE p.ativo = 1 AND p.estoque_atual > 0
              ORDER BY valor_custo DESC'
        );
        $produtos = $stmt->fetchAll();

        $totalCusto = array_sum(array_column($produtos, 'valor_custo'));
        $totalVenda = array_sum(array_column($produtos, 'valor_venda'));
        $lucroEstimado = $totalVenda - $totalCusto;

        if (!empty($_GET['exportar'])) {
            $this->exportarCsv($produtos, 'valor_estoque', [
                'Produto', 'Marca', 'Categoria', 'Estoque', 'Custo Unit.', 'Venda Unit.',
                'Valor a Custo', 'Valor a Venda'
            ], fn($r) => [
                $r['nome'], $r['marca'], $r['categoria'], $r['estoque_atual'],
                number_format($r['preco_custo'], 2, ',', '.'),
                number_format($r['preco_venda'], 2, ',', '.'),
                number_format($r['valor_custo'], 2, ',', '.'),
                number_format($r['valor_venda'], 2, ',', '.'),
            ]);
        }

        $tituloPagina = 'Valor do Estoque';
        $paginaAtiva  = 'rel-valor';
        require_once dirname(__DIR__) . '/views/relatorios/valor-estoque.php';
    }

    /**
     * Gera e envia um arquivo CSV para download.
     *
     * @param array    $dados     Linhas de dados
     * @param string   $nomeArq   Nome base do arquivo
     * @param array    $cabecalho Rótulos das colunas
     * @param callable $mapeador  Função que transforma uma linha em array de strings
     */
    private function exportarCsv(array $dados, string $nomeArq, array $cabecalho, callable $mapeador): void
    {
        $nomeArquivo = $nomeArq . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $saida = fopen('php://output', 'w');
        // BOM UTF-8 para abrir corretamente no Excel
        fputs($saida, "\xEF\xBB\xBF");
        fputcsv($saida, $cabecalho, ';');

        foreach ($dados as $linha) {
            fputcsv($saida, $mapeador($linha), ';');
        }

        fclose($saida);
        exit;
    }
}
