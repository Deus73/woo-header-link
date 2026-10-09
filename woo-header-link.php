<?php
/**
 * Plugin Name:       Woo Header Link
 * Plugin URI:        https://github.com/Deus73/woo-header-link
 * Description:       Zet een klikbare afbeelding of link in de linkerbovenhoek van je (WooCommerce) site. Opent in een nieuw venster en is volledig in te stellen via Instellingen.
 * Version:           1.0.0
 * Author:            Deus Dust
 * Author URI:        https://github.com/Deus73
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       woo-header-link
 * Domain Path:       /languages
 * Requires at least: 5.6
 * Requires PHP:      7.2
 *
 * @package WooHeaderLink
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Geen directe toegang.
}

define( 'WHL_VERSION', '1.0.0' );
define( 'WHL_FILE', __FILE__ );
define( 'WHL_DIR', plugin_dir_path( __FILE__ ) );
define( 'WHL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Standaard instellingen.
 *
 * @return array
 */
function whl_defaults() {
	return array(
		'url'            => '',
		'link_text'      => '',
		'image_id'       => 0,
		'image_url'      => '',
		'new_tab'        => 1,
		'image_height'   => 40,
		'position'       => 'fixed',
		'hook'           => 'wp_body_open',
		'only_woo'       => 0,
		'offset_top'     => 8,
		'offset_left'    => 8,
		'z_index'        => 9999,
		'custom_class'   => '',
		'hide_on_mobile' => 0,
	);
}

/**
 * Haal de opgeslagen instellingen op, samengevoegd met de defaults.
 *
 * @return array
 */
function whl_get_options() {
	$saved = get_option( 'whl_settings', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return wp_parse_args( $saved, whl_defaults() );
}

/**
 * Bij activatie: zet defaults klaar (zonder bestaande waarden te overschrijven).
 */
function whl_activate() {
	if ( false === get_option( 'whl_settings' ) ) {
		add_option( 'whl_settings', whl_defaults() );
	}
}
register_activation_hook( __FILE__, 'whl_activate' );

/* -------------------------------------------------------------------------
 *  Admin: instellingenpagina
 * ---------------------------------------------------------------------- */

/**
 * Registreer de instellingen, sectie en velden.
 */
function whl_register_settings() {
	register_setting(
		'whl_settings_group',
		'whl_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'whl_sanitize_settings',
			'default'           => whl_defaults(),
		)
	);
}
add_action( 'admin_init', 'whl_register_settings' );

/**
 * Schoon alle invoer op.
 *
 * @param array $input Ruwe invoer.
 * @return array
 */
function whl_sanitize_settings( $input ) {
	$defaults = whl_defaults();
	$clean    = array();

	$clean['url']       = isset( $input['url'] ) ? esc_url_raw( trim( $input['url'] ) ) : '';
	$clean['link_text'] = isset( $input['link_text'] ) ? sanitize_text_field( $input['link_text'] ) : '';
	$clean['image_id']  = isset( $input['image_id'] ) ? absint( $input['image_id'] ) : 0;

	// Afbeelding-URL: bij voorkeur afleiden uit de attachment-ID.
	$clean['image_url'] = '';
	if ( $clean['image_id'] ) {
		$src = wp_get_attachment_image_src( $clean['image_id'], 'full' );
		if ( $src ) {
			$clean['image_url'] = esc_url_raw( $src[0] );
		}
	} elseif ( ! empty( $input['image_url'] ) ) {
		$clean['image_url'] = esc_url_raw( trim( $input['image_url'] ) );
	}

	$clean['new_tab']      = empty( $input['new_tab'] ) ? 0 : 1;
	$clean['image_height'] = isset( $input['image_height'] ) ? max( 8, min( 400, absint( $input['image_height'] ) ) ) : 40;

	$allowed_positions    = array( 'fixed', 'absolute', 'inline' );
	$clean['position']    = ( isset( $input['position'] ) && in_array( $input['position'], $allowed_positions, true ) ) ? $input['position'] : 'fixed';

	// Alleen geldige hook-namen toestaan.
	$hook                 = isset( $input['hook'] ) ? trim( $input['hook'] ) : $defaults['hook'];
	$clean['hook']        = preg_match( '/^[A-Za-z0-9_\-]+$/', $hook ) ? $hook : $defaults['hook'];

	$clean['only_woo']       = empty( $input['only_woo'] ) ? 0 : 1;
	$clean['hide_on_mobile'] = empty( $input['hide_on_mobile'] ) ? 0 : 1;
	$clean['offset_top']     = isset( $input['offset_top'] ) ? max( 0, min( 2000, absint( $input['offset_top'] ) ) ) : 8;
	$clean['offset_left']    = isset( $input['offset_left'] ) ? max( 0, min( 2000, absint( $input['offset_left'] ) ) ) : 8;
	$clean['z_index']        = isset( $input['z_index'] ) ? max( 0, min( 1000000, absint( $input['z_index'] ) ) ) : 9999;
	$clean['custom_class']   = isset( $input['custom_class'] ) ? sanitize_html_class( $input['custom_class'] ) : '';

	return $clean;
}

/**
 * Voeg de menu-item toe onder "Instellingen".
 */
function whl_add_menu() {
	add_options_page(
		__( 'Woo Header Link', 'woo-header-link' ),
		__( 'Woo Header Link', 'woo-header-link' ),
		'manage_options',
		'woo-header-link',
		'whl_render_settings_page'
	);
}
add_action( 'admin_menu', 'whl_add_menu' );

/**
 * Laad admin-assets alleen op onze pagina.
 *
 * @param string $hook Huidige admin pagina hook.
 */
function whl_admin_assets( $hook ) {
	if ( 'settings_page_woo-header-link' !== $hook ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'whl-admin', WHL_URL . 'assets/admin.css', array(), WHL_VERSION );
	wp_enqueue_script( 'whl-admin', WHL_URL . 'assets/admin.js', array( 'jquery' ), WHL_VERSION, true );
	wp_localize_script(
		'whl-admin',
		'WHL_ADMIN',
		array(
			'title'  => __( 'Kies een afbeelding', 'woo-header-link' ),
			'button' => __( 'Gebruik deze afbeelding', 'woo-header-link' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'whl_admin_assets' );

/**
 * Teken de instellingenpagina.
 */
function whl_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$opts  = whl_get_options();
	$image = '';
	if ( ! empty( $opts['image_url'] ) ) {
		$image = $opts['image_url'];
	}
	?>
	<div class="wrap whl-wrap">
		<h1><?php echo esc_html__( 'Woo Header Link', 'woo-header-link' ); ?></h1>
		<p class="description">
			<?php echo esc_html__( 'Toon een klikbare afbeelding of link in de linkerbovenhoek van je site. Gebruik de shortcode [header_link] of het widget om de link in de header van je thema te plaatsen.', 'woo-header-link' ); ?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'whl_settings_group' ); ?>

			<h2><?php echo esc_html__( 'Link', 'woo-header-link' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="whl_url"><?php echo esc_html__( 'URL waarnaar gelinkt wordt', 'woo-header-link' ); ?></label></th>
					<td>
						<input type="url" id="whl_url" name="whl_settings[url]" value="<?php echo esc_attr( $opts['url'] ); ?>" class="regular-text code" placeholder="https://" />
						<p class="description"><?php echo esc_html__( 'Plak hier de volledige URL, inclusief https://.', 'woo-header-link' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="whl_link_text"><?php echo esc_html__( 'Linktekst (optioneel)', 'woo-header-link' ); ?></label></th>
					<td>
						<input type="text" id="whl_link_text" name="whl_settings[link_text]" value="<?php echo esc_attr( $opts['link_text'] ); ?>" class="regular-text" />
						<p class="description"><?php echo esc_html__( 'Wordt gebruikt als er geen afbeelding is gekozen, of als tekst naast de afbeelding.', 'woo-header-link' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html__( 'Openen', 'woo-header-link' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="whl_settings[new_tab]" value="1" <?php checked( 1, $opts['new_tab'] ); ?> />
							<?php echo esc_html__( 'Openen in een nieuw venster/tab', 'woo-header-link' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<h2><?php echo esc_html__( 'Afbeelding', 'woo-header-link' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php echo esc_html__( 'Afbeelding', 'woo-header-link' ); ?></th>
					<td>
						<div class="whl-image-field">
							<div class="whl-image-preview"<?php echo $image ? '' : ' style="display:none"'; ?>>
								<img id="whl-image-preview-img" src="<?php echo esc_url( $image ); ?>" alt="" />
							</div>
							<input type="hidden" id="whl_image_id" name="whl_settings[image_id]" value="<?php echo esc_attr( $opts['image_id'] ); ?>" />
							<input type="url" id="whl_image_url" name="whl_settings[image_url]" value="<?php echo esc_attr( $opts['image_url'] ); ?>" class="regular-text code" placeholder="https://.../logo.png" />
							<p>
								<button type="button" class="button" id="whl-select-image"><?php echo esc_html__( 'Afbeelding kiezen', 'woo-header-link' ); ?></button>
								<button type="button" class="button" id="whl-remove-image"<?php echo $opts['image_id'] || $opts['image_url'] ? '' : ' style="display:none"'; ?>><?php echo esc_html__( 'Verwijderen', 'woo-header-link' ); ?></button>
							</p>
							<p class="description"><?php echo esc_html__( 'Kies een afbeelding uit de mediabibliotheek of plak een directe afbeeldings-URL.', 'woo-header-link' ); ?></p>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="whl_image_height"><?php echo esc_html__( 'Hoogte (px)', 'woo-header-link' ); ?></label></th>
					<td>
						<input type="number" id="whl_image_height" name="whl_settings[image_height]" value="<?php echo esc_attr( $opts['image_height'] ); ?>" min="8" max="400" step="1" class="small-text" />
						<p class="description"><?php echo esc_html__( 'Hoogte van de afbeelding in pixels. De breedte schaalt automatisch mee.', 'woo-header-link' ); ?></p>
					</td>
				</tr>
			</table>

			<h2><?php echo esc_html__( 'Positie', 'woo-header-link' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="whl_position"><?php echo esc_html__( 'Positionering', 'woo-header-link' ); ?></label></th>
					<td>
						<select id="whl_position" name="whl_settings[position]">
							<option value="fixed" <?php selected( 'fixed', $opts['position'] ); ?>><?php echo esc_html__( 'Vast in het scherm (linkerbovenhoek)', 'woo-header-link' ); ?></option>
							<option value="absolute" <?php selected( 'absolute', $opts['position'] ); ?>><?php echo esc_html__( 'Absoluut in de header (linkerbovenhoek)', 'woo-header-link' ); ?></option>
							<option value="inline" <?php selected( 'inline', $opts['position'] ); ?>><?php echo esc_html__( 'Inline (op de plek van de hook/shortcode)', 'woo-header-link' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="whl_hook"><?php echo esc_html__( 'Thema-hook', 'woo-header-link' ); ?></label></th>
					<td>
						<input type="text" id="whl_hook" name="whl_settings[hook]" value="<?php echo esc_attr( $opts['hook'] ); ?>" class="regular-text code" />
						<p class="description"><?php echo esc_html__( 'Waar de link wordt ingevoegd. Standaard wp_body_open (werkt bij de meeste thema\'s). Voorbeelden: storefront_before_header, astra_header_before, get_header.', 'woo-header-link' ); ?></p>
					</td>
				</tr>
				<tr class="whl-offsets">
					<th scope="row"><?php echo esc_html__( 'Afstand (px)', 'woo-header-link' ); ?></th>
					<td>
						<label><?php echo esc_html__( 'Van boven:', 'woo-header-link' ); ?> <input type="number" name="whl_settings[offset_top]" value="<?php echo esc_attr( $opts['offset_top'] ); ?>" min="0" max="2000" class="small-text" /></label>
						&nbsp;
						<label><?php echo esc_html__( 'Van links:', 'woo-header-link' ); ?> <input type="number" name="whl_settings[offset_left]" value="<?php echo esc_attr( $opts['offset_left'] ); ?>" min="0" max="2000" class="small-text" /></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="whl_z_index"><?php echo esc_html__( 'Z-index', 'woo-header-link' ); ?></label></th>
					<td>
						<input type="number" id="whl_z_index" name="whl_settings[z_index]" value="<?php echo esc_attr( $opts['z_index'] ); ?>" min="0" max="1000000" class="small-text" />
						<p class="description"><?php echo esc_html__( 'Hoger = bovenop andere elementen.', 'woo-header-link' ); ?></p>
					</td>
				</tr>
			</table>

			<h2><?php echo esc_html__( 'Weergave', 'woo-header-link' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php echo esc_html__( 'Beperken', 'woo-header-link' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="whl_settings[only_woo]" value="1" <?php checked( 1, $opts['only_woo'] ); ?> />
							<?php echo esc_html__( 'Alleen tonen op WooCommerce-pagina\'s', 'woo-header-link' ); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="whl_settings[hide_on_mobile]" value="1" <?php checked( 1, $opts['hide_on_mobile'] ); ?> />
							<?php echo esc_html__( 'Verbergen op mobiel (scherm < 768px)', 'woo-header-link' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="whl_custom_class"><?php echo esc_html__( 'Extra CSS-class', 'woo-header-link' ); ?></label></th>
					<td>
						<input type="text" id="whl_custom_class" name="whl_settings[custom_class]" value="<?php echo esc_attr( $opts['custom_class'] ); ?>" class="regular-text code" />
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>

		<hr />
		<h2><?php echo esc_html__( 'Handmatig plaatsen', 'woo-header-link' ); ?></h2>
		<p><?php echo esc_html__( 'Kopieer deze shortcode in een HTML-blok, header-widget of je thema-header:', 'woo-header-link' ); ?></p>
		<p><code>[header_link]</code></p>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 *  Front-end: de link renderen
 * ---------------------------------------------------------------------- */

/**
 * Bepaal of de link op de huidige pagina getoond moet worden.
 *
 * @return bool
 */
function whl_should_display() {
	$opts = whl_get_options();

	if ( ! empty( $opts['only_woo'] ) ) {
		$is_woo = ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) );
		if ( ! $is_woo ) {
			return false;
		}
	}

	return true;
}

/**
 * Bouw de HTML voor de link.
 *
 * @param bool $force_inline Forceer inline weergave (voor shortcode/widget).
 * @return string
 */
function whl_get_html( $force_inline = false ) {
	$opts = whl_get_options();

	if ( empty( $opts['url'] ) && empty( $opts['image_url'] ) && empty( $opts['link_text'] ) ) {
		return '';
	}
	if ( ! whl_should_display() ) {
		return '';
	}

	$is_new_tab = ! empty( $opts['new_tab'] );
	$target     = $is_new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
	$url        = $opts['url'] ? $opts['url'] : '#';

	$classes = array( 'whl-link' );
	if ( $force_inline ) {
		$classes[] = 'whl-inline';
	} else {
		$classes[] = 'whl-' . $opts['position'];
	}
	if ( ! empty( $opts['hide_on_mobile'] ) ) {
		$classes[] = 'whl-hide-mobile';
	}
	if ( ! empty( $opts['custom_class'] ) ) {
		$classes[] = $opts['custom_class'];
	}

	// Inline styles alleen nodig voor fixed/absolute.
	$style = '';
	if ( ! $force_inline && 'inline' !== $opts['position'] ) {
		$style = sprintf(
			'position:%1$s;top:%2$dpx;left:%3$dpx;z-index:%4$d;',
			$opts['position'],
			$opts['offset_top'],
			$opts['offset_left'],
			$opts['z_index']
		);
	}

	$inner = '';
	if ( ! empty( $opts['image_url'] ) ) {
		$inner .= sprintf(
			'<img src="%1$s" alt="%2$s" style="height:%3$dpx;width:auto;display:block;" />',
			esc_url( $opts['image_url'] ),
			esc_attr( $opts['link_text'] ),
			(int) $opts['image_height']
		);
	}
	if ( ! empty( $opts['link_text'] ) ) {
		$inner .= '<span class="whl-text">' . esc_html( $opts['link_text'] ) . '</span>';
	}

	$html  = sprintf(
		'<a class="%1$s" href="%2$s"%3$s%4$s>%5$s</a>',
		esc_attr( implode( ' ', $classes ) ),
		esc_url( $url ),
		$target,
		$style ? ' style="' . esc_attr( $style ) . '"' : '',
		$inner
	);

	return $html;
}

/**
 * Echo de link op de gekozen hook.
 */
function whl_render() {
	echo whl_get_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is reeds ge-escaped.
}

/**
 * Koppel de weergave aan de gekozen hook.
 */
function whl_hook_render() {
	$opts = whl_get_options();
	$hook = $opts['hook'];

	// Voorkom oneindige lus of ongeldige lege hook.
	if ( empty( $hook ) || 'whl_render' === $hook ) {
		return;
	}

	add_action( $hook, 'whl_render', 5 );
}
add_action( 'template_redirect', 'whl_hook_render' );

/**
 * Shortcode [header_link] voor handmatige plaatsing.
 *
 * @param array $atts Attributen.
 * @return string
 */
function whl_shortcode( $atts ) {
	$atts = shortcode_atts( array(), (array) $atts, 'header_link' );
	return whl_get_html( true );
}
add_shortcode( 'header_link', 'whl_shortcode' );

/**
 * Laad de front-end CSS.
 */
function whl_frontend_assets() {
	wp_enqueue_style( 'whl-frontend', WHL_URL . 'assets/frontend.css', array(), WHL_VERSION );

	$opts      = whl_get_options();
	$inline_css = '';
	if ( ! empty( $opts['hide_on_mobile'] ) ) {
		$inline_css .= '@media (max-width:767px){.whl-link.whl-hide-mobile{display:none !important;}}';
	}
	if ( $inline_css ) {
		wp_add_inline_style( 'whl-frontend', $inline_css );
	}
}
add_action( 'wp_enqueue_scripts', 'whl_frontend_assets' );

/* -------------------------------------------------------------------------
 *  Widget voor thema's met een header-widgetgebied
 * ---------------------------------------------------------------------- */

/**
 * Widget: toont de link in een widgetgebied.
 */
class WHL_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'whl_widget',
			__( 'Woo Header Link', 'woo-header-link' ),
			array( 'description' => __( 'Toont de ingestelde header-link in een widgetgebied.', 'woo-header-link' ) )
		);
	}

	/**
	 * Front-end weergave.
	 *
	 * @param array $args     Widget-args.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		$html = whl_get_html( true );
		if ( '' === $html ) {
			return;
		}
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Admin formulier.
	 *
	 * @param array $instance Instance.
	 */
	public function form( $instance ) {
		echo '<p>' . esc_html__( 'Deze widget toont de link die je onder Instellingen > Woo Header Link hebt ingesteld.', 'woo-header-link' ) . '</p>';
	}

	/**
	 * Opslaan (geen opties).
	 *
	 * @param array $new_instance Nieuwe waarden.
	 * @param array $old_instance Oude waarden.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return $old_instance;
	}
}

/**
 * Registreer de widget.
 */
function whl_register_widget() {
	register_widget( 'WHL_Widget' );
}
add_action( 'widgets_init', 'whl_register_widget' );
