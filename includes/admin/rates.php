<?php

$this->init_settings();

global $woocommerce;

$general_settings = get_option('woocommerce_shipping_rates_cities_wc_sr_settings');

$title = isset($general_settings['shipping_rates_cities_wc_sr_db']) && $general_settings['shipping_rates_cities_wc_sr_db'] === true ? 'Resubir Archivo .xls (costo de envío)' : 'Subir Archivo .xls (costo de envío)';

$htmlRates = '
<table>
    <tr valign="top">
        <td style="width:25%;font-weight:bold;padding-top:40px;">
            <label for="servientrega_upload_matriz">' . $title . '</label><span class="woocommerce-help-tip" data-tip="' . __('Suba el archivo excel con extensión .xsl, este archivo sube información a a la base de datos (id_ciudad_destino, oste_rate)') . '"></span>
        </td>
        <td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
            <fieldset style="padding:3px;">
                <input id="shipping_rates_cities_wc_sr_upload" accept=".xls" type="file">' .
    wp_nonce_field( "shipping_rates_cities_wc_sr_excel", "shipping_rates_cities_wc_sr_excel" ) . ' 
            </fieldset>
        </td>
     </tr>';

return $htmlRates;