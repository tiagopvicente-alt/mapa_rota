<?php
/**
 * Plugin Name: Rota da Energia — Mapa de municípios
 * Description: Mapa dos municípios visitados pela Rota da Energia. Use o shortcode [rota_energia_mapa].
 * Version:     1.0.1
 * Author:      ADENE
 * Text Domain: rota-energia-mapa
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package RotaEnergiaMapa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'REM_VERSION', '1.0.1' );
define( 'REM_URL', plugin_dir_url( __FILE__ ) );
define( 'REM_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Devolve os dados dos municípios.
 *
 * @return array
 */
function rem_dados() {
	static $dados = null;
	if ( null === $dados ) {
		$dados = require REM_DIR . 'includes/municipios.php';
	}
	return $dados;
}

/**
 * Códigos dos municípios visitados.
 *
 * @return string[]
 */
function rem_visitados() {
	$guardados = get_option( 'rem_visitados', null );
	return is_array( $guardados ) ? array_values( array_map( 'strval', $guardados ) ) : rem_dados()['visitados'];
}

register_activation_hook(
	__FILE__,
	function () {
		add_option( 'rem_visitados', rem_dados()['visitados'] );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_register_style( 'rem-leaflet', REM_URL . 'assets/vendor/leaflet.css', array(), '1.9.4' );
		wp_register_style( 'rem-mapa', REM_URL . 'assets/mapa.css', array( 'rem-leaflet' ), REM_VERSION );
		wp_register_script( 'rem-leaflet', REM_URL . 'assets/vendor/leaflet.js', array(), '1.9.4', true );
		wp_register_script( 'rem-mapa', REM_URL . 'assets/mapa.js', array( 'rem-leaflet' ), REM_VERSION, true );
	}
);

add_shortcode(
	'rota_energia_mapa',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'titulo' => 'Onde já fomos' ), $atts, 'rota_energia_mapa' );

		wp_enqueue_style( 'rem-mapa' );
		wp_enqueue_script( 'rem-mapa' );

		$municipios = rem_dados()['municipios'];
		$nomes      = array();
		foreach ( rem_visitados() as $codigo ) {
			if ( isset( $municipios[ $codigo ] ) ) {
				$nomes[] = $municipios[ $codigo ][0];
			}
		}
		natcasesort( $nomes );

		$config = array(
			'dados'     => REM_URL . 'assets/municipios.json?v=' . REM_VERSION,
			'visitados' => rem_visitados(),
			'tiles'     => (string) get_option( 'rem_tiles_url', '' ),
			'atrib'     => (string) get_option( 'rem_tiles_atrib', '' ),
		);

		$paineis = array(
			'acores'     => 'Açores',
			'madeira'    => 'Madeira',
			'continente' => 'Portugal continental',
		);

		ob_start();
		?>
		<section class="rem-seccao alignfull" data-rem-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
			<div class="rem-conteudo">
				<div class="rem-cabecalho">
					<span class="rem-marca" aria-hidden="true"><i></i><b></b></span>
					<h2 class="rem-titulo"><?php echo esc_html( $atts['titulo'] ); ?></h2>
				</div>
				<div class="rem-cartao">
					<div class="rem-paineis">
						<?php foreach ( $paineis as $id => $legenda ) : ?>
							<div class="rem-painel rem-painel--<?php echo esc_attr( $id ); ?>">
								<div class="rem-mapa" data-rem-regiao="<?php echo esc_attr( $id ); ?>" role="img" aria-label="<?php echo esc_attr( 'Mapa de municípios: ' . $legenda ); ?>"></div>
								<span class="rem-legenda"><?php echo esc_html( $legenda ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<ul class="rem-sr">
					<?php foreach ( $nomes as $nome ) : ?>
						<li><?php echo esc_html( $nome ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
);

if ( is_admin() ) {
	require REM_DIR . 'includes/admin.php';
}
