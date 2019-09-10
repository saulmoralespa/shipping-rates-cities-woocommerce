<?php

$this->init_settings();

global $woocommerce;


if(isset($_POST['shipping_rates_cities_wc_sr_general_save_changes_button']))
{

    if ( !isset( $_POST['shipping_rates_cities_wc_sr_general'] )
        || !wp_verify_nonce( $_POST['shipping_rates_cities_wc_sr_general'], 'shipping_rates_cities_wc_sr_general' ))
        return;

    $title = sanitize_text_field($_POST['title']);
    $enabled = (isset($_POST['shipping_rates_cities_enabled']) && $_POST['shipping_rates_cities_enabled'] ==='yes') ? true : false;
    $debug = (isset($_POST['shipping_rates_cities_debug']) && $_POST['shipping_rates_cities_debug'] ==='yes') ? true : false;

    $wc_main_settings = get_option('woocommerce_shipping_rates_cities_wc_sr_settings');
    $wc_main_settings['title'] = $title;
    $wc_main_settings['enabled'] = $enabled;
    $wc_main_settings['debug'] = $debug;

    update_option('woocommerce_shipping_rates_cities_wc_sr_settings', $wc_main_settings);
}

$general_settings = get_option('woocommerce_shipping_rates_cities_wc_sr_settings');
$general_settings = empty($general_settings) ? array() : $general_settings;

$value_title = '';

if(isset($general_settings['title']))
    $value_title = $general_settings['title'];

$htmlGeneral = '
<table>
    <tbody>';
$htmlGeneral .= '<tr valign="top">
 <td style="width:25%;padding-top:40px;font-weight:bold;">
            <label for="shipping_rates_cities_enabled">' . __('Activar/Desactivar') . '</label><span class="woocommerce-help-tip" data-tip="' . __('Activar o desactivar el método de envío') . '"></span>
        </td>
        <td scope="row" class="titledesc" style="display:block;margin-bottom:20px;padding-top:40px;">
<fieldset style="padding:3px;">';
if(isset($general_settings['enabled']) && $general_settings['enabled'] === true)
{
    $htmlGeneral .= '<input class="input-text regular-input " type="radio" name="shipping_rates_cities_enabled"  id="shipping_rates_cities_enabled"';
    $htmlGeneral .= 'value="no">' . __('No');
    $htmlGeneral .= '<input class="input-text regular-input " type="radio"  name="shipping_rates_cities_enabled" checked="true" id="shipping_rates_cities_enabled"';
    $htmlGeneral .= 'value="yes">' . __('Sí');
}else {
    $htmlGeneral .= '<input class="input-text regular-input" type="radio" name="shipping_rates_cities_enabled" checked="true" id="shipping_rates_cities_enabled"';
    $htmlGeneral .= 'value="no">' . __('No');
    $htmlGeneral .= '<input class="input-text regular-input" type="radio" name="shipping_rates_cities_enabled" id="shipping_rates_cities_enabled"';
    $htmlGeneral .= 'value="yes">' . __('Sí');
}
$htmlGeneral .= '</fieldset>
       </td>
    </tr>';
$htmlGeneral .= '<tr valign="top">
            <td style="width:25%;font-weight:bold;">
                <label for="shipping_rates_cities_title">' . __('Título') . '</label><span class="woocommerce-help-tip" data-tip="' . __('Corresponde al título que verá el usuario') . '"></span>
            </td>
			<td scope="row" class="titledesc" style="display:block;margin-bottom:20px;">
			    <fieldset>
			        <input class="input-text regular-input" type="text" name="title" value="'.$value_title.'" required>
			    </fieldset>
			</td>
		</tr>';
$htmlGeneral .= '<tr valign="top">
 <td style="width:25%;font-weight:bold;">
            <label for="shipping_rates_cities_debug">' . __('Activar debug') . '</label><span class="woocommerce-help-tip" data-tip="' . __('Generar registros de depuración') . '"></span>
        </td>
        <td scope="row" class="titledesc" style="display:block;margin-bottom:20px;">
<fieldset style="padding:3px;">';
if(isset($general_settings['debug']) && $general_settings['debug'] === true)
{
    $htmlGeneral .= '<input class="input-text regular-input " type="radio" name="shipping_rates_cities_debug"  id="shipping_rates_cities_debug"';
    $htmlGeneral .= 'value="no">' . __('No');
    $htmlGeneral .= '<input class="input-text regular-input " type="radio"  name="shipping_rates_cities_debug" checked="true" id="shipping_rates_cities_debug"';
    $htmlGeneral .= 'value="yes">' . __('Sí');
}else {
    $htmlGeneral .= '<input class="input-text regular-input" type="radio" name="shipping_rates_cities_debug" checked="true" id="shipping_rates_cities_debug"';
    $htmlGeneral .= 'value="no">' . __('No');
    $htmlGeneral .= '<input class="input-text regular-input" type="radio" name="shipping_rates_cities_debug" id="shipping_rates_cities_debug"';
    $htmlGeneral .= 'value="yes">' . __('Sí');
}
$htmlGeneral .= '</fieldset>
       </td>
    </tr>';
$htmlGeneral .= '<tr>
        <td colspan="2" style="text-align:center;">' .
    wp_nonce_field( "shipping_rates_cities_wc_sr_general", "shipping_rates_cities_wc_sr_general" ) . '
            <button type="submit" class="button button-primary" name="shipping_rates_cities_wc_sr_general_save_changes_button">' . __('Guardar cambios') . '</button>
        </td>
    </tr>';
$htmlGeneral .= '</tbody>';

return $htmlGeneral;