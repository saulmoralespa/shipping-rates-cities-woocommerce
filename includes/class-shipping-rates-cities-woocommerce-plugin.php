<?php

class Shipping_Rates_Cities_Woocommerce_WC_Plugin
{
    /**
     * Filepath of main plugin file.
     *
     * @var string
     */
    public $file;
    /**
     * Plugin version.
     *
     * @var string
     */
    public $version;
    /**
     * Absolute plugin path.
     *
     * @var string
     */
    public $plugin_path;
    /**
     * Absolute plugin URL.
     *
     * @var string
     */
    public $plugin_url;
    /**
     * assets plugin.
     *
     * @var string
     */
    public $assets;
    /**
     * Absolute path to plugin includes dir.
     *
     * @var string
     */
    public $includes_path;
    /**
     * Absolute path to plugin lib dir
     *
     * @var string
     */
    public $lib_path;
    /**
     * @var bool
     */
    private $_bootstrapped = false;

    public function __construct($file, $version)
    {
        $this->file = $file;
        $this->version = $version;

        $this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
        $this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
        $this->assets = $this->plugin_url . trailingslashit('assets');
        $this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
        $this->lib_path = $this->plugin_path . trailingslashit( 'lib' );
    }

    public function run_shipping_rates_cities()
    {
        try{
            if ($this->_bootstrapped){
                throw new Exception( 'Shipping Rates Cities Woocommerce can only be called once');
            }
            $this->_run();
            $this->_bootstrapped = true;
        }catch (Exception $e){
            if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
                add_action('admin_notices', function() use($e) {
                    shipping_rates_cities_wc_sr_notices($e->getMessage());
                });
            }
        }
    }

    protected function _run()
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Reader\Xls'))
            require_once ($this->lib_path . 'vendor/autoload.php');
        require_once ($this->includes_path . 'class-method-shipping-rates-cities-woocommerce.php');
        //require_once ($this->includes_path . 'class-shipping-servientrega-wc.php');

        add_filter( 'plugin_action_links_' . plugin_basename( $this->file), array( $this, 'plugin_action_links' ) );
        add_filter( 'woocommerce_shipping_methods', array( $this, 'shipping_rates_cities_wc_sr_add_method') );
        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts_admin') );
        add_action( 'wp_ajax_shipping_rates_cities_wc_sr_db',array($this, 'shipping_rates_cities_wc_sr_db'));
    }

    public function plugin_action_links($links)
    {
        $plugin_links = array();
        $plugin_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipping_rates_cities_woocommerce') . '">' . 'Configuraciones' . '</a>';
        $plugin_links[] = '<a target="_blank" href="https://shop.saulmoralespa.com/shipping-servientrega-woocommerce/">' . 'Documentación' . '</a>';
        return array_merge( $plugin_links, $links );
    }

    public function shipping_rates_cities_wc_sr_add_method( $methods )
    {
        $methods['shipping_rates_cities_woocommerce'] = 'WC_Shipping_Method_Shipping_Rates_Cities_WC';
        return $methods;
    }

    public function log($message)
    {
        if (is_array($message) || is_object($message))
            $message = print_r($message, true);
        $logger = new WC_Logger();
        $logger->add('shipping-rates-cities-woocommerce', $message);
    }

    public static function create_table()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'shipping_rates_cities_wc_sr';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name )
            return;

        $sql = "CREATE TABLE $table_name (
		id_ciudad_destino INT NOT NULL,
		coste_rate VARCHAR(60),
		PRIMARY KEY  (id_ciudad_destino)
	) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public function enqueue_scripts_admin($hook)
    {
        if ($hook !== 'woocommerce_page_wc-settings') return;

        wp_enqueue_script( 'shipping_rates_cities_wc_sr', $this->assets. 'js/config.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( 'shipping_rates_cities_wc_sr_sweet_alert', $this->assets. 'js/sweetalert.js', array( 'jquery' ), $this->version, true );
    }

    public function shipping_rates_cities_wc_sr_db()
    {
        if ( ! isset( $_POST['shipping_rates_cities_wc_sr_excel'] )
            || ! wp_verify_nonce( $_POST['shipping_rates_cities_wc_sr_excel'], 'shipping_rates_cities_wc_sr_excel' )
        )
            return;

        $fileName = sanitize_text_field($_FILES["shipping_rates_cities_wc_sr_xsl"]["name"]);
        $fileTmpName = sanitize_text_field($_FILES["shipping_rates_cities_wc_sr_xsl"]["tmp_name"]);

        $supported_type = [
            'application/excel',
            'application/vnd.ms-excel',
            'application/x-excel',
            'application/x-msexcel'
        ];
        $arr_file_type = wp_check_filetype(basename($fileName));

        $uploaded_type = $arr_file_type['type'];

        if(!in_array($uploaded_type, $supported_type))
            wp_send_json(
                [
                    'status' => false,
                    'message' => 'Tipo de archivo no aceptado debe ser excel con extensión .xsl'
                ]
            );

        $dir = $this->pathUpload();
        $name = $this->changeName($fileName);

        $pathXLS = $dir . $name;

        $result = [
            'status' => true
        ];

        $wc_main_settings = get_option('woocommerce_shipping_rates_cities_wc_sr_settings');
        $wc_main_settings['shipping_rates_cities_wc_sr_db'] = true;

        if (!move_uploaded_file($fileTmpName, $pathXLS))
            wp_send_json(['status' => false]);


        try{
            $reader = new PhpOffice\PhpSpreadsheet\Reader\Xls();
            $spreadsheet = $reader->load($pathXLS);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $data = array_shift($rows);

            $keysColumns = $this->columns($data);

            if (empty($keysColumns))
                wp_send_json(
                    [
                        'status' => false,
                        'message' => 'El excel debe tener las columnas ID_CIUDAD_DESTINO, COSTE_RATE'
                    ]
                );

            global $wpdb;
            $table_name = $wpdb->prefix . 'shipping_rates_cities_wc_sr';

            if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name ){
                $sql = "DELETE FROM $table_name";
                $wpdb->query($sql);
            }

            self::create_table();

            foreach ($rows as $column){

                $wpdb->insert(
                    $table_name,
                    [
                        'id_ciudad_destino' => (int)$column[$keysColumns[0]],
                        'coste_rate' => $this->check_value($column[$keysColumns[1]])
                    ]
                );
            }
        }catch (\Exception $exception){
        $result = [
            'status' => false,
            'message' => $exception->getMessage()
        ];
        $wc_main_settings['shipping_rates_cities_wc_sr_db'] = '';
    }

        update_option('woocommerce_shipping_rates_cities_wc_sr_settings', $wc_main_settings);
        wp_send_json($result);

    }

    public function pathUpload()
    {
        $upload_dir = wp_upload_dir();
        return trailingslashit($upload_dir['basedir']);
    }

    public function changeName($file)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $name = "rate-shipping.$extension";

        return $name;
    }

    public function columns($data)
    {
        $columns = [];

        if (!$id_ciudad_destino = array_keys($data, 'ID_CIUDAD_DESTINO'))
            return [];
        $columns[] = $id_ciudad_destino[0];

        if (!$coste_rate = array_keys($data, 'COST_RATE'))
            return [];
        $columns[] = $coste_rate[0];

        return $columns;

    }

    public function check_value($value)
    {
        $cost = 0;
        if (empty($value) || (!empty($value) && !is_numeric($value)))
            return $cost;

        return $value;
    }
}
