<?php 
namespace Hellodev\InventoryManager;

class IM_Product_Simple extends \WC_Product_Simple {
    
    public function __construct($product) {
        parent::__construct($product);
        //carregar stock
        $this->get_stock_quantity();
    }
    
    /**
     * {@inheritDoc}
     * @see WC_Product::get_stock_quantity()
     */
    public function get_stock_quantity( $context = 'view' ) {
        //vamos ter em atenção o armazém online (se for o caso), senão total de todos os warehouses
        $product_warehouse_repository = new IM_Product_Warehouse_Repository();
        $status = get_option('hd_warehouses_online_warehouse_restriction');
        if($status == 1) {
            $warehouse_id = get_option('hd_warehouses_online_warehouse');
            $product_warehouses = $product_warehouse_repository->getByProductWarehouseID($this->id, $warehouse_id);
            return $product_warehouses->stock;
        }
        
        $stock = 0;
        $warehouses = $product_warehouse_repository->getByProductID($this->id);
        foreach($warehouses as $warehouse) {
            $stock += $warehouse->stock;
        }
        
        return $stock;
    }
    
}