<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Menu
{
  public $im_warehouses_settings = "hellodev-inventory-manager-plugin-settings";
  public $general_settings_key = "main_settings";
  public $plugin_settings_tabs = array();

    public function __construct()
    {
        // Defines setting tabs
        $this->plugin_settings_tabs = apply_filters('hd_inventory_manager_filter_settings_tabs', array(
            "main_settings" => __("Main Settings", "hellodev-inventory-manager"),
            "online_warehouse_settings" => __("Online Warehouse", "hellodev-inventory-manager"),
            "stock_report_settings" => __("Stock Report", "hellodev-inventory-manager"),
            "email_settings" => __("Email Settings", "hellodev-inventory-manager"),
            "error_messages" => __("Error Messages", "hellodev-inventory-manager")
        ));
        add_action('admin_menu', array(
            $this,
            'add_my_custom_menu'
        ));
        add_action('admin_init', array(
            $this,
            'hd_register_mysettings'
        ));
        
        //add_action('init', array($this, 'hd_delete_orphaned_variations'));
    }
    
    public function hd_delete_orphaned_variations(){
	   global $wpdb; 
	   $results = $wpdb->get_results('DELETE o FROM ' . $wpdb->posts. ' o
LEFT OUTER JOIN ' . $wpdb->posts . '  r
ON o.post_parent = r.ID
WHERE r.id IS null AND o.post_type = "product_variation"');

    }

    public function hd_register_mysettings(){
        // must be in admin init to change the headers
        if (isset($_GET["page"]) && $_GET["page"] == "hellodev-inventory-manager-stock-report-csv") {
            $this->stock_report_csv();
        }
        
        // must be in admin init to change the headers
        if (isset($_GET["page"]) && $_GET["page"] == "hellodev-inventory-manager-stock-export-csv") {
            $this->stock_export_csv();
        }
    }

    public function add_my_custom_menu()
    {
        // add an item to the menu
        if (current_user_can("manage_options") || current_user_can("hellodev_im_stock_log") || current_user_can("hellodev_im_manage_warehouses")) {
            add_menu_page(__('WooCommerce Warehouses', "woocommerce-inventorymanager"), __('Warehouses', "woocommerce-inventorymanager"), 'read', 'hellodev-inventory-manager', array(
                $this,
                'inventory_manager'
            ), 'dashicons-store', '59');
        }

        if (current_user_can("manage_options") || current_user_can("hellodev_im_manage_warehouses")) {
            add_submenu_page('hellodev-inventory-manager', __('WooCommerce Warehouses - Add Warehouse', "woocommerce-inventorymanager"), __("Add Warehouse", "woocommerce-inventorymanager"), 'read', 'hellodev-inventory-manager-add-warehouse', array(
                $this,
                'add_warehouse'
            ));
        }

        if (current_user_can("manage_options") || current_user_can("hellodev_im_stock_log")) {
            add_submenu_page('hellodev-inventory-manager', __('WooCommerce Warehouses - Stock Log', "woocommerce-inventorymanager"), __("Stock Log", "woocommerce-inventorymanager"), 'read', 'hellodev-inventory-manager-stock-log', array(
                $this,
                'stock_log'
            ));
            add_submenu_page('hellodev-inventory-manager', __('WooCommerce Warehouses - Stock Report', "woocommerce-inventorymanager"), __("Stock Report", "woocommerce-inventorymanager"), 'read', 'hellodev-inventory-manager-stock-report', array(
                $this,
                'stock_report'
            ));
            add_submenu_page(null, __('WooCommerce Warehouses - Stock Report', "woocommerce-inventorymanager"), __("Stock Report", "woocommerce-inventorymanager"), 'read', 'hellodev-inventory-manager-stock-report-csv', array(
                $this,
                'stock_report_csv'
            ));

            add_submenu_page(null, __('WooCommerce Warehouses - Stock Expory', "woocommerce-inventorymanager"), __("Stock Export", "woocommerce-inventorymanager"), 'read', 'hellodev-inventory-manager-stock-export-csv', array(
                $this,
                'stock_export_csv'
            ));

            add_submenu_page('hellodev-inventory-manager', __('WooCommerce Warehouses - Stock Import', "woocommerce-inventorymanager"), __("Stock Import", "woocommerce-inventorymanager"), 'read', 'hellodev-inventory-manager-stock-import', array(
                $this,
                'stock_import'
            ));
        }

        do_action('hd_inventory_manager_add_menus');

        if (current_user_can("manage_options") || current_user_can("hellodev_im_plugin_settings")) {
            add_submenu_page('hellodev-inventory-manager', __('WooCommerce Warehouses - Plugin Settings', "woocommerce-inventorymanager"), __("Plugin Settings", "woocommerce-inventorymanager"), 'read', 'hellodev-inventory-manager-plugin-settings', array(
                $this,
                'plugin_settings'
            ));
        }

        if (current_user_can("manage_options") || current_user_can("hellodev_im_stock_log") || current_user_can("hellodev_im_manage_warehouses")) {
            add_submenu_page('hellodev-inventory-manager', __('WooCommerce Warehouses - Plugin Documentation', "woocommerce-inventorymanager"), __("Documentation", "woocommerce-inventorymanager"), 'read', 'hellodev-inventory-manager-documentation', array(
                $this,
                'documentation'
            ));
        }
    }

    public function inventory_manager()
    {
        $values = array();
        $this->viewRender = IM_View_Render::get_instance();
        $this->viewRender->render("list-warehouses", $values);
    }

    public function add_warehouse()
    {
        $warehouseController = new IM_Warehouse_Controller();
        $warehouseController->handleRequest();
    }

    public function stock_log()
    {
        new IM_Stock_Log_Controller();
    }

    public function stock_report()
    {
        $controller = new IM_Stock_Report_Controller();
        $controller->renderView();
    }

    public function stock_report_csv()
    {
        $controller = new IM_Stock_Report_Controller();
        $controller->exportStockReportCSV();        
    }

    public function stock_export_csv(){
	    $controller = new IM_Stock_Export_Controller();
        $controller->exportStockCSV();
    }

    public function stock_import(){
      $controller = new IM_Stock_Import_Controller();
      $controller->renderView();
    }

    // Add option tabs to settings page
    public function plugin_options_tabs(){
        global $wp_settings_fields;

        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : $this->general_settings_key;

        screen_icon();
        echo '<h2 class="nav-tab-wrapper">';

        foreach ($this->plugin_settings_tabs as $tab_key => $tab_caption) {
            if (isset($wp_settings_fields[$this->im_warehouses_settings][$tab_key]) && count($wp_settings_fields[$this->im_warehouses_settings][$tab_key]) > 0) {
                $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
                echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->im_warehouses_settings . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
            }
        }
        echo '</h2>';
    }

    public function plugin_settings()
    {
          $tab = isset($_GET['tab']) ? $_GET['tab'] : $this->general_settings_key;
          ?>
          <div class="wrap woocommerce">
  	         <div class="icon32 icon32-woocommerce-settings" id="icon-woocommerce">
  		       <br />
  	        </div>
  	         <h2><?php _e('Warehouses Settings', "woocommerce-inventorymanager"); ?></h2>
          	 <?php $this->plugin_options_tabs(); ?>
          	 <form method="post" id="mainform" action="options.php">
                  <?php settings_errors(); ?>
                  <?php settings_fields($tab); ?>

                  <table class="form-table">
                  	 <?php do_settings_fields($this->im_warehouses_settings, $tab); ?>
                  </table>

                  <?php submit_button(); ?>
          	</form>
          </div>
          <?php
        /*$this->viewRender = IM_View_Render::get_instance();
        $this->viewRender->render("plugin-settings", array());*/
    }

    public function documentation()
    {
        ?>
        <style type="text/css">
        #wpcontent, #wpbody, #wpbody-content, #wpbody-content .wrap {
        	min-height: 100%;
        	padding: 0 !important;
        	margin: 0 !important;
        }

        iframe {
        	position: relative;
        	z-index: 0;
        }
        </style>
        <script>
        	function resize(element) {
        		element.width=document.getElementById('wpwrap').offsetWidth - document.getElementById('adminmenuback').offsetWidth;
        		element.height=document.getElementById('wpwrap').offsetHeight;
        		element.style.paddingLeft=document.getElementById('adminmenuback').offsetWidth + 'px';
            }

        	window.onresize = function(event) {
        	    var element = document.getElementById('hd_doc_iframe');
        	    resize(element);
        	};
        </script>
        <div class="wrap">
        	<iframe id="hd_doc_iframe" src="<?php echo plugin_dir_url( IM_PLUGIN_FILE ) . "docs/index.html?token=3"; ?>"
        		onload="resize(this);"></iframe>
        </div>
        <?php
    }
}
