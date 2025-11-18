<?php
/**
 * Settings manager.
 *
 * This class defines plugin settings, both in dashboard or in front-end.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    TASKSPN
 * @subpackage TASKSPN/includes
 * @author     Padres en la Nube
 */
class TASKSPN_Settings {
  public function taskspn_get_options() {
    $taskspn_options = [];
    // Colors section (first)
    $taskspn_options['taskspn_colors_section_start'] = [
      'id' => 'taskspn_colors_section_start',
      'section' => 'start',
      'label' => __('Main colors', 'taskspn'),
      'description' => __('Configure the colors used in the main CSS :root.', 'taskspn'),
    ];

    $taskspn_options['taskspn_color_main'] = [
      'id' => 'taskspn_color_main',
      'class' => 'taskspn-input taskspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Primary color', 'taskspn'),
      'value' => '#d45500',
      'description' => __('Maps to --taskspn-color-main', 'taskspn'),
    ];

    $taskspn_options['taskspn_bg_color_main'] = [
      'id' => 'taskspn_bg_color_main',
      'class' => 'taskspn-input taskspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Primary background', 'taskspn'),
      'value' => '#d45500',
      'description' => __('Maps to --taskspn-bg-color-main', 'taskspn'),
    ];

    $taskspn_options['taskspn_border_color_main'] = [
      'id' => 'taskspn_border_color_main',
      'class' => 'taskspn-input taskspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Primary border', 'taskspn'),
      'value' => '#d45500',
      'description' => __('Maps to --taskspn-border-color-main', 'taskspn'),
    ];

    $taskspn_options['taskspn_color_main_alt'] = [
      'id' => 'taskspn_color_main_alt',
      'class' => 'taskspn-input taskspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Alternate color', 'taskspn'),
      'value' => '#232323',
      'description' => __('Maps to --taskspn-color-main-alt', 'taskspn'),
    ];

    $taskspn_options['taskspn_bg_color_main_alt'] = [
      'id' => 'taskspn_bg_color_main_alt',
      'class' => 'taskspn-input taskspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Alternate background', 'taskspn'),
      'value' => '#232323',
      'description' => __('Maps to --taskspn-bg-color-main-alt', 'taskspn'),
    ];

    $taskspn_options['taskspn_border_color_main_alt'] = [
      'id' => 'taskspn_border_color_main_alt',
      'class' => 'taskspn-input taskspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Alternate border', 'taskspn'),
      'value' => '#232323',
      'description' => __('Maps to --taskspn-border-color-main-alt', 'taskspn'),
    ];

    $taskspn_options['taskspn_color_main_blue'] = [
      'id' => 'taskspn_color_main_blue',
      'class' => 'taskspn-input taskspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Primary blue', 'taskspn'),
      'value' => '#6e6eff',
      'description' => __('Maps to --taskspn-color-main-blue', 'taskspn'),
    ];

    $taskspn_options['taskspn_color_main_grey'] = [
      'id' => 'taskspn_color_main_grey',
      'class' => 'taskspn-input taskspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'label' => __('Primary gray', 'taskspn'),
      'value' => '#f5f5f5',
      'description' => __('Maps to --taskspn-color-main-grey', 'taskspn'),
    ];

    $taskspn_options['taskspn_colors_section_end'] = [
      'id' => 'taskspn_colors_section_end',
      'section' => 'end',
    ];
    
    $taskspn_options['taskspn'] = [
      'id' => 'taskspn',
      'class' => 'taskspn-input taskspn-width-100-percent',
      'input' => 'input',
      'type' => 'text',
      'label' => __('Task slug', 'taskspn'),
      'placeholder' => __('Task slug', 'taskspn'),
      'description' => __('This option sets the slug of the main Task archive page, and the Task pages. By default they will be:', 'taskspn') . '<br><a href="' . esc_url(home_url('/taskspn-task')) . '" target="_blank">' . esc_url(home_url('/taskspn-task')) . '</a><br>' . esc_url(home_url('/taskspn-task/task-name')),
    ];
    $taskspn_options['taskspn_options_remove'] = [
      'id' => 'taskspn_options_remove',
      'class' => 'taskspn-input taskspn-width-100-percent',
      'input' => 'input',
      'type' => 'checkbox',
      'label' => __('Remove plugin options on deactivation', 'taskspn'),
      'description' => __('If you activate this option the plugin will remove all options on deactivation. Please, be careful. This process cannot be undone.', 'taskspn'),
    ];
    $taskspn_options['taskspn_nonce'] = [
      'id' => 'taskspn_nonce',
      'input' => 'input',
      'type' => 'nonce',
    ];
    $taskspn_options['taskspn_submit'] = [
      'id' => 'taskspn_submit',
      'input' => 'input',
      'type' => 'submit',
      'value' => __('Save options', 'taskspn'),
    ];

    return $taskspn_options;
  }

	/**
	 * Administrator menu.
	 *
	 * @since    1.0.0
	 */
	public function taskspn_admin_menu() {
    add_menu_page(
      esc_html__('Tasks Manager - PN', 'taskspn'), 
      esc_html__('Tasks Manager - PN', 'taskspn'), 
      'administrator', 
      'taskspn_options', 
      [$this, 'taskspn_options'], 
      esc_url(TASKSPN_URL . 'assets/media/taskspn-menu-icon.svg'),
    );
		
    add_submenu_page(
      'taskspn_options',
      esc_html__('Settings', 'taskspn'), 
      esc_html__('Settings', 'taskspn'), 
      'administrator', 
      'taskspn_options', 
      [$this, 'taskspn_options'], 
    );
	}

	public function taskspn_options() {
	  ?>
	    <div class="taskspn-options taskspn-max-width-1000 taskspn-margin-auto taskspn-mt-50 taskspn-mb-50">
        <img src="<?php echo esc_url(TASKSPN_URL . 'assets/media/banner-1544x500.png'); ?>" alt="<?php esc_html_e('Plugin main Banner', 'taskspn'); ?>" title="<?php esc_html_e('Plugin main Banner', 'taskspn'); ?>" class="taskspn-width-100-percent taskspn-border-radius-20 taskspn-mb-30">
        <h1 class="taskspn-mb-30"><?php esc_html_e('Tasks Manager - PN Settings', 'taskspn'); ?></h1>
        <div class="taskspn-options-fields taskspn-mb-30">
          <form action="" method="post" id="taskspn-form-setting" class="taskspn-form taskspn-p-30">
          <?php 
            $options = self::taskspn_get_options();

            foreach ($options as $taskspn_option) {
              TASKSPN_Forms::taskspn_input_wrapper_builder($taskspn_option, 'option', 0, 0, 'half');
            }
          ?>
          </form> 
        </div>
      </div>
	  <?php
	}

  public function taskspn_activated_plugin($plugin) {
    if($plugin == 'taskspn/taskspn.php') {
      if (get_option('taskspn_pages_taskpn') && get_option('taskspn_url_main')) {
        if (!get_transient('taskspn_just_activated') && !defined('DOING_AJAX')) {
          set_transient('taskspn_just_activated', true, 30);
        }
      }
    }
  }

  public function taskspn_check_activation() {
    // Only run in admin and not during AJAX requests
    if (!is_admin() || defined('DOING_AJAX')) {
      return;
    }

    // Check if we're already in the redirection process
    if (get_option('taskspn_redirecting')) {
      delete_option('taskspn_redirecting');
      return;
    }

    if (get_transient('taskspn_just_activated')) {
      $target_url = admin_url('admin.php?page=taskspn_options');
      
      if ($target_url) {
        // Mark that we're in the redirection process
        update_option('taskspn_redirecting', true);
        
        // Remove the transient
        delete_transient('taskspn_just_activated');
        
        // Redirect and exit
        wp_safe_redirect(esc_url($target_url));
        exit;
      }
    }
  }

  /**
   * Adds the Settings link to the plugin list
   */
  public function taskspn_plugin_action_links($links) {
      $settings_link = '<a href="admin.php?page=taskspn_options">' . esc_html__('Settings', 'taskspn') . '</a>';
      array_unshift($links, $settings_link);
      
      return $links;
  }
}