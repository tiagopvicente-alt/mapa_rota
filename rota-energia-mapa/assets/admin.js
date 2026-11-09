(function () {
	var total = document.getElementById('rem-total');
	var caixas = document.querySelectorAll('.rem-grupo input[type=checkbox]');
	var pesquisa = document.getElementById('rem-pesquisa');
	function contar() {
		total.textContent = document.querySelectorAll('.rem-grupo input:checked').length;
	}
	caixas.forEach(function (c) { c.addEventListener('change', contar); });
	contar();
	pesquisa.addEventListener('input', function () {
		var q = pesquisa.value.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
		document.querySelectorAll('.rem-grupo').forEach(function (g) {
			var algum = false;
			g.querySelectorAll('label').forEach(function (l) {
				var ok = l.getAttribute('data-nome').indexOf(q) !== -1;
				l.classList.toggle('rem-oculto', !ok);
				algum = algum || ok;
			});
			g.classList.toggle('rem-oculto', !algum);
		});
	});
})();
