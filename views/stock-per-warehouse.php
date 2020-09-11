<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}
?>
<style>
.hd-im-stock-table {
	border-collapse: collapse;
	border: 1px solid #D7E3EE !important;
}

.hd-im-stock-table tr th {
	position:relative;
	padding: 10px;
	border-bottom: 1px solid #D7E3EE !important;
}

.hd-im-stock-table tr th:after {
  content:"";
  background: #D7E3EE;
  position: absolute;
  bottom: 15%;
  right: 0;
  height: 65%;
  width: 1px;
}

.hd-im-stock-table tr th:last-child:after {
	width: 0;
}

.hd-im-stock-table tr td {
   padding: 5px 10px 5px 10px;
}

.hd-im-stock-table input[type="text"] {
	width: 100%;
}

.hd-im-stock-header {
	background-color: #F6F9FE;
	border-color: #D7E3EE !important;
	color: #99B3CD !important;
}

.hd-im-stock-header tr th {
	color: #99B3CD !important;
}

.hd-im-stock-table-body p, .hd-im-stock-table-body input[type=text] {
	color: #718BA8 !important;
}
</style>
<table class="fixed hd-im-stock-table">
  <thead class="hd-im-stock-header">
    <tr>
      <th colspan="3" style="text-align: center; font-weight: bold"><?php _e("Warehouse Stocks Plugin Options", "woocommerce-inventorymanager"); ?> - <?php _e("List of warehouses", "woocommerce-inventorymanager"); ?></th>
    </tr>
    <tr>
      <th class="left manage-column column-columnname" style="text-align: center"><?php _e("Warehouse name", "woocommerce-inventorymanager"); ?></th>
      <th class="left manage-column column-columnname" style="text-align: center"><?php _e("Stock", "woocommerce-inventorymanager"); ?></th>
      <th class="left manage-column column-columnname" style="text-align: center"><?php _e("Stock reduction priority", "woocommerce-inventorymanager"); ?></th>
    </tr>
  </thead>
  <tbody class="hd-im-stock-table-body">
    <?php foreach($values['warehouses'] as $warehouse): ?>
      <tr class="form-field">
        <td class="right column-columnname">
          <p><?php echo $warehouse["warehouse"]->name; ?></p>
        </td>
        <td class="left column-columnname">
          <p><input type="text" class="input_stock_warehouse" name="product[<?php echo $warehouse["product_id"]; ?>][<?php echo $warehouse["warehouse"]->id; ?>]" value="<?php if(isset($warehouse["stock"]) && !empty($warehouse["stock"])) { echo $warehouse["stock"]; } else { echo "0"; } ?>" /></p>
        </td>
        <td class="left column-columnname">
          <p><input type="text" class="input_priority_warehouse" name="product-priority[<?php echo $warehouse["product_id"]; ?>][<?php echo $warehouse["warehouse"]->id; ?>]" value="<?php if(isset($warehouse["priority"]) && !empty($warehouse["priority"])) { echo $warehouse["priority"]; } else { echo "0"; } ?>" /></p>
        </td>
      </tr>
    <?php endforeach; ?>
    <tr>
      <td class="right column-columnname">
        <p style="font-weight: bold"><?php _e("Total stock in all warehouses", "woocommerce-inventorymanager"); ?></p>
      </td>
      <td class="left column-columnname" colspan="2">
        <p style="font-weight: bold" id="total_stock_<?php echo $warehouse["product_id"]; ?>"><?php echo $values['total_stock']; ?> <?php _e("units", "woocommerce-inventorymanager"); ?></p>
      </td>
    </tr>
  </tbody>
</table>
