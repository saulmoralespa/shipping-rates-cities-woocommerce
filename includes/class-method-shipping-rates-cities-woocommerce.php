<?php

class WC_Shipping_Method_Shipping_Rates_Cities_WC extends WC_Shipping_Method
{
    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->id = 'shipping_rates_cities_woocommerce';
        $this->instance_id = absint( $instance_id );
        $this->method_title = __( 'Shipping rates cities' );
        $this->method_description = __( 'Shipping rates cities, calculate shipping cost by cities' );

        $wc_main_settings = get_option('woocommerce_shipping_rates_cities_wc_sr_settings');
        $this->title = isset($wc_main_settings['title']) ? $wc_main_settings['title'] : '';
        $this->enabled = isset($wc_main_settings['enabled']) ? $wc_main_settings['enabled'] : false;
        $this->debug = isset($wc_main_settings['debug']) ? $wc_main_settings['debug'] : false;

        $this->supports = [
            'settings',
            'shipping-zones'
        ];

        $this->init();
    }

    public function is_available($package)
    {
        $db = self::get_data_shipping(2);
        return parent::is_available($package) &&
            !empty($db);
    }

    public function init()
    {
        $this->init_form_fields();
    }

    public function init_form_fields()
    {
        if(isset($_GET['page']) && $_GET['page'] === 'wc-settings')
            $this->form_fields = include( dirname( __FILE__ ) . '/admin/settings.php' );
    }

    public function generate_shipping_rates_cities_tab_box_html()
    {
        include( dirname( __FILE__ ) . '/admin/tabs.php' );
    }

    public function page_tabs($current = 'general')
    {
        $tabs = array(
            'general' => __("General"),
            'rates' => __("Cargar costos")
        );
        $html = '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            $class = ($tab == $current) ? 'nav-tab-active' : '';
            $style = ($tab == $current) ? 'border-bottom: 1px solid transparent !important;' : '';
            $html .= '<a style="text-decoration:none !important;' . $style . '" class="nav-tab ' . $class .
                '" href="?page=wc-settings&tab=shipping&section=shipping_rates_cities_woocommerce&subtab=' . $tab . '">' . $name . '</a>';
        }
        $html .= '</h2>';
        return $html;
    }

    public function name_tabs()
    {
        $tabs = [
            'general',
            'rates'
        ];

        return apply_filters( 'shipping_rates_cities_woocommerce_tabs', $tabs );
    }

    public function add_tab_per_file($tab)
    {
        $tab = file_exists(__DIR__ . "/admin/$tab.php") ? __DIR__ . "/admin/$tab.php" : $this->plugin_path_extension($tab);

        return $tab;
    }

    public function plugin_path_extension($tab)
    {
        $name_class = get_class($this);
        $name_class = strtolower($name_class);
        $name_class = str_replace('_', '-', $name_class);

        return trailingslashit(WP_PLUGIN_DIR) . trailingslashit($name_class) . "includes/admin/$tab.php";
    }

    public function calculate_shipping($package = [])
    {
        $country = $package['destination']['country'];
        $state_destination = $package['destination']['state'];
        $city_destination  = $package['destination']['city'];
        $city_destination = self::clean_string($city_destination);
        $city_destination = self::clean_city($city_destination);

        if($country !== 'CO' || empty($state_destination))
            return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $package, $this );

        $name_state_destination = self::name_destination($country, $state_destination);

        if (empty($name_state_destination))
            return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $package, $this );

        $address_destine = "$city_destination - $name_state_destination";

        $cities = include dirname(__FILE__) . '/cities.php';

        $destine = array_search($address_destine, $cities);

        if(!$destine)
            $destine = array_search($address_destine, self::clean_cities($cities));

        if(!$destine)
            return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $package, $this );

        $data_db = self::get_data_shipping($destine);

        if (empty($data_db))
            return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $package, $this );

        $rate = [
            'id' => $this->id,
            'label' => $this->title,
            'cost' => $data_db['coste_rate'],
            'package' => $package,
        ];

        return $this->add_rate( $rate );

    }

    public static function get_data_shipping($id_ciudad_destino)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'shipping_rates_cities_wc_sr';

        $result = [];

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name )
            return $result;

        $query = "SELECT * FROM $table_name WHERE id_ciudad_destino='$id_ciudad_destino'";
        $result = $wpdb->get_row( $query, ARRAY_A );

        return $result;
    }

    public static  function name_destination($country, $state_destination)
    {
        $countries_obj = new WC_Countries();
        $country_states_array = $countries_obj->get_states();

        $name_state_destination = '';

        if(!isset($country_states_array[$country][$state_destination]))
            return $name_state_destination;

        $name_state_destination = $country_states_array[$country][$state_destination];
        $name_state_destination = self::clean_string($name_state_destination);
        return self::short_name_location($name_state_destination);
    }

    public static function short_name_location($name_location)
    {
        if ( 'Valle del Cauca' === $name_location )
            $name_location =  'Valle';
        return $name_location;
    }

    public static function clean_string($string)
    {
        $not_permitted = array ("á","é","í","ó","ú","Á","É","Í",
            "Ó","Ú","ñ");
        $permitted = array ("a","e","i","o","u","A","E","I","O",
            "U","n");
        $text = str_replace($not_permitted, $permitted, $string);
        return $text;
    }

    public static function clean_city($city)
    {
        Return $city == 'Bogota D.C' ? 'Bogota' : $city;
    }

    public static function clean_cities($cities)
    {
        foreach ($cities as $key => $value){
            $cities[$key] = self::clean_string($value);
        }

        return $cities;
    }
}