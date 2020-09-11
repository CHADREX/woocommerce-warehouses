<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Warehouse_Country_Repository extends IM_Database
{

    public function __construct()
    {
        $this->tableName = IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_COUNTRY;
    }

	/**
	 * [getByCountry get a list of warehouses that accept a specific country]
	 * @param  [string] $country_code [WooCommerce Country Code]
	 * @return [array] [array of tuples]
	 */
	public function getByCountry($country_code) {
		$pair = array();
        $pair["country"] = $country_code;
        $array = parent::get_by($pair, "=");

		$warehouse_repository = new IM_Warehouse_Repository();

		$warehouses = array();

		foreach($array as $row) {
			$warehouse = $warehouse_repository->getByID($row->warehouse_id);

			$warehouses[] = $warehouse;
		}

		return $warehouses;
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
	public function getByWarehouseArrayOfCountryCodes($warehouse_id) {
		$results = $this->getByWarehouse($warehouse_id);
		$allCountries = array();
		// pass this into an array..
		foreach($results as $key => $result) {
			$allCountries[] = $result->country;
		}
		return $allCountries;
	}

	/**
	 * [getByWarehouseJsonOfCountries retrieves a Json Object with the countries of a specific warehouse]
	 * @param  [int] $warehouse_id [warehouse id]
	 * @return [json]               [countries]
	 */
	public function getByWarehouseJsonOfCountries($warehouse_id) {
		global $woocommerce;
		$countries_obj = new \WC_Countries();
		$countries = $countries_obj->__get('countries');

		$allCountriesOfWarehouse = $this->getByWarehouseArrayOfCountryCodes($warehouse_id);
		$arrayToSerialize = array();

		foreach($allCountriesOfWarehouse as $countryOfWarehouse) {
			$dto = array();
			$dto["country"] = html_entity_decode($countries[$countryOfWarehouse]);
			$dto["countryCode"] = $countryOfWarehouse;
			$object = new IM_Warehouse_Country($dto);
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
	public function checkIfWarehouseCountryAvailable($warehouse_id, $country_code) {
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
	}

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
	public function deleteByCountry($country)
	{
		$pair = array();
		$pair["country"] = $country;
		parent::delete($pair);
	}
}
