<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Product_Warehouse_Tab
{
    public function __construct()
    {
        add_action("woocommerce_product_options_stock_fields", array(
            $this,
            'add_stock_fields'
        ));

        add_action("woocommerce_product_after_variable_attributes", array(
            $this,
            'add_stock_fields_variation'
        ), 10, 3);

        add_action('save_post', array(
            $this,
            'update_product_stock'
        ), 10, 1);

        add_action('before_delete_post', array(
            $this,
            'delete_product_stock'
        ), 10, 1);

        add_action('woocommerce_save_product_variation', array(
            $this,
            'update_product_variation_stock'
        ), 99, 1);

        add_filter('get_post_metadata', array(
            $this,
            'product_get_intercept_warehouse_meta'
        ), 10, 4);

        add_filter('update_post_metadata', array(
            $this,
            'product_save_intercept_warehouse_meta'
        ), 10, 5);

    }

    /**
     * Issue #63
     * This function intercepts the metadata of a product.
     * If it is a warehouse meta it will check
     * against the ID of a warehouse.
     *
     * @return filtered meta
     */
    public function product_get_intercept_warehouse_meta($metadata, $product_id, $meta_key, $single)
    {
        if (! isset($product_id) && ! isset($meta_key))
            return;

        $parts = explode("_", $meta_key);

        // Format: warehouse_$ID
        if (is_array($parts) && count($parts) == 3 && $parts[1] == "warehouse") {
            $repository_product_warehouse = new IM_Product_Warehouse_Repository();
            $product_object = $repository_product_warehouse->getByProductWarehouseID($product_id, $parts[2]);
            if (! empty($product_object->id)) {
                return $product_object->getStock();
            }
        }

        return $metadata;
    }

    /**
     * Issue #63
     * This function intercepts the metadata of a product.
     * If it is a warehouse meta it will check
     * against the ID of a warehouse.
     *
     * @return filtered meta
     */
    public function product_save_intercept_warehouse_meta($check, $product_id, $meta_key, $meta_value, $prev_value)
    {
        if (! isset($product_id) && ! isset($meta_key))
            return null;

        $post = get_post($product_id);

    		if($post->post_type == 'product' && ($post->post_status == 'auto-draft' || $post->post_status == 'draft')){
    			return null;
    		}

        $product = wc_get_product($product_id);

        // Issue #69 - Exception for product bundles, because it asks for non-existant meta fields.
        if ($product instanceof \WC_Product_Bundle)
            return null;
            // end issue #69

        if (isset($meta_key) && ! empty($meta_key)) {
            $parts = explode("_", $meta_key);
            // Format: warehouse_$ID
            if (is_array($parts) && count($parts) == 3 && $parts[1] == "warehouse") {
                $repository_product_warehouse = new IM_Product_Warehouse_Repository();
                $product_object = $repository_product_warehouse->getByProductWarehouseID($product_id, $parts[2]);

                if (! empty($product_object->id) && ! empty($product_object->warehouse_id)) {

                    if ($meta_value == "no_logging")
                        return null;

                    $repository_product_stock_log = new IM_Stock_Log_Repository();

                    // save
                    $repository_product_warehouse->updateStock($product_object->product_id, $product_object->warehouse_id, $meta_value);

                    // set the total
                    //$product = get_product($product_object->product_id);
                    //$product->set_stock( $repository_product_warehouse->getTotalStock($product_id) );

                    // log it
                    $repository_product_stock_log->addStockLog($product_object->product_id, $product_object->warehouse_id, $meta_value, "changed via post meta");
                }

                return false;
            }
        }
        return null;
    }

    /**
     * Refreshes relations for a specific product id.
     * @param int $id product id
     */
    public function refresh_database($id)
    {
        $repository = new IM_Product_Warehouse_Repository();
        /* this method adds non existent warehouses to the current product warehouse relation */
        $repository->refresh_relations($id);
    }

    /**
     * Renders warehouse stock fields for a specific product i
     * @param int $product_id product id
     */
    public function render_warehouse_stock_fields_product($product_id)
    {
        $repository = new IM_Warehouse_Repository();
        $warehouses = $repository->get_all();

        $product = wc_get_product($product_id);

        // variations don't show stock table
        if($product instanceof \WC_Product_Variable)
            return;

        $block = true;
        if (current_user_can("manage_options") || current_user_can("hellodev_im_product_stock")) {
          $block = false;
        }

        if (isset($product)) { ?>
<script type="text/javascript">
    jQuery(document).ready(function($) {
      $("._stock_field").remove();
      $(".input_stock_warehouse").change(function() {
        var sum = 0;
        $(".input_stock_warehouse").each(function(){
          sum += parseFloat(this.value);
        });
        $("#_stock").val(sum);
      });

      var block = "<?php echo $block?>";
      if(block){
        $(".input_stock_warehouse").prop( "readonly", true );
        $(".input_priority_warehouse").prop( "readonly", true );
      }

      $(".show_if_variation_manage_stock .form-row-first").remove();
      $(".wc_input_stock").remove();

      $('.input_stock_warehouse').on('input',function(e){
        var product_id = "<?php echo $product_id; ?>";
        var totalStock = 0;
        var count = 0;
        $(".input_stock_warehouse").each(function() {
            count++;
            if($(this).val() && Math.floor($(this).val()) == $(this).val() && $.isNumeric($(this).val())){
              totalStock += parseInt($(this).val());
          }
        });
        if(count > 0){
          $(".column-columnname #total_stock_" + product_id).text(totalStock + ' units');
        }
      });

    });
    </script>
<div class="wrap" style="padding-top: 15px; padding-bottom: 15px; width: 75%; margin: 0 auto;">
      <?php
        if($product instanceof \WC_Product_Bundle){
          _e("It is recommended not to use stock management on bundled products. <br>
          Stock will be managed individually on each product that constitutes the bundle.
          <br>Disable manage stock and set stock status to In Stock to do so.","hellodev-inventory-manager");
        }
        else{
            $product = wc_get_product($product_id);
            $get_stock = $product->get_stock_quantity();
            $stock_qty = 0;
            ?>
      <div class="stock_fields show_if_variation_manage_stock"
		style="display: block;">
        <?php
            $i = 0;
            $values = array();
            $warehouses_values = array();
            foreach ($warehouses as $warehouse) :
                $warehouse_id = $warehouse->id;
                $repository_product_warehouse = new IM_Product_Warehouse_Repository();
                $product_object = $repository_product_warehouse->getByProductWarehouseID($product_id, $warehouse_id);
                $warehouses_values[$i]["product_id"] = $product_id;
                $warehouses_values[$i]["warehouse"] = $warehouse;
                $warehouses_values[$i]["stock"] = $product_object->getStock();
                $warehouses_values[$i]["priority"] = $product_object->getPriority();
                $i ++;
                $stock_qty += $product_object->getStock();
            endforeach
            ;
            $values["warehouses"] = $warehouses_values;
            $values["total_stock"] = $stock_qty;
            $this->viewRender = IM_View_Render::get_instance();
            $this->viewRender->render("stock-per-warehouse", $values);

            ?>
      </div>
      <input type="hidden" name="_stock" id="_stock"
		value="<?php echo $stock_qty; ?>" />
<?php
        }?>
  </div><?php
      }
    }

    /**
     * Renders specific stock fields for a specific product variation.
     * @param int $product_id product id
     */
    public function render_warehouse_stock_fields_variation($product_id, $loop)
    {
        $repository = new IM_Warehouse_Repository();
        $warehouses = $repository->get_all();

        $block = true;
        if (current_user_can("manage_options") || current_user_can("hellodev_im_product_stock")) {
          $block = false;
        }

        ?>
<script type="text/javascript">
    jQuery(document).ready(function($) {
      //$("._stock_field").remove();
      $(".input_stock_warehouse").change(function() {
        var sum = 0;
        $(".input_stock_warehouse").each(function(){
          sum += parseFloat(this.value);
        });
        $("#_stock").val(sum);
      });

      $(".show_if_variation_manage_stock .form-row-first input[name^=variable_stock]").prop( "readonly", true );

      //$(".wc_input_stock").remove();

      var block = "<?php echo $block?>";
      if(block){
        $(".input_stock_warehouse").prop( "readonly", true );
        $(".input_priority_warehouse").prop( "readonly", true );
      };

      $('.input_stock_warehouse').on('input',function(e){
        var loop = "<?php echo $loop; ?>";
        var product_id = "<?php echo $product_id; ?>";
        var totalStock = 0;
        var count = 0;
        $(".input_stock_warehouse").each(function() {
          var name = ($(this).attr("name"));
          name = name.substring(name.indexOf("[") + 1);
          name = name.substring(0, name.indexOf("]"));
          if(name == product_id){
            count++;
            if($(this).val() && Math.floor($(this).val()) == $(this).val() && $.isNumeric($(this).val())){
              totalStock += parseInt($(this).val());
            }
          }
        });
        if(count > 0){
          $(".show_if_variation_manage_stock .form-row-first input[name^=variable_stock]").eq(loop).val(totalStock);
          $(".column-columnname #total_stock_" + product_id).text(totalStock + ' units');
        }
      });
    });
    </script>
<div class="wrap"
	style="padding-top: 15px; padding-bottom: 15px; width: 75%; margin: 0 auto;">
      <?php
        $product = wc_get_product($product_id);
        $get_stock = $product->get_stock_quantity();
        $stock_qty = 0;
        ?>
      <div class="stock_fields show_if_variation_manage_stock"
		style="display: block;">
        <?php
        $i = 0;
        $values = array();
        $warehouses_values = array();
        foreach ($warehouses as $warehouse) :
            $warehouse_id = $warehouse->id;
            $repository_product_warehouse = new IM_Product_Warehouse_Repository();
            $product_object = $repository_product_warehouse->getByProductWarehouseID($product_id, $warehouse_id);
            $warehouses_values[$i]["product_id"] = $product_id;
            $warehouses_values[$i]["warehouse"] = $warehouse;
            $warehouses_values[$i]["stock"] = $product_object->getStock();
            $warehouses_values[$i]["priority"] = $product_object->getPriority();
            $i ++;
            $stock_qty += $product_object->getStock();
        endforeach

        ;
        $values["warehouses"] = $warehouses_values;
        $values["total_stock"] = $stock_qty;
        $this->viewRender = IM_View_Render::get_instance();
        $this->viewRender->render("stock-per-warehouse", $values);
        ?>
      </div>
</div>
<?php
    }

    /**
     * Adds the stock fields to a product variation.
     * @param unknown $loop
     * @param unknown $variation_data
     * @param unknown $variation
     */
    public function add_stock_fields_variation($loop, $variation_data, $variation)
    {
        global $post;
        $this->refresh_database($post->ID);
        $this->render_warehouse_stock_fields_variation($variation->ID, $loop);
    }

    /**
     * Adds stock fields to the current loaded product.
     * (from global $post)
     */
    public function add_stock_fields(){
        global $post;
        $this->refresh_database($post->ID);
        $this->render_warehouse_stock_fields_product($post->ID);
    }

    /**
     * This method updates a specific product stock
     * @param WC_Product $product
     */
    public function update_wc_product_stock($product) {
        if(isset($product) && $product->managing_stock()) {

	        if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
		        if($product instanceof \WC_Product_Variation) {
	                $id = $product->get_id();
	            } else {
	                $id = $product->get_id();
	            }

		    }
		    else{

	            if($product instanceof \WC_Product_Variation) {
	                $id = $product->variation_id;
	            } else {
	                $id = $product->get_id();
	            }
            }

            if (isset($_POST["product"])) {
                update_post_meta($id, "_warehouse_enabled", 1);
            } else {
                delete_post_meta($id, "_warehouse_enabled");
            }
            $repository = new IM_Warehouse_Repository();
            $repository_product_stock_log = new IM_Stock_Log_Repository();
            $repository_product_warehouse = new IM_Product_Warehouse_Repository();
            // create rows for this product
            $repository_product_warehouse->refresh_relations($id);
            $warehouses = $repository->get_all();
            $total = 0;
            foreach ($warehouses as $warehouse) {
                $warehouse_id = $warehouse->id;
                $old_stock = $repository_product_warehouse->getByProductWarehouseID($id, $warehouse_id)->stock;
                $new_stock = ((isset($_POST["product"])) ? $_POST["product"] : null);
                // check if a stock value for the current combination exists..
                if (isset($new_stock[$id][$warehouse_id])) {
                    $new_stock = $new_stock[$id][$warehouse_id];
                      $repository_product_warehouse->updateStock($id, $warehouse_id, $new_stock);
                      $total += $new_stock;
                    if($new_stock !== $old_stock){
                      // Issue #19
                      $repository_product_stock_log->addStockLog($id, $warehouse_id, $new_stock, "direct change");
                      // end issue #19
                    }
                }
                $priority = ((isset($_POST["product-priority"])) ? $_POST["product-priority"] : null);
                // check if a priority value for the current combination exists..
                if (isset($priority[$id][$warehouse_id])) {
                    $priority = $priority[$id][$warehouse_id];
                    $repository_product_warehouse->updatePriority($id, $warehouse_id, $priority);
                }
                // dummy in case if a plugin tries to check in the database
                update_post_meta($id, "_warehouse_" . $warehouse->id, 0);
            }
              //update_post_meta($id, "_stock", $total);
              //$product->stock = $total;

            // Issue #66
            IM_Online_Warehouse::get_instance()->checkOnlineWarehouseStatus($id);
            // end issue #66
        }
    }

    /**
     * Updates the product stock
     */
    public function update_product_variation_stock($post_id){
        $product = wc_get_product($post_id);

        if (isset($product) && ($product instanceof \WC_Product_Variable || $product instanceof \WC_Product_Variation)) {

            $this->update_wc_product_stock($product);

            // For product variations we must check their childs
            $children_array = $product->get_children();
            if (sizeof($children_array) > 0) {
                foreach ($children_array as $children) {
                    $children_product = wc_get_product($children);
                    $this->update_wc_product_stock($children_product);
                }
            }
        }
    }

    public function update_product_stock($post_id){
        $product = wc_get_product($post_id);

        if (isset($product) && ($product instanceof \WC_Product_Simple )) {
            $this->update_wc_product_stock($product);
        }
    }

    /**
     * After a product is deleted we want to clean up its rows from the database
     * @param int $id product id
     */
    public function delete_product_stock($id) {
        $repository = new IM_Product_Warehouse_Repository();
        $repository->deleteByProductID($id);
    }
}
