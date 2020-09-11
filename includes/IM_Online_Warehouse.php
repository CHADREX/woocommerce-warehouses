<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
  exit();
}

class IM_Online_Warehouse
{
  public $status;

  public $id;

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
    $this->status = get_option('hd_warehouses_online_warehouse_restriction');
    $this->id = get_option('hd_warehouses_online_warehouse');
  }

  /**
  * Function that checks the product stock status and fixes it if needed.
  * @param int $product_id
  * @return boolean result
  */
  public function checkOnlineWarehouseStatus($product_id) {
    if($this->status == 1) {
      $this->updateOnlineWarehouseStatus($product_id);
      return true;
    }
    return false;
  }

  /**
  * Function that updates a product status depending on the online warehouse stock status.
  * @param int $product_id
  * @return string product status
  */
  public function updateOnlineWarehouseStatus($product_id) {
    $repository_product_warehouse = new IM_Product_Warehouse_Repository();

    $product_warehouse = $repository_product_warehouse->getByProductWarehouseID($product_id, $this->id);
	$product = im_get_product($product_id);
    // if in this warehouse the stock is 0 we mark it as out of stock.
    // also we must check if the product is managing stock issue #81
    if($product_warehouse->stock <= 0 && $product->managing_stock()) {
      update_post_meta($product_id, "_stock_status", "outofstock");
      return "outofstock";
    }
    return "instock";
  }
}
