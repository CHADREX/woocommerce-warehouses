<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Warehouse_Order
{

    public function __construct()
    {
        // disable reduce order stock after payment
        add_action('woocommerce_admin_order_item_headers', array(
            $this,
            'admin_order_item_headers'
        ));
        add_action('woocommerce_admin_order_item_values', array(
            $this,
            'admin_order_item_values'
        ), 10, 3);
        add_action('woocommerce_before_delete_order_item', array(
            $this,
            'restock_deleted_item_line'
        ), 10, 1);
        add_action('save_post', array(
            $this,
            'warehouse_to_warehouse'
        ), 10, 1);
        add_action('woocommerce_process_shop_order_meta', array(
            $this,
            'warehouse_to_warehouse'
        ), 10, 1);
        add_action('woocommerce_process_shop_order_meta', array(
            $this,
            'warehouse_to_warehouse'
        ), 51, 1);
        add_filter('woocommerce_hidden_order_itemmeta', array(
            $this,
            'custom_woocommerce_hidden_order_itemmeta'
        ), 10, 1);
        add_action('woocommerce_admin_order_data_after_shipping_address', array(
            $this,
            'destination_warehouse'
        ), 10, 1);
        add_action('woocommerce_process_shop_order_meta', array(
            $this,
            'add_order_status_hooks'
        ), 11, 1);
        add_action('woocommerce_order_status_changed', array(
            $this,
            'add_order_status_hooks'
        ), 11, 1);

		add_action('woocommerce_checkout_order_processed', array(
            $this,
            'add_order_status_hooks'
        ), 11, 1);

        // Partial refund
        add_action('woocommerce_order_refunded', array(
            $this,
            'process_partial_refund'
        ), 10, 2);

        // Full refund
        add_action( 'woocommerce_order_status_refunded', array(
            $this,'process_full_refund'
        ), 30);

        /*
         * Mysterious hook that is not documented in WooCommerce docs (22 September 2015).
         * Traced via debugging.
         */
        add_action('woocommerce_refund_deleted', array(
            $this,
            'cancel_refund'
        ), 10, 2);

        add_action('add_meta_boxes', array(
            $this,
            'select_warehouse_container'
        ));

		add_action( 'woocommerce_restore_order_stock', array(
			$this,
			'action_woocommerce_restore_order_stock'
		), 10, 1 );

		add_action( 'woocommerce_reduce_order_stock', array(
			$this,
			'action_woocommerce_reduce_order_stock'
		), 10, 1 );

    add_filter( 'woocommerce_package_rates', array(
      $this ,
      'hide_pickup_shipping_when_needed'
      ), 10, 2 );

  add_filter( 'woocommerce_checkout_fields' , array(
      $this,
      'add_pickup_location_field'
    ), 10, 2 );

    add_filter( 'woocommerce_checkout_process' , array(
      $this,
      'validate_pickup_location'
    ), 10, 2 );

    add_action( 'woocommerce_checkout_update_order_meta', array(
      $this,
      'save_pickup_location_name'
    ));

    add_filter('woocommerce_email_order_meta_keys', array(
      $this,
      'add_pickup_custom_meta'
    ));

    // POS Integration Filter
    add_filter('woocommerce_api_create_order', array(
      $this,
      'new_woocommerce_api_order'
    ), 10, 3);

    // Per country cart check
    add_action( 'woocommerce_check_cart_items', array(
      $this,
      'check_cart_items_warehouses'
    ), 10 );

    // Online warehouse cart check
    add_action( 'woocommerce_check_cart_items', array(
      $this,
      'check_cart_items_online_warehouse'
    ), 10 );

    // Manual warehouse cart check
    add_action( 'woocommerce_check_cart_items', array(
      $this,
      'check_cart_items_manual_warehouse'
    ), 10 );

    add_action( 'woocommerce_before_cart_totals', array(
      $this,
      'country_dropdown_warehouses'
    ), 10 );

    add_action( 'init', array(
      $this,
      'shipping_country_cookie'
    ), 10 );

    add_filter( 'default_checkout_country' , array(
      $this,
      'override_checkout_countries'
    ), 10 );

    add_action( 'wp_ajax_hd_warehouses_check_country', array(
      $this,
      'check_country_stock_ajax' ));

    add_action( 'wp_ajax_nopriv_hd_warehouses_check_country', array(
      $this,
      'check_country_stock_ajax' ));

    add_action( 'wp_ajax_hd_warehouses_change_country', array(
      $this,
      'change_country_usermeta_ajax' ));

    add_action( 'wp_ajax_hd_warehouses_change_product_var_warehouse', array(
      $this,
      'change_product_var_warehouse' ));

	add_action( 'wp_ajax_nopriv_hd_warehouses_change_product_var_warehouse', array(
      $this,
      'change_product_var_warehouse' ));

    add_action( 'wp_head', array(
      $this,
      'add_ajax_library' ));

    add_action( 'admin_head', array(
      $this,
      'add_gm_api_key' ));

    // ABE integration
    add_filter( 'abe_loopmedata_val', array(
      $this,
      'add_abe_integration_loopmedata' ));

    add_filter( 'abe_custom_fields_save', array(
      $this,
      'add_abe_integration_fields_save' ), 10, 3);

     // Add Base warehouse field to user profile
     add_action('show_user_profile', array($this, 'add_base_warehouse_field' ), 110);
	 add_action('edit_user_profile', array($this, 'add_base_warehouse_field' ), 110);
	 add_action("user_new_form", 	 array($this, "add_base_warehouse_field" ), 100);

	 add_action( 'personal_options_update',  array( $this, 'save_base_warehouse_field') );
	 add_action( 'edit_user_profile_update', array( $this, 'save_base_warehouse_field') );
	 add_action( 'user_register', 			 array( $this, 'save_base_warehouse_field'));

    // Add order status for stock transfers
    add_filter('wc_order_statuses', array($this, 'add_order_status'));
    add_action( 'init', array($this, 'register_stock_transferred_order_status'));

    add_action('wp_ajax_hd_csv_update_warehouses_stock', array($this, 'hd_csv_update_warehouses_stock_ajax'));

    // hooks and actions nedded for manual warehouse selection
		$option = get_option('hd_warehouses_frontend_stock_selection');
		$show_stock = get_option('hd_warehouses_stock_product_page');
		if(isset($option) && !empty($option) && $option == "manual"){
			$this->manual_warehouse_selection_hooks();
		}else if($show_stock){
			$this->show_stock_product_page();
    }
  }

  public function register_stock_transferred_order_status(){
    register_post_status( 'wc-stock-transferred', array(
        'label'                     => 'Stock Transferred',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Stock Transferred <span class="count">(%s)</span>', 'Stock Transferred <span class="count">(%s)</span>' )
    ) );
  }

  public function add_order_status($statuses){
    $new_statuses = array_slice($statuses, 0, 4, true) +
    array("wc-stock-transferred" => "Stock Transferred") +
    array_slice($statuses, 4, count($statuses)-4, true);
    return $new_statuses;
  }

  public function stockautoUTF($s){
      if (preg_match('#[\x80-\x{1FF}\x{2000}-\x{3FFF}]#u', $s))
          return $s;

      if (preg_match('#[\x7F-\x9F\xBC]#', $s))
          return iconv('WINDOWS-1250', 'UTF-8', $s);

      return iconv('ISO-8859-2', 'UTF-8', $s);
  }

  public function hd_csv_update_warehouses_stock_ajax(){
    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) ) {

    $stock_file = get_option('hd_warehouse_stock_file');

    if(!$stock_file){
      echo json_encode(array('result' => 'failure', 'message' => 'You need to upload a file first!'));
      die();
    }

    $target_dir = IM_PLUGIN_PATH . 'assets/csv/';
    $file_dir = $target_dir . $stock_file;
    $id_key = '';
    $ts_key = '';
    $warehouse_array = array();
    $row = 1;
    $updated = 0;

    $size = (int) $_REQUEST['size'];
    $offset = (int) $_REQUEST['offset'];
    $repository = new IM_Warehouse_Repository();
    $product_repository = new IM_Product_Warehouse_Repository();

    $delimiter = get_option("hd_warehouses_csv_export_delimiter");

    if (($handle = fopen($file_dir, "r")) !== FALSE) {

        while (($data = fgetcsv($handle, $size, $delimiter)) !== FALSE) {
          $num = count($data);

          if( $row == 1){
            foreach($data as $key=>$dat){
              switch($this->stockautoUTF($dat)){
                case 'id':
                  $id_key = $key;
                  break;
                case 'total_stock':
                  $ts_key = $key;
                  break;
                default:
                  if (strpos($dat, 'stock_') !== false) {
                    $slug = str_replace("stock_","",$dat);
                    $warehouses = $repository->getBySlug($slug);
                    if(isset($warehouses[0]->id)){
                      $warehouse_array[$key] = $warehouses[0]->id;
                    }
                  }
                  break;
              }
            }
            if($id_key === ''){
              echo json_encode(array('result' => 'failure', 'message' => 'id column not provided! Check if csv delimiter is well defined on Settings->Stock Report!'));
              die();
            }
            if(empty($warehouse_array)){
              echo json_encode(array('result' => 'failure', 'message' => 'no valid warehouse slugs were provided!'));
              die();
            }
          }

          if($row != 1){
            $product_id   = $this->stockautoUTF($data[$id_key]);
            $stocks       = array();

            if($product_id){
              foreach($warehouse_array as $k=>$v){
                $stock = $this->stockautoUTF($data[$k]);
                if($stock !== '' && is_numeric($stock)){
                  $product_repository->updateStock($product_id,$v,$stock);
                }
              }
              $total_stock = $product_repository->getTotalStock($product_id);
              update_post_meta($product_id, '_stock', $total_stock);
              if($total_stock > 0){
                update_post_meta($product_id, '_stock_status', 'instock');
              }
              else{
                update_post_meta($product_id, '_stock_status', 'outofstock');
              }
              $updated++;
            }
          }
          $row++;
    }
      fclose($handle);
    }
    else{
      echo json_encode(array('result' => 'failure', 'message' => 'Problem reading csv file.'));
      die();
    }

    echo json_encode(array('result' => 'success', 'products_updated' => $updated, 'offset' => $offset, 'finished' => true));
    }
      die();
   }

  public function show_stock_product_page(){
    // frontend dropdown
		add_action('woocommerce_before_add_to_cart_button', array(
			$this,
			'frontend_show_stock_dropdown'
		));
  }

  /**
   * [manual_warehouse_selection_hooks method that adds all the needed actions and filters for this feature]
   */
	public function manual_warehouse_selection_hooks() {
		// frontend dropdown
		add_action('woocommerce_before_add_to_cart_button', array(
			$this,
			'frontend_manual_product_dropdown'
		));

    // Substitute add to cart button
    add_action('woocommerce_after_shop_loop_item', array(
      $this,
      'replace_add_to_cart'
    ));

    // Remove add to cart button from shop
    add_action('init', array(
      $this,
      'remove_add_to_cart'
    ));

    if(isset($_POST["_warehouse_id"])){

  		// add meta
  		add_filter( 'woocommerce_add_cart_item_data', function ( $cartItemData, $productId, $variationId ) {
  			$warehouse_id = $_POST["_warehouse_id"];
  		    $cartItemData['_warehouse_id'] = $warehouse_id;
  		    return $cartItemData;
  		}, 10, 3 );

    }

		// restore if it is in present in the cart session
		add_filter( 'woocommerce_get_cart_item_from_session', function ( $cartItemData, $cartItemSessionData, $cartItemKey ) {
			if ( isset( $cartItemSessionData['_warehouse_id'] ) ) {
				$cartItemData['_warehouse_id'] = $cartItemSessionData['_warehouse_id'];
			}
			return $cartItemData;
		}, 10, 3 );

		// show warehouse id in the cart/checkout page if it is needed
		add_filter( 'woocommerce_get_item_data', function ( $data, $cartItem ) {
		    if ( isset( $cartItem['_warehouse_id'] ) ) {
			    $warehouse_repository = new IM_Warehouse_Repository();
			    $warehouse = $warehouse_repository->getByID($cartItem['_warehouse_id']);
			    if(isset($warehouse->IM_Warehouse_name)){
			        $data[] = array(
			            'name' => __('Warehouse', 'woocommerce-inventory-manager'),
			            'value' => $warehouse->IM_Warehouse_name
			        );
		        }
		    }
		    return $data;
		}, 10, 2 );

		// pass item meta to order meta when needed
		add_action( 'woocommerce_add_order_item_meta', function ( $itemId, $values, $key ) {
		    if ( isset( $values['_warehouse_id'] ) ) {
		        wc_add_order_item_meta( $itemId, '_warehouse_id', $values['_warehouse_id'] );
		    }
		}, 10, 3 );
  }

  public function remove_add_to_cart(){
	  $user = wp_get_current_user();
  	if(!get_option('hd_warehouses_frontend_manual_user_role') || in_array( get_option('hd_warehouses_frontend_manual_user_role'), (array) $user->roles )){
    	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	   }
  }

  public function replace_add_to_cart(){
	$user = wp_get_current_user();
  	if(!get_option('hd_warehouses_frontend_manual_user_role') || in_array( get_option('hd_warehouses_frontend_manual_user_role'), (array) $user->roles )){
	    global $product;
	    $link = $product->get_permalink();
	    echo do_shortcode('<a href="'.$link.'" class="button addtocartbutton">View Options</a>');
    }
  }

  public function frontend_manual_product_dropdown() {
		global $post;

		$user = wp_get_current_user();
  	if(!get_option('hd_warehouses_frontend_manual_user_role') || in_array( get_option('hd_warehouses_frontend_manual_user_role'), (array) $user->roles )){
			$product = im_get_product($post->ID);
			$values = array();
			$product_warehouse_repository = new IM_Product_Warehouse_Repository();
			$values['product_warehouses'] = $product_warehouse_repository->getByProductID($product->get_id());
			$warehouse_repository = new IM_Warehouse_Repository();

			// hide warehouses without stock
			/*if(get_option('hd_warehouses_frontend_stock_selection_manual_negative_stock') != 1) {
				foreach($values['product_warehouses'] as $key => $product_warehouse) {
					if($product_warehouse->stock <= 0) {
						unset($values['product_warehouses'][$key]);
					}
				}
			}*/

			// fix the keys
			$values['product_warehouses'] = array_values($values['product_warehouses']);

			foreach($values['product_warehouses'] as $key => $product_warehouse) {
				$warehouse = $warehouse_repository->getByID($product_warehouse->warehouse_id);
				$values['product_warehouses'][$key]->warehouse = $warehouse;
			}

			$this->viewRender = IM_View_Render::get_instance();
			$this->viewRender->render("select-product-dropdown", $values);
		}
	}

  public function frontend_show_stock_dropdown(){
    global $post;

		$user = wp_get_current_user();
	if(!get_option('hd_warehouses_frontend_manual_user_role') || in_array( get_option('hd_warehouses_frontend_manual_user_role'), (array) $user->roles )){
			$product = im_get_product($post->ID);
			$values = array();
			$product_warehouse_repository = new IM_Product_Warehouse_Repository();
			$values['product_warehouses'] = $product_warehouse_repository->getByProductID($product->get_id());
			$warehouse_repository = new IM_Warehouse_Repository();

			$values['product_warehouses'] = array_values($values['product_warehouses']);

			foreach($values['product_warehouses'] as $key => $product_warehouse) {
				$warehouse = $warehouse_repository->getByID($product_warehouse->warehouse_id);
				$values['product_warehouses'][$key]->warehouse = $warehouse;
			}

			$this->viewRender = IM_View_Render::get_instance();
			$this->viewRender->render("show-stock-product-dropdown", $values);
		}
  }



  public function add_abe_integration_loopmedata($val){
    $explode = explode("_", $val->meta_key);
    if(count($explode) == 3 && $explode[1] == "warehouse") {
        $val->meta_value = get_post_meta($val->ID, $val->meta_key);
    }

    return $val;
  }

  public function add_abe_integration_fields_save($ID, $i, $Row){
    if (strpos($i, '_warehouse_') !== false) {
      if(class_exists('Hellodev\InventoryManager\IM_Product_Warehouse_Repository')){
        $warehouse_id = str_replace("_warehouse_", "", $i);
        $repository_product_warehouse = new IM_Product_Warehouse_Repository();
        $repository_product_warehouse->updateStock($ID, $warehouse_id, $Row);

        $total_stock = $repository_product_warehouse->getTotalStock($ID);
        update_post_meta($ID, '_stock', $total_stock);

        if($total_stock > 0){
          update_post_meta($ID, 'stock_status', 'instock');
        }
        else{
          update_post_meta($ID, 'stock_status', 'outofstock');
        }
      }
    }
  }

  public function add_ajax_library(){
    $html = '<script type="text/javascript">';
    $html .= 'var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '";';
    if(get_option('hd_warehouses_show_googlemap_pickup')){
      $google_api_key = get_option('hd_warehouses_show_googlemap_key');
      if($google_api_key){
        $html .= 'var hd_gm_api_key = "' . $google_api_key . '";';
      }
    }
    $html .= '</script>';

    echo $html;
  }

  public function add_gm_api_key(){
    if(get_option('hd_warehouses_show_googlemap_pickup')){
      $google_api_key = get_option('hd_warehouses_show_googlemap_key');
      if($google_api_key){
        $html = '<script type="text/javascript">';
        $html .= 'var hd_gm_api_key = "' . $google_api_key . '";';
        $html .= '</script>';
        echo $html;
      }
    }
  }

  public function change_country_usermeta_ajax() {

    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) ) {

      $shipping_country = $_REQUEST['shipping_country'];
      $user_id = get_current_user_id();

      update_user_meta($user_id, 'shipping_country', $shipping_country);
      update_user_meta($user_id, 'billing_country', $shipping_country);

      echo true;
    }
      die();
  }

  public function change_product_var_warehouse(){
    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) ) {

      $variation_id = $_REQUEST['variation_id'];

  		$values = array();
  		$product_warehouse_repository = new IM_Product_Warehouse_Repository();
  		$values['product_warehouses'] = $product_warehouse_repository->getByProductID($variation_id);

  		$warehouse_repository = new IM_Warehouse_Repository();

  		// hide warehouses without stock
  		/*if(get_option('hd_warehouses_frontend_stock_selection_manual_negative_stock') != 1) {
  			foreach($values['product_warehouses'] as $key => $product_warehouse) {
  				if($product_warehouse->stock <= 0) {
  					unset($values['product_warehouses'][$key]);
  				}
  			}
  		}*/

  		// fix the keys
  		$values['product_warehouses'] = array_values($values['product_warehouses']);

  		foreach($values['product_warehouses'] as $key => $product_warehouse) {
  			$warehouse = $warehouse_repository->getByID($product_warehouse->warehouse_id);
  			$values['product_warehouses'][$key]->warehouse = $warehouse;
  		}

      echo json_encode($values);
    }
      die();
  }

  public function check_country_stock_ajax() {

    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) ) {

      $billing_country = $_REQUEST['billing_country'];
      $country = $billing_country;
      if(isset($_REQUEST['shipping_country'])){
        $shipping_country = $_REQUEST['shipping_country'];
        $country = $shipping_country;
      }

      if(get_option("hd_warehouses_frontend_stock_selection") == "per_country"){
        $warehouse_country = new IM_Warehouse_Country_Repository();
        $_pf = new \WC_Product_Factory();

        if(isset($country)){
          $warehouses = $warehouse_country->getByCountry($country);
          if($warehouses){
            global $woocommerce;
            $warehouse_id = $warehouses[0]->IM_Warehouse_id;

            $warehouse_repo = new IM_Warehouse_Repository();
            $warehouse_obj = $warehouse_repo->getByID($warehouse_id);
            if($warehouse_obj && $warehouse_obj->IM_Warehouse_prevent){

              $items = $woocommerce->cart->cart_contents;
              foreach($items as $item){
                $error_string = '';
                $product_id = $item['product_id'];
                if($item['variation_id'] > 0){
                  $product_id = $item['variation_id'];
                }
				if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
					$_product = $_pf->get_product($product_id);
				}
				else{
                	$_product = $_pf->get_product($product_id);
                }
                $title = $_product->get_title();

                if($_product instanceof \WC_Product_Variation){
                    $attributes = $_product->get_variation_attributes();
                    $title = $_product->get_title() . ' - ' . implode( ', ', $attributes );
                  }

                $quantity = $item['quantity'];
                $product_warehouse = new IM_Product_Warehouse_Repository();
                $ware = $product_warehouse->getByProductWarehouseID($product_id, $warehouse_id);
                $stock = $ware->stock;
                if($stock == 0 || $stock < $quantity){
                  echo $country;
                  die();
                }
            }
          }
        }
      }
    }
  }
      echo false;
      die();
 }

  function override_checkout_countries($value) {
    if(get_option("hd_warehouses_frontend_stock_selection") == "per_country"){
      if (isset($_COOKIE['woocommerce_warehouses'])) {
        return $_COOKIE['woocommerce_warehouses'];
      }
    }
    return $value;

  }

  function shipping_country_cookie(){
    if (!isset($_COOKIE['woocommerce_warehouses'])) {

      if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        	$user_ip = $_SERVER['HTTP_CLIENT_IP'];
    	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        	$user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    	} else {
        	$user_ip = $_SERVER['REMOTE_ADDR'];
    	}

      $ip_info = $this->ip_info($user_ip);

      if(isset($ip_info['country_code'])){
        $default_country = $ip_info['country_code'];
        setcookie('woocommerce_warehouses', $default_country, time()+86400*30, "/"); // One month cookie
      }
    }
  }

  function country_dropdown_warehouses() {

    if(get_option("hd_warehouses_frontend_stock_selection") == "per_country"){
      $user_id = get_current_user_id();
      $default_country = '';

        global $woocommerce;
        $countries_obj = new \WC_Countries();
        $countries = $countries_obj->__get('countries');

        if(isset($_COOKIE['woocommerce_warehouses']) && $default_country == ''){
          $default_country = $_COOKIE['woocommerce_warehouses'];
        }

        echo '<div id="warehouses_shipping_country_div">';

        woocommerce_form_field('warehouses_shipping_country', array(
        'type'       => 'select',
        'class'      => array( 'chzn-drop' ),
        'label'      => __('Select your shipping country', 'woocommerce-inventory-manager'),
        'options'    => $countries,
        'default'    => $default_country
        )
        );

        echo '</div>';
      }

  }

  function new_woocommerce_api_order( $order_id, $data, $instance ) {
    $order_type = get_post_meta($order_id, 'wc_pos_order_type', TRUE);
    if($order_type == 'POS'){
      $register = get_post_meta($order_id, 'wc_pos_id_register', TRUE);
      if($register > 0){
        $outlet_repository = new IM_Outlet_Repository();
    		$result = $outlet_repository->get_outlet_by_register($register);
    		if(isset($result->warehouse_id)){
          $warehouse_id = $result->warehouse_id;
          $this->add_order_status_hooks($order_id, $warehouse_id);
        }
        else{
          $this->add_order_status_hooks($order_id);
        }
      }
    }
  }

  // This method prevents users from purchasing products that are out of stock in their country
  public function check_cart_items_warehouses() {
    if(get_option("hd_warehouses_frontend_stock_selection") == "per_country"){
      $warehouse_country = new IM_Warehouse_Country_Repository();
      $user_id = get_current_user_id();
      $_pf = new \WC_Product_Factory();

      if(isset($_COOKIE['woocommerce_warehouses'])){
        $country = $_COOKIE['woocommerce_warehouses'];
      }
      if(isset($country)){
        $warehouses = $warehouse_country->getByCountry($country);
        $error_string = '';
        $errors = 0;
        if($warehouses){
          global $woocommerce;
          foreach($warehouses as $ware_){
            $warehouse_id = $ware_->IM_Warehouse_id;

            $warehouse_repo = new IM_Warehouse_Repository();
            $warehouse_obj = $warehouse_repo->getByID($warehouse_id);
            if($warehouse_obj && $warehouse_obj->IM_Warehouse_prevent){
              $items = $woocommerce->cart->cart_contents;
              foreach($items as $item){
                $product_id = $item['product_id'];
                if($item['variation_id'] > 0){
                  $product_id = $item['variation_id'];
                }

                if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
					$_product = $_pf->get_product($product_id);
				}
				else{
                	$_product = $_pf->get_product($product_id);
                }
                $title = $_product->get_title();

                if($_product instanceof \WC_Product_Variation){
                    $attributes = $_product->get_variation_attributes();
                    $title = $_product->get_title() . ' - ' . implode( ', ', $attributes );
                  }

                $quantity = $item['quantity'];
                $product_warehouse = new IM_Product_Warehouse_Repository();
                $ware = $product_warehouse->getByProductWarehouseID($product_id, $warehouse_id);
                $stock = $ware->stock;
                if($stock == 0){
                  if(!$error_string){
                    $message = get_option('hd_warehouses_country_nostock');
                    $error_string = sprintf(__( $message, 'woocommerce' ), $title);
                  }
                  $errors++;
                }
                else if($stock < $quantity){
                  $message = get_option('hd_warehouses_country_notenough_stock');
                  $error_string = sprintf(__( $message, 'woocommerce' ), $title, $stock );
                  $errors++;
                }
              }
            }
          }
          if(count($warehouses) == $errors && $error_string){
            $error = new \WP_Error();
            $error->add( 'out-of-stock', $error_string);
            wc_add_notice( $error->get_error_message(), 'error' );
          }
        }
      }
    }
  }

  // This method prevents users from purchasing products that are out of stock when using online warehouse
  public function check_cart_items_online_warehouse() {
    if(get_option("hd_warehouses_online_warehouse_restriction") && get_option('hd_warehouses_online_warehouse')){
      $warehouse_id = get_option('hd_warehouses_online_warehouse');
      $_pf = new \WC_Product_Factory();
      $warehouse_repo = new IM_Warehouse_Repository();
      $warehouse_obj = $warehouse_repo->getByID($warehouse_id);
      if($warehouse_obj){
        global $woocommerce;
        $items = $woocommerce->cart->cart_contents;
        foreach($items as $item){
          $error_string = '';
          $product_id = $item['product_id'];
          if($item['variation_id'] > 0){
            $product_id = $item['variation_id'];
          }

          if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			  $_product = $_pf->get_product($product_id);
		  }
		  else{
        	  $_product = $_pf->get_product($product_id);
          }
          $title = $_product->get_title();

          if($_product instanceof \WC_Product_Variation){
              $attributes = $_product->get_variation_attributes();
              $title = $_product->get_title() . ' - ' . implode( ', ', $attributes );
            }

          $quantity = $item['quantity'];
          $product_warehouse = new IM_Product_Warehouse_Repository();
          $ware = $product_warehouse->getByProductWarehouseID($product_id, $warehouse_id);
          $stock = $ware->stock;
          if($stock == 0){
            $message = get_option('hd_warehouses_online_nostock');
            $error_string = sprintf(__( $message, 'woocommerce' ), $title);
          }
          else if($stock < $quantity){
            $message = get_option('hd_warehouses_online_notenough_stock');
            $error_string = sprintf(__( $message, 'woocommerce' ), $title, $stock );
          }

          if($error_string !== ''){
            $error = new \WP_Error();
            $error->add( 'out-of-stock', $error_string);
            wc_add_notice( $error->get_error_message(), 'error' );
          }
        }
      }
    }
  }

  // This method prevents users from purchasing products that are out of stock when using manual warehouse selection
  public function check_cart_items_manual_warehouse() {
    if(get_option("hd_warehouses_frontend_stock_selection") == "manual"){
	    global $woocommerce;
		$items = $woocommerce->cart->cart_contents;
		$_pf = new \WC_Product_Factory();
        foreach($items as $item){
	        if(isset($item['_warehouse_id'])){
		        $error_string = '';

		        $warehouse_id = $item['_warehouse_id'];
		        $product_id = $item['product_id'];
		        if($item['variation_id'] > 0){
		          $product_id = $item['variation_id'];
		        }
		        if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
					$_product = $_pf->get_product($product_id);
				}
				else{
                	$_product = $_pf->get_product($product_id);
                }
		        $title = $_product->get_title();

		        if($_product instanceof \WC_Product_Variation){
	              $attributes = $_product->get_variation_attributes();
	              $title = $_product->get_title() . ' - ' . implode( ', ', $attributes );
	            }

	            $quantity = $item['quantity'];
		        $product_warehouse = new IM_Product_Warehouse_Repository();
		        $ware = $product_warehouse->getByProductWarehouseID($product_id, $warehouse_id);
		        $stock = $ware->stock;
		        if($stock == 0){
		          $message = get_option('hd_warehouses_manual_nostock');
		          $error_string = sprintf(__( $message, 'woocommerce' ), $title);
		        }
		        else if($stock < $quantity){
		          $message = get_option('hd_warehouses_manual_notenough_stock');
		          $error_string = sprintf(__( $message, 'woocommerce' ), $title, $stock );
		        }

		        if($error_string !== ''){
		          $error = new \WP_Error();
		          $error->add( 'out-of-stock', $error_string);
		          wc_add_notice( $error->get_error_message(), 'error' );
		        }
	        }
        }
        do_action('hd_inventory_manager_validate_manual_warehouse_cart');
	}
  }

  //This method hides local_pickup shipping method when there are 0 warehouses that act as a pickup location or the local pickup option is not being used
  public function hide_pickup_shipping_when_needed( $rates, $package ) {

    $repository = new IM_Warehouse_Repository();
    $warehouses = $repository->getPickupWarehouses();

    // If number of pickup warehouses = 0 or option is not checked unset local_pickup shipping method
    if((get_option('hd_warehouses_allow_pickup', true) != 1) || count($warehouses) == 0 ){
  		//unset( $rates['local_pickup'] );
	  }

	return $rates;
}

  // Our hooked in function - $fields is passed via the filter!
  function add_pickup_location_field( $fields ) {

    if(!is_admin()){

      $repository = new IM_Warehouse_Repository();
      $warehouses = $repository->getPickupWarehouses();

      if((get_option('hd_warehouses_allow_pickup', true) == 1) && count($warehouses) > 0 ){
        $formated_warehouses = array();
        $address_warehouses = array();
        $formated_warehouses[''] = __('None', 'woocommerce-inventorymanager');
        foreach($warehouses as $warehouse){
          $formated_warehouses[$warehouse->id] = $warehouse->name;
          $address_warehouses[$warehouse->id] = $warehouse->address;
        }

         $fields['billing']["billing_pickup_warehouse"] = array(
             'label'       => __('Pickup location', 'woocommerce-inventorymanager'),
             'placeholder' => _x('', 'placeholder', 'woocommerce-inventorymanager'),
             'clear'       => false,
             'type'        => 'select',
             'options'     => $formated_warehouses,
             'required'    => false,
         );

         echo"<script>
          var hd_address_warehouses = " .json_encode($address_warehouses).";
         </script>";

      }

    }

     return $fields;
  }

  // Validates pickup waerehouse!
  public function validate_pickup_location() {

    if ( isset($_POST['billing_pickup_warehouse']) &&  !$_POST['billing_pickup_warehouse'] ){
        wc_add_notice( __( 'You must choose a pickup location!', 'woocommerce-inventorymanager'), 'error' );
      }
    }

    function save_pickup_location_name( $order_id ) {
    if ( isset($_POST['billing_pickup_warehouse']) &&  !empty($_POST['billing_pickup_warehouse']) ) {
        $repository = new IM_Warehouse_Repository();
        $warehouse = $repository->getByID(sanitize_text_field($_POST['billing_pickup_warehouse']));
        update_post_meta( $order_id, '_pickup_location_name', $warehouse->IM_Warehouse_name );
      }
    }

    // Adds pickup location to metakeys
    public function add_pickup_custom_meta( $keys ) {
         $keys['Pickup location'] = '_pickup_location_name';
         return $keys;
    }

    /**
     * This method processes a partial refund, considering a partial refund a refund
     * done inline and not by the full state change.
     *
     * @param int $refund_id
     */
    public function process_partial_refund($order_id, $refund_id)
    {
        // create a order refund object from the id
        $order_refund = new \WC_Order_Refund($refund_id);
        // create a order object from the order id
        $order_object = new \WC_Order($order_refund->id);

        $reduced_stock = get_post_meta($order_id, '_reduced_stock_already', true);

        $restock = 'true';
        if(isset($_REQUEST['restock_refunded_items'])){
          $restock = $_REQUEST['restock_refunded_items'];
        }
        // Check if stock has been deducted before restocking
        if($reduced_stock && $restock == 'true'){

          $order_items = $order_object->get_items();

          if (count($order_items) > 0) {
              // loop the order items
              foreach ($order_items as $item) {

                  // get the warehouse id of this line.
                  if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
                  	$product_id = $item->get_product_id();
                  }
                  else{
	                $product_id = $item["item_meta"]["_product_id"][0];
                  }

                  if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
					  $quantity = $item->get_quantity();
					  $refunded_item_id = $item->get_meta('_refunded_item_id');
					  $warehouse_id = wc_get_order_item_meta($refunded_item_id, "_warehouse_id", true);
				  }
				  else{
					  $quantity = $item['item_meta']['_qty'][0];
					  $refunded_item_id = $item["item_meta"]["_refunded_item_id"][0];
					  $warehouse_id = wc_get_order_item_meta($refunded_item_id, "_warehouse_id", true);
					  $item['item_meta']['_stock_reduced'][0] = $item['item_meta']['_qty'][0];
				  }

                  $this->update_meta_order_stock_reduced($item["item_meta"]["_refunded_item_id"][0], $item, $warehouse_id, "wc-refunded", $order_object->id);
                  // Quick fix for issue #55
                  wc_update_order_item_meta($item["item_meta"]["_refunded_item_id"][0], '_stock_reduced', $quantity);

                  $repository_product_warehouse = new IM_Product_Warehouse_Repository();
                  $product_warehouse_destination = $repository_product_warehouse->getByProductWarehouseID($product_id, $warehouse_id);
                  $total_stock_destination = $product_warehouse_destination->getStock();

                  $reason = "order #" . $order_id . " - restock refunded item";
                  $this->add_stock_log($product_id, $warehouse_id, $total_stock_destination, $reason);
              }
          }
      }
    }

    /**
     * This method processes a full refund, considering a partial refund a refund
     * done inline and not by the full state change.
     *
     * @param int order_id
     */
    public function process_full_refund($order_id){

      // create a order object from the order id
      $order_object = new \WC_Order($order_id);

      $refunds = $order_object->get_refunds();
      $last_refund = end($refunds);

      $refund_id = $last_refund->id;
      $order_object_new = new \WC_Order($refund_id);

      $order_items = $order_object_new->get_items();

      if(!empty($refunds) && count($order_items) == 0){

        // Add order note
    		$note = sprintf( __( 'Refund #%s did not restock items, to do so: <br>On Order Items use Actions->Increase line item stock to restock items.', 'woocommerce-inventorymanager' ), $refund_id);
    		$order_object->add_order_note( $note );
      }
    }

    public function cancel_refund($refund_id, $order_id)
    {
        // create a order object from the order id
        $order_object = new \WC_Order($order_id);

        $order_items = $order_object->get_items();

        if (count($order_items) > 0) {
            // loop the order items
            foreach ($order_items as $key => $item) {
                // get the warehouse id of this line.
                $warehouse_id = wc_get_order_item_meta($key, "_warehouse_id", true);

                if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
                	wc_update_order_item_meta($item->get_id(), '_stock_reduced', 0);
                }
                $item['item_meta']['_stock_reduced'][0] = 0;
                $this->update_meta_order_stock_reduced($key, $item, $warehouse_id, "wc-completed", $order_object->id);
            }
        }
    }

    public function add_order_status_hooks($post_id, $pos_warehouse = 0)
    {

        $pickup_warehouse = get_post_meta($post_id, '_billing_pickup_warehouse', true);
        $reduced_stock = get_post_meta($post_id, '_reduced_stock_already', true);
        $chose_warehouse = get_post_meta($post_id, '_chose_warehouse_already', true);

        // This is used to select a warehouse
        if ($pos_warehouse > 0){
          $order_object = new \WC_Order($post_id);
          $order_items = $order_object->get_items();
          if (count($order_items) > 0) {
              // loop the order items
              foreach ($order_items as $key => $item) {
                  $this->add_warehouse_id_to_item($key, $pos_warehouse);
              }
          }
        }else if(!empty($pickup_warehouse)){
          $order_object = new \WC_Order($post_id);
          $order_items = $order_object->get_items();
          if (count($order_items) > 0) {
              // loop the order items
              foreach ($order_items as $key => $item) {
                  $this->add_warehouse_id_to_item($key, $pickup_warehouse);
              }
          }
        }

        /*
         * Considered statuses:
         * 'wc-pending' => string 'Pending Payment' (length=15)
         * 'wc-processing' => string 'Processing' (length=10)
         * 'wc-on-hold' => string 'On Hold' (length=7)
         * 'wc-completed' => string 'Completed' (length=9)
         * 'wc-cancelled' => string 'Cancelled' (length=9)
         * 'wc-refunded' => string 'Refunded' (length=8)
         * 'wc-failed' => string 'Failed' (length=6)
         */

        if(!$reduced_stock){

          // gets the current order status
          $current_status = get_post_status($post_id);

          $status = get_option('stock_reduction_state');

          // Get woocommerce order status
          $statuses = wc_get_order_statuses();

          $number_current = 0;
          foreach ($statuses as $key => $value) {
              if(strpos($key, $current_status) !== false){
                break;
              }
              ++$number_current;
          }

          $number_trigger = 0;
          foreach ($statuses as $key => $value) {
              if(strpos($key, $status) !== false){
                break;
              }
              ++$number_trigger;
          }

          // Interchange processing and on-hold status
          if($number_current == 2){
            $number_current = 1;
          }
          else if($number_current == 1){
            $number_current = 2;
          }

          if($number_trigger == 2){
            $number_trigger = 1;
          }
          else if($number_trigger == 1){
            $number_trigger = 2;
          }

          if(($number_current < 4 && $number_trigger <= $number_current) || ($number_trigger == $number_current)){
            $reduce_stock = true;
          }
          else if(isset($_POST["IM_Warehouse_destination"]) && ! empty($_POST["IM_Warehouse_destination"]) && $current_status == "wc-stock-transferred"){
            $reduce_stock = true;
          }
          else{
            $reduce_stock = false;
          }

          if($reduce_stock || !$chose_warehouse){
            $this->update_stocks_per_item($post_id, $reduce_stock);
            if($reduce_stock){
              update_post_meta($post_id, '_chose_warehouse_already', true);
            }
          }

          if(isset($_POST['warehouse'])){
            $warehouse = $_POST['warehouse'];
            $order_object = new \WC_Order($post_id);
            $order_items = $order_object->get_items();
            if (count($order_items) > 0) {
                // loop the order items
                foreach ($order_items as $key => $item) {
                  if(isset($warehouse[$key])){
                    $this->add_warehouse_id_to_item($key, $warehouse[$key], $chose_warehouse);
                  }
                }
            }
          }

        }
    }

	/**
	 * [admin_order_item_headers method that adds to the order a header with the warehouse option]
	 */
    public function admin_order_item_headers()
    {
        ?>
		<th class="warehouse"><?php _e('Warehouse', "woocommerce-inventorymanager"); ?></th>
		<?php
    }

	/**
	 * [destination_warehouse method that draws the destination warehouse dropdown]
	 */
    public function destination_warehouse()
    {
        global $post;
        ?>
<div class="form-field form-field-wide">
	<h4><?php _e('Warehouse destination'); ?>:</h4>
	<select id="IM_Warehouse_destination" name="IM_Warehouse_destination">
        <?php
        $repository = new IM_Warehouse_Repository();
        $warehouses = $repository->get_all();
        $warehouse_destination_id = get_post_meta($post->ID, "_warehouse_destination_id", true);
        ?>
        <option
			<?php if($warehouse_destination_id == null) echo "selected"; ?>
			value=""><?php _e('none'); ?></option>
        <?php
        foreach ($warehouses as $warehouse) :
            ?>
          <option value="<?php echo $warehouse->id; ?>"
			<?php if($warehouse_destination_id == $warehouse->id) echo "selected"; ?>><?php echo $warehouse->name; ?></option>
          <?php
        endforeach
        ;
        ?>
      </select>
</div>
<?php
    }

	/**
	 * [admin_order_item_values method that renders the warehouse select for each order item]
	 * @param  [WC_Product] $product [product object]
	 * @param  [WC_Order_Item_Meta] $item    [order item object]
	 * @param  [int] $item_id [order item id]
	 */
    public function admin_order_item_values($product, $item, $item_id)
    {
        if (isset($product) && is_a($product, 'WC_Product_Variation')) {
            $id = $product->variation_id;
        } else
            if (isset($product)) {
                $id = $product->get_id();
            }

        ?>
<td class="warehouse" style="width: 350px">
      <?php if (isset($product)) { ?>
        <div class="edit">
          <?php
            $repository = new IM_Warehouse_Repository();
            $warehouses = $repository->get_all();
            ?>
          <select name="warehouse[<?php echo absint($item_id); ?>]"
			style="width: 100%">
            <?php
            foreach ($warehouses as $warehouse) :
                $repository_product_warehouse = new IM_Product_Warehouse_Repository();
                $product_repository_row = $repository_product_warehouse->getByProductWarehouseID($id, $warehouse->id);
                ?>
              <option value="<?php echo $warehouse->id; ?>"
				<?php if(isset($item['warehouse_id']) && $item['warehouse_id'] == $warehouse->id) echo "selected"; ?>><?php echo $warehouse->name; ?> - Stock: <?php echo $product_repository_row->getStock(); ?></option>
                <?php
            endforeach
            ;
            ?>
            </select>
	</div>
          <?php } ?>
        </td>
<?php
    }

    /**
     * Updates the product warehouse with the specified stock.
     *
     * @param int $product_id
     * @param int $warehouse_id
     * @param int $stock
     */
    public function update_product_warehouse_stock($product_id, $warehouse_id, $stock)
    {
        $repository_product_warehouse = new IM_Product_Warehouse_Repository();
        $product_warehouse = $repository_product_warehouse->getByProductWarehouseID($product_id, $warehouse_id);
        $dto = array(
            "stock" => $stock
        );
        $condition = array(
            "id" => $product_warehouse->getId()
        );
        $repository_product_warehouse->update($dto, $condition);
    }

    /**
     * This method is used to restock deleted items from the order.
     */
    public function restock_deleted_item_line($item_id)
    {
        global $wpdb;

        $meta = wc_get_order_item_meta($item_id, "_variation_id");

        if (! empty($meta)) {
            $product_id = $meta;
        } else {
            $product_id = wc_get_order_item_meta($item_id, "_product_id");
        }

        $warehouse_id = wc_get_order_item_meta($item_id, "_warehouse_id");
        $stock_reduced = wc_get_order_item_meta($item_id, "_stock_reduced");

        if (! empty($product_id) && ! empty($warehouse_id)) {

            $repository_product_warehouse = new IM_Product_Warehouse_Repository();
            $product_warehouse = $repository_product_warehouse->getByProductWarehouseID($product_id, $warehouse_id);
            $total_stock = $product_warehouse->getStock();
            $stock = $total_stock + $stock_reduced;

            $this->update_product_warehouse_stock($product_id, $warehouse_id, $stock);

            wp_reset_query();
            // martelanço porque não consigo aceder ao $post global
            $order_id = $wpdb->get_var($wpdb->prepare("SELECT order_id FROM " . $wpdb->prefix . "woocommerce_order_items WHERE order_item_id = %d", $item_id));

            $warehouse_destination_id = get_post_meta($order_id, "_warehouse_destination_id", true);
            if (! empty($warehouse_destination_id)) {
                $repository_product_warehouse = new IM_Product_Warehouse_Repository();
                $product_warehouse_destination = $repository_product_warehouse->getByProductWarehouseID($product_id, $warehouse_destination_id);
                $total_stock_destination = $product_warehouse_destination->getStock();
                $total_stock_destination = ($total_stock_destination - $stock_reduced);

                $this->update_product_warehouse_stock($product_id, $warehouse_destination_id, $total_stock_destination);

                $reason = "order #" . $order_id . " - restock deleted line";
                $this->add_stock_log($product_id, $warehouse_destination_id, $total_stock_destination, $reason);
            }

            // Issue #19
            $reason = "order #" . $order_id . " - restock deleted line";
            $this->add_stock_log($product_id, $warehouse_id, $stock, $reason);
            // end issue #19

            $this->update_total_stocks_woocommerce($product_id);
        }
    }

    /**
     * This is for issue #19, add a stock log to store the stock movements
     */
    public function add_stock_log($product_id, $warehouse_id, $stock, $reason)
    {
        // Issue #19
        $stock_log_content = array();
        $stock_log_content["product_id"] = $product_id;
        $stock_log_content["warehouse_id"] = $warehouse_id;
        $stock_log_content["stock"] = $stock;
        $stock_log_content["reason"] = $reason;
        $repository_product_stock_log = new IM_Stock_Log_Repository();
        $repository_product_stock_log->insert($stock_log_content);
        // end issue #19

		$threshold = get_option("hd_warehouses_low_stock_threshold_per_warehouse");
		// if it behaves like this lets notify the store owner
		if((int)$threshold > 0 && (int)$stock <= (int)$threshold) {
			$warehouse_repository = new IM_Warehouse_Repository();
			$product = wc_get_product($product_id);
			$warehouse = $warehouse_repository->getByID($warehouse_id);
			$stock_notification = new IM_Stock_Notification();
			$stock_notification->notify_low_stock($product, $warehouse, $stock);
		}
    }

    /**
     * Method that adds the warehouse id to the item if needed.
     *
     * @param int $item_id
     * @param int $warehouse_id
     */
    public function add_warehouse_id_to_item($item_id, $warehouse_id, $chose_warehouse = true)
    {
        $current_meta = wc_get_order_item_meta($item_id, '_warehouse_id', true);
        if (empty($current_meta) || !$chose_warehouse) {
            // persist the changes
            wc_update_order_item_meta($item_id, '_warehouse_id', $warehouse_id);
        }
    }

    /**
     * Method that adds the stock reduced to the item if needed
     *
     * @param int $item_id
     */
    public function add_stock_reduced_to_item($item_id)
    {
        $current_meta = wc_get_order_item_meta($item_id, '_stock_reduced', true);
        if (empty($current_meta)) {
            // persist the changes
            wc_add_order_item_meta($item_id, '_stock_reduced', 0, true);
        }
    }

    // sort the elements using usort
    public function cmp($a, $b)
    {
        if ($a->priority == $b->priority) {
            return 0;
        }
        return ($a->priority < $b->priority) ? - 1 : 1;
    }

    /**
     * Method that applies the stock reduction by priorities.
     *
     * @param int post_id
     * @param array $warehouse
     * @param int $item_id
     * @param array $item_array
     * @return int $warehouse_id
     */
    public function apply_warehouse_stock_reduction_priorities($post_id, $item_id, $item_array)
    {
		// get the order object
		$order = wc_get_order($post_id);

    $repository_product_warehouse = new IM_Product_Warehouse_Repository();

        // Issue #66 - Online Warehouse
        if (IM_Online_Warehouse::get_instance()->status == 1) {
            return IM_Online_Warehouse::get_instance()->id;
        }
        // end issue #66

        $meta = wc_get_order_item_meta($item_id, "_variation_id");

        if (! empty($meta)) {
            $product_id = $meta;
        } else {
            $product_id = wc_get_order_item_meta($item_id, "_product_id");
        }

		// grab the product warehouses that this product is in.
		$product_warehouses = $repository_product_warehouse->getByProductID($product_id);
		$count = 1;

		if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			if($order->get_shipping_country()){
				$country = $order->get_shipping_country();
			}
			else{
				$country = $order->get_billing_country();
			}
		}
		else{
			if($order->shipping_country){
	    		$country = $order->shipping_country;
	    	}
	    	else{
		    	$country = $order->billing_country;
	    	}
	    }
	    $warehousesByCountries = $repository_product_warehouse->getByProductWarehouseByCountry($product_id, $country);

		// Issue #59 - Set by country
		$option = get_option('hd_warehouses_frontend_stock_selection');
		if(isset($option) && !empty($option) && $option == "per_country" && !empty($warehousesByCountries)) {
			$warehouse_repository = new IM_Warehouse_Repository();

			// loop the regular warehouses
			foreach ($warehousesByCountries as $warehousesByCountry) {
				// if the local priority is zero, we grab the global priority for the element
	            if ($warehousesByCountry->priority == 0) {
					$row = $warehouse_repository->getByID($warehousesByCountry->warehouse_id);
					$warehousesByCountry->priority = $row->IM_Warehouse_priority;
				}

				// swap the priority
				$warehousesByCountry->priority = $count;
				$count++;
			}

			$notInThisCountry = array_udiff($product_warehouses, $warehousesByCountries, function ($obj_a, $obj_b) {
			    return $obj_a->id - $obj_b->id;
			});

			foreach($notInThisCountry as $warehouse) {
				// if the local priority is zero, we grab the global priority for the element
	            if ($warehouse->priority == 0) {
					$row = $warehouse_repository->getByID($warehouse->warehouse_id);
					$warehouse->priority = $row->IM_Warehouse_priority;
				}

				// swap the priority
				$warehouse->priority = $warehouse->priority + $count;
				$count++;
			}

			$product_warehouses = array_merge($warehousesByCountries, $notInThisCountry);

			// sort them
			usort($product_warehouses, array(
				$this,
				"cmp"
			));

		}
		else
		{
	        // loop them
	        for ($i = 0; $i < count($product_warehouses); $i ++) {
	            // if the local priority is zero, we grab the global priority for the element
	            if ($product_warehouses[$i]->priority == 0) {
	                $repository_warehouse = new IM_Warehouse_Repository();
	                $row = $repository_warehouse->get_by(array(
	                    "id" => $product_warehouses[$i]->warehouse_id
	                ));
	                $row = $row[0];
	                $product_warehouses[$i]->priority = $row->priority;
	            }
	        }
		}

        foreach($product_warehouses as $key => $prod){
    			if(!isset($prod->priority) || is_null($prod->priority)){
    				unset($product_warehouses[$key]);
    			}
    		}

    	if(!$option || $option == 'user_based'){

	    	if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    		$user = $order->get_customer_id();
	    	}
	    	else{
	    		$user = $order->customer_user;
	    	}

	    	if(isset($user) && $user){
	    		$base_warehouse     = get_user_meta($user, 'hd_ime_base_warehouse', TRUE);
	    		if($base_warehouse){
		    		foreach($product_warehouses as $k=>$product_ware){
			    		if($product_ware->warehouse_id == $base_warehouse){
				    		$product_warehouses[$k]->priority = 1;
			    		}
			    		else{
				    		$product_warehouses[$k]->priority++;
			    		}
		    		}
	    		}
	    	}

    	}



        usort($product_warehouses, array(
            $this,
            "cmp"
        ));

        $_pf = new \WC_Product_Factory();
        if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			       $_product = $_pf->get_product($product_id);
  		  }
  		  else{
          	$_product = $_pf->get_product($product_id);
        }

        // loop them after sorted.
        foreach ($product_warehouses as $product_warehouse) {
            // if it has stock then we can assign it and end the case.
            $allow_exclusive = false;
            $repository_warehouse = new IM_Warehouse_Repository();
            $row = $repository_warehouse->get_by(array(
                "id" => $product_warehouse->warehouse_id
            ));

            foreach($warehousesByCountries as $warehouseByCountry){
              if ($warehouseByCountry->id = $product_warehouse->warehouse_id){
                $allow_exclusive = true;
                break;
              }
            }

            if ($product_warehouse->stock > 0 || ($_product->stock <= 0 && $_product->backorders_allowed()) || ($row[0]->exclusive && $allow_exclusive && isset($option) && !empty($option) && $option == "per_country")){
              if(($row[0]->exclusive) && (!$allow_exclusive) && isset($option) && !empty($option) && $option == "per_country"){
                break;
              }
                return $product_warehouse->warehouse_id;
                break;
            }
        }

        return $product_warehouses[0]->warehouse_id;
    }

    /**
     * Updates the stocks depending on the current order status, it will respect
     * the settings defined at the settings page.
     */
    public function update_stocks_per_item($post_id, $reduce = true)
    {
        $factory = new \WC_Order_Factory();
        $order = $factory->get_order($post_id);
        $status = $order->get_status();
        $items = $order->get_items();
        $pickup_warehouse = get_post_meta($post_id, '_billing_pickup_warehouse', true);
        $order_type = get_post_meta($post_id, 'wc_pos_order_type', TRUE);
        if($order_type == 'POS'){
          $register = get_post_meta($post_id, 'wc_pos_id_register', TRUE);
          if($register > 0){
            $outlet_repository = new IM_Outlet_Repository();
        		$outlet_obj = $outlet_repository->get_outlet_by_register($register);
          }
        }

        if (isset($_POST['warehouse'])) {
            if (is_admin()) {
                $warehouse = $_POST['warehouse'];
            } else {
                return;
            }
        } else if(!empty($pickup_warehouse)){
            $warehouse = $pickup_warehouse;
        }
        else if($order_type == 'POS' && $register && isset($outlet_obj->warehouse_id)){
          $warehouse = 0;
          $warehouse = $outlet_obj->warehouse_id;
        }
        else {
            $warehouse = array();
            // issue #10 - warehouse priorities applied
            foreach ($items as $key => $item) {

              // if there is no warehouse selected we use the priorities mechanism
      				if(!isset($item["warehouse_id"])) {
      	                $warehouse[$key] = $this->apply_warehouse_stock_reduction_priorities($post_id, $key, $item);
      				} else {
      					$warehouse[$key] = $item["warehouse_id"];
      				}
                  }
                  // end of issue #10
              }
              // end is set $_POST warehouse

        // add the warehouse id and the stock reduced to the item meta if needed
        foreach ($items as $key => $item) {
            if(is_array($warehouse)){
              $this->add_warehouse_id_to_item($key, $warehouse[$key]);
            }
            else{
              $this->add_warehouse_id_to_item($key, $warehouse);
            }
            $this->add_stock_reduced_to_item($key);
        }

        $warehouse_items = array();

        // reduce the stock
        $items = $order->get_items();

        if($reduce){
          foreach ($items as $key => $item) {

		  	if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			  	if($item->get_variation_id()){
				  	$pid = $item->get_variation_id();
			  	}
			  	else{
				  	$pid = $item->get_product_id();
			  	}
		  	}
		  	else{
	            if($item['variation_id'] == 0){
	              $pid = $item['item_meta']['_product_id'][0];
	            }
	            else{
	              $pid = $item['variation_id'];
	            }
            }

            $product_factory = new \WC_Product_Factory();
			$product = $product_factory->get_product($pid);

            if($product && $product->managing_stock()){
              if(is_array($warehouse)){
                $warehouse_id = $warehouse[$key];
              }
              else{
                $warehouse_id = $warehouse;
              }
              if (isset($warehouse_id)) {
                  $item = $this->update_meta_order_stock_reduced($key, $item, $warehouse_id, $status, $post_id);
              }
            }
          }

        update_post_meta($post_id, '_reduced_stock_already', true);

        // Add stock removed order note
        $stock_changes_order_notes = get_option("hd_warehouses_order_stock_notes");
        if($stock_changes_order_notes){
    		    $order->add_order_note( __( 'Order stocks have been removed from selected warehouse(s).', 'woocommerce-inventorymanager' ) );
        }

        $number_diferent_warehouses = 0;

        if(is_array($warehouse)){
          $number_diferent_warehouses = array_count_values($warehouse);
        }

        if(count($number_diferent_warehouses) == 1 || !is_array($warehouse)){
          $option = get_option('hd_warehouses_warehouse_email');
      		if($option == 'all' || $option == $warehouse_id) {
	      	  if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
		        $warehouse_notificated = get_post_meta($order->get_id(), "_warehouse_email_sent", true);
	      	  }
	      	  else{
		      	if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			      	$warehouse_notificated = get_post_meta($order->get_id(), "_warehouse_email_sent", true);
			    }
			    else{
              		$warehouse_notificated = get_post_meta($order->id, "_warehouse_email_sent", true);
              	}
              }
              if(empty($warehouse_notificated)){
                $warehouse_notification = new IM_Stock_Notification();
                $warehouse_notification->notify_warehouse($warehouse_id, $order);
              }
          }
          if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			update_post_meta($order->get_id(), "_warehouse_email_sent", true);
		  }
	      else{
          	update_post_meta($order->id, "_warehouse_email_sent", true);
          }
        }
      }
    }

    public function warehouse_to_warehouse($post_id)
    {
        $order = wc_get_order($post_id);

        if (isset($order) && $order instanceof \WC_Order) {

            // checks if warehouse destination is set
            if (isset($_POST["IM_Warehouse_destination"]) && ! empty($_POST["IM_Warehouse_destination"])) {
                // save the warehouse if
                update_post_meta($post_id, '_warehouse_destination_id', $_POST["IM_Warehouse_destination"]);
                $warehouse_repository = new IM_Warehouse_Repository();
                $warehouse_object_results = $warehouse_repository->get_by(array(
                    "id" => $_POST["IM_Warehouse_destination"]
                ));
                $warehouse_object = $warehouse_object_results[0];

                $address = array(
                    'first_name' => $warehouse_object->name,
                    'last_name' => "",
                    'company' => $warehouse_object->name,
                    'address_1' => $warehouse_object->address,
                    'address_2' => "",
                    'postcode' => $warehouse_object->postcode,
                    'city' => $warehouse_object->city,
                    'country' => $warehouse_object->country
                );
				if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
					update_post_meta($order->get_id(), '_billing_VAT_code', $warehouse_object->vat);
				}
				else{
                	update_post_meta($order->id, '_billing_VAT_code', $warehouse_object->vat);
                }

                $order->set_address($address, 'billing');
                $order->set_address($address, 'shipping');
            }
        }
    }

    /**
     * Function to update the meta field stock reduced.
     * This field lets us know how many
     * units have been removed from the stock.
     */
    public function update_meta_order_stock_reduced($key, $item, $warehouse_id, $status, $post_id = NULL)
    {
	    if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	        $qty = (int) $item->get_quantity();
	    }
	    else{
	        $qty = (int) $item['item_meta']['_qty'][0];
	    }

        $no_note = false;
        if (isset($item['item_meta']['_stock_reduced'][0]) && $qty == $item['item_meta']['_stock_reduced'][0]) {
            $no_note = true;
        }
        if ($post_id == null) {
            global $post;
            $post_id = $post->ID;
        }
        // check if it a product variation
        if (! empty($item['variation_id'])) {
            $item_id = $item['variation_id'];
        } else {
            $item_id = $item['product_id'];
        }
		$product_object = wc_get_product($item_id);

		if($product_object instanceof \WC_Product_Bundle) {
			return $item;
		}

        $repository_product_warehouse = new IM_Product_Warehouse_Repository();
        $product_warehouse = $repository_product_warehouse->getByProductWarehouseID($item_id, $warehouse_id);
        // total stock of this product
        $total_stock = $product_warehouse->getStock();
        // this way it is only reduced once for the units that have not been reduced yet.
        $stock = $this->stock_calculator($total_stock, $status, $item);
        // persist the content
        $dto = array(
            "stock" => $stock
        );
        $condition = array(
            "id" => $product_warehouse->getId()
        );
        $repository_product_warehouse->update($dto, $condition);
        $warehouse_destination_id = get_post_meta($post_id, "_warehouse_destination_id", true);
        if ($status == "wc-cancelled" || $status == "wc-refunded" || $status == "wc-failed") {
            // issue #18 - move stocks
            if ($warehouse_destination_id != "") {
                // so now we remove the stock from the destination warehouse
                $product_warehouse_destination = $repository_product_warehouse->getByProductWarehouseID($item_id, $warehouse_destination_id);
                $total_stock_destination = $product_warehouse_destination->getStock();

                if (isset($item['item_meta']['_stock_reduced'][0])) {
                    $reduced = (int) $item['item_meta']['_stock_reduced'][0];
                } else {
                    $reduced = 0;
                }
                $stock_destination_moved = (int) $total_stock_destination + (int) $reduced;
                $dto = array(
                    "stock" => $stock_destination_moved
                );
                $condition = array(
                    "id" => $product_warehouse_destination->getId()
                );
                $repository_product_warehouse->update($dto, $condition);
                $this->add_stock_log($item_id, $warehouse_destination_id, $stock_destination_moved, "order #" . $post_id . " - order updated");
            }
            // end of issue #18
            $item['item_meta']['_stock_reduced'][0] = 0;
			wc_update_order_item_meta($key, '_warehouse_id', $warehouse_id);
            wc_update_order_item_meta($key, '_stock_reduced', 0);
        } else {
            // issue #18 - move stocks
            if ($warehouse_destination_id != "") {
                // so now we add the stock from the origin warehouse to the destination
                $product_warehouse_destination = $repository_product_warehouse->getByProductWarehouseID($item_id, $warehouse_destination_id);
                $total_stock_destination = $product_warehouse_destination->getStock();

                if (isset($item['item_meta']['_stock_reduced'][0])) {
                    $reduced = (int) $item['item_meta']['_stock_reduced'][0];
                } else {
                    $reduced = 0;
                }
                if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	                $qty = (int) $item->get_quantity();
                }
                else{
                	$qty = (int) $item['item_meta']['_qty'][0];
                }
                $stock_destination_moved = ($total_stock_destination + ($qty - $reduced));
                $dto = array(
                    "stock" => $stock_destination_moved
                );
                $condition = array(
                    "id" => $product_warehouse_destination->getId()
                );
                $repository_product_warehouse->update($dto, $condition);
                $this->add_stock_log($item_id, $warehouse_destination_id, $stock_destination_moved, "order #" . $post_id . " - order updated");
            }

            // end of issue #18
			wc_update_order_item_meta($key, '_warehouse_id', $warehouse_id);
            wc_update_order_item_meta($key, '_stock_reduced', $item['qty']);
        }
        $this->update_total_stocks_woocommerce($item_id);
        // Issue #19
        if ($no_note == false) {
            $this->add_stock_log($item_id, $warehouse_id, $stock, "order #" . $post_id . " - order updated");
        }
        // end issue #19
        return $item;
    }

    /**
     * This is how the stock qty is calculated in the plugin
     */
    public function stock_calculator($total_stock, $status, $item)
    {
        $stock = 0;
        switch ($status) {
            case 'wc-cancelled':
            case 'wc-refunded':
              if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	          	  $qty = (int) $item->get_quantity();
	          }
	          else{
		          $qty = (int) $item['item_meta']['_qty'][0];
	          }
              if (isset($item['item_meta']['_stock_reduced'])) {
                  $reduced = (int) $item['item_meta']['_stock_reduced'][0];
              } else {
                  $reduced = 0;
              }
              $stock = $total_stock - $qty;
              break;
            case 'wc-failed':
                $stock = $total_stock + ((int) $item['item_meta']['_stock_reduced'][0]);
                break;

            default:
                if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	          	    $qty = (int) $item->get_quantity();
	          	}
	          	else{
		         	$qty = (int) $item['item_meta']['_qty'][0];
	          	}
                if (isset($item['item_meta']['_stock_reduced'])) {
                    $reduced = (int) $item['item_meta']['_stock_reduced'][0];
                } else {
                    $reduced = 0;
                }
                $stock = $total_stock - ($qty - $reduced);
                break;
        }
        return $stock;
    }

    public function update_total_stocks_woocommerce($product_id)
    {
        // repository pattern
        $repository_product_warehouse = new IM_Product_Warehouse_Repository();
        $product_warehouses = $repository_product_warehouse->getByProductID($product_id);

        $total_stock = 0;
        foreach ($product_warehouses as $product_warehouse) {
            $raw_stock = $product_warehouse->stock;
            $total_stock += $raw_stock;
        }

        if ($total_stock > 0) {
            update_post_meta($product_id, "_stock_status", "instock");
        } else {
            update_post_meta($product_id, "_stock_status", "outofstock");
        }

        // Issue #66
        IM_Online_Warehouse::get_instance()->checkOnlineWarehouseStatus($product_id);
        // end issue #66

        update_post_meta($product_id, "_stock", $total_stock);
    }

    public function custom_woocommerce_hidden_order_itemmeta($arr)
    {
        $arr[] = '_warehouse_id';
        $arr[] = '_stock_reduced';
        return $arr;
    }

	/**
	 * [select_warehouse_container adds a metabox to allow select a specific warehouse to all the lines in the order]
	 */
    public function select_warehouse_container()
    {
        global $post;

        if($post->post_type !== 'shop_order'){
	        return;
        }

        $order = new \WC_Order($post->ID);
        if (! $order->is_editable()) {
            return;
        }
        add_meta_box('im-warehouse-select-warehouse-container', __('Select warehouse to all lines', "woocommerce-inventorymanager"), array(
            $this,
            'create_select_warehouse_container'
        ), 'shop_order', 'side');
    }

	/**
	 * [create_select_warehouse_container method that renders the warehouse container]
	 */
    public function create_select_warehouse_container()
    {
        $repository = new IM_Warehouse_Repository();
        $warehouses = $repository->get_all();
        $values = array(
            "warehouses" => $warehouses
        );
        $this->viewRender = IM_View_Render::get_instance();
        $this->viewRender->render("select-warehouse-metabox", $values);
    }

	/**
	 * [action_woocommerce_restore_order_stock hooks into this action that is in the order]
	 * @param  [type] $order [description]
	 */
	public function action_woocommerce_restore_order_stock($order) {
		$return = array();
		$order_items = $order->get_items();
		$order_item_ids = isset( $_POST['order_item_ids'] ) ? $_POST['order_item_ids'] : array();
		$order_item_qty = isset( $_POST['order_item_qty'] ) ? $_POST['order_item_qty'] : array();

		if ( $order && !empty( $order_items ) && sizeof( $order_item_ids ) > 0 ) {
			foreach ( $order_items as $item_id => $order_item ) {
				// make sure we only have checked items
				if ( ! in_array( $item_id, $order_item_ids ) ) {
					continue;
				}

				// get the product
				$product = $order->get_product_from_item( $order_item );


				if ( $product->exists() && $product->managing_stock() && isset( $order_item_qty[ $item_id ] ) && $order_item_qty[ $item_id ] > 0 ) {
					//$stock_change = apply_filters( 'woocommerce_reduce_order_stock_quantity', $order_item_qty[ $item_id ], $item_id );

					$warehouse_id = wc_get_order_item_meta($item_id, "_warehouse_id", true);

					$product_warehouse_repository = new IM_Product_Warehouse_Repository();
          if($product instanceof \WC_Product_Variation){
              $product_warehouse = $product_warehouse_repository->getByProductWarehouseID($product->variation_id, $warehouse_id);
          }
          else{
					    $product_warehouse = $product_warehouse_repository->getByProductWarehouseID($product->get_id(), $warehouse_id);
          }
					$old_stock = $product_warehouse->getStock();

					$new_quantity = $product_warehouse->increaseStock($order_item_qty[ $item_id ]);
					$product_warehouse_repository->updateRow($product_warehouse);
		  if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			  $this->add_stock_log($product->get_id(), $warehouse_id, $new_quantity, "order #" . $order->get_id() . " - order updated");
		  }
		  else{
          	  $this->add_stock_log($product->get_id(), $warehouse_id, $new_quantity, "order #" . $order->id . " - order updated");
          }

          // Workaround to a stock bug
          if($product->stock != $new_quantity){
            $product->increase_stock( $order_item_qty[ $item_id ] );
          }
				}
			}
		}
	}

	public function save_base_warehouse_field( $user_id ){
		// Base warehouse
      if(isset($_POST['hd_ime_base_warehouse'])){
        update_user_meta( $user_id, 'hd_ime_base_warehouse', sanitize_text_field( $_POST['hd_ime_base_warehouse'] ) );
      }
	}

	public function add_base_warehouse_field( $user ){
		if (current_user_can( 'manage_options' )) {
			$base_warehouse     = get_user_meta($user->ID, 'hd_ime_base_warehouse', TRUE);
	        $repository = new IM_Warehouse_Repository();
			$warehouses = $repository->get_all();

			?>
			<h3>Base Warehouse</h3>
	              <table class="form-table">
	                <tr>
	                  <td><select name="hd_ime_base_warehouse" id="hd_ime_base_warehouse"><?php
		                echo "<option value=''>" . 'Default' . '</option>';
	                  foreach($warehouses as $v){
	                    $selected = '';
	                    if($v->id == $base_warehouse){
	                      $selected = 'selected';
	                    }
	                    echo "<option value='{$v->id}' $selected>" . $v->name . '</option>';
	                  }
	                  ?>
	                </select></td>
	              </tr>
	            </table>
	            <hr>
	            <?php
		}
	}

	/**
	 * [action_woocommerce_reduce_order_stock hooks into this action that is in the order]
	 * @param  [type] $order [order object]
	 */
	public function action_woocommerce_reduce_order_stock($order) {
		$return = array();
		$order_items = $order->get_items();
		$order_item_ids = isset( $_POST['order_item_ids'] ) ? $_POST['order_item_ids'] : array();
		$order_item_qty = isset( $_POST['order_item_qty'] ) ? $_POST['order_item_qty'] : array();
    $warehouse_id = $_POST['warehouse'];

		if ( $order && !empty( $order_items ) && sizeof( $order_item_ids ) > 0 ) {
			foreach ( $order_items as $item_id => $order_item ) {
				// make sure we only have checked items
				if ( ! in_array( $item_id, $order_item_ids ) ) {
					continue;
				}

				// get the product
				$product = $order->get_product_from_item( $order_item );

				if ( $product->exists() && $product->managing_stock() && isset( $order_item_qty[ $item_id ] ) && $order_item_qty[ $item_id ] > 0 ) {
					$warehouse_id = wc_get_order_item_meta($item_id, "_warehouse_id", true);

					$product_warehouse_repository = new IM_Product_Warehouse_Repository();

          if($product instanceof \WC_Product_Variation){
              $product_warehouse = $product_warehouse_repository->getByProductWarehouseID($product->variation_id, $warehouse_id);
          }
          else{
					    $product_warehouse = $product_warehouse_repository->getByProductWarehouseID($product->get_id(), $warehouse_id);
          }

					$old_stock = $product_warehouse->getStock();
          $new_quantity = $product_warehouse->decreaseStock($order_item_qty[ $item_id ]);
					$product_warehouse_repository->updateRow($product_warehouse);
		  if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			  $this->add_stock_log($product->get_id(), $warehouse_id, $new_quantity, "order #" . $order->get_id() . " - order updated");
		  }
		  else{
          	  $this->add_stock_log($product->get_id(), $warehouse_id, $new_quantity, "order #" . $order->id . " - order updated");
          }

				}
			}
		}

	}

  function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
    $output = NULL;
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }
    $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
    $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
    $continents = array(
        "AF" => "Africa",
        "AN" => "Antarctica",
        "AS" => "Asia",
        "EU" => "Europe",
        "OC" => "Australia (Oceania)",
        "NA" => "North America",
        "SA" => "South America"
    );
    if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
        $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            switch ($purpose) {
                case "location":
                    $output = array(
                        "city"           => @$ipdat->geoplugin_city,
                        "state"          => @$ipdat->geoplugin_regionName,
                        "country"        => @$ipdat->geoplugin_countryName,
                        "country_code"   => @$ipdat->geoplugin_countryCode,
                        "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                        "continent_code" => @$ipdat->geoplugin_continentCode
                    );
                    break;
                case "address":
                    $address = array($ipdat->geoplugin_countryName);
                    if (@strlen($ipdat->geoplugin_regionName) >= 1)
                        $address[] = $ipdat->geoplugin_regionName;
                    if (@strlen($ipdat->geoplugin_city) >= 1)
                        $address[] = $ipdat->geoplugin_city;
                    $output = implode(", ", array_reverse($address));
                    break;
                case "city":
                    $output = @$ipdat->geoplugin_city;
                    break;
                case "state":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "region":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "country":
                    $output = @$ipdat->geoplugin_countryName;
                    break;
                case "countrycode":
                    $output = @$ipdat->geoplugin_countryCode;
                    break;
            }
        }
    }
    return $output;
  }
}
