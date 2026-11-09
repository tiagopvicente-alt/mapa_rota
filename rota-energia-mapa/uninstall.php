<?php
/**
 * Remove as opções do plugin.
 *
 * @package RotaEnergiaMapa
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
delete_option( 'rem_visitados' );
delete_option( 'rem_tiles_url' );
delete_option( 'rem_tiles_atrib' );
