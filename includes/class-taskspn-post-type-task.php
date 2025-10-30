<?php
/**
 * Task creator.
 *
 * This class defines Task options, menus and templates.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    TASKSPN
 * @subpackage TASKSPN/includes
 * @author     Padres en la Nube
 */
class TASKSPN_Post_Type_Task {
  public static function taskspn_task_get_fields($task_id = 0) {
    $taskspn_fields = [];
      $taskspn_fields['taskspn_task_title'] = [
        'id' => 'taskspn_task_title',
        'class' => 'taskspn-input taskspn-width-100-percent',
        'input' => 'input',
        'type' => 'text',
        'required' => true,
        'value' => !empty($task_id) ? esc_html(get_the_title($task_id)) : '',
        'label' => __('Task title', 'taskspn'),
        'placeholder' => __('Task title', 'taskspn'),
      ];
      $taskspn_fields['taskspn_task_description'] = [
        'id' => 'taskspn_task_description',
        'class' => 'taskspn-input taskspn-width-100-percent',
        'input' => 'textarea',
        'required' => true,
        'value' => !empty($task_id) ? (str_replace(']]>', ']]&gt;', apply_filters('the_content', get_post($task_id)->post_content))) : '',
        'label' => __('Task description', 'taskspn'),
        'placeholder' => __('Task description', 'taskspn'),
      ];
    
    // Allow other plugins to extend task fields
    $taskspn_fields = apply_filters('taskspn_task_fields', $taskspn_fields, $task_id);
    
    return $taskspn_fields;
  }

  public static function taskspn_task_get_fields_meta($task_id = 0) {
    $taskspn_fields_meta = [];
      $taskspn_fields_meta['taskspn_task_date'] = [
        'id' => 'taskspn_task_date',
        'class' => 'taskspn-input taskspn-width-100-percent',
        'input' => 'input',
        'type' => 'date',
        'label' => __('Task date', 'taskspn'),
        'placeholder' => __('Task date', 'taskspn'),
      ];
      $taskspn_fields_meta['taskspn_task_time'] = [
        'id' => 'taskspn_task_time',
        'class' => 'taskspn-input taskspn-width-100-percent',
        'input' => 'input',
        'type' => 'time',
        'label' => __('Task time', 'taskspn'),
        'placeholder' => __('Task time', 'taskspn'),
      ];
      $taskspn_fields_meta['taskspn_task_estimated_hours'] = [
        'id' => 'taskspn_task_estimated_hours',
        'class' => 'taskspn-input taskspn-width-100-percent',
        'input' => 'input',
        'type' => 'number',
        'step' => '0.25',
        'min' => '0',
        'label' => __('Estimated hours', 'taskspn'),
        'placeholder' => __('Estimated hours (e.g., 1.5)', 'taskspn'),
      ];
      $taskspn_fields_meta['taskspn_task_repeat'] = [
        'id' => 'taskspn_task_repeat',
        'class' => 'taskspn-input taskspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Repeat task', 'taskspn'),
        'placeholder' => __('Repeat task', 'taskspn'),
      ]; 
        $taskspn_fields_meta['taskspn_task_periodicity_value'] = [
          'id' => 'taskspn_task_periodicity_value',
          'class' => 'taskspn-input taskspn-width-100-percent',
          'input' => 'input',
          'type' => 'number',
          'min' => 1,
          'max' => 365,
          'parent' => 'taskspn_task_repeat',
          'parent_option' => 'on',
          'label' => __('Repeat every', 'taskspn'),
          'placeholder' => __('Number', 'taskspn'),
        ];
        $taskspn_fields_meta['taskspn_task_periodicity_type'] = [
          'id' => 'taskspn_task_periodicity_type',
          'class' => 'taskspn-select taskspn-width-100-percent',
          'input' => 'select',
          'parent' => 'taskspn_task_repeat',
          'parent_option' => 'on',
          'label' => __('Period', 'taskspn'),
          'placeholder' => __('Select period', 'taskspn'),
          'options' => [
            'days' => __('Days', 'taskspn'),
            'weeks' => __('Weeks', 'taskspn'),
            'months' => __('Months', 'taskspn'),
          ],
        ];
        $taskspn_fields_meta['taskspn_task_repeat_until'] = [
          'id' => 'taskspn_task_repeat_until',
          'class' => 'taskspn-input taskspn-width-100-percent',
          'input' => 'input',
          'type' => 'date',
          'parent' => 'taskspn_task_repeat',
          'parent_option' => 'on',
          'label' => __('Repeat until', 'taskspn'),
          'placeholder' => __('Repeat until', 'taskspn'),
        ];
      $taskspn_fields_meta['taskspn_task_owners'] = [
        'id' => 'taskspn_task_owners',
        'class' => 'taskspn-select taskspn-width-100-percent',
        'input' => 'select',
        'type' => 'text',
        'multiple' => true,
        'label' => __('Assigned to', 'taskspn'),
        'placeholder' => __('Select users', 'taskspn'),
        'options' => self::taskspn_get_users_for_select(),
      ];
      $taskspn_fields_meta['taskspn_task_public'] = [
        'id' => 'taskspn_task_public',
        'class' => 'taskspn-input taskspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Show task in public calendar', 'taskspn'),
        'placeholder' => __('Show task in public calendar', 'taskspn'),
      ];
      $taskspn_fields_meta['taskspn_task_multimedia'] = [
        'id' => 'taskspn_task_multimedia',
        'class' => 'taskspn-input taskspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Task multimedia content', 'taskspn'),
        'placeholder' => __('Task multimedia content', 'taskspn'),
      ]; 
        $taskspn_fields_meta['taskspn_task_url'] = [
          'id' => 'taskspn_task_url',
          'class' => 'taskspn-input taskspn-width-100-percent',
          'input' => 'input',
          'type' => 'url',
          'parent' => 'taskspn_task_multimedia',
          'parent_option' => 'on',
          'label' => __('Task url', 'taskspn'),
          'placeholder' => __('Task url', 'taskspn'),
        ];
        $taskspn_fields_meta['taskspn_task_url_audio'] = [
          'id' => 'taskspn_task_url_audio',
          'class' => 'taskspn-input taskspn-width-100-percent',
          'input' => 'input',
          'type' => 'url',
          'parent' => 'taskspn_task_multimedia',
          'parent_option' => 'on',
          'label' => __('Task audio url', 'taskspn'),
          'placeholder' => __('Task audio url', 'taskspn'),
        ];
        $taskspn_fields_meta['taskspn_task_url_video'] = [
          'id' => 'taskspn_task_url_video',
          'class' => 'taskspn-input taskspn-width-100-percent',
          'input' => 'input',
          'type' => 'url',
          'parent' => 'taskspn_task_multimedia',
          'parent_option' => 'on',
          'label' => __('Task video url', 'taskspn'),
          'placeholder' => __('Task video url', 'taskspn'),
        ];
      $taskspn_fields_meta['taskspn_task_form'] = [
        'id' => 'taskspn_task_form',
        'input' => 'input',
        'type' => 'hidden',
      ];
      $taskspn_fields_meta['taskspn_ajax_nonce'] = [
        'id' => 'taskspn_ajax_nonce',
        'input' => 'input',
        'type' => 'nonce',
      ];
    
    // Allow other plugins to extend task meta fields
    $taskspn_fields_meta = apply_filters('taskspn_task_fields_meta', $taskspn_fields_meta, $task_id);
    
    return $taskspn_fields_meta;
  }

  /**
   * Get all task fields (basic + meta) merged and extended by filters
   * 
   * @since    1.0.0
   * @param    int    $task_id    Optional task ID for getting field values
   * @return   array  Combined array of all task fields
   */
  public static function taskspn_task_get_all_fields($task_id = 0) {
    $basic_fields = self::taskspn_task_get_fields($task_id);
    $meta_fields = self::taskspn_task_get_fields_meta($task_id);
    
    // Merge and allow final extension by other plugins
    $all_fields = array_merge($basic_fields, $meta_fields);
    $all_fields = apply_filters('taskspn_task_all_fields', $all_fields, $task_id);
    
    return $all_fields;
  }

  /**
   * Register Task.
   *
   * @since    1.0.0
   */
  public function taskspn_task_register_post_type() {
    $labels = [
      'name'                => _x('Task', 'Post Type general name', 'taskspn'),
      'singular_name'       => _x('Task', 'Post Type singular name', 'taskspn'),
      'menu_name'           => esc_html(__('Tasks', 'taskspn')),
      'parent_item_colon'   => esc_html(__('Parent Task', 'taskspn')),
      'all_items'           => esc_html(__('All Tasks', 'taskspn')),
      'view_item'           => esc_html(__('View Task', 'taskspn')),
      'add_new_item'        => esc_html(__('Add new Task', 'taskspn')),
      'add_new'             => esc_html(__('Add new Task', 'taskspn')),
      'edit_item'           => esc_html(__('Edit Task', 'taskspn')),
      'update_item'         => esc_html(__('Update Task', 'taskspn')),
      'search_items'        => esc_html(__('Search Tasks', 'taskspn')),
      'not_found'           => esc_html(__('Not Task found', 'taskspn')),
      'not_found_in_trash'  => esc_html(__('Not Task found in Trash', 'taskspn')),
    ];

    $args = [
      'labels'              => $labels,
      'rewrite'             => ['slug' => (!empty(get_option('taskspn_task_slug')) ? get_option('taskspn_task_slug') : 'taskspn'), 'with_front' => false],
      'label'               => esc_html(__('Tasks', 'taskspn')),
      'description'         => esc_html(__('Task description', 'taskspn')),
      'supports'            => ['title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'page-attributes', ],
      'hierarchical'        => true,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'show_in_nav_menus'   => true,
      'show_in_admin_bar'   => true,
      'menu_position'       => 5,
      'menu_icon'           => esc_url(TASKSPN_URL . 'assets/media/taskspn-task-menu-icon.svg'),
      'can_export'          => true,
      'has_archive'         => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'capability_type'     => 'page',
      'capabilities'        => constant('TASKSPN_ROLE_TASKSPN_TASK_CAPABILITIES'),
      'taxonomies'          => ['taskspn_task_category'],
      'show_in_rest'        => true, /* REST API */
    ];

    register_post_type('taskspn_task', $args);
    add_theme_support('post-thumbnails', ['page', 'taskspn_task']);
  }

  /**
   * Add Task dashboard metabox.
   *
   * @since    1.0.0
   */
  public function taskspn_task_add_meta_box() {
    add_meta_box('taskspn_meta_box', esc_html(__('Task details', 'taskspn')), [$this, 'taskspn_task_meta_box_function'], 'taskspn_task', 'normal', 'high', ['__block_editor_compatible_meta_box' => true,]);
  }

  /**
   * Defines Task dashboard contents.
   *
   * @since    1.0.0
   */
  public function taskspn_task_meta_box_function($post) {
    $all_fields = self::taskspn_task_get_all_fields($post->ID);
    foreach ($all_fields as $taskspn_field) {
      // Skip basic fields (title, description) in meta box as they are handled by WordPress
      if (in_array($taskspn_field['id'], ['taskspn_task_title', 'taskspn_task_description'])) {
        continue;
      }
      if (!is_null(TASKSPN_Forms::taskspn_input_wrapper_builder($taskspn_field, 'post', $post->ID))) {
        echo wp_kses(TASKSPN_Forms::taskspn_input_wrapper_builder($taskspn_field, 'post', $post->ID), taskspn_KSES);
      }
    }
  }

  /**
   * Defines single template for Task.
   *
   * @since    1.0.0
   */
  public function taskspn_task_single_template($single) {
    if (get_post_type() == 'taskspn_task') {
      if (file_exists(TASKSPN_DIR . 'templates/public/single-taskspn_task.php')) {
        return TASKSPN_DIR . 'templates/public/single-taskspn_task.php';
      }
    }

    return $single;
  }

  /**
   * Defines archive template for Task.
   *
   * @since    1.0.0
   */
  public function taskspn_task_archive_template($archive) {
    if (get_post_type() == 'taskspn_task') {
      if (file_exists(TASKSPN_DIR . 'templates/public/archive-taskspn_task.php')) {
        return TASKSPN_DIR . 'templates/public/archive-taskspn_task.php';
      }
    }

    return $archive;
  }

  public function taskspn_task_save_post($post_id, $cpt, $update) {
    if($cpt->post_type == 'taskspn_task' && array_key_exists('taskspn_task_form', $_POST)){
      // Always require nonce verification
      if (!array_key_exists('taskspn_ajax_nonce', $_POST)) {
        echo wp_json_encode([
          'error_key' => 'taskspn_nonce_error_required',
          'error_content' => esc_html(__('Security check failed: Nonce is required.', 'taskspn')),
        ]);

        exit;
      }

      if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['taskspn_ajax_nonce'])), 'taskspn-nonce')) {
        echo wp_json_encode([
          'error_key' => 'taskspn_nonce_error_invalid',
          'error_content' => esc_html(__('Security check failed: Invalid nonce.', 'taskspn')),
        ]);

        exit;
      }

      if (!array_key_exists('taskspn_duplicate', $_POST)) {
        foreach (self::taskspn_task_get_all_fields($post_id) as $taskspn_field) {
          $taskspn_input = array_key_exists('input', $taskspn_field) ? $taskspn_field['input'] : '';

          if (array_key_exists($taskspn_field['id'], $_POST) || $taskspn_input == 'html_multi') {
            $taskspn_value = array_key_exists($taskspn_field['id'], $_POST) ? 
              TASKSPN_Forms::taskspn_sanitizer(
                wp_unslash($_POST[$taskspn_field['id']]),
                $taskspn_field['input'], 
                !empty($taskspn_field['type']) ? $taskspn_field['type'] : '',
                $taskspn_field // Pass the entire field config
              ) : '';

            if (!empty($taskspn_input)) {
              switch ($taskspn_input) {
                case 'input':
                  if (array_key_exists('type', $taskspn_field) && $taskspn_field['type'] == 'checkbox') {
                    if (isset($_POST[$taskspn_field['id']])) {
                      update_post_meta($post_id, $taskspn_field['id'], $taskspn_value);
                    } else {
                      update_post_meta($post_id, $taskspn_field['id'], '');
                    }
                  } else {
                    update_post_meta($post_id, $taskspn_field['id'], $taskspn_value);
                  }

                  break;
                case 'select':
                  if (array_key_exists('multiple', $taskspn_field) && $taskspn_field['multiple']) {
                    $multi_array = [];
                    $empty = true;

                    if (array_key_exists($taskspn_field['id'], $_POST) && !empty($_POST[$taskspn_field['id']])) {
                      foreach (wp_unslash($_POST[$taskspn_field['id']]) as $multi_value) {
                        if (!empty($multi_value)) {
                          $empty = false;
                          $multi_array[] = TASKSPN_Forms::taskspn_sanitizer(
                            $multi_value, 
                            $taskspn_field['input'], 
                            !empty($taskspn_field['type']) ? $taskspn_field['type'] : '',
                            $taskspn_field // Pass the entire field config
                          );
                        }
                      }
                    }

                    update_post_meta($post_id, $taskspn_field['id'], $empty ? [] : $multi_array);
                    
                    // If this is the owners field and it's empty, assign the author
                    if ($taskspn_field['id'] === 'taskspn_task_owners' && $empty) {
                      $task = get_post($post_id);
                      if ($task && !empty($task->post_author)) {
                        update_post_meta($post_id, 'taskspn_task_owners', [$task->post_author]);
                      }
                    }
                  } else {
                    update_post_meta($post_id, $taskspn_field['id'], $taskspn_value);
                  }
                  
                  break;
                case 'html_multi':
                  foreach ($taskspn_field['html_multi_fields'] as $taskspn_multi_field) {
                    if (array_key_exists($taskspn_multi_field['id'], $_POST)) {
                      $multi_array = [];
                      $empty = true;

                      // Sanitize the POST data before using it
                      $sanitized_post_data = isset($_POST[$taskspn_multi_field['id']]) ? 
                        array_map(function($value) {
                            return sanitize_text_field(wp_unslash($value));
                        }, (array)$_POST[$taskspn_multi_field['id']]) : [];
                      
                      foreach ($sanitized_post_data as $multi_value) {
                        if (!empty($multi_value)) {
                          $empty = false;
                        }

                        $multi_array[] = TASKSPN_Forms::taskspn_sanitizer(
                          $multi_value, 
                          $taskspn_multi_field['input'], 
                          !empty($taskspn_multi_field['type']) ? $taskspn_multi_field['type'] : '',
                          $taskspn_multi_field // Pass the entire field config
                        );
                      }

                      if (!$empty) {
                        update_post_meta($post_id, $taskspn_multi_field['id'], $multi_array);
                      } else {
                        update_post_meta($post_id, $taskspn_multi_field['id'], '');
                      }
                    }
                  }

                  break;
                case 'tags':
                  // Handle tags field - save as array
                  $tags_array_field_name = $taskspn_field['id'] . '_tags_array';
                  if (array_key_exists($tags_array_field_name, $_POST)) {
                    $tags_json = TASKSPN_Forms::taskspn_sanitizer(
                      wp_unslash($_POST[$tags_array_field_name]),
                      'input',
                      'text',
                      $taskspn_field
                    );
                    
                    // Decode JSON and save as array
                    $tags_array = json_decode($tags_json, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($tags_array)) {
                      update_post_meta($post_id, $taskspn_field['id'], $tags_array);
                    } else {
                      // Fallback: treat as comma-separated string
                      $tags_string = TASKSPN_Forms::taskspn_sanitizer(
                        wp_unslash($_POST[$taskspn_field['id']]),
                        'input',
                        'text',
                        $taskspn_field
                      );
                      $tags_array = array_map('trim', explode(',', $tags_string));
                      $tags_array = array_filter($tags_array); // Remove empty values
                      update_post_meta($post_id, $taskspn_field['id'], $tags_array);
                    }
                  } else {
                    // Fallback: save the text input value as comma-separated array
                    $tags_string = TASKSPN_Forms::taskspn_sanitizer(
                      wp_unslash($_POST[$taskspn_field['id']]),
                      'input',
                      'text',
                      $taskspn_field
                    );
                    $tags_array = array_map('trim', explode(',', $tags_string));
                    $tags_array = array_filter($tags_array); // Remove empty values
                    update_post_meta($post_id, $taskspn_field['id'], $tags_array);
                  }
                  break;
                default:
                  update_post_meta($post_id, $taskspn_field['id'], $taskspn_value);
                  break;
              }
            }
          } else {
            update_post_meta($post_id, $taskspn_field['id'], '');
          }
        }
        
        // After saving all fields, ensure author is assigned if no owners were set
        $taskspn_owners = get_post_meta($post_id, 'taskspn_task_owners', true);
        $task = get_post($post_id);
        
        if ($task && !empty($task->post_author)) {
          // Check if owners field is empty or not set
          $owners_empty = false;
          if (empty($taskspn_owners)) {
            $owners_empty = true;
          } elseif (is_array($taskspn_owners)) {
            $taskspn_owners = array_filter($taskspn_owners, function($owner) {
              return !empty($owner);
            });
            if (empty($taskspn_owners)) {
              $owners_empty = true;
            }
          }
          
          if ($owners_empty) {
            update_post_meta($post_id, 'taskspn_task_owners', [$task->post_author]);
          } else {
            // Ensure author is always included even if other owners are set
            if (is_array($taskspn_owners)) {
              if (!in_array($task->post_author, $taskspn_owners)) {
                $taskspn_owners[] = $task->post_author;
                update_post_meta($post_id, 'taskspn_task_owners', array_unique($taskspn_owners));
              }
            }
          }
        }
      }
    }
  }

  public function taskspn_task_form_save($element_id, $key_value, $taskspn_form_type, $taskspn_form_subtype) {
    $post_type = !empty(get_post_type($element_id)) ? get_post_type($element_id) : 'taskspn_task';

    if ($post_type == 'taskspn_task') {
      switch ($taskspn_form_type) {
        case 'post':
          switch ($taskspn_form_subtype) {
            case 'post_new':
              if (!empty($key_value)) {
                foreach ($key_value as $key => $value) {
                  if (strpos((string)$key, 'taskspn_') !== false) {
                    ${$key} = $value;
                    delete_post_meta($element_id, $key);
                  }
                }
              }

              $post_functions = new TASKSPN_Functions_Post();
              $current_user_id = get_current_user_id();
              $task_id = $post_functions->taskspn_insert_post(esc_html($taskspn_task_title), $taskspn_task_description, '', sanitize_title(esc_html($taskspn_task_title)), 'taskspn_task', 'publish', $current_user_id);

              if (!empty($key_value)) {
                foreach ($key_value as $key => $value) {
                  update_post_meta($task_id, $key, $value);
                }
              }
              
              // Set default owner if not set or empty
              $taskspn_owners = get_post_meta($task_id, 'taskspn_task_owners', true);
              $owners_empty = false;
              
              if (empty($taskspn_owners)) {
                $owners_empty = true;
              } elseif (is_array($taskspn_owners)) {
                // Filter out empty values
                $taskspn_owners = array_filter($taskspn_owners, function($owner) {
                  return !empty($owner);
                });
                if (empty($taskspn_owners)) {
                  $owners_empty = true;
                }
              }
              
              if ($owners_empty) {
                update_post_meta($task_id, 'taskspn_task_owners', [$current_user_id]);
              } else {
                // Ensure creator is always included
                if (is_array($taskspn_owners)) {
                  if (!in_array($current_user_id, $taskspn_owners)) {
                    $taskspn_owners[] = $current_user_id;
                    update_post_meta($task_id, 'taskspn_task_owners', array_unique($taskspn_owners));
                  }
                }
              }

              // Repeated tasks are now calculated dynamically in the calendar
              break;
            case 'post_edit':
              if (!empty($key_value)) {
                foreach ($key_value as $key => $value) {
                  if (strpos((string)$key, 'taskspn_') !== false) {
                    ${$key} = $value;
                    delete_post_meta($element_id, $key);
                  }
                }
              }

              $task_id = $element_id;
              $task = get_post($task_id);
              wp_update_post(['ID' => $task_id, 'post_title' => $taskspn_task_title, 'post_content' => $taskspn_task_description,]);

              if (!empty($key_value)) {
                foreach ($key_value as $key => $value) {
                  update_post_meta($task_id, $key, $value);
                }
              }
              
              // Ensure task author is always included in owners
              $taskspn_owners = get_post_meta($task_id, 'taskspn_task_owners', true);
              $task_author = $task ? $task->post_author : 0;
              
              if (!empty($task_author)) {
                $owners_empty = false;
                
                if (empty($taskspn_owners)) {
                  $owners_empty = true;
                } elseif (is_array($taskspn_owners)) {
                  // Filter out empty values
                  $taskspn_owners = array_filter($taskspn_owners, function($owner) {
                    return !empty($owner);
                  });
                  if (empty($taskspn_owners)) {
                    $owners_empty = true;
                  }
                } else {
                  // Single value, convert to array
                  $taskspn_owners = [intval($taskspn_owners)];
                }
                
                if ($owners_empty) {
                  update_post_meta($task_id, 'taskspn_task_owners', [$task_author]);
                } else {
                  // Ensure author is always included
                  if (is_array($taskspn_owners)) {
                    if (!in_array($task_author, $taskspn_owners)) {
                      $taskspn_owners[] = $task_author;
                      update_post_meta($task_id, 'taskspn_task_owners', array_unique($taskspn_owners));
                    }
                  }
                }
              }

              // Repeated tasks are now calculated dynamically in the calendar
              break;
          }
      }
    }
  }

  public function taskspn_task_register_scripts() {
    if (!wp_script_is('taskspn-aux', 'registered')) {
      wp_register_script('taskspn-aux', TASKSPN_URL . 'assets/js/taskspn-aux.js', [], TASKSPN_VERSION, true);
    }

    if (!wp_script_is('taskspn-forms', 'registered')) {
      wp_register_script('taskspn-forms', TASKSPN_URL . 'assets/js/taskspn-forms.js', [], TASKSPN_VERSION, true);
    }
    
    if (!wp_script_is('taskspn-selector', 'registered')) {
      wp_register_script('taskspn-selector', TASKSPN_URL . 'assets/js/taskspn-selector.js', [], TASKSPN_VERSION, true);
    }
  }

  public function taskspn_task_print_scripts() {
    wp_print_scripts(['taskspn-aux', 'taskspn-forms', 'taskspn-selector']);
  }

  public function taskspn_task_list_wrapper() {
    ob_start();
    ?>
      <div class="taskspn-cpt-list taskspn-taskspn_task-list taskspn-mb-100 taskspn-max-width-700 taskspn-margin-auto">
        <div class="taskspn-cpt-search-container taskspn-mb-20 taskspn-text-align-right">
          <div class="taskspn-cpt-search-wrapper taskspn-taskspn_task-search-wrapper">
            <input type="text" class="taskspn-cpt-search-input taskspn-taskspn_task-search-input taskspn-input taskspn-display-none" placeholder="<?php esc_attr_e('Filter...', 'taskspn'); ?>" />
            <i class="material-icons-outlined taskspn-cpt-search-toggle taskspn-taskspn_task-search-toggle taskspn-cursor-pointer taskspn-font-size-30 taskspn-vertical-align-middle taskspn-tooltip" title="<?php esc_attr_e('Search Tasks', 'taskspn'); ?>">search</i>
            
            <a href="#" class="taskspn-popup-open-ajax taskspn-text-decoration-none" data-taskspn-popup-id="taskspn-popup-taskspn_task-add" data-taskspn-ajax-type="taskspn_task_new">
              <i class="material-icons-outlined taskspn-cursor-pointer taskspn-font-size-30 taskspn-vertical-align-middle taskspn-tooltip" title="<?php esc_attr_e('Add new Task', 'taskspn'); ?>">add</i>
            </a>
          </div>
        </div>

        <div class="taskspn-cpt-list-wrapper taskspn-taskspn_task-list-wrapper">
          <?php echo wp_kses(self::taskspn_task_list(), TASKSPN_KSES); ?>
        </div>
      </div>
    <?php
    $taskspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $taskspn_return_string;
  }

  public function taskspn_task_list() {
    $task_atts = [
      'fields' => 'ids',
      'numberposts' => -1,
      'post_type' => 'taskspn_task',
      'post_status' => 'any', 
      'orderby' => 'menu_order', 
      'order' => 'ASC',
      'meta_query' => [
        [
          'key' => 'taskspn_repeated_from',
          'compare' => 'NOT EXISTS'
        ]
      ]
    ];
    
    if (class_exists('Polylang')) {
      $task_atts['lang'] = pll_current_language('slug');
    }

    $task = get_posts($task_atts);

    // Filter assets based on user permissions
    $task = TASKSPN_Functions_User::taskspn_filter_user_posts($task, 'taskspn_task');

    ob_start();
    ?>
      <ul class="taskspn-tasks taskspn-list-style-none taskspn-p-0 taskspn-margin-auto">
        <?php if (!empty($task)): ?>
          <?php foreach ($task as $task_id): ?>
            <?php $is_completed = get_post_meta($task_id, 'taskspn_task_completed', true) === 'on'; ?>
            <li class="taskspn-task taskspn-taskspn_task-list-item taskspn-mb-10 <?php echo $is_completed ? 'taskspn-completed' : ''; ?>" data-taskspn_task-id="<?php echo esc_attr($task_id); ?>">
              <div class="taskspn-display-table taskspn-width-100-percent">
                <div class="taskspn-display-inline-table taskspn-width-80-percent">
                  <a href="#" class="taskspn-popup-open-ajax taskspn-text-decoration-none" data-taskspn-popup-id="taskspn-popup-taskspn_task-view" data-taskspn-ajax-type="taskspn_task_view">
                    <span><?php echo esc_html(get_the_title($task_id)); ?></span>
                  </a>
                </div>

                <div class="taskspn-display-inline-table taskspn-width-20-percent taskspn-text-align-right taskspn-position-relative">
                  <a href="#" class="taskspn-toggle-completed taskspn-ml-10 taskspn-tooltip" title="<?php echo $is_completed ? esc_attr__('Mark as not done', 'taskspn') : esc_attr__('Mark as done', 'taskspn'); ?>" aria-label="<?php echo $is_completed ? esc_attr__('Mark as not done', 'taskspn') : esc_attr__('Mark as done', 'taskspn'); ?>">
                    <i class="material-icons-outlined taskspn-font-size-30 taskspn-vertical-align-middle <?php echo $is_completed ? 'taskspn-color-green' : ''; ?>"><?php echo $is_completed ? 'task_alt' : 'circle'; ?></i>
                  </a>
                  <i class="material-icons-outlined taskspn-menu-more-btn taskspn-cursor-pointer taskspn-vertical-align-middle taskspn-font-size-30">more_vert</i>

                  <div class="taskspn-menu-more taskspn-z-index-99 taskspn-display-none-soft">
                    <ul class="taskspn-list-style-none">
                      <li>
                        <a href="#" class="taskspn-popup-open-ajax taskspn-text-decoration-none" data-taskspn-popup-id="taskspn-popup-taskspn_task-view" data-taskspn-ajax-type="taskspn_task_view">
                          <div class="taskspn-display-table taskspn-width-100-percent">
                            <div class="taskspn-display-inline-table taskspn-width-70-percent">
                              <p><?php esc_html_e('View Task', 'taskspn'); ?></p>
                            </div>
                            <div class="taskspn-display-inline-table taskspn-width-20-percent taskspn-text-align-right">
                              <i class="material-icons-outlined taskspn-vertical-align-middle taskspn-font-size-30 taskspn-ml-30">visibility</i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li>
                        <a href="#" class="taskspn-popup-open-ajax taskspn-text-decoration-none" data-taskspn-popup-id="taskspn-popup-taskspn_task-edit" data-taskspn-ajax-type="taskspn_task_edit"> 
                          <div class="taskspn-display-table taskspn-width-100-percent">
                            <div class="taskspn-display-inline-table taskspn-width-70-percent">
                              <p><?php esc_html_e('Edit Task', 'taskspn'); ?></p>
                            </div>
                            <div class="taskspn-display-inline-table taskspn-width-20-percent taskspn-text-align-right">
                              <i class="material-icons-outlined taskspn-vertical-align-middle taskspn-font-size-30 taskspn-ml-30">edit</i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li>
                        <a href="#" class="taskspn-taskspn_task-duplicate-post">
                          <div class="taskspn-display-table taskspn-width-100-percent">
                            <div class="taskspn-display-inline-table taskspn-width-70-percent">
                              <p><?php esc_html_e('Duplicate Task', 'taskspn'); ?></p>
                            </div>
                            <div class="taskspn-display-inline-table taskspn-width-20-percent taskspn-text-align-right">
                              <i class="material-icons-outlined taskspn-vertical-align-middle taskspn-font-size-30 taskspn-ml-30">copy</i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li>
                        <a href="#" class="taskspn-popup-open" data-taskspn-popup-id="taskspn-popup-taskspn_task-remove">
                          <div class="taskspn-display-table taskspn-width-100-percent">
                            <div class="taskspn-display-inline-table taskspn-width-70-percent">
                              <p><?php esc_html_e('Remove Task', 'taskspn'); ?></p>
                            </div>
                            <div class="taskspn-display-inline-table taskspn-width-20-percent taskspn-text-align-right">
                              <i class="material-icons-outlined taskspn-vertical-align-middle taskspn-font-size-30 taskspn-ml-30">delete</i>
                            </div>
                          </div>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </li>
          <?php endforeach ?>
        <?php endif ?>

        <li class="taskspn-add-new-cpt taskspn-mt-50 taskspn-task" data-taskspn_task-id="0">
          <?php if (is_user_logged_in()): ?>
            <a href="#" class="taskspn-popup-open-ajax taskspn-text-decoration-none" data-taskspn-popup-id="taskspn-popup-taskspn_task-add" data-taskspn-ajax-type="taskspn_task_new">
              <div class="taskspn-display-table taskspn-width-100-percent">
                <div class="taskspn-display-inline-table taskspn-width-20-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-text-align-center">
                  <i class="material-icons-outlined taskspn-cursor-pointer taskspn-vertical-align-middle taskspn-font-size-30 taskspn-width-25">add</i>
                </div>
                <div class="taskspn-display-inline-table taskspn-width-80-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent">
                  <?php esc_html_e('Add new Task', 'taskspn'); ?>
                </div>
              </div>
            </a>
          <?php endif ?>
        </li>
      </ul>
    <?php
    $taskspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $taskspn_return_string;
  }

  public function taskspn_task_view($task_id) {
    // Validate task ID
    if (empty($task_id) || !is_numeric($task_id)) {
      return '<div class="taskspn_task-view taskspn-p-30"><p class="taskspn-text-align-center">' . esc_html__('Invalid task ID', 'taskspn') . '</p></div>';
    }
    
    // Check if task exists
    $task = get_post($task_id);
    if (!$task || $task->post_type !== 'taskspn_task') {
      return '<div class="taskspn_task-view taskspn-p-30"><p class="taskspn-text-align-center">' . esc_html__('Task not found', 'taskspn') . '</p></div>';
    }
    
    ob_start();
    self::taskspn_task_register_scripts();
    self::taskspn_task_print_scripts();
    ?>
      <div class="taskspn_task-view taskspn-p-30" data-taskspn_task-id="<?php echo esc_attr($task_id); ?>">
        <h4 class="taskspn-text-align-center"><?php echo esc_html(get_the_title($task_id)); ?></h4>
        
        <div class="taskspn-word-wrap-break-word">
          <?php 
          $task_content = '';
          if ($task && !empty($task->post_content)) {
            $task_content = str_replace(']]>', ']]&gt;', apply_filters('the_content', $task->post_content));
          }
          if (!empty($task_content)) {
            echo '<p>' . wp_kses($task_content, TASKSPN_KSES) . '</p>';
          }
          ?>
        </div>

        <div class="taskspn_task-view-list">
          <?php 
          try {
            $all_fields = self::taskspn_task_get_all_fields($task_id);
            foreach ($all_fields as $taskspn_field): 
              if (empty($taskspn_field['id']) || in_array($taskspn_field['id'], ['taskspn_task_title', 'taskspn_task_description', 'taskspn_ajax_nonce', 'taskspn_task_form'])) {
                continue;
              }
          ?>
            <?php echo wp_kses(TASKSPN_Forms::taskspn_input_display_wrapper($taskspn_field, 'post', $task_id), TASKSPN_KSES); ?>
          <?php 
            endforeach;
          } catch (Exception $e) {
            echo '<p class="taskspn-text-align-center">' . esc_html__('Error loading task fields', 'taskspn') . '</p>';
          }
          ?>

          <div class="taskspn-text-align-right taskspn-task" data-taskspn_task-id="<?php echo esc_attr($task_id); ?>">
            <a href="#" class="taskspn-btn taskspn-btn-mini taskspn-popup-open-ajax" data-taskspn-popup-id="taskspn-popup-taskspn_task-edit" data-taskspn-ajax-type="taskspn_task_edit"><?php esc_html_e('Edit Task', 'taskspn'); ?></a>
          </div>
        </div>
      </div>
    <?php
    $taskspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $taskspn_return_string;
  }

  public function taskspn_task_new() {
    if (!is_user_logged_in()) {
      wp_die(esc_html__('You must be logged in to create a new asset.', 'taskspn'), esc_html__('Access Denied', 'taskspn'), ['response' => 403]);
    }

    ob_start();
    self::taskspn_task_register_scripts();
    self::taskspn_task_print_scripts();
    ?>
      <div class="taskspn_task-new taskspn-p-30">
        <a href="#" class="taskspn-popup-close taskspn-text-decoration-none taskspn-close-icon"><i class="material-icons-outlined">close</i></a>
        
        <h4 class="taskspn-mb-30"><?php esc_html_e('Add new Task', 'taskspn'); ?></h4>

        <form action="" method="post" id="taskspn-task-form-new" class="taskspn-form">      
          <?php foreach (self::taskspn_task_get_all_fields(0) as $taskspn_field): ?>
            <?php echo wp_kses(TASKSPN_Forms::taskspn_input_wrapper_builder($taskspn_field, 'post'), TASKSPN_KSES); ?>
          <?php endforeach ?>

          <div class="taskspn-text-align-right">
            <input class="taskspn-btn" data-taskspn-type="post" data-taskspn-subtype="post_new" data-taskspn-post-type="taskspn_task" type="submit" value="<?php esc_attr_e('Create Task', 'taskspn'); ?>"/>
          </div>
        </form> 
      </div>
    <?php
    $taskspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $taskspn_return_string;
  }

  public function taskspn_task_edit($task_id) {
    ob_start();
    self::taskspn_task_register_scripts();
    self::taskspn_task_print_scripts();
    ?>
      <div class="taskspn_task-edit taskspn-p-30">
        <a href="#" class="taskspn-popup-close taskspn-text-decoration-none taskspn-close-icon"><i class="material-icons-outlined">close</i></a>
        
        <p class="taskspn-text-align-center taskspn-mb-0 taskspn-font-size-small"><?php esc_html_e('Editing Task', 'taskspn'); ?></p>
        
        <h4 class="taskspn-text-align-center taskspn-mb-30"><?php echo esc_html(get_the_title($task_id)); ?></h4>

        <form action="" method="post" id="taskspn-task-form-edit" class="taskspn-form">      
          <?php foreach (self::taskspn_task_get_all_fields($task_id) as $taskspn_field): ?>
            <?php echo wp_kses(TASKSPN_Forms::taskspn_input_wrapper_builder($taskspn_field, 'post', $task_id), TASKSPN_KSES); ?>
          <?php endforeach ?>

          <div class="taskspn-text-align-right">
            <input class="taskspn-btn" type="submit" data-taskspn-type="post" data-taskspn-subtype="post_edit" data-taskspn-post-type="taskspn_task" data-taskspn-post-id="<?php echo esc_attr($task_id); ?>" value="<?php esc_attr_e('Save Task', 'taskspn'); ?>"/>
          </div>
        </form> 
      </div>
    <?php
    $taskspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $taskspn_return_string;
  }

  public function taskspn_task_history_add($task_id) {  
    $taskspn_meta = get_post_meta($task_id);
    $taskspn_meta_array = [];

    if (!empty($taskspn_meta)) {
      foreach ($taskspn_meta as $taskspn_meta_key => $taskspn_meta_value) {
        if (strpos((string)$taskspn_meta_key, 'taskspn_') !== false && !empty($taskspn_meta_value[0])) {
          $taskspn_meta_array[$taskspn_meta_key] = $taskspn_meta_value[0];
        }
      }
    }
    
    if(empty(get_post_meta($task_id, 'taskspn_task_history', true))) {
      update_post_meta($task_id, 'taskspn_task_history', [strtotime('now') => $taskspn_meta_array]);
    } else {
      $taskspn_post_meta_new = get_post_meta($task_id, 'taskspn_task_history', true);
      $taskspn_post_meta_new[strtotime('now')] = $taskspn_meta_array;
      update_post_meta($task_id, 'taskspn_task_history', $taskspn_post_meta_new);
    }
  }

  public function taskspn_task_get_next($task_id) {
    // Try new periodicity fields first, fallback to old field
    $taskspn_task_periodicity_value = get_post_meta($task_id, 'taskspn_task_periodicity_value', true);
    $taskspn_task_periodicity_type = get_post_meta($task_id, 'taskspn_task_periodicity_type', true);
    
    // Build periodicity string
    $taskspn_task_periodicity = '';
    if (!empty($taskspn_task_periodicity_value) && !empty($taskspn_task_periodicity_type)) {
      switch ($taskspn_task_periodicity_type) {
        case 'days':
          $taskspn_task_periodicity = $taskspn_task_periodicity_value . ' days';
          break;
        case 'weeks':
          $taskspn_task_periodicity = ($taskspn_task_periodicity_value * 7) . ' days';
          break;
        case 'months':
          $taskspn_task_periodicity = $taskspn_task_periodicity_value . ' months';
          break;
      }
    } else {
      // Fallback to old field
      $taskspn_task_periodicity = get_post_meta($task_id, 'taskspn_task_periodicity', true);
    }
    
    $taskspn_task_date = get_post_meta($task_id, 'taskspn_task_date', true);
    $taskspn_task_time = get_post_meta($task_id, 'taskspn_task_time', true);

    $taskspn_task_timestamp = strtotime($taskspn_task_date . ' ' . $taskspn_task_time);

    if (!empty($taskspn_task_periodicity) && !empty($taskspn_task_timestamp)) {
      $now = strtotime('now');

      while ($taskspn_task_timestamp < $now) {
        $taskspn_task_timestamp = strtotime('+' . $taskspn_task_periodicity, $taskspn_task_timestamp);
      }

      return $taskspn_task_timestamp;
    }
  }

  /**
   * Get users list for select field
   * 
   * @return array Array of user ID => display name
   */
  public static function taskspn_get_users_for_select() {
    $users = get_users([
      'orderby' => 'display_name',
      'order' => 'ASC',
    ]);
    
    $users_array = [];
    foreach ($users as $user) {
      $display_name = TASKSPN_Functions_User::taskspn_user_get_name($user->ID);
      $users_array[$user->ID] = $display_name . ' (' . $user->user_email . ')';
    }
    
    return $users_array;
  }

  public function taskspn_task_owners($task_id) {
    $taskspn_owners = get_post_meta($task_id, 'taskspn_task_owners', true);
    $task = get_post($task_id);
    $taskspn_owners_array = [];
    
    // Always include the author
    if (!empty($task) && !empty($task->post_author)) {
      $taskspn_owners_array[] = $task->post_author;
    }

    if (!empty($taskspn_owners)) {
      if (is_array($taskspn_owners)) {
        foreach ($taskspn_owners as $owner_id) {
          $taskspn_owners_array[] = intval($owner_id);
        }
      } else {
        $taskspn_owners_array[] = intval($taskspn_owners);
      }
    }

    return array_unique($taskspn_owners_array);
  }

  /**
   * Generate repeated task instances based on periodicity
   * 
   * @since    1.0.0
   * @param    int    $task_id    Task ID to generate instances for
   * @return   void
   */
  public function taskspn_task_generate_repeated($task_id) {
    // Check if this task should be repeated
    $taskspn_task_repeat = get_post_meta($task_id, 'taskspn_task_repeat', true);
    
    if ($taskspn_task_repeat !== 'on') {
      return;
    }

    // Get task data
    $task = get_post($task_id);
    $taskspn_task_periodicity_value = get_post_meta($task_id, 'taskspn_task_periodicity_value', true);
    $taskspn_task_periodicity_type = get_post_meta($task_id, 'taskspn_task_periodicity_type', true);
    $taskspn_task_date = get_post_meta($task_id, 'taskspn_task_date', true);
    $taskspn_task_time = get_post_meta($task_id, 'taskspn_task_time', true);
    $taskspn_task_repeat_until = get_post_meta($task_id, 'taskspn_task_repeat_until', true);

    // Build periodicity string from value and type
    $taskspn_task_periodicity = '';
    if (!empty($taskspn_task_periodicity_value) && !empty($taskspn_task_periodicity_type)) {
      // Convert to days for strtotime calculation
      switch ($taskspn_task_periodicity_type) {
        case 'days':
          $taskspn_task_periodicity = $taskspn_task_periodicity_value . ' days';
          break;
        case 'weeks':
          $taskspn_task_periodicity = ($taskspn_task_periodicity_value * 7) . ' days';
          break;
        case 'months':
          $taskspn_task_periodicity = $taskspn_task_periodicity_value . ' months';
          break;
      }
    }

    // If no periodicity or date configured, exit
    if (empty($taskspn_task_periodicity) || empty($taskspn_task_date)) {
      return;
    }

    // If repeat until date is set, use it; otherwise repeat for 1 year
    if (!empty($taskspn_task_repeat_until)) {
      $end_timestamp = strtotime($taskspn_task_repeat_until);
    } else {
      $end_timestamp = strtotime('+1 year', strtotime($taskspn_task_date));
    }

    $current_timestamp = strtotime($taskspn_task_date . ' ' . $taskspn_task_time);
    
    // Get all existing repeated instances to avoid duplicates
    $existing_repeated = get_posts([
      'fields' => 'ids',
      'post_type' => 'taskspn_task',
      'post_status' => 'any',
      'meta_query' => [
        [
          'key' => 'taskspn_repeated_from',
          'value' => $task_id,
          'compare' => '='
        ]
      ]
    ]);

    // Calculate next occurrence
    $next_timestamp = strtotime('+' . $taskspn_task_periodicity, $current_timestamp);
    
    // Generate tasks until the end date
    $max_instances = 100; // Safety limit to prevent infinite loops
    $instance_count = 0;
    
    while ($next_timestamp <= $end_timestamp && $instance_count < $max_instances) {
      // Check if this instance already exists
      $instance_exists = false;
      foreach ($existing_repeated as $existing_id) {
        $existing_date = get_post_meta($existing_id, 'taskspn_task_date', true);
        $existing_time = get_post_meta($existing_id, 'taskspn_task_time', true);
        $existing_timestamp = strtotime($existing_date . ' ' . $existing_time);
        
        if ($existing_timestamp == $next_timestamp) {
          $instance_exists = true;
          break;
        }
      }

      // If instance doesn't exist, create it
      if (!$instance_exists) {
        $next_date = date('Y-m-d', $next_timestamp);
        $next_time = !empty($taskspn_task_time) ? $taskspn_task_time : '00:00';
        
        // Prepare meta data for the new instance
        $all_meta = get_post_meta($task_id);
        $new_meta = [];
        
        // Get all task-specific meta
        foreach ($all_meta as $key => $value) {
          if (strpos((string)$key, 'taskspn_') !== false) {
            // Skip the repeat and periodicity fields for the new instance
            if ($key !== 'taskspn_task_repeat' && 
                $key !== 'taskspn_task_periodicity' && 
                $key !== 'taskspn_task_periodicity_value' && 
                $key !== 'taskspn_task_periodicity_type' && 
                $key !== 'taskspn_task_repeat_until') {
              $new_meta[$key] = maybe_unserialize($value[0]);
              
              // Update date and time for this instance
              if ($key === 'taskspn_task_date') {
                $new_meta[$key] = $next_date;
              } elseif ($key === 'taskspn_task_time') {
                $new_meta[$key] = $next_time;
              }
            }
          }
        }
        
        // Add metadata to link this instance to the original task
        $new_meta['taskspn_repeated_from'] = $task_id;
        
        // Create the new task instance
        $post_functions = new TASKSPN_Functions_Post();
        $title_with_date = $task->post_title . ' - ' . date_i18n(get_option('date_format'), $next_timestamp);
        
        // Use unique slug for each repeated instance to avoid overwriting
        $unique_slug = sanitize_title($title_with_date) . '-' . $next_timestamp;
        
        $new_task_id = $post_functions->taskspn_insert_post(
          $title_with_date,
          $task->post_content,
          $task->post_excerpt,
          $unique_slug,
          'taskspn_task',
          'publish',
          $task->post_author,
          0,
          [],
          [],
          $new_meta,
          false // Don't overwrite - create new instance each time
        );
        
        if ($new_task_id) {
          $instance_count++;
        }
      }
      
      // Move to next occurrence
      $next_timestamp = strtotime('+' . $taskspn_task_periodicity, $next_timestamp);
    }
  }
}