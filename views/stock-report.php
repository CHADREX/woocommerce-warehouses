<?php
namespace Hellodev\InventoryManager;
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
</style>
<div class="wrap">
  <h2><?php _e("Stock Report", "woocommerce-inventorymanager") ?> <a class="add-new-h2" target="_blank" href="<?php menu_page_url("hellodev-inventory-manager-stock-report-csv"); ?>"><?php _e("Export CSV") ?></a></h2>

  <form id="events-filter" method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    <?php
    $wp_list_table = new IM_Stock_Report_List_Table();
    $wp_list_table->prepare_items();
    $wp_list_table->display();
    ?>
  </form>
</div>
