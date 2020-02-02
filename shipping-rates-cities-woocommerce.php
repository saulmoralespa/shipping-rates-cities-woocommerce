<?php
/**
 * Plugin Name: Shipping Rates Cities Woocommerce
 * Description: Shipping Rates by Cities for Woocommerce is available for Colombia
 * Version: 1.0.1
 * Author: Saul Morales Pacheco
 * Author URI: https://saulmoralespa.com
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * WC tested up to: 3.5
 * WC requires at least: 2.6
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if(!defined('SHIPPING_RATES_CITIES_WC_SR_VERSION')){
    define('SHIPPING_RATES_CITIES_WC_SR_VERSION', '1.0.1');
}

add_action( 'plugins_loaded', 'shipping_rates_cities_wc_sr_init', 1 );

function shipping_rates_cities_wc_sr_init(){
    if ( !shipping_rates_cities_wc_sr_requirements() )
        return;

    shipping_rates_cities_wc_sr()->run_shipping_rates_cities();

}

function shipping_rates_cities_wc_sr_notices( $notice ) {
    ?>
    <div class="error notice">
        <p><?php echo esc_html( $notice ); ?></p>
    </div>
    <?php
}

function shipping_rates_cities_wc_sr_requirements(){

    if ( ! extension_loaded( 'xml' ) ){
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    shipping_rates_cities_wc_sr_notices( 'Shipping Rates Cities Woocommerce: Requiere la extensión xml se encuentre instalada' );
                }
            );
        }
        return false;
    }

    if ( ! extension_loaded( 'simplexml' ) ){
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    shipping_rates_cities_wc_sr_notices( 'Shipping Rates Cities Woocommerce: Requiere la extensión simplexml se encuentre instalada' );
                }
            );
        }
        return false;
    }

    if ( !in_array(
        'woocommerce/woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    shipping_rates_cities_wc_sr_notices( 'Shipping Rates Cities Woocommerce: Requiere que se encuentre instalado y activo el plugin: Woocommerce' );
                }
            );
        }
        return false;
    }

    if ( ! in_array(
        'departamentos-y-ciudades-de-colombia-para-woocommerce/departamentos-y-ciudades-de-colombia-para-woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    shipping_rates_cities_wc_sr_notices( 'Shipping Rates Cities Woocommerce: Requiere que se encuentre instalado y activo el plugin: Departamentos y ciudades de Colombia para Woocommerce' );
                }
            );
        }
        return false;
    }

    $woo_countries   = new WC_Countries();
    $default_country = $woo_countries->get_base_country();

    if ( ! in_array( $default_country, array( 'CO' ), true ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    $country = 'Shipping Rates Cities Woocommerce: Requiere que el país donde se encuentra ubicada la tienda sea Colombia '  .
                        sprintf(
                            '%s',
                            '<a href="' . admin_url() .
                            'admin.php?page=wc-settings&tab=general#s2id_woocommerce_currency">' .
                            'Click para establecer</a>' );
                    shipping_rates_cities_wc_sr_notices( $country );
                }
            );
        }
        return false;
    }

    return true;
}

function shipping_rates_cities_wc_sr(){
    static $plugin;
    if (!isset($plugin)){
        require_once('includes/class-shipping-rates-cities-woocommerce-plugin.php');
        $plugin = new Shipping_Rates_Cities_Woocommerce_WC_Plugin(__FILE__, SHIPPING_RATES_CITIES_WC_SR_VERSION);
    }
    return $plugin;
}