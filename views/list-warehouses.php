<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}

?>
<style>
.copyright {
	font-size: 14px;
	padding-top: 50px;
}

.copyright img {
	vertical-align: middle;
	height: 28px;
}

.wp-list-table .column-cb {
	width: 5%;
}

.wp-list-table .column-id {
	width: 5%;
}

.wp-list-table .column-time {
	width: 10%;
}

.wp-list-table .column-name {
	width: 8%;
}

.wp-list-table .column-address {
	width: 15%;
}

.wp-list-table .column-postcode {
	width: 10%;
}

.wp-list-table .column-city {
	width: 10%;
}

.wp-list-table .column-country {
	width: 10%;
}

.wp-list-table .column-vat {
	width: 8%;
}

.wp-list-table .column-email {
	width: 17%;
}

.wp-list-table .column-slug {
	width: 10%;
}

.wp-list-table .column-pickup {
	width: 7%;
}

.wp-list-table .column-exclusive {
	width: 7%;
}

.wp-list-table .column-priority {
	width: 7%;
}
</style>
<div class="wrap">
    <form id="events-filter" method="get">
    	<input type="hidden" name="page"
    		value="<?php echo $_REQUEST['page'] ?>" />
    <?php
    $wp_list_table = new IM_Warehouse_List_Table();

    $action = $wp_list_table->process_action();
    switch($action){
      case 'delete':
        $wp_list_table->remove_warehouses_display();
      break;
      case 'show_table':
      default:
        ?>
        <h2>
          <?php _e("List of warehouses", "woocommerce-inventorymanager"); ?>
          <a class="add-new-h2"
      			href="<?php menu_page_url("hellodev-inventory-manager-add-warehouse") ?>"><?php _e("Add warehouse", "woocommerce-inventorymanager"); ?></a>
      	</h2>
        <?php

        $wp_list_table->show_message();
        $wp_list_table->prepare_items();
        $wp_list_table->display();
      break;
    }
    ?>
    </form>
</div>
