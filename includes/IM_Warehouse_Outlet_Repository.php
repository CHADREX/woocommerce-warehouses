<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Warehouse_Outlet_Repository extends IM_Database
{

    public function __construct()
    {
        $this->tableName = IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_OUTLET;
    }

	/**
	 * [getByWarehouse function to return countries associated with a specific warehouse]
	 * @param  [int] $warehouse_id [warehouse id]
	 * @return [array]               [countries]
	 */
	public function getByWarehouse($warehouse_id) {
		$pair = array();
        $pair["warehouse_id"] = $warehouse_id;
        $array = parent::get_by($pair, "=");

		return $array;
	}

	/**
	 * [getByWarehouseArrayOfCountryCodes gets a array of country codes of a specific warehouse]
	 * @param  [int] $warehouse_id [warehouse id]
	 * @return [array]               [countries]
	 */
	public function getByWarehouseArrayOfOutletCodes($warehouse_id) {
		$results = $this->getByWarehouse($warehouse_id);
		$allOutlets = array();
		// pass this into an array..
		foreach($results as $key => $result) {
			$allOutlets[] = $result->outlet_id;
		}
		return $allOutlets;
	}

  /**
	 * [getByWarehouseArrayOfCountryCodes gets a array of country codes of a specific warehouse]
	 * @param  [int] $warehouse_id [warehouse id]
	 * @return [array]               [countries]
	 */
	public function getArrayOfOutletCodes($warehouse_id) {
		$results = $this->get_all();
		$allOutlets = array();
		// pass this into an array..
		foreach($results as $key => $result) {
			$allOutlets[] = $result->outlet_id;
		}
		return $allOutlets;
	}

	/**
	 * [getByWarehouseJsonOfCountries retrieves a Json Object with the countries of a specific warehouse]
	 * @param  [int] $warehouse_id [warehouse id]
	 * @return [json]               [countries]
	 */
	public function getByWarehouseJsonOfOutlets($warehouse_id) {
		global $woocommerce, $wpdb;
    $outlet_repository = new IM_Outlet_Repository();
		$results = $outlet_repository->get_all();

		$outlets = array();
		foreach ($results as $outlet){
			$outlets[$outlet->ID] = $outlet->name;
		}

		$allOutletsOfWarehouse = $this->getByWarehouseArrayOfOutletCodes($warehouse_id);
		$arrayToSerialize = array();

		foreach($allOutletsOfWarehouse as $outletOfWarehouse) {
			$dto = array();
			$dto["country"] = html_entity_decode($outlets[$outletOfWarehouse]);
			$dto["countryCode"] = $outletOfWarehouse;
			$object = new IM_Warehouse_Outlet($dto);
			$arrayToSerialize[] = $object;
		}

		return json_encode($arrayToSerialize);
	}

	/**
	 * [checkIfWarehouseCountryAvailable check if a warehouse and country is not in use]
	 * @param  [int]      $warehouse_id   [warehouse id]
	 * @param  [string]   $country_code   [woocommerce country code]
	 * @return [bool]                     [boolean]
	 */
	/*public function checkIfWarehouseCountryAvailable($warehouse_id, $country_code) {
		$pair = array();
		$pair["warehouse_id"] = $warehouse_id;
		$pair["country"] = $country_code;
		$array = parent::get_by($pair, "=");

		if(empty($array)) {
			return true;
		}

		return false;
	}

	public function insert(array $data = array()) {
		if($this->checkIfWarehouseCountryAvailable($data["warehouse_id"], $data["country"])) {
			return parent::insert($data);
		}
		return false;
	}*/

	/**
	 * [deleteByWarehouseId deletes a row with a specific warehouse id]
	 * @param  [int] $warehouse_id [warehouse id]
	 */
	public function deleteByWarehouseId($warehouse_id)
	{
		$pair = array();
		$pair["warehouse_id"] = $warehouse_id;
		parent::delete($pair);
	}

	/**
	 * [deleteByCountry deletes rows with a specific country code]
	 * @param  [string] $country [wooocommerce country code]
	 */
	public function deleteByOutlet($outlet)
	{
		$pair = array();
		$pair["outlet_id"] = $outlet;
		parent::delete($pair);
	}
}
