<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    taskspn
 * @subpackage taskspn/includes
 * @author     Padres en la Nube
 */
class TASKSPN_Activator {
	/**
   * Plugin activation functions
   *
   * Functions to be loaded on plugin activation. This actions creates roles, options and post information attached to the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function taskspn_activate() {
    require_once TASKSPN_DIR . 'includes/class-taskspn-functions-post.php';
    require_once TASKSPN_DIR . 'includes/class-taskspn-functions-attachment.php';

    $post_functions = new TASKSPN_Functions_Post();
    $attachment_functions = new TASKSPN_Functions_Attachment();

    add_role('taskspn_role_manager', esc_html(__('Tasks Manager - PN', 'taskspn')));

    $taskspn_role_admin = get_role('administrator');
    $taskspn_role_manager = get_role('taskspn_role_manager');

    $taskspn_role_manager->add_cap('upload_files'); 
    $taskspn_role_manager->add_cap('read'); 

    foreach (TASKSPN_CPTS as $cpt_key => $cpt_name) { 
      // Assign all custom capabilities for the CPT to both admin and manager roles to ensure menu visibility
      $capabilities = constant('TASKSPN_ROLE_' . strtoupper($cpt_key) . '_CAPABILITIES');
      foreach ($capabilities as $cap) {
        $taskspn_role_admin->add_cap($cap);
        $taskspn_role_manager->add_cap($cap);
      }
      
      // Additionally, assign the management option      
      $taskspn_role_admin->add_cap('manage_' . $cpt_key . '_options');
      $taskspn_role_manager->add_cap('manage_' . $cpt_key . '_options');
    }

    if (empty(get_posts(['fields' => 'ids', 'numberposts' => -1, 'post_type' => 'taskspn_task', 'post_status' => 'any', ]))) {
      $taskspn_title = __('Task Test', 'taskspn');
      $taskspn_post_content = '';
      $taskspn_id = $post_functions->taskspn_insert_post(esc_html($taskspn_title), $taskspn_post_content, '', sanitize_title(esc_html($taskspn_title)), 'taskspn_task', 'publish', 1);

      if (class_exists('Polylang') && function_exists('pll_default_language')) {
        $language = pll_default_language();
        pll_set_post_language($taskspn_id, $language);
        $locales = pll_languages_list(['hide_empty' => false]);

        if (!empty($locales)) {
          foreach ($locales as $locale) {
            if ($locale != $language) {
              $taskspn_title = __('Task Test', 'taskspn') . ' ' . $locale;
              $taskspn_post_content = '';
              $translated_taskpnspn_id = $post_functions->taskspn_insert_post(esc_html($taskspn_title), $taskspn_post_content, '', sanitize_title(esc_html($taskspn_title)), 'taskspn_task', 'publish', 1);

              pll_set_post_language($translated_taskpnspn_id, $locale);

              pll_save_post_translations([
                $language => $taskspn_id,
                $locale => $translated_taskpnspn_id,
              ]);
            }
          }
        }
      }
    }

    update_option('taskspn_options_changed', true);
  }
}