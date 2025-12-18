<?php
/**
 * Load the plugin Ajax functions.
 *
 * Load the plugin Ajax functions to be executed in background.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/includes
 * @author     Padres en la Nube
 */
class PN_TASKS_MANAGER_Ajax {
  /**
   * Load ajax functions.
   *
   * @since    1.0.0
   */
  public function pn_tasks_manager_ajax_server() {
    $raw_action = isset($_POST['action']) ? wp_unslash($_POST['action']) : '';
    $posted_action = $raw_action !== '' ? sanitize_text_field($raw_action) : '';
    $raw_ajax_type = isset($_POST['pn_tasks_manager_ajax_type']) ? wp_unslash($_POST['pn_tasks_manager_ajax_type']) : '';
    $sanitized_ajax_type = $raw_ajax_type !== '' ? PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer($raw_ajax_type) : '';

    // Clean any existing output buffers first
    while (ob_get_level() > 0) {
      ob_end_clean();
    }
    
    // Set proper headers for JSON response BEFORE any output
    if (!headers_sent()) {
      header('Content-Type: application/json; charset=utf-8');
      status_header(200); // Always return 200, errors are in JSON
    }
    
    // Check if action matches (WordPress requires this)
    if ($posted_action !== 'pn_tasks_manager_ajax') {
      echo wp_json_encode([
        'error_key' => 'pn_tasks_manager_ajax_action_error',
        'error_content' => esc_html(__('Invalid action.', 'pn-tasks-manager')),
      ]);
      exit;
    }
    
    if (array_key_exists('pn_tasks_manager_ajax_type', $_POST)) {
      // Always require nonce verification
      if (!array_key_exists('pn_tasks_manager_ajax_nonce', $_POST) || empty($_POST['pn_tasks_manager_ajax_nonce'])) {
        echo wp_json_encode([
          'error_key' => 'pn_tasks_manager_nonce_ajax_error_required',
          'error_content' => esc_html(__('Security check failed: Nonce is required.', 'pn-tasks-manager')),
        ]);

        exit;
      }

      $nonce_value = isset($_POST['pn_tasks_manager_ajax_nonce']) ? sanitize_text_field(wp_unslash($_POST['pn_tasks_manager_ajax_nonce'])) : '';
      $nonce_verified = wp_verify_nonce($nonce_value, 'pn-tasks-manager-nonce');
      
      if (!$nonce_verified) {
        echo wp_json_encode([
          'error_key' => 'pn_tasks_manager_nonce_ajax_error_invalid',
          'error_content' => esc_html(__('Security check failed: Invalid nonce.', 'pn-tasks-manager')),
        ]);

        exit;
      }

      $pn_tasks_manager_ajax_type = $sanitized_ajax_type;

      $pn_tasks_manager_ajax_keys = !empty($_POST['pn_tasks_manager_ajax_keys']) && isset($_POST['pn_tasks_manager_ajax_keys']) ? array_map(function($key) {
        return array(
          'id' => sanitize_key($key['id']),
          'node' => sanitize_key($key['node']),
          'type' => sanitize_key($key['type']),
          'field_config' => !empty($key['field_config']) ? $key['field_config'] : []
        );
      }, wp_unslash($_POST['pn_tasks_manager_ajax_keys'])) : [];

      $pn_tasks_manager_task_id = !empty($_POST['pn_tasks_manager_task_id']) && isset($_POST['pn_tasks_manager_task_id']) ? PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(wp_unslash($_POST['pn_tasks_manager_task_id'])) : 0;
      
      $pn_tasks_manager_key_value = [];

      if (!empty($pn_tasks_manager_ajax_keys)) {
        foreach ($pn_tasks_manager_ajax_keys as $pn_tasks_manager_key) {
          if (strpos((string)$pn_tasks_manager_key['id'], '[]') !== false) {
            $pn_tasks_manager_clear_key = str_replace('[]', '', $pn_tasks_manager_key['id']);
            ${$pn_tasks_manager_clear_key} = $pn_tasks_manager_key_value[$pn_tasks_manager_clear_key] = [];

            if (!empty($_POST[$pn_tasks_manager_clear_key]) && isset($_POST[$pn_tasks_manager_clear_key])) {
              $unslashed_array = wp_unslash($_POST[$pn_tasks_manager_clear_key]);
              $sanitized_array = array_map(function($value) use ($pn_tasks_manager_key) {
                return PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(
                  $value,
                  $pn_tasks_manager_key['node'],
                  $pn_tasks_manager_key['type'],
                  $pn_tasks_manager_key['field_config']
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
              ${$pn_tasks_manager_clear_key} = $pn_tasks_manager_key_value[$pn_tasks_manager_clear_key] = $sanitized_array;
            } else {
              // explicit empty array for multiple fields
              ${$pn_tasks_manager_clear_key} = [];
              $pn_tasks_manager_key_value[$pn_tasks_manager_clear_key] = [];
            }
          } else {
            $sanitized_key = sanitize_key($pn_tasks_manager_key['id']);
            $pn_tasks_manager_key_id = !empty($_POST[$sanitized_key]) && isset($_POST[$sanitized_key]) ? 
              PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(
                wp_unslash($_POST[$sanitized_key]), 
                $pn_tasks_manager_key['node'], 
                $pn_tasks_manager_key['type'],
                $pn_tasks_manager_key['field_config']
              ) : '';
            ${$pn_tasks_manager_key['id']} = $pn_tasks_manager_key_value[$pn_tasks_manager_key['id']] = $pn_tasks_manager_key_id;
          }
        }
      }

      switch ($pn_tasks_manager_ajax_type) {
        case 'pn_tasks_manager_task_toggle_completed':
          if (!is_user_logged_in()) {
            echo wp_json_encode([
              'error_key' => 'not_logged_in',
              'error_content' => esc_html(__('You must be logged in to update a task.', 'pn-tasks-manager')),
            ]);
            exit;
          }

          $task_id = intval($pn_tasks_manager_task_id);
          $task = get_post($task_id);
          if (!$task || $task->post_type !== 'pn_tasks_task') {
            echo wp_json_encode([
              'error_key' => 'invalid_task',
              'error_content' => esc_html(__('Invalid task.', 'pn-tasks-manager')),
            ]);
            exit;
          }

          $task_class = new PN_TASKS_MANAGER_Post_Type_Task();
          $new = $task_class->pn_tasks_manager_task_toggle_completed($task_id, get_current_user_id());
          echo wp_json_encode([
            'error_key' => '',
            'completed' => $new,
          ]);
          exit;
          break;
        case 'pn_tasks_manager_users_ranking_user_tasks':
          $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
          if (empty($user_id)) {
            echo wp_json_encode([
              'error_key' => 'invalid_user',
              'error_content' => esc_html(__('Invalid user.', 'pn-tasks-manager')),
            ]);
            exit;
          }

          // Fetch completed tasks and filter by ownership
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

          $items = [];
          $task_class = new PN_TASKS_MANAGER_Post_Type_Task();
          foreach ($tasks as $tid) {
            // Use canonical owners resolver to include author and normalize formats
            $owners = $task_class->pn_tasks_manager_task_owners($tid);
            if (!in_array(intval($user_id), array_map('intval', $owners), true)) { continue; }

            $hours = floatval(get_post_meta($tid, 'pn_tasks_manager_task_estimated_hours', true));
            $completed_at = get_post_meta($tid, 'pn_tasks_manager_task_completed_at', true);
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
          <div class="pn-tasks-manager-p-20">
            <h4 class="pn-tasks-manager-mb-20"><?php esc_html_e('Completed tasks', 'pn-tasks-manager'); ?></h4>
            <?php if (empty($items)) : ?>
              <p><?php esc_html_e('No completed tasks found for this user.', 'pn-tasks-manager'); ?></p>
            <?php else : ?>
              <ul class="pn-tasks-manager-list-style-none pn-tasks-manager-p-0">
                <?php foreach ($items as $it) : ?>
                  <li class="pn-tasks-manager-bordered pn-tasks-manager-border-radius-5 pn-tasks-manager-p-10 pn-tasks-manager-mb-10">
                    <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                      <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-30-percent">
                        <strong><?php echo esc_html($it['title']); ?></strong>
                      </div>
                      <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-40-percent pn-tasks-manager-text-align-right">
                        <i class="material-icons-outlined pn-tasks-manager-font-size-20 pn-tasks-manager-vertical-align-middle pn-tasks-manager-mr-10">calendar_today</i>
                        <small class="pn-tasks-manager-font-size-small"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($it['completed_at']))); ?></small>
                      </div>
                      <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-30-percent">
                        <i class="material-icons-outlined pn-tasks-manager-font-size-20 pn-tasks-manager-vertical-align-middle pn-tasks-manager-mr-10">timer</i>
                        <span class="pn-tasks-manager-mr-10"><?php echo esc_html(number_format((float)$it['hours'], 2)); ?> <?php esc_html_e('hours', 'pn-tasks-manager'); ?></span>
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
        case 'pn_tasks_manager_task_join':
          if (!is_user_logged_in()) {
            echo wp_json_encode([
              'error_key' => 'not_logged_in',
              'error_content' => esc_html(__('You must be logged in to join a task.', 'pn-tasks-manager')),
            ]);
            exit;
          }

          $task_id = intval($pn_tasks_manager_task_id);
          $task = get_post($task_id);
          if (!$task || $task->post_type !== 'pn_tasks_task' || $task->post_status !== 'publish') {
            echo wp_json_encode([
              'error_key' => 'invalid_task',
              'error_content' => esc_html(__('Invalid task.', 'pn-tasks-manager')),
            ]);
            exit;
          }

          // If set to public_only in shortcode, tasks likely have meta 'pn_tasks_manager_task_public' => 'on'. We do not enforce here strictly.
          $owners = get_post_meta($task_id, 'pn_tasks_manager_task_owners', true);
          $user_id = get_current_user_id();
          if (!is_array($owners)) {
            $owners = empty($owners) ? [] : [intval($owners)];
          }
          if (!in_array($user_id, $owners)) {
            $owners[] = $user_id;
            update_post_meta($task_id, 'pn_tasks_manager_task_owners', array_values(array_unique(array_map('intval', $owners))));
          }

          echo wp_json_encode([
            'error_key' => '',
            'status' => 'joined',
          ]);
          exit;
          break;
        case 'pn_tasks_manager_task_view':
          // Ensure we have proper headers for JSON response
          // Set headers before any output
          if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            status_header(200);
          }
          
          if (!empty($pn_tasks_manager_task_id)) {
            // Clean any existing output buffers
            while (ob_get_level() > 0) {
              ob_end_clean();
            }
            
            try {
              // Clean any existing output buffers first
              while (ob_get_level() > 0) {
                ob_end_clean();
              }
              
              $plugin_post_type_taskpn = new PN_TASKS_MANAGER_Post_Type_Task();
              
              // Call pn_tasks_manager_task_view - it uses its own output buffering internally
              $task_html = $plugin_post_type_taskpn->pn_tasks_manager_task_view(intval($pn_tasks_manager_task_id));
              
              if (!empty($task_html) && strpos($task_html, 'error crítico') === false && strpos($task_html, 'critical error') === false && strpos($task_html, 'Ha habido un error crítico') === false) {
                $response_payload = [
                  'error_key' => '',
                  'html' => $task_html,
                ];

                if (wp_json_encode($response_payload) !== false) {
                  wp_send_json($response_payload);
                } else {
                  echo wp_json_encode([
                    'error_key' => 'pn_tasks_manager_task_view_error', 
                    'error_content' => esc_html(__('An error occurred while encoding the response.', 'pn-tasks-manager')), 
                  ]);
                }
              } else {
                echo wp_json_encode([
                  'error_key' => 'pn_tasks_manager_task_view_error', 
                  'error_content' => esc_html(__('An error occurred while showing the Task.', 'pn-tasks-manager')), 
                ]);
              }
            } catch (Exception $e) {
              // Clean any output buffer
              while (ob_get_level() > 0) {
                ob_end_clean();
              }
              echo wp_json_encode([
                'error_key' => 'pn_tasks_manager_task_view_error', 
                'error_content' => esc_html(__('An error occurred while showing the Task.', 'pn-tasks-manager')), 
              ]);
            } catch (Error $e) {
              // Clean any output buffer
              while (ob_get_level() > 0) {
                ob_end_clean();
              }
              echo wp_json_encode([
                'error_key' => 'pn_tasks_manager_task_view_error', 
                'error_content' => esc_html(__('An error occurred while showing the Task.', 'pn-tasks-manager')), 
              ]);
            }

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'pn_tasks_manager_task_view_error', 
              'error_content' => esc_html(__('Task ID is required.', 'pn-tasks-manager')), 
            ]);

            exit;
          }
          break;
        case 'pn_tasks_manager_task_edit':
          // Check if the Task exists
          $pn_tasks_manager_task = get_post($pn_tasks_manager_task_id);
          

          if (!empty($pn_tasks_manager_task_id)) {
            $plugin_post_type_taskpn = new PN_TASKS_MANAGER_Post_Type_Task();
            echo wp_json_encode([
              'error_key' => '', 
              'html' => $plugin_post_type_taskpn->pn_tasks_manager_task_edit($pn_tasks_manager_task_id), 
            ]);

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'pn_tasks_manager_task_edit_error', 
              'error_content' => esc_html(__('An error occurred while showing the Task.', 'pn-tasks-manager')), 
            ]);

            exit;
          }
          break;
        case 'pn_tasks_manager_task_new':
          if (!is_user_logged_in()) {
            echo wp_json_encode([
              'error_key' => 'not_logged_in',
              'error_content' => esc_html(__('You must be logged in to create a new asset.', 'pn-tasks-manager')),
            ]);
            exit;
          }

          $plugin_post_type_taskpn = new PN_TASKS_MANAGER_Post_Type_Task();

          echo wp_json_encode([
            'error_key' => '', 
            'html' => $plugin_post_type_taskpn->pn_tasks_manager_task_new($pn_tasks_manager_task_id), 
          ]);

          exit;
          break;
        case 'pn_tasks_manager_task_check':
          // Ensure we have proper headers for JSON response
          if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            status_header(200);
          }
          
          if (!empty($pn_tasks_manager_task_id)) {
            // Clean any existing output buffers
            while (ob_get_level() > 0) {
              ob_end_clean();
            }
            
            try {
              $plugin_post_type_taskpn = new PN_TASKS_MANAGER_Post_Type_Task();
              $task_html = $plugin_post_type_taskpn->pn_tasks_manager_task_check(intval($pn_tasks_manager_task_id));
              
              if (!empty($task_html)) {
                $response_payload = [
                  'error_key' => '',
                  'html' => $task_html,
                ];
                
                if (wp_json_encode($response_payload) !== false) {
                  wp_send_json($response_payload);
                } else {
                  echo wp_json_encode([
                    'error_key' => 'pn_tasks_manager_task_check_error', 
                    'error_content' => esc_html(__('An error occurred while encoding the response.', 'pn-tasks-manager')), 
                  ]);
                }
              } else {
                echo wp_json_encode([
                  'error_key' => 'pn_tasks_manager_task_check_error', 
                  'error_content' => esc_html(__('An error occurred while checking the Task.', 'pn-tasks-manager')), 
                ]);
              }
            } catch (Exception $e) {
              while (ob_get_level() > 0) {
                ob_end_clean();
              }
              echo wp_json_encode([
                'error_key' => 'pn_tasks_manager_task_check_error', 
                'error_content' => esc_html(__('An error occurred while checking the Task.', 'pn-tasks-manager')), 
              ]);
            } catch (Error $e) {
              while (ob_get_level() > 0) {
                ob_end_clean();
              }
              echo wp_json_encode([
                'error_key' => 'pn_tasks_manager_task_check_error', 
                'error_content' => esc_html(__('An error occurred while checking the Task.', 'pn-tasks-manager')), 
              ]);
            }

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'pn_tasks_manager_task_check_error', 
              'error_content' => esc_html(__('Task ID is required.', 'pn-tasks-manager')), 
            ]);

            exit;
          }
          break;
        case 'pn_tasks_manager_task_duplicate':
          if (!empty($pn_tasks_manager_task_id)) {
            $plugin_post_type_post = new PN_TASKS_MANAGER_Functions_Post();
            $plugin_post_type_post->pn_tasks_manager_duplicate_post($pn_tasks_manager_task_id, 'publish');
            
            $plugin_post_type_taskpn = new PN_TASKS_MANAGER_Post_Type_Task();
            $orderby = isset($_POST['orderby']) ? sanitize_text_field(wp_unslash($_POST['orderby'])) : 'date';
            echo wp_json_encode([
              'error_key' => '', 
              'html' => $plugin_post_type_taskpn->pn_tasks_manager_task_list($orderby), 
            ]);

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'pn_tasks_manager_task_duplicate_error', 
              'error_content' => esc_html(__('An error occurred while duplicating the Task.', 'pn-tasks-manager')), 
            ]);

            exit;
          }
          break;
        case 'pn_tasks_manager_task_remove':
          if (!empty($pn_tasks_manager_task_id)) {
            wp_delete_post($pn_tasks_manager_task_id, true);

            $plugin_post_type_taskpn = new PN_TASKS_MANAGER_Post_Type_Task();
            $orderby = isset($_POST['orderby']) ? sanitize_text_field(wp_unslash($_POST['orderby'])) : 'date';
            echo wp_json_encode([
              'error_key' => '', 
              'html' => $plugin_post_type_taskpn->pn_tasks_manager_task_list($orderby), 
            ]);

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'pn_tasks_manager_task_remove_error', 
              'error_content' => esc_html(__('An error occurred while removing the Task.', 'pn-tasks-manager')), 
            ]);

            exit;
          }
          break;
        case 'pn_tasks_manager_task_list_sort':
          $orderby = isset($_POST['orderby']) ? sanitize_text_field(wp_unslash($_POST['orderby'])) : 'date';
          $plugin_post_type_taskpn = new PN_TASKS_MANAGER_Post_Type_Task();
          echo wp_json_encode([
            'error_key' => '', 
            'html' => $plugin_post_type_taskpn->pn_tasks_manager_task_list($orderby), 
          ]);

          exit;
          break;
        case 'pn_tasks_manager_task_share':
          $plugin_post_type_taskpn = new PN_TASKS_MANAGER_Post_Type_Task();
          echo wp_json_encode([
            'error_key' => '', 
            'html' => $plugin_post_type_taskpn->pn_tasks_manager_task_share(), 
          ]);

          exit;
          break;
        case 'pn_tasks_manager_calendar_view':
          $calendar_view = !empty($_POST['calendar_view']) ? sanitize_text_field(wp_unslash($_POST['calendar_view'])) : 'month';
          $calendar_year = !empty($_POST['calendar_year']) ? intval(wp_unslash($_POST['calendar_year'])) : gmdate('Y');
          $calendar_month = !empty($_POST['calendar_month']) ? intval(wp_unslash($_POST['calendar_month'])) : gmdate('m');
          $calendar_day = !empty($_POST['calendar_day']) ? intval(wp_unslash($_POST['calendar_day'])) : gmdate('d');
          $hide_others = !empty($_POST['hide_others']) ? (bool) intval(wp_unslash($_POST['hide_others'])) : false;
          
          $plugin_calendar = new PN_TASKS_MANAGER_Calendar();
          $calendar_html = $plugin_calendar->pn_tasks_manager_calendar_render_view_content($calendar_view, $calendar_year, $calendar_month, $calendar_day, $hide_others);
          
          echo wp_json_encode([
            'error_key' => '', 
            'html' => $calendar_html,
            'view' => $calendar_view,
            'year' => $calendar_year,
            'month' => $calendar_month,
            'day' => $calendar_day,
            'hide_others' => $hide_others ? 1 : 0
          ]);

          exit;
          break;
        case 'pn_tasks_manager_create_taxonomy_term':
          if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('You must be logged in to create a category.', 'pn-tasks-manager')]);
          }

          // Verify nonce
          if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'pn-tasks-manager-nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'pn-tasks-manager')]);
          }

          $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field(wp_unslash($_POST['taxonomy'])) : '';
          $term_name = isset($_POST['term_name']) ? sanitize_text_field(wp_unslash($_POST['term_name'])) : '';

          if (empty($taxonomy) || empty($term_name)) {
            wp_send_json_error(['message' => __('Taxonomy and term name are required.', 'pn-tasks-manager')]);
          }

          // Check if term already exists
          $existing_term = get_term_by('name', $term_name, $taxonomy);
          if ($existing_term) {
            wp_send_json_success([
              'term_id' => $existing_term->term_id,
              'message' => __('Category already exists.', 'pn-tasks-manager')
            ]);
          }

          // Create new term
          $term_result = wp_insert_term($term_name, $taxonomy);
          
          if (is_wp_error($term_result)) {
            wp_send_json_error(['message' => $term_result->get_error_message()]);
          }

          wp_send_json_success([
            'term_id' => $term_result['term_id'],
            'message' => __('Category created successfully.', 'pn-tasks-manager')
          ]);
          break;
      }

      echo wp_json_encode([
        'error_key' => 'pn_tasks_manager_save_error', 
      ]);

      exit;
    }
  }

  /**
   * Handle AJAX request for creating taxonomy terms
   * 
   * @since    1.0.0
   */
  public function pn_tasks_manager_create_taxonomy_term_ajax() {
    // Set proper headers for JSON response
    if (!headers_sent()) {
      header('Content-Type: application/json; charset=utf-8');
      status_header(200);
    }

    if (!is_user_logged_in()) {
      wp_send_json_error(['message' => __('You must be logged in to create a category.', 'pn-tasks-manager')]);
    }

    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'pn-tasks-manager-nonce')) {
      wp_send_json_error(['message' => __('Security check failed.', 'pn-tasks-manager')]);
    }

    $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field(wp_unslash($_POST['taxonomy'])) : '';
    $term_name = isset($_POST['term_name']) ? sanitize_text_field(wp_unslash($_POST['term_name'])) : '';

    if (empty($taxonomy) || empty($term_name)) {
      wp_send_json_error(['message' => __('Taxonomy and term name are required.', 'pn-tasks-manager')]);
    }

    // Check if term already exists
    $existing_term = get_term_by('name', $term_name, $taxonomy);
    if ($existing_term) {
      wp_send_json_success([
        'term_id' => $existing_term->term_id,
        'message' => __('Category already exists.', 'pn-tasks-manager')
      ]);
    }

    // Create new term
    $term_result = wp_insert_term($term_name, $taxonomy);
    
    if (is_wp_error($term_result)) {
      wp_send_json_error(['message' => $term_result->get_error_message()]);
    }

    wp_send_json_success([
      'term_id' => $term_result['term_id'],
      'message' => __('Category created successfully.', 'pn-tasks-manager')
    ]);
  }
}