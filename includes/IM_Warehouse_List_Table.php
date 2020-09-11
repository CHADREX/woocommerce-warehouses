<?php

namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Warehouse_List_Table extends IM_List_Table
{

    private $table_name;
    private $delete_array;
    private $message;

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    public function __construct()
    {
        global $wpdb;
        $this->delete_array = array();
        $this->message;
        $this->table_name = $wpdb->prefix . 'inventory_manager_warehouses';
        parent::__construct(array(
            'singular' => 'wp_list_im_warehouse', // Singular label
            'plural' => 'wp_list_im_warehouses', // plural label, also this well be one of the table css class
            'ajax' => false
        )); // We won't support Ajax for this table
    }

    /**
     * Add extra markup in the toolbars before or after the list
     *
     * @param string $which,
     *            helps you decide if you add the markup after (bottom) or before (top) the list
     */
    public function extra_tablenav($which)
    {}

    /**
     * Define the columns that are going to be used in the table
     *
     * @return array $columns, the array of columns to use with the table
     */
    public function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'id' => __('ID', "woocommerce-inventorymanager"),
            'time' => __('Date added', "woocommerce-inventorymanager"),
            'name' => __('Name', "woocommerce-inventorymanager"),
            'address' => __('Address', "woocommerce-inventorymanager"),
            'postcode' => __('Postcode', "woocommerce-inventorymanager"),
            'city' => __('City', "woocommerce-inventorymanager"),
            'country' => __('Country', "woocommerce-inventorymanager"),
            'vat' => __('VAT', "woocommerce-inventorymanager"),
			'email' => __('Email', "woocommerce-inventorymanager"),
            'slug' => __('Slug', "woocommerce-inventorymanager"),
            'pickup' => __('Pickup', "woocommerce-inventorymanager"),
            'exclusive' => __('Exclusive', "woocommerce-inventorymanager"),
            'priority' => __('Priority', "woocommerce-inventorymanager")
        );
    }

    public function column_cb($item)
    {
        return sprintf('<th scope="row" class="check-column"><input type="checkbox" name="%1$s[]" value="%2$s" /></th>',
    /*$1%s*/ $this->_args['singular'], // Let's simply repurpose the table's singular label ("video")
        /* $2%s */
        $item->id); // The value of the checkbox should be the record's id
    }

    /**
     * Returns the list of available bulk actions.
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    public function show_message(){
      if(isset($this->message)){?>
        <div class="updated">
        <p><?php echo $this->message ?></p>
        </div><?php
      }
    }

    public function add_stock_log($product_id, $warehouse_id, $stock, $reason){
        $stock_log_content = array();
        $stock_log_content["product_id"] = $product_id;
        $stock_log_content["warehouse_id"] = $warehouse_id;
        $stock_log_content["stock"] = $stock;
        $stock_log_content["reason"] = $reason;
        $repository_product_stock_log = new IM_Stock_Log_Repository();
        $repository_product_stock_log->insert($stock_log_content);
    }

    /**
     * How the bulk actions are processed for this table.
     */
    public function process_action(){
        if ('delete' === $this->current_action()) {
            if(isset($_GET[$this->_args['singular']])){
              $delete_array = array();
              foreach ($_GET[$this->_args['singular']] as $item) {
                  $delete_array[] = $item;
              }
              if(!empty($delete_array)){
                $this->delete_array = $delete_array;
                return 'delete';
              }
            }
        }
        else if('dodelete' === $this->current_action()) {

          $warehouses_array = array();
          if(isset($_REQUEST['warehouses'])){
            $warehouses_array = $_REQUEST['warehouses'];
          }

          if(isset($_REQUEST['delete_option'])){
            $delete_option = $_REQUEST['delete_option'];
            switch($delete_option){
              case 'delete':
                $_pf = new \WC_Product_Factory();
                foreach($warehouses_array as $id){
                  $repository = new IM_Product_Warehouse_Repository();
                  $products = $repository->getByWarehouseID($id);
                  foreach($products as $product){
                    if($product->stock != 0){
                      $_product = $_pf->get_product($product->product_id);
                      $_product->reduce_stock($product->stock);
                    }

                    $repository->deleteByWarehouseID($id);
                    $this->delete_this_warehouse($id);
                    if(count($warehouses_array) > 1){
                      $this->message = __('Warehouses successfully deleted.');
                    }
                    else{
                      $this->message = sprintf(__('Warehouse #%s successfully deleted.'), $warehouses_array[0]);
                    }
                  }
                }
                break;
              case 'reassign':
                if(isset($_REQUEST['delete_select'])){
                  $transfer_warehouse_id = $_REQUEST['delete_select'];
                  $_pf = new \WC_Product_Factory();
                  foreach($warehouses_array as $id){
                    $repository = new IM_Product_Warehouse_Repository();
                    $products = $repository->getByWarehouseID($id);
                    foreach($products as $product){
                      if($product->stock != 0){
                        $_product = $_pf->get_product($product->product_id);

                        $product_warehouse = $repository->getByProductWarehouseID($product->product_id, $transfer_warehouse_id);
              					$new_quantity = $product_warehouse->increaseStock($product->stock);
                        $repository->updateRow($product_warehouse);

                        // Stock transfer log
                        $reason = "warehouse #" . $id . " deleted - stock transferred";
                        $this->add_stock_log($product->product_id, $transfer_warehouse_id, $new_quantity, $reason);
                      }

                      $repository->deleteByWarehouseID($id);
                      $this->delete_this_warehouse($id);
                      if(count($warehouses_array) > 1){
                        $this->message = __('Warehouses successfully deleted and stock transferred.');
                      }
                      else{
                        $this->message = sprintf(__('Warehouse #%s successfully deleted and stock transferred.'), $warehouses_array[0]);
                      }
                    }
                  }
                }
                break;
              default:
                break;
            }
          }

        }
        return 'show_table';
    }

    public function remove_button_js(){
      ?><script>
      jQuery(document).ready( function($) {
      	var submit = $('#submit').prop('disabled', true);
      	$('input[name=delete_option]').one('change', function() {
      		submit.prop('disabled', false);
      	});
      	$('#reassign_user').focus( function() {
      		$('#delete_option1').prop('checked', true).trigger('change');
      	});
      });
      </script><?php
    }

    public function remove_warehouses_display(){
      $this->remove_button_js();
      ?>
      <h2>
        <form method="post" name="removewarehouses" id="removewarehouses">
        <?php _e("Delete Warehouses", "woocommerce-inventorymanager"); ?>
      </h2>
      <?php if ( 1 == count( $this->delete_array ) ) : ?>
      	<p><?php _e( 'You have specified this warehouse for deletion:', "woocommerce-inventorymanager"); ?></p>
      <?php else : ?>
      	<p><?php _e( 'You have specified these warehouses for deletion:', "woocommerce-inventorymanager" ); ?></p>
      <?php endif;

      $repository = new IM_Warehouse_Repository();
      $warehouses = $repository->get_all();

      foreach ( $this->delete_array as $id ) {
        $warehouse = $repository->getByID($id);
    			echo "<li><input type=\"hidden\" name=\"warehouses[]\" value=\"" . esc_attr($id) . "\" />" . sprintf(__('ID #%1$s: %2$s'), $id, $warehouse->IM_Warehouse_name) . "</li>\n";
    	}

      if ( 1 == count( $this->delete_array )) : ?>
    		<fieldset><p><legend><?php _e( 'What should be done with content owned by this warehouse?', "woocommerce-inventorymanager" ); ?></legend></p>
    	<?php else : ?>
    		<fieldset><p><legend><?php _e( 'What should be done with content owned by these warehouses?', "woocommerce-inventorymanager" ); ?></legend></p>
    	<?php endif; ?>

      <ul style="list-style:none;">
    		<li><label><input type="radio" id="delete_option0" name="delete_option" value="delete" />

        <?php _e('Delete all content.'); ?></label></li>
        <?php
        if(count($this->delete_array) < count($warehouses)){ ?>
      		<li><input type="radio" id="delete_option1" name="delete_option" value="reassign" />
          <?php echo '<label for="delete_option1">' . __( 'Attribute all content to:' ) . '</label> ';

          echo '<select id="delete_select" name="delete_select">';
          foreach ($warehouses as $warehouse) {
            if(!in_array ($warehouse->id, $this->delete_array)){
              echo '<option value="'.$warehouse->id.'">'.$warehouse->name.'</option>';
            }
          }
          echo '</select>';

      		 ?></li>
         <?php } ?>

    	</ul></fieldset>

      <input type="hidden" name="action" value="dodelete" />
    	<?php submit_button( __('Confirm Deletion'), 'secondary' ); ?>

      </form><?php
    }

    /**
     * Checks between action and action2.
     */
    public function current_action()
    {
        if (isset($_REQUEST['action']) && - 1 != $_REQUEST['action'])
            return $_REQUEST['action'];

        if (isset($_REQUEST['action2']) && - 1 != $_REQUEST['action2'])
            return $_REQUEST['action2'];

        return false;
    }

    /**
     * Decide which columns to activate the sorting functionality on
     *
     * @return array $sortable, the array of columns that can be sorted by the user
     *
     */
    public function get_sortable_columns()
    {
        return array(
            'id' => array(
                'id',
                false
            ),
            'name' => array(
                'name',
                false
            )
        );
    }

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    public function prepare_items()
    {
        global $wpdb;
        $screen = get_current_screen();

        /* -- Check if there are bulk actions -- */
        //$this->process_bulk_action();

        /* -- Preparing your query -- */
        $query = "SELECT * FROM $this->table_name";

        /* -- Ordering parameters -- */
        // Parameters that are going to be used to order the result
        $orderby = ! empty($_GET["orderby"]) ? ($_GET["orderby"]) : 'ASC';
        $order = ! empty($_GET["order"]) ? ($_GET["order"]) : '';
        if (! empty($orderby) & ! empty($order)) {
            $query .= ' ORDER BY ' . $orderby . ' ' . $order;
        }

        /* -- Pagination parameters -- */
        // Number of elements in your table?
        $totalitems = $wpdb->query($query); // return the total number of affected rows
                                            // How many to display per page?
        $perpage = 25;
        // Which page is this?
        $paged = ! empty($_GET["paged"]) ? ($_GET["paged"]) : '';
        // Page Number
        if (empty($paged) || ! is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }
        // How many pages do we have in total?
        $totalpages = ceil($totalitems / $perpage);
        // adjust the query to take pagination into account
        if (! empty($paged) && ! empty($perpage)) {
            $offset = ($paged - 1) * $perpage;
            $query .= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
        }

        /* -- Register the pagination -- */
        $this->set_pagination_args(array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage
        ));
        // The pagination links are automatically built according to those parameters

        /* -- Register the Columns -- */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array(
            $columns,
            $hidden,
            $sortable
        );

        /* -- Fetch the items -- */
        $this->items = $wpdb->get_results($query);
    }

    /**
     * Display the rows of records in the table
     *
     * @return string, echo the markup of the rows
     *
     */
    public function display_rows()
    {

        // Get the records registered in the prepare_items method
        $records = $this->items;

        // Get the columns registered in the get_columns and get_sortable_columns methods
        list ($columns, $hidden) = $this->get_column_info();

        // Loop for each record
        if (! empty($records)) {
            foreach ($records as $rec) {
                // Open the line
                echo '<tr id="record_' . $rec->id . '">';

                foreach ($columns as $column_name => $column_display_name) {

                    // Style attributes for each col
                    $class = "class='$column_name column-$column_name'";
                    $style = "";
                    if (in_array($column_name, $hidden))
                        $style = ' style="display:none;"';
                    $attributes = $class . $style;

                    // edit link
                    $editlink = menu_page_url("hellodev-inventory-manager-add-warehouse", 0) . '&IM_Warehouse_id=' . (int) $rec->id;

                    // Display the cell
                    switch ($column_name) {
                        case "id":
                            echo '<td ' . $attributes . '><a href="' . $editlink . '">' . stripslashes($rec->id) . '</a></td>';
                            break;
                        case "name":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->name) . '</td>';
                            break;
                        case "time":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->time) . '</td>';
                            break;
                        case "address":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->address) . '</td>';
                            break;
                        case "postcode":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->postcode) . '</td>';
                            break;
                        case "city":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->city) . '</td>';
                            break;
                        case "country":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->country) . '</td>';
                            break;
                        case "vat":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->vat) . '</td>';
                            break;
						            case "email":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->email) . '</td>';
                            break;
                        case "slug":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->slug) . '</td>';
                            break;
                        case "pickup":
                            $pickup = ($rec->pickup) ? __('yes', 'woocommerce-warehouses') : __('no', 'woocommerce-warehouses');
                            echo '<td ' . $attributes . '>' . stripslashes($pickup) . '</td>';
                            break;
                        case "exclusive":
                            $exclusive = ($rec->exclusive) ? __('yes', 'woocommerce-warehouses') : __('no', 'woocommerce-warehouses');
                            echo '<td ' . $attributes . '>' . stripslashes($exclusive) . '</td>';
                            break;
                        case "priority":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->priority) . '</td>';
                            break;
                        case "cb":
                            echo $this->column_cb($rec);
                            break;
                    }
                }

                // Close the line
                echo '</tr>';
            }
        }
    }

    public function delete_this_warehouse($warehouse_id)
    {
        global $wpdb;
        /* -- Preparing the delete query to avoid SQL injection -- */
        $query = $wpdb->prepare("DELETE FROM $this->table_name WHERE id = %d", $warehouse_id);

        $wpdb->query($query);
    }
}
