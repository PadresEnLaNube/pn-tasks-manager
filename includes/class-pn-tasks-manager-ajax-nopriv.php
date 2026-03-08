<?php
/**
 * Load the plugin no private Ajax functions.
 *
 * Load the plugin no private Ajax functions to be executed in background.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/includes
 * @author     Padres en la Nube
 */
class PN_TASKS_MANAGER_Ajax_Nopriv {
  /**
   * Load the plugin templates.
   *
   * @since    1.0.0
   */
  public function pn_tasks_manager_ajax_nopriv_server() {
    // Clean any existing output buffers first
    while (ob_get_level() > 0) {
      ob_end_clean();
    }
    
    // Set proper headers for JSON response first
    if (!headers_sent()) {
      header('Content-Type: application/json; charset=utf-8');
      status_header(200); // Always return 200, errors are in JSON
    }
    
    // Check if this is a public viewing request (allow without nonce for public viewing)
    $is_public_view = isset($_POST['pn_tasks_manager_ajax_nopriv_type']) && in_array(
      sanitize_text_field(wp_unslash($_POST['pn_tasks_manager_ajax_nopriv_type'])), 
      ['pn_tasks_manager_task_view', 'pn_tasks_manager_calendar_view']
    );
    
    if (array_key_exists('pn_tasks_manager_ajax_nopriv_type', $_POST)) {
      // For public views (task view, calendar view), nonce is optional
      if (!$is_public_view) {
        if (!array_key_exists('pn_tasks_manager_ajax_nopriv_nonce', $_POST) || empty($_POST['pn_tasks_manager_ajax_nopriv_nonce'])) {
          echo wp_json_encode([
            'error_key' => 'pn_tasks_manager_nonce_ajax_nopriv_error_required',
            'error_content' => esc_html(__('Security check failed: Nonce is required.', 'pn-tasks-manager')),
          ]);

          exit;
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pn_tasks_manager_ajax_nopriv_nonce'])), 'pn-tasks-manager-nonce')) {
          echo wp_json_encode([
            'error_key' => 'pn_tasks_manager_nonce_ajax_nopriv_error_invalid',
            'error_content' => esc_html(__('Security check failed: Invalid nonce.', 'pn-tasks-manager')),
          ]);

          exit;
        }
      } else {
        // For public views, verify nonce if provided, but don't require it
        if (array_key_exists('pn_tasks_manager_ajax_nopriv_nonce', $_POST) && !empty($_POST['pn_tasks_manager_ajax_nopriv_nonce'])) {
          if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pn_tasks_manager_ajax_nopriv_nonce'])), 'pn-tasks-manager-nonce')) {
            echo wp_json_encode([
              'error_key' => 'pn_tasks_manager_nonce_ajax_nopriv_error_invalid',
              'error_content' => esc_html(__('Security check failed: Invalid nonce.', 'pn-tasks-manager')),
            ]);

            exit;
          }
        }
      }

      $pn_tasks_manager_ajax_nopriv_type = isset($_POST['pn_tasks_manager_ajax_nopriv_type']) ? PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(sanitize_text_field(wp_unslash($_POST['pn_tasks_manager_ajax_nopriv_type']))) : '';
      
      $raw_pn_tasks_manager_ajax_keys = isset($_POST['pn_tasks_manager_ajax_keys']) ? wp_unslash($_POST['pn_tasks_manager_ajax_keys']) : [];
      $sanitized_pn_tasks_manager_ajax_keys = is_array($raw_pn_tasks_manager_ajax_keys) ? map_deep($raw_pn_tasks_manager_ajax_keys, 'wp_kses_post') : [];
      $pn_tasks_manager_ajax_keys = !empty($sanitized_pn_tasks_manager_ajax_keys) ? array_map(function($key) {
        $sanitized_key = wp_unslash($key);
        return array(
          'id' => sanitize_key($sanitized_key['id']),
          'node' => sanitize_key($sanitized_key['node']),
          'type' => sanitize_key($sanitized_key['type']),
          // keep original truthiness (can be true/false or 'true'/'false')
          'multiple' => isset($sanitized_key['multiple']) ? $sanitized_key['multiple'] : ''
        );
      }, $sanitized_pn_tasks_manager_ajax_keys) : [];

      $pn_tasks_manager_key_value = [];

      if (!empty($pn_tasks_manager_ajax_keys)) {
        foreach ($pn_tasks_manager_ajax_keys as $pn_tasks_manager_key) {
          // Robust detection of multiple-value fields
          $raw_id = isset($pn_tasks_manager_key['id']) ? $pn_tasks_manager_key['id'] : '';
          $clear_key = str_replace('[]', '', $raw_id);
          $posted_value = isset($_POST[$clear_key]) && array_key_exists($clear_key, $_POST) ? wp_unslash($_POST[$clear_key]) : null;
          if (is_array($posted_value)) {
            $posted_value = map_deep($posted_value, 'wp_kses_post');
          } elseif (!is_null($posted_value)) {
            $posted_value = wp_kses_post($posted_value);
          }
          $is_multiple_field = (
            $pn_tasks_manager_key['multiple'] === 'true' ||
            $pn_tasks_manager_key['multiple'] === true ||
            $pn_tasks_manager_key['multiple'] === 1 ||
            $pn_tasks_manager_key['type'] === 'select-multiple' ||
            is_array($posted_value)
          );

          if ($is_multiple_field) {
            $pn_tasks_manager_clear_key = $clear_key;
            ${$pn_tasks_manager_clear_key} = $pn_tasks_manager_key_value[$pn_tasks_manager_clear_key] = [];

            if (!empty($posted_value)) {
              $unslashed_array = $posted_value;
              if (!is_array($unslashed_array)) {
                $unslashed_array = array($unslashed_array);
              }

              // Special handling: for select[multiple], sanitize the full array at once
              if ($pn_tasks_manager_key['node'] === 'select' && $pn_tasks_manager_key['type'] === 'select-multiple') {
                $sanitized_array = PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(
                  $unslashed_array,
                  'select',
                  'select-multiple',
                  $pn_tasks_manager_key['field_config'] ?? []
                );
                if (!is_array($sanitized_array)) {
                  $sanitized_array = [];
                }
              } else {
                $sanitized_array = array_map(function($value) use ($pn_tasks_manager_key) {
                  return PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(
                    $value,
                    $pn_tasks_manager_key['node'],
                    $pn_tasks_manager_key['type'],
                    $pn_tasks_manager_key['field_config'] ?? []
                  );
                }, $unslashed_array);
              }

              // Keep only non-empty values
              $sanitized_array = array_filter($sanitized_array, function($v) { return $v !== '' && $v !== null; });
              // Normalize: cast to int if all numeric, unique, and reindex
              $all_numeric = !empty($sanitized_array) && count(array_filter($sanitized_array, 'is_numeric')) === count($sanitized_array);
              if ($all_numeric) {
                $sanitized_array = array_map('intval', $sanitized_array);
              }
              $sanitized_array = array_values(array_unique($sanitized_array));

              ${$pn_tasks_manager_clear_key} = $pn_tasks_manager_key_value[$pn_tasks_manager_clear_key] = $sanitized_array;
            } else {
              // Explicitly store empty array for multiple fields with no selection
              ${$pn_tasks_manager_clear_key} = [];
              $pn_tasks_manager_key_value[$pn_tasks_manager_clear_key] = [];
            }
          } else {
            $sanitized_key = sanitize_key($pn_tasks_manager_key['id']);
            $unslashed_value = !empty($_POST[$sanitized_key]) && isset($_POST[$sanitized_key]) ? wp_unslash($_POST[$sanitized_key]) : '';
            if (is_array($unslashed_value)) {
              $unslashed_value = map_deep($unslashed_value, 'wp_kses_post');
            } else {
              $unslashed_value = wp_kses_post($unslashed_value);
            }
            
            $pn_tasks_manager_key_id = !empty($unslashed_value) ? 
              PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(
                $unslashed_value, 
                $pn_tasks_manager_key['node'], 
                $pn_tasks_manager_key['type'],
                $pn_tasks_manager_key['field_config'] ?? [],
              ) : '';
            
              ${$pn_tasks_manager_key['id']} = $pn_tasks_manager_key_value[$pn_tasks_manager_key['id']] = $pn_tasks_manager_key_id;
          }
        }
      }

      $pn_tasks_manager_task_id = !empty($_POST['pn_tasks_manager_task_id']) && isset($_POST['pn_tasks_manager_task_id']) ? absint(wp_unslash($_POST['pn_tasks_manager_task_id'])) : 0;
      
      // Get calendar view parameters
      $calendar_view = !empty($_POST['calendar_view']) ? sanitize_text_field(wp_unslash($_POST['calendar_view'])) : 'month';
      $calendar_year = !empty($_POST['calendar_year']) ? intval(wp_unslash($_POST['calendar_year'])) : gmdate('Y');
      $calendar_month = !empty($_POST['calendar_month']) ? intval(wp_unslash($_POST['calendar_month'])) : gmdate('m');
      $calendar_day = !empty($_POST['calendar_day']) ? intval(wp_unslash($_POST['calendar_day'])) : gmdate('d');
      $hide_others = !empty($_POST['hide_others']) ? (bool) intval(wp_unslash($_POST['hide_others'])) : false;
      
      switch ($pn_tasks_manager_ajax_nopriv_type) {
        case 'pn_tasks_manager_calendar_view':
          // Ensure we have proper headers for JSON response
          header('Content-Type: application/json; charset=utf-8');
          status_header(200);
          
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
        case 'pn_tasks_manager_task_view':
          // Ensure we have proper headers for JSON response
          if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            status_header(200);
          }
          
          // Clean any existing output buffers
          while (ob_get_level() > 0) {
            ob_end_clean();
          }
          
          // Get task ID from POST if not already set
          if (empty($pn_tasks_manager_task_id)) {
            $pn_tasks_manager_task_id = !empty($_POST['pn_tasks_manager_task_id']) && isset($_POST['pn_tasks_manager_task_id']) ? absint(wp_unslash($_POST['pn_tasks_manager_task_id'])) : 0;
          }
          
          if (!empty($pn_tasks_manager_task_id)) {
            try {
              $plugin_post_type_taskpn = new PN_TASKS_MANAGER_Post_Type_Task();
              $task_html = $plugin_post_type_taskpn->pn_tasks_manager_task_view(intval($pn_tasks_manager_task_id));
              
              if (!empty($task_html)) {
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
              while (ob_get_level() > 0) {
                ob_end_clean();
              }
              echo wp_json_encode([
                'error_key' => 'pn_tasks_manager_task_view_error', 
                'error_content' => esc_html(__('An error occurred while showing the Task.', 'pn-tasks-manager')), 
              ]);
            } catch (Error $e) {
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
        case 'pn_tasks_manager_form_save':
          $pn_tasks_manager_form_type = !empty($_POST['pn_tasks_manager_form_type']) && isset($_POST['pn_tasks_manager_form_type']) ? sanitize_text_field(wp_unslash($_POST['pn_tasks_manager_form_type'])) : '';

          if (!empty($pn_tasks_manager_key_value) && !empty($pn_tasks_manager_form_type)) {
            $pn_tasks_manager_form_id = !empty($_POST['pn_tasks_manager_form_id']) && isset($_POST['pn_tasks_manager_form_id']) ? sanitize_text_field(wp_unslash($_POST['pn_tasks_manager_form_id'])) : 0;
            $pn_tasks_manager_form_subtype = !empty($_POST['pn_tasks_manager_form_subtype']) && isset($_POST['pn_tasks_manager_form_subtype']) ? sanitize_text_field(wp_unslash($_POST['pn_tasks_manager_form_subtype'])) : '';
            $user_id = !empty($_POST['pn_tasks_manager_form_user_id']) && isset($_POST['pn_tasks_manager_form_user_id']) ? absint(wp_unslash($_POST['pn_tasks_manager_form_user_id'])) : 0;
            $post_id = !empty($_POST['pn_tasks_manager_form_post_id']) && isset($_POST['pn_tasks_manager_form_post_id']) ? absint(wp_unslash($_POST['pn_tasks_manager_form_post_id'])) : 0;
            $post_type = !empty($_POST['pn_tasks_manager_form_post_type']) && isset($_POST['pn_tasks_manager_form_post_type']) ? sanitize_key(wp_unslash($_POST['pn_tasks_manager_form_post_type'])) : '';

            if (($pn_tasks_manager_form_type == 'user' && empty($user_id) && !in_array($pn_tasks_manager_form_subtype, ['user_alt_new'])) || ($pn_tasks_manager_form_type == 'post' && (empty($post_id) && !(!empty($pn_tasks_manager_form_subtype) && in_array($pn_tasks_manager_form_subtype, ['post_new', 'post_edit'])))) || ($pn_tasks_manager_form_type == 'option' && !is_user_logged_in())) {
              session_start();

              $_SESSION['pn_tasks_manager_form'] = [];
              $_SESSION['pn_tasks_manager_form'][$pn_tasks_manager_form_id] = [];
              $_SESSION['pn_tasks_manager_form'][$pn_tasks_manager_form_id]['form_type'] = $pn_tasks_manager_form_type;
              $_SESSION['pn_tasks_manager_form'][$pn_tasks_manager_form_id]['values'] = $pn_tasks_manager_key_value;

              if (!empty($post_id)) {
                $_SESSION['pn_tasks_manager_form'][$pn_tasks_manager_form_id]['post_id'] = $post_id;
              }

              echo wp_json_encode(['error_key' => 'pn_tasks_manager_form_save_error_unlogged', ]);exit;
            }else{
              switch ($pn_tasks_manager_form_type) {
                case 'user':
                  if (!in_array($pn_tasks_manager_form_subtype, ['user_alt_new'])) {
                      if (empty($user_id)) {
                        if (PN_TASKS_MANAGER_Functions_User::pn_tasks_manager_user_is_admin(get_current_user_id())) {
                          $user_login = !empty($_POST['user_login']) && isset($_POST['user_login']) ? sanitize_user(wp_unslash($_POST['user_login']), true) : '';
                          $user_password = !empty($_POST['user_password']) && isset($_POST['user_password']) ? wp_unslash($_POST['user_password']) : '';
                          $user_email = !empty($_POST['user_email']) && isset($_POST['user_email']) ? sanitize_email(wp_unslash($_POST['user_email'])) : '';

                        $user_id = PN_TASKS_MANAGER_Functions_User::pn_tasks_manager_user_insert($user_login, $user_password, $user_email);
                      }
                    }

                    if (!empty($user_id)) {
                      foreach ($pn_tasks_manager_key_value as $pn_tasks_manager_key => $pn_tasks_manager_value) {
                        // Skip action and ajax type keys
                        if (in_array($pn_tasks_manager_key, ['action', 'pn_tasks_manager_ajax_nopriv_type'])) {
                          continue;
                        }

                        // Ensure option name is prefixed with pn_tasks_manager_
                        // Special case: if key is just 'pn-tasks-manager', don't add prefix as it's already the main option
                        if ($pn_tasks_manager_key !== 'pn-tasks-manager' && strpos((string)$pn_tasks_manager_key, 'pn_tasks_manager_') !== 0) {
                          $pn_tasks_manager_key = 'pn_tasks_manager_' . $pn_tasks_manager_key;
                        } else {
                          // Key already has correct prefix
                        }

                        update_user_meta($user_id, $pn_tasks_manager_key, $pn_tasks_manager_value);
                      }
                    }
                  }

                  do_action('pn_tasks_manager_form_save', $user_id, $pn_tasks_manager_key_value, $pn_tasks_manager_form_type, $pn_tasks_manager_form_subtype);
                  break;
                case 'post':
                 
                  if (empty($pn_tasks_manager_form_subtype) || in_array($pn_tasks_manager_form_subtype, ['post_new', 'post_edit', 'post_check'])) {
                    // For post_check, we don't need to create a new post or update title/description
                    if ($pn_tasks_manager_form_subtype !== 'post_check') {
                      // Build correct field prefix: 'pn_tasks_task' → 'pn_tasks_manager_task'
                      $field_prefix = str_replace('pn_tasks_', 'pn_tasks_manager_', $post_type);
                      $title_key = $field_prefix . '_title';
                      $description_key = $field_prefix . '_description';

                      if (empty($post_id)) {
                        // Allow any logged-in user to create a new post
                        if (is_user_logged_in()) {
                          $post_functions = new PN_TASKS_MANAGER_Functions_Post();
                          $title = !empty($pn_tasks_manager_key_value[$title_key]) ? $pn_tasks_manager_key_value[$title_key] : '';
                          $description = !empty($pn_tasks_manager_key_value[$description_key]) ? $pn_tasks_manager_key_value[$description_key] : '';

                          $post_id = $post_functions->pn_tasks_manager_insert_post($title, $description, '', sanitize_title($title), $post_type, 'publish', get_current_user_id());
                        }
                      }

                      if (!empty($post_id)) {
                        foreach ($pn_tasks_manager_key_value as $pn_tasks_manager_key => $pn_tasks_manager_value) {
                          if ($pn_tasks_manager_key === $title_key) {
                            wp_update_post([
                              'ID' => $post_id,
                              'post_title' => sanitize_text_field($pn_tasks_manager_value),
                            ]);
                          }

                          if ($pn_tasks_manager_key === $description_key) {
                            wp_update_post([
                              'ID' => $post_id,
                              'post_content' => wp_kses_post($pn_tasks_manager_value),
                            ]);
                          }

                          // Skip action and ajax type keys
                          if (in_array($pn_tasks_manager_key, ['action', 'pn_tasks_manager_ajax_nopriv_type'])) {
                            continue;
                          }

                          // Ensure option name is prefixed with pn_tasks_manager_
                          // Special case: if key is just 'pn-tasks-manager', don't add prefix as it's already the main option
                          if ($pn_tasks_manager_key !== 'pn-tasks-manager' && strpos((string)$pn_tasks_manager_key, 'pn_tasks_manager_') !== 0) {
                            $pn_tasks_manager_key = 'pn_tasks_manager_' . $pn_tasks_manager_key;
                          } else {
                            // Key already has correct prefix
                          }

                          // Generic normalization for any multiple field saved as array
                          if (is_array($pn_tasks_manager_value)) {
                            $values = array_filter($pn_tasks_manager_value, function($v) { return $v !== '' && $v !== null; });
                            $all_numeric = !empty($values) && count(array_filter($values, 'is_numeric')) === count($values);
                            if ($all_numeric) {
                              $values = array_map('intval', $values);
                            }
                            $pn_tasks_manager_value = array_values(array_unique($values));
                          }

                          update_post_meta($post_id, $pn_tasks_manager_key, $pn_tasks_manager_value);
                        }
                      }
                    }

                    // Always trigger the hook for all post subtypes, including post_check
                    do_action('pn_tasks_manager_form_save', $post_id, $pn_tasks_manager_key_value, $pn_tasks_manager_form_type, $pn_tasks_manager_form_subtype, $post_type);
                  }
                  break;
                case 'option':
                  if (PN_TASKS_MANAGER_Functions_User::pn_tasks_manager_user_is_admin(get_current_user_id())) {
                    $pn_tasks_manager_settings = new PN_TASKS_MANAGER_Settings();
                    $pn_tasks_manager_options = $pn_tasks_manager_settings->pn_tasks_manager_get_options();
                    $pn_tasks_manager_allowed_options = array_keys($pn_tasks_manager_options);

                    // First, add html_multi field IDs to allowed options temporarily
                    foreach ($pn_tasks_manager_options as $option_key => $option_config) {
                      if (isset($option_config['input']) && $option_config['input'] === 'html_multi' && 
                          isset($option_config['html_multi_fields']) && is_array($option_config['html_multi_fields'])) {
                        foreach ($option_config['html_multi_fields'] as $multi_field) {
                          if (isset($multi_field['id'])) {
                            $pn_tasks_manager_allowed_options[] = $multi_field['id'];
                          }
                        }
                      }
                    }

                    // Process remaining individual fields
                    foreach ($pn_tasks_manager_key_value as $pn_tasks_manager_key => $pn_tasks_manager_value) {
                      // Skip action and ajax type keys
                      if (in_array($pn_tasks_manager_key, ['action', 'pn_tasks_manager_ajax_nopriv_type'])) {
                        continue;
                      }

                      // Ensure option name is prefixed with pn_tasks_manager_
                      // Special case: if key is just 'pn-tasks-manager', don't add prefix as it's already the main option
                      if ($pn_tasks_manager_key !== 'pn-tasks-manager' && strpos((string)$pn_tasks_manager_key, 'pn_tasks_manager_') !== 0) {
                        $pn_tasks_manager_key = 'pn_tasks_manager_' . $pn_tasks_manager_key;
                      } else {
                        // Key already has correct prefix
                      }

                      // Only update if option is in allowed options list
                      if (in_array($pn_tasks_manager_key, $pn_tasks_manager_allowed_options)) {
                        update_option($pn_tasks_manager_key, $pn_tasks_manager_value);
                      }
                    }
                  }

                  do_action('pn_tasks_manager_form_save', 0, $pn_tasks_manager_key_value, $pn_tasks_manager_form_type, $pn_tasks_manager_form_subtype);
                  break;
              }

              $popup_close = in_array($pn_tasks_manager_form_subtype, ['post_new', 'post_edit', 'user_alt_new']) ? true : '';
              $update_list = in_array($pn_tasks_manager_form_subtype, ['post_new', 'post_edit', 'user_alt_new', 'post_check']) ? true : '';
              $check = in_array($pn_tasks_manager_form_subtype, ['post_check', 'post_uncheck']) ? $pn_tasks_manager_form_subtype : '';
              
              if ($update_list && !empty($post_type)) {
                switch ($post_type) {
                  case 'pn_tasks_task':
                    $plugin_post_type_taskpn = new PN_TASKS_MANAGER_Post_Type_Task();
                    // Return the full wrapper so the search/add toolbar persists
                    $update_html = $plugin_post_type_taskpn->pn_tasks_manager_task_list_wrapper();
                    break;
                }
              }else{
                $update_html = '';
              }

              echo wp_json_encode(['error_key' => '', 'popup_close' => $popup_close, 'update_list' => $update_list, 'update_html' => $update_html, 'check' => $check]);exit;
            }
          }else{
            echo wp_json_encode(['error_key' => 'pn_tasks_manager_form_save_error', ]);exit;
          }
          break;
      }

      echo wp_json_encode(['error_key' => '', ]);exit;
    }
  }
}