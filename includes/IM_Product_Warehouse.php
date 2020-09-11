<?php
namespace Hellodev\InventoryManager;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class IM_Product_Warehouse {
	public $id;
	public $product_id;
	public $warehouse_id;
	public $stock;
	public $priority;

	public function __construct($parameters = array()) {
		// auto-populate object..
		foreach($parameters as $key => $value) {
		  $this->$key = $value;
		}
	}

	public function getId() {
		return $this->id;
	}

	public function getStock() {
		return $this->stock;
	}

	public function getPriority() {
		return $this->priority;
	}

	/**
	 * [increaseStock increases stock]
	 * @param  [int] $change [amount to increase]
	 * @return [int]         [new stock]
	 */
	public function increaseStock($change) {
		$this->stock += $change;
		return $this->stock;
	}

	/**
	 * [decreaseStock decreases stock]
	 * @param  [int] $change [amount to decrease]
	 * @return [int]         [new stock]
	 */
	public function decreaseStock($change) {
		$this->stock -= $change;
		return $this->stock;
	}
}
