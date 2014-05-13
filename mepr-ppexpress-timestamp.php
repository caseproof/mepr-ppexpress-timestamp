<?php
/*
Plugin Name: MemberPress - PayPal Timezone Adjustment
Plugin URI: http://memberpress.com
Description: Allows users in Australia and New Zeland who are affected by the PayPal timezone bug to offset their Subscription start times to account for this.
Version: 1.0.0
Author: Caseproof, LLC
Author URI: http://caseproof.com
Text Domain: memberpress
Copyright: 2004-2013, Caseproof, LLC
*/

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

//Shows the dropdown on the MP PayPal Gateway Options
function mepr_ppets_display_option($gateway) {
  $offset = get_option('mepr-ppets-' . $gateway->id, 0);
  
  ?>
    <tr>
      <td><?php _e('Timezone Offset'); ?>:</td>
      <td>
        <select name="mepr-ppets-ts-offset">
          <?php for($i = 0; $i <= 24; $i++): ?>
            <option value="<?php echo $i; ?>" <?php selected($offset, $i); ?>><?php echo $i; ?></option>
          <?php endfor; ?>
        </select>
        <?php _e('Hours'); ?>
        <!-- Just in case they have more than one PayPal gateway activated -->
        <input type="hidden" name="mepr-ppets-gateway-id" value="<?php echo $gateway->id; ?>" />
      </td>
    <tr>
  <?php
}
add_action('mepr-paypal-express-options-form', 'mepr_ppets_display_option');

//Overrides the MP timestamp for the start date of the subscription
function mepr_ppets_override_ts($ts, $gateway) {
  $offset = get_option('mepr-ppets-' . $gateway->id, 0);
  
  return ($ts + MeprUtils::hours($offset));
}
add_filter('mepr-paypal-express-subscr-start-ts', 'mepr_ppets_override_ts', 10, 2);

//Store the option in the DB
function mepr_ppets_store_offset() {
  if(isset($_POST['mepr-ppets-gateway-id']) && !empty($_POST['mepr-ppets-gateway-id'])) {
    $gateway_id = stripslashes($_POST['mepr-ppets-gateway-id']);
    $offset = (isset($_POST['mepr-ppets-ts-offset']) && (int)$_POST['mepr-ppets-ts-offset'] > 0)?(int)$_POST['mepr-ppets-ts-offset']:0;
    
    update_option('mepr-ppets-' . $gateway_id, $offset);
  }
}
add_action('mepr-process-options', 'mepr_ppets_store_offset');
