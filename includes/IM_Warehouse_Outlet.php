<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Warehouse_Outlet
{
	public $outlet;

	public $outletCode;

	public function __construct($parameters = array())
    {
		// auto-populate object..
        foreach ($parameters as $key => $value) {
            $this->$key = $value;
        }
	}

}
