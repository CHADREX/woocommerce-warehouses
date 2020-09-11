<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Plugin_Settings_Controller
{
  public $plugin_settings_tabs = array(
      "main_settings" => "Main Settings",
      "online_warehouse_settings" => "Online Warehouse",
      "stock_report_settings" => "Stock Report",
      "email_settings" => "Email Settings",
      "error_messages" => "Error Messages"
  );

  public function __construct()
  {
      // Hook up settings initialization
      add_action('admin_init', array(
          $this,
          'settings_init'
      ));
  }

  // Function that initiates plugin settings
  public function settings_init(){
      global $woocommerce;
      if($woocommerce) {
        wp_enqueue_style('woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css');
      }

      $settings_name = 'hd_warehouses_settings';
      $settings_title = __('Warehouses Settings', "woocommerce-inventory-manager");
      $settings_page = 'hellodev-inventory-manager-plugin-settings';

      $settings = array(
          array(
              'name' => "main_settings",
              'title' => $settings_title,
              'page' => $settings_page,
              'settings' => array(
                  array(
                      'name' => 'stock_reduction_state',
                      'title' => __('WooCommerce Stock Reduction State', "woocommerce-inventory-manager")
                  ),
                  array(
                      'name' => 'hd_warehouses_frontend_stock_selection',
                      'title' => __('Frontend orders warehouse selection method', "woocommerce-inventory-manager")
                  ),
                  array(
                      'name' => 'hd_warehouses_frontend_manual_user_role',
                      'title' => __('Manual warehouse user role', "woocommerce-inventory-manager")
                  ),
                  array(
                      'name' => 'hd_warehouses_stock_product_page',
                      'title' => __('Show stock at product page', "woocommerce-inventory-manager")
                  ),
                  array(
                      'name' => 'hd_warehouses_allow_pickup',
                      'title' => __('Allow warehouse order pickups', "woocommerce-inventory-manager")
                  ),
                  array(
                      'name' => 'hd_warehouses_show_googlemap_pickup',
                      'title' => __('Show Google Map of Pickup location', "woocommerce-inventory-manager")
                  ),
                  array(
                      'name' => 'hd_warehouses_show_googlemap_key',
                      'title' => __('Google Map API Key', "woocommerce-inventory-manager")
                  ),
                  array(
                      'name' => 'hd_warehouses_order_stock_notes',
                      'title' => __('Show stock changes (order notes)', "woocommerce-inventory-manager")
                  ),
                  array(
                      'name' => 'hd_warehouses_low_stock_threshold_per_warehouse',
                      'title' => __('Low stock threshold', "woocommerce-inventory-manager")
                  ),
              )
          ),
          array(
              'name' => "online_warehouse_settings",
              'title' => "Online Warehouse",
              'page' => $settings_page,
              'settings' => array(
                array(
                    'name' => 'hd_warehouses_online_warehouse_restriction',
                    'title' => __('Enable online warehouse restriction', "woocommerce-inventory-manager")
                ),
                array(
                    'name' => 'hd_warehouses_online_warehouse',
                    'title' => __('Online warehouse', "woocommerce-inventory-manager")
                )
              )
          ),
          array(
              'name' => "stock_report_settings",
              'title' => "Stock Report",
              'page' => $settings_page,
              'settings' => array(
                array(
                    'name' => 'hd_warehouses_csv_export_delimiter',
                    'title' => __('Stock Report Export CSV delimiter', "woocommerce-inventory-manager")
                ),
                array(
                    'name' => 'hd_warehouses_custom_meta_stock_export',
                    'title' => __('Custom meta fields in Stock Report', "woocommerce-inventory-manager")
                ),
                array(
                    'name' => 'hd_warehouses_stock_log_per_page',
                    'title' => __('Stock entries per page', "woocommerce-inventory-manager")
                )
              )
          ),
          array(
              'name' => "email_settings",
              'title' => "Email Settings",
              'page' => $settings_page,
              'settings' => array(
                array(
                    'name' => 'hd_warehouses_warehouse_email',
                    'title' => __('Email Warehouse', "woocommerce-inventory-manager")
                )
              )
          ),
          array(
              'name' => "error_messages",
              'title' => "Error Messages",
              'page' => $settings_page,
              'settings' => array(
                array(
                    'name' => 'hd_warehouses_country_nostock',
                    'title' => __('No stock for country message', "woocommerce-inventory-manager")
                ),
                array(
                    'name' => 'hd_warehouses_country_notenough_stock',
                    'title' => __('Not enough stock for country message', "woocommerce-inventory-manager")
                ),
                array(
                    'name' => 'hd_warehouses_online_nostock',
                    'title' => __('Not enough stock for country message', "woocommerce-inventory-manager")
                ),
                array(
                    'name' => 'hd_warehouses_online_notenough_stock',
                    'title' => __('Not enough stock for country message', "woocommerce-inventory-manager")
                ),
                array(
                    'name' => 'hd_warehouses_manual_nostock',
                    'title' => __('Not enough stock for manual warehouse message', "woocommerce-inventory-manager")
                ),
                array(
                    'name' => 'hd_warehouses_manual_notenough_stock',
                    'title' => __('Not enough stock for manual warehouse message', "woocommerce-inventory-manager")
                )
              )
          )
      );

      foreach ($settings as $section) {
          // add the main part
          add_settings_section($section['name'], $section['title'], array(
              $this,
              $section['name']
          ), $section['page']);

          // loop each settings of the block
          foreach ($section['settings'] as $option) {
              // add & register the settings field
              add_settings_field($option['name'], $option['title'], array(
                  $this,
                  $option['name']
              ), $section['page'], $section['name']);

              register_setting($section['name'], $option['name'], array($this, 'plugin_main_settings_validate'));
          }
      }
  }

  public function hd_warehouses_custom_meta_stock_export()
  {
      echo '<input type="text" name="hd_warehouses_custom_meta_stock_export" id="hd_warehouses_custom_meta_stock_export" value="' . get_option('hd_warehouses_custom_meta_stock_export') . '" autocomplete="off" />';
      echo '<label for="hd_warehouses_custom_meta_stock_export"> ' . __("Please input the fields (seperated by ;).", "woocommerce-inventorymanager") . '</label>';
  }

  public function hd_warehouses_settings()
  {
      echo '<p>' . __('Please fill in the necessary settings below.', "woocommerce-inventory-manager") . '</p>';
  }

  public function hd_warehouses_csv_export_delimiter()
  {
      echo '<input type="text" name="hd_warehouses_csv_export_delimiter" id="hd_warehouses_csv_export_delimiter" value="' . get_option('hd_warehouses_csv_export_delimiter') . '" autocomplete="off" />';
      echo '<label for="hd_warehouses_csv_export_delimiter"> ' . __('This will be used when you export a CSV Stock Report. European format is ";" and american is ",". Default: ";"', "woocommerce-inventorymanager") . '</label>';
  }

  public function hd_warehouses_online_warehouse_restriction()
  {
      $checked = (get_option('hd_warehouses_online_warehouse_restriction') == 1) ? 'checked="checked"' : '';
      echo '<input type="hidden" name="hd_warehouses_online_warehouse_restriction" value="0" />';
      echo '<input type="checkbox" name="hd_warehouses_online_warehouse_restriction" id="hd_warehouses_online_warehouse_restriction" value="1" '.$checked.' autocomplete="off" />';
      echo '<label for="hd_warehouses_online_warehouse_restriction"> ' . __('This is combined with the warehouse you select in the below dropdown.', "woocommerce-inventory-manager") . '</label>';
  }

  public function hd_warehouses_stock_product_page(){
    $checked = (get_option('hd_warehouses_stock_product_page') == 1) ? 'checked="checked"' : '';
    echo '<input type="hidden" name="hd_warehouses_stock_product_page" value="0" />';
    echo '<input type="checkbox" name="hd_warehouses_stock_product_page" id="hd_warehouses_stock_product_page" value="1" '.$checked.' autocomplete="off" />';
  }

  public function hd_warehouses_allow_pickup()
  {
      $checked = (get_option('hd_warehouses_allow_pickup') == 1) ? 'checked="checked"' : '';
      echo '<input type="hidden" name="hd_warehouses_allow_pickup" value="0" />';
      echo '<input type="checkbox" name="hd_warehouses_allow_pickup" id="hd_warehouses_allow_pickup" value="1" '.$checked.' autocomplete="off" />';
      echo '<label for="hd_warehouses_allow_pickup"> ' . __('Allows users to choose a warehouse as a pickup location (uses woocommerce local_pickup shipping method).', "woocommerce-inventory-manager") . '</label>';
  }

  public function hd_warehouses_show_googlemap_pickup()
  {
      $checked = (get_option('hd_warehouses_show_googlemap_pickup') == 1) ? 'checked="checked"' : '';
      echo '<input type="hidden" name="hd_warehouses_show_googlemap_pickup" value="0" />';
      echo '<input type="checkbox" name="hd_warehouses_show_googlemap_pickup" id="hd_warehouses_show_googlemap_pickup" value="1" '.$checked.' autocomplete="off" />';
      echo '<label for="hd_warehouses_show_googlemap_pickup"> ' . __('Shows Google Map of pickup location.', "woocommerce-inventory-manager") . '</label>';
  }

  public function hd_warehouses_show_googlemap_key()
  {
      echo '<input type="text" name="hd_warehouses_show_googlemap_key" id="hd_warehouses_show_googlemap_key" value="' . get_option('hd_warehouses_show_googlemap_key') . '" autocomplete="off" />';
      echo '<label for="hd_warehouses_show_googlemap_key"> ' . __('Requires Google Static Maps API activation on your google developer console.', "woocommerce-inventory-manager") . '</label>';
  }

  public function hd_warehouses_order_stock_notes()
  {
      $checked = (get_option('hd_warehouses_order_stock_notes') == 1) ? 'checked="checked"' : '';
      echo '<input type="hidden" name="hd_warehouses_order_stock_notes" value="0" />';
      echo '<input type="checkbox" name="hd_warehouses_order_stock_notes" id="hd_warehouses_order_stock_notes" value="1" '.$checked.' autocomplete="off" />';
  }



  public function hd_warehouses_warehouse_email()
  {
    $repository = new IM_Warehouse_Repository();
    $warehouses = $repository->get_all();

    echo '<select id="hd_warehouses_warehouse_email" name="hd_warehouses_warehouse_email">';

    echo '<option value="0">' . __('None', "woocommerce-inventory-manager") . '</option>';

    foreach ($warehouses as $warehouse) {
        $selected = (get_option('hd_warehouses_warehouse_email') == $warehouse->id) ? 'selected="selected"' : '';
       echo '<option ' . $selected . ' value="'.$warehouse->id.'">'.$warehouse->name.'</option>';
    }

    $selected = (get_option('hd_warehouses_warehouse_email') == 'all') ? 'selected="selected"' : '';
    echo '<option ' . $selected . ' value="all">' . __('All', "woocommerce-inventory-manager") . '</option>';

    echo '</select>';
    echo '<label for="hd_warehouses_warehouse_email"> ' . __('Send an email to a warehouse with order info once stock has been deducted.', "woocommerce-inventory-manager") . '</label>';
  }

  public function hd_warehouses_online_warehouse()
  {
      $repository = new IM_Warehouse_Repository();
      $warehouses = $repository->get_all();

      echo '<select id="hd_warehouses_online_warehouse" name="hd_warehouses_online_warehouse">';

      echo '<option value="0">' . __('None.', "woocommerce-inventory-manager") . '</option>';

      foreach ($warehouses as $warehouse) {
          $selected = (get_option('hd_warehouses_online_warehouse') == $warehouse->id) ? 'selected="selected"' : '';
         echo '<option ' . $selected . ' value="'.$warehouse->id.'">'.$warehouse->name.'</option>';
      }

      echo '</select>';

  }

public function hd_warehouses_frontend_stock_selection() {

  $selected = 'selected="selected"';

  	  $stock_selection_method = get_option('hd_warehouses_frontend_stock_selection');
  	
  	  $stock_selection_options = array('0' => array('value' => '0', 'label' => 'Default'), 'per_country' => array('value' => 'per_country', 'label' => __('Warehouse per country (if available).', 'woocommerce-inventorymanager')), 'manual' => array('value' => 'manual', 'label' => __('Manual warehouse selection.', 'woocommerce-inventorymanager')));
  	  
  	  $stock_selection_options = apply_filters('hd_warehouse_frontend_stock_selection_filter', $stock_selection_options);
  	  
      echo '<select id="hd_warehouses_frontend_stock_selection" name="hd_warehouses_frontend_stock_selection">';
      foreach($stock_selection_options as $k => $stock_option){
	  	echo '<option ' . ( ($stock_selection_method == $k) ? $selected : "") . ' value="' . $stock_option['value'] . '">' . $stock_option['label'] . '</option>';
	  }
	  echo '</select>';
}

public function hd_warehouses_frontend_stock_selection_manual_negative_stock() {
  $checked = (get_option('hd_warehouses_frontend_stock_selection_manual_negative_stock') == 1) ? 'checked="checked"' : '';
      echo '<input type="hidden" name="hd_warehouses_frontend_stock_selection_manual_negative_stock" value="0" />';
      echo '<input type="checkbox" name="hd_warehouses_frontend_stock_selection_manual_negative_stock" id="hd_warehouses_frontend_stock_selection_manual_negative_stock" value="1" '.$checked.' autocomplete="off" />';
      echo '<label for="hd_warehouses_frontend_stock_selection_manual_negative_stock"> ' . __('This is exclusive of manual frontend warehouse selection.', "woocommerce-inventory-manager") . '</label>';
}

public function hd_warehouses_low_stock_threshold_per_warehouse()
{
  echo '<input type="number" name="hd_warehouses_low_stock_threshold_per_warehouse" id="hd_warehouses_low_stock_threshold_per_warehouse" value="' . get_option('hd_warehouses_low_stock_threshold_per_warehouse') . '" autocomplete="off" min="0"/>';
      echo '<label for="hd_warehouses_low_stock_threshold_per_warehouse"> ' . __("Set 0 if you don't want to receive any stock alerts.", "woocommerce-inventory-manager") . '</label>';
}

public function hd_warehouses_stock_log_per_page()
{
  echo '<input type="number" name="hd_warehouses_stock_log_per_page" id="hd_warehouses_stock_log_per_page" value="' . get_option('hd_warehouses_stock_log_per_page') . '" autocomplete="off" min="10"/>';
}

public function hd_warehouses_country_nostock()
{
    echo '<textarea type="text" name="hd_warehouses_country_nostock" id="hd_warehouses_country_nostock" rows="10" cols="100">' . get_option('hd_warehouses_country_nostock') . '</textarea>';
    echo '<br/><label for="hd_warehouses_country_nostock">' . __("Error for country warehouse when stock is less than 0 .", "woocommerce-inventory-manager") . '</label>';
}

public function hd_warehouses_country_notenough_stock()
{
    echo '<textarea type="text" name="hd_warehouses_country_notenough_stock" id="hd_warehouses_country_notenough_stock" rows="10" cols="100">' . get_option('hd_warehouses_country_notenough_stock') . '</textarea>';
    echo '<br/><label for="hd_warehouses_country_notenough_stock">' . __("Error for country warehouse when stock is less than required .", "woocommerce-inventory-manager") . '</label>';
}

public function hd_warehouses_online_nostock()
{
    echo '<textarea type="text" name="hd_warehouses_online_nostock" id="hd_warehouses_online_nostock" rows="10" cols="100">' . get_option('hd_warehouses_online_nostock') . '</textarea>';
    echo '<br/><label for="hd_warehouses_online_nostock">' . __("Error for country warehouse when stock is less than 0 .", "woocommerce-inventory-manager") . '</label>';
}

public function hd_warehouses_online_notenough_stock()
{
    echo '<textarea type="text" name="hd_warehouses_online_notenough_stock" id="hd_warehouses_online_notenough_stock" rows="10" cols="100">' . get_option('hd_warehouses_online_notenough_stock') . '</textarea>';
    echo '<br/><label for="hd_warehouses_online_notenough_stock">' . __("Error for country warehouse when stock is less than required .", "woocommerce-inventory-manager") . '</label>';
}

public function hd_warehouses_manual_nostock()
{
    echo '<textarea type="text" name="hd_warehouses_manual_nostock" id="hd_warehouses_manual_nostock" rows="10" cols="100">' . get_option('hd_warehouses_manual_nostock') . '</textarea>';
    echo '<br/><label for="hd_warehouses_manual_nostock">' . __("Error for manual warehouse when stock is less than 0 .", "woocommerce-inventory-manager") . '</label>';
}

public function hd_warehouses_manual_notenough_stock()
{
    echo '<textarea type="text" name="hd_warehouses_manual_notenough_stock" id="hd_warehouses_manual_notenough_stock" rows="10" cols="100">' . get_option('hd_warehouses_manual_notenough_stock') . '</textarea>';
    echo '<br/><label for="hd_warehouses_manual_notenough_stock">' . __("Error for manual warehouse when stock is less than required .", "woocommerce-inventory-manager") . '</label>';
}

public function hd_warehouses_frontend_manual_user_role(){
	$roles = get_editable_roles();
  ?>
  <select id="hd_warehouses_frontend_manual_user_role" name="hd_warehouses_frontend_manual_user_role">
	  <option value=""
	  	<?php $this->if_selected_roles(''); ?>>All Roles</option>
        <?php foreach ($roles as $key => $value): ?>
          <option value="<?php echo $key ?>"
    <?php $this->if_selected_roles($key); ?>> <?php echo $value['name']; ?></option>
        <?php endforeach; ?>
      </select>
<?php
	echo '<br/><label for="hd_warehouses_frontend_manual_user_role">' . __("Used for manual warehouse selection only.", "woocommerce-inventory-manager") . '</label>';
}

  public function stock_reduction_state()
  {
      $statuses = wc_get_order_statuses();
      $statuses = array_slice($statuses, 0, 4);
      ?>
  <select id="stock_reduction_state" name="stock_reduction_state">
        <?php foreach ($statuses as $key => $value): ?>
          <option value="<?php echo $key ?>"
    <?php $this->if_selected($key); ?>> <?php echo $value; ?></option>
        <?php endforeach; ?>
      </select>
<?php
  }

  public function if_selected($value)
  {
      $selected = get_option('stock_reduction_state');
      if ($selected == $value)
          echo 'selected="selected"';
  }

  public function if_selected_roles($value)
  {
      $selected = get_option('hd_warehouses_frontend_manual_user_role');
      if ($selected == $value)
          echo 'selected="selected"';
  }

  //Settings validation function
  function plugin_main_settings_validate($arr_input) {
    return $arr_input;
  }

}
