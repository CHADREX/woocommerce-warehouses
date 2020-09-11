<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit();
}

class IM_Outlet_Repository extends IM_Database
{

    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . "wc_poin_of_sale_outlets";
        $this->registerName = $wpdb->prefix . "wc_poin_of_sale_registers";
    }

    public function get_outlet_by_register($register_id)
    {
        global $wpdb;
        $result = $wpdb->get_row( "SELECT * FROM $this->registerName AS register JOIN {$wpdb->prefix}inventory_manager_warehouse_outlet as outlet ON register.outlet = outlet.outlet_id WHERE register.ID = $register_id", OBJECT );
        return $result;
    }
}
