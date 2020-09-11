<?php
namespace Hellodev\InventoryManager;
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
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
</style>
<?php
function if_selected($value) {
  $selected = get_option('stock_reduction_state');
  if($selected == $value)
  echo "selected";
}
?>
<div class="wrap">
  <h2><?php _e("Stock Log", "woocommerce-inventorymanager") ?></h2>

  <form id="events-filter" method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    <?php
    $wp_list_table = new IM_Stock_Log_List_Table();
    $wp_list_table->prepare_items();
    $wp_list_table->display();
    ?>
  </form>

</div>
