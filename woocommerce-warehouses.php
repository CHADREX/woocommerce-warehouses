<?php
/*
 * Plugin Name: WooCommerce Warehouses
 * Plugin URI: http://codecanyon.net/item/woocommerce-warehouses/13087646
 * Description: Add the possiblity of having mutiple warehouses and stock control inside WooCommerce.
 * Version: 1.7
 * Author: hellodev.us
 * Author URI: http://www.hellodev.us
 * Text Domain: woocommerce-inventory-manager
 * Domain Path: /assets/translations
 */

if (! defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}

// use composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

global $wpdb;

define('IM_PLUGIN_FILE', __FILE__);
define('IM_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('IM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('IM_PLUGIN_DATABASE_TABLE', $wpdb->prefix . 'inventory_manager_warehouses');
define('IM_PLUGIN_DATABASE_TABLE_PRODUCT_WAREHOUSE', $wpdb->prefix . 'inventory_manager_product_warehouse');
define('IM_PLUGIN_DATABASE_TABLE_STOCK_LOG', $wpdb->prefix . 'inventory_manager_stock_log');
define('IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_COUNTRY', $wpdb->prefix . 'inventory_manager_warehouse_country');
define('IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_OUTLET', $wpdb->prefix . 'inventory_manager_warehouse_outlet');

new Hellodev\InventoryManager\InventoryManager();

/**
 * This function returns a IM_Product for a given WP_Post. This abstraction of WC_Product allows us
 * to handle warehouses stock.
 * @param WP_Post $the_product
 * @param array $args
 * @return \Hellodev\InventoryManager\IM_Product|boolean
 */
function im_get_product( $the_product = false, $args = array() ) {

    $im_product_factory = new Hellodev\InventoryManager\IM_Product_Factory();

    return $im_product_factory->get_product( $the_product, $args );
}
