<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Stock_Report_List_Table extends IM_List_Table
{

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'wp_list_im_stock_report', // Singular label
            'plural' => 'wp_list_im_stocks_reports', // plural label, also this well be one of the table css class
            'ajax' => false
        )) // We won't support Ajax for this table
;
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
        $warehouse_repository = new IM_Warehouse_Repository();
        $warehouse_results = $warehouse_repository->get_all();

        $columns = array(
            'product_id' => __("Product ID", "woocommerce-inventorymanager"),
            'product_name' => __("Product Name", "woocommerce-inventorymanager"),
            'product_link' => __("Product Link", "woocommerce-inventorymanager")
        );

        foreach ($warehouse_results as $value) {
            $columns["warehouse_" . $value->id] = $value->name;
        }

        $options_raw = get_option("hd_warehouses_custom_meta_stock_export");
        // they're splitted by ;
        $options = array_filter(explode(";", $options_raw));

        if ($options != false && count($options) > 0) {
            foreach ($options as $value) {
                $columns[$value] = $value;
            }
        }

        return $columns;
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
     */
    public function get_sortable_columns()
    {
        return array(
            'product_id' => array(
                'product_id',
                false
            )
        );
    }

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    public function prepare_items()
    {
        $screen = get_current_screen();

        /* -- Check if there are bulk actions -- */
        $this->process_bulk_action();

        /* -- Ordering parameters -- */
        // Parameters that are going to be used to order the result
        $orderby = ! empty($_GET["orderby"]) ? ($_GET["orderby"]) : 'timestamp';
        $order = ! empty($_GET["order"]) ? ($_GET["order"]) : 'DESC';

        /* -- Pagination parameters -- */
        $paged = ! empty($_GET["paged"]) ? ($_GET["paged"]) : '';
        // Page Number
        if (empty($paged) || ! is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }

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

        $stock_per_page = get_option("hd_warehouses_stock_log_per_page");
        if(isset($stock_per_page) && $stock_per_page){
          $perpage = $stock_per_page;
        }
        else{
          $perpage = 25;
        }

        $totalitems = $products->found_posts;
        $totalpages = ceil($totalitems / $perpage);

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
        $args = array(
            'post_type' => array(
                'product',
                'product_variation'
            ),
            'paged' => $paged,
            "orderby" => $orderby,
            "order" => $order,
            "posts_per_page" => $perpage,
            "post_status"    => 'publish'
        );
        $products = new \WP_Query($args);
        $this->items = $products->posts;
    }

    /**
     * Display the rows of records in the table
     *
     * @return string, echo the markup of the rows
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
                echo '<tr id="record_' . $rec->ID . '">';

                $warehouse_repository = new IM_Warehouse_Repository();

                $product_warehouse_repository = new IM_Product_Warehouse_Repository();
                $product_warehouse_results = $product_warehouse_repository->getByProductID($rec->ID);

                $product_factory = new \WC_Product_Factory();
                $product = $product_factory->get_product($rec->ID);

                $product_id = $rec->ID;

                $title = $rec->post_title;

                if(is_object($product)){

                //Issue #115
                if($product instanceof \WC_Product_Variation){
	              if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
		              $product_id = $product->get_id();
                  if(is_object($product) && ($product instanceof \WC_Product_Variation)) {
                    $product_id = $product->get_parent_id();
                  }
		            }
		          else{
	                  $product_id = $product->post->ID;
                  }
                  $attributes = $product->get_variation_attributes();
                  $title = $product->get_title() . ' - ' . implode( ', ', $attributes );
                }

                foreach ($columns as $column_name => $column_display_name) {

                    // Style attributes for each col
                    $class = "class='$column_name column-$column_name'";
                    $style = "";
                    if (in_array($column_name, $hidden))
                        $style = ' style="display:none;"';
                    $attributes = $class . $style;

                    // Display the cell
                    switch ($column_name) {
                        case "product_id":
                            echo '<td ' . $attributes . '>' . $rec->ID . '</td>';
                            break;
                        case "product_name":
                            echo '<td ' . $attributes . '>' . $title . '</td>';
                            break;
                        case "product_link":
                            echo '<td ' . $attributes . '><a href="' . get_edit_post_link($product_id) . '">' . __("Link", "woocommerce-inventorymanager") . '</a></td>';
                            break;
                    }

					if($product->managing_stock()) {
	                    foreach ($product_warehouse_results as $value) {
	                        if ($column_name == "warehouse_" . $value->warehouse_id) :
	                            echo '<td ' . $attributes . '>' . stripslashes($value->stock) . '</td>';
				            endif;
	                    }
					}
					else
					{
						if (strncmp($column_name, "warehouse_", 10) == 0) :
							echo '<td ' . $attributes . '>' . __("None", "woocommerce-inventorymanager") . '</td>';
						endif;
					}

                    $options_raw = get_option("hd_warehouses_custom_meta_stock_export");
                    // they're splitted by ;
                    $options = array_filter(explode(";", $options_raw));

                    if ($options != false && count($options) > 0) {
                        foreach ($options as $value) {
                            switch ($column_name) {
                                case $value:
                                    echo '<td ' . $attributes . '>' . get_post_meta($rec->ID, $value, true) . '</td>';
                                    break;
                            }
                        }
                    }
                }

                // Close the line
                echo '</tr>';
              }
            }
        }
    }
}
