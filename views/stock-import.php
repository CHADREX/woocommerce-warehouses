<?php
	delete_option('hd_warehouse_stock_file');
	$uploaded = 'btn_disabled';
	$uploaded_label = __('Upload file first','woocommerce-inventory-manager');
	$csv_example_url = plugin_dir_url(IM_PLUGIN_FILE) . 'assets/csv/hd_csv_example_import.csv';
	$csv_example_element = "  <a href='$csv_example_url'>(Example)</a>";
	?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?> <a class="add-new-h2" target="_blank" href="<?php menu_page_url("hellodev-inventory-manager-stock-export-csv"); ?>"><?php _e("Export Stock") ?></a></h2>

<div class="t-col-6">
  <div class="toret-box box-info">
    <div class="box-header">
      <h3 class="box-title"><?php _e('Import','woocommerce-inventory-manager'); ?></h3>
    </div>
  <div class="box-body">
    <h4><?php _e('You can upload csv file, with your stock data.','woocommerce-inventory-manager'); echo $csv_example_element;?></h4>
    <p><?php _e('CSV file must have the following fields and contain a maximum of 5000 lines. ','woocommerce-inventory-manager'); ?></p>
    <h3><?php _e('File fields','stock-manager'); ?></h3>

    <ul>
      <li><strong><?php _e('id','woocommerce-inventorymanager'); ?></strong> <?php _e('Product unique identificator.','woocommerce-inventory-manager'); ?></li>
      <li><strong><?php _e('stock_{warehouse_slug}','woocommerce-inventorymanager'); ?></strong> <?php _e('Stock quantity of a warehouse with a given slug (one column per warehouse).','woocommerce-inventory-manager'); ?></li>
    </ul>


    <form method="post" action="" class="setting-form" enctype="multipart/form-data">
        <table class="table-bordered">
          <tr>
            <th><?php _e('Upload csv file', 'stock-manager'); ?></th>
            <td>
              <input type="file" name="uploadFile">
            </td>
          </tr>

        </table>
      <input type="hidden" name="upload" value="ok" />
      <input type="submit" class="btn btn-info" value="<?php _e('Upload', 'woocommerce-inventory-manager'); ?>" />
    </form>
    <?php
    if(isset($_POST['upload'])){

      $target_dir = IM_PLUGIN_PATH . 'assets/csv/';
      $target_dir = $target_dir . basename( $_FILES["uploadFile"]["name"]);
      $uploadOk   = true;

      if (move_uploaded_file($_FILES["uploadFile"]["tmp_name"], $target_dir)) {

          echo '<p id="hd_upload_info">' .__('The file '. basename( $_FILES['uploadFile']['name']). ' has been uploaded. You can now import stock from it.','csv-category').'</p>';
          update_option('hd_warehouse_stock_file', $_FILES['uploadFile']['name']);
					$uploaded = '';
					$uploaded_label = __('Start Update','woocommerce-inventory-manager');
					echo "<script>
					setTimeout(function(){
							jQuery('#hd_upload_info').remove();
							}, 5000);
					</script>";
      }else{
        echo '<p>'.__('Sorry, there was an error uploading your file.','woocommerce-inventory-manager').'</p>';
				delete_option('hd_warehouse_stock_file');
      }
}
?>
  </div>
</div>
</div>

<div class="t-col-6">
  <div class="toret-box box-info">
    <div class="box-header">
      <h3 class="box-title"><?php _e('Update','woocommerce-inventory-manager'); ?></h3>
    </div>
  <div class="box-body">
    <h4><?php _e('Update stock from a previously uploaded csv file. ','woocommerce-inventory-manager'); ?></h4>
    <p><a id="hd_import_from_csv" class="btn btn-info <?php echo $uploaded; ?>"><?php echo $uploaded_label; ?></a></p>
	<div id="hd_list_of_events"></div>
  </div>
</div>
</div>


</div>
