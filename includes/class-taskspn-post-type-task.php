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
  /**
   * Try to resolve the public calendar page URL (page using [taskspn-calendar])
   */
  private static function taskspn_get_calendar_url() {
    // Find first published page containing our calendar shortcode
    $pages = get_posts([
      'post_type' => 'page',
      'post_status' => 'publish',
      'numberposts' => -1,
      's' => '[taskspn-calendar',
      'fields' => 'ids',
    ]);
    if (!empty($pages)) {
      foreach ($pages as $page_id) {
        $content = get_post_field('post_content', $page_id);
        if ($content && has_shortcode($content, 'taskspn-calendar')) {
          return get_permalink($page_id);
        }
      }
    }
    // Fallback: home
    return home_url('/');
  }

  /**
   * Send an email via MailPN to a user (by ID or email)
   */
  private static function taskspn_mailpn_send($to, $subject, $html_content, $mailpn_type = 'email_coded') {
    // If MailPN is available, prefer direct class usage
    if (class_exists('MAILPN_Mailing')) {
      try {
        $mailing = new MAILPN_Mailing();
        $mailing->mailpn_sender([
          'mailpn_user_to' => $to,
          'mailpn_type' => $mailpn_type,
          'mailpn_subject' => $subject,
        ], $html_content);
        return true;
      } catch (Exception $e) {
        // Silently ignore to avoid breaking save flow
        return false;
      }
    }

    // Fallback to shortcode if available
    if (shortcode_exists('mailpn-sender')) {
      $shortcode = '[mailpn-sender mailpn_user_to="' . esc_attr($to) . '" mailpn_type="' . esc_attr($mailpn_type) . '" mailpn_subject="' . esc_attr($subject) . '"]'
        . $html_content . '[/mailpn-sender]';
      do_shortcode($shortcode);
      return true;
    }

    // MailPN not available; do nothing (admin notice will inform in admin area)
    return false;
  }

  /**
   * Compose and send task assignment email to a single user (ID or email)
   */
  private static function taskspn_notify_assignment($task_id, $user_to) {
    $task = get_post($task_id);
    if (!$task) { return; }

    $title = get_the_title($task_id);
    $desc = $task->post_content;
    $date = get_post_meta($task_id, 'taskspn_task_date', true);
    $time = get_post_meta($task_id, 'taskspn_task_time', true);
    $calendar_url = self::taskspn_get_calendar_url();

    /* translators: %s: Task title */
    $subject = sprintf(__('New task assigned: %s', 'taskspn'), $title);
    $date_line = '';
    if (!empty($date)) {
      $formatted = date_i18n(get_option('date_format'), strtotime($date));
      if (!empty($time)) {
        $formatted .= ' ' . $time;
      }
      $date_line = '<p><strong>' . esc_html__('Scheduled for', 'taskspn') . ':</strong> ' . esc_html($formatted) . '</p>';
    }

    $taskspn_the_content_hook = 'the_content';
    $content = ''
      . '<h2>' . esc_html($title) . '</h2>'
      . $date_line
      . (!empty($desc) ? wpautop(wp_kses_post(apply_filters($taskspn_the_content_hook, $desc))) : '')
      . '<p><a href="' . esc_url($calendar_url) . '">' . esc_html__('Open calendar', 'taskspn') . '</a></p>';

    self::taskspn_mailpn_send($user_to, $subject, $content, 'email_coded');
  }
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
      $taskspn_the_content_hook = 'the_content';
      $taskspn_fields['taskspn_task_description'] = [
        'id' => 'taskspn_task_description',
        'class' => 'taskspn-input taskspn-width-100-percent',
        'input' => 'textarea',
        'required' => true,
        'value' => !empty($task_id) ? (str_replace(']]>', ']]&gt;', apply_filters($taskspn_the_content_hook, get_post($task_id)->post_content))) : '',
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
      $taskspn_fields_meta['taskspn_task_icon'] = [
        'id' => 'taskspn_task_icon',
        'class' => 'taskspn-select taskspn-width-100-percent',
        'input' => 'select',
        'label' => __('Task icon', 'taskspn'),
        'placeholder' => __('Select an icon', 'taskspn'),
        'value' => !empty($task_id) ? esc_attr(get_post_meta($task_id, 'taskspn_task_icon', true)) : '',
        'description' => __('Select a Material Icons icon to display in the calendar view. Leave empty to use default.', 'taskspn'),
        'options' => [
          '' => __('Default (event)', 'taskspn'),
          'event' => __('Event', 'taskspn'),
          'task' => __('Task', 'taskspn'),
          'task_alt' => __('Task Alt', 'taskspn'),
          'alarm' => __('Alarm', 'taskspn'),
          'schedule' => __('Schedule', 'taskspn'),
          'calendar_today' => __('Calendar Today', 'taskspn'),
          'school' => __('School', 'taskspn'),
          'work' => __('Work', 'taskspn'),
          'home' => __('Home', 'taskspn'),
          'shopping_cart' => __('Shopping', 'taskspn'),
          'restaurant' => __('Restaurant', 'taskspn'),
          'local_hospital' => __('Hospital', 'taskspn'),
          'flight' => __('Flight', 'taskspn'),
          'directions_car' => __('Car', 'taskspn'),
          'fitness_center' => __('Fitness', 'taskspn'),
          'movie' => __('Movie', 'taskspn'),
          'music_note' => __('Music', 'taskspn'),
          'book' => __('Book', 'taskspn'),
          'sports_soccer' => __('Sports', 'taskspn'),
          'beach_access' => __('Beach', 'taskspn'),
          'cake' => __('Cake', 'taskspn'),
          'child_care' => __('Child Care', 'taskspn'),
          'pets' => __('Pets', 'taskspn'),
          'favorite' => __('Favorite', 'taskspn'),
          'star' => __('Star', 'taskspn'),
          'check_circle' => __('Check Circle', 'taskspn'),
          'info' => __('Info', 'taskspn'),
          'warning' => __('Warning', 'taskspn'),
          'error' => __('Error', 'taskspn'),
          'notifications' => __('Notifications', 'taskspn'),
          'email' => __('Email', 'taskspn'),
          'phone' => __('Phone', 'taskspn'),
          'videocam' => __('Video', 'taskspn'),
          'photo_camera' => __('Photo', 'taskspn'),
          'attach_money' => __('Money', 'taskspn'),
          'shopping_bag' => __('Shopping Bag', 'taskspn'),
          'local_grocery_store' => __('Grocery Store', 'taskspn'),
          'restaurant_menu' => __('Restaurant Menu', 'taskspn'),
          'hotel' => __('Hotel', 'taskspn'),
          'directions_bus' => __('Bus', 'taskspn'),
          'train' => __('Train', 'taskspn'),
          'bike_scooter' => __('Bike', 'taskspn'),
          'pool' => __('Pool', 'taskspn'),
          'spa' => __('Spa', 'taskspn'),
          'local_library' => __('Library', 'taskspn'),
          'museum' => __('Museum', 'taskspn'),
          'theater_comedy' => __('Theater', 'taskspn'),
          'celebration' => __('Celebration', 'taskspn'),
        ],
      ];
      $taskspn_fields_meta['taskspn_task_color'] = [
        'id' => 'taskspn_task_color',
        'class' => 'taskspn-input taskspn-width-100-percent',
        'input' => 'input',
        'type' => 'color',
        'label' => __('Task color', 'taskspn'),
        'placeholder' => __('Task color', 'taskspn'),
        'value' => !empty($task_id) ? (get_post_meta($task_id, 'taskspn_task_color', true) ?: (get_option('taskspn_color_main') ?: '#d45500')) : (get_option('taskspn_color_main') ?: '#d45500'),
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
      $taskspn_fields_meta['taskspn_task_category'] = [
        'id' => 'taskspn_task_category',
        'class' => 'taskspn-select taskspn-width-100-percent',
        'input' => 'taxonomy',
        'taxonomy' => 'taskspn_task_category',
        'multiple' => false,
        'allow_new' => true,
        'label' => __('Task category', 'taskspn'),
        'placeholder' => __('Select a category', 'taskspn'),
        'description' => __('Select a category for this task. Categories help organize and color-code tasks in the calendar.', 'taskspn'),
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
      'rewrite'             => false,
      'label'               => esc_html(__('Tasks', 'taskspn')),
      'description'         => esc_html(__('Task description', 'taskspn')),
      'supports'            => ['title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'page-attributes', ],
      'hierarchical'        => true,
      'public'              => false,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'show_in_nav_menus'   => false,
      'show_in_admin_bar'   => true,
      'menu_position'       => 5,
      'menu_icon'           => esc_url(TASKSPN_URL . 'assets/media/taskspn-task-menu-icon.svg'),
      'can_export'          => true,
      'has_archive'         => false,
      'exclude_from_search' => true,
      'publicly_queryable'  => false,
      'capability_type'     => 'page',
      'capabilities'        => constant('TASKSPN_ROLE_TASKSPN_TASK_CAPABILITIES'),
      'taxonomies'          => ['taskspn_task_category'],
      'show_in_rest'        => true, /* Keep Gutenberg editor working */
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
   * Hide task REST endpoints for unauthenticated requests to keep it out of public API
   * but allow Gutenberg/editor to function for logged-in users.
   */
  public function taskspn_hide_task_rest_endpoints($endpoints) {
    if (is_user_logged_in()) {
      return $endpoints;
    }

    if (isset($endpoints['/wp/v2/taskspn_task'])) {
      unset($endpoints['/wp/v2/taskspn_task']);
    }
    // Single item route pattern
    foreach (array_keys($endpoints) as $route) {
      if (preg_match('#^/wp/v2/taskspn_task/\(\?P<id>\[\\d\]\+\)$#', $route)) {
        unset($endpoints[$route]);
      }
    }

    return $endpoints;
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
                wp_unslash(isset($_POST[$taskspn_field['id']]) ? $_POST[$taskspn_field['id']] : ''),
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
                      $post_value = isset($_POST[$taskspn_field['id']]) ? $_POST[$taskspn_field['id']] : [];
                      foreach (wp_unslash($post_value) as $multi_value) {
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
                        }, (array)wp_unslash($_POST[$taskspn_multi_field['id']])) : [];
                      
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
                    $post_value = isset($_POST[$tags_array_field_name]) ? $_POST[$tags_array_field_name] : '';
                    $tags_json = TASKSPN_Forms::taskspn_sanitizer(
                      wp_unslash($post_value),
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
                      $post_value = isset($_POST[$taskspn_field['id']]) ? $_POST[$taskspn_field['id']] : '';
                      $tags_string = TASKSPN_Forms::taskspn_sanitizer(
                        wp_unslash($post_value),
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
                    $post_value = isset($_POST[$taskspn_field['id']]) ? $_POST[$taskspn_field['id']] : '';
                    $tags_string = TASKSPN_Forms::taskspn_sanitizer(
                      wp_unslash($post_value),
                      'input',
                      'text',
                      $taskspn_field
                    );
                    $tags_array = array_map('trim', explode(',', $tags_string));
                    $tags_array = array_filter($tags_array); // Remove empty values
                    update_post_meta($post_id, $taskspn_field['id'], $tags_array);
                  }
                  break;
                case 'taxonomy':
                  // Handle taxonomy field - save as WordPress taxonomy terms
                  $taxonomy = !empty($taskspn_field['taxonomy']) ? $taskspn_field['taxonomy'] : 'category';
                  
                  if (array_key_exists($taskspn_field['id'], $_POST)) {
                    $post_value = isset($_POST[$taskspn_field['id']]) ? $_POST[$taskspn_field['id']] : '';
                    
                    if (array_key_exists('multiple', $taskspn_field) && $taskspn_field['multiple']) {
                      // Multiple selection
                      $term_ids = [];
                      if (!empty($post_value)) {
                        if (is_array($post_value)) {
                          foreach (wp_unslash($post_value) as $term_id) {
                            $term_id = intval($term_id);
                            if ($term_id > 0) {
                              $term_ids[] = $term_id;
                            }
                          }
                        } else {
                          $term_id = intval($post_value);
                          if ($term_id > 0) {
                            $term_ids[] = $term_id;
                          }
                        }
                      }
                      wp_set_post_terms($post_id, $term_ids, $taxonomy, false);
                    } else {
                      // Single selection
                      $term_id = !empty($post_value) ? intval($post_value) : 0;
                      if ($term_id > 0) {
                        wp_set_post_terms($post_id, [$term_id], $taxonomy, false);
                      } else {
                        wp_set_post_terms($post_id, [], $taxonomy, false);
                      }
                    }
                  } else {
                    // No value submitted, clear terms
                    wp_set_post_terms($post_id, [], $taxonomy, false);
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
          // If owners list is empty or resolves to empty, set author as the default owner
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
            case 'post_check':
              // Handle task completion form
              if (!empty($element_id)) {
                // Save completed status
                $completed = isset($key_value['taskspn_task_completed']) && $key_value['taskspn_task_completed'] === 'on';
                
                update_post_meta($element_id, 'taskspn_task_completed', $completed ? 'on' : '');

                if ($completed) {
                  // Store completion metadata
                  update_post_meta($element_id, 'taskspn_task_completed_at', current_time('mysql'));
                  update_post_meta($element_id, 'taskspn_task_completed_by', get_current_user_id());
                }

                // Save comment if provided
                $comment = !empty($key_value['taskspn_task_comment']) ? sanitize_textarea_field($key_value['taskspn_task_comment']) : '';
                
                if (!empty($comment)) {
                  $task_comments = get_post_meta($element_id, 'taskspn_task_comments', true);
                  if (!is_array($task_comments)) {
                    $task_comments = [];
                  }

                  $timestamp = current_time('timestamp');
                  $task_comments[$timestamp] = [
                    'comment' => $comment,
                    'user_id' => get_current_user_id(),
                    'timestamp' => $timestamp,
                  ];

                  update_post_meta($element_id, 'taskspn_task_comments', $task_comments);
                }
              }
              break;
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
                  // Handle taxonomy separately
                  if ($key === 'taskspn_task_category') {
                    $taxonomy = 'taskspn_task_category';
                    $term_id = !empty($value) ? intval($value) : 0;
                    if ($term_id > 0) {
                      wp_set_post_terms($task_id, [$term_id], $taxonomy, false);
                    }
                  } else {
                    update_post_meta($task_id, $key, $value);
                  }
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
              }

              // Repeated tasks are now calculated dynamically in the calendar
              // Notify all assigned users about the new task
              $owners = $this->taskspn_task_owners($task_id);
              if (!empty($owners)) {
                foreach ($owners as $owner_id) {
                  self::taskspn_notify_assignment($task_id, intval($owner_id));
                }
              }
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
              // Capture previous owners before updating
              $previous_owners = $this->taskspn_task_owners($task_id);
              $task = get_post($task_id);
              wp_update_post(['ID' => $task_id, 'post_title' => $taskspn_task_title, 'post_content' => $taskspn_task_description,]);

              if (!empty($key_value)) {
                foreach ($key_value as $key => $value) {
                  // Handle taxonomy separately
                  if ($key === 'taskspn_task_category') {
                    $taxonomy = 'taskspn_task_category';
                    $term_id = !empty($value) ? intval($value) : 0;
                    if ($term_id > 0) {
                      wp_set_post_terms($task_id, [$term_id], $taxonomy, false);
                    }
                  } else {
                    update_post_meta($task_id, $key, $value);
                  }
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
                }
              }

              // After ensuring owners, compute newly added owners and notify only them
              $current_owners = $this->taskspn_task_owners($task_id);
              $previous_owners = is_array($previous_owners) ? $previous_owners : [];
              $current_owners = is_array($current_owners) ? $current_owners : [];
              $new_owners = array_diff($current_owners, $previous_owners);
              if (!empty($new_owners)) {
                foreach ($new_owners as $owner_id) {
                  self::taskspn_notify_assignment($task_id, intval($owner_id));
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
    // Use output buffering to safely capture script output in AJAX context
    ob_start();
    try {
      wp_print_scripts(['taskspn-aux', 'taskspn-forms', 'taskspn-selector']);
    } catch (Exception $e) {
      // Silently fail if scripts can't be printed (e.g., in AJAX context)
      ob_end_clean();
      return;
    } catch (Error $e) {
      // Silently fail if scripts can't be printed (e.g., in AJAX context)
      ob_end_clean();
      return;
    }
    $scripts_output = ob_get_clean();
    if (!empty($scripts_output)) {
      echo $scripts_output;
    }
  }

  public function taskspn_task_list_wrapper() {
    ob_start();
    ?>
      <div class="taskspn-cpt-list taskspn-taskspn_task-list taskspn-mb-100 taskspn-max-width-700 taskspn-margin-auto">
        <?php if (!is_user_logged_in()): ?>
          <?php echo do_shortcode('[taskspn-call-to-action taskspn_call_to_action_icon="error_outline" taskspn_call_to_action_title="' . esc_html__('Access denied', 'taskspn') . '" taskspn_call_to_action_content="' . esc_html__('Log in or create an account to create and manage tasks.', 'taskspn') . '" taskspn_call_to_action_button_text="' . esc_html__('Create account', 'taskspn') . '" taskspn_call_to_action_button_link="' . esc_url(wp_registration_url()) . '"]'); ?>
        <?php else: ?>
          <div class="taskspn-cpt-search-container taskspn-mb-20 taskspn-text-align-right">
            <div class="taskspn-cpt-search-wrapper taskspn-taskspn_task-search-wrapper">
              <input type="text" class="taskspn-cpt-search-input taskspn-taskspn_task-search-input taskspn-input taskspn-display-none" placeholder="<?php esc_attr_e('Filter...', 'taskspn'); ?>" />
              <?php
              // Get calendar page URL
              $calendar_url = self::taskspn_get_calendar_url();
              if ($calendar_url && $calendar_url !== home_url('/')): ?>
                <a href="<?php echo esc_url($calendar_url); ?>" class="taskspn-text-decoration-none taskspn-mr-10">
                  <i class="material-icons-outlined taskspn-cursor-pointer taskspn-font-size-25 taskspn-vertical-align-middle taskspn-tooltip" title="<?php esc_attr_e('View Calendar', 'taskspn'); ?>">calendar_today</i>
                </a>
              <?php endif; ?>
              <i class="material-icons-outlined taskspn-cpt-search-toggle taskspn-taskspn_task-search-toggle taskspn-cursor-pointer taskspn-font-size-30 taskspn-vertical-align-middle taskspn-tooltip" title="<?php esc_attr_e('Search Tasks', 'taskspn'); ?>">search</i>
              
              <a href="#" class="taskspn-popup-open-ajax taskspn-text-decoration-none" data-taskspn-popup-id="taskspn-popup-taskspn_task-add" data-taskspn-ajax-type="taskspn_task_new">
                <i class="material-icons-outlined taskspn-cursor-pointer taskspn-font-size-30 taskspn-vertical-align-middle taskspn-tooltip" title="<?php esc_attr_e('Add new Task', 'taskspn'); ?>">add</i>
              </a>
            </div>
          </div>
  
          <div class="taskspn-cpt-list-wrapper taskspn-taskspn_task-list-wrapper">
            <?php echo wp_kses(self::taskspn_task_list(), TASKSPN_KSES); ?>
          </div>
        <?php endif; ?>

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
                        <a href="#" class="taskspn-popup-open-ajax taskspn-text-decoration-none" data-taskspn-popup-id="taskspn-popup-taskspn_task-check" data-taskspn-ajax-type="taskspn_task_check">
                          <div class="taskspn-display-table taskspn-width-100-percent">
                            <div class="taskspn-display-inline-table taskspn-width-70-percent">
                              <p><?php esc_html_e('Complete Task', 'taskspn'); ?></p>
                            </div>
                            <div class="taskspn-display-inline-table taskspn-width-20-percent taskspn-text-align-right">
                              <i class="material-icons-outlined taskspn-vertical-align-middle taskspn-font-size-30 taskspn-ml-30">check_circle</i>
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
      // Get calendar page URL
      $calendar_url = self::taskspn_get_calendar_url();
      if ($calendar_url && $calendar_url !== home_url('/')): ?>
        <div class="taskspn-task-list-footer taskspn-text-align-center taskspn-mt-30">
          <a href="<?php echo esc_url($calendar_url); ?>" class="taskspn-btn taskspn-btn-primary">
            <i class="material-icons-outlined taskspn-vertical-align-middle taskspn-mr-10">calendar_today</i>
            <?php esc_html_e('View Calendar', 'taskspn'); ?>
          </a>
        </div>
      <?php endif; ?>
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

    // Check if user is owner or administrator
    $current_user_id = get_current_user_id();
    $is_administrator = current_user_can('manage_options') || current_user_can('administrator');
    $is_owner = false;
    
    if ($current_user_id > 0) {
      $task_owners = $this->taskspn_task_owners($task_id);
      $is_owner = in_array($current_user_id, $task_owners);
    }

    // Always show view-only mode for taskspn_task_view
    // The completion form (taskspn_task_check) is accessed separately via the menu
    ob_start();
    try {
      self::taskspn_task_register_scripts();
      self::taskspn_task_print_scripts();
    } catch (Exception $e) {
      // Silently continue if scripts can't be registered/printed
    } catch (Error $e) {
      // Silently continue if scripts can't be registered/printed
    }
    ?>
      <div class="taskspn_task-view taskspn-p-30" data-taskspn_task-id="<?php echo esc_attr($task_id); ?>">
        <a href="#" class="taskspn-popup-close taskspn-text-decoration-none taskspn-close-icon"><i class="material-icons-outlined">close</i></a>
        <h4 class="taskspn-text-align-center"><?php echo esc_html(get_the_title($task_id)); ?></h4>
        
        <div class="taskspn-word-wrap-break-word">
          <?php 
          $taskspn_the_content_hook = 'the_content';
          $task_content = '';
          if ($task && !empty($task->post_content)) {
            $task_content = str_replace(']]>', ']]&gt;', apply_filters($taskspn_the_content_hook, $task->post_content));
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
              
              // Check if field has a value - skip empty fields
              $field_has_value = false;
              
              // Check if field has a parent and if parent is enabled
              if (!empty($taskspn_field['parent']) && !empty($taskspn_field['parent_option'])) {
                $parent_value = get_post_meta($task_id, $taskspn_field['parent'], true);
                // If parent is not enabled, skip this field
                if ($parent_value !== $taskspn_field['parent_option']) {
                  continue;
                }
              }
              
              // Get field value based on input type
              if ($taskspn_field['input'] === 'taxonomy') {
                $taxonomy = !empty($taskspn_field['taxonomy']) ? $taskspn_field['taxonomy'] : 'category';
                $terms = wp_get_post_terms($task_id, $taxonomy, ['fields' => 'ids']);
                $field_has_value = !empty($terms) && !is_wp_error($terms) && is_array($terms) && count($terms) > 0;
              } else {
                $field_value = TASKSPN_Forms::taskspn_get_field_value($taskspn_field['id'], 'post', $task_id, 0, 0, $taskspn_field);
                
                // Check if value is not empty
                if ($taskspn_field['input'] === 'input' && $taskspn_field['type'] === 'checkbox') {
                  // For checkboxes, show if value is 'on'
                  $field_has_value = ($field_value === 'on');
                } elseif ($taskspn_field['input'] === 'input' && $taskspn_field['type'] === 'url') {
                  // For URLs, check if not empty and valid
                  $field_has_value = !empty($field_value) && filter_var($field_value, FILTER_VALIDATE_URL);
                } elseif ($taskspn_field['input'] === 'input' && $taskspn_field['type'] === 'color') {
                  // For colors, check if not empty
                  $field_has_value = !empty($field_value);
                } elseif ($taskspn_field['input'] === 'input' && ($taskspn_field['type'] === 'time' || $taskspn_field['type'] === 'date' || $taskspn_field['type'] === 'datetime-local')) {
                  // For time/date fields, check if not empty
                  $field_has_value = !empty($field_value) && trim($field_value) !== '';
                } elseif ($taskspn_field['input'] === 'input' && ($taskspn_field['type'] === 'number' || $taskspn_field['type'] === 'text')) {
                  // For number/text fields, check if not empty
                  $field_has_value = !empty($field_value) && trim($field_value) !== '';
                } elseif ($taskspn_field['input'] === 'select') {
                  // For selects, check if value is set and not empty
                  $field_has_value = !empty($field_value) && trim($field_value) !== '';
                } elseif ($taskspn_field['input'] === 'image') {
                  // For images, check if there are images
                  $image_value = get_post_meta($task_id, $taskspn_field['id'], true);
                  $field_has_value = !empty($image_value);
                } elseif ($taskspn_field['input'] === 'textarea') {
                  // For textareas, check if not empty
                  $field_has_value = !empty($field_value) && trim($field_value) !== '';
                } else {
                  // For other fields, check if not empty
                  $field_has_value = !empty($field_value) && trim($field_value) !== '';
                }
              }
              
              // Only display field if it has a value
              if ($field_has_value):
          ?>
            <?php echo wp_kses(TASKSPN_Forms::taskspn_input_display_wrapper($taskspn_field, 'post', $task_id), TASKSPN_KSES); ?>
          <?php 
              endif;
            endforeach;
          } catch (Exception $e) {
            echo '<p class="taskspn-text-align-center">' . esc_html__('Error loading task fields', 'taskspn') . '</p>';
          }
          ?>

          <?php if ($is_owner || $is_administrator): ?>
            <div class="taskspn-text-align-right taskspn-task" data-taskspn_task-id="<?php echo esc_attr($task_id); ?>">
              <a href="#" class="taskspn-btn taskspn-btn-mini taskspn-popup-open-ajax" data-taskspn-popup-id="taskspn-popup-taskspn_task-edit" data-taskspn-ajax-type="taskspn_task_edit"><?php esc_html_e('Edit Task', 'taskspn'); ?></a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php
    $taskspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $taskspn_return_string;
  }

  /**
   * Get task check form fields
   * 
   * @since    1.0.0
   * @param    int    $task_id    Task ID
   * @return   array  Array of field configurations
   */
  public static function taskspn_task_get_check_fields($task_id = 0) {
    $is_completed = !empty($task_id) ? (get_post_meta($task_id, 'taskspn_task_completed', true) === 'on') : false;
    
    $check_fields = [];
    
    $check_fields['taskspn_task_completed'] = [
      'id' => 'taskspn_task_completed',
      'class' => 'taskspn-input',
      'input' => 'input',
      'type' => 'checkbox',
      'value' => $is_completed ? 'on' : '',
      'label' => __('Mark as completed', 'taskspn'),
    ];
    
    $check_fields['taskspn_task_comment'] = [
      'id' => 'taskspn_task_comment',
      'class' => 'taskspn-input taskspn-width-100-percent',
      'input' => 'textarea',
      'required' => false,
      'value' => '',
      'label' => __('Comments', 'taskspn'),
      'placeholder' => __('Add a comment about this task execution...', 'taskspn'),
    ];
    
    $check_fields['taskspn_task_form'] = [
      'id' => 'taskspn_task_form',
      'input' => 'input',
      'type' => 'hidden',
    ];
    
    $check_fields['taskspn_ajax_nonce'] = [
      'id' => 'taskspn_ajax_nonce',
      'input' => 'input',
      'type' => 'nonce',
    ];
    
    return apply_filters('taskspn_task_check_fields', $check_fields, $task_id);
  }

  /**
   * Show task completion form (for admin panel)
   * 
   * @since    1.0.0
   * @param    int    $task_id    Task ID
   * @return   string HTML form
   */
  public function taskspn_task_check($task_id) {
    // Validate task ID
    if (empty($task_id) || !is_numeric($task_id)) {
      return '<div class="taskspn_task-check taskspn-p-30"><p class="taskspn-text-align-center">' . esc_html__('Invalid task ID', 'taskspn') . '</p></div>';
    }
    
    // Check if task exists
    $task = get_post($task_id);
    if (!$task || $task->post_type !== 'taskspn_task') {
      return '<div class="taskspn_task-check taskspn-p-30"><p class="taskspn-text-align-center">' . esc_html__('Task not found', 'taskspn') . '</p></div>';
    }

    $task_comments = get_post_meta($task_id, 'taskspn_task_comments', true);
    if (!is_array($task_comments)) {
      $task_comments = [];
    }
    
    ob_start();
    self::taskspn_task_register_scripts();
    self::taskspn_task_print_scripts();
    ?>
      <div class="taskspn_task-check taskspn-p-30" data-taskspn_task-id="<?php echo esc_attr($task_id); ?>">
        <a href="#" class="taskspn-popup-close taskspn-text-decoration-none taskspn-close-icon"><i class="material-icons-outlined">close</i></a>
        
        <h4 class="taskspn-mb-30"><?php echo esc_html(get_the_title($task_id)); ?></h4>

        <form action="" method="post" id="taskspn-task-check-form" class="taskspn-form">
          <?php 
          $check_fields = self::taskspn_task_get_check_fields($task_id);
          foreach ($check_fields as $taskspn_field): 
            if (in_array($taskspn_field['id'], ['taskspn_task_form', 'taskspn_ajax_nonce'])) {
              continue;
            }
            echo wp_kses(TASKSPN_Forms::taskspn_input_wrapper_builder($taskspn_field, 'post', $task_id), TASKSPN_KSES);
          endforeach;
          ?>

          <?php if (!empty($task_comments)): ?>
            <div class="taskspn-mb-20">
              <h5 class="taskspn-mb-20 taskspn-font-size-medium"><?php esc_html_e('Previous comments', 'taskspn'); ?></h5>
              <div class="taskspn-task-comments-list">
                <?php 
                // Sort comments by timestamp descending
                krsort($task_comments);
                foreach ($task_comments as $timestamp => $comment_data): 
                  $comment = is_array($comment_data) && isset($comment_data['comment']) ? $comment_data['comment'] : $comment_data;
                  $user_id = is_array($comment_data) && isset($comment_data['user_id']) ? $comment_data['user_id'] : 0;
                  $user_name = $user_id ? TASKSPN_Functions_User::taskspn_user_get_name($user_id) : __('Unknown', 'taskspn');
                  $date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
                ?>
                  <div class="taskspn-task-comment">
                    <div class="taskspn-display-table taskspn-width-100-percent taskspn-mb-8">
                      <div class="taskspn-display-inline-table taskspn-width-70-percent">
                        <strong class="taskspn-font-size-small taskspn-color-dark"><?php echo esc_html($user_name); ?></strong>
                      </div>
                      <div class="taskspn-display-inline-table taskspn-width-30-percent taskspn-text-align-right">
                        <small class="taskspn-font-size-small taskspn-color-gray"><?php echo esc_html($date); ?></small>
                      </div>
                    </div>
                    <div class="taskspn-word-wrap-break-word taskspn-task-comment-content">
                      <?php echo wp_kses(wpautop($comment), TASKSPN_KSES); ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="taskspn-text-align-right">
            <input class="taskspn-btn" type="submit" data-taskspn-type="post" data-taskspn-subtype="post_check" data-taskspn-post-id="<?php echo esc_attr($task_id); ?>" data-taskspn-post-type="taskspn_task" value="<?php esc_attr_e('Save', 'taskspn'); ?>" />
          </div>
        </form>
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
   * Toggle task completed state and set metadata like completed_at and completed_by
   *
   * @param int $task_id
   * @param int $user_id The user performing the action
   * @return bool New completed state (true if completed)
   */
  public function taskspn_task_toggle_completed($task_id, $user_id = 0) {
    $task_id = intval($task_id);
    if (!$task_id) { return false; }
    $task = get_post($task_id);
    if (!$task || $task->post_type !== 'taskspn_task') { return false; }

    $current = get_post_meta($task_id, 'taskspn_task_completed', true) === 'on';
    $new = !$current;
    update_post_meta($task_id, 'taskspn_task_completed', $new ? 'on' : '');

    if ($new) {
      // Store completion metadata for later reporting
      update_post_meta($task_id, 'taskspn_task_completed_at', current_time('mysql'));
      if (!empty($user_id)) {
        update_post_meta($task_id, 'taskspn_task_completed_by', intval($user_id));
      }
    }
    return $new;
  }

  /**
   * Cron job to reset completed repeated tasks when their next execution date arrives
   * 
   * @since    1.0.0
   * @return   void
   */
  public function taskspn_reset_repeated_tasks() {
    // Get all tasks that are marked as repeat
    $repeated_tasks = get_posts([
      'fields' => 'ids',
      'post_type' => 'taskspn_task',
      'post_status' => 'publish',
      'numberposts' => -1,
      'meta_query' => [
        [
          'key' => 'taskspn_task_repeat',
          'value' => 'on',
          'compare' => '='
        ]
      ]
    ]);

    if (empty($repeated_tasks)) {
      return;
    }

    $current_time = current_time('timestamp');
    
    foreach ($repeated_tasks as $task_id) {
      $task_date = get_post_meta($task_id, 'taskspn_task_date', true);
      $task_time = get_post_meta($task_id, 'taskspn_task_time', true);
      $task_completed = get_post_meta($task_id, 'taskspn_task_completed', true);
      $taskspn_task_periodicity_value = get_post_meta($task_id, 'taskspn_task_periodicity_value', true);
      $taskspn_task_periodicity_type = get_post_meta($task_id, 'taskspn_task_periodicity_type', true);
      $taskspn_task_repeat_until = get_post_meta($task_id, 'taskspn_task_repeat_until', true);
      
      if (empty($task_date) || $task_completed !== 'on') {
        continue;
      }
      
      // Build periodicity string
      $periodicity = '';
      if (!empty($taskspn_task_periodicity_value) && !empty($taskspn_task_periodicity_type)) {
        switch ($taskspn_task_periodicity_type) {
          case 'days':
            $periodicity = $taskspn_task_periodicity_value . ' days';
            break;
          case 'weeks':
            $periodicity = ($taskspn_task_periodicity_value * 7) . ' days';
            break;
          case 'months':
            $periodicity = $taskspn_task_periodicity_value . ' months';
            break;
        }
      }
      
      if (empty($periodicity)) {
        continue;
      }
      
      // Get task execution timestamp
      $task_time_str = !empty($task_time) ? $task_time : '00:00';
      $task_timestamp = strtotime($task_date . ' ' . $task_time_str);
      
      // Calculate next occurrence
      $next_timestamp = strtotime('+' . $periodicity, $task_timestamp);
      
      // Check if repeat until date is set and if we've exceeded it
      if (!empty($taskspn_task_repeat_until)) {
        $repeat_until_timestamp = strtotime($taskspn_task_repeat_until . ' 23:59:59');
        if ($next_timestamp > $repeat_until_timestamp) {
          continue; // Task should no longer repeat
        }
      }
      
      // If current time has passed the next occurrence, reset the task
      if ($current_time >= $next_timestamp) {
        // Mark task as incomplete
        update_post_meta($task_id, 'taskspn_task_completed', '');
        delete_post_meta($task_id, 'taskspn_task_completed_at');
        delete_post_meta($task_id, 'taskspn_task_completed_by');
        
        // Update task date to next occurrence
        $next_date = gmdate('Y-m-d', $next_timestamp);
        update_post_meta($task_id, 'taskspn_task_date', $next_date);
        
        // Optionally clear comments for the new occurrence (uncomment if needed)
        // delete_post_meta($task_id, 'taskspn_task_comments');
      }
    }
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
        $next_date = gmdate('Y-m-d', $next_timestamp);
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

  /**
   * TEMPORARY FUNCTION: Add tasks from captured calendar images
   * This function creates tasks based on the calendar data provided
   * 
   * @since    1.0.0
   * @return   void
   */
  public static function taskspn_add_tasks_from_calendar() {
    // Only run if explicitly called (e.g., via admin action or WP-CLI)
    if (!current_user_can('manage_options')) {
      return;
    }

    // Define category mappings with icons and colors
    $category_config = [
      'ASAMBLEA' => ['icon' => 'groups', 'color' => '#2d5016'], // Dark Green
      'JUNTA GESTORA' => ['icon' => 'groups', 'color' => '#1b5e20'], // Dark Forest Green
      'Fiesta Nacional' => ['icon' => 'flag', 'color' => '#8b4513'], // Reddish-brown/Maroon
      'CAMPAMENTOS' => ['icon' => 'camping', 'color' => '#1565c0'], // Dark Blue
      'No lectivo / Día sin cole' => ['icon' => 'school_off', 'color' => '#c9a961'], // Light Green/Yellowish-brown
      'Bibliobus' => ['icon' => 'local_library', 'color' => '#8b4513'], // Dark Brown
      'EXCURSIÓN PRIMARIA' => ['icon' => 'school', 'color' => '#9c27b0'], // Purple
      'EVENTO FAMILIAS' => ['icon' => 'family_restroom', 'color' => '#2196f3'], // Medium Blue
      'EXCURSIÓN ALGAS' => ['icon' => 'nature', 'color' => '#6a1b9a'], // Dark Plum/Purple
      'Todos los Santos' => ['icon' => 'celebration', 'color' => '#b71c1c'], // Red
      'Navidad' => ['icon' => 'celebration', 'color' => '#b71c1c'], // Red
      'Año Nuevo' => ['icon' => 'celebration', 'color' => '#b71c1c'], // Red
      'Día de Reyes' => ['icon' => 'celebration', 'color' => '#b71c1c'], // Red
      'Concepción' => ['icon' => 'celebration', 'color' => '#b71c1c'], // Red
      'Jueves Santo' => ['icon' => 'celebration', 'color' => '#8b4513'], // Dark Red/Maroon
      'Viernes Santo' => ['icon' => 'celebration', 'color' => '#8b4513'], // Dark Red/Maroon
      'Dom. de Pascua' => ['icon' => 'celebration', 'color' => '#8b4513'], // Dark Red/Maroon
      'L. de Pascua' => ['icon' => 'celebration', 'color' => '#8b4513'], // Dark Red/Maroon
      'Fiesta del Trabajo' => ['icon' => 'work', 'color' => '#8b4513'], // Dark Red/Maroon
      'Día de la Madre' => ['icon' => 'favorite', 'color' => '#8b4513'], // Dark Red/Maroon
      'Pentecostés' => ['icon' => 'celebration', 'color' => '#8b4513'], // Dark Red/Maroon
      'DÍA SIN COLE' => ['icon' => 'school_off', 'color' => '#8bc34a'], // Light Green
      'Bibliobús' => ['icon' => 'local_library', 'color' => '#8b4513'], // Brown/Orange
      'Batucada' => ['icon' => 'music_note', 'color' => '#03a9f4'], // Light Blue
      'Síndrome Down' => ['icon' => 'favorite', 'color' => '#03a9f4'], // Light Blue
      'Día de la Infancia' => ['icon' => 'child_care', 'color' => '#03a9f4'], // Light Blue
      'CAB' => ['icon' => 'event', 'color' => '#03a9f4'], // Light Blue
      'Día de las familias' => ['icon' => 'family_restroom', 'color' => '#03a9f4'], // Light Blue
      'Solsticio' => ['icon' => 'wb_sunny', 'color' => '#03a9f4'], // Light Blue
      'Bicicletada' => ['icon' => 'directions_bike', 'color' => '#03a9f4'], // Light Blue
      'FIN DE CURSO' => ['icon' => 'school', 'color' => '#03a9f4'], // Light Blue
      'Solsticio de invierno' => ['icon' => 'wb_sunny', 'color' => '#1565c0'], // Blue
      'El rock se cuela en la escuela' => ['icon' => 'music_note', 'color' => '#9c27b0'], // Purple
      'La Boheme' => ['icon' => 'theater_comedy', 'color' => '#9c27b0'], // Purple
      'Salakasim + patinaje' => ['icon' => 'sports', 'color' => '#9c27b0'], // Purple
      'Aula Medio Ambiente' => ['icon' => 'nature', 'color' => '#9c27b0'], // Purple
      'Constitución' => ['icon' => 'gavel', 'color' => '#2d5016'], // Green
      'Horario de invierno' => ['icon' => 'schedule', 'color' => '#757575'], // Gray
      'Horario de verano' => ['icon' => 'schedule', 'color' => '#757575'], // Gray
      'Nochebuena' => ['icon' => 'celebration', 'color' => '#757575'], // Gray
      'Nochevieja' => ['icon' => 'celebration', 'color' => '#757575'], // Gray
      '8M' => ['icon' => 'favorite', 'color' => '#757575'], // Gray
      'Marzas' => ['icon' => 'celebration', 'color' => '#757575'], // Gray
      'Equinocio primavera' => ['icon' => 'wb_sunny', 'color' => '#757575'], // Gray
      'Despedida Voluntarios' => ['icon' => 'people', 'color' => '#757575'], // Gray
      'Día de la Tierra' => ['icon' => 'nature', 'color' => '#757575'], // Gray
      'Cultural Cordón' => ['icon' => 'event', 'color' => '#757575'], // Gray
    ];

    // Define tasks from the calendar images
    $tasks_data = [
      // 2025 Tasks
      ['date' => '2025-08-15', 'title' => 'Asunción', 'category' => 'Fiesta Nacional', 'public' => true],
      ['date' => '2025-10-12', 'title' => 'Fiesta Nacional', 'category' => 'Fiesta Nacional', 'public' => true],
      ['date' => '2025-10-26', 'title' => 'Horario de invierno', 'category' => 'Horario de invierno', 'public' => true],
      ['date' => '2025-11-01', 'title' => 'Todos los Santos', 'category' => 'Todos los Santos', 'public' => true],
      ['date' => '2025-11-04', 'title' => 'ASAMBLEA', 'category' => 'ASAMBLEA', 'public' => true],
      ['date' => '2025-11-18', 'title' => 'JUNTA GESTORA', 'category' => 'JUNTA GESTORA', 'public' => true],
      ['date' => '2025-11-20', 'title' => 'El rock se cuela en la escuela', 'category' => 'El rock se cuela en la escuela', 'public' => true],
      ['date' => '2025-11-25', 'title' => 'Bibliobús', 'category' => 'Bibliobus', 'public' => true],
      ['date' => '2025-12-02', 'title' => 'ASAMBLEA', 'category' => 'ASAMBLEA', 'public' => true],
      ['date' => '2025-12-05', 'title' => 'La Boheme', 'category' => 'La Boheme', 'public' => true],
      ['date' => '2025-12-06', 'title' => 'Constitución', 'category' => 'Constitución', 'public' => true],
      ['date' => '2025-12-08', 'title' => 'Concepción', 'category' => 'Concepción', 'public' => true],
      ['date' => '2025-12-15', 'title' => 'Bibliobús', 'category' => 'Bibliobus', 'public' => true],
      ['date' => '2025-12-16', 'title' => 'JUNTA GESTORA', 'category' => 'JUNTA GESTORA', 'public' => true],
      ['date' => '2025-12-17', 'title' => 'Salakasim + patinaje', 'category' => 'Salakasim + patinaje', 'public' => true],
      ['date' => '2025-12-19', 'title' => 'Solsticio de invierno', 'category' => 'Solsticio de invierno', 'public' => true],
      ['date' => '2025-12-22', 'title' => 'CAMPAMENTOS', 'category' => 'CAMPAMENTOS', 'public' => true],
      ['date' => '2025-12-23', 'title' => 'CAMPAMENTOS', 'category' => 'CAMPAMENTOS', 'public' => true],
      ['date' => '2025-12-24', 'title' => 'Nochebuena', 'category' => 'Nochebuena', 'public' => true],
      ['date' => '2025-12-25', 'title' => 'Navidad', 'category' => 'Navidad', 'public' => true],
      ['date' => '2025-12-26', 'title' => 'CAMPAMENTOS', 'category' => 'CAMPAMENTOS', 'public' => true],
      ['date' => '2025-12-29', 'title' => 'CAMPAMENTOS', 'category' => 'CAMPAMENTOS', 'public' => true],
      ['date' => '2025-12-30', 'title' => 'CAMPAMENTOS', 'category' => 'CAMPAMENTOS', 'public' => true],
      ['date' => '2025-12-31', 'title' => 'Nochevieja', 'category' => 'Nochevieja', 'public' => true],
      
      // 2026 Tasks
      ['date' => '2026-01-01', 'title' => 'Año Nuevo', 'category' => 'Año Nuevo', 'public' => true],
      ['date' => '2026-01-02', 'title' => 'CAMPAMENTOS', 'category' => 'CAMPAMENTOS', 'public' => true],
      ['date' => '2026-01-05', 'title' => 'CAMPAMENTOS', 'category' => 'CAMPAMENTOS', 'public' => true],
      ['date' => '2026-01-06', 'title' => 'Día de Reyes', 'category' => 'Día de Reyes', 'public' => true],
      ['date' => '2026-01-07', 'title' => 'CAMPAMENTOS', 'category' => 'CAMPAMENTOS', 'public' => true],
      ['date' => '2026-01-13', 'title' => 'ASAMBLEA', 'category' => 'ASAMBLEA', 'public' => true],
      ['date' => '2026-01-20', 'title' => 'JUNTA GESTORA', 'category' => 'JUNTA GESTORA', 'public' => true],
      ['date' => '2026-01-27', 'title' => 'Bibliobús', 'category' => 'Bibliobus', 'public' => true],
      ['date' => '2026-01-30', 'title' => 'Aula Medio Ambiente', 'category' => 'Aula Medio Ambiente', 'public' => true],
      
      // 2026 February - July
      ['date' => '2026-02-03', 'title' => 'ASAMBLEA', 'category' => 'ASAMBLEA', 'public' => true],
      ['date' => '2026-02-13', 'title' => 'Batucada', 'category' => 'Batucada', 'public' => true],
      ['date' => '2026-02-16', 'title' => 'DÍA SIN COLE', 'category' => 'DÍA SIN COLE', 'public' => true],
      ['date' => '2026-02-17', 'title' => 'DÍA SIN COLE', 'category' => 'DÍA SIN COLE', 'public' => true],
      ['date' => '2026-02-24', 'title' => 'JUNTA GESTORA', 'category' => 'JUNTA GESTORA', 'public' => true],
      
      ['date' => '2026-03-03', 'title' => 'ASAMBLEA', 'category' => 'ASAMBLEA', 'public' => true],
      ['date' => '2026-03-06', 'title' => '8M', 'category' => '8M', 'public' => true],
      ['date' => '2026-03-10', 'title' => 'Bibliobús', 'category' => 'Bibliobus', 'public' => true],
      ['date' => '2026-03-17', 'title' => 'JUNTA GESTORA', 'category' => 'JUNTA GESTORA', 'public' => true],
      ['date' => '2026-03-20', 'title' => 'Síndrome Down', 'category' => 'Síndrome Down', 'public' => true],
      ['date' => '2026-03-26', 'title' => 'Marzas', 'category' => 'Marzas', 'public' => true],
      ['date' => '2026-03-26', 'title' => 'Equinocio primavera', 'category' => 'Equinocio primavera', 'public' => true],
      ['date' => '2026-03-27', 'title' => 'CAMPAMENTOS', 'category' => 'CAMPAMENTOS', 'public' => true],
      ['date' => '2026-03-29', 'title' => 'Horario de verano', 'category' => 'Horario de verano', 'public' => true],
      ['date' => '2026-03-30', 'title' => 'CAMPAMENTOS', 'category' => 'CAMPAMENTOS', 'public' => true],
      ['date' => '2026-03-31', 'title' => 'CAMPAMENTOS', 'category' => 'CAMPAMENTOS', 'public' => true],
      
      ['date' => '2026-04-01', 'title' => 'CAMPAMENTOS', 'category' => 'CAMPAMENTOS', 'public' => true],
      ['date' => '2026-04-02', 'title' => 'Jueves Santo', 'category' => 'Jueves Santo', 'public' => true],
      ['date' => '2026-04-03', 'title' => 'Viernes Santo', 'category' => 'Viernes Santo', 'public' => true],
      ['date' => '2026-04-05', 'title' => 'Dom. de Pascua', 'category' => 'Dom. de Pascua', 'public' => true],
      ['date' => '2026-04-06', 'title' => 'L. de Pascua', 'category' => 'L. de Pascua', 'public' => true],
      ['date' => '2026-04-07', 'title' => 'ASAMBLEA', 'category' => 'ASAMBLEA', 'public' => true],
      ['date' => '2026-04-10', 'title' => 'Despedida Voluntarios', 'category' => 'Despedida Voluntarios', 'public' => true],
      ['date' => '2026-04-15', 'title' => 'Día de la Infancia', 'category' => 'Día de la Infancia', 'public' => true],
      ['date' => '2026-04-17', 'title' => 'CAB', 'category' => 'CAB', 'public' => true],
      ['date' => '2026-04-21', 'title' => 'Bibliobús', 'category' => 'Bibliobus', 'public' => true],
      ['date' => '2026-04-21', 'title' => 'Día de la Tierra', 'category' => 'Día de la Tierra', 'public' => true],
      ['date' => '2026-04-21', 'title' => 'JUNTA GESTORA', 'category' => 'JUNTA GESTORA', 'public' => true],
      ['date' => '2026-04-23', 'title' => 'DÍA SIN COLE', 'category' => 'DÍA SIN COLE', 'public' => true],
      ['date' => '2026-04-24', 'title' => 'DÍA SIN COLE', 'category' => 'DÍA SIN COLE', 'public' => true],
      
      ['date' => '2026-05-01', 'title' => 'Fiesta del Trabajo', 'category' => 'Fiesta del Trabajo', 'public' => true],
      ['date' => '2026-05-03', 'title' => 'Día de la Madre', 'category' => 'Día de la Madre', 'public' => true],
      ['date' => '2026-05-05', 'title' => 'ASAMBLEA', 'category' => 'ASAMBLEA', 'public' => true],
      ['date' => '2026-05-06', 'title' => 'Cultural Cordón', 'category' => 'Cultural Cordón', 'public' => true],
      ['date' => '2026-05-12', 'title' => 'Bibliobús', 'category' => 'Bibliobus', 'public' => true],
      ['date' => '2026-05-15', 'title' => 'Día de las familias', 'category' => 'Día de las familias', 'public' => true],
      ['date' => '2026-05-19', 'title' => 'JUNTA GESTORA', 'category' => 'JUNTA GESTORA', 'public' => true],
      ['date' => '2026-05-24', 'title' => 'Pentecostés', 'category' => 'Pentecostés', 'public' => true],
      
      ['date' => '2026-06-01', 'title' => 'Bibliobús', 'category' => 'Bibliobus', 'public' => true],
      ['date' => '2026-06-02', 'title' => 'ASAMBLEA', 'category' => 'ASAMBLEA', 'public' => true],
      ['date' => '2026-06-16', 'title' => 'JUNTA GESTORA', 'category' => 'JUNTA GESTORA', 'public' => true],
      ['date' => '2026-06-22', 'title' => 'Solsticio', 'category' => 'Solsticio', 'public' => true],
      ['date' => '2026-06-23', 'title' => 'Bicicletada', 'category' => 'Bicicletada', 'public' => true],
      ['date' => '2026-06-23', 'title' => 'Bibliobús', 'category' => 'Bibliobus', 'public' => true],
      ['date' => '2026-06-24', 'title' => 'FIN DE CURSO', 'category' => 'FIN DE CURSO', 'public' => true],
    ];

    $current_user_id = get_current_user_id();
    if (empty($current_user_id)) {
      $current_user_id = 1; // Fallback to admin user
    }

    $created_count = 0;
    $taxonomy = 'taskspn_task_category';

    foreach ($tasks_data as $task_data) {
      // Check if task already exists
      $existing = get_posts([
        'post_type' => 'taskspn_task',
        'post_status' => 'any',
        'title' => $task_data['title'],
        'meta_query' => [
          [
            'key' => 'taskspn_task_date',
            'value' => $task_data['date'],
            'compare' => '='
          ]
        ],
        'fields' => 'ids',
        'numberposts' => 1
      ]);

      if (!empty($existing)) {
        continue; // Skip if already exists
      }

      // Get or create category term
      $category_name = $task_data['category'];
      $term = get_term_by('name', $category_name, $taxonomy);
      
      if (!$term) {
        // Create term
        $term_result = wp_insert_term($category_name, $taxonomy);
        if (!is_wp_error($term_result)) {
          $term_id = $term_result['term_id'];
          
          // Set category icon and color as term meta
          if (isset($category_config[$category_name])) {
            update_term_meta($term_id, 'taskspn_category_icon', $category_config[$category_name]['icon']);
            update_term_meta($term_id, 'taskspn_category_color', $category_config[$category_name]['color']);
          }
        } else {
          $term_id = 0;
        }
      } else {
        $term_id = $term->term_id;
        
        // Update category icon and color if not set
        if (isset($category_config[$category_name])) {
          $existing_icon = get_term_meta($term_id, 'taskspn_category_icon', true);
          $existing_color = get_term_meta($term_id, 'taskspn_category_color', true);
          
          if (empty($existing_icon)) {
            update_term_meta($term_id, 'taskspn_category_icon', $category_config[$category_name]['icon']);
          }
          if (empty($existing_color)) {
            update_term_meta($term_id, 'taskspn_category_color', $category_config[$category_name]['color']);
          }
        }
      }

      // Create task
      $post_functions = new TASKSPN_Functions_Post();
      $task_id = $post_functions->taskspn_insert_post(
        $task_data['title'],
        '',
        '',
        sanitize_title($task_data['title'] . '-' . $task_data['date']),
        'taskspn_task',
        'publish',
        $current_user_id
      );

      if ($task_id) {
        // Set task meta
        update_post_meta($task_id, 'taskspn_task_date', $task_data['date']);
        update_post_meta($task_id, 'taskspn_task_public', $task_data['public'] ? 'on' : '');
        
        // Set category icon and color from term meta
        if ($term_id > 0) {
          wp_set_post_terms($task_id, [$term_id], $taxonomy, false);
          
          $category_icon = get_term_meta($term_id, 'taskspn_category_icon', true);
          $category_color = get_term_meta($term_id, 'taskspn_category_color', true);
          
          if (!empty($category_icon)) {
            update_post_meta($task_id, 'taskspn_task_icon', $category_icon);
          }
          if (!empty($category_color)) {
            update_post_meta($task_id, 'taskspn_task_color', $category_color);
          }
        }
        
        $created_count++;
      }
    }

    return $created_count;
  }

  /**
   * Get category icon and color for a task
   * 
   * @param int $task_id Task ID
   * @return array Array with 'icon' and 'color' keys
   */
  public static function taskspn_get_task_category_style($task_id) {
    $taxonomy = 'taskspn_task_category';
    $terms = wp_get_post_terms($task_id, $taxonomy, ['fields' => 'ids']);
    
    $icon = 'event'; // Default icon
    $color = get_option('taskspn_color_main') ?: '#d45500'; // Default color
    
    if (!empty($terms) && !is_wp_error($terms)) {
      $term_id = $terms[0]; // Get first term
      
      $category_icon = get_term_meta($term_id, 'taskspn_category_icon', true);
      $category_color = get_term_meta($term_id, 'taskspn_category_color', true);
      
      if (!empty($category_icon)) {
        $icon = $category_icon;
      }
      if (!empty($category_color)) {
        $color = $category_color;
      }
    }
    
    return ['icon' => $icon, 'color' => $color];
  }
}