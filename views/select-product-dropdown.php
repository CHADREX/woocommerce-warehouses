<?php if(!empty($values["product_warehouses"])){ ?>
<p>
	<select id="hd_select_warehouse_product" name="_warehouse_id">
		<?php foreach($values['product_warehouses'] as $product_warehouse) : ?>
			<option data-stock="<?php echo $product_warehouse->stock; ?>" value="<?php echo $product_warehouse->warehouse_id ?>">
				<?php echo $product_warehouse->warehouse->IM_Warehouse_name; echo " - "; _e("Stock", "woocommerce-inventorymanager"); echo ": " . $product_warehouse->stock; ?>
			</option>
		<?php endforeach; ?>
	</select>
</p>
<?php }
else{ ?>
	<select disabled id="hd_select_warehouse_product" name="_warehouse_id">
	<select id="hd_select_warehouse_product" name="_warehouse_id">
		<option value="">Choose an option first</option>
	</select>
<?php } ?>
