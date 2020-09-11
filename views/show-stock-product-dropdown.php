<?php if(!empty($values["product_warehouses"])){ ?>
<p>
	<?php
	if(!empty($values['product_warehouses'])){ ?>
		<h4>Stocks:</h4>
		<ul id="hd_warehouse_stocks" name="hd_warehouse_stocks">
			<?php foreach($values['product_warehouses'] as $product_warehouse) : ?>
				<li> <?php echo $product_warehouse->warehouse->IM_Warehouse_name . " - " . __("Stock", "woocommerce-inventorymanager") . ": " . $product_warehouse->stock; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php
	}?>
</p>
<?php }
else{ ?>
	<select disabled id="hd_select_warehouse_product_stock" name="_warehouse_id">
	<select id="hd_select_warehouse_product_stock" name="_warehouse_id">
		<option value="">Choose an option first</option>
	</select>
<?php } ?>
