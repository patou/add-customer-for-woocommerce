<?php

/**
 * Plugin Name: Add Customer for WooCommerce
 * Class description: Various helper methods.
 * Author: Dan's Art
 * Author URI: http://dev.dans-art.ch
 *
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class woo_add_customer_helper
{
    protected $version = '1.2';
    public $plugin_path = '';

    public function __construct()
    {
        $plugin_meta = get_plugin_data($this->plugin_path . 'add-customer-for-woocommerce.php');
        $this->version = (!empty($plugin_meta['Version'])) ? $plugin_meta['Version'] : "000";
    }

    /**
     * Creates a fake email with the domain of the site.
     * It is recommended to setup a catch-all email
     *
     * @param string $username - A username to start with or null
     * @return void
     */
    public function create_fake_email($username = null)
    {
        $urlparts = parse_url(home_url());
        $domain_name = ($urlparts['host'] !== 'localhost') ? $urlparts['host'] : 'local.host';
        $number = '';
        $name = (!empty($username)) ? sanitize_user($username) : wp_generate_password(5, false);
        while (get_user_by('email', $name . $number . '@' . $domain_name) !== false) {
            $number = (int)($number === '') ? 1 : $number++;
        }
        $email = $name . $number . '@' . $domain_name;
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Disables sending emails to the customer while creating a new user.
     *
     * @param integer $customer_id - Customer ID
     * @return bool true if emails got disabled, false if emails get send.
     */
    public function wac_disable_new_customer_mail($customer_id)
    {
        if (isset($_REQUEST['wac_add_customer']) and $_REQUEST['wac_add_customer'] === 'true') {
            add_filter('woocommerce_email_enabled_customer_new_account', function ($enabled, $user, $email) {
                return false; //$enabled = false;
            }, 10, 3);
            return true;
        }
    }
    /**
     * Loads the translation of the plugin.
     * Located at: plugins/add-customer-for-woocommerce/languages/
     *
     * @return void
     */
    public function wac_load_textdomain()
    {
        load_textdomain('wac', $this->wac_get_home_path() . 'wp-content/plugins/add-customer-for-woocommerce/languages/wac-' . determine_locale() . '.mo');
    }

    /**
     * Gets the Home Path. Workaround if WP is not completly loaded yet. 
     *
     * @return string  Full filesystem path to the root of the WordPress installation. (/var/www/htdocs/)
     */
    public function wac_get_home_path()
    {
        if (function_exists('get_home_path')) {
            return get_home_path();
        }
        $home    = set_url_scheme(get_option('home'), 'http');
        $siteurl = set_url_scheme(get_option('siteurl'), 'http');

        if (!empty($home) && 0 !== strcasecmp($home, $siteurl)) {
            $wp_path_rel_to_home = str_ireplace($home, '', $siteurl); /* $siteurl - $home */
            $pos                 = strripos(str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']), trailingslashit($wp_path_rel_to_home));
            $home_path           = substr($_SERVER['SCRIPT_FILENAME'], 0, $pos);
            $home_path           = trailingslashit($home_path);
        } else {
            $home_path = ABSPATH;
        }

        return str_replace('\\', '/', $home_path);
    }
    /**
     * Registers the custom settings Field
     *
     * @return void
     */
    public function wac_add_settings_section_init()
    {
        register_setting('general', 'wac_add_customer');
    }

    /**
     * Enqueue the styles of the plugin
     * Located at: plugins/add-customer-for-woocommerce/style/admin-style.css
     *
     * @return void
     */
    public function wac_enqueue_admin_style()
    {
        wp_enqueue_style('wac-admin-style', get_option('siteurl') . '/wp-content/plugins/add-customer-for-woocommerce/style/admin-style.css', array(), $this->version);
    }

    /**
     * Enqueue the scripts of the plugin
     * Located at: plugins/add-customer-for-woocommerce/include/js/wac-main-script.min.js
     *
     * @return void
     */
    public function wac_enqueue_admin_scripts()
    {
        wp_enqueue_script('wac-admin-script', get_option('siteurl') . '/wp-content/plugins/add-customer-for-woocommerce/include/js/wac-main-script.min.js', array('jquery'), $this->version);
    }

    /**
     * Logs Events to the Simple History Plugin and to the PHP Error Log on error.
     * Some Errors get displayed to the user
     * 
     * @param string $log_type - The log type. Allowed types: added_user, failed_to_add_user
     * @param string $order_id - The order id
     * @param mixed $args - Args for the vspringf() Function. String or Int 
     * 
     * @return void
     */
    public function log_event($log_type, $order_id, ...$args)
    {
        $additional_log = array();
        //$print_log = false;
        $type = 'null';

        switch ($log_type) {
            case 'existing_account':
                $message = htmlspecialchars(__('Email "%s" already exists. No new customer got created.', 'wac'));
                break;
            case 'added_user':
                $message = htmlspecialchars(__('Added customer "%s <%s>"', 'wac'));
                $type = 'success';
                break;
            case 'email_send':
                $message = __('Email send to new customer "%s"', 'wac');
                $type = 'success';
                break;
            case 'no_name':
                $message = __('Could not save customer. No Name provided.', 'wac');
                $type = 'null';
                break;
            case 'failed_to_send_user_mail':
                $message = __('Failed to send email notification to customer.', 'wac');
                $type = 'error';
                break;
            case 'failed_to_add_user':
                $message = __('New customer could not be added by Add Customer Plugin. Please contact the Plugin Author.', 'wac');
                $type = 'error';
                $additional_log = array('wc_create_new_customer' => $args[0], 'user' => $args[1], 'email' => $args[2]);
                error_log($message . " - " . json_encode($args)); //Prints the args with the error message from wc_create_new_customer to the error log
                $print_log = $message;
                break;
            default:
                $message = __('Log Type not found!', 'wac');
                break;
        }
        if (!empty($args)) {
            $msg_trans = vsprintf($message, $args);
        } else {
            $msg_trans = $message;
        }
        apply_filters('simple_history_log', "{$msg_trans} - by Add Customer", $additional_log);
        $this->wac_set_notice($msg_trans, $type, $order_id);
        return;
    }

    /**
     * Loads template to variable.
     * @param string $template_name - Name of the template without extension
     * @param string $subfolder - Name of the Subfolder(s). Base folder is Plugin_dir/templates/
     * @param string $template_args - Arguments to pass to the template
     * 
     * @return string Template content or eror Message
     */
    public function load_template_to_var(string $template_name = '', string $subfolder = '', ...$template_args)
    {
        $args = get_defined_vars();
        $path = $this->plugin_path . 'templates/' . $subfolder . $template_name . '.php';
        if (file_exists($path)) {
            ob_start();
            include($path);
            $output_string = ob_get_contents();
            ob_end_clean();
            wp_reset_postdata();
            return $output_string;
        }
        return sprintf(__('Template "%s" not found! (%s)', 'plek'), $template_name, $path);
    }

    /**
     * Get the option value of the wac options
     * @param string $template_args - Arguments to pass to the template
     * 
     * @return mixed Option value or Null, if option is not found
     */
    public function get_wac_option(string $options_name = '')
    {
        $options = get_option('wac_general_options');
        if (empty($options_name)) {
            return null;
        }
        if (empty($options[$options_name])) {
            return null;
        }
        return $options[$options_name];
    }

    /**
     * Sends a email with username and password to the new customer
     *  @param string $email - The email address of the recipient
     *  @param string $name - The first name of the user
     *  @param string $password - the password of the user
     * 
     *  @return bool true on success, false on error.
     * 
     */
    public function send_mail_to_new_customer(string $email = '', string $name = '', string $password = '')
    {
        $mailer = WC()->mailer();
        $blog_name = get_bloginfo('name');
        $blog_name = html_entity_decode($blog_name, ENT_QUOTES, 'UTF-8');
        $message = $this->load_template_to_var('new-account', 'email/', $email, $name, $password, $blog_name);
        $template = 'new-account.php';

        $subject = sprintf(__("New account created at %s", 'wac'), $blog_name);
        $headers = "Content-Type: text/html\r\n";
        //Send email
        $send = $mailer->send($email, $subject, $message, $headers);
        return $send;
    }

    /**
     * Saves a message to be displayed as an admin_notice
     *
     * @param string $notice - The message to display
     * @param string $type - Type of message (success, error)
     * @param int $order_id - The order_id / post_id
     * @return bool True on success, false on error
     */
    public function wac_set_notice(string $notice, string $type, $order_id)
    {
        $user_id = get_current_user_id();
        $trans_id = "wac_admin_notice_{$user_id}_{$order_id}";
        $classes = "";
        switch ($type) {
            case 'error':
                $classes = 'notice notice-error';
                break;
            case 'success':
                $classes = 'notice notice-success';
                break;

            default:
                $classes = 'notice notice-info';
                break;
        }
        $notice = "<div class='{$classes}'><p>{$notice}</p></div>";
        $trans_notices = get_transient($trans_id);
        if (is_array($trans_notices)) {
            $trans_notices[] = $notice;
        } else {
            $trans_notices = array($notice);
        }
        return set_transient($trans_id, $trans_notices, 45);
    }

    /**
     * Displays the stored messages as admin_notices
     *
     * @return void
     */
    public function wac_display_notices()
    {
        add_action('admin_notices', function () {
            $user_id = get_current_user_id();
            $order_id = (!empty($_GET['post'])) ? $_GET['post'] : 0;
            $trans_id = "wac_admin_notice_{$user_id}_{$order_id}";

            $notices = get_transient($trans_id);
            if (is_array($notices)) {
                foreach ($notices as $notice) {
                    echo $notice;
                }
            }
            delete_transient($trans_id);
        });
    }
}
