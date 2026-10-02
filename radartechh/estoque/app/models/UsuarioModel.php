<?php
/**
 * UsuarioModel.php
 * Model responsável por operações de usuários no banco de dados.
 */

class UsuarioModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Busca um usuário pelo login (para autenticação).
     * Retorna o array com dados do usuário ou false se não encontrado.
     */
    public function buscarPorLogin(string $login): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, nome, login, senha_hash, perfil, ativo
               FROM usuarios
              WHERE login = :login
              LIMIT 1'
        );
        $stmt->execute([':login' => $login]);
        return $stmt->fetch();
    }

    /**
     * Busca usuário pelo ID.
     */
    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, nome, login, perfil, ativo, criado_em
               FROM usuarios
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Lista todos os usuários (para o admin).
     */
    public function listarTodos(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nome, login, perfil, ativo, criado_em
               FROM usuarios
              ORDER BY nome ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Cria um novo usuário.
     */
    public function criar(string $nome, string $login, string $senha, string $perfil): bool
    {
        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nome, login, senha_hash, perfil)
             VALUES (:nome, :login, :senha_hash, :perfil)'
        );
        return $stmt->execute([
            ':nome'      => $nome,
            ':login'     => $login,
            ':senha_hash'=> $hash,
            ':perfil'    => $perfil,
        ]);
    }

    /**
     * Atualiza dados do usuário (sem alterar senha).
     */
    public function atualizar(int $id, string $nome, string $login, string $perfil, int $ativo): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
                SET nome   = :nome,
                    login  = :login,
                    perfil = :perfil,
                    ativo  = :ativo
              WHERE id = :id'
        );
        return $stmt->execute([
            ':nome'   => $nome,
            ':login'  => $login,
            ':perfil' => $perfil,
            ':ativo'  => $ativo,
            ':id'     => $id,
        ]);
    }

    /**
     * Altera a senha de um usuário.
     */
    public function alterarSenha(int $id, string $novaSenha): bool
    {
        $hash = password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare(
            'UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :id'
        );
        return $stmt->execute([':senha_hash' => $hash, ':id' => $id]);
    }

    /**
     * Verifica se um login já está em uso por outro usuário.
     */
    public function loginEmUso(string $login, ?int $ignorarId = null): bool
    {
        if ($ignorarId) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM usuarios WHERE login = :login AND id != :id'
            );
            $stmt->execute([':login' => $login, ':id' => $ignorarId]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM usuarios WHERE login = :login'
            );
            $stmt->execute([':login' => $login]);
        }
        return (int) $stmt->fetchColumn() > 0;
    }
}
