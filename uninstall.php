<?php
/**
 * Verwijder opgeslagen instellingen bij het verwijderen van de plugin.
 *
 * Alle instellingen (inclusief het zwevende menu) staan in de enkele optie
 * "whl_settings". Op multisite wordt de optie per site verwijderd.
 *
 * @package WooHeaderLink
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Verwijder de plugin-optie voor één site.
 */
function whl_uninstall_site() {
	delete_option( 'whl_settings' );
}

if ( is_multisite() ) {
	$whl_site_ids = get_sites( array( 'fields' => 'ids' ) );
	foreach ( $whl_site_ids as $whl_site_id ) {
		switch_to_blog( $whl_site_id );
		whl_uninstall_site();
		restore_current_blog();
	}
} else {
	whl_uninstall_site();
}
