<?php
/**
 * Provide common popups for the plugin
 *
 * This file is used to markup the common popups of the plugin.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 *
 * @package    taskspn
 * @subpackage taskspn/common/templates
 */

  if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div class="taskspn-popup-overlay taskspn-display-none-soft"></div>
<div class="taskspn-menu-more-overlay taskspn-display-none-soft"></div>

<?php foreach (TASKSPN_CPTS as $cpt => $cpt_name) : ?>
  <div id="taskspn-popup-<?php echo esc_attr($cpt); ?>-add" class="taskspn-popup taskspn-popup-size-medium taskspn-display-none-soft">
    <?php TASKSPN_Data::taskspn_popup_loader(); ?>
  </div>

  <div id="taskspn-popup-<?php echo esc_attr($cpt); ?>-check" class="taskspn-popup taskspn-popup-size-medium taskspn-display-none-soft">
    <?php TASKSPN_Data::taskspn_popup_loader(); ?>
  </div>

  <div id="taskspn-popup-<?php echo esc_attr($cpt); ?>-view" class="taskspn-popup taskspn-popup-size-medium taskspn-display-none-soft">
    <?php TASKSPN_Data::taskspn_popup_loader(); ?>
  </div>

  <div id="taskspn-popup-<?php echo esc_attr($cpt); ?>-edit" class="taskspn-popup taskspn-popup-size-medium taskspn-display-none-soft">
    <?php TASKSPN_Data::taskspn_popup_loader(); ?>
  </div>

  <div id="taskspn-popup-<?php echo esc_attr($cpt); ?>-remove" class="taskspn-popup taskspn-popup-size-medium taskspn-display-none-soft">
    <div class="taskspn-popup-content">
      <div class="taskspn-p-30">
        <h3 class="taskspn-text-align-center taskspn-mb-20"><?php echo esc_html($cpt_name); ?> <?php esc_html_e('removal', 'taskspn'); ?></h3>
        <p class="taskspn-text-align-center taskspn-mb-20"><?php echo esc_html($cpt_name); ?> <?php esc_html_e('will be completely deleted. This process cannot be reversed and cannot be recovered.', 'taskspn'); ?></p>

        <div class="taskspn-display-table taskspn-width-100-percent">
          <div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-text-align-center">
            <a href="#" class="taskspn-popup-close taskspn-text-decoration-none taskspn-font-size-small"><?php esc_html_e('Cancel', 'taskspn'); ?></a>
          </div>
          <div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-text-align-center">
            <a href="#" class="taskspn-btn taskspn-btn-mini taskspn-<?php echo esc_attr($cpt); ?>-remove" data-taskspn-post-type="taskspn_<?php echo esc_attr($cpt); ?>"><?php esc_html_e('Remove', 'taskspn'); ?> <?php echo esc_html($cpt_name); ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>