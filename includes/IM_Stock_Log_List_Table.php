<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Stock_Log_List_Table extends IM_List_Table
{

    private $table_name;
    private $filter_array;

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    public function __construct()
    {
        $this->table_name = IM_PLUGIN_DATABASE_TABLE_STOCK_LOG;
        parent::__construct(array(
            'singular' => 'wp_list_im_stock_log', // Singular label
            'plural' => 'wp_list_im_stocks_logs', // plural label, also this well be one of the table css class
            'ajax' => false
        ));

        if(isset($_REQUEST['hd-im-filter-stocklog-warehouse-top'])){

          // Process filter data into array
          $this->filter_array = $this->get_filter_data();
        }
    }

    public function get_filter_data(){
      $filter_data = array('warehouse_id' => $_REQUEST['hd-im-filter-stocklog-warehouse-top'], 'date_1' => $_REQUEST['hd-im-stocklog-date1-top'], 'date_2' => $_REQUEST['hd-im-stocklog-date2-top'],
      'product' => $_REQUEST['hd-im-stocklog-product-top']);

      return $filter_data;
    }

    /**
     * Add extra markup in the toolbars before or after the list
     *
     * @param string $which,
     *            helps you decide if you add the markup after (bottom) or before (top) the list
     */
    public function extra_tablenav($which){

      // Add filter to top tablenav
      if($which == 'top'){

        //Event type

        echo "</select>\n";

        $warehouse_repo = new IM_Warehouse_Repository();
        $warehouses = $warehouse_repo->get_all();

        echo "<select name='hd-im-filter-stocklog-warehouse-top' id='hd-im-filter-stocklog-warehouse-top" . "'>";

    		echo "<option value=''>" . __( 'Any Warehouse' ) . "</option>\n";

    		foreach ( $warehouses as $warehouse ) {
          $selected_warehouse = '';
          if(isset($this->filter_array['warehouse_id']) && $this->filter_array['warehouse_id'] == $warehouse->id){
            $selected_warehouse = "selected='selected'";
          }

    			echo "\t<option value='$warehouse->id' $selected_warehouse>$warehouse->name</option>\n";
        }
        echo "</select>\n";

        $date_1 = '';
        $date_2 = '';

        if(isset($this->filter_array['date_1'])){
          $date_1 = $this->filter_array['date_1'];
        }

        if(isset($this->filter_array['date_2'])){
          $date_2 = $this->filter_array['date_2'];
        }

        echo "<input type='text' name='hd-im-stocklog-date1-top' id='hd-im-stocklog-date1-top' class='hd-input-add-date' placeholder='From Date' value='$date_1'/>";
        echo "<input type='text' name='hd-im-stocklog-date2-top' id='hd-im-stocklog-date2-top' class='hd-input-add-date' placeholder='To Date'' value='$date_2'/>";

        $product = '';
        if(isset($this->filter_array['product'])){
          $product = $this->filter_array['product'];
        }

        echo "<input type='number' name='hd-im-stocklog-product-top' id='hd-im-stocklog-product-top' placeholder='Product ID' min='0' value='$product'/>";
      }
    }

    /**
     * Define the columns that are going to be used in the table
     *
     * @return array $columns, the array of columns to use with the table
     */
    public function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'product' => __('Product', "woocommerce-inventorymanager"),
            'warehouse_name' => __('Warehouse name', "woocommerce-inventorymanager"),
            'stock' => __('Stock', "woocommerce-inventorymanager"),
            'timestamp' => __('Timestamp', "woocommerce-inventorymanager"),
            'description' => __('Description', "woocommerce-inventorymanager")
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

    /**
     * How the bulk actions are processed for this table.
     */
    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            foreach ($_GET[$this->_args['singular']] as $item) {
                $this->delete_this_log($item);
            }
        }
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
            'product' => array(
                'product_id',
                true
            ),
            'timestamp' => array(
                'timestamp',
                false
            )
        );
    }

    public function build_query(){
      $query = "SELECT * FROM $this->table_name";
      $where_count = 0;
      if(isset($this->filter_array['warehouse_id']) && !empty($this->filter_array['warehouse_id'])){
        $warehouse_id = $this->filter_array['warehouse_id'];
        $where_count++;
        $query .= " WHERE warehouse_id = '$warehouse_id'";
      }

      if(isset($this->filter_array['date_1']) && !empty($this->filter_array['date_1']) && isset($this->filter_array['date_2']) && !empty($this->filter_array['date_2'])){
        $date_1 = $this->filter_array['date_1'];
        $date_2 = $this->filter_array['date_2'];
        if($where_count == 0){
          $query .= " WHERE timestamp BETWEEN '$date_1' AND '$date_2'";
        }else{
          $query .= " AND timestamp BETWEEN '$date_1' AND '$date_2'";
        }
        $where_count++;
      }

      if(isset($this->filter_array['product']) && !empty($this->filter_array['product'])){
        $product_id = $this->filter_array['product'];
        if($where_count == 0){
          $query .= " WHERE product_id = '$product_id'";
        }else{
          $query .= " AND product_id = '$product_id'";
        }
        $where_count++;
      }

      return $query;
    }

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    public function prepare_items()
    {
        global $wpdb;
        $screen = get_current_screen();

        /* -- Check if there are bulk actions -- */
        $this->process_bulk_action();

        /* -- Preparing your query -- */
        $query = $this->build_query();

        /* -- Ordering parameters -- */
        // Parameters that are going to be used to order the result
        $orderby = ! empty($_GET["orderby"]) ? ($_GET["orderby"]) : 'timestamp';
        $order = ! empty($_GET["order"]) ? ($_GET["order"]) : 'DESC';
        if (! empty($orderby) & ! empty($order)) {
            $query .= ' ORDER BY ' . $orderby . ' ' . $order;
        }

        /* -- Pagination parameters -- */
        // Number of elements in your table?
        $totalitems = $wpdb->query($query); // return the total number of affected rows
                                            // How many to display per page?
        $stock_per_page = get_option("hd_warehouses_stock_log_per_page");
        if(isset($stock_per_page)){
          $perpage = (int) $stock_per_page;
        }
        else{
          $perpage = 25;
        }
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
        $_wp_column_headers[$screen->id] = $columns;
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

                    $product_factory = new \WC_Product_Factory();
                    $warehouse_repository = new IM_Warehouse_Repository();
                    $product = $product_factory->get_product($rec->product_id);
                    $warehouse_results = $warehouse_repository->getByID($rec->warehouse_id);
                    $warehouse = $warehouse_results;

                    $product_id = $rec->product_id;
                    if(is_object($product) && ($product instanceof \WC_Product_Variation)) {
                      $product_id = $product->get_parent_id();
                    }

                    if($product){

                    // Display the cell
                    switch ($column_name) {
                        case "id":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->id) . '</td>';
                            break;
                        case "product":
                            echo '<td ' . $attributes . '><a href="' . get_edit_post_link($product_id) . '">' . $product->get_formatted_name() . '</a></td>';
                            break;
                        case "warehouse_name":
                            echo '<td ' . $attributes . '>' . stripslashes($warehouse->IM_Warehouse_name) . '</td>';
                            break;
                        case "stock":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->stock) . '</td>';
                            break;
                        case "timestamp":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->timestamp) . '</td>';
                            break;
                        case "description":
                            echo '<td ' . $attributes . '>' . stripslashes($rec->reason) . '</td>';
                            break;
                        case "cb":
                            echo $this->column_cb($rec);
                            break;
                    }
                  }
                }

                // Close the line
                echo '</tr>';
            }
        }
    }

    /**
     * Deletes a specific stock log.
     * @param int $log_id log id
     */
    public function delete_this_log($log_id)
    {
        global $wpdb;
        /* -- Preparing the delete query to avoid SQL injection -- */
        $query = $wpdb->prepare("DELETE FROM $this->table_name WHERE id = %d", $log_id);

        $wpdb->query($query); // return the total number of affected rows
    }
}
