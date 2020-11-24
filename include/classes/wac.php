<?php

/**
 * Plugin Name: WooCommerce Add Customer
 * Class description: Main Class. Includes the plugins functionalities to front- and backend. 
 * Author: dans-art
 * Author URI: http://dev.dans-art.ch
 *
 */
class woo_add_customer extends woo_add_customer_helper
{



    public function __construct()
    {
    }
    /**
     * Loads the admin class
     *
     * @return void
     */
    public function wac_admin_init()
    {
        $adminclass = new woo_add_customer_admin;
    }
}
