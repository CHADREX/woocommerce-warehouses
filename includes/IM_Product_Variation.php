<?php 
namespace Hellodev\InventoryManager;

class IM_Product_Variation extends \WC_Product_Variation {
        
    public function __construct($variation, $args = array()) {
		parent::__construct($variation, $args);
        
        // refresh stocks
        $this->get_stock_quantity();
    }
    
    public function get_stock_quantity( $context = 'view' ) {
        // if this occurs it is an invalid product.
        if(empty($this->parent)) {
            return;
        }
        
        if(false === $this->managing_stock()) 
        {    
            return parent::get_stock_quantity();
        }
        
        //vamos ter em atenção o armazém online (se for o caso), senão total de todos os warehouses
        $product_warehouse_repository = new IM_Product_Warehouse_Repository();
        $status = get_option('hd_warehouses_online_warehouse_restriction');
        if($status == 1) {
            $warehouse_id = get_option('hd_warehouses_online_warehouse');
            $product_warehouses = $product_warehouse_repository->getByProductWarehouseID($this->variation_id, $warehouse_id);
            return $product_warehouses->stock;
        }
        
        $stock = 0;
        $warehouses = $product_warehouse_repository->getByProductID($this->variation_id);
        foreach($warehouses as $warehouse) {
            $stock += $warehouse->stock;
        }
        
        return $stock;
    }
    
    /**
     * Returns whether or not the product has enough stock for the order.
     *
     * @param mixed $quantity
     * @return bool
     */
    public function has_enough_stock( $quantity ) {
        if ( true === $this->managing_stock() ) {
            $result = parent::has_enough_stock( $quantity );
            return $result;
        } else {
            return $this->parent->has_enough_stock( $quantity );
        }
    }
}