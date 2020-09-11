<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
	exit();
}

class IM_Warehouse_Controller
{

	private $viewRender;

	public function __construct() {}

	/**
	 * [handleRequest method that handles an incoming request (save or edit) ]
	 */
	public function handleRequest()
	{
		global $wpdb;
		$values = array();

		// handle post and validate data
		if (isset($_REQUEST['IM_Warehouse_id']) && ! empty($_REQUEST['IM_Warehouse_id'])) {
			$id = $_REQUEST['IM_Warehouse_id'];
			$values['IM_Warehouse_id'] = $id;
			$repository_warehouse = new IM_Warehouse_Repository();
			$rows = $repository_warehouse->get_by(array(
				"id" => $id
			));
			$row = $rows[0];
			$values["IM_Warehouse_name"] = $row->name;
			$values["IM_Warehouse_address"] = $row->address;
			$values["IM_Warehouse_city"] = $row->city;
			$values["IM_Warehouse_postcode"] = $row->postcode;
			$values["IM_Warehouse_country"] = $row->country;
			$values["IM_Warehouse_vat"] = $row->vat;
			$values["IM_Warehouse_email"] = $row->email;
			$values["IM_Warehouse_slug"] = $row->slug;
			$values["IM_Warehouse_pickup"] = $row->pickup;
			$values['IM_Warehouse_priority'] = $row->priority;
			$values['IM_Warehouse_exclusive'] = $row->exclusive;
			$values['IM_Warehouse_prevent'] = $row->prevent;
			$repository_warehouse_country = new IM_Warehouse_Country_Repository();
			$values["IM_Warehouse_countries"] = $repository_warehouse_country->getByWarehouseJsonOfCountries($id);
			if (class_exists('WC_POS') || class_exists("WoocommercePointOfSale")){
				$repository_warehouse_outlet = new IM_Warehouse_Outlet_Repository();
				$values["IM_Warehouse_outlets"] = $repository_warehouse_outlet->getByWarehouseJsonOfOutlets($id);
			}

		}
		if (isset($_POST['IM_Warehouse_name']) && ! empty($_POST['IM_Warehouse_name'])) {
			$values['IM_Warehouse_name'] = $_POST['IM_Warehouse_name'];
		}
		if (isset($_POST['IM_Warehouse_address']) && ! empty($_POST['IM_Warehouse_address'])) {
			$values['IM_Warehouse_address'] = $_POST['IM_Warehouse_address'];
		}
		if (isset($_POST['IM_Warehouse_city']) && ! empty($_POST['IM_Warehouse_city'])) {
			$values['IM_Warehouse_city'] = $_POST['IM_Warehouse_city'];
		}
		if (isset($_POST['IM_Warehouse_postcode']) && ! empty($_POST['IM_Warehouse_postcode'])) {
			$values['IM_Warehouse_postcode'] = $_POST['IM_Warehouse_postcode'];
		}
		if (isset($_POST['IM_Warehouse_country']) && ! empty($_POST['IM_Warehouse_country'])) {
			$values['IM_Warehouse_country'] = $_POST['IM_Warehouse_country'];
		}
		if (isset($_POST['IM_Warehouse_vat']) && ! empty($_POST['IM_Warehouse_vat'])) {
			$values['IM_Warehouse_vat'] = $_POST['IM_Warehouse_vat'];
		}
		if (isset($_POST['IM_Warehouse_email']) && ! empty($_POST['IM_Warehouse_email'])) {
			$values['IM_Warehouse_email'] = $_POST['IM_Warehouse_email'];
		}
		if (isset($_POST['IM_Warehouse_slug']) && ! empty($_POST['IM_Warehouse_slug'])) {
			$values['IM_Warehouse_slug'] = $_POST['IM_Warehouse_slug'];
		}
		if (isset($_POST['IM_Warehouse_pickup'])) {
			$values['IM_Warehouse_pickup'] = $_POST['IM_Warehouse_pickup'];
		}
		if (isset($_POST['IM_Warehouse_exclusive'])) {
			$values['IM_Warehouse_exclusive'] = $_POST['IM_Warehouse_exclusive'];
		}
		if (isset($_POST['IM_Warehouse_prevent'])) {
			$values['IM_Warehouse_prevent'] = $_POST['IM_Warehouse_prevent'];
		}
		if (isset($_POST['IM_Warehouse_priority']) && ! empty($_POST['IM_Warehouse_priority'])) {
			$values['IM_Warehouse_priority'] = $_POST['IM_Warehouse_priority'];
		}
		if (isset($_POST['IM_Warehouse_countries']) && ! empty($_POST['IM_Warehouse_countries'])) {
			$values['IM_Warehouse_countries'] = $_POST['IM_Warehouse_countries'];
		}

		if (isset($_POST['IM_Warehouse_outlets']) && ! empty($_POST['IM_Warehouse_outlets'])) {
			$values['IM_Warehouse_outlets'] = $_POST['IM_Warehouse_outlets'];
		}

			$values["error"] = __("Error while saving. Name and Slug are required fields!", "woocommerce-inventorymanager");
			$values["success"] = __("Saved with success.", "woocommerce-inventorymanager");

		if (isset($_POST["submit"])) {
			$warehouse = new IM_Warehouse($values);
			$warehouse->save();
			$values = (array) $warehouse;

			// if it is a new warehouse we have to create new rows to it.
			if (!isset($_REQUEST['IM_Warehouse_id'])) {
    			$warehouse_repository = new IM_Warehouse_Repository();
    			$all = $warehouse_repository->get_all();

    			$repository = new IM_Product_Warehouse_Repository();

    			$posts = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'product' OR post_type = 'product_variation'");

    			if (count($posts) > 0) {
    				foreach ($posts as $post) {
    					if (count($all) == 1) {
    						$repository->refresh_relations($post->ID, 1);
    					} else {
    						$repository->refresh_relations($post->ID);
    					}
    				}
    			}
			}
			wp_reset_postdata();
			return $warehouse;
		} else {
		  $this->renderView($values);
		}
	}

	/**
	 * This method checks if a warehouse has been saved.
	 */
	public function check_if_saved() {
	    if (isset($_POST["submit"]) && isset($_POST["hellodev-inventory-manager-add-warehouse"])) {
	        $warehouse = $this->handleRequest();
					if((isset($_POST["IM_Warehouse_slug"]) && empty($_POST["IM_Warehouse_slug"])) || (isset($_POST["IM_Warehouse_name"]) && empty($_POST["IM_Warehouse_name"]))){
						$location = menu_page_url("hellodev-inventory-manager-add-warehouse", 0) . "&IM_Warehouse_id=" . $warehouse->IM_Warehouse_id . "&saved=error";
					}
					else{
	        	$location = menu_page_url("hellodev-inventory-manager-add-warehouse", 0) . "&IM_Warehouse_id=" . $warehouse->IM_Warehouse_id . "&saved=success";
					}
	        wp_redirect($location, 301);
	        exit;
	    }
	}

	/**
	 * [renderView method renders the view for the warehouse add and edit controller]
	 * @param  [type] $values [values]
	 */
	public function renderView($values = null)
	{
		global $woocommerce, $wpdb;
	  $countries_obj = new \WC_Countries();

		if (class_exists('WC_POS') || class_exists("WoocommercePointOfSale")){
			$outlet_repository = new IM_Outlet_Repository();
			$outlets = $outlet_repository->get_all();

			$outlet_array = array();
			foreach ($outlets as $outlet){
				$outlet_array[$outlet->ID] = $outlet->name;
			}

			$values["outlets"] = $outlet_array;
		}
		$values["countries"] = $countries_obj->__get('countries');
		$this->viewRender = IM_View_Render::get_instance();
		$this->viewRender->render("add-warehouse", $values);
	}
}
