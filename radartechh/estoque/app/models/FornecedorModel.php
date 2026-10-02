<?php
/**
 * FornecedorModel.php
 * Operações de banco para a tabela fornecedores.
 */
class FornecedorModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /** Lista todos os fornecedores ativos. */
    public function listar(bool $apenasAtivos = false): array
    {
        if ($apenasAtivos) {
            return $this->db
                ->query('SELECT * FROM fornecedores WHERE ativo = 1 ORDER BY nome ASC')
                ->fetchAll();
        }
        return $this->db
            ->query('SELECT * FROM fornecedores ORDER BY nome ASC')
            ->fetchAll();
    }

    /** Busca fornecedor pelo ID. */
    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM fornecedores WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** Cria novo fornecedor. */
    public function criar(string $nome, string $telefone, string $email): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO fornecedores (nome, telefone, email)
             VALUES (:nome, :telefone, :email)'
        );
        return $stmt->execute([
            ':nome'     => $nome,
            ':telefone' => $telefone ?: null,
            ':email'    => $email    ?: null,
        ]);
    }

    /** Atualiza fornecedor. */
    public function atualizar(int $id, string $nome, string $telefone, string $email, int $ativo): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE fornecedores
                SET nome = :nome, telefone = :telefone, email = :email, ativo = :ativo
              WHERE id = :id'
        );
        return $stmt->execute([
            ':nome'     => $nome,
            ':telefone' => $telefone ?: null,
            ':email'    => $email    ?: null,
            ':ativo'    => $ativo,
            ':id'       => $id,
        ]);
    }

    /**
     * Exclui fornecedor (somente se não tiver movimentações vinculadas).
     */
    public function excluir(int $id): bool|string
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM movimentacoes WHERE fornecedor_id = :id'
        );
        $stmt->execute([':id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return 'Este fornecedor possui movimentações registradas e não pode ser excluído.';
        }
        $stmt = $this->db->prepare('DELETE FROM fornecedores WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return true;
    }
}
