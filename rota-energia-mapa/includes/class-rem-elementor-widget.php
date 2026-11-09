<?php
/**
 * Widget do Elementor para o mapa da Rota da Energia.
 *
 * @package RotaEnergiaMapa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget "Mapa da Rota da Energia".
 */
class REM_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Identificador do widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'rota_energia_mapa';
	}

	/**
	 * Nome apresentado no painel.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'Mapa da Rota da Energia';
	}

	/**
	 * Ícone do widget.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-map-pin';
	}

	/**
	 * Categorias do painel.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Palavras de pesquisa.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'mapa', 'rota', 'energia', 'municípios', 'adene' );
	}

	/**
	 * Folhas de estilo necessárias.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'rem-mapa' );
	}

	/**
	 * Scripts necessários.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'rem-mapa' );
	}

	/**
	 * Mostra o widget.
	 */
	protected function render() {
		echo rem_render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escapado em rem_render().
	}
}
