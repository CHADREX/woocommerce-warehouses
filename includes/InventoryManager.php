<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
	exit(); // Exit if accessed directly
}

/**
* Main Plugin class
*/
class InventoryManager
{

	// Singleton design pattern
	protected static $instance = NULL;

	// Method to return the singleton instance
	public static function get_instance()
	{
		if (null == self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct()
	{

		if(phpversion() < 5.4){

				// If so display warning message on admin notices
    			add_action( 'admin_notices', array($this, 'php_error_activation_notice'));

    }else{
			add_action('plugins_loaded', array(
				$this,
				'load_plugin_textdomain'
			));
			add_action('init', array(
				$this,
				'apply_our_filters'
			));

			$this->define_constants();

			$this->install();

			add_action('plugins_loaded', array(
				$this,
				'init'
			));
		}

	}

	function php_error_activation_notice() {
      // Show error notice if REST API is not active
  		$php_notice = __("You need PHP 5.4+ in order to use WooCommerce Warehouses.", 'hellodev-hellosys');
  		echo "<div class='error'><p><strong>$php_notice</strong></p></div>";
  	}

	public function init()
	{
		$this->includes();
	}

	public function woocommerce_init() {
	    // SWAP WOOCOMMERCE PRODUCT FACTORY FOR OUR FACTORY
	    WC()->product_factory = new IM_Product_Factory();
	}

	/**
	* Handles the translations
	*/
	public function load_plugin_textdomain()
	{
		load_plugin_textdomain('woocommerce-inventorymanager', FALSE, dirname(plugin_basename(__FILE__)) . '/assets/translations');
	}

	// Constants necessary for the use of the plugin
	private function define_constants()
	{
		global $wpdb;
		$upload_dir = wp_upload_dir();
	}

	/**
	* Define constant if not already set
	*
	* @param string $name
	* @param string|bool $value
	*/
	private function define($name, $value)
	{
		if (! defined($name)) {
			define($name, $value);
		}
	}

	// Includes of our plugin
	public function includes()
	{
		new JSAutoloader();
		new IM_Plugin_Settings_Controller();
		new IM_Menu();
		new IM_Product_Warehouse_Tab();
		new IM_Warehouse_Order();

		$warehouseController = new IM_Warehouse_Controller();
		add_action( 'admin_init', array($warehouseController, 'check_if_saved'), 1 );
	}

	public function install()
	{
		im_install();
	}

	public function apply_our_filters()
	{
		// to block stock reduce & restore
		add_filter('woocommerce_payment_complete_reduce_order_stock', function () {
			return false;
		});
		add_filter('woocommerce_can_reduce_order_stock', function () {
			return false;
		});
		add_filter('woocommerce_restore_order_stock_quantity', function () {
			return false;
		});
	}
}

function remove()
{
	im_remove();
}

register_uninstall_hook(IM_PLUGIN_FILE, 'remove');
