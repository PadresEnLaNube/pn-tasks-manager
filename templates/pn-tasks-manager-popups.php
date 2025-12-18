<?php
/**
 * Provide common popups for the plugin
 *
 * This file is used to markup the common popups of the plugin.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 *
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/common/templates
 */

  if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div class="pn-tasks-manager-popup-overlay pn-tasks-manager-display-none-soft"></div>
<div class="pn-tasks-manager-menu-more-overlay pn-tasks-manager-display-none-soft"></div>

<?php foreach (PN_TASKS_MANAGER_CPTS as $pn_tasks_manager_cpt => $pn_tasks_manager_cpt_name) : ?>
  <div id="pn-tasks-manager-popup-<?php echo esc_attr($pn_tasks_manager_cpt); ?>-add" class="pn-tasks-manager-popup pn-tasks-manager-popup-size-medium pn-tasks-manager-display-none-soft">
    <?php PN_TASKS_MANAGER_Data::pn_tasks_manager_popup_loader(); ?>
  </div>

  <div id="pn-tasks-manager-popup-<?php echo esc_attr($pn_tasks_manager_cpt); ?>-check" class="pn-tasks-manager-popup pn-tasks-manager-popup-size-medium pn-tasks-manager-display-none-soft">
    <?php PN_TASKS_MANAGER_Data::pn_tasks_manager_popup_loader(); ?>
  </div>

  <div id="pn-tasks-manager-popup-<?php echo esc_attr($pn_tasks_manager_cpt); ?>-view" class="pn-tasks-manager-popup pn-tasks-manager-popup-size-medium pn-tasks-manager-display-none-soft">
    <?php PN_TASKS_MANAGER_Data::pn_tasks_manager_popup_loader(); ?>
  </div>

  <div id="pn-tasks-manager-popup-<?php echo esc_attr($pn_tasks_manager_cpt); ?>-edit" class="pn-tasks-manager-popup pn-tasks-manager-popup-size-medium pn-tasks-manager-display-none-soft">
    <?php PN_TASKS_MANAGER_Data::pn_tasks_manager_popup_loader(); ?>
  </div>

  <div id="pn-tasks-manager-popup-<?php echo esc_attr($pn_tasks_manager_cpt); ?>-remove" class="pn-tasks-manager-popup pn-tasks-manager-popup-size-medium pn-tasks-manager-display-none-soft">
    <div class="pn-tasks-manager-popup-content">
      <div class="pn-tasks-manager-p-30">
        <h3 class="pn-tasks-manager-text-align-center pn-tasks-manager-mb-20"><?php echo esc_html($pn_tasks_manager_cpt_name); ?> <?php esc_html_e('removal', 'pn-tasks-manager'); ?></h3>
        <p class="pn-tasks-manager-text-align-center pn-tasks-manager-mb-20"><?php echo esc_html($pn_tasks_manager_cpt_name); ?> <?php esc_html_e('will be completely deleted. This process cannot be reversed and cannot be recovered.', 'pn-tasks-manager'); ?></p>

        <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
          <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-text-align-center">
            <a href="#" class="pn-tasks-manager-popup-close pn-tasks-manager-text-decoration-none pn-tasks-manager-font-size-small"><?php esc_html_e('Cancel', 'pn-tasks-manager'); ?></a>
          </div>
          <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-text-align-center">
            <a href="#" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-<?php echo esc_attr($pn_tasks_manager_cpt); ?>-remove" data-pn-tasks-manager-post-type="<?php echo esc_attr($pn_tasks_manager_cpt); ?>"><?php esc_html_e('Remove', 'pn-tasks-manager'); ?> <?php echo esc_html($pn_tasks_manager_cpt_name); ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>