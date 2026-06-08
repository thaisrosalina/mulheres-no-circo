<?php require_once "includes/redes.php"; ?>
<?php include "includes/header.php"; ?>

<div class="container my-5">
    <div class="page-narrow mx-auto">
    <h1 class="mb-1">Política de Privacidade</h1>
    <p class="text-muted">Última atualização: 03/06/2026</p>

    <p class="lead">
        A plataforma <strong>Mulheres no Circo</strong> respeita a sua privacidade e segue a
        Lei Geral de Proteção de Dados (LGPD – Lei nº 13.709/2018). Esta página explica quais
        dados coletamos, para quê, e quais são os seus direitos.
    </p>

    <h4 class="mt-4">1. Quais dados coletamos</h4>
    <p>Ao se cadastrar e usar a plataforma, podemos coletar:</p>
    <ul>
        <li><strong>Dados de identificação e perfil:</strong> nome artístico, e-mail, data de nascimento,
            cidade de origem e cidade atual, foto de perfil.</li>
        <li><strong>Dados profissionais:</strong> área de atuação, especialidades, biografia,
            trabalhos (imagens, descrições, PDFs e links de vídeo) e links de redes/sites.</li>
        <li><strong>Dados sensíveis (opcionais):</strong> identidade de gênero e orientação sexual.
            O preenchimento é facultativo e usado apenas para fins estatísticos do projeto —
            <strong>esses dados não são exibidos no seu perfil público</strong>.</li>
        <li><strong>Dados de acesso e segurança:</strong> endereço IP, navegador (user-agent),
            data/hora de login e registros de eventos, usados para segurança e prevenção de abusos.</li>
        <li><strong>Mensagens de contato:</strong> nome, e-mail e conteúdo enviados pelo formulário de contato.</li>
    </ul>

    <h4 class="mt-4">2. Como usamos os dados</h4>
    <ul>
        <li>Compor e exibir o seu perfil público no diretório de artistas.</li>
        <li>Permitir que produtoras, curadoras e o público encontrem e entrem em contato com artistas.</li>
        <li>Garantir a segurança da conta (autenticação, prevenção de tentativas de acesso indevido).</li>
        <li>Responder às mensagens enviadas pelo formulário de contato.</li>
        <li>Gerar estatísticas agregadas sobre a presença de mulheres nas artes circenses.</li>
    </ul>

    <h4 class="mt-4">3. Compartilhamento</h4>
    <p>
        Não vendemos nem compartilhamos seus dados com terceiros para fins comerciais.
        As informações marcadas como públicas no seu perfil (nome, foto, biografia, trabalhos,
        redes e disponibilidade) ficam visíveis a qualquer visitante do diretório — é essa
        a finalidade da plataforma. Dados sensíveis e de acesso permanecem privados.
    </p>

    <h4 class="mt-4">4. Senhas e segurança</h4>
    <p>
        Sua senha é armazenada de forma criptografada (hash) e não pode ser lida por nós.
        Utilizamos proteção contra falsificação de requisições (CSRF) e limite de tentativas
        de login para proteger sua conta.
    </p>

    <h4 class="mt-4">5. Cookies</h4>
    <p>
        Usamos apenas cookies essenciais: o cookie de sessão (para manter você autenticada) e,
        se você marcar “Lembrar-me”, um cookie para reconexão automática. Não utilizamos cookies
        de rastreamento publicitário.
    </p>

    <h4 class="mt-4">6. Seus direitos (LGPD)</h4>
    <p>Você pode, a qualquer momento, solicitar:</p>
    <ul>
        <li>Acesso aos seus dados e confirmação de tratamento;</li>
        <li>Correção de dados incompletos ou desatualizados (também editáveis em “Meu Perfil”);</li>
        <li>Exclusão dos seus dados e da sua conta;</li>
        <li>Revogação do consentimento dado no cadastro.</li>
    </ul>

    <h4 class="mt-4">7. Como exercer seus direitos / Contato</h4>
    <p>Fale com a equipe responsável pelo projeto:</p>
    <ul>
        <li><i class="bi bi-envelope"></i> E-mail:
            <a href="mailto:<?= htmlspecialchars(CONTATO_EMAIL) ?>"><?= htmlspecialchars(CONTATO_EMAIL) ?></a>
        </li>
        <?php if (whatsappLink() !== ""): ?>
            <li><i class="bi bi-whatsapp"></i> WhatsApp:
                <a href="<?= htmlspecialchars(whatsappLink()) ?>" target="_blank" rel="noopener">fale conosco</a>
            </li>
        <?php endif; ?>
        <li><i class="bi bi-chat-left-text"></i> Formulário: <a href="contato.php">página de Contato</a></li>
    </ul>

    <p class="text-muted mt-4">
        Ao se cadastrar, você declara ter lido e concordado com esta Política de Privacidade.
        Esta política pode ser atualizada; a data no topo indica a última revisão.
    </p>
    </div>
</div>

<?php include "includes/footer.php"; ?>
