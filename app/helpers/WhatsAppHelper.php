<?php
namespace app\helpers;

/** Telefones brasileiros cadastrados no perfil; nenhum envio automático de mensagem. */
class WhatsAppHelper
{
    public static function linkAdocao(?string $telefone, string $adotante, string $animal): ?string
    {
        $telefone = trim($telefone ?? '');
        if ($telefone === '' || !preg_match('/^\+?[0-9\s().-]+$/D', $telefone)) return null;
        $numero = preg_replace('/\D/', '', $telefone);
        if (str_starts_with($numero, '0055')) $numero = substr($numero, 2);
        if (in_array(strlen($numero), [12, 13], true) && str_starts_with($numero, '55')) {
            $numero = substr($numero, 2);
        } elseif (str_starts_with($telefone, '+') || str_starts_with($telefone, '00')) {
            return null;
        }
        // DDD brasileiro válido, telefone fixo ou celular com nono dígito.
        $ddd = '(?:1[1-9]|2[12478]|3[1-578]|4[1-9]|5[1345]|6[1-9]|7[134579]|8[1-9]|9[1-9])';
        if (!preg_match('/^'.$ddd.'(?:[2-5][0-9]{7}|9[0-9]{8})$/D', $numero)) return null;
        $mensagem = "Olá! Sou {$adotante} e gostaria de conversar sobre a adoção do pet {$animal}. Ele ainda está disponível? Contato iniciado pelo CãoNectados.";
        return 'https://wa.me/55'.$numero.'?text='.rawurlencode($mensagem);
    }
}