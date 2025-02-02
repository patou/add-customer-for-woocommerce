<?php

/**
 * The update customer data checkbox
 * 
 * @version     1.6.5
 * @package     WAC\BackendTemplate
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $wac;
$wac = (!is_object($wac)) ? new woo_add_customer() : $wac;

extract(get_defined_vars());
$order = (isset($template_args[0])) ? $template_args[0] : null; //The current order object
$order_status = (is_object($order)) ? $order->get_status() : null;

//Only pre-check the checkbox if the option is selected by the user and the order is a new order. 
$checked = ($wac->get_wac_option('wac_preselect') === 'yes' and $order_status === 'auto-draft') ? 'checked' : '';
$checked_notify = ($wac->get_wac_option('wac_send_notification') === 'yes') ? 'checked' : '';
?>

<div id='wac_add_customer_con' class="edit_address">
    <div class="_add_customer_fields">
        <label><?php echo __('Add new Customer', 'wac'); ?></label>
        <p class="wac_add_customer_field">
            <input type="checkbox" name="wac_add_customer" id="wac_add_customer" value="true" placeholder="" autocomplete="off">
            <label for="wac_add_customer"><?php echo __('Save as new customer', 'wac'); ?></label>
        </p>
        <p class="wac_add_customer_notify_field" style="display: none;">
            <input type="checkbox" name="wac_add_customer_notify" id="wac_add_customer_notify" value="true" placeholder="" autocomplete="off" <?php echo $checked_notify; ?>>
            <label for="wac_add_customer_notify"><?php echo __('Send email to new customer', 'wac'); ?></label>
        </p>
    </div>
</div>
<!-- Variables for the JS to use -->
<script type="text/javascript">
    window.sep_variables = {
        'default_options': {
            'add_customer': '<?php echo $checked; ?>',
            'notify_customer': '<?php echo $checked_notify; ?>'
        }
    }
</script>