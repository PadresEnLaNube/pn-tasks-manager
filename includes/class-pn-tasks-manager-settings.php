<?php
/**
 * Settings manager.
 *
 * This class defines plugin settings, both in dashboard or in front-end.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/includes
 * @author     Padres en la Nube
 */
class PN_TASKS_MANAGER_Settings {
  public function pn_tasks_manager_get_options() {
    $pn_tasks_manager_options = [];
    // Plugin Pages section (first)
    $pn_tasks_manager_options['pn_tasks_manager_pages_section_start'] = [
      'id' => 'pn_tasks_manager_pages_section_start',
      'section' => 'start',
      'label' => __('Plugin Pages', 'pn-tasks-manager'),
      'description' => __('Create and manage the pages that display the plugin blocks.', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn_tasks_manager_page_task_list'] = [
      'id' => 'pn_tasks_manager_page_task_list',
      'input' => 'page_manager',
      'label' => __('Task List', 'pn-tasks-manager'),
      'block_name' => 'pn-tasks-manager/task-list',
      'page_option' => 'pn_tasks_manager_page_task_list',
    ];

    $pn_tasks_manager_options['pn_tasks_manager_page_calendar'] = [
      'id' => 'pn_tasks_manager_page_calendar',
      'input' => 'page_manager',
      'label' => __('Calendar', 'pn-tasks-manager'),
      'block_name' => 'pn-tasks-manager/calendar',
      'page_option' => 'pn_tasks_manager_page_calendar',
    ];

    $pn_tasks_manager_options['pn_tasks_manager_pages_section_end'] = [
      'id' => 'pn_tasks_manager_pages_section_end',
      'section' => 'end',
    ];

    // Colors section
    $pn_tasks_manager_options['pn_tasks_manager_colors_section_start'] = [
      'id' => 'pn_tasks_manager_colors_section_start',
      'section' => 'start',
      'label' => __('Main colors', 'pn-tasks-manager'),
      'description' => __('Configure the colors used in the main CSS :root.', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn_tasks_manager_color_main'] = [
      'id' => 'pn_tasks_manager_color_main',
      'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Primary color', 'pn-tasks-manager'),
      'value' => '#b84a00',
      'description' => __('Maps to --pn-tasks-manager-color-main', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn_tasks_manager_bg_color_main'] = [
      'id' => 'pn_tasks_manager_bg_color_main',
      'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Primary background', 'pn-tasks-manager'),
      'value' => '#b84a00',
      'description' => __('Maps to --pn-tasks-manager-bg-color-main', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn_tasks_manager_border_color_main'] = [
      'id' => 'pn_tasks_manager_border_color_main',
      'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Primary border', 'pn-tasks-manager'),
      'value' => '#b84a00',
      'description' => __('Maps to --pn-tasks-manager-border-color-main', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn_tasks_manager_color_main_alt'] = [
      'id' => 'pn_tasks_manager_color_main_alt',
      'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Alternate color', 'pn-tasks-manager'),
      'value' => '#232323',
      'description' => __('Maps to --pn-tasks-manager-color-main-alt', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn_tasks_manager_bg_color_main_alt'] = [
      'id' => 'pn_tasks_manager_bg_color_main_alt',
      'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Alternate background', 'pn-tasks-manager'),
      'value' => '#232323',
      'description' => __('Maps to --pn-tasks-manager-bg-color-main-alt', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn_tasks_manager_border_color_main_alt'] = [
      'id' => 'pn_tasks_manager_border_color_main_alt',
      'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Alternate border', 'pn-tasks-manager'),
      'value' => '#232323',
      'description' => __('Maps to --pn-tasks-manager-border-color-main-alt', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn_tasks_manager_color_main_blue'] = [
      'id' => 'pn_tasks_manager_color_main_blue',
      'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Primary blue', 'pn-tasks-manager'),
      'value' => '#6e6eff',
      'description' => __('Maps to --pn-tasks-manager-color-main-blue', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn_tasks_manager_color_main_grey'] = [
      'id' => 'pn_tasks_manager_color_main_grey',
      'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Primary gray', 'pn-tasks-manager'),
      'value' => '#f5f5f5',
      'description' => __('Maps to --pn-tasks-manager-color-main-grey', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn_tasks_manager_colors_section_end'] = [
      'id' => 'pn_tasks_manager_colors_section_end',
      'section' => 'end',
    ];

    $pn_tasks_manager_options['pn_tasks_manager_system_section_start'] = [
      'id' => 'pn_tasks_manager_system_section_start',
      'section' => 'start',
      'label' => __('System', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn-tasks-manager'] = [
      'id' => 'pn-tasks-manager',
      'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
      'input' => 'input',
      'type' => 'text',
      'label' => __('Task slug', 'pn-tasks-manager'),
      'placeholder' => __('Task slug', 'pn-tasks-manager'),
      'description' => __('This option sets the slug of the main Task archive page, and the Task pages. By default they will be:', 'pn-tasks-manager') . '<br><a href="' . esc_url(home_url('/pn-tasks-manager-task')) . '" target="_blank">' . esc_url(home_url('/pn-tasks-manager-task')) . '</a><br>' . esc_url(home_url('/pn-tasks-manager-task/task-name')),
    ];
    $pn_tasks_manager_options['pn_tasks_manager_options_remove'] = [
      'id' => 'pn_tasks_manager_options_remove',
      'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
      'input' => 'input',
      'type' => 'checkbox',
      'label' => __('Remove plugin options on deactivation', 'pn-tasks-manager'),
      'description' => __('If you activate this option the plugin will remove all options on deactivation. Please, be careful. This process cannot be undone.', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn_tasks_manager_system_section_end'] = [
      'id' => 'pn_tasks_manager_system_section_end',
      'section' => 'end',
    ];

    $pn_tasks_manager_options['pn_tasks_manager_role_section_start'] = [
      'id' => 'pn_tasks_manager_role_section_start',
      'section' => 'start',
      'label' => __('User Roles', 'pn-tasks-manager'),
      'description' => __('Manage user role assignments for this plugin.', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn_tasks_manager_role_selector_manager'] = [
      'id' => 'pn_tasks_manager_role_selector_manager',
      'input' => 'user_role_selector',
      'label' => __('PN Tasks Manager', 'pn-tasks-manager'),
      'role' => 'pn_tasks_manager_role_manager',
      'role_label' => __('PN Tasks Manager', 'pn-tasks-manager'),
    ];

    $pn_tasks_manager_options['pn_tasks_manager_role_section_end'] = [
      'id' => 'pn_tasks_manager_role_section_end',
      'section' => 'end',
    ];

    $pn_tasks_manager_options['pn_tasks_manager_nonce'] = [
      'id' => 'pn_tasks_manager_nonce',
      'input' => 'input',
      'type' => 'nonce',
    ];
    $pn_tasks_manager_options['pn_tasks_manager_submit'] = [
      'id' => 'pn_tasks_manager_submit',
      'input' => 'input',
      'type' => 'submit',
      'value' => __('Save options', 'pn-tasks-manager'),
    ];

    return $pn_tasks_manager_options;
  }

	/**
	 * Administrator menu.
	 *
	 * @since    1.0.0
	 */
	public function pn_tasks_manager_admin_menu() {
    add_menu_page(
      esc_html__('PN Tasks Manager', 'pn-tasks-manager'), 
      esc_html__('PN Tasks Manager', 'pn-tasks-manager'), 
      'administrator', 
      'pn_tasks_manager_options', 
      [$this, 'pn_tasks_manager_options'], 
      esc_url(PN_TASKS_MANAGER_URL . 'assets/media/pn-tasks-manager-menu-icon.svg'),
    );
		
    add_submenu_page(
      'pn_tasks_manager_options',
      esc_html__('Settings', 'pn-tasks-manager'), 
      esc_html__('Settings', 'pn-tasks-manager'), 
      'administrator', 
      'pn_tasks_manager_options', 
      [$this, 'pn_tasks_manager_options'], 
    );
	}

	public function pn_tasks_manager_options() {
	  ?>
	    <div class="pn-tasks-manager-options pn-tasks-manager-max-width-1000 pn-tasks-manager-margin-auto pn-tasks-manager-mt-50 pn-tasks-manager-mb-50">
        <img src="<?php echo esc_url(PN_TASKS_MANAGER_URL . 'assets/media/banner-1544x500.png'); ?>" alt="<?php esc_html_e('Plugin main Banner', 'pn-tasks-manager'); ?>" title="<?php esc_html_e('Plugin main Banner', 'pn-tasks-manager'); ?>" class="pn-tasks-manager-width-100-percent pn-tasks-manager-border-radius-20 pn-tasks-manager-mb-30">
        <h1 class="pn-tasks-manager-mb-30"><?php esc_html_e('PN Tasks Manager Settings', 'pn-tasks-manager'); ?></h1>
        <div class="pn-tasks-manager-options-fields pn-tasks-manager-mb-30">
          <form action="" method="post" id="pn-tasks-manager-form-setting" class="pn-tasks-manager-form pn-tasks-manager-p-30">
          <?php 
            $options = self::pn_tasks_manager_get_options();

            foreach ($options as $pn_tasks_manager_option) {
              PN_TASKS_MANAGER_Forms::pn_tasks_manager_input_wrapper_builder($pn_tasks_manager_option, 'option', 0, 0, 'half');
            }
          ?>
          </form> 
        </div>
      </div>
	  <?php
	}

  public function pn_tasks_manager_activated_plugin($plugin) {
    if($plugin == 'pn-tasks-manager/pn-tasks-manager.php') {
      if (get_option('pn_tasks_manager_pages_taskpn') && get_option('pn_tasks_manager_url_main')) {
        if (!get_transient('pn_tasks_manager_just_activated') && !defined('DOING_AJAX')) {
          set_transient('pn_tasks_manager_just_activated', true, 30);
        }
      }
    }
  }

  public function pn_tasks_manager_check_activation() {
    // Only run in admin and not during AJAX requests
    if (!is_admin() || defined('DOING_AJAX')) {
      return;
    }

    // Check if we're already in the redirection process
    if (get_option('pn_tasks_manager_redirecting')) {
      delete_option('pn_tasks_manager_redirecting');
      return;
    }

    if (get_transient('pn_tasks_manager_just_activated')) {
      $target_url = admin_url('admin.php?page=pn_tasks_manager_options');
      
      if ($target_url) {
        // Mark that we're in the redirection process
        update_option('pn_tasks_manager_redirecting', true);
        
        // Remove the transient
        delete_transient('pn_tasks_manager_just_activated');
        
        // Redirect and exit
        wp_safe_redirect(esc_url($target_url));
        exit;
      }
    }
  }

  /**
   * Adds the Settings link to the plugin list
   */
  public function pn_tasks_manager_plugin_action_links($links) {
      $settings_link = '<a href="admin.php?page=pn_tasks_manager_options">' . esc_html__('Settings', 'pn-tasks-manager') . '</a>';
      array_unshift($links, $settings_link);
      
      return $links;
  }
}