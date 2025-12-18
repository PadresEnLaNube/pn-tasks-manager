<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    pn_tasks_manager
 * @subpackage pn-tasks-manager/includes
 * @author     Padres en la Nube
 */
class PN_TASKS_MANAGER_Activator {
	/**
   * Plugin activation functions
   *
   * Functions to be loaded on plugin activation. This actions creates roles, options and post information attached to the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function pn_tasks_manager_activate() {
    require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-functions-post.php';
    require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-functions-attachment.php';
    
    // Schedule cron job for resetting repeated tasks
    if (!wp_next_scheduled('pn_tasks_manager_reset_repeated_tasks')) {
      wp_schedule_event(time(), 'hourly', 'pn_tasks_manager_reset_repeated_tasks');
    }

    $post_functions = new PN_TASKS_MANAGER_Functions_Post();
    $attachment_functions = new PN_TASKS_MANAGER_Functions_Attachment();

    add_role('pn_tasks_manager_role_manager', esc_html(__('PN Tasks Manager', 'pn-tasks-manager')));

    $pn_tasks_manager_role_admin = get_role('administrator');
    $pn_tasks_manager_role_manager = get_role('pn_tasks_manager_role_manager');

    $pn_tasks_manager_role_manager->add_cap('upload_files'); 
    $pn_tasks_manager_role_manager->add_cap('read'); 

    foreach (PN_TASKS_MANAGER_CPTS as $cpt_key => $cpt_name) { 
      // Assign all custom capabilities for the CPT to both admin and manager roles to ensure menu visibility
      $capabilities_constant = 'PN_TASKS_MANAGER_ROLE_' . strtoupper($cpt_key) . '_CAPABILITIES';
      if (defined($capabilities_constant)) {
        $capabilities = constant($capabilities_constant);
        foreach ($capabilities as $cap) {
          $pn_tasks_manager_role_admin->add_cap($cap);
          $pn_tasks_manager_role_manager->add_cap($cap);
        }
      }
      
      // Additionally, assign the management option      
      $pn_tasks_manager_role_admin->add_cap('manage_' . $cpt_key . '_options');
      $pn_tasks_manager_role_manager->add_cap('manage_' . $cpt_key . '_options');
    }

    if (empty(get_posts(['fields' => 'ids', 'numberposts' => -1, 'post_type' => 'pn_tasks_task', 'post_status' => 'any', ]))) {
      $pn_tasks_manager_title = __('Task Test', 'pn-tasks-manager');
      $pn_tasks_manager_post_content = '';
      $pn_tasks_manager_id = $post_functions->pn_tasks_manager_insert_post(esc_html($pn_tasks_manager_title), $pn_tasks_manager_post_content, '', sanitize_title(esc_html($pn_tasks_manager_title)), 'pn_tasks_task', 'publish', 1);

      if (class_exists('Polylang') && function_exists('pll_default_language')) {
        $language = pll_default_language();
        pll_set_post_language($pn_tasks_manager_id, $language);
        $locales = pll_languages_list(['hide_empty' => false]);

        if (!empty($locales)) {
          foreach ($locales as $locale) {
            if ($locale != $language) {
              $pn_tasks_manager_title = __('Task Test', 'pn-tasks-manager') . ' ' . $locale;
              $pn_tasks_manager_post_content = '';
              $translated_taskpnspn_id = $post_functions->pn_tasks_manager_insert_post(esc_html($pn_tasks_manager_title), $pn_tasks_manager_post_content, '', sanitize_title(esc_html($pn_tasks_manager_title)), 'pn_tasks_task', 'publish', 1);

              pll_set_post_language($translated_taskpnspn_id, $locale);

              pll_save_post_translations([
                $language => $pn_tasks_manager_id,
                $locale => $translated_taskpnspn_id,
              ]);
            }
          }
        }
      }
    }

    update_option('pn_tasks_manager_options_changed', true);
  }
}