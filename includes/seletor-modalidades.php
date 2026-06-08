<?php
/**
 * Componente reutilizável: seletor de modalidades/habilidades (chips em acordeão).
 *
 * Requer (global): $MODALIDADES_CIRCENSES — de includes/modalidades.php.
 * Opcional: $selecionadas — array de valores já marcados (ex.: especialidades atuais).
 *
 * Uso: <?php $selecionadas = [...]; include "includes/seletor-modalidades.php"; ?>
 * Deve ser incluído dentro de um contexto de .row (usa col-md-12).
 */
$selecionadas = (isset($selecionadas) && is_array($selecionadas)) ? $selecionadas : [];
?>
<div class="col-md-12">
    <label class="form-label d-flex justify-content-between align-items-center">
        <span>Modalidades e habilidades circenses</span>
        <span class="badge bg-circo-soft text-dark border">
            <span id="totalModalidades">0</span> selecionada(s)
        </span>
    </label>

    <input type="text" id="buscaModalidade" class="form-control mb-2"
        placeholder="Filtrar modalidades... (ex.: tecido, palhaçaria, fogo)">

    <div class="accordion modalidades-box" id="accModalidades">
        <?php $gi = 0; foreach ($MODALIDADES_CIRCENSES as $grupo => $itens): $gi++;
            $selNoGrupo = count(array_intersect($itens, $selecionadas)); ?>
            <div class="accordion-item modalidade-grupo">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#grpMod<?= $gi ?>">
                        <?= htmlspecialchars($grupo) ?>
                        <span class="badge bg-circo ms-2 contador-grupo" <?= $selNoGrupo ? "" : 'style="display:none"' ?>>
                            <?= $selNoGrupo ?>
                        </span>
                    </button>
                </h2>
                <div id="grpMod<?= $gi ?>" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="d-flex flex-wrap gap-2">
                            <?php $ii = 0; foreach ($itens as $item): $ii++; $cid = "mod_{$gi}_{$ii}"; ?>
                                <span class="modalidade-item">
                                    <input class="btn-check chk-modalidade" type="checkbox" autocomplete="off"
                                        name="especialidades[]" value="<?= htmlspecialchars($item) ?>"
                                        id="<?= $cid ?>" <?= in_array($item, $selecionadas) ? "checked" : "" ?>>
                                    <label class="btn btn-chip btn-sm" for="<?= $cid ?>">
                                        <?= htmlspecialchars($item) ?>
                                    </label>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <small class="text-muted">Marque quantas quiser — elas aparecem como tags no seu perfil público.</small>
</div>

<script src="assets/js/modalidades.js"></script>
