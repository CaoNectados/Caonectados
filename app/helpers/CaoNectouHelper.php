<?php

namespace app\helpers;

class CaoNectouHelper
{
    public static function dados(array $solicitacoes, bool $visaoResponsavel, int $usuarioId): array
    {
        $encontros = [];
        foreach ($solicitacoes as $s) {
            $id = (int) $s['solicitacao_id'];
            if (($s['status_solicitacao'] ?? '') !== 'aprovada'
                || ($s['animal_status'] ?? '') !== 'adotado'
                || !empty($s['animal_deletado_em'])
                || (int) ($s['solicitacao_atual_id'] ?? 0) !== $id) continue;

            $animal = $s['animal_nome'];
            $adotante = $s['adotante_nome'] ?: 'Adotante';
            $responsavel = $s['nome_fantasia'] ?: 'Protetor independente';
            $tipoResponsavel = ($s['tipo_documento'] ?? '') === 'cnpj' ? 'ONG' : 'protetor';
            $contatoResponsavel = $tipoResponsavel === 'ONG'
                ? (preg_match('/^ONG\b/iu', $responsavel) ? 'a '.$responsavel : 'a ONG '.$responsavel)
                : 'o protetor '.$responsavel;
            $mensagem = $visaoResponsavel
                ? "Olá, {$adotante}! Sou de {$responsavel}. Vamos combinar os próximos passos da adoção de {$animal}? Contato iniciado pelo CãoNectados."
                : "Olá! Sou {$adotante}. Minha solicitação para adotar {$animal} foi aprovada. Vamos combinar os próximos passos? Contato iniciado pelo CãoNectados.";
            $encontros[$id] = [
                'animal' => $animal,
                'adotante' => $adotante,
                'responsavel' => $responsavel,
                'fotoAnimal' => self::foto($s['animal_foto'] ?? null),
                'fotoAdotante' => self::foto($s['adotante_foto'] ?? null),
                'fotoResponsavel' => self::foto($s['protetor_foto'] ?? null),
                'mensagem' => $visaoResponsavel
                    ? "{$adotante} e {$animal} têm tudo para serem melhores amigos. Converse com o adotante para combinar os próximos passos da adoção."
                    : "Você e {$animal} têm tudo para serem melhores amigos. Converse com {$contatoResponsavel} para combinar os próximos passos da adoção.",
                'whatsapp' => WhatsAppHelper::linkMensagem($visaoResponsavel ? ($s['adotante_telefone'] ?? null) : ($s['protetor_telefone'] ?? null), $mensagem),
                'rotuloWhatsapp' => $visaoResponsavel ? 'Conversar com o adotante pelo WhatsApp' : ($tipoResponsavel === 'ONG' ? 'Conversar com a ONG pelo WhatsApp' : 'Conversar com o protetor pelo WhatsApp'),
                'semTelefone' => $visaoResponsavel ? 'O adotante ainda não cadastrou um telefone válido para contato pelo WhatsApp.' : 'O responsável ainda não cadastrou um telefone válido para contato pelo WhatsApp.',
                'sessaoKey' => 'caonectou:'.$usuarioId.':'.($visaoResponsavel ? 'responsavel' : 'adotante').':'.$id.':'.($s['data_finalizacao'] ?? ''),
            ];
        }
        return $encontros;
    }

    private static function foto(?string $caminho): string
    {
        $caminho = trim($caminho ?? '');
        if ($caminho === '' || str_starts_with($caminho, '//') || preg_match('#^[a-z][a-z0-9+.-]*:#i', $caminho) && !preg_match('#^https?://#i', $caminho)) {
            return rtrim(URL_BASE, '/').'/assets/img/perfil-placeholder.png';
        }
        return preg_match('#^https?://#i', $caminho) ? $caminho : rtrim(URL_BASE, '/').'/'.ltrim($caminho, '/');
    }
}
