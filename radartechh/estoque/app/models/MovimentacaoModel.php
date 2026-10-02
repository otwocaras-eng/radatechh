<?php
/**
 * MovimentacaoModel.php
 * Registra entradas e saídas de estoque usando transações PDO.
 * O estoque_atual do produto é SEMPRE atualizado dentro da mesma transação,
 * garantindo consistência mesmo em caso de falha.
 */
class MovimentacaoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Registra uma ENTRADA de estoque.
     * Atualiza estoque_atual e preco_custo do produto na mesma transação.
     *
     * @throws RuntimeException em caso de estoque inválido
     */
    public function registrarEntrada(array $d): void
    {
        $produtoId   = (int)   $d['produto_id'];
        $quantidade  = (int)   $d['quantidade'];
        $custoUnit   = (float) $d['custo_unitario'];
        $fornecedorId= $d['fornecedor_id'] ? (int) $d['fornecedor_id'] : null;
        $usuarioId   = (int)   $d['usuario_id'];
        $motivo      = $d['motivo']     ?? 'Compra';
        $observacao  = $d['observacao'] ?? null;

        if ($quantidade <= 0) {
            throw new RuntimeException('A quantidade deve ser maior que zero.');
        }

        $this->db->beginTransaction();
        try {
            // Insere a movimentação
            $stmt = $this->db->prepare(
                'INSERT INTO movimentacoes
                    (produto_id, tipo, quantidade, motivo, custo_unitario,
                     fornecedor_id, usuario_id, observacao)
                 VALUES
                    (:produto_id, "entrada", :quantidade, :motivo, :custo_unitario,
                     :fornecedor_id, :usuario_id, :observacao)'
            );
            $stmt->execute([
                ':produto_id'    => $produtoId,
                ':quantidade'    => $quantidade,
                ':motivo'        => $motivo,
                ':custo_unitario'=> $custoUnit ?: null,
                ':fornecedor_id' => $fornecedorId,
                ':usuario_id'    => $usuarioId,
                ':observacao'    => $observacao,
            ]);

            // Atualiza estoque e preço de custo do produto
            $atualizarCusto = $custoUnit > 0
                ? ', preco_custo = :custo'
                : '';

            $stmt2 = $this->db->prepare(
                "UPDATE produtos
                    SET estoque_atual = estoque_atual + :qtd
                        $atualizarCusto
                  WHERE id = :id"
            );
            $params = [':qtd' => $quantidade, ':id' => $produtoId];
            if ($custoUnit > 0) $params[':custo'] = $custoUnit;
            $stmt2->execute($params);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Registra uma SAÍDA de estoque.
     * Impede saída maior que o estoque disponível.
     *
     * @throws RuntimeException se estoque insuficiente
     */
    public function registrarSaida(array $d): void
    {
        $produtoId  = (int) $d['produto_id'];
        $quantidade = (int) $d['quantidade'];
        $motivo     = $d['motivo']     ?? 'Venda';
        $observacao = $d['observacao'] ?? null;
        $usuarioId  = (int) $d['usuario_id'];

        if ($quantidade <= 0) {
            throw new RuntimeException('A quantidade deve ser maior que zero.');
        }

        $this->db->beginTransaction();
        try {
            // Verifica estoque disponível (com bloqueio para escrita)
            $stmt = $this->db->prepare(
                'SELECT estoque_atual FROM produtos WHERE id = :id FOR UPDATE'
            );
            $stmt->execute([':id' => $produtoId]);
            $estoqueAtual = (int) $stmt->fetchColumn();

            if ($quantidade > $estoqueAtual) {
                $this->db->rollBack();
                throw new RuntimeException(
                    "Estoque insuficiente. Disponível: {$estoqueAtual} unidade(s)."
                );
            }

            // Insere a movimentação
            $stmt2 = $this->db->prepare(
                'INSERT INTO movimentacoes
                    (produto_id, tipo, quantidade, motivo, usuario_id, observacao)
                 VALUES
                    (:produto_id, "saida", :quantidade, :motivo, :usuario_id, :observacao)'
            );
            $stmt2->execute([
                ':produto_id' => $produtoId,
                ':quantidade' => $quantidade,
                ':motivo'     => $motivo,
                ':usuario_id' => $usuarioId,
                ':observacao' => $observacao,
            ]);

            // Decrementa o estoque
            $stmt3 = $this->db->prepare(
                'UPDATE produtos SET estoque_atual = estoque_atual - :qtd WHERE id = :id'
            );
            $stmt3->execute([':qtd' => $quantidade, ':id' => $produtoId]);

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Lista movimentações com filtros e paginação.
     */
    public function listar(array $filtros = [], int $pagina = 1, int $porPagina = 25): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['produto_id'])) {
            $where[]              = 'm.produto_id = :produto_id';
            $params[':produto_id']= (int) $filtros['produto_id'];
        }
        if (!empty($filtros['tipo'])) {
            $where[]        = 'm.tipo = :tipo';
            $params[':tipo']= $filtros['tipo'];
        }
        if (!empty($filtros['data_inicio'])) {
            $where[]              = 'DATE(m.criado_em) >= :data_inicio';
            $params[':data_inicio']= $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $where[]           = 'DATE(m.criado_em) <= :data_fim';
            $params[':data_fim']= $filtros['data_fim'];
        }

        $whereStr = implode(' AND ', $where);

        $stmtTotal = $this->db->prepare("SELECT COUNT(*) FROM movimentacoes m WHERE $whereStr");
        $stmtTotal->execute($params);
        $total = (int) $stmtTotal->fetchColumn();

        $offset = ($pagina - 1) * $porPagina;
        $stmt   = $this->db->prepare(
            "SELECT m.*, p.nome AS produto_nome, p.codigo_barras,
                    u.nome AS usuario_nome,
                    f.nome AS fornecedor_nome
               FROM movimentacoes m
               JOIN produtos  p ON p.id = m.produto_id
               JOIN usuarios  u ON u.id = m.usuario_id
               LEFT JOIN fornecedores f ON f.id = m.fornecedor_id
              WHERE $whereStr
              ORDER BY m.criado_em DESC
              LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit',  $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
        $stmt->execute();

        return ['itens' => $stmt->fetchAll(), 'total' => $total];
    }
}
