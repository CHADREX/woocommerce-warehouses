<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}

?>
<ul id="im-warehouse-select-warehouse"
	class="im-warehouse-select-warehouse">
	<li>
		<p>
			<strong><?php _e("Warehouse") ?>:</strong>
		</p>
		<p>
			<select name="IM_Warehouse_all_lines" id="IM_Warehouse_all_lines">
				<option selected="selected" value=""><?php _e('none'); ?></option>
                <?php
                foreach ($values["warehouses"] as $warehouse) :
                    ?>
                  <option value="<?php echo $warehouse->id; ?>"><?php echo $warehouse->name; ?></option>
                  <?php
                endforeach
                ;
                ?>
      		</select>
		</p>
	</li>
</ul>