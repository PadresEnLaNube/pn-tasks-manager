<?php
/**
 * Provide a common footer area view for the plugin
 *
 * This file is used to markup the common footer facing aspects of the plugin.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 *
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/common/templates
 */

  if (!defined('ABSPATH')) exit; // Exit if accessed directly

  // Ensure the global variable exists
  if (!isset($GLOBALS['pn_tasks_manager_data'])) {
    $GLOBALS['pn_tasks_manager_data'] = array(
      'user_id' => get_current_user_id(),
      'post_id' => is_admin() ? (!empty($GLOBALS['_REQUEST']['post']) ? $GLOBALS['_REQUEST']['post'] : 0) : get_the_ID()
    );
  }

  $pn_tasks_manager_data = $GLOBALS['pn_tasks_manager_data'];
?>

<div id="pn-tasks-manager-main-message" class="pn-tasks-manager-main-message pn-tasks-manager-display-none-soft pn-tasks-manager-z-index-top" style="display:none;" data-user-id="<?php echo esc_attr($pn_tasks_manager_data['user_id']); ?>" data-post-id="<?php echo esc_attr($pn_tasks_manager_data['post_id']); ?>">
  <span id="pn-tasks-manager-main-message-span"></span><i class="material-icons-outlined pn-tasks-manager-vertical-align-bottom pn-tasks-manager-ml-20 pn-tasks-manager-cursor-pointer pn-tasks-manager-color-white pn-tasks-manager-close-icon">close</i>

  <div id="pn-tasks-manager-bar-wrapper">
  	<div id="pn-tasks-manager-bar"></div>
  </div>
</div>
