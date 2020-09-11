<?php

namespace Hellodev\InventoryManager;

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class JSAutoloader {
  /**
  * Path to the includes directory
  * @var string
  */
  private $include_path = '';

  /**
  * The Constructor
  */
  public function __construct() {
    $this->include_path = '/assets/js';
    add_action('admin_enqueue_scripts', array($this, 'autoload_backend'));
    add_action('wp_enqueue_scripts', array($this, 'autoload_frontend'));
  }

  /**
  * Include a javascript file into wordpress
  * @param  string $path
  * @return bool successful or not
  */
  private function load_file( $path ) {
    wp_register_script(basename($path), plugins_url( $path, IM_PLUGIN_FILE), array('jquery'),'20151110', true);
    wp_enqueue_script(basename($path));
  }

  /**
  * Auto-load javascript files
  */
  public function autoload_backend() {
    $plugins_path = untrailingslashit( plugin_dir_path( IM_PLUGIN_FILE ) );
    $path = $plugins_path . $this->include_path . '/im-js-order-backend.js';
    $file = str_replace($plugins_path, "", $path);
    $this->load_file($file);

	  $path = $plugins_path . $this->include_path . '/im-crud-warehouse-backend.js';
    $file = str_replace($plugins_path, "", $path);
    $this->load_file($file);

    $path = $plugins_path . $this->include_path . '/selectize.js';
    $file = str_replace($plugins_path, "", $path);
    $this->load_file($file);

    $path = $plugins_path . $this->include_path . '/im-admin.js';
    $file = str_replace($plugins_path, "", $path);
    $this->load_file($file);

    $path = $plugins_path . $this->include_path . '/jquery.plugin.js';
    $file = str_replace($plugins_path, "", $path);
    $this->load_file($file);

    $path = $plugins_path . $this->include_path . '/jquery.datepick.js';
      $file = str_replace($plugins_path, "", $path);
      $this->load_file($file);

    $path = $plugins_path . $this->include_path . '/im-js-filter-stocklog.js';
      $file = str_replace($plugins_path, "", $path);
      $this->load_file($file);

    $path = $plugins_path . $this->include_path . '/im-map.js';
      $file = str_replace($plugins_path, "", $path);
      $this->load_file($file);

      wp_enqueue_style( 'hellodev-warehouses-admin-css', plugins_url('assets/css/admin.css', IM_PLUGIN_FILE) );
      wp_enqueue_style( 'hellodev-warehouses-datepick-css', plugins_url('assets/css/jquery.datepick.css', IM_PLUGIN_FILE) );
      wp_enqueue_style( 'hellodev-warehouses-selectize-css', plugins_url('assets/css/selectize.css', IM_PLUGIN_FILE) );
  }

  /**
  * Auto-load javascript files
  */
  public function autoload_frontend() {
    $plugins_path = untrailingslashit( plugin_dir_path( IM_PLUGIN_FILE ) );
    $path = $plugins_path . $this->include_path . '/im-js-order-frontend.js';
    $file = str_replace($plugins_path, "", $path);
    $this->load_file($file);

    $path = $plugins_path . $this->include_path . '/im-map.js';
    $file = str_replace($plugins_path, "", $path);
    $this->load_file($file);

	$path = $plugins_path . $this->include_path . '/im-js-product-frontend.js';
    $file = str_replace($plugins_path, "", $path);
    $this->load_file($file);
    
  }
}
