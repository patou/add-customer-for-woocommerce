<?php

/**
 * Plugin Name: Add Customer for WooCommerce
 * Description: Allows you to add a customer when a new order is created at the orders page.
 * Plugin URI: https://dev.dans-art.ch/blog/wordpress/add-customer-for-woocommerce/
 * Contributors: dansart
 * Contributors URL: http://dev.dans-art.ch
 * Tags: woocommerce, customer, tools, helper
 * Version: 1.3
 * Stable tag: 1.3
 * 
 * Requires at least: 5.5.3
 * Tested up to: 5.9
 * 
 * WC requires at least: 4.7.0
 * WC tested up to: 6.1.1
 * 
 * Requires PHP: 7.4
 * 
 * Domain Path: /languages
 * Text Domain: wac
 * 
 * Author: Dan's Art
 * Author URI: https://dev.dans-art.ch
 * Donate link: https://paypal.me/dansart13

 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Load the classes
 */
require_once('include/tools/helper.php');
require_once('include/classes/wac.php');
require_once('include/classes/wac-admin.php');
require_once('include/classes/wac-backend.php');

//Make sure all other Plugins are loaded, before running this.
add_action('plugins_loaded', function () {
    $wac = new woo_add_customer();
    $wac->wac_admin_init();
});