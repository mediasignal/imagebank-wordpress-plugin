<?php
/*
Plugin Name: Kuvapankki Media
Description: Kuvapankki integration plugin
Author: Mediasignal
Text Domain: kuvapankki-media
Domain Path: /languages/
Version: 1.0
*/

define( 'KUVAPANKKI_MEDIA_VERSION', '1.0' );
define( 'KUVAPANKKI_MEDIA_PLUGIN', __FILE__ );
define( 'KUVAPANKKI_MEDIA_PLUGIN_BASENAME', plugin_basename( KUVAPANKKI_MEDIA_PLUGIN ) );
define( 'KUVAPANKKI_MEDIA_PLUGIN_NAME', trim( dirname( KUVAPANKKI_MEDIA_PLUGIN_BASENAME ), '/' ) );
define( 'KUVAPANKKI_MEDIA_PLUGIN_DIR', untrailingslashit( dirname( KUVAPANKKI_MEDIA_PLUGIN ) ) );
define( 'KUVAPANKKI_MEDIA_INTEGRATION_NAME', 'Kuvapankki');

require_once KUVAPANKKI_MEDIA_PLUGIN_DIR . '/includes/class-kuvapankki-api.php';

require_once KUVAPANKKI_MEDIA_PLUGIN_DIR . '/includes/functions.php';
require_once KUVAPANKKI_MEDIA_PLUGIN_DIR . '/includes/functions-kuvapankki.php';


if ( is_admin() ) {
	require_once KUVAPANKKI_MEDIA_PLUGIN_DIR . '/admin/admin.php';
	require_once KUVAPANKKI_MEDIA_PLUGIN_DIR . '/admin/plugin.php';
}

/* Init */

add_action( 'init', 'kuvapankki_init' );

function kuvapankki_init() {

	/* L10N */
	load_plugin_textdomain( 'kuvapankki-media', false, 'kuvapankki-media/languages' );

	do_action( 'kuvapankki_init' );
}
