<?php
/**
 * CategoriaModel.php
 * Operações de banco para a tabela categorias.
 */
class CategoriaModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /** Lista todas as categorias em ordem alfabética. */
    public function listar(): array
    {
        return $this->db
            ->query('SELECT id, nome FROM categorias ORDER BY nome ASC')
            ->fetchAll();
    }

    /** Busca categoria pelo ID. */
    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM categorias WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** Cria nova categoria. */
    public function criar(string $nome): bool
    {
        $stmt = $this->db->prepare('INSERT INTO categorias (nome) VALUES (:nome)');
        return $stmt->execute([':nome' => $nome]);
    }

    /** Atualiza nome de uma categoria. */
    public function atualizar(int $id, string $nome): bool
    {
        $stmt = $this->db->prepare('UPDATE categorias SET nome = :nome WHERE id = :id');
        return $stmt->execute([':nome' => $nome, ':id' => $id]);
    }

    /**
     * Exclui categoria (apenas se não tiver produtos vinculados).
     * Retorna true em sucesso, string com mensagem de erro em falha.
     */
    public function excluir(int $id): bool|string
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM produtos WHERE categoria_id = :id AND ativo = 1'
        );
        $stmt->execute([':id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return 'Esta categoria possui produtos ativos vinculados e não pode ser excluída.';
        }
        $stmt = $this->db->prepare('DELETE FROM categorias WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return true;
    }

    /** Verifica se o nome já está em uso por outra categoria. */
    public function nomeEmUso(string $nome, ?int $ignorarId = null): bool
    {
        if ($ignorarId) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM categorias WHERE nome = :nome AND id != :id'
            );
            $stmt->execute([':nome' => $nome, ':id' => $ignorarId]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM categorias WHERE nome = :nome'
            );
            $stmt->execute([':nome' => $nome]);
        }
        return (int) $stmt->fetchColumn() > 0;
    }
}
