<?php
/**
 * Platform shortcodes.
 *
 * This class defines all shortcodes of the platform.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    TASKSPN
 * @subpackage TASKSPN/includes
 * @author     Padres en la Nube
 */
class TASKSPN_Shortcodes {
	/**
	 * Manage the shortcodes in the platform.
	 *
	 * @since    1.0.0
	 */
	public function taskspn_test($atts) {
    $a = extract(shortcode_atts([
      'user_id' => 0,
      'post_id' => 0,
    ], $atts));

    ob_start();
    ?>
      <div class="taskspn-shortcode-example">
      	Shortcode example
      	<p>User id: <?php echo intval($user_id); ?></p>
      	<p>Post id: <?php echo intval($post_id); ?></p>
      </div>
    <?php
    $taskspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $taskspn_return_string;
	}

  public function taskspn_call_to_action($atts) {
    // echo do_shortcode('[taskspn-call-to-action taskspn_call_to_action_icon="error_outline" taskspn_call_to_action_title="' . esc_html(__('Default title', 'taskspn')) . '" taskspn_call_to_action_content="' . esc_html(__('Default content', 'taskspn')) . '" taskspn_call_to_action_button_link="#" taskspn_call_to_action_button_text="' . esc_html(__('Button text', 'taskspn')) . '" taskspn_call_to_action_button_class="taskspn-class"]');
    $a = extract(shortcode_atts(array(
      'taskspn_call_to_action_class' => '',
      'taskspn_call_to_action_icon' => '',
      'taskspn_call_to_action_title' => '',
      'taskspn_call_to_action_content' => '',
      'taskspn_call_to_action_button_link' => '#',
      'taskspn_call_to_action_button_text' => '',
      'taskspn_call_to_action_button_class' => '',
      'taskspn_call_to_action_button_data_key' => '',
      'taskspn_call_to_action_button_data_value' => '',
      'taskspn_call_to_action_button_blank' => 0,
    ), $atts));

    ob_start();
    ?>
      <div class="taskspn-call-to-action taskspn-text-align-center taskspn-pt-30 taskspn-pb-50 <?php echo esc_attr($taskspn_call_to_action_class); ?>">
        <div class="taskspn-call-to-action-icon">
          <i class="material-icons-outlined taskspn-font-size-75 taskspn-color-main-0"><?php echo esc_html($taskspn_call_to_action_icon); ?></i>
        </div>

        <h4 class="taskspn-call-to-action-title taskspn-text-align-center taskspn-mt-10 taskspn-mb-20"><?php echo esc_html($taskspn_call_to_action_title); ?></h4>
        
        <?php if (!empty($taskspn_call_to_action_content)): ?>
          <p class="taskspn-text-align-center"><?php echo wp_kses_post($taskspn_call_to_action_content); ?></p>
        <?php endif ?>

        <?php if (!empty($taskspn_call_to_action_button_text)): ?>
          <div class="taskspn-text-align-center taskspn-mt-20">
            <a class="taskspn-btn taskspn-btn-transparent taskspn-margin-auto <?php echo esc_attr($taskspn_call_to_action_button_class); ?>" <?php echo ($taskspn_call_to_action_button_blank) ? 'target="_blank"' : ''; ?> href="<?php echo esc_url($taskspn_call_to_action_button_link); ?>" <?php echo (!empty($taskspn_call_to_action_button_data_key) && !empty($taskspn_call_to_action_button_data_value)) ? esc_attr($taskspn_call_to_action_button_data_key) . '="' . esc_attr($taskspn_call_to_action_button_data_value) . '"' : ''; ?>><?php echo esc_html($taskspn_call_to_action_button_text); ?></a>
          </div>
        <?php endif ?>
      </div>
    <?php 
    $taskspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
      return $taskspn_return_string;
  }

  /**
   * Calendar shortcode
   */
  public function taskspn_calendar($atts) {
    $plugin_calendar = new TASKSPN_Calendar();
    return $plugin_calendar->taskspn_calendar_render($atts);
  }

  /**
   * Joinable tasks list shortcode
   *
   * Usage: [taskspn-joinable-tasks]
   */
  public function taskspn_joinable_tasks($atts) {
    $atts = shortcode_atts([
      'public_only' => '1',
    ], $atts);

    $query_args = [
      'fields' => 'ids',
      'numberposts' => -1,
      'post_type' => 'taskspn_task',
      'post_status' => 'publish',
      'orderby' => 'menu_order',
      'order' => 'ASC',
      'meta_query' => [
        [
          'key' => 'taskspn_repeated_from',
          'compare' => 'NOT EXISTS'
        ]
      ]
    ];

    // For logged-in users show all joinable tasks (not limited to public); guests only see public
    if ($atts['public_only'] === '1' && !is_user_logged_in()) {
      $query_args['meta_query'][] = [
        'key' => 'taskspn_task_public',
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
      <div class="taskspn-joinable-tasks taskspn-max-width-700 taskspn-margin-auto">
        <ul class="taskspn-list-style-none taskspn-p-0">
          <?php if (!empty($tasks)): ?>
            <?php foreach ($tasks as $task_id): ?>
              <?php 
                $title = get_the_title($task_id);
                $owners = (new TASKSPN_Post_Type_Task())->taskspn_task_owners($task_id);
                $is_owner = is_user_logged_in() ? in_array(get_current_user_id(), $owners) : false;
              ?>
              <li class="taskspn-bordered taskspn-border-radius-5 taskspn-p-20 taskspn-mb-20" data-taskspn_task-id="<?php echo esc_attr($task_id); ?>">
                <div class="taskspn-display-table taskspn-width-100-percent">
                  <div class="taskspn-display-inline-table taskspn-width-70-percent">
                    <strong><?php echo esc_html($title); ?></strong>
                  </div>
                  <div class="taskspn-display-inline-table taskspn-width-30-percent taskspn-text-align-right">
                    <?php if (!is_user_logged_in()): ?>
                      <span><?php esc_html_e('Log in to join', 'taskspn'); ?></span>
                    <?php elseif ($is_owner): ?>
                      <span class="taskspn-color-green taskspn-font-size-small"><?php esc_html_e('You are an owner', 'taskspn'); ?></span>
                    <?php else: ?>
                      <a href="#" class="taskspn-btn taskspn-btn-mini taskspn-join-task-btn" data-task-id="<?php echo esc_attr($task_id); ?>"><?php esc_html_e('Join task', 'taskspn'); ?></a>
                    <?php endif; ?>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li><?php echo wp_kses_post(do_shortcode('[taskspn-call-to-action taskspn_call_to_action_icon="error_outline" taskspn_call_to_action_title="' . esc_html(__('No tasks found', 'taskspn')) . '" taskspn_call_to_action_content="' . esc_html(__('No joinable tasks found. Add some tasks to the platform to make them joinable.', 'taskspn')) . '"]')); ?></li>
          <?php endif; ?>
        </ul>
      </div>

      <script>
      (function(){
        var container = document.currentScript ? document.currentScript.previousElementSibling : null;
        if(!container) return;
        container.addEventListener('click', function(e){
          var btn = e.target.closest('.taskspn-join-task-btn');
          if(!btn) return;
          e.preventDefault();
          if(btn.dataset.loading === '1') return;
          btn.dataset.loading = '1';
          var taskId = btn.getAttribute('data-task-id');
          var data = new FormData();
          data.append('action', 'taskspn_ajax');
          data.append('taskspn_ajax_type', 'taskspn_task_join');
          data.append('taskspn_task_id', taskId);
          data.append('taskspn_ajax_nonce', (window.taskspn_ajax && taskspn_ajax.taskspn_ajax_nonce) ? taskspn_ajax.taskspn_ajax_nonce : '');
          fetch((window.taskspn_ajax && taskspn_ajax.ajax_url) ? taskspn_ajax.ajax_url : '/wp-admin/admin-ajax.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: data
          }).then(function(r){ return r.json(); }).then(function(resp){
            if(!resp || resp.error_key){
              alert((window.taskspn_i18n && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error');
            } else {
              btn.replaceWith((function(){
                var span = document.createElement('span');
                span.className = 'taskspn-color-green';
                span.textContent = (window.taskspn_i18n && taskspn_i18n.saved_successfully) ? taskspn_i18n.saved_successfully : 'Joined';
                return span;
              })());
            }
          }).catch(function(){
            alert((window.taskspn_i18n && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error');
          }).finally(function(){
            btn.dataset.loading = '0';
          });
        });
      })();
      </script>
    <?php
    $taskspn_return_string = ob_get_contents();
    ob_end_clean();
    return $taskspn_return_string;
  }

  /**
   * Users ranking by completed task hours (admin-only)
   *
   * Usage: [taskspn-users-ranking]
   */
  public function taskspn_users_ranking($atts) {
    if (!current_user_can('manage_options')) {
      return '';
    }

    $atts = shortcode_atts([
      'limit' => 20,
    ], $atts);

    $query_args = [
      'fields' => 'ids',
      'numberposts' => -1,
      'post_type' => 'taskspn_task',
      'post_status' => 'publish',
      'meta_query' => [
        [ 'key' => 'taskspn_task_completed', 'value' => 'on', 'compare' => '=' ],
        [ 'key' => 'taskspn_repeated_from', 'compare' => 'NOT EXISTS' ],
      ],
    ];

    if (class_exists('Polylang')) {
      $query_args['lang'] = pll_current_language('slug');
    }

    $tasks = get_posts($query_args);

    $hours_per_user = [];
    $task_class = new TASKSPN_Post_Type_Task();

    foreach ($tasks as $task_id) {
      $hours = floatval(get_post_meta($task_id, 'taskspn_task_estimated_hours', true));
      if ($hours <= 0) { continue; }
      $owners = $task_class->taskspn_task_owners($task_id);
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
      <div class="taskspn-users-ranking taskspn-max-width-700 taskspn-margin-auto">
        <h4 class="taskspn-mb-20"><?php esc_html_e('Users ranking by completed hours', 'taskspn'); ?></h4>
        <?php if (empty($hours_per_user)): ?>
          <p><?php esc_html_e('No completed hours found.', 'taskspn'); ?></p>
        <?php else: ?>
          <ol class="taskspn-list-style-none taskspn-p-0">
            <?php foreach ($hours_per_user as $user_id => $total_hours): ?>
              <?php $display_name = TASKSPN_Functions_User::taskspn_user_get_name($user_id); ?>
              <li class="taskspn-bordered taskspn-border-radius-5 taskspn-p-10 taskspn-mb-10 taskspn-users-ranking-item taskspn-cursor-pointer" data-user-id="<?php echo esc_attr($user_id); ?>" title="<?php esc_attr_e('Click to view completed tasks', 'taskspn'); ?>">
                <div class="taskspn-display-table taskspn-width-100-percent">
                  <div class="taskspn-display-inline-table taskspn-width-70-percent">
                    <strong><?php echo esc_html($display_name); ?></strong>
                  </div>
                  <div class="taskspn-display-inline-table taskspn-width-30-percent taskspn-text-align-right">
                    <span><?php echo esc_html(number_format($total_hours, 2)); ?> h</span>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>
        <?php endif; ?>
      </div>

      <?php echo wp_kses_post( TASKSPN_Popups::open('<div id="taskspn-users-ranking-popup-content"></div>', ['id' => 'taskspn-users-ranking-popup']) ); ?>

      <script>
      (function(){
        var popupId = 'taskspn-users-ranking-popup';
        document.addEventListener('click', function(e){
          var item = e.target.closest('.taskspn-users-ranking-item');
          if(!item){ return; }
          var userId = item.getAttribute('data-user-id');
          if(!userId){ return; }
          var data = new FormData();
          data.append('action','taskspn_ajax');
          data.append('taskspn_ajax_type','taskspn_users_ranking_user_tasks');
          data.append('user_id', userId);
          data.append('taskspn_ajax_nonce', (window.taskspn_ajax && taskspn_ajax.taskspn_ajax_nonce) ? taskspn_ajax.taskspn_ajax_nonce : '');
          fetch((window.taskspn_ajax && taskspn_ajax.ajax_url) ? taskspn_ajax.ajax_url : '/wp-admin/admin-ajax.php', { method: 'POST', credentials: 'same-origin', body: data })
            .then(function(r){ return r.json(); })
            .then(function(resp){
              if(!resp || resp.error_key){
                alert((window.taskspn_i18n && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error');
                return;
              }
              var popup = document.getElementById(popupId);
              if(!popup){ return; }
              var content = popup.querySelector('#taskspn-users-ranking-popup-content');
              if(content){ content.innerHTML = resp.html; }
              if (typeof TASKSPN_Popups !== 'undefined') {
                // Pass popup id string so taskspn-popups.js resolves jQuery element correctly
                TASKSPN_Popups.open(popupId);
              }
            }).catch(function(){
              alert((window.taskspn_i18n && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error');
            });
        });
      })();
      </script>
    <?php
    $taskspn_return_string = ob_get_contents();
    ob_end_clean();
    return $taskspn_return_string;
  }
}