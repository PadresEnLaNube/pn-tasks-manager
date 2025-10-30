<?php
/**
 * Load the plugin no private Ajax functions.
 *
 * Load the plugin no private Ajax functions to be executed in background.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    TASKSPN
 * @subpackage TASKSPN/includes
 * @author     Padres en la Nube
 */
class TASKSPN_Ajax_Nopriv {
  /**
   * Load the plugin templates.
   *
   * @since    1.0.0
   */
  public function taskspn_ajax_nopriv_server() {
    if (array_key_exists('taskspn_ajax_nopriv_type', $_POST)) {
      if (!array_key_exists('taskspn_ajax_nopriv_nonce', $_POST)) {
        echo wp_json_encode([
          'error_key' => 'taskspn_nonce_ajax_nopriv_error_required',
          'error_content' => esc_html(__('Security check failed: Nonce is required.', 'taskspn')),
        ]);

        exit;
      }

      if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['taskspn_ajax_nopriv_nonce'])), 'taskspn-nonce')) {
        echo wp_json_encode([
          'error_key' => 'taskspn_nonce_ajax_nopriv_error_invalid',
          'error_content' => esc_html(__('Security check failed: Invalid nonce.', 'taskspn')),
        ]);

        exit;
      }

      $taskspn_ajax_nopriv_type = TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['taskspn_ajax_nopriv_type']));
      
      $taskspn_ajax_keys = !empty($_POST['taskspn_ajax_keys']) ? array_map(function($key) {
        $sanitized_key = wp_unslash($key);
        return array(
          'id' => sanitize_key($sanitized_key['id']),
          'node' => sanitize_key($sanitized_key['node']),
          'type' => sanitize_key($sanitized_key['type']),
          // keep original truthiness (can be true/false or 'true'/'false')
          'multiple' => isset($sanitized_key['multiple']) ? $sanitized_key['multiple'] : ''
        );
      }, wp_unslash($_POST['taskspn_ajax_keys'])) : [];

      $taskspn_key_value = [];

      if (!empty($taskspn_ajax_keys)) {
        foreach ($taskspn_ajax_keys as $taskspn_key) {
          // Robust detection of multiple-value fields
          $raw_id = isset($taskspn_key['id']) ? $taskspn_key['id'] : '';
          $clear_key = str_replace('[]', '', $raw_id);
          $posted_value = isset($_POST[$clear_key]) ? wp_unslash($_POST[$clear_key]) : null;
          $is_multiple_field = (
            $taskspn_key['multiple'] === 'true' ||
            $taskspn_key['multiple'] === true ||
            $taskspn_key['multiple'] === 1 ||
            $taskspn_key['type'] === 'select-multiple' ||
            is_array($posted_value)
          );

          if ($is_multiple_field) {
            $taskspn_clear_key = $clear_key;
            ${$taskspn_clear_key} = $taskspn_key_value[$taskspn_clear_key] = [];

            if (!empty($posted_value)) {
              $unslashed_array = $posted_value;
              if (!is_array($unslashed_array)) {
                $unslashed_array = array($unslashed_array);
              }

              // Special handling: for select[multiple], sanitize the full array at once
              if ($taskspn_key['node'] === 'select' && $taskspn_key['type'] === 'select-multiple') {
                $sanitized_array = TASKSPN_Forms::taskspn_sanitizer(
                  $unslashed_array,
                  'select',
                  'select-multiple',
                  $taskspn_key['field_config'] ?? []
                );
                if (!is_array($sanitized_array)) {
                  $sanitized_array = [];
                }
              } else {
                $sanitized_array = array_map(function($value) use ($taskspn_key) {
                  return TASKSPN_Forms::taskspn_sanitizer(
                    $value,
                    $taskspn_key['node'],
                    $taskspn_key['type'],
                    $taskspn_key['field_config'] ?? []
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

              ${$taskspn_clear_key} = $taskspn_key_value[$taskspn_clear_key] = $sanitized_array;
            } else {
              // Explicitly store empty array for multiple fields with no selection
              ${$taskspn_clear_key} = [];
              $taskspn_key_value[$taskspn_clear_key] = [];
            }
          } else {
            $sanitized_key = sanitize_key($taskspn_key['id']);
            $unslashed_value = !empty($_POST[$sanitized_key]) ? wp_unslash($_POST[$sanitized_key]) : '';
            
            $taskspn_key_id = !empty($unslashed_value) ? 
              TASKSPN_Forms::taskspn_sanitizer(
                $unslashed_value, 
                $taskspn_key['node'], 
                $taskspn_key['type'],
                $taskspn_key['field_config'] ?? [],
              ) : '';
            
              ${$taskspn_key['id']} = $taskspn_key_value[$taskspn_key['id']] = $taskspn_key_id;
          }
        }
      }

      switch ($taskspn_ajax_nopriv_type) {
        case 'taskspn_form_save':
          $taskspn_form_type = !empty($_POST['taskspn_form_type']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['taskspn_form_type'])) : '';

          if (!empty($taskspn_key_value) && !empty($taskspn_form_type)) {
            $taskspn_form_id = !empty($_POST['taskspn_form_id']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['taskspn_form_id'])) : 0;
            $taskspn_form_subtype = !empty($_POST['taskspn_form_subtype']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['taskspn_form_subtype'])) : '';
            $user_id = !empty($_POST['taskspn_form_user_id']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['taskspn_form_user_id'])) : 0;
            $post_id = !empty($_POST['taskspn_form_post_id']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['taskspn_form_post_id'])) : 0;
            $post_type = !empty($_POST['taskspn_form_post_type']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['taskspn_form_post_type'])) : '';

            if (($taskspn_form_type == 'user' && empty($user_id) && !in_array($taskspn_form_subtype, ['user_alt_new'])) || ($taskspn_form_type == 'post' && (empty($post_id) && !(!empty($taskspn_form_subtype) && in_array($taskspn_form_subtype, ['post_new', 'post_edit'])))) || ($taskspn_form_type == 'option' && !is_user_logged_in())) {
              session_start();

              $_SESSION['taskspn_form'] = [];
              $_SESSION['taskspn_form'][$taskspn_form_id] = [];
              $_SESSION['taskspn_form'][$taskspn_form_id]['form_type'] = $taskspn_form_type;
              $_SESSION['taskspn_form'][$taskspn_form_id]['values'] = $taskspn_key_value;

              if (!empty($post_id)) {
                $_SESSION['taskspn_form'][$taskspn_form_id]['post_id'] = $post_id;
              }

              echo wp_json_encode(['error_key' => 'taskspn_form_save_error_unlogged', ]);exit;
            }else{
              switch ($taskspn_form_type) {
                case 'user':
                  if (!in_array($taskspn_form_subtype, ['user_alt_new'])) {
                    if (empty($user_id)) {
                      if (TASKSPN_Functions_User::taskspn_user_is_admin(get_current_user_id())) {
                        $user_login = !empty($_POST['user_login']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['user_login'])) : 0;
                        $user_password = !empty($_POST['user_password']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['user_password'])) : 0;
                        $user_email = !empty($_POST['user_email']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST['user_email'])) : 0;

                        $user_id = TASKSPN_Functions_User::taskspn_user_insert($user_login, $user_password, $user_email);
                      }
                    }

                    if (!empty($user_id)) {
                      foreach ($taskspn_key_value as $taskspn_key => $taskspn_value) {
                        // Skip action and ajax type keys
                        if (in_array($taskspn_key, ['action', 'taskspn_ajax_nopriv_type'])) {
                          continue;
                        }

                        // Ensure option name is prefixed with taskspn_
                        // Special case: if key is just 'taskspn', don't add prefix as it's already the main option
                        if ($taskspn_key !== 'taskspn' && strpos((string)$taskspn_key, 'taskspn_') !== 0) {
                          $taskspn_key = 'taskspn_' . $taskspn_key;
                        } else {
                          // Key already has correct prefix
                        }

                        update_user_meta($user_id, $taskspn_key, $taskspn_value);
                      }
                    }
                  }

                  do_action('taskspn_form_save', $user_id, $taskspn_key_value, $taskspn_form_type, $taskspn_form_subtype);
                  break;
                case 'post':
                  if (empty($taskspn_form_subtype) || in_array($taskspn_form_subtype, ['post_new', 'post_edit'])) {
                    if (empty($post_id)) {
                      // Allow any logged-in user to create a new post
                      if (is_user_logged_in()) {
                        $post_functions = new TASKSPN_Functions_Post();
                        $title = !empty($_POST[$post_type . '_title']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST[$post_type . '_title'])) : '';
                        $description = !empty($_POST[$post_type . '_description']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST[$post_type . '_description'])) : '';
                        
                        $post_id = $post_functions->taskspn_insert_post($title, $description, '', sanitize_title($title), $post_type, 'publish', get_current_user_id());
                      }
                    }

                    if (!empty($post_id)) {
                      foreach ($taskspn_key_value as $taskspn_key => $taskspn_value) {
                        if ($taskspn_key == $post_type . '_title') {
                          wp_update_post([
                            'ID' => $post_id,
                            'post_title' => esc_html($taskspn_value),
                          ]);
                        }

                        if ($taskspn_key == $post_type . '_description') {
                          wp_update_post([
                            'ID' => $post_id,
                            'post_content' => esc_html($taskspn_value),
                          ]);
                        }

                        // Skip action and ajax type keys
                        if (in_array($taskspn_key, ['action', 'taskspn_ajax_nopriv_type'])) {
                          continue;
                        }

                        // Ensure option name is prefixed with taskspn_
                        // Special case: if key is just 'taskspn', don't add prefix as it's already the main option
                        if ($taskspn_key !== 'taskspn' && strpos((string)$taskspn_key, 'taskspn_') !== 0) {
                          $taskspn_key = 'taskspn_' . $taskspn_key;
                        } else {
                          // Key already has correct prefix
                        }

                        // Generic normalization for any multiple field saved as array
                        if (is_array($taskspn_value)) {
                          $values = array_filter($taskspn_value, function($v) { return $v !== '' && $v !== null; });
                          $all_numeric = !empty($values) && count(array_filter($values, 'is_numeric')) === count($values);
                          if ($all_numeric) {
                            $values = array_map('intval', $values);
                          }
                          $taskspn_value = array_values(array_unique($values));
                        }

                        update_post_meta($post_id, $taskspn_key, $taskspn_value);
                      }
                    }
                  }

                  do_action('taskspn_form_save', $post_id, $taskspn_key_value, $taskspn_form_type, $taskspn_form_subtype, $post_type);
                  break;
                case 'option':
                  if (TASKSPN_Functions_User::taskspn_user_is_admin(get_current_user_id())) {
                    $taskspn_settings = new TASKSPN_Settings();
                    $taskspn_options = $taskspn_settings->taskspn_get_options();
                    $taskspn_allowed_options = array_keys($taskspn_options);

                    // First, add html_multi field IDs to allowed options temporarily
                    foreach ($taskspn_options as $option_key => $option_config) {
                      if (isset($option_config['input']) && $option_config['input'] === 'html_multi' && 
                          isset($option_config['html_multi_fields']) && is_array($option_config['html_multi_fields'])) {
                        foreach ($option_config['html_multi_fields'] as $multi_field) {
                          if (isset($multi_field['id'])) {
                            $taskspn_allowed_options[] = $multi_field['id'];
                          }
                        }
                      }
                    }

                    // Process remaining individual fields
                    foreach ($taskspn_key_value as $taskspn_key => $taskspn_value) {
                      // Skip action and ajax type keys
                      if (in_array($taskspn_key, ['action', 'taskspn_ajax_nopriv_type'])) {
                        continue;
                      }

                      // Ensure option name is prefixed with taskspn_
                      // Special case: if key is just 'taskspn', don't add prefix as it's already the main option
                      if ($taskspn_key !== 'taskspn' && strpos((string)$taskspn_key, 'taskspn_') !== 0) {
                        $taskspn_key = 'taskspn_' . $taskspn_key;
                      } else {
                        // Key already has correct prefix
                      }

                      // Only update if option is in allowed options list
                      if (in_array($taskspn_key, $taskspn_allowed_options)) {
                        update_option($taskspn_key, $taskspn_value);
                      }
                    }
                  }

                  do_action('taskspn_form_save', 0, $taskspn_key_value, $taskspn_form_type, $taskspn_form_subtype);
                  break;
              }

              $popup_close = in_array($taskspn_form_subtype, ['post_new', 'post_edit', 'user_alt_new']) ? true : '';
              $update_list = in_array($taskspn_form_subtype, ['post_new', 'post_edit', 'user_alt_new']) ? true : '';
              $check = in_array($taskspn_form_subtype, ['post_check', 'post_uncheck']) ? $taskspn_form_subtype : '';
              
              if ($update_list && !empty($post_type)) {
                switch ($post_type) {
                  case 'taskspn_task':
                    $plugin_post_type_taskpn = new TASKSPN_Post_Type_Task();
                    // Return the full wrapper so the search/add toolbar persists
                    $update_html = $plugin_post_type_taskpn->taskspn_task_list_wrapper();
                    break;
                }
              }else{
                $update_html = '';
              }

              echo wp_json_encode(['error_key' => '', 'popup_close' => $popup_close, 'update_list' => $update_list, 'update_html' => $update_html, 'check' => $check]);exit;
            }
          }else{
            echo wp_json_encode(['error_key' => 'taskspn_form_save_error', ]);exit;
          }
          break;
      }

      echo wp_json_encode(['error_key' => '', ]);exit;
    }
  }
}