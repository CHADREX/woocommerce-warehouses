<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Product_Warehouse_Repository extends IM_Database
{

    public function __construct()
    {
        $this->tableName = IM_PLUGIN_DATABASE_TABLE_PRODUCT_WAREHOUSE;
    }

    public function refresh_relations($id, $keep_stock = 0)
    {
        $product_factory = new \WC_Product_Factory();
        $product = $product_factory->get_product($id);
        if(isset($product) && is_object($product) && $product->managing_stock()) {
            $repository = new IM_Warehouse_Repository();
            $warehouses = $repository->get_all();
            if (! empty($warehouses)) {
                foreach ($warehouses as $warehouse) {
                    $product_warehouses = $this->getByProductWarehouseID($id, $warehouse->id);
                    /* if it is an empty product object */
                    if ($product_warehouses->getId() == NULL) {
                        $values = array();
                        $values["warehouse_id"] = $warehouse->id;
                        $values["product_id"] = $id;
                        if ($keep_stock == 1) {
                            $values["stock"] = $product->stock;
                        } else {
                            $values["stock"] = 0;
                        }
                        // dummy in case if a plugin tries to check in the database
                        update_post_meta($id, "_warehouse_" . $warehouse->id, 0);
                        $values["priority"] = 0;
                        parent::insert($values);
                    }
                }
            }
        }
    }

    public function get_all($orderBy = NULL)
    {
        $array = parent::get_all($orderBy);
        $objects = array();
        $i = 0;
        foreach ($array as $row) {
            $objects[$i] = new IM_Product_Warehouse($row);
            $i ++;
        }
        return $objects;
    }

    public function get($id)
    {
        $pair = array();
        $pair["id"] = $id;
        $array = parent::get_by($pair);
        return new IM_Product_Warehouse($array[0]);
    }

    public function getByProductWarehouseID($product_id, $warehouse_id)
    {
        $pair = array();
        $pair["product_id"] = $product_id;
        $pair["warehouse_id"] = $warehouse_id;
        $array = parent::get_by($pair, "=");

        if (! empty($array)) {
            return new IM_Product_Warehouse($array[0]);
        } else {
            return new IM_Product_Warehouse();
        }
    }

    public function getByWarehouseID($warehouse_id, $order_by = 'priority ASC')
    {
        $pair = array();
        $pair["warehouse_id"] = $warehouse_id;
        $array = parent::get_by($pair, "=");
        return $array;
    }

    public function getByProductID($product_id, $order_by = 'priority ASC')
    {
        $pair = array();
        $pair["product_id"] = $product_id;
        $array = parent::get_by($pair, "=", $order_by);
        return $array;
    }

	/**
	 * [getByProductWarehouseByCountry returns product warehouses rows of a specific warehouse country]
	 * @param  [string] $country [woocommerce country code]
	 * @return [array]         	 [array of IM_Product_Warehouse]
	 */
	public function getByProductWarehouseByCountry($product_id, $country_required) {
		$filteredProductWarehouses = array();

		// repository of countries
		$warehouse_country_repository = new IM_Warehouse_Country_Repository();

		// get warehouses of this product id
		$warehouses = $this->getByProductID($product_id);

		// loop all the warehouses retrieved
		foreach($warehouses as $warehouse) {
			$countries = $warehouse_country_repository->getByWarehouse($warehouse->warehouse_id);
			foreach($countries as $country) {
				// if it has been found...
				if($country->country == $country_required) {
					$filteredProductWarehouses[]=$warehouse;
					break;
				}
			}
		}

		return $filteredProductWarehouses;
	}

    /**
     * Delete all product id rows in the database
     * @param int $product_id
     */
    public function deleteByProductID($product_id) {
        $pair = array();
        $pair["product_id"] = $product_id;
        parent::delete($pair);
    }

    public function deleteByWarehouseID($warehouse_id) {
        $pair = array();
        $pair["warehouse_id"] = $warehouse_id;
        parent::delete($pair);
    }

    /**
     * This function updates a Product_Warehouse row with a new stock value
     * @param int $product_id
     * @param int $warehouse_id
     * @param int $new_stock
     * @return mixed updated value
     */
    public function updateStock($product_id, $warehouse_id, $new_stock) {
        $new_stock_pair = array();
        $new_stock_pair["stock"] = $new_stock;
        $condition_pair = array();
        $condition_pair["product_id"] = $product_id;
        $condition_pair["warehouse_id"] = $warehouse_id;
        $updated = $this->update($new_stock_pair, $condition_pair);
        return $updated;
    }

    /**
     * This function updates a Product_Warehouse row with a new priority value
     * @param int $product_id
     * @param int $warehouse_id
     * @param int $priority
     */
    public function updatePriority($product_id, $warehouse_id, $priority) {
        $new_stock_pair = array();
        $new_stock_pair["priority"] = $priority;
        $condition_pair = array();
        $condition_pair["product_id"] = $product_id;
        $condition_pair["warehouse_id"] = $warehouse_id;
        $updated = $this->update($new_stock_pair, $condition_pair);
        return $updated;
    }

    /**
     * Returns the total stock of a specific product.
     * @param int $product_id product id
     * @return int stock
     */
    public function getTotalStock($product_id) {
        $warehouses = $this->getByProductID($product_id);
        $total = 0;
        foreach($warehouses as $warehouse) {
            $total+=$warehouse->stock;
        }
        return $total;
    }

	/**
	 * [updateRow update a specific product warehouse]
	 * @param  [IM_Product_Warehouse] $product_warehouse [product warehouse object]
	 * @return [type]                    [changed row]
	 */
	public function updateRow($product_warehouse) {
		$new_stock_pair = array();
		$new_stock_pair["stock"] = $product_warehouse->stock;
		$new_stock_pair["priority"] = $product_warehouse->priority;
		$condition_pair = array();
		$condition_pair["product_id"] = $product_warehouse->product_id;
		$condition_pair["warehouse_id"] = $product_warehouse->warehouse_id;
		return $this->update($new_stock_pair, $condition_pair);
	}
}
