<?php
require_once __DIR__ . "/redes.php";
require_once __DIR__ . "/modalidades.php";

$regioes = ["Norte", "Nordeste", "Centro-Oeste", "Sudeste", "Sul"];
$formatos = ["Rua", "Palco", "Espaços Alternativos", "Festivais", "Eventos privados"];

$redesFooter = [
    ["url" => REDE_INSTAGRAM, "icon" => "instagram", "t" => "Instagram"],
    ["url" => REDE_YOUTUBE,   "icon" => "youtube",   "t" => "YouTube"],
    ["url" => REDE_LINKEDIN,  "icon" => "linkedin",  "t" => "LinkedIn"],
    ["url" => REDE_FACEBOOK,  "icon" => "facebook",  "t" => "Facebook"],
    ["url" => REDE_TIKTOK,    "icon" => "tiktok",    "t" => "TikTok"],
];
?>

<!-- Chamada para curadores -->
<section class="footer-curador">
    <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="h5 mb-1 text-white">É curador(a), produtor(a) ou festival?</h4>
            <p class="mb-0">Cadastre-se para baixar materiais das artistas e receber atualizações da plataforma.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="cadastro-curador.php" class="btn btn-light">Quero acompanhar</a>
            <a href="login.php" class="btn btn-outline-light">Acesse sua área</a>
        </div>
    </div>
</section>

<footer class="footer-mnc">
    <div class="container">
        <div class="row g-4 py-5">

            <!-- Marca + frase + redes -->
            <div class="col-lg-4">
                <a href="index.php" class="d-flex align-items-center gap-2 mb-3 text-decoration-none">
                    <img src="assets/img/logo.svg" alt="" width="44" height="44">
                    <span class="footer-marca">Mulheres no Circo</span>
                </a>
                <p class="footer-frase">
                    Plataforma de mapeamento, visibilidade e conexão para mulheres das artes
                    circenses brasileiras — trajetórias, trabalhos e talento em um só lugar.
                </p>
                <div class="mt-3">
                    <span class="footer-acompanhe">Acompanhe</span>
                    <div class="d-flex gap-3 fs-5 mt-2">
                        <?php foreach ($redesFooter as $r): if ($r["url"] !== ""): ?>
                            <a href="<?= htmlspecialchars($r["url"]) ?>" target="_blank" rel="noopener" title="<?= $r["t"] ?>">
                                <i class="bi bi-<?= $r["icon"] ?>"></i>
                            </a>
                        <?php endif; endforeach; ?>
                        <?php if (whatsappLink() !== ""): ?>
                            <a href="<?= htmlspecialchars(whatsappLink()) ?>" target="_blank" rel="noopener" title="WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Regiões -->
            <div class="col-6 col-lg-2">
                <h6 class="footer-col-titulo">Regiões</h6>
                <ul class="footer-lista">
                    <?php foreach ($regioes as $reg): ?>
                        <li><a href="diretorio.php"><?= htmlspecialchars($reg) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Modalidades -->
            <div class="col-6 col-lg-3">
                <h6 class="footer-col-titulo">Modalidades</h6>
                <ul class="footer-lista">
                    <?php foreach ($AREAS_ATUACAO as $cat): ?>
                        <li><a href="diretorio.php?area=<?= urlencode($cat) ?>"><?= htmlspecialchars($cat) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Formatos -->
            <div class="col-6 col-lg-3">
                <h6 class="footer-col-titulo">Formatos de trabalho</h6>
                <ul class="footer-lista">
                    <?php foreach ($formatos as $fmt): ?>
                        <li><a href="diretorio.php"><?= htmlspecialchars($fmt) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <hr class="footer-hr">

        <!-- Base: Realização + créditos (centralizado e equilibrado) -->
        <div class="footer-base text-center py-4">
            <p class="footer-realizacao-label mb-3">Realização</p>
            <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
                <span class="footer-logo-ph">Circoteca</span>
                <span class="footer-logo-ph">Cia Gêmea</span>
                <span class="footer-logo-ph">Tô Feito Estúdio Criativo</span>
            </div>
            <p class="small mb-1">Mulheres no Circo — Todos os direitos reservados</p>
            <p class="small mb-2">Desenvolvido por <strong>Thais Oliveira</strong> — Central de Produção Cultural</p>
            <a href="politica-privacidade.php" class="footer-link-pp small">Política de Privacidade</a>
        </div>
    </div>
</footer>

<?php if (whatsappLink() !== ""): ?>
    <a
        href="<?= htmlspecialchars(whatsappLink()) ?>"
        target="_blank"
        rel="noopener"
        class="whatsapp-flutuante"
        title="Fale conosco no WhatsApp"
        aria-label="Fale conosco no WhatsApp"
    >
        <i class="bi bi-whatsapp"></i>
    </a>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
