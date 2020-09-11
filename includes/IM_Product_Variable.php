<?php
namespace Hellodev\InventoryManager;

class IM_Product_Variable extends \WC_Product_Variable {
    
    public function __construct($product) {
        parent::__construct($product);
        //carregar stock
        $this->get_total_stock();
    }
 
    /**
     * {@inheritDoc}
     * @see WC_Product_Variable::get_total_stock()
     */
    public function get_total_stock() {
        
        if ( empty( $this->total_stock ) ) {
            
            $this->total_stock = 0;
            
            $product_warehouse_repository = new IM_Product_Warehouse_Repository();
            $status = get_option('hd_warehouses_online_warehouse_restriction');
            $warehouse_id = get_option('hd_warehouses_online_warehouse');
                    
            if ( sizeof( $this->get_children() ) > 0 ) {
                
                foreach ( $this->get_children() as $child_id ) {
                    
                    if ( 'yes' === get_post_meta( $child_id, '_manage_stock', true ) ) {
                        
                        if($status == 1) {
                            $product_warehouses = $product_warehouse_repository->getByProductWarehouseID($child_id, $warehouse_id);
                            $this->total_stock += $product_warehouses->stock;
                        } else {
                            $stock = get_post_meta( $child_id, '_stock', true );
                            $this->total_stock += max( 0, wc_stock_amount( $stock ) );
                        }
                        
                    }
                    
                }
                
            }
            
        }
                
        return wc_stock_amount( $this->total_stock );
    }
    
    /**
     * {@inheritDoc}
     * @see WC_Product_Variable::get_child()
     */
    public function get_child( $child_id ) {
        return im_get_product( $child_id, array(
            'parent_id' => $this->id,
            'parent' 	=> $this
        ) );
    }
    
}