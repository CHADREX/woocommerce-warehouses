<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}
?>
<style>
.copyright {
	font-size: 14px;
	padding-top: 50px;
}

.copyright img {
	vertical-align: middle;
	height: 28px;
}
</style>
<div class="wrap">
	<h2><?php _e('Woocommerce Warehouses Settings'); ?></h2>
	<form method="post" action="options.php">
            <?php settings_fields('hellodev-inventory-manager-plugin-settings'); ?>
            <?php do_settings_sections('hellodev-inventory-manager-plugin-settings'); ?>
            <?php submit_button(); ?>
          </form>
</div>
