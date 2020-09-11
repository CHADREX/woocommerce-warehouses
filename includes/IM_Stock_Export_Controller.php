<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Stock_Export_Controller
{

    private $viewRender;

    public function __construct(){
	    add_action('admin_init', array(
            $this,
            'change_headers'
        ));
    }


    public function exportStockCSV()
    {
		header('Content-Type: application/csv');
	    header('Content-Disposition: attachment; filename="stock.csv";');

	    $delimiter = get_option("hd_warehouses_csv_export_delimiter");

        $warehouses_repository = new IM_Warehouse_Repository();
        $warehouses = $warehouses_repository->get_all();

        $args = array(
            'post_type' => array(
                'product',
                'product_variation'
            ),
            'posts_per_page' => -1,
            'fields' 		 => 'ids',
            'post_status'    => 'publish'
        );
        $products = new \WP_Query($args);

        /**
         * open raw memory as file, no need for temp files, be careful not to run out of memory thought
         */
        $f = tmpfile();

        $line = array(
            __("id", "woocommerce-inventorymanager"),
            __("Product Name", "woocommerce-inventorymanager"),
            __("Product SKU", "woocommerce-inventorymanager")
        );

        $warehouses_ids = array();

        foreach ($warehouses as $warehouse) {
            $line[] = sprintf(__("stock_%s", "woocommerce-inventorymanager"), $warehouse->slug);
            $warehouses_ids[] = $warehouse->id;
        }

        $options_raw = get_option("hd_warehouses_custom_meta_stock_export");
        // they're splitted by ;
        $options = array_filter(explode(";", $options_raw));

        if ($options != false && count($options) > 0) {
            foreach ($options as $value) {
                $line[$value] = $value;
            }
        }

        fputcsv($f, $line, $delimiter);

        /**
         * loop through all products
         */
        foreach ($products->posts as $value) {

            $hide = false;
            $product_warehouse_repository = new IM_Product_Warehouse_Repository();
            $product_warehouses = $product_warehouse_repository->getByProductID($value, 'warehouse_id ASC');

            $product_factory = new \WC_Product_Factory();
            $product = $product_factory->get_product($value);

            if(is_object($product)){
            if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
		            $product_id = $product->get_id();
		        }
		    else{
			    if(isset($product->post->ID) && $product->post->ID){
            		$product_id = $product->post->ID;
            	}
            }

            if(is_object($product) && isset($product_id)){
	            if($product instanceof \WC_Product_Variable){
	            }
	            else{
	              if($product instanceof \WC_Product_Variation){

		            if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			            $parent_id = $product->get_parent_id();
			            $product_parent = get_post($parent_id);
			            if($product_parent->post_status !== 'publish'){
		                  $hide = true;
		                }
			        }
			        else{
		                if($product->parent->post->post_status !== 'publish'){
		                  $hide = true;
		                }
	                }

	                $attributes = $product->get_variation_attributes();
	                $title = $product->get_title() . '-' . implode( ', ', $attributes );
	                $line = array(
	                    $value,
	                    $title,
	                    $product->get_sku()
	                );
	              }

	              else{
                  $title = $product->get_title();
	                $line = array(
	                    $value,
	                    $title,
	                    $product->get_sku()
	                );
	              }

	              $warehouses_added = 0;
	              $i = 0;

	              /**
	               * loop through all warehouses with this product
	               */
	              foreach ($product_warehouses as $product_warehouse) {
	                  /**
	                   * if is the same by the same order provided before add it
	                   */
	                  if ($warehouses_ids[$i] == $product_warehouse->warehouse_id) {
	                      $line[] = $product_warehouse->stock;
	                      $warehouses_added ++;
	                  }
	                  $i ++;
	              }

	              /**
	               * In case stock control is disabled, mark it *
	               */
	              if ($warehouses_added == 0) {
	                  foreach ($warehouses as $warehouse) {
	                      $line[] = __("Stock control disabled", "woocommerce-inventorymanager");
	                  }
	              }

	              $options_raw = get_option("hd_warehouses_custom_meta_stock_export");
	              // they're splitted by ;
	              $options = array_filter(explode(";", $options_raw));

	              if ($options != false && count($options) > 0) {
	                  foreach ($options as $value2) {
	                      $meta = get_post_meta($value, $value2, true);
	                      if (!empty($meta)) {
	                          $line[] = $meta;
	                      } else {
	                          $line[] = "";
	                      }
	                  }
	              }

	              /**
	               * default php csv handler *
	               */
	              if ($warehouses_added !== 0 && !$hide) {
	              	fputcsv($f, $line, $delimiter);
	              }
              }
            }
          }
        }


	    /**
	     * rewrind the "file" with the csv lines *
	     */
	    fseek($f, 0);

	    fpassthru($f);
	        die();
    }
}
