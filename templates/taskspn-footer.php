<?php
/**
 * Provide a common footer area view for the plugin
 *
 * This file is used to markup the common footer facing aspects of the plugin.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 *
 * @package    TASKSPN
 * @subpackage TASKSPN/common/templates
 */

  if (!defined('ABSPATH')) exit; // Exit if accessed directly

  // Ensure the global variable exists
  if (!isset($GLOBALS['taskspn_data'])) {
    $GLOBALS['taskspn_data'] = array(
      'user_id' => get_current_user_id(),
      'post_id' => is_admin() ? (!empty($GLOBALS['_REQUEST']['post']) ? $GLOBALS['_REQUEST']['post'] : 0) : get_the_ID()
    );
  }

  $taskspn_data = $GLOBALS['taskspn_data'];
?>

<div id="taskspn-main-message" class="taskspn-main-message taskspn-display-none-soft taskspn-z-index-top" style="display:none;" data-user-id="<?php echo esc_attr($taskspn_data['user_id']); ?>" data-post-id="<?php echo esc_attr($taskspn_data['post_id']); ?>">
  <span id="taskspn-main-message-span"></span><i class="material-icons-outlined taskspn-vertical-align-bottom taskspn-ml-20 taskspn-cursor-pointer taskspn-color-white taskspn-close-icon">close</i>

  <div id="taskspn-bar-wrapper">
  	<div id="taskspn-bar"></div>
  </div>
</div>
