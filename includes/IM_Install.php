<?php
global $im_db_version;
$im_db_version = '1.5.4';

function im_install()
{
    global $wpdb;
    global $im_db_version;
    $installed_ver = get_option("im_db_version");

	// catch exception if needed..
	try {
	    if ($installed_ver != $im_db_version) {

			require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

	        $charset_collate = $wpdb->get_charset_collate();

			/*
			 * ====================================================================
			 * ===== EXPORT OLD CONTENT TO FILES IN CASE SOMETHING GOES WRONG =====
			 * ====================================================================
			 */

	        if (!empty($installed_ver))
	        {
	            $pathWarehouses = plugin_dir_path(IM_PLUGIN_FILE) . 'backups/' . IM_PLUGIN_DATABASE_TABLE . '.csv';
	            $pathProductWarehouse = plugin_dir_path(IM_PLUGIN_FILE) . 'backups/' . IM_PLUGIN_DATABASE_TABLE_PRODUCT_WAREHOUSE . '.csv';
	            $pathStockLog = plugin_dir_path(IM_PLUGIN_FILE) . 'backups/' . IM_PLUGIN_DATABASE_TABLE_STOCK_LOG . '.csv';
      				$pathWarehouseCountry = plugin_dir_path(IM_PLUGIN_FILE) . 'backups/' . IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_COUNTRY . '.csv';
              $pathWarehouseOutlet = plugin_dir_path(IM_PLUGIN_FILE) . 'backups/' . IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_OUTLET . '.csv';

				$val = $wpdb->get_col("SELECT IF( EXISTS(SELECT * FROM " . IM_PLUGIN_DATABASE_TABLE . "), 1, 0)", 0, ARRAY_A);
				if($val[0]) {
		            $results = $wpdb->get_results('SELECT * FROM ' . IM_PLUGIN_DATABASE_TABLE, ARRAY_A);
					$columns = $wpdb->get_col_info();
					if(!empty($results)) {
		            	$fp = fopen($pathWarehouses, 'w');
						if ( !$fp ) {
							throw new Exception(__('File open failed. Make sure your backups folder inside WooCommerce Warehouses has the right permissions (must be readable and writable).', 'woocommerce-inventorymanager'));
						} else {
							// columns
							fputcsv($fp, array_values($columns));
			                foreach ($results as $row) {
			                    fputcsv($fp, array_values($row));
			                }
			                fclose($fp);
			            }
					}

				}

				$val = $wpdb->get_col("SELECT IF( EXISTS(SELECT * FROM " . IM_PLUGIN_DATABASE_TABLE_PRODUCT_WAREHOUSE . "), 1, 0)", 0, ARRAY_A);
				if($val[0]) {

		            $results = $wpdb->get_results('SELECT * FROM ' . IM_PLUGIN_DATABASE_TABLE_PRODUCT_WAREHOUSE, ARRAY_A);
					$columns = $wpdb->get_col_info();
					if(!empty($results)) {
		            	$fp = fopen($pathProductWarehouse, 'w');
						if ( !$fp ) {
							throw new Exception(__('File open failed. Make sure your backups folder inside WooCommerce Warehouses has the right permissions (must be readable and writable).', 'woocommerce-inventorymanager'));
						} else {
							// columns
							fputcsv($fp, array_values($columns));
			                foreach ($results as $row) {
			                    fputcsv($fp, array_values($row));
			                }
			                fclose($fp);
			            }
					}

				}

				$val = $wpdb->get_col("SELECT IF( EXISTS(SELECT * FROM " . IM_PLUGIN_DATABASE_TABLE_STOCK_LOG . "), 1, 0)", 0, ARRAY_A);
				if($val[0]) {

		            $results = $wpdb->get_results('SELECT * FROM ' . IM_PLUGIN_DATABASE_TABLE_STOCK_LOG, ARRAY_A);
					$columns = $wpdb->get_col_info();
					if(!empty($results)) {
			            $fp = fopen($pathStockLog, 'w');
						if ( !$fp ) {
							throw new Exception(__('File open failed. Make sure your backups folder inside WooCommerce Warehouses has the right permissions (must be readable and writable).', 'woocommerce-inventorymanager'));
						} else {
							// columns
							fputcsv($fp, array_values($columns));
			                foreach ($results as $row) {
			                    fputcsv($fp, array_values($row));
			                }
			                fclose($fp);
			            }
					}

				}

				$val = $wpdb->get_col("SELECT IF( EXISTS(SELECT * FROM " . IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_COUNTRY . "), 1, 0)", 0, ARRAY_A);
				if($val[0]) {
					$results = $wpdb->get_results('SELECT * FROM ' . IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_COUNTRY, ARRAY_A);
					$columns = $wpdb->get_col_info();
					if(!empty($results)) {
						$fp = fopen($pathWarehouseCountry, 'w');
						if ( !$fp ) {
							throw new Exception(__('File open failed. Make sure your backups folder inside WooCommerce Warehouses has the right permissions (must be readable and writable).', 'woocommerce-inventorymanager'));
						} else {
							// columns
							fputcsv($fp, array_values($columns));
							foreach ($results as $row) {
								fputcsv($fp, array_values($row));
							}
							fclose($fp);
						}
					}

				}

        $val = $wpdb->get_col("SELECT IF( EXISTS(SELECT * FROM " . IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_OUTLET . "), 1, 0)", 0, ARRAY_A);
				if($val && $val[0]) {
					$results = $wpdb->get_results('SELECT * FROM ' . IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_OUTLET, ARRAY_A);
					$columns = $wpdb->get_col_info();
					if(!empty($results)) {
						$fp = fopen($pathWarehouseOutlet, 'w');
						if ( !$fp ) {
							throw new Exception(__('File open failed. Make sure your backups folder inside WooCommerce Warehouses has the right permissions (must be readable and writable).', 'woocommerce-inventorymanager'));
						} else {
							// columns
							fputcsv($fp, array_values($columns));
							foreach ($results as $row) {
								fputcsv($fp, array_values($row));
							}
							fclose($fp);
						}
					}

				}
	        }

			/*
			 * ==============================
			 * ===== END OF EXPORTATION =====
			 * ==============================
			 */

			/*
			 * =======================
			 * ===== DROP tables =====
			 * =======================
			 */
	        $wpdb->query("DROP TABLE IF EXISTS " . IM_PLUGIN_DATABASE_TABLE_STOCK_LOG);
	        $wpdb->query("DROP TABLE IF EXISTS " . IM_PLUGIN_DATABASE_TABLE_PRODUCT_WAREHOUSE);
	        $wpdb->query("DROP TABLE IF EXISTS " . IM_PLUGIN_DATABASE_TABLE);
    			$wpdb->query("DROP TABLE IF EXISTS " . IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_COUNTRY);
          $wpdb->query("DROP TABLE IF EXISTS " . IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_OUTLET);
			/*
			 * ==============================
			 * ===== END OF DROP tables =====
			 * ==============================
			 */

			/*
			 * ==================================
			 * ===== CREATE WAREHOUSE table =====
			 * ==================================
			 */

	        $table_name = IM_PLUGIN_DATABASE_TABLE;

	        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            name tinytext NULL,
            address text NULL,
            postcode text NULL,
            city text NULL,
            country text NULL,
            vat text NULL,
            email text NULL,
            slug text NOT NULL,
            pickup tinyint NULL,
            exclusive tinyint NULL,
            prevent tinyint NULL,
            priority int(255) NULL,
            PRIMARY KEY id (id)
            ) $charset_collate;";

	        dbDelta($sql);

			/*
			 * =========================================
			 * ===== END OF CREATE WAREHOUSE table =====
			 * =========================================
			 */

			/*
			 * ==========================================
			 * ===== CREATE product-warehouse table =====
			 * ==========================================
			 */
	        $table_name = IM_PLUGIN_DATABASE_TABLE_PRODUCT_WAREHOUSE;

	        $charset_collate = $wpdb->get_charset_collate();

	        $sql = "CREATE TABLE $table_name (
id mediumint(9) NOT NULL,
product_id mediumint(9),
warehouse_id mediumint(9),
stock int(255) NULL,
priority int(255) NULL
) $charset_collate;";

	        dbDelta($sql);

	        $sql = "ALTER TABLE " . $table_name . " ADD CONSTRAINT `id` PRIMARY KEY id (id, product_id, warehouse_id)";
	        $wpdb->query($sql);

			$sql = "ALTER TABLE " . $table_name . " CHANGE `id` `id` MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT";
			$wpdb->query($sql);

			$sql = "ALTER TABLE " . $table_name . " ADD CONSTRAINT `product_warehouse_unique` UNIQUE KEY `product_warehouse_unique` (`product_id`, `warehouse_id`)";
	        $wpdb->query($sql);

	        $sql = "ALTER TABLE " . $table_name . " ADD CONSTRAINT `product_warehouse_warehouse` FOREIGN KEY (warehouse_id) REFERENCES " . IM_PLUGIN_DATABASE_TABLE . "(id) ON DELETE CASCADE";
	        $wpdb->query($sql);


			/*
			 * =================================================
			 * ===== END OF CREATE product-warehouse table =====
			 * =================================================
			 */

			/*
			 * ==================================
			 * ===== CREATE stock log table =====
			 * ==================================
			 */
	        $table_name = IM_PLUGIN_DATABASE_TABLE_STOCK_LOG;

	        $charset_collate = $wpdb->get_charset_collate();

	        $sql = "CREATE TABLE $table_name (
id mediumint(9) NOT NULL AUTO_INCREMENT,
product_id mediumint(9),
warehouse_id mediumint(9),
stock int(255) NULL,
reason varchar(255) NULL,
timestamp timestamp,
PRIMARY KEY (id)
) $charset_collate;";

	        dbDelta($sql);

	        $sql = "ALTER TABLE " . $table_name . " ADD CONSTRAINT `stock_log_warehouse` FOREIGN KEY (warehouse_id) REFERENCES " . IM_PLUGIN_DATABASE_TABLE . "(id) ON DELETE CASCADE";
	        $wpdb->query($sql);

			/*
			 * =========================================
			 * ===== END OF CREATE stock log table =====
			 * =========================================
			 */

			/*
			 * ==========================================
			 * ===== CREATE warehouse country table =====
			 * ==========================================
			 */

			$table_name = IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_COUNTRY;

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        warehouse_id mediumint(9),
        country varchar(255) NULL,
        PRIMARY KEY (id)
        ) $charset_collate;";

			dbDelta($sql);

			/*
			 * =================================================
			 * ===== END OF CREATE warehouse country table =====
			 * =================================================
			 */

       /*
 			 * =========================================
 			 * ===== CREATE warehouse outlet table =====
 			 * =========================================
 			 */

 			$table_name = IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_OUTLET;

 			$charset_collate = $wpdb->get_charset_collate();

 			$sql = "CREATE TABLE $table_name (
       id mediumint(9) NOT NULL AUTO_INCREMENT,
       warehouse_id mediumint(9),
       outlet_id mediumint(9),
       PRIMARY KEY (id)
       ) $charset_collate;";

 			dbDelta($sql);

 			/*
 			 * ================================================
 			 * ===== END OF CREATE warehouse outlet table =====
 			 * ================================================
 			 */

			/*
			 * =====================================
			 * ===== IMPORT THE EXPORTED FILES =====
			 * =====================================
			 */

	        if (!empty($installed_ver))
	        {
	            if (file_exists($pathWarehouses) && ($handle = fopen($pathWarehouses, "r")) !== FALSE) {
	                $table = IM_PLUGIN_DATABASE_TABLE;
					$count = 0;
					$columns = "";
	                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						// CASE TO HANDLE THE COLUMNS
						$num = count($data);
						if($count == 0) {
							$columns = "(";
							for ($c=0; $c < $num; $c++) {
		                        $columns .= $data[$c];
		                        if($c+1 < $num) {
		                            $columns .= ',';
		                        }
		                    }
							$columns .= ")";
							$count++;
							continue;
						}

	                    $values = "INSERT INTO $table $columns VALUES(";
	                    for ($c=0; $c < $num; $c++) {
	                        $values .= "'". $data[$c] . "'";
	                        if($c+1 < $num) {
	                            $values .= ',';
	                        }
	                    }
	                    $values .= ");";

	                    // INSERT THE ROW
	                    $wpdb->query($values);
						$count++;
	                }
	                fclose($handle);
	            }

	            if (file_exists($pathProductWarehouse) && ($handle = fopen($pathProductWarehouse, "r")) !== FALSE) {
	                $table = IM_PLUGIN_DATABASE_TABLE_PRODUCT_WAREHOUSE;
					$count = 0;
					$columns = "";
	                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						// CASE TO HANDLE THE COLUMNS
						$num = count($data);
						if($count == 0) {
							$columns = "(";
							for ($c=0; $c < $num; $c++) {
		                        $columns .= $data[$c];
		                        if($c+1 < $num) {
		                            $columns .= ',';
		                        }
		                    }
							$columns .= ")";
							$count++;
							continue;
						}

	                    $values = "INSERT INTO $table $columns VALUES(";

	                    for ($c=0; $c < $num; $c++) {
	                        $values .= "'". $data[$c] . "'";
	                        if($c+1 < $num) {
	                            $values .= ',';
	                        }
	                    }

	                    $values .= ");";

	                    // INSERT THE ROW
	                    $wpdb->query($values);
						$count++;
	                }
	                fclose($handle);
	            }

	            if (file_exists($pathStockLog) && ($handle = fopen($pathStockLog, "r")) !== FALSE) {
	                $table = IM_PLUGIN_DATABASE_TABLE_STOCK_LOG;
					$count = 0;
					$columns = "";
	                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						// CASE TO HANDLE THE COLUMNS
						$num = count($data);
						if($count == 0) {
							$columns = "(";
							for ($c=0; $c < $num; $c++) {
								$columns .= $data[$c];
								if($c+1 < $num) {
									$columns .= ',';
								}
							}
							$columns .= ")";
							$count++;
							continue;
						}

	                    $values = "INSERT INTO $table $columns VALUES (";

	                    for ($c=0; $c < $num; $c++) {
	                        $values .= "'". $data[$c] . "'";
	                        if($c+1 < $num) {
	                            $values .= ',';
	                        }
	                    }

	                    $values .= ");";

	                    // INSERT THE ROW
	                    $wpdb->query($values);
						$count++;
	                }
	                fclose($handle);
	            }

				if (file_exists($pathWarehouseCountry) && ($handle = fopen($pathWarehouseCountry, "r")) !== FALSE) {
	                $table = IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_COUNTRY;
					$count = 0;
					$columns = "";
	                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						// CASE TO HANDLE THE COLUMNS
						$num = count($data);
						if($count == 0) {
							$columns = "(";
							for ($c=0; $c < $num; $c++) {
								$columns .= $data[$c];
								if($c+1 < $num) {
									$columns .= ',';
								}
							}
							$columns .= ")";
							$count++;
							continue;
						}

	                    $values = "INSERT INTO $table $columns VALUES (";

	                    for ($c=0; $c < $num; $c++) {
	                        $values .= "'". $data[$c] . "'";
	                        if($c+1 < $num) {
	                            $values .= ',';
	                        }
	                    }

	                    $values .= ");";

	                    // INSERT THE ROW
	                    $wpdb->query($values);
						$count++;
	                }
	                fclose($handle);
	            }

          if (file_exists($pathWarehouseOutlet) && ($handle = fopen($pathWarehouseOutlet, "r")) !== FALSE) {
  	                $table = IM_PLUGIN_DATABASE_TABLE_WAREHOUSE_OUTLET;
  					$count = 0;
  					$columns = "";
  	                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
  						// CASE TO HANDLE THE COLUMNS
  						$num = count($data);
  						if($count == 0) {
  							$columns = "(";
  							for ($c=0; $c < $num; $c++) {
  								$columns .= $data[$c];
  								if($c+1 < $num) {
  									$columns .= ',';
  								}
  							}
  							$columns .= ")";
  							$count++;
  							continue;
  						}

  	                    $values = "INSERT INTO $table $columns VALUES (";

  	                    for ($c=0; $c < $num; $c++) {
  	                        $values .= "'". $data[$c] . "'";
  	                        if($c+1 < $num) {
  	                            $values .= ',';
  	                        }
  	                    }

  	                    $values .= ");";

  	                    // INSERT THE ROW
  	                    $wpdb->query($values);
  						$count++;
  	                }
  	                fclose($handle);
  	            }
	        }

			/*
			 * ============================================
			 * ===== END OF IMPORT THE EXPORTED FILES =====
			 * ============================================
			 */

	        if ($installed_ver != null) {
	            update_option("im_db_version", $im_db_version);
	        } else {
	            add_option("im_db_version", $im_db_version);
	        }

	        $stock_reduction_state = get_option("stock_reduction_state");

	        if (!$stock_reduction_state) {
	            add_option("stock_reduction_state", "wc-pending");
	        }

	        $csv_export_delimiter = get_option("hd_warehouses_csv_export_delimiter");

	        if (!$csv_export_delimiter) {
	            add_option("hd_warehouses_csv_export_delimiter", ";");
	        }

	        $online_warehouse_restriction = get_option("hd_warehouses_online_warehouse_restriction");

	        if (!$online_warehouse_restriction) {
	            add_option("hd_warehouses_online_warehouse_restriction", "0");
	        }

	        $online_warehouse = get_option("hd_warehouses_online_warehouse");

	        if (!$online_warehouse) {
	            add_option("hd_warehouses_online_warehouse", "0");
	        }

			$frontend_stock_selection = get_option("hd_warehouses_frontend_stock_selection");

			if (!$frontend_stock_selection) {
				add_option("hd_warehouses_frontend_stock_selection", "0");
			}

			$frontend_stock_selection_manual_show_stock = get_option("hd_warehouses_frontend_stock_selection_manual_show_stock");

			if (!$frontend_stock_selection_manual_show_stock != null) {
				add_option("hd_warehouses_frontend_stock_selection_manual_show_stock", "0");
			}

			$frontend_stock_selection_manual_negative_stock = get_option("hd_warehouses_frontend_stock_selection_manual_negative_stock");

			if (!$frontend_stock_selection_manual_negative_stock) {
				add_option("hd_warehouses_frontend_stock_selection_manual_negative_stock", "0");
			}

			$low_stock_threshold_per_warehouse = get_option("hd_warehouses_low_stock_threshold_per_warehouse");

			if (!$low_stock_threshold_per_warehouse) {
				add_option("hd_warehouses_low_stock_threshold_per_warehouse", "0");
			}

      $stock_logs_per_page = get_option("hd_warehouses_stock_log_per_page");

      if (!$stock_logs_per_page) {
				add_option("hd_warehouses_stock_log_per_page", "25");
			}

      $email_warehouses = get_option("hd_warehouses_warehouse_email");

      if (!$email_warehouses) {
				add_option("hd_warehouses_warehouse_email", "0");
			}

      $stock_changes_order_notes = get_option("hd_warehouses_order_stock_notes");

      if (!$stock_changes_order_notes) {
				add_option("hd_warehouses_order_stock_notes", "0");
			}

      //Default messages
      $hd_warehouses_country_nostock = get_option("hd_warehouses_country_nostock");

      if (!$hd_warehouses_country_nostock) {
				add_option("hd_warehouses_country_nostock", 'Sorry, "%s" is not in stock in your country. Please edit your cart and try again. We apologise for any inconvenience caused.');
			}

      $hd_warehouses_country_notenough_stock = get_option("hd_warehouses_country_notenough_stock");

      if (!$hd_warehouses_country_notenough_stock) {
				add_option("hd_warehouses_country_notenough_stock", 'Sorry, we do not have enough "%s" in stock to fulfill your order (%s in stock). Please edit your cart and try again. We apologise for any inconvenience caused.');
			}

      $hd_warehouses_online_nostock = get_option("hd_warehouses_online_nostock");

      if (!$hd_warehouses_online_nostock) {
				add_option("hd_warehouses_online_nostock", 'Sorry, "%s" is not in stock. Please edit your cart and try again. We apologise for any inconvenience caused.');
			}

      $hd_warehouses_online_notenough_stock = get_option("hd_warehouses_online_notenough_stock");

      if (!$hd_warehouses_online_notenough_stock) {
				add_option("hd_warehouses_online_notenough_stock", 'Sorry, we do not have enough "%s" in stock to fulfill your order (%s in stock). Please edit your cart and try again. We apologise for any inconvenience caused.');
			}
			
	  $hd_warehouses_manual_nostock = get_option("hd_warehouses_manual_nostock");

      if (!$$hd_warehouses_manual_nostock) {
				add_option("hd_warehouses_manual_nostock", 'Sorry, "%s" is not in stock for the selected warehouse. Please edit your cart and try again. We apologise for any inconvenience caused.');
			}

      $hd_warehouses_manual_notenough_stock = get_option("hd_warehouses_manual_notenough_stock");

      if (!$hd_warehouses_manual_notenough_stock) {
				add_option("hd_warehouses_manual_notenough_stock", 'Sorry, we do not have enough "%s" in stock to fulfill your order (%s in stock) in the selected warehouse. Please edit your cart and try again. We apologise for any inconvenience caused.');
			}

      global $wp_roles;

      if (!isset($wp_roles)) {
          $wp_roles = new WP_Roles();
      }

      if (isset($wp_roles->roles['administrator'])) {

        $administrator = $wp_roles->role_objects['administrator'];
        $ware_caps = get_warehouses_caps();
        foreach(array_keys($ware_caps) as $cap) {
            if (!$administrator->has_cap($cap)) {
                $administrator->add_cap($cap, true);
            }
        }

      }

	    }
	} catch(Exception $e) {
		wp_die($e->getMessage(), "WooCommerce Warehouses - Error in the upgrade/installation process.");
	}
}

/**
 * This function can be used to insert data into the database newly created.
 */
function im_install_data(){
    global $wpdb;

    $welcome_name = 'Dear user';
    $welcome_text = 'Congratulations, you just completed the installation!';

    $table_name = $wpdb->prefix . 'inventory_manager_warehouses';
}

function get_warehouses_caps() {

    $ware_caps = array(
        'hellodev_im_manage_warehouses' => 1,
        'hellodev_im_stock_log' => 1,
        'hellodev_im_product_stock' => 1
    );

    return $ware_caps;
}

register_activation_hook(__FILE__, 'im_install');
register_activation_hook(__FILE__, 'im_install_data');

function im_update_db_check()
{
    global $im_db_version;
    if (get_site_option('im_db_version') != $im_db_version) {
        im_install();
    }
}

add_action('plugins_loaded', 'im_update_db_check');
