<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}
?>
<style>
.input-add-warehouse {
	width: 25em !important;
}

textarea {
	height: 100px;
}
</style>

<?php $options = ""; ?>
<div class="wrap">
	<?php if(!isset($values['IM_Warehouse_id'])) : ?>
		<h2><?php _e("Add warehouse", "woocommerce-inventorymanager");
    ?> <a class="add-new-h2"
      href="<?php menu_page_url("hellodev-inventory-manager") ?>"><?php _e("Back", "woocommerce-inventorymanager"); ?></a></h2>
	<?php else : ?>
		<h2><?php _e("Edit warehouse", "woocommerce-inventorymanager"); ?>
    <a class="add-new-h2"
      href="<?php menu_page_url("hellodev-inventory-manager") ?>"><?php _e("Back", "woocommerce-inventorymanager"); ?></a></h2>
	<?php endif;?>
  <?php if(isset($values['error']) && isset($_REQUEST['saved']) && $_REQUEST['saved'] == 'error'): ?>
  	<div class="error">
		<p><?php echo $values['error']; ?></p>
	</div>
  <?php endif; ?>
  <?php if(isset($values['success']) && isset($_REQUEST['saved']) && $_REQUEST['saved'] == 'success'): ?>
  	<div class="updated">
		<p><?php echo $values['success']; ?></p>
	</div>
  <?php endif; ?>
  <form method="post" action="">
		<table class="form-table">
			<tbody>
				<tr class="form-field">
					<th scope="row"><label for="IM_Warehouse_name"><?php _e("Name", "woocommerce-inventorymanager"); ?></label></th>
					<td><input type="text" name="IM_Warehouse_name"
						class="input-add-warehouse"
						value="<?php if(isset($values['IM_Warehouse_name'])) echo $values['IM_Warehouse_name'] ?>"
						<?php echo $options; ?> /></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="IM_Warehouse_address"><?php _e("Full Address", "woocommerce-inventorymanager"); ?></label></th>
					<td><textarea name="IM_Warehouse_address" id="IM_Warehouse_address"
							class="input-add-warehouse" <?php echo $options; ?>><?php if(isset($values['IM_Warehouse_address'])) echo $values['IM_Warehouse_address'] ?></textarea>
            <?php if(get_option('hd_warehouses_show_googlemap_pickup') && get_option('hd_warehouses_show_googlemap_key')){?>
            <span class="hd_see_map hd_map_backend" place="<?php if(isset($values['IM_Warehouse_address'])) echo $values['IM_Warehouse_address'] ?>" zoom="16">See Map</span>
            <?php }?>
            </td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="IM_Warehouse_city"><?php _e("City", "woocommerce-inventorymanager"); ?></label></th>
					<td><input type="text" name="IM_Warehouse_city"
						class="input-add-warehouse"
						value="<?php if(isset($values['IM_Warehouse_city'])) echo $values['IM_Warehouse_city'] ?>"
						<?php echo $options; ?> />
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="IM_Warehouse_postcode"><?php _e("Postcode", "woocommerce-inventorymanager"); ?></label></th>
					<td><input type="text" name="IM_Warehouse_postcode"
						class="input-add-warehouse"
						value="<?php if(isset($values['IM_Warehouse_postcode'])) echo $values['IM_Warehouse_postcode'] ?>"
						<?php echo $options; ?> /></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="IM_Warehouse_country"><?php _e("Country", "woocommerce-inventorymanager"); ?></label></th>
					<td><input type="text" name="IM_Warehouse_country"
						class="input-add-warehouse"
						value="<?php if(isset($values['IM_Warehouse_country'])) echo $values['IM_Warehouse_country'] ?>"
						<?php echo $options; ?> /></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="IM_Warehouse_vat"><?php _e("VAT", "woocommerce-inventorymanager"); ?></label></th>
					<td><input type="text" name="IM_Warehouse_vat"
						class="input-add-warehouse"
						value="<?php if(isset($values['IM_Warehouse_vat'])) echo $values['IM_Warehouse_vat'] ?>"
						<?php echo $options; ?> /></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="IM_Warehouse_email"><?php _e("Email", "woocommerce-inventorymanager"); ?></label></th>
					<td><input type="text" name="IM_Warehouse_email"
						class="input-add-warehouse"
						value="<?php if(isset($values['IM_Warehouse_email'])) echo $values['IM_Warehouse_email'] ?>"
						<?php echo $options; ?> /></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="IM_Warehouse_slug"><?php _e("Slug", "woocommerce-inventorymanager"); ?></label></th>
					<td><input type="text" name="IM_Warehouse_slug"
						class="input-add-warehouse"
						value="<?php if(isset($values['IM_Warehouse_slug'])) echo $values['IM_Warehouse_slug'] ?>"
						<?php echo $options; ?> /></td>
				</tr>
        <tr class="form-field">
          <th scope="row"><label for="IM_Warehouse_pickup"><?php _e("Use as pickup location", "woocommerce-inventorymanager"); ?>?</label></th>
          <input type="hidden" name="IM_Warehouse_pickup" class="input-add-warehouse" value="0">
          <td><input type="checkbox" name="IM_Warehouse_pickup" value="1"
          <?php if(isset($values['IM_Warehouse_pickup'])){
                  if($values['IM_Warehouse_pickup'] == 1) {?>
                      checked="checked">
                      <?php } }?>
            </td>
          </tr>
          <?php if(!isset($values['IM_Warehouse_priority']) || !$values['IM_Warehouse_priority']){
            $values['IM_Warehouse_priority'] = 1;
          }?>
				<tr class="form-field">
					<th scope="row"><label for="IM_Warehouse_priority"><?php _e("Global stock priority", "woocommerce-inventorymanager"); ?></label></th>
					<td><input type="number" min="1" max="1000" name="IM_Warehouse_priority"
						class="input-add-warehouse"
						value="<?php if(isset($values['IM_Warehouse_priority'])) echo $values['IM_Warehouse_priority'] ?>"
						<?php echo $options; ?> /></td>
				</tr>

        <?php
        if (class_exists('WC_POS') || class_exists("WoocommercePointOfSale")){
				?>
				<tr class="form-field">
					<th scope="row"><label for="outlets"><?php _e("Outlets", "woocommerce-inventorymanager"); ?></label></th>
					<td>
						<div id="hellodev-inventory-manager-outlets-added"></div>
						<?php
						woocommerce_form_field(
							'hellodev-inventory-manager-outlets',
							array(
							    'type'       => 'select',
							    'class'      => array( 'input-add-warehouse' ),
							    'placeholder'    => __("Enter something", "woocommerce-inventorymanager"),
							    'options'    => $values["outlets"]
						    )
    					);
						?>
						<button type="button"
							class="button-primary"
							id="hellodev-inventory-manager-add-outlet"
							name="hellodev-inventory-manager-add-outlet">
							<?php _e("Add outlet", "woocommerce-inventorymanager"); ?>
						</button>
						<input type="hidden"
							id="IM_Warehouse_outlets"
							name="IM_Warehouse_outlets"
							value="<?php if(isset($values['IM_Warehouse_outlets'])) echo htmlspecialchars($values['IM_Warehouse_outlets']); ?>" />
					</td>
				</tr>
				<?php
      }

				if(get_option("hd_warehouses_frontend_stock_selection") == "per_country") {
				?>
        <tr class="form-field">
          <th scope="row"><label for="IM_Warehouse_exclusive"><?php _e("Exclusive", "woocommerce-inventorymanager"); ?>?</label></th>
          <input type="hidden" name="IM_Warehouse_exclusive" class="input-add-warehouse" value="0">
          <td><input type="checkbox" name="IM_Warehouse_exclusive" value="1"
          <?php if(isset($values['IM_Warehouse_exclusive'])){
                  if($values['IM_Warehouse_exclusive'] == 1) {?>
                      checked="checked">
                      <?php } }?>
                      <label for="IM_Warehouse_exclusive"> <?php _e("Exclusive to selected countries.", "woocommerce-inventorymanager")?></label>
            </td>
          </tr>
          <th scope="row"><label for="IM_Warehouse_prevent"><?php _e("Block stock", "woocommerce-inventorymanager"); ?>?</label></th>
          <input type="hidden" name="IM_Warehouse_prevent" class="input-add-warehouse" value="0">
          <td><input type="checkbox" name="IM_Warehouse_prevent" value="1"
          <?php if(isset($values['IM_Warehouse_prevent'])){
                  if($values['IM_Warehouse_prevent'] == 1) {?>
                      checked="checked">
                      <?php } }?>
                      <label for="IM_Warehouse_prevent"> <?php _e("Block purchases when out of stock to selected countries.", "woocommerce-inventorymanager")?></label>
            </td>
          </tr>
				<tr class="form-field">
					<th scope="row"><label for="countries"><?php _e("Countries", "woocommerce-inventorymanager"); ?></label></th>
					<td>
						<div id="hellodev-inventory-manager-countries-added"></div>
						<?php
						woocommerce_form_field(
							'hellodev-inventory-manager-countries',
							array(
							    'type'       => 'select',
							    'class'      => array( 'input-add-warehouse' ),
							    'placeholder'    => __("Enter something", "woocommerce-inventorymanager"),
							    'options'    => $values["countries"]
						    )
    					);
						?>
						<button type="button"
							class="button-primary"
							id="hellodev-inventory-manager-add-country"
							name="hellodev-inventory-manager-add-country">
							<?php _e("Add country", "woocommerce-inventorymanager"); ?>
						</button>
						<input type="hidden"
							id="IM_Warehouse_countries"
							name="IM_Warehouse_countries"
							value="<?php if(isset($values['IM_Warehouse_countries'])) echo htmlspecialchars($values['IM_Warehouse_countries']); ?>" />
					</td>
				</tr>
				<?php
				}
				?>
			</tbody>
		</table>

		<?php if(isset($values['IM_Warehouse_id'])): ?>
			<input type="hidden" name="IM_Warehouse_id" class="button-primary"
				value="<?php echo $values['IM_Warehouse_id']; ?>" />
		<?php endif; ?>

		<input type="hidden" name="hellodev-inventory-manager-add-warehouse" value="1" />

		<input type="submit" name="submit" class="button-primary"
			value="<?php _e("Save warehouse", "woocommerce-inventorymanager"); ?>" />
	</form>
</div>
