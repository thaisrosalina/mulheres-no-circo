/* Seletor de modalidades (acordeão de chips):
   contadores por grupo, total e filtro de busca. */
(function () {
    function init() {
        var box = document.getElementById("accModalidades");
        if (!box) return;

        var total = document.getElementById("totalModalidades");
        var busca = document.getElementById("buscaModalidade");
        var grupos = box.querySelectorAll(".modalidade-grupo");

        function atualizarContadores() {
            var n = 0;
            grupos.forEach(function (g) {
                var sel = g.querySelectorAll(".chk-modalidade:checked").length;
                var badge = g.querySelector(".contador-grupo");
                if (badge) {
                    badge.textContent = sel;
                    badge.style.display = sel ? "" : "none";
                }
                n += sel;
            });
            if (total) total.textContent = n;
        }

        box.querySelectorAll(".chk-modalidade").forEach(function (c) {
            c.addEventListener("change", atualizarContadores);
        });
        atualizarContadores();

        if (busca) {
            busca.addEventListener("input", function () {
                var q = this.value.toLowerCase().trim();
                grupos.forEach(function (g) {
                    var visiveis = 0;
                    g.querySelectorAll(".modalidade-item").forEach(function (it) {
                        var match = it.textContent.toLowerCase().indexOf(q) !== -1;
                        it.style.display = match ? "" : "none";
                        if (match) visiveis++;
                    });
                    g.style.display = visiveis > 0 ? "" : "none";
                });
            });
        }
    }

    if (document.readyState !== "loading") init();
    else document.addEventListener("DOMContentLoaded", init);
})();
