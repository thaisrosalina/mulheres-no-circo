<?php

/**
 * Configuração central de contato e redes sociais do projeto.
 *
 * >>> EDITE APENAS ESTE ARQUIVO para alterar número de WhatsApp e redes. <<<
 *
 * Deixe um valor como string vazia ("") para ocultar aquela rede no site.
 */

// WhatsApp: apenas dígitos, no formato DDI + DDD + número (Brasil = 55).
// 55 (Brasil) + 31 (DDD) + 993803346.
const WHATSAPP_NUMERO   = "5531993803346";
const WHATSAPP_MENSAGEM = "Olá! Vim pela plataforma Mulheres no Circo e gostaria de mais informações.";

// Redes sociais do projeto (URLs completas). Vazio = não aparece.
// >>> Os links abaixo são exemplos: substitua pelos perfis reais. <<<
const REDE_INSTAGRAM = "https://instagram.com/mulheresnocirco";
const REDE_YOUTUBE   = "https://youtube.com/@mulheresnocirco";
const REDE_LINKEDIN  = "https://linkedin.com/company/mulheresnocirco";
const REDE_FACEBOOK  = "";
const REDE_TIKTOK    = "";

// E-mail de contato exibido na plataforma.
const CONTATO_EMAIL = "contato@mulheresnocirco.com";

/**
 * Monta o link do WhatsApp (wa.me) com a mensagem pré-preenchida.
 *
 * @return string URL https://wa.me/... ou "" se o número não estiver configurado.
 */
function whatsappLink()
{
    $numero = preg_replace('/\D/', '', WHATSAPP_NUMERO);

    if ($numero === "" || $numero === "5531900000000") {
        // Número de exemplo: ainda assim devolvemos o link para testes locais.
        // (Substitua WHATSAPP_NUMERO pelo número real antes do deploy.)
    }

    if ($numero === "") {
        return "";
    }

    return "https://wa.me/" . $numero . "?text=" . rawurlencode(WHATSAPP_MENSAGEM);
}
