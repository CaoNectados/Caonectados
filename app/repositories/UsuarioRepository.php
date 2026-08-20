<?php

namespace app\repositories;

use app\core\BaseRepository;
use app\models\Usuario;
use PDO;

class UsuarioRepository extends BaseRepository
{
    /**
     * Atualiza os dados de onboarding, define o tipo_atual e ADICIONA o perfil à lista de perfis_ativos (SET)
     */
    public function atualizarOnboarding(Usuario $usuario, string $novoPerfil): bool
    {
        // 'usuario' é apenas o estado transitório pré-onboarding: ao concluir o
        // onboarding ele é substituído pelo perfil real, nunca mantido na lista.
        $stmtAtual = $this->db->prepare("SELECT perfis_ativos FROM USUARIO WHERE usuario_id = :usuario_id");
        $stmtAtual->bindValue(':usuario_id', $usuario->getUsuarioId(), PDO::PARAM_INT);
        $stmtAtual->execute();
        $perfisAtuais = (string) ($stmtAtual->fetchColumn() ?: '');

        $listaPerfis = array_filter(
            array_map('trim', explode(',', $perfisAtuais)),
            static fn(string $p): bool => $p !== '' && $p !== 'usuario'
        );
        $listaPerfis[] = $novoPerfil;
        $perfisFinal = implode(',', array_unique($listaPerfis));

        $sql = "UPDATE USUARIO
                SET nome = :nome,
                    regiao_id = :regiao_id,
                    logradouro = :logradouro,
                    numero = :numero,
                    telefone = :telefone,
                    dt_nasc = :dt_nasc,
                    tipo_atual = :tipo_atual,
                    perfis_ativos = :perfis_ativos,
                    status_conta = :status_conta
                WHERE usuario_id = :usuario_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', $usuario->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':regiao_id', $usuario->getRegiaoId(), $usuario->getRegiaoId() ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':logradouro', $usuario->getLogradouro(), PDO::PARAM_STR);
        $stmt->bindValue(':numero', $usuario->getNumero(), PDO::PARAM_STR);
        $stmt->bindValue(':telefone', $usuario->getTelefone(), PDO::PARAM_STR);
        $stmt->bindValue(':dt_nasc', $usuario->getDtNasc(), PDO::PARAM_STR);
        $stmt->bindValue(':tipo_atual', $usuario->getTipoAtual(), PDO::PARAM_STR);
        $stmt->bindValue(':perfis_ativos', $perfisFinal, PDO::PARAM_STR);
        $stmt->bindValue(':status_conta', $usuario->getStatusConta(), PDO::PARAM_STR);
        $stmt->bindValue(':usuario_id', $usuario->getUsuarioId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function salvarNovoUsuario(Usuario $usuario): int
    {
        $sql = "INSERT INTO USUARIO (email, senha, tipo_atual, perfis_ativos) 
                VALUES (:email, :senha, :tipo_atual, :perfis_ativos)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $usuario->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':senha', $usuario->getSenha(), PDO::PARAM_STR);
        $stmt->bindValue(':tipo_atual', $usuario->getTipoAtual() ?? 'usuario', PDO::PARAM_STR);
        $stmt->bindValue(':perfis_ativos', $usuario->getPerfisAtivos() ?? 'usuario', PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        $sql = "SELECT * FROM USUARIO WHERE email = :email AND deletado_em IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dados === false ? null : $this->mapUsuario($dados);
    }

    public function buscarPorId(int $usuarioId): ?array
    {
        $sql = "SELECT usuario_id, regiao_id, logradouro, numero, telefone, email, nome, dt_nasc, tipo_atual, perfis_ativos, status_conta 
                FROM USUARIO 
                WHERE usuario_id = :id AND deletado_em IS NULL 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dados ?: null;
    }

    public function atualizarDadosPerfil(int $usuarioId, string $nome, ?string $telefone, ?int $regiaoId, string $logradouro, string $numero): bool
    {
        $sql = "UPDATE USUARIO 
                SET nome = :nome, 
                    telefone = :telefone, 
                    regiao_id = :regiao_id, 
                    logradouro = :logradouro, 
                    numero = :numero
                WHERE usuario_id = :usuario_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindValue(':telefone', $telefone, $telefone ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':regiao_id', $regiaoId, $regiaoId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':logradouro', $logradouro, PDO::PARAM_STR);
        $stmt->bindValue(':numero', $numero, PDO::PARAM_STR);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function atualizarTipoAtual(int $usuarioId, string $tipoAtual): bool
    {
        $sql = "UPDATE USUARIO SET tipo_atual = :tipo WHERE usuario_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tipo', $tipoAtual, PDO::PARAM_STR);
        $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function atualizarEmail(int $usuarioId, string $novoEmail): void
    {
        $sql = "UPDATE USUARIO SET email = :email WHERE usuario_id = :usuario_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $novoEmail, PDO::PARAM_STR);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function mapUsuario(array $row): Usuario
    {
        $usuario = new Usuario();
        $usuario->setUsuarioId((int) $row['usuario_id']);
        $usuario->setRegiaoId(isset($row['regiao_id']) ? (int) $row['regiao_id'] : null);
        $usuario->setLogradouro($row['logradouro'] ?? null);
        $usuario->setNumero($row['numero'] ?? null);
        $usuario->setNome($row['nome'] ?? null);
        $usuario->setEmail($row['email'] ?? null);
        $usuario->setSenha($row['senha'] ?? null);
        $usuario->setTelefone($row['telefone'] ?? null);
        $usuario->setTipoAtual($row['tipo_atual'] ?? null);
        $usuario->setPerfisAtivos($row['perfis_ativos'] ?? null);
        $usuario->setStatusConta($row['status_conta'] ?? null);
        $usuario->setDtNasc($row['dt_nasc'] ?? null);
        $usuario->setCriadoEm($row['criado_em'] ?? null);
        $usuario->setDeletadoEm($row['deletado_em'] ?? null);

        return $usuario;
    }

    public function salvarCodigoVerificacao(int $usuarioId, string $codigo, string $expiraEm): void
    {
        $sql = "INSERT INTO CODIGO_VERIFICACAO (usuario_id, codigo, expira_em) VALUES (:usuario_id, :codigo, :expira_em)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->bindValue(':expira_em', $expiraEm, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function buscarCodigoValido(int $usuarioId, string $codigo): ?array
    {
        $sql = "SELECT * FROM CODIGO_VERIFICACAO 
                WHERE usuario_id = :usuario_id 
                  AND codigo = :codigo 
                  AND usado = FALSE 
                  AND expira_em >= NOW() 
                ORDER BY codigo_id DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dados ?: null;
    }

    public function marcarCodigoComoUsado(int $codigoId): void
    {
        $sql = "UPDATE CODIGO_VERIFICACAO SET usado = TRUE WHERE codigo_id = :codigo_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':codigo_id', $codigoId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function atualizarSenha(int $usuarioId, string $novaSenhaHash): void
    {
        $sql = "UPDATE USUARIO SET senha = :senha WHERE usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':senha', $novaSenhaHash, PDO::PARAM_STR);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
    }

  /**
     * Lista usuários com paginação, filtros e busca dinâmica de foto
     */
   public function listarUsuariosAdmin(string $busca = '', string $filtroStatus = '', string $filtroPerfil = '', int $pagina = 1, int $porPagina = 10): array
    {
        $offset = ($pagina - 1) * $porPagina;
        $sql = "SELECT u.usuario_id, u.nome, u.email, u.telefone, u.status_conta, u.tipo_atual, u.perfis_ativos, u.criado_em,
                       (SELECT COUNT(*) FROM ADOTANTE t WHERE t.usuario_id = u.usuario_id) as tem_adotante,
                       (SELECT pr.tipo_documento FROM PROTETOR pr WHERE pr.usuario_id = u.usuario_id LIMIT 1) as tipo_protetor,
                       COALESCE(
                           (SELECT pg.foto_perfil 
                              FROM PROTETOR pr 
                              INNER JOIN PAGINA pg ON pr.protetor_id = pg.protetor_id 
                             WHERE pr.usuario_id = u.usuario_id 
                             LIMIT 1),
                           (SELECT a.foto_perfil 
                              FROM ADOTANTE a 
                             WHERE a.usuario_id = u.usuario_id 
                             LIMIT 1)
                       ) AS foto_perfil
                FROM USUARIO u
                WHERE 1=1";
        
        $params = [];

        if (!empty($busca)) {
            $sql .= " AND (u.nome LIKE :busca_nome OR u.email LIKE :busca_email)";
            $params[':busca_nome'] = "%{$busca}%";
            $params[':busca_email'] = "%{$busca}%";
        }

        if (!empty($filtroStatus)) {
            $sql .= " AND u.status_conta = :status";
            $params[':status'] = $filtroStatus;
        }

        if (!empty($filtroPerfil)) {
            if ($filtroPerfil === 'administrador') {
                $sql .= " AND u.tipo_atual = 'administrador'";
            } else {
                $sql .= " AND FIND_IN_SET(:perfil, u.perfis_ativos)";
                $params[':perfil'] = $filtroPerfil;
            }
        }

        $sql .= " ORDER BY u.usuario_id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $porPagina, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    /**
     * Conta o total de usuários (para a paginação)
     */
    public function contarUsuariosAdmin(string $busca = '', string $filtroStatus = '', string $filtroPerfil = ''): int
    {
        $sql = "SELECT COUNT(*) as total FROM usuario u WHERE 1=1";
        $params = [];

        if (!empty($busca)) {
            $sql .= " AND (u.nome LIKE :busca_nome OR u.email LIKE :busca_email)";
            $params[':busca_nome'] = "%{$busca}%";
            $params[':busca_email'] = "%{$busca}%";
        }
        
        if (!empty($filtroStatus)) {
            $sql .= " AND u.status_conta = :status";
            $params[':status'] = $filtroStatus;
        }
        
        if (!empty($filtroPerfil)) {
            if ($filtroPerfil === 'administrador') {
                $sql .= " AND u.tipo_atual = 'administrador'";
            } else {
                $sql .= " AND FIND_IN_SET(:perfil, u.perfis_ativos)";
                $params[':perfil'] = $filtroPerfil;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($res['total'] ?? 0);
    }
    public function contarAdminsAtivos(): int
    {
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE (tipo_atual = 'administrador' OR FIND_IN_SET('administrador', perfis_ativos)) AND status_conta = 'ativo'";
        $stmt = $this->db->query($sql);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($res['total'] ?? 0);
    }

    public function atualizarStatusConta(int $usuarioId, string $novoStatus): bool
    {
        $sql = "UPDATE usuario SET status_conta = :status WHERE usuario_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':status' => $novoStatus, ':id' => $usuarioId]);
    }

    public function atualizarPerfisAtivos(int $usuarioId, string $perfisAtivos, string $tipoAtual): bool
    {
        $sql = "UPDATE usuario SET perfis_ativos = :perfis, tipo_atual = :tipo WHERE usuario_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':perfis' => $perfisAtivos,
            ':tipo'   => $tipoAtual,
            ':id'     => $usuarioId
        ]);
    }
}
