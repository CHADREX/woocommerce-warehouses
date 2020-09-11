<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Warehouse extends IM_Database
{

    public $IM_Warehouse_id;

    public $IM_Warehouse_name;

    public $IM_Warehouse_address;

    public $IM_Warehouse_postcode;

    public $IM_Warehouse_city;

    public $IM_Warehouse_country;

    public $IM_Warehouse_vat;

	public $IM_Warehouse_email;

    public $IM_Warehouse_slug;

    public $IM_Warehouse_pickup;

    public $IM_Warehouse_exclusive;

    public $IM_Warehouse_prevent;

    public $IM_Warehouse_priority;

	public $IM_Warehouse_countries;

  public $IM_Warehouse_outlets;

    public function __construct($parameters = array())
    {
        $this->tableName = IM_PLUGIN_DATABASE_TABLE;
        // auto-populate object..
        foreach ($parameters as $key => $value) {
            $this->$key = $value;
        }
		if(!empty($this->IM_Warehouse_countries)) {
			if(!is_array($this->IM_Warehouse_countries)) {
				$countries = json_decode(stripslashes($this->IM_Warehouse_countries));
				$this->IM_Warehouse_countries = array();
				foreach($countries as $country) {
					$this->IM_Warehouse_countries[] = $country->countryCode;
				}
			}
		}
		else {
			$this->IM_Warehouse_countries = array();
		}

    if(!empty($this->IM_Warehouse_outlets)) {
			if(!is_array($this->IM_Warehouse_outlets)) {
				$outlets = json_decode(stripslashes($this->IM_Warehouse_outlets));
				$this->IM_Warehouse_outlets = array();
				foreach($outlets as $outlet) {
					$this->IM_Warehouse_outlets[] = $outlet->countryCode;
				}
			}
		}
		else {
			$this->IM_Warehouse_outlets = array();
		}

    }

	/**
	 * [save - saves the current warehouse object]
	 */
    public function save()
    {
        $warehouse_array = array();
        $warehouse_array['name'] = $this->IM_Warehouse_name;
        $warehouse_array['address'] = $this->IM_Warehouse_address;
        $warehouse_array['postcode'] = $this->IM_Warehouse_postcode;
        $warehouse_array['city'] = $this->IM_Warehouse_city;
        $warehouse_array['country'] = $this->IM_Warehouse_country;
        $warehouse_array['vat'] = $this->IM_Warehouse_vat;
        $warehouse_array['email'] = $this->IM_Warehouse_email;
        $warehouse_array['slug'] = $this->IM_Warehouse_slug;
        $warehouse_array['pickup'] = $this->IM_Warehouse_pickup;
        $warehouse_array['exclusive'] = $this->IM_Warehouse_exclusive;
        $warehouse_array['prevent'] = $this->IM_Warehouse_prevent;
        $warehouse_array['priority'] = $this->IM_Warehouse_priority;
        $warehouse_array['time'] = date('Y-m-d H:i:s');

        if(empty($this->IM_Warehouse_countries)){
          $warehouse_array['exclusive'] = 0;
          $warehouse_array['prevent'] = 0;
        }

		$new = false;

        if ($this->IM_Warehouse_id == NULL) {
            // lets make a new record
            // returns the id
            $this->IM_Warehouse_id = parent::insert($warehouse_array);
			$new = true;
        } else {
            // update the existing record
            parent::update($warehouse_array, array(
                "id" => $this->IM_Warehouse_id
            ));
        }

		// Issue #59
		$warehouse_country_repository = new IM_Warehouse_Country_Repository();

		foreach($this->IM_Warehouse_countries as $country) {
			$warehouse_country_array = array();
			$warehouse_country_array["warehouse_id"] = $this->IM_Warehouse_id;
			$warehouse_country_array["country"] = $country;
			$warehouse_country_repository->insert($warehouse_country_array);
		}
		// grab all the countries of a specific warehouse
		$allCountries = $warehouse_country_repository
							->getByWarehouseArrayOfCountryCodes($this->IM_Warehouse_id);

		// find which elements do not belong now to this scenario
		$difference = array_diff($allCountries, $this->IM_Warehouse_countries);

		// now that we have a list filtered lets loop it through and delete
		foreach($difference as $result) {
			$warehouse_country_repository->deleteByCountry($result);
		}

    if (class_exists('WC_POS') || class_exists("WoocommercePointOfSale")){

      $warehouse_outlet_repository = new IM_Warehouse_Outlet_Repository();

      // Insert outlets
      $allOutlets = $warehouse_outlet_repository
  							->getArrayOfOutletCodes($this->IM_Warehouse_id);

  		foreach($this->IM_Warehouse_outlets as $outlet) {
        if ( !in_array($outlet, $allOutlets)){
  			   $warehouse_outlet_array = array();
  			   $warehouse_outlet_array["warehouse_id"] = $this->IM_Warehouse_id;
  			   $warehouse_outlet_array["outlet_id"] = $outlet;
  			   $warehouse_outlet_repository->insert($warehouse_outlet_array);
        }
  		}
  		// grab all the countries of a specific warehouse
  		$allWarehouseOutlets = $warehouse_outlet_repository
  							->getByWarehouseArrayOfOutletCodes($this->IM_Warehouse_id);

  		// find which elements do not belong now to this scenario
  		$difference = array_diff($allWarehouseOutlets, $this->IM_Warehouse_outlets);

  		// now that we have a list filtered lets loop it through and delete
  		foreach($difference as $result) {
  			$warehouse_outlet_repository->deleteByOutlet($result);
  		}
    }

  }
}
