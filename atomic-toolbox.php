<?php
/**
 * Collection of tools commonly used on my sites.
 *
 * @link              https://atomiclotus.net
 * @since             1.0.0
 * @package           Atomic Toolbox
 *
 * @wordpress-plugin
 * Plugin Name:       Atomic Toolbox
 * Plugin URI:        https://atomiclotus.net
 * Description:       Collection of tools commonly used on my sites.
 * Version:           1.0.0
 * Author:            Scott Lewis
 * Author URI:        https://atomiclotus.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       atomic-tools
 */

require_once( trailingslashit( dirname( __FILE__ ) ) . 'inc/index.php' );

class AtomicToolbox {

    private static $current_url;

    /**
     * Do some WP cleanup.
     */
    function __construct() {
        self::remove_emojis();
    }

    public static function remove_emojis() {
        remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
        remove_action( 'wp_print_styles',     'print_emoji_styles' );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'admin_print_styles',  'print_emoji_styles' );
    }

    /**
     * Remove the JetPack OpenGraph tags
     */
    public static function clean( $header ) {

        remove_action( 'wp_head', 'jetpack_og_tags' );
    }

}


add_action( 'template_redirect', array( 'AtomicToolbox', 'clean' ) );