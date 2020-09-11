=== WooCommerce Warehouses ===
Contributors: hellodev
Tags: ecommerce, e-commerce, woocommerce, warehouses, warehouse, stocks, inventory, backend
Requires at least: 4.0
Tested up to: 4.3.1
Author: hellodev.us <https://www.hellodev.us/>

== Description ==

The WooCommerce Warehouses plugin has the ability to manage stock that are stored in different locations, like warehouses. By creating multiple warehouses you can manage the inventory of your product automatically, also by establishing stock usage priorities.
This plugin works alongside your existing WooCommerce store by adding a new option in the product configuration screen, inside the existing Inventory option, extending its features.
You don't need to subscribe to any other 3rd party services, it is a standalone solution, to work exclusively alongside WooCommerce.

This plugin adds the following features to WooCommerce:
- Multiple warehouses.
- Stock management.
- Stock movement log.
- Warehouse to warehouse stock transfer.
- Stock usage priorities by warehouse (global and per item priorities).
- Backend orders with warehouse stock usage.
- Handles both products and product variations.

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of this plugin, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

== Manual installation ==

The manual installation method involves downloading our plugin and uploading it to your webserver via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

== Changelog ==

= 1.6.2.2 =

# Solved stock report and export memory leak.
# Solved add product bug.
# Solved stock report memory leak.
# Fixed variable products links in stock report and log.

= 1.6.2 =

# Added integration with WooCommerce 3.x.

= 1.6.1 =
# Small bug fixes.

= 1.6 =

# Added stock transfer status, used for warehouse to warehouse transfers. Solves stock report bug.
# Added stock import feature.
# Added stock export feature.
# Added pickup location map feature using google static maps API.
# Added user manual warehouse selection at product level (filtered by user role).
# Stock can now be shown at product details page.
# Fixed multiple country warehouses bug.
# Small performance tweaks and bug fixes.

= 1.5.4 =

# Added fix to POS integration when using online warehouse.
# Partial refunds bug fixed.
# Stock report paging fixed.
# Other small tweaks.

= 1.5.3 =

# Added fix for Online Warehouse Restrictions.
# Small tweaks and bug fixes.

= 1.5.2 =

# Added fix for Product Bundles bug.
# Added fix to view product javascript bugs.
# Added fix for local pickups on Woocommerce 2.6.x.
# Small requested tweaks.

= 1.5.1 =

# Added fix for new versions of POS.

= 1.5 =
# NEW Added possibility to prevent sales from country warehouses on lack of stock.
# NEW Added customizable message for above feature.
# NEW Capability that allows user to manage product stock.
# NEW Capabilities now automatically created.
# NEW Added filter to stock log.
# Some bug-fixes and tweaks.

= 1.4 =
# NEW Integration with ActualityExtentions WooCommerce Point of Sale (POS).

= 1.3.4 =
# Variations stock could not be changed via order actions. SOLVED.
# Some small bug-fixes.

= 1.3.3 =
* NEW feature that allows you to set warehouse exclusivity for a group of countries. This way only buyers from those countries use that warehouse stock.
* Changed product stock management UI.
* Bugfixes on Bundled and Variable products.

= 1.3.2 =
* NEW feature that allows you to have order notes when stock has been reduced on an order.
* Number of records per page (stock log/report) is now editable on plugin settings.
* Small performance and UI tweaks.
* Some bug-fixes.

= 1.3.1 =
* Removed manual warehouse selection. Instead you can allow warehouses to serve as pickup locations on front-end orders.
* Orders with a chosen pickup location use WooCommerce local_pickup shipping method.
* Changed product name for variable products on stock report.
* Some bug-fixes.

= 1.3 =
* NEW feature that allows your customers to select a warehouse manually in the frontend with/out stock and the possibility of showing the stock or not.
* NEW feature that allows you to assign a country to a warehouse and during checkout any orders with a shipping address in that country will be assigned to that specific warehouse.
* NEW feature that allows you to have a low stock/negative stock threshold warning email the same way WooCommerce does.
* NEW feature that sends order emails to warehouses. 
* Improved warehouse stock UI.
* Adds support to increase/reduce item stock via order items.
* Adds ability to transfer stock when deleting a warehouse.
* Improved Settings UI.
* Some bug-fixes.

= 1.2 =
* Added online warehouse definition option
* Replaces the WooCommerce Product Factory with a custom product factory allowing better integration with other plugins.
* Correction of bugs reported in the version 1.1.
* Few refactorings in the codebase.
* Database is now exported into backups folder for every update (please check the docs).

= 1.1 =
* PSR-4 implementation with Composer Autoloader.
* Big refactoring to improve maintainability.
* Fixed few reported issues.
* Fixed PHP warnings.

= 1.0 =
* First release
