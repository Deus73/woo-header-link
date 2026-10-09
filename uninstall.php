<?php
/**
 * Verwijder opgeslagen instellingen bij het verwijderen van de plugin.
 *
 * @package WooHeaderLink
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'whl_settings' );
