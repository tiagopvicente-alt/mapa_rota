<?php
/**
 * Página de gestão no wp-admin.
 *
 * @package RotaEnergiaMapa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	function () {
		add_menu_page( 'Mapa da Rota da Energia', 'Mapa da Rota', 'manage_options', 'rota-energia-mapa', 'rem_pagina_admin', 'dashicons-location-alt', 58 );
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( 'toplevel_page_rota-energia-mapa' === $hook ) {
			wp_enqueue_style( 'rem-admin', REM_URL . 'assets/admin.css', array(), REM_VERSION );
			wp_enqueue_script( 'rem-admin', REM_URL . 'assets/admin.js', array(), REM_VERSION, true );
		}
	}
);

add_action(
	'admin_post_rem_guardar',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Sem permissões.', 403 );
		}
		check_admin_referer( 'rem_guardar' );

		$recebidos = isset( $_POST['visitados'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['visitados'] ) ) : array();
		$validos   = array_values( array_intersect( $recebidos, array_map( 'strval', array_keys( rem_dados()['municipios'] ) ) ) );
		update_option( 'rem_visitados', $validos );

		$url = isset( $_POST['tiles_url'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['tiles_url'] ) ) ) : '';
		if ( '' !== $url && ! preg_match( '#^https://[^\s"\'<>]+\{z\}[^\s"\'<>]*$#i', $url ) ) {
			$url = '';
		}
		update_option( 'rem_tiles_url', $url );

		$atrib = isset( $_POST['tiles_atrib'] ) ? wp_kses( wp_unslash( $_POST['tiles_atrib'] ), array( 'a' => array( 'href' => array() ) ) ) : '';
		update_option( 'rem_tiles_atrib', $atrib );

		wp_safe_redirect( add_query_arg( array( 'page' => 'rota-energia-mapa', 'guardado' => '1' ), admin_url( 'admin.php' ) ) );
		exit;
	}
);

/**
 * Desenha a página de gestão.
 */
function rem_pagina_admin() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$visitados = array_flip( rem_visitados() );
	$grupos    = array();
	foreach ( rem_dados()['municipios'] as $codigo => $m ) {
		$grupos[ $m[1] ][ (string) $codigo ] = $m[0];
	}
	uksort(
		$grupos,
		function ( $a, $b ) {
			$ilhas = ( 0 === strpos( $a, 'Açores' ) || 'Madeira' === $a ) <=> ( 0 === strpos( $b, 'Açores' ) || 'Madeira' === $b );
			return $ilhas ? $ilhas : strcmp( remove_accents( $a ), remove_accents( $b ) );
		}
	);
	?>
	<div class="wrap rem-admin">
		<h1>Mapa da Rota da Energia</h1>
		<?php if ( isset( $_GET['guardado'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p>Alterações guardadas.</p></div>
		<?php endif; ?>
		<p>Marque os municípios onde a Rota da Energia já esteve. Para mostrar o mapa numa página, use o shortcode <code>[rota_energia_mapa]</code>.</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="rem_guardar">
			<?php wp_nonce_field( 'rem_guardar' ); ?>

			<p class="rem-ferramentas">
				<input type="search" id="rem-pesquisa" placeholder="Pesquisar município" aria-label="Pesquisar município">
				<strong><span id="rem-total">0</span> de 308 municípios marcados</strong>
			</p>

			<div class="rem-grupos">
				<?php foreach ( $grupos as $grupo => $itens ) : ?>
					<fieldset class="rem-grupo">
						<legend><?php echo esc_html( $grupo ); ?></legend>
						<?php foreach ( $itens as $codigo => $nome ) : ?>
							<label data-nome="<?php echo esc_attr( remove_accents( mb_strtolower( $nome ) ) ); ?>">
								<input type="checkbox" name="visitados[]" value="<?php echo esc_attr( $codigo ); ?>" <?php checked( isset( $visitados[ $codigo ] ) ); ?>>
								<?php echo esc_html( $nome ); ?>
							</label>
						<?php endforeach; ?>
					</fieldset>
				<?php endforeach; ?>
			</div>

			<h2>Mapa de fundo</h2>
			<p>Opcional. Sem URL, o mapa mostra apenas os limites dos municípios e não contacta serviços externos.</p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="rem-tiles-url">URL dos mosaicos</label></th>
					<td>
						<input class="large-text code" type="text" id="rem-tiles-url" name="tiles_url" value="<?php echo esc_attr( get_option( 'rem_tiles_url', '' ) ); ?>" placeholder="https://api.maptiler.com/maps/dataviz-dark/{z}/{x}/{y}.png?key=A_SUA_CHAVE">
						<p class="description">Tem de começar por https:// e incluir {z}, {x} e {y}.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="rem-tiles-atrib">Atribuição</label></th>
					<td><input class="large-text" type="text" id="rem-tiles-atrib" name="tiles_atrib" value="<?php echo esc_attr( get_option( 'rem_tiles_atrib', '' ) ); ?>" placeholder="© MapTiler © OpenStreetMap"></td>
				</tr>
			</table>
			<?php submit_button( 'Guardar alterações' ); ?>
		</form>
	</div>
	<?php
}
