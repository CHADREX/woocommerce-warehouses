<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Stock_Report_Controller
{

    private $viewRender;

    public function __construct()
    {
        add_action('admin_init', array(
            $this,
            'change_headers'
        ));
    }

    public function renderView($values = null)
    {
        $values = array();
        $this->viewRender = IM_View_Render::get_instance();
        $this->viewRender->render("stock-report", $values);
    }

    public function exportStockReportCSV()
    {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="stock_report.csv";');

        $delimiter = get_option("hd_warehouses_csv_export_delimiter");

        $warehouses_repository = new IM_Warehouse_Repository();
        $warehouses = $warehouses_repository->get_all();

        $args = array(
            'post_type' => array(
                'product',
                'product_variation'
            ),
            'fields' => 'ids',
            'posts_per_page' => - 1,
            'post_status'    => 'publish'
        );
        $products = new \WP_Query($args);

        /**
         * open raw memory as file, no need for temp files, be careful not to run out of memory thought
         */
        $f = tmpfile();

        $line = array(
            __("Product ID", "woocommerce-inventorymanager"),
            __("Product Name", "woocommerce-inventorymanager")
        );

        $warehouses_ids = array();

        foreach ($warehouses as $warehouse) {
            $line[] = sprintf(__("Warehouse %s stock", "woocommerce-inventorymanager"), $warehouse->name);
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

            $product_warehouse_repository = new IM_Product_Warehouse_Repository();
            $product_warehouses = $product_warehouse_repository->getByProductID($value, 'warehouse_id ASC');

            $product_factory = new \WC_Product_Factory();
            $product = $product_factory->get_product($value);

            if(is_object($product)){
            if($product instanceof \WC_Product_Variation){
  	          if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
  		            $product_id = $product->get_id();
  		        }
		          else{
              	$product_id = $product->post->ID;
              }
              $attributes = $product->get_variation_attributes();
              $title = $product->get_title() . '-' . implode( ', ', $attributes );
              $line = array(
                  $value,
                  $title
              );
            }

            else{

              $title = $product->get_title();

              $line = array(
                  $value,
                  $title
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
            fputcsv($f, $line, $delimiter);
          }
        }
        /**
         * rewrind the "file" with the csv lines *
         */
        fseek($f, 0);
        /**
         * Send file to browser for download
         */
        fpassthru($f);
        die();
    }
}
