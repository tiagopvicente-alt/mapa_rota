(function () {
	'use strict';

	var LIMITES = {
		continente: [[36.9, -9.6], [42.2, -6.15]],
		madeira: [[32.35, -17.35], [33.15, -16.2]],
		acores: [[36.85, -31.4], [39.8, -24.9]]
	};

	function criarMapa(el, regiao, geo, visitados, cfg) {
		var mapa = L.map(el, {
			zoomSnap: 0,
			zoomDelta: 1,
			scrollWheelZoom: true,
			wheelPxPerZoomLevel: 120,
			doubleClickZoom: false,
			boxZoom: false,
			attributionControl: !!cfg.tiles,
			zoomControl: false
		});
		var zoom = L.control.zoom({ position: 'topright', zoomInTitle: 'Aproximar', zoomOutTitle: 'Afastar' }).addTo(mapa);
		// Os botões ficam numa faixa própria do painel, fora da área do mapa.
		el.parentNode.appendChild(zoom.getContainer());
		if (mapa.attributionControl) {
			mapa.attributionControl.setPrefix(false);
		}

		if (cfg.tiles) {
			L.tileLayer(cfg.tiles, { attribution: cfg.atrib || '', maxZoom: 18 }).addTo(mapa);
		}

		var limites = LIMITES[regiao];
		function ajustar() {
			mapa.invalidateSize(false);
			mapa.fitBounds(limites, { padding: [6, 6], animate: false });
			mapa.setMinZoom(mapa.getZoom());
			mapa.setMaxZoom(mapa.getZoom() + 6);
		}
		ajustar();

		var daRegiao = geo.features.filter(function (f) { return f.properties.r === regiao; });
		var estilo = { fillOpacity: 1, weight: 0.6 };

		L.geoJSON(
			{ type: 'FeatureCollection', features: daRegiao.filter(function (f) { return !visitados[f.properties.c]; }) },
			{ interactive: false, style: function () { return L.extend({ className: 'rem-m' }, estilo); } }
		).addTo(mapa);

		L.geoJSON(
			{ type: 'FeatureCollection', features: daRegiao.filter(function (f) { return visitados[f.properties.c]; }) },
			{
				style: function () { return L.extend({ className: 'rem-m rem-m--visitado' }, estilo); },
				onEachFeature: function (f, camada) {
					camada.bindTooltip(f.properties.n, { sticky: true, direction: 'top', offset: [0, -8], className: 'rem-tooltip', opacity: 1 });
				}
			}
		).addTo(mapa);

		if (window.ResizeObserver) {
			var largura = el.clientWidth;
			new ResizeObserver(function () {
				if (el.clientWidth && el.clientWidth !== largura) {
					largura = el.clientWidth;
					ajustar();
				}
			}).observe(el);
		}
	}

	function iniciarSeccao(seccao) {
		if (seccao.getAttribute('data-rem-iniciado')) {
			return;
		}
		seccao.setAttribute('data-rem-iniciado', '1');
		var cfg = JSON.parse(seccao.getAttribute('data-rem-config'));
		var visitados = {};
		cfg.visitados.forEach(function (c) { visitados[c] = true; });
		fetch(cfg.dados)
			.then(function (r) { return r.json(); })
			.then(function (geo) {
				seccao.querySelectorAll('[data-rem-regiao]').forEach(function (el) {
					criarMapa(el, el.getAttribute('data-rem-regiao'), geo, visitados, cfg);
				});
			});
	}

	function iniciar(raiz) {
		(raiz || document).querySelectorAll('.rem-seccao[data-rem-config]').forEach(iniciarSeccao);
	}

	function ligarElementor() {
		window.elementorFrontend.hooks.addAction('frontend/element_ready/rota_energia_mapa.default', function ($el) {
			iniciar($el[0]);
		});
	}

	if (window.elementorFrontend && window.elementorFrontend.hooks) {
		ligarElementor();
	} else if (window.jQuery) {
		window.jQuery(window).on('elementor/frontend/init', ligarElementor);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { iniciar(); });
	} else {
		iniciar();
	}
})();
