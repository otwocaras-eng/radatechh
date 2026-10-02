<?php
/**
 * ProdutoModel.php
 * Operações de banco para a tabela produtos.
 * Exclusão lógica via campo "ativo" — nunca DELETE físico.
 */
class ProdutoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Lista produtos com filtros opcionais e paginação.
     *
     * @param array $filtros  ['busca'=>string, 'categoria_id'=>int, 'status'=>'ativo'|'inativo'|'todos', 'abaixo_minimo'=>bool]
     * @param int   $pagina   Página atual (1-indexed)
     * @param int   $porPagina Itens por página
     * @return array ['itens'=>[], 'total'=>int]
     */
    public function listar(array $filtros = [], int $pagina = 1, int $porPagina = 20): array
    {
        $where  = ['1=1'];
        $params = [];

        // Filtro de status (padrão: apenas ativos)
        $status = $filtros['status'] ?? 'ativo';
        if ($status === 'ativo') {
            $where[] = 'p.ativo = 1';
        } elseif ($status === 'inativo') {
            $where[] = 'p.ativo = 0';
        }
        // 'todos' não adiciona filtro

        // Filtro de busca por nome/código/marca
        if (!empty($filtros['busca'])) {
            $where[]            = '(p.nome LIKE :busca OR p.codigo_barras LIKE :busca2 OR p.marca LIKE :busca3)';
            $like               = '%' . $filtros['busca'] . '%';
            $params[':busca']   = $like;
            $params[':busca2']  = $like;
            $params[':busca3']  = $like;
        }

        // Filtro por categoria
        if (!empty($filtros['categoria_id'])) {
            $where[]                = 'p.categoria_id = :categoria_id';
            $params[':categoria_id']= (int) $filtros['categoria_id'];
        }

        // Apenas produtos abaixo do mínimo
        if (!empty($filtros['abaixo_minimo'])) {
            $where[] = 'p.estoque_atual <= p.estoque_minimo';
        }

        $whereStr = implode(' AND ', $where);

        // Total de registros (para paginação)
        $stmtTotal = $this->db->prepare(
            "SELECT COUNT(*) FROM produtos p WHERE $whereStr"
        );
        $stmtTotal->execute($params);
        $total = (int) $stmtTotal->fetchColumn();

        // Dados paginados
        $offset = ($pagina - 1) * $porPagina;
        $stmt   = $this->db->prepare(
            "SELECT p.*, c.nome AS categoria_nome
               FROM produtos p
               LEFT JOIN categorias c ON c.id = p.categoria_id
              WHERE $whereStr
              ORDER BY p.nome ASC
              LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
        $stmt->execute();

        return ['itens' => $stmt->fetchAll(), 'total' => $total];
    }

    /** Busca produto pelo ID (inclui nome da categoria). */
    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.nome AS categoria_nome
               FROM produtos p
               LEFT JOIN categorias c ON c.id = p.categoria_id
              WHERE p.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** Cria novo produto. Retorna o ID inserido. */
    public function criar(array $d): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO produtos
                (nome, codigo_barras, categoria_id, marca, preco_custo, preco_venda,
                 estoque_atual, estoque_minimo, ativo)
             VALUES
                (:nome, :codigo_barras, :categoria_id, :marca, :preco_custo, :preco_venda,
                 :estoque_atual, :estoque_minimo, 1)'
        );
        $stmt->execute([
            ':nome'           => $d['nome'],
            ':codigo_barras'  => $d['codigo_barras']  ?: null,
            ':categoria_id'   => $d['categoria_id']   ?: null,
            ':marca'          => $d['marca']           ?: null,
            ':preco_custo'    => $d['preco_custo'],
            ':preco_venda'    => $d['preco_venda'],
            ':estoque_atual'  => $d['estoque_atual'],
            ':estoque_minimo' => $d['estoque_minimo'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Atualiza produto existente. */
    public function atualizar(int $id, array $d): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE produtos
                SET nome           = :nome,
                    codigo_barras  = :codigo_barras,
                    categoria_id   = :categoria_id,
                    marca          = :marca,
                    preco_custo    = :preco_custo,
                    preco_venda    = :preco_venda,
                    estoque_minimo = :estoque_minimo,
                    ativo          = :ativo
              WHERE id = :id'
        );
        return $stmt->execute([
            ':nome'           => $d['nome'],
            ':codigo_barras'  => $d['codigo_barras']  ?: null,
            ':categoria_id'   => $d['categoria_id']   ?: null,
            ':marca'          => $d['marca']           ?: null,
            ':preco_custo'    => $d['preco_custo'],
            ':preco_venda'    => $d['preco_venda'],
            ':estoque_minimo' => $d['estoque_minimo'],
            ':ativo'          => $d['ativo'],
            ':id'             => $id,
        ]);
    }

    /** Inativa produto (exclusão lógica). */
    public function inativar(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE produtos SET ativo = 0 WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /** Verifica se código de barras já está em uso. */
    public function codigoBarrasEmUso(string $codigo, ?int $ignorarId = null): bool
    {
        if (!$codigo) return false;
        if ($ignorarId) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM produtos WHERE codigo_barras = :c AND id != :id'
            );
            $stmt->execute([':c' => $codigo, ':id' => $ignorarId]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM produtos WHERE codigo_barras = :c'
            );
            $stmt->execute([':c' => $codigo]);
        }
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Retorna produtos com estoque abaixo ou igual ao mínimo. */
    public function abaixoMinimo(): array
    {
        return $this->db->query(
            'SELECT p.*, c.nome AS categoria_nome
               FROM produtos p
               LEFT JOIN categorias c ON c.id = p.categoria_id
              WHERE p.ativo = 1 AND p.estoque_atual <= p.estoque_minimo
              ORDER BY p.estoque_atual ASC'
        )->fetchAll();
    }

    /**
     * Cria produto a partir de uma linha CSV.
     * Usado pelo ImportacaoController. Retorna true ou string com erro.
     */
    public function criarDoCsv(array $d): bool|string
    {
        // Verifica código de barras duplicado
        if (!empty($d['codigo_barras']) && $this->codigoBarrasEmUso($d['codigo_barras'])) {
            return 'Código de barras "' . $d['codigo_barras'] . '" já existe.';
        }
        $this->criar($d);
        return true;
    }
}
