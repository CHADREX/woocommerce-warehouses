<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Warehouse_Repository extends IM_Database
{

    public function __construct()
    {
        $this->tableName = IM_PLUGIN_DATABASE_TABLE;
    }

    /**
     * Get a IM_Warehouse by a specific id.
     * @param int $id IM_Warehouse id
     * @return \Hellodev\InventoryManager\IM_Warehouse
     */
    public function getByID($id)
    {
        $pair = array();
        $pair["id"] = $id;
        $array = parent::get_by($pair, "=", "priority ASC");
		$warehouse = new IM_Warehouse(array(
			"IM_Warehouse_id" => $array[0]->id,
			"IM_Warehouse_name" => $array[0]->name,
			"IM_Warehouse_address" => $array[0]->address,
			"IM_Warehouse_postcode" => $array[0]->postcode,
			"IM_Warehouse_city" => $array[0]->city,
			"IM_Warehouse_country" => $array[0]->country,
      "IM_Warehouse_email" => $array[0]->email,
			"IM_Warehouse_vat" => $array[0]->vat,
			"IM_Warehouse_slug" => $array[0]->slug,
      "IM_Warehouse_pickup" => $array[0]->pickup,
      "IM_Warehouse_exclusive" => $array[0]->exclusive,
      "IM_Warehouse_prevent" => $array[0]->prevent,
			"IM_Warehouse_priority" => $array[0]->priority
		));

        return $warehouse;
    }

    public function getPickupWarehouses() {
  		$pair = array();
          $pair["pickup"] = 1;
          $array = parent::get_by($pair, "=");

  		return $array;
  	}

    public function getBySlug($slug) {
  		$pair = array();
          $pair["slug"] = $slug;
          $array = parent::get_by($pair, "=");

  		return $array;
  	}
}
