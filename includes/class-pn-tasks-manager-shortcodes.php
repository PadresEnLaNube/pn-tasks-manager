<?php
/**
 * Platform shortcodes.
 *
 * This class defines all shortcodes of the platform.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/includes
 * @author     Padres en la Nube
 */
class PN_TASKS_MANAGER_Shortcodes {
	/**
	 * Manage the shortcodes in the platform.
	 *
	 * @since    1.0.0
	 */
	public function pn_tasks_manager_test($atts) {
    $a = extract(shortcode_atts([
      'user_id' => 0,
      'post_id' => 0,
    ], $atts));

    ob_start();
    ?>
      <div class="pn-tasks-manager-shortcode-example">
      	Shortcode example
      	<p>User id: <?php echo intval($user_id); ?></p>
      	<p>Post id: <?php echo intval($post_id); ?></p>
      </div>
    <?php
    $pn_tasks_manager_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $pn_tasks_manager_return_string;
	}

  public function pn_tasks_manager_call_to_action($atts) {
    // echo do_shortcode('[pn-tasks-manager-call-to-action pn_tasks_manager_call_to_action_icon="error_outline" pn_tasks_manager_call_to_action_title="' . esc_html(__('Default title', 'pn-tasks-manager')) . '" pn_tasks_manager_call_to_action_content="' . esc_html(__('Default content', 'pn-tasks-manager')) . '" pn_tasks_manager_call_to_action_button_link="#" pn_tasks_manager_call_to_action_button_text="' . esc_html(__('Button text', 'pn-tasks-manager')) . '" pn_tasks_manager_call_to_action_button_class="pn-tasks-manager-class"]');
    $a = extract(shortcode_atts(array(
      'pn_tasks_manager_call_to_action_class' => '',
      'pn_tasks_manager_call_to_action_icon' => '',
      'pn_tasks_manager_call_to_action_title' => '',
      'pn_tasks_manager_call_to_action_content' => '',
      'pn_tasks_manager_call_to_action_button_link' => '#',
      'pn_tasks_manager_call_to_action_button_text' => '',
      'pn_tasks_manager_call_to_action_button_class' => '',
      'pn_tasks_manager_call_to_action_button_data_key' => '',
      'pn_tasks_manager_call_to_action_button_data_value' => '',
      'pn_tasks_manager_call_to_action_button_blank' => 0,
    ), $atts));

    ob_start();
    ?>
      <div class="pn-tasks-manager-call-to-action pn-tasks-manager-text-align-center pn-tasks-manager-pt-30 pn-tasks-manager-pb-50 <?php echo esc_attr($pn_tasks_manager_call_to_action_class); ?>">
        <div class="pn-tasks-manager-call-to-action-icon">
          <i class="material-icons-outlined pn-tasks-manager-font-size-75 pn-tasks-manager-color-main-0"><?php echo esc_html($pn_tasks_manager_call_to_action_icon); ?></i>
        </div>

        <h4 class="pn-tasks-manager-call-to-action-title pn-tasks-manager-text-align-center pn-tasks-manager-mt-10 pn-tasks-manager-mb-20"><?php echo esc_html($pn_tasks_manager_call_to_action_title); ?></h4>
        
        <?php if (!empty($pn_tasks_manager_call_to_action_content)): ?>
          <p class="pn-tasks-manager-text-align-center"><?php echo wp_kses_post($pn_tasks_manager_call_to_action_content); ?></p>
        <?php endif ?>

        <?php if (!empty($pn_tasks_manager_call_to_action_button_text)): ?>
          <div class="pn-tasks-manager-text-align-center pn-tasks-manager-mt-20">
            <a class="pn-tasks-manager-btn pn-tasks-manager-btn-transparent pn-tasks-manager-margin-auto <?php echo esc_attr($pn_tasks_manager_call_to_action_button_class); ?>" <?php echo ($pn_tasks_manager_call_to_action_button_blank) ? 'target="_blank"' : ''; ?> href="<?php echo esc_url($pn_tasks_manager_call_to_action_button_link); ?>" <?php echo (!empty($pn_tasks_manager_call_to_action_button_data_key) && !empty($pn_tasks_manager_call_to_action_button_data_value)) ? esc_attr($pn_tasks_manager_call_to_action_button_data_key) . '="' . esc_attr($pn_tasks_manager_call_to_action_button_data_value) . '"' : ''; ?>><?php echo esc_html($pn_tasks_manager_call_to_action_button_text); ?></a>
          </div>
        <?php endif ?>
      </div>
    <?php 
    $pn_tasks_manager_return_string = ob_get_contents(); 
    ob_end_clean(); 
      return $pn_tasks_manager_return_string;
  }

  /**
   * Calendar shortcode
   */
  public function pn_tasks_manager_calendar($atts) {
    $plugin_calendar = new PN_TASKS_MANAGER_Calendar();
    return $plugin_calendar->pn_tasks_manager_calendar_render($atts);
  }

  /**
   * Joinable tasks list shortcode
   *
   * Usage: [pn-tasks-manager-joinable-tasks]
   */
  public function pn_tasks_manager_joinable_tasks($atts) {
    $atts = shortcode_atts([
      'public_only' => '1',
    ], $atts);

    $query_args = [
      'fields' => 'ids',
      'numberposts' => -1,
      'post_type' => 'pn_tasks_task',
      'post_status' => 'publish',
      'orderby' => 'menu_order',
      'order' => 'ASC',
      'meta_query' => [
        [
          'key' => 'pn_tasks_manager_repeated_from',
          'compare' => 'NOT EXISTS'
        ]
      ]
    ];

    // For logged-in users show all joinable tasks (not limited to public); guests only see public
    if ($atts['public_only'] === '1' && !is_user_logged_in()) {
      $query_args['meta_query'][] = [
        'key' => 'pn_tasks_manager_task_public',
        'value' => 'on',
        'compare' => '='
      ];
    }

    if (class_exists('Polylang')) {
      $query_args['lang'] = pll_current_language('slug');
    }

    $tasks = get_posts($query_args);

    ob_start();
    ?>
      <div class="pn-tasks-manager-joinable-tasks pn-tasks-manager-max-width-700 pn-tasks-manager-margin-auto">
        <ul class="pn-tasks-manager-list-style-none pn-tasks-manager-p-0">
          <?php if (!empty($tasks)): ?>
            <?php foreach ($tasks as $task_id): ?>
              <?php 
                $title = get_the_title($task_id);
                $owners = (new PN_TASKS_MANAGER_Post_Type_Task())->pn_tasks_manager_task_owners($task_id);
                $is_owner = is_user_logged_in() ? in_array(get_current_user_id(), $owners) : false;
              ?>
              <li class="pn-tasks-manager-bordered pn-tasks-manager-border-radius-5 pn-tasks-manager-p-20 pn-tasks-manager-mb-20" data-pn_tasks_manager_task-id="<?php echo esc_attr($task_id); ?>">
                <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                  <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-70-percent">
                    <strong><?php echo esc_html($title); ?></strong>
                  </div>
                  <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-30-percent pn-tasks-manager-text-align-right">
                    <?php if (!is_user_logged_in()): ?>
                      <span><?php esc_html_e('Log in to join', 'pn-tasks-manager'); ?></span>
                    <?php elseif ($is_owner): ?>
                      <span class="pn-tasks-manager-color-green pn-tasks-manager-font-size-small"><?php esc_html_e('You are an owner', 'pn-tasks-manager'); ?></span>
                    <?php else: ?>
                      <a href="#" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-join-task-btn" data-task-id="<?php echo esc_attr($task_id); ?>"><?php esc_html_e('Join task', 'pn-tasks-manager'); ?></a>
                    <?php endif; ?>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li><?php echo wp_kses_post(do_shortcode('[pn-tasks-manager-call-to-action pn_tasks_manager_call_to_action_icon="error_outline" pn_tasks_manager_call_to_action_title="' . esc_html(__('No tasks found', 'pn-tasks-manager')) . '" pn_tasks_manager_call_to_action_content="' . esc_html(__('No joinable tasks found. Add some tasks to the platform to make them joinable.', 'pn-tasks-manager')) . '"]')); ?></li>
          <?php endif; ?>
        </ul>
      </div>

    <?php
    $pn_tasks_manager_return_string = ob_get_contents();
    ob_end_clean();
    return $pn_tasks_manager_return_string;
  }

  /**
   * Users ranking by completed task hours (admin-only)
   *
   * Usage: [pn-tasks-manager-users-ranking]
   */
  public function pn_tasks_manager_users_ranking($atts) {
    if (!current_user_can('manage_options')) {
      return '';
    }

    $atts = shortcode_atts([
      'limit' => 20,
    ], $atts);

    $query_args = [
      'fields' => 'ids',
      'numberposts' => -1,
      'post_type' => 'pn_tasks_task',
      'post_status' => 'publish',
      'meta_query' => [
        [ 'key' => 'pn_tasks_manager_task_completed', 'value' => 'on', 'compare' => '=' ],
        [ 'key' => 'pn_tasks_manager_repeated_from', 'compare' => 'NOT EXISTS' ],
      ],
    ];

    if (class_exists('Polylang')) {
      $query_args['lang'] = pll_current_language('slug');
    }

    $tasks = get_posts($query_args);

    $hours_per_user = [];
    $task_class = new PN_TASKS_MANAGER_Post_Type_Task();

    foreach ($tasks as $task_id) {
      $hours = floatval(get_post_meta($task_id, 'pn_tasks_manager_task_estimated_hours', true));
      if ($hours <= 0) { continue; }
      $owners = $task_class->pn_tasks_manager_task_owners($task_id);
      if (empty($owners)) { continue; }
      foreach ($owners as $user_id) {
        if (empty($user_id)) { continue; }
        if (!isset($hours_per_user[$user_id])) { $hours_per_user[$user_id] = 0.0; }
        $hours_per_user[$user_id] += $hours;
      }
    }

    // Sort by hours desc
    arsort($hours_per_user, SORT_NUMERIC);
    $limit = intval($atts['limit']);
    if ($limit > 0) {
      $hours_per_user = array_slice($hours_per_user, 0, $limit, true);
    }

    ob_start();
    ?>
      <div class="pn-tasks-manager-users-ranking pn-tasks-manager-max-width-700 pn-tasks-manager-margin-auto">
        <h4 class="pn-tasks-manager-mb-20"><?php esc_html_e('Users ranking by completed hours', 'pn-tasks-manager'); ?></h4>
        <?php if (empty($hours_per_user)): ?>
          <p><?php esc_html_e('No completed hours found.', 'pn-tasks-manager'); ?></p>
        <?php else: ?>
          <ol class="pn-tasks-manager-list-style-none pn-tasks-manager-p-0">
            <?php foreach ($hours_per_user as $user_id => $total_hours): ?>
              <?php $display_name = PN_TASKS_MANAGER_Functions_User::pn_tasks_manager_user_get_name($user_id); ?>
              <li class="pn-tasks-manager-bordered pn-tasks-manager-border-radius-5 pn-tasks-manager-p-10 pn-tasks-manager-mb-10 pn-tasks-manager-users-ranking-item pn-tasks-manager-cursor-pointer" data-user-id="<?php echo esc_attr($user_id); ?>" title="<?php esc_attr_e('Click to view completed tasks', 'pn-tasks-manager'); ?>">
                <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                  <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-70-percent">
                    <strong><?php echo esc_html($display_name); ?></strong>
                  </div>
                  <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-30-percent pn-tasks-manager-text-align-right">
                    <span><?php echo esc_html(number_format($total_hours, 2)); ?> h</span>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>
        <?php endif; ?>
      </div>

      <?php echo wp_kses_post( PN_TASKS_MANAGER_Popups::open('<div id="pn-tasks-manager-users-ranking-popup-content"></div>', ['id' => 'pn-tasks-manager-users-ranking-popup']) ); ?>
    <?php
    $pn_tasks_manager_return_string = ob_get_contents();
    ob_end_clean();
    return $pn_tasks_manager_return_string;
  }
}