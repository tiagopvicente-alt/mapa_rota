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
		L.control.zoom({ position: regiao === 'madeira' ? 'topleft' : 'topright', zoomInTitle: 'Aproximar', zoomOutTitle: 'Afastar' }).addTo(mapa);
		if (mapa.attributionControl) {
			mapa.attributionControl.setPrefix(false);
		}

		if (cfg.tiles) {
			L.tileLayer(cfg.tiles, { attribution: cfg.atrib || '', maxZoom: 18 }).addTo(mapa);
		}

		var limites = LIMITES[regiao];
		function ajustar() {
			mapa.invalidateSize(false);
			mapa.fitBounds(limites, { paddingTopLeft: [10, 10], paddingBottomRight: [10, 34], animate: false });
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

	function iniciar() {
		document.querySelectorAll('.rem-seccao[data-rem-config]').forEach(function (seccao) {
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
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', iniciar);
	} else {
		iniciar();
	}
})();
