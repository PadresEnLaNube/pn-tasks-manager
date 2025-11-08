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
    // Set proper headers for JSON response
    header('Content-Type: application/json; charset=utf-8');
    status_header(200); // Always return 200, errors are in JSON
    
    if (array_key_exists('taskspn_ajax_type', $_POST)) {
      // Always require nonce verification
      if (!array_key_exists('taskspn_ajax_nonce', $_POST) || empty($_POST['taskspn_ajax_nonce'])) {
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

      $taskspn_ajax_type = isset($_POST['taskspn_ajax_type']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['taskspn_ajax_type'])) : '';

      $taskspn_ajax_keys = !empty($_POST['taskspn_ajax_keys']) && isset($_POST['taskspn_ajax_keys']) ? array_map(function($key) {
        return array(
          'id' => sanitize_key($key['id']),
          'node' => sanitize_key($key['node']),
          'type' => sanitize_key($key['type']),
          'field_config' => !empty($key['field_config']) ? $key['field_config'] : []
        );
      }, wp_unslash($_POST['taskspn_ajax_keys'])) : [];

      $taskspn_task_id = !empty($_POST['taskspn_task_id']) && isset($_POST['taskspn_task_id']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['taskspn_task_id'])) : 0;
      
      $taskspn_key_value = [];

      if (!empty($taskspn_ajax_keys)) {
        foreach ($taskspn_ajax_keys as $taskspn_key) {
          if (strpos((string)$taskspn_key['id'], '[]') !== false) {
            $taskspn_clear_key = str_replace('[]', '', $taskspn_key['id']);
            ${$taskspn_clear_key} = $taskspn_key_value[$taskspn_clear_key] = [];

            if (!empty($_POST[$taskspn_clear_key]) && isset($_POST[$taskspn_clear_key])) {
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
            $taskspn_key_id = !empty($_POST[$sanitized_key]) && isset($_POST[$sanitized_key]) ? 
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

          $task_class = new TASKSPN_Post_Type_Task();
          $new = $task_class->taskspn_task_toggle_completed($task_id, get_current_user_id());
          echo wp_json_encode([
            'error_key' => '',
            'completed' => $new,
          ]);
          exit;
          break;
        case 'taskspn_users_ranking_user_tasks':
          $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
          if (empty($user_id)) {
            echo wp_json_encode([
              'error_key' => 'invalid_user',
              'error_content' => esc_html(__('Invalid user.', 'taskspn')),
            ]);
            exit;
          }

          // Fetch completed tasks and filter by ownership
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

          $items = [];
          $task_class = new TASKSPN_Post_Type_Task();
          foreach ($tasks as $tid) {
            // Use canonical owners resolver to include author and normalize formats
            $owners = $task_class->taskspn_task_owners($tid);
            if (!in_array(intval($user_id), array_map('intval', $owners), true)) { continue; }

            $hours = floatval(get_post_meta($tid, 'taskspn_task_estimated_hours', true));
            $completed_at = get_post_meta($tid, 'taskspn_task_completed_at', true);
            if (empty($completed_at)) {
              $p = get_post($tid);
              $completed_at = $p ? $p->post_modified : '';
            }
            $items[] = [
              'title' => get_the_title($tid),
              'hours' => $hours,
              'completed_at' => $completed_at,
              'id' => $tid,
            ];
          }

          // Sort by completion time desc
          usort($items, function($a, $b) {
            return strtotime($b['completed_at']) <=> strtotime($a['completed_at']);
          });

          ob_start();
          ?>
          <div class="taskspn-p-20">
            <h4 class="taskspn-mb-20"><?php esc_html_e('Completed tasks', 'taskspn'); ?></h4>
            <?php if (empty($items)) : ?>
              <p><?php esc_html_e('No completed tasks found for this user.', 'taskspn'); ?></p>
            <?php else : ?>
              <ul class="taskspn-list-style-none taskspn-p-0">
                <?php foreach ($items as $it) : ?>
                  <li class="taskspn-bordered taskspn-border-radius-5 taskspn-p-10 taskspn-mb-10">
                    <div class="taskspn-display-table taskspn-width-100-percent">
                      <div class="taskspn-display-inline-table taskspn-width-30-percent">
                        <strong><?php echo esc_html($it['title']); ?></strong>
                      </div>
                      <div class="taskspn-display-inline-table taskspn-width-40-percent taskspn-text-align-right">
                        <i class="material-icons-outlined taskspn-font-size-20 taskspn-vertical-align-middle taskspn-mr-10">calendar_today</i>
                        <small class="taskspn-font-size-small"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($it['completed_at']))); ?></small>
                      </div>
                      <div class="taskspn-display-inline-table taskspn-width-30-percent">
                        <i class="material-icons-outlined taskspn-font-size-20 taskspn-vertical-align-middle taskspn-mr-10">timer</i>
                        <span class="taskspn-mr-10"><?php echo esc_html(number_format((float)$it['hours'], 2)); ?> <?php esc_html_e('hours', 'taskspn'); ?></span>
                      </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
          <?php
          $html = ob_get_clean();
          echo wp_json_encode([
            'error_key' => '',
            'html' => $html,
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
          // Ensure we have proper headers for JSON response
          header('Content-Type: application/json; charset=utf-8');
          status_header(200); // Ensure we return 200 even on error
          
          if (!empty($taskspn_task_id)) {
            try {
              // Use output buffering to catch any unexpected output
              ob_start();
              
              $plugin_post_type_taskpn = new TASKSPN_Post_Type_Task();
              $task_html = $plugin_post_type_taskpn->taskspn_task_view(intval($taskspn_task_id));
              
              // Clean any unexpected output
              $unexpected_output = ob_get_clean();
              
              // Only use the HTML if it's not empty and doesn't contain error messages
              if (!empty($task_html) && strpos($task_html, 'error crítico') === false && strpos($task_html, 'critical error') === false) {
                $json_response = wp_json_encode([
                  'error_key' => '', 
                  'html' => $task_html, 
                ]);
                
                if ($json_response !== false) {
                  echo $json_response;
                } else {
                  echo wp_json_encode([
                    'error_key' => 'taskspn_task_view_error', 
                    'error_content' => esc_html(__('An error occurred while encoding the response.', 'taskspn')), 
                  ]);
                }
              } else {
                echo wp_json_encode([
                  'error_key' => 'taskspn_task_view_error', 
                  'error_content' => esc_html(__('An error occurred while showing the Task.', 'taskspn')), 
                ]);
              }
            } catch (Exception $e) {
              // Clean any output buffer
              if (ob_get_level() > 0) {
                ob_end_clean();
              }
              // error_log('TASKSPN Error in taskspn_task_view: ' . $e->getMessage());
              echo wp_json_encode([
                'error_key' => 'taskspn_task_view_error', 
                'error_content' => esc_html(__('An error occurred while showing the Task.', 'taskspn')), 
              ]);
            } catch (Error $e) {
              // Clean any output buffer
              if (ob_get_level() > 0) {
                ob_end_clean();
              }
              // error_log('TASKSPN Fatal Error in taskspn_task_view: ' . $e->getMessage());
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
          $calendar_year = !empty($_POST['calendar_year']) ? intval(wp_unslash($_POST['calendar_year'])) : gmdate('Y');
          $calendar_month = !empty($_POST['calendar_month']) ? intval(wp_unslash($_POST['calendar_month'])) : gmdate('m');
          $calendar_day = !empty($_POST['calendar_day']) ? intval(wp_unslash($_POST['calendar_day'])) : gmdate('d');
          
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
        case 'taskspn_create_taxonomy_term':
          if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('You must be logged in to create a category.', 'taskspn')]);
          }

          // Verify nonce
          if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'taskspn-nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'taskspn')]);
          }

          $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field(wp_unslash($_POST['taxonomy'])) : '';
          $term_name = isset($_POST['term_name']) ? sanitize_text_field(wp_unslash($_POST['term_name'])) : '';

          if (empty($taxonomy) || empty($term_name)) {
            wp_send_json_error(['message' => __('Taxonomy and term name are required.', 'taskspn')]);
          }

          // Check if term already exists
          $existing_term = get_term_by('name', $term_name, $taxonomy);
          if ($existing_term) {
            wp_send_json_success([
              'term_id' => $existing_term->term_id,
              'message' => __('Category already exists.', 'taskspn')
            ]);
          }

          // Create new term
          $term_result = wp_insert_term($term_name, $taxonomy);
          
          if (is_wp_error($term_result)) {
            wp_send_json_error(['message' => $term_result->get_error_message()]);
          }

          wp_send_json_success([
            'term_id' => $term_result['term_id'],
            'message' => __('Category created successfully.', 'taskspn')
          ]);
          break;
      }

      echo wp_json_encode([
        'error_key' => 'taskspn_save_error', 
      ]);

      exit;
    }
  }
}