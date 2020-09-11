<?php
namespace Hellodev\InventoryManager;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class IM_Stock_Log_Repository extends IM_Database {

  public function __construct() {
    $this->tableName = IM_PLUGIN_DATABASE_TABLE_STOCK_LOG;
  }

  /**
   * This function adds a new row to the stock log.
   * @param int $product_id
   * @param int $warehouse_id
   * @param int $new_stock
   * @param string $reason
   */
  public function addStockLog($product_id, $warehouse_id, $new_stock, $reason) {
      $stock_log_content = array();
      $stock_log_content["product_id"] = $product_id;
      $stock_log_content["warehouse_id"] = $warehouse_id;
      $stock_log_content["stock"] = $new_stock;
      $stock_log_content["reason"] = $reason;
      $this->insert($stock_log_content);
  }
}
