<?php

function im_remove()
{
    $option_name = 'im_db_version';
    
    delete_option($option_name);
    
    // For site options in multisite
    delete_site_option($option_name);
    
    global $wpdb;
    
    $wpdb->query("DROP TABLE if exists " . IM_PLUGIN_DATABASE_TABLE_STOCK_LOG);
    $wpdb->query("DROP TABLE if exists " . IM_PLUGIN_DATABASE_TABLE_PRODUCT_WAREHOUSE);
    $wpdb->query("DROP TABLE if exists " . IM_PLUGIN_DATABASE_TABLE);
}

?>
