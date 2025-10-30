<?php
/**
 * Load the plugin Ajax functions.
 *
 * Load the plugin Ajax functions to be executed in background.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    TASKSPN
 * @subpackage TASKSPN/includes
 * @author     Padres en la Nube
 */
class TASKSPN_Ajax {
  /**
   * Load ajax functions.
   *
   * @since    1.0.0
   */
  public function taskspn_ajax_server() {
    if (array_key_exists('taskspn_ajax_type', $_POST)) {
      // Always require nonce verification
      if (!array_key_exists('taskspn_ajax_nonce', $_POST)) {
        echo wp_json_encode([
          'error_key' => 'taskspn_nonce_ajax_error_required',
          'error_content' => esc_html(__('Security check failed: Nonce is required.', 'taskspn')),
        ]);

        exit;
      }

      if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['taskspn_ajax_nonce'])), 'taskspn-nonce')) {
        echo wp_json_encode([
          'error_key' => 'taskspn_nonce_ajax_error_invalid',
          'error_content' => esc_html(__('Security check failed: Invalid nonce.', 'taskspn')),
        ]);

        exit;
      }

      $taskspn_ajax_type = TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['taskspn_ajax_type']));

      $taskspn_ajax_keys = !empty($_POST['taskspn_ajax_keys']) ? array_map(function($key) {
        return array(
          'id' => sanitize_key($key['id']),
          'node' => sanitize_key($key['node']),
          'type' => sanitize_key($key['type']),
          'field_config' => !empty($key['field_config']) ? $key['field_config'] : []
        );
      }, wp_unslash($_POST['taskspn_ajax_keys'])) : [];

      $taskspn_task_id = !empty($_POST['taskspn_task_id']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['taskspn_task_id'])) : 0;
      
      $taskspn_key_value = [];

      if (!empty($taskspn_ajax_keys)) {
        foreach ($taskspn_ajax_keys as $taskspn_key) {
          if (strpos((string)$taskspn_key['id'], '[]') !== false) {
            $taskspn_clear_key = str_replace('[]', '', $taskspn_key['id']);
            ${$taskspn_clear_key} = $taskspn_key_value[$taskspn_clear_key] = [];

            if (!empty($_POST[$taskspn_clear_key])) {
              $unslashed_array = wp_unslash($_POST[$taskspn_clear_key]);
              $sanitized_array = array_map(function($value) use ($taskspn_key) {
                return TASKSPN_Forms::taskspn_sanitizer(
                  $value,
                  $taskspn_key['node'],
                  $taskspn_key['type'],
                  $taskspn_key['field_config']
                );
              }, $unslashed_array);
              
              // filter empty entries
              $sanitized_array = array_filter($sanitized_array, function($v) { return $v !== '' && $v !== null; });
              // generic normalization: ints if all numeric, unique, reindex
              $all_numeric = !empty($sanitized_array) && count(array_filter($sanitized_array, 'is_numeric')) === count($sanitized_array);
              if ($all_numeric) {
                $sanitized_array = array_map('intval', $sanitized_array);
              }
              $sanitized_array = array_values(array_unique($sanitized_array));
              ${$taskspn_clear_key} = $taskspn_key_value[$taskspn_clear_key] = $sanitized_array;
            } else {
              // explicit empty array for multiple fields
              ${$taskspn_clear_key} = [];
              $taskspn_key_value[$taskspn_clear_key] = [];
            }
          } else {
            $sanitized_key = sanitize_key($taskspn_key['id']);
            $taskspn_key_id = !empty($_POST[$sanitized_key]) ? 
              TASKSPN_Forms::taskspn_sanitizer(
                wp_unslash($_POST[$sanitized_key]), 
                $taskspn_key['node'], 
                $taskspn_key['type'],
                $taskspn_key['field_config']
              ) : '';
            ${$taskspn_key['id']} = $taskspn_key_value[$taskspn_key['id']] = $taskspn_key_id;
          }
        }
      }

      switch ($taskspn_ajax_type) {
        case 'taskspn_task_toggle_completed':
          if (!is_user_logged_in()) {
            echo wp_json_encode([
              'error_key' => 'not_logged_in',
              'error_content' => esc_html(__('You must be logged in to update a task.', 'taskspn')),
            ]);
            exit;
          }

          $task_id = intval($taskspn_task_id);
          $task = get_post($task_id);
          if (!$task || $task->post_type !== 'taskspn_task') {
            echo wp_json_encode([
              'error_key' => 'invalid_task',
              'error_content' => esc_html(__('Invalid task.', 'taskspn')),
            ]);
            exit;
          }

          $current = get_post_meta($task_id, 'taskspn_task_completed', true) === 'on';
          $new = !$current;
          update_post_meta($task_id, 'taskspn_task_completed', $new ? 'on' : '');
          echo wp_json_encode([
            'error_key' => '',
            'completed' => $new,
          ]);
          exit;
          break;
        case 'taskspn_task_join':
          if (!is_user_logged_in()) {
            echo wp_json_encode([
              'error_key' => 'not_logged_in',
              'error_content' => esc_html(__('You must be logged in to join a task.', 'taskspn')),
            ]);
            exit;
          }

          $task_id = intval($taskspn_task_id);
          $task = get_post($task_id);
          if (!$task || $task->post_type !== 'taskspn_task' || $task->post_status !== 'publish') {
            echo wp_json_encode([
              'error_key' => 'invalid_task',
              'error_content' => esc_html(__('Invalid task.', 'taskspn')),
            ]);
            exit;
          }

          // If set to public_only in shortcode, tasks likely have meta 'taskspn_task_public' => 'on'. We do not enforce here strictly.
          $owners = get_post_meta($task_id, 'taskspn_task_owners', true);
          $user_id = get_current_user_id();
          if (!is_array($owners)) {
            $owners = empty($owners) ? [] : [intval($owners)];
          }
          if (!in_array($user_id, $owners)) {
            $owners[] = $user_id;
            update_post_meta($task_id, 'taskspn_task_owners', array_values(array_unique(array_map('intval', $owners))));
          }

          echo wp_json_encode([
            'error_key' => '',
            'status' => 'joined',
          ]);
          exit;
          break;
        case 'taskspn_task_view':
          if (!empty($taskspn_task_id)) {
            try {
              $plugin_post_type_taskpn = new TASKSPN_Post_Type_Task();
              $task_html = $plugin_post_type_taskpn->taskspn_task_view(intval($taskspn_task_id));
              
              echo wp_json_encode([
                'error_key' => '', 
                'html' => $task_html, 
              ]);
            } catch (Exception $e) {
              error_log('TASKSPN Error in taskspn_task_view: ' . $e->getMessage());
              echo wp_json_encode([
                'error_key' => 'taskspn_task_view_error', 
                'error_content' => esc_html(__('An error occurred while showing the Task.', 'taskspn')), 
              ]);
            }

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'taskspn_task_view_error', 
              'error_content' => esc_html(__('Task ID is required.', 'taskspn')), 
            ]);

            exit;
          }
          break;
        case 'taskspn_task_edit':
          // Check if the Task exists
          $taskspn_task = get_post($taskspn_task_id);
          

          if (!empty($taskspn_task_id)) {
            $plugin_post_type_taskpn = new TASKSPN_Post_Type_Task();
            echo wp_json_encode([
              'error_key' => '', 
              'html' => $plugin_post_type_taskpn->taskspn_task_edit($taskspn_task_id), 
            ]);

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'taskspn_task_edit_error', 
              'error_content' => esc_html(__('An error occurred while showing the Task.', 'taskspn')), 
            ]);

            exit;
          }
          break;
        case 'taskspn_task_new':
          if (!is_user_logged_in()) {
            echo wp_json_encode([
              'error_key' => 'not_logged_in',
              'error_content' => esc_html(__('You must be logged in to create a new asset.', 'taskspn')),
            ]);
            exit;
          }

          $plugin_post_type_taskpn = new TASKSPN_Post_Type_Task();

          echo wp_json_encode([
            'error_key' => '', 
            'html' => $plugin_post_type_taskpn->taskspn_task_new($taskspn_task_id), 
          ]);

          exit;
          break;
        case 'taskspn_task_check':
          if (!empty($taskspn_task_id)) {
            $plugin_post_type_taskpn = new TASKSPN_Post_Type_Task();
            echo wp_json_encode([
              'error_key' => '', 
              'html' => $plugin_post_type_taskpn->taskspn_task_check($taskspn_task_id), 
            ]);

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'taskspn_task_check_error', 
              'error_content' => esc_html(__('An error occurred while checking the Task.', 'taskspn')), 
              ]);

            exit;
          }
          break;
        case 'taskspn_task_duplicate':
          if (!empty($taskspn_task_id)) {
            $plugin_post_type_post = new TASKSPN_Functions_Post();
            $plugin_post_type_post->taskspn_duplicate_post($taskspn_task_id, 'publish');
            
            $plugin_post_type_taskpn = new TASKSPN_Post_Type_Task();
            echo wp_json_encode([
              'error_key' => '', 
              'html' => $plugin_post_type_taskpn->taskspn_task_list(), 
            ]);

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'taskspn_task_duplicate_error', 
              'error_content' => esc_html(__('An error occurred while duplicating the Task.', 'taskspn')), 
            ]);

            exit;
          }
          break;
        case 'taskspn_task_remove':
          if (!empty($taskspn_task_id)) {
            wp_delete_post($taskspn_task_id, true);

            $plugin_post_type_taskpn = new TASKSPN_Post_Type_Task();
            echo wp_json_encode([
              'error_key' => '', 
              'html' => $plugin_post_type_taskpn->taskspn_task_list(), 
            ]);

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'taskspn_task_remove_error', 
              'error_content' => esc_html(__('An error occurred while removing the Task.', 'taskspn')), 
            ]);

            exit;
          }
          break;
        case 'taskspn_task_share':
          $plugin_post_type_taskpn = new TASKSPN_Post_Type_Task();
          echo wp_json_encode([
            'error_key' => '', 
            'html' => $plugin_post_type_taskpn->taskspn_task_share(), 
          ]);

          exit;
          break;
        case 'taskspn_calendar_view':
          $calendar_view = !empty($_POST['calendar_view']) ? sanitize_text_field(wp_unslash($_POST['calendar_view'])) : 'month';
          $calendar_year = !empty($_POST['calendar_year']) ? intval($_POST['calendar_year']) : date('Y');
          $calendar_month = !empty($_POST['calendar_month']) ? intval($_POST['calendar_month']) : date('m');
          $calendar_day = !empty($_POST['calendar_day']) ? intval($_POST['calendar_day']) : date('d');
          
          $plugin_calendar = new TASKSPN_Calendar();
          $calendar_html = $plugin_calendar->taskspn_calendar_render_view_content($calendar_view, $calendar_year, $calendar_month, $calendar_day);
          
          echo wp_json_encode([
            'error_key' => '', 
            'html' => $calendar_html,
            'view' => $calendar_view,
            'year' => $calendar_year,
            'month' => $calendar_month,
            'day' => $calendar_day
          ]);

          exit;
          break;
      }

      echo wp_json_encode([
        'error_key' => 'taskspn_save_error', 
      ]);

      exit;
    }
  }
}