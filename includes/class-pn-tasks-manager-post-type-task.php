<?php
/**
 * Task creator.
 *
 * This class defines Task options, menus and templates.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/includes
 * @author     Padres en la Nube
 */
class PN_TASKS_MANAGER_Post_Type_Task {
  /**
   * Try to resolve the public calendar page URL (page using [pn-tasks-manager-calendar])
   */
  private static function pn_tasks_manager_get_calendar_url() {
    // Find first published page containing our calendar shortcode
    $pages = get_posts([
      'post_type' => 'page',
      'post_status' => 'publish',
      'numberposts' => -1,
      's' => '[pn-tasks-manager-calendar',
      'fields' => 'ids',
    ]);
    if (!empty($pages)) {
      foreach ($pages as $page_id) {
        $content = get_post_field('post_content', $page_id);
        if ($content && has_shortcode($content, 'pn-tasks-manager-calendar')) {
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
  private static function pn_tasks_manager_mailpn_send($to, $subject, $html_content, $mailpn_type = 'email_coded') {
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
   * Normalize task content through WordPress formatting filters.
   *
   * @param string $content Task content to filter.
   * @return string
   */
  private static function pn_tasks_manager_filter_task_content($content) {
    if (empty($content)) {
      return '';
    }

    $content = apply_filters('pn_tasks_manager_task_content_pre', $content);

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- need core formatting and shortcode parsing.
    $content = apply_filters('the_content', $content);

    return apply_filters('pn_tasks_manager_task_content', $content);
  }

  /**
   * Compose and send task assignment email to a single user (ID or email)
   */
  private static function pn_tasks_manager_notify_assignment($task_id, $user_to) {
    $task = get_post($task_id);
    if (!$task) { return; }

    $title = get_the_title($task_id);
    $desc = $task->post_content;
    $date = get_post_meta($task_id, 'pn_tasks_manager_task_date', true);
    $time = get_post_meta($task_id, 'pn_tasks_manager_task_time', true);
    $calendar_url = self::pn_tasks_manager_get_calendar_url();

    /* translators: %s: Task title */
    $subject = sprintf(__('New task assigned: %s', 'pn-tasks-manager'), $title);
    $date_line = '';
    if (!empty($date)) {
      $formatted = date_i18n(get_option('date_format'), strtotime($date));
      if (!empty($time)) {
        $formatted .= ' ' . $time;
      }
      $date_line = '<p><strong>' . esc_html__('Scheduled for', 'pn-tasks-manager') . ':</strong> ' . esc_html($formatted) . '</p>';
    }

    $content = ''
      . '<h2>' . esc_html($title) . '</h2>'
      . $date_line
      . (!empty($desc) ? wpautop(wp_kses_post(self::pn_tasks_manager_filter_task_content($desc))) : '')
      . '<p><a href="' . esc_url($calendar_url) . '">' . esc_html__('Open calendar', 'pn-tasks-manager') . '</a></p>';

    self::pn_tasks_manager_mailpn_send($user_to, $subject, $content, 'email_coded');
  }
  public static function pn_tasks_manager_task_get_fields($task_id = 0) {
    $pn_tasks_manager_fields = [];
      $pn_tasks_manager_fields['pn_tasks_manager_task_title'] = [
        'id' => 'pn_tasks_manager_task_title',
        'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
        'input' => 'input',
        'type' => 'text',
        'required' => true,
        'value' => !empty($task_id) ? esc_html(get_the_title($task_id)) : '',
        'label' => __('Task title', 'pn-tasks-manager'),
        'placeholder' => __('Task title', 'pn-tasks-manager'),
      ];
      $pn_tasks_manager_fields['pn_tasks_manager_task_description'] = [
        'id' => 'pn_tasks_manager_task_description',
        'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
        'input' => 'textarea',
        'required' => true,
        'value' => !empty($task_id) ? (str_replace(']]>', ']]&gt;', self::pn_tasks_manager_filter_task_content(get_post($task_id)->post_content))) : '',
        'label' => __('Task description', 'pn-tasks-manager'),
        'placeholder' => __('Task description', 'pn-tasks-manager'),
      ];
    
    // Allow other plugins to extend task fields
    $pn_tasks_manager_fields = apply_filters('pn_tasks_manager_task_fields', $pn_tasks_manager_fields, $task_id);
    
    return $pn_tasks_manager_fields;
  }

  public static function pn_tasks_manager_task_get_fields_meta($task_id = 0) {
    $pn_tasks_manager_fields_meta = [];
      $pn_tasks_manager_fields_meta['pn_tasks_manager_task_date'] = [
        'id' => 'pn_tasks_manager_task_date',
        'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
        'input' => 'input',
        'type' => 'date',
        'label' => __('Task date', 'pn-tasks-manager'),
        'placeholder' => __('Task date', 'pn-tasks-manager'),
      ];
      $pn_tasks_manager_fields_meta['pn_tasks_manager_task_time'] = [
        'id' => 'pn_tasks_manager_task_time',
        'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
        'input' => 'input',
        'type' => 'time',
        'label' => __('Task time', 'pn-tasks-manager'),
        'placeholder' => __('Task time', 'pn-tasks-manager'),
      ];
      $pn_tasks_manager_fields_meta['pn_tasks_manager_task_estimated_hours'] = [
        'id' => 'pn_tasks_manager_task_estimated_hours',
        'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
        'input' => 'input',
        'type' => 'number',
        'step' => '0.25',
        'min' => '0',
        'label' => __('Estimated hours', 'pn-tasks-manager'),
        'placeholder' => __('Estimated hours (e.g., 1.5)', 'pn-tasks-manager'),
      ];
      $pn_tasks_manager_fields_meta['pn_tasks_manager_task_repeat'] = [
        'id' => 'pn_tasks_manager_task_repeat',
        'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Repeat task', 'pn-tasks-manager'),
        'placeholder' => __('Repeat task', 'pn-tasks-manager'),
      ]; 
        $pn_tasks_manager_fields_meta['pn_tasks_manager_task_periodicity_value'] = [
          'id' => 'pn_tasks_manager_task_periodicity_value',
          'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
          'input' => 'input',
          'type' => 'number',
          'min' => 1,
          'max' => 365,
          'parent' => 'pn_tasks_manager_task_repeat',
          'parent_option' => 'on',
          'label' => __('Repeat every', 'pn-tasks-manager'),
          'placeholder' => __('Number', 'pn-tasks-manager'),
        ];
        $pn_tasks_manager_fields_meta['pn_tasks_manager_task_periodicity_type'] = [
          'id' => 'pn_tasks_manager_task_periodicity_type',
          'class' => 'pn-tasks-manager-select pn-tasks-manager-width-100-percent',
          'input' => 'select',
          'parent' => 'pn_tasks_manager_task_repeat',
          'parent_option' => 'on',
          'label' => __('Period', 'pn-tasks-manager'),
          'placeholder' => __('Select period', 'pn-tasks-manager'),
          'options' => [
            'days' => __('Days', 'pn-tasks-manager'),
            'weeks' => __('Weeks', 'pn-tasks-manager'),
            'months' => __('Months', 'pn-tasks-manager'),
          ],
        ];
        $pn_tasks_manager_fields_meta['pn_tasks_manager_task_repeat_until'] = [
          'id' => 'pn_tasks_manager_task_repeat_until',
          'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
          'input' => 'input',
          'type' => 'date',
          'parent' => 'pn_tasks_manager_task_repeat',
          'parent_option' => 'on',
          'label' => __('Repeat until', 'pn-tasks-manager'),
          'placeholder' => __('Repeat until', 'pn-tasks-manager'),
        ];
      $pn_tasks_manager_fields_meta['pn_tasks_manager_task_owners'] = [
        'id' => 'pn_tasks_manager_task_owners',
        'class' => 'pn-tasks-manager-select pn-tasks-manager-width-100-percent',
        'input' => 'select',
        'type' => 'text',
        'multiple' => true,
        'label' => __('Assigned to', 'pn-tasks-manager'),
        'placeholder' => __('Select users', 'pn-tasks-manager'),
        'options' => self::pn_tasks_manager_get_users_for_select(),
      ];
      $pn_tasks_manager_fields_meta['pn_tasks_manager_task_public'] = [
        'id' => 'pn_tasks_manager_task_public',
        'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Show task in public calendar', 'pn-tasks-manager'),
        'placeholder' => __('Show task in public calendar', 'pn-tasks-manager'),
      ];
      $pn_tasks_manager_fields_meta['pn_tasks_manager_task_icon'] = [
        'id' => 'pn_tasks_manager_task_icon',
        'class' => 'pn-tasks-manager-select pn-tasks-manager-width-100-percent',
        'input' => 'select',
        'label' => __('Task icon', 'pn-tasks-manager'),
        'placeholder' => __('Select an icon', 'pn-tasks-manager'),
        'value' => !empty($task_id) ? esc_attr(get_post_meta($task_id, 'pn_tasks_manager_task_icon', true)) : '',
        'description' => __('Select a Material Icons icon to display in the calendar view. Leave empty to use default.', 'pn-tasks-manager'),
        'options' => [
          '' => __('Default (event)', 'pn-tasks-manager'),
          'event' => __('Event', 'pn-tasks-manager'),
          'task' => __('Task', 'pn-tasks-manager'),
          'task_alt' => __('Task Alt', 'pn-tasks-manager'),
          'alarm' => __('Alarm', 'pn-tasks-manager'),
          'schedule' => __('Schedule', 'pn-tasks-manager'),
          'calendar_today' => __('Calendar Today', 'pn-tasks-manager'),
          'school' => __('School', 'pn-tasks-manager'),
          'work' => __('Work', 'pn-tasks-manager'),
          'home' => __('Home', 'pn-tasks-manager'),
          'shopping_cart' => __('Shopping', 'pn-tasks-manager'),
          'restaurant' => __('Restaurant', 'pn-tasks-manager'),
          'local_hospital' => __('Hospital', 'pn-tasks-manager'),
          'flight' => __('Flight', 'pn-tasks-manager'),
          'directions_car' => __('Car', 'pn-tasks-manager'),
          'fitness_center' => __('Fitness', 'pn-tasks-manager'),
          'movie' => __('Movie', 'pn-tasks-manager'),
          'music_note' => __('Music', 'pn-tasks-manager'),
          'book' => __('Book', 'pn-tasks-manager'),
          'sports_soccer' => __('Sports', 'pn-tasks-manager'),
          'beach_access' => __('Beach', 'pn-tasks-manager'),
          'cake' => __('Cake', 'pn-tasks-manager'),
          'child_care' => __('Child Care', 'pn-tasks-manager'),
          'pets' => __('Pets', 'pn-tasks-manager'),
          'favorite' => __('Favorite', 'pn-tasks-manager'),
          'star' => __('Star', 'pn-tasks-manager'),
          'check_circle' => __('Check Circle', 'pn-tasks-manager'),
          'info' => __('Info', 'pn-tasks-manager'),
          'warning' => __('Warning', 'pn-tasks-manager'),
          'error' => __('Error', 'pn-tasks-manager'),
          'notifications' => __('Notifications', 'pn-tasks-manager'),
          'email' => __('Email', 'pn-tasks-manager'),
          'phone' => __('Phone', 'pn-tasks-manager'),
          'videocam' => __('Video', 'pn-tasks-manager'),
          'photo_camera' => __('Photo', 'pn-tasks-manager'),
          'attach_money' => __('Money', 'pn-tasks-manager'),
          'shopping_bag' => __('Shopping Bag', 'pn-tasks-manager'),
          'local_grocery_store' => __('Grocery Store', 'pn-tasks-manager'),
          'restaurant_menu' => __('Restaurant Menu', 'pn-tasks-manager'),
          'hotel' => __('Hotel', 'pn-tasks-manager'),
          'directions_bus' => __('Bus', 'pn-tasks-manager'),
          'train' => __('Train', 'pn-tasks-manager'),
          'bike_scooter' => __('Bike', 'pn-tasks-manager'),
          'pool' => __('Pool', 'pn-tasks-manager'),
          'spa' => __('Spa', 'pn-tasks-manager'),
          'local_library' => __('Library', 'pn-tasks-manager'),
          'museum' => __('Museum', 'pn-tasks-manager'),
          'theater_comedy' => __('Theater', 'pn-tasks-manager'),
          'celebration' => __('Celebration', 'pn-tasks-manager'),
        ],
      ];
      $pn_tasks_manager_fields_meta['pn_tasks_manager_task_color'] = [
        'id' => 'pn_tasks_manager_task_color',
        'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
        'input' => 'input',
        'type' => 'color',
        'label' => __('Task color', 'pn-tasks-manager'),
        'placeholder' => __('Task color', 'pn-tasks-manager'),
        'value' => !empty($task_id) ? (get_post_meta($task_id, 'pn_tasks_manager_task_color', true) ?: (get_option('pn_tasks_manager_color_main') ?: '#b84a00')) : (get_option('pn_tasks_manager_color_main') ?: '#b84a00'),
      ];
      $pn_tasks_manager_fields_meta['pn_tasks_manager_task_multimedia'] = [
        'id' => 'pn_tasks_manager_task_multimedia',
        'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Task multimedia content', 'pn-tasks-manager'),
        'placeholder' => __('Task multimedia content', 'pn-tasks-manager'),
      ]; 
        $pn_tasks_manager_fields_meta['pn_tasks_manager_task_url'] = [
          'id' => 'pn_tasks_manager_task_url',
          'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
          'input' => 'input',
          'type' => 'url',
          'parent' => 'pn_tasks_manager_task_multimedia',
          'parent_option' => 'on',
          'label' => __('Task url', 'pn-tasks-manager'),
          'placeholder' => __('Task url', 'pn-tasks-manager'),
        ];
        $pn_tasks_manager_fields_meta['pn_tasks_manager_task_url_audio'] = [
          'id' => 'pn_tasks_manager_task_url_audio',
          'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
          'input' => 'input',
          'type' => 'url',
          'parent' => 'pn_tasks_manager_task_multimedia',
          'parent_option' => 'on',
          'label' => __('Task audio url', 'pn-tasks-manager'),
          'placeholder' => __('Task audio url', 'pn-tasks-manager'),
        ];
        $pn_tasks_manager_fields_meta['pn_tasks_manager_task_url_video'] = [
          'id' => 'pn_tasks_manager_task_url_video',
          'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
          'input' => 'input',
          'type' => 'url',
          'parent' => 'pn_tasks_manager_task_multimedia',
          'parent_option' => 'on',
          'label' => __('Task video url', 'pn-tasks-manager'),
          'placeholder' => __('Task video url', 'pn-tasks-manager'),
        ];
      $pn_tasks_manager_fields_meta['pn_tasks_manager_task_category'] = [
        'id' => 'pn_tasks_manager_task_category',
        'class' => 'pn-tasks-manager-select pn-tasks-manager-width-100-percent',
        'input' => 'taxonomy',
        'taxonomy' => 'pn_tasks_manager_task_category',
        'multiple' => false,
        'allow_new' => true,
        'label' => __('Task category', 'pn-tasks-manager'),
        'placeholder' => __('Select a category', 'pn-tasks-manager'),
        'description' => __('Select a category for this task. Categories help organize and color-code tasks in the calendar.', 'pn-tasks-manager'),
      ];
      $pn_tasks_manager_fields_meta['pn_tasks_manager_task_form'] = [
        'id' => 'pn_tasks_manager_task_form',
        'input' => 'input',
        'type' => 'hidden',
      ];
      $pn_tasks_manager_fields_meta['pn_tasks_manager_ajax_nonce'] = [
        'id' => 'pn_tasks_manager_ajax_nonce',
        'input' => 'input',
        'type' => 'nonce',
      ];
    
    // Allow other plugins to extend task meta fields
    $pn_tasks_manager_fields_meta = apply_filters('pn_tasks_manager_task_fields_meta', $pn_tasks_manager_fields_meta, $task_id);
    
    return $pn_tasks_manager_fields_meta;
  }

  /**
   * Get all task fields (basic + meta) merged and extended by filters
   * 
   * @since    1.0.0
   * @param    int    $task_id    Optional task ID for getting field values
   * @return   array  Combined array of all task fields
   */
  public static function pn_tasks_manager_task_get_all_fields($task_id = 0) {
    $basic_fields = self::pn_tasks_manager_task_get_fields($task_id);
    $meta_fields = self::pn_tasks_manager_task_get_fields_meta($task_id);
    
    // Merge and allow final extension by other plugins
    $all_fields = array_merge($basic_fields, $meta_fields);
    $all_fields = apply_filters('pn_tasks_manager_task_all_fields', $all_fields, $task_id);
    
    return $all_fields;
  }

  /**
   * Register Task.
   *
   * @since    1.0.0
   */
  public function pn_tasks_manager_task_register_post_type() {
    $labels = [
      'name'                => _x('Task', 'Post Type general name', 'pn-tasks-manager'),
      'singular_name'       => _x('Task', 'Post Type singular name', 'pn-tasks-manager'),
      'menu_name'           => esc_html(__('Tasks', 'pn-tasks-manager')),
      'parent_item_colon'   => esc_html(__('Parent Task', 'pn-tasks-manager')),
      'all_items'           => esc_html(__('All Tasks', 'pn-tasks-manager')),
      'view_item'           => esc_html(__('View Task', 'pn-tasks-manager')),
      'add_new_item'        => esc_html(__('Add new Task', 'pn-tasks-manager')),
      'add_new'             => esc_html(__('Add new Task', 'pn-tasks-manager')),
      'edit_item'           => esc_html(__('Edit Task', 'pn-tasks-manager')),
      'update_item'         => esc_html(__('Update Task', 'pn-tasks-manager')),
      'search_items'        => esc_html(__('Search Tasks', 'pn-tasks-manager')),
      'not_found'           => esc_html(__('Not Task found', 'pn-tasks-manager')),
      'not_found_in_trash'  => esc_html(__('Not Task found in Trash', 'pn-tasks-manager')),
    ];

    $args = [
      'labels'              => $labels,
      'rewrite'             => false,
      'label'               => esc_html(__('Tasks', 'pn-tasks-manager')),
      'description'         => esc_html(__('Task description', 'pn-tasks-manager')),
      'supports'            => ['title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'page-attributes', ],
      'hierarchical'        => true,
      'public'              => false,
      'show_ui'             => true,
      'show_in_menu'        => 'pn_tasks_manager_options',
      'show_in_nav_menus'   => false,
      'show_in_admin_bar'   => true,
      'menu_position'       => 5,
      'menu_icon'           => esc_url(PN_TASKS_MANAGER_URL . 'assets/media/pn-tasks-manager-task-menu-icon.svg'),
      'can_export'          => true,
      'has_archive'         => false,
      'exclude_from_search' => true,
      'publicly_queryable'  => false,
      'capability_type'     => 'page',
      // Use dynamically defined capabilities constant for pn_tasks_task CPT
      'capabilities'        => defined('PN_TASKS_MANAGER_ROLE_PN_TASKS_TASK_CAPABILITIES') ? constant('PN_TASKS_MANAGER_ROLE_PN_TASKS_TASK_CAPABILITIES') : [],
      'taxonomies'          => ['pn_tasks_manager_task_category'],
      'show_in_rest'        => true, /* Keep Gutenberg editor working */
    ];

    register_post_type('pn_tasks_task', $args);
    add_theme_support('post-thumbnails', ['page', 'pn_tasks_task']);
  }

  /**
   * Add Task dashboard metabox.
   *
   * @since    1.0.0
   */
  public function pn_tasks_manager_task_add_meta_box() {
    add_meta_box('pn_tasks_manager_meta_box', esc_html(__('Task details', 'pn-tasks-manager')), [$this, 'pn_tasks_manager_task_meta_box_function'], 'pn_tasks_task', 'normal', 'high', ['__block_editor_compatible_meta_box' => true,]);
  }

  /**
   * Hide task REST endpoints for unauthenticated requests to keep it out of public API
   * but allow Gutenberg/editor to function for logged-in users.
   */
  public function pn_tasks_manager_hide_task_rest_endpoints($endpoints) {
    if (is_user_logged_in()) {
      return $endpoints;
    }

    if (isset($endpoints['/wp/v2/pn_tasks_manager_task'])) {
      unset($endpoints['/wp/v2/pn_tasks_manager_task']);
    }
    // Single item route pattern
    foreach (array_keys($endpoints) as $route) {
      if (preg_match('#^/wp/v2/pn_tasks_manager_task/\(\?P<id>\[\\d\]\+\)$#', $route)) {
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
  public function pn_tasks_manager_task_meta_box_function($post) {
    $all_fields = self::pn_tasks_manager_task_get_all_fields($post->ID);
    foreach ($all_fields as $pn_tasks_manager_field) {
      // Skip basic fields (title, description) in meta box as they are handled by WordPress
      if (in_array($pn_tasks_manager_field['id'], ['pn_tasks_manager_task_title', 'pn_tasks_manager_task_description'])) {
        continue;
      }
      if (!is_null(PN_TASKS_MANAGER_Forms::pn_tasks_manager_input_wrapper_builder($pn_tasks_manager_field, 'post', $post->ID))) {
        echo wp_kses(PN_TASKS_MANAGER_Forms::pn_tasks_manager_input_wrapper_builder($pn_tasks_manager_field, 'post', $post->ID), pn_tasks_manager_KSES);
      }
    }
  }

  /**
   * Defines single template for Task.
   *
   * @since    1.0.0
   */
  public function pn_tasks_manager_task_single_template($single) {
    if (get_post_type() == 'pn_tasks_task') {
      if (file_exists(PN_TASKS_MANAGER_DIR . 'templates/public/single-pn_tasks_manager_task.php')) {
        return PN_TASKS_MANAGER_DIR . 'templates/public/single-pn_tasks_manager_task.php';
      }
    }

    return $single;
  }

  /**
   * Defines archive template for Task.
   *
   * @since    1.0.0
   */
  public function pn_tasks_manager_task_archive_template($archive) {
    if (get_post_type() == 'pn_tasks_task') {
      if (file_exists(PN_TASKS_MANAGER_DIR . 'templates/public/archive-pn_tasks_manager_task.php')) {
        return PN_TASKS_MANAGER_DIR . 'templates/public/archive-pn_tasks_manager_task.php';
      }
    }

    return $archive;
  }

  public function pn_tasks_manager_task_save_post($post_id, $cpt, $update) {
    if($cpt->post_type == 'pn_tasks_task' && array_key_exists('pn_tasks_manager_task_form', $_POST)){
      // Always require nonce verification
      if (!array_key_exists('pn_tasks_manager_ajax_nonce', $_POST)) {
        echo wp_json_encode([
          'error_key' => 'pn_tasks_manager_nonce_error_required',
          'error_content' => esc_html(__('Security check failed: Nonce is required.', 'pn-tasks-manager')),
        ]);

        exit;
      }

      if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pn_tasks_manager_ajax_nonce'])), 'pn-tasks-manager-nonce')) {
        echo wp_json_encode([
          'error_key' => 'pn_tasks_manager_nonce_error_invalid',
          'error_content' => esc_html(__('Security check failed: Invalid nonce.', 'pn-tasks-manager')),
        ]);

        exit;
      }

      if (!array_key_exists('pn_tasks_manager_duplicate', $_POST)) {
        foreach (self::pn_tasks_manager_task_get_all_fields($post_id) as $pn_tasks_manager_field) {
          $pn_tasks_manager_input = array_key_exists('input', $pn_tasks_manager_field) ? $pn_tasks_manager_field['input'] : '';

          if (array_key_exists($pn_tasks_manager_field['id'], $_POST) || $pn_tasks_manager_input == 'html_multi') {
            $pn_tasks_manager_value = array_key_exists($pn_tasks_manager_field['id'], $_POST) ? 
              PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(
                wp_unslash(isset($_POST[$pn_tasks_manager_field['id']]) ? $_POST[$pn_tasks_manager_field['id']] : ''),
                $pn_tasks_manager_field['input'], 
                !empty($pn_tasks_manager_field['type']) ? $pn_tasks_manager_field['type'] : '',
                $pn_tasks_manager_field // Pass the entire field config
              ) : '';

            if (!empty($pn_tasks_manager_input)) {
              switch ($pn_tasks_manager_input) {
                case 'input':
                  if (array_key_exists('type', $pn_tasks_manager_field) && $pn_tasks_manager_field['type'] == 'checkbox') {
                    if (isset($_POST[$pn_tasks_manager_field['id']])) {
                      update_post_meta($post_id, $pn_tasks_manager_field['id'], $pn_tasks_manager_value);
                    } else {
                      update_post_meta($post_id, $pn_tasks_manager_field['id'], '');
                    }
                  } else {
                    update_post_meta($post_id, $pn_tasks_manager_field['id'], $pn_tasks_manager_value);
                  }

                  break;
                case 'select':
                  if (array_key_exists('multiple', $pn_tasks_manager_field) && $pn_tasks_manager_field['multiple']) {
                    $multi_array = [];
                    $empty = true;

                    if (array_key_exists($pn_tasks_manager_field['id'], $_POST) && !empty($_POST[$pn_tasks_manager_field['id']])) {
                      $post_value = isset($_POST[$pn_tasks_manager_field['id']]) ? $_POST[$pn_tasks_manager_field['id']] : [];
                      foreach (wp_unslash($post_value) as $multi_value) {
                        if (!empty($multi_value)) {
                          $empty = false;
                          $multi_array[] = PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(
                            $multi_value, 
                            $pn_tasks_manager_field['input'], 
                            !empty($pn_tasks_manager_field['type']) ? $pn_tasks_manager_field['type'] : '',
                            $pn_tasks_manager_field // Pass the entire field config
                          );
                        }
                      }
                    }

                    update_post_meta($post_id, $pn_tasks_manager_field['id'], $empty ? [] : $multi_array);
                    
                    // If this is the owners field and it's empty, assign the author
                    if ($pn_tasks_manager_field['id'] === 'pn_tasks_manager_task_owners' && $empty) {
                      $task = get_post($post_id);
                      if ($task && !empty($task->post_author)) {
                        update_post_meta($post_id, 'pn_tasks_manager_task_owners', [$task->post_author]);
                      }
                    }
                  } else {
                    update_post_meta($post_id, $pn_tasks_manager_field['id'], $pn_tasks_manager_value);
                  }
                  
                  break;
                case 'html_multi':
                  foreach ($pn_tasks_manager_field['html_multi_fields'] as $pn_tasks_manager_multi_field) {
                    if (array_key_exists($pn_tasks_manager_multi_field['id'], $_POST)) {
                      $multi_array = [];
                      $empty = true;

                      // Sanitize the POST data before using it
                      $sanitized_post_data = isset($_POST[$pn_tasks_manager_multi_field['id']]) ? 
                        array_map(function($value) {
                            return sanitize_text_field(wp_unslash($value));
                        }, (array)wp_unslash($_POST[$pn_tasks_manager_multi_field['id']])) : [];
                      
                      foreach ($sanitized_post_data as $multi_value) {
                        if (!empty($multi_value)) {
                          $empty = false;
                        }

                        $multi_array[] = PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(
                          $multi_value, 
                          $pn_tasks_manager_multi_field['input'], 
                          !empty($pn_tasks_manager_multi_field['type']) ? $pn_tasks_manager_multi_field['type'] : '',
                          $pn_tasks_manager_multi_field // Pass the entire field config
                        );
                      }

                      if (!$empty) {
                        update_post_meta($post_id, $pn_tasks_manager_multi_field['id'], $multi_array);
                      } else {
                        update_post_meta($post_id, $pn_tasks_manager_multi_field['id'], '');
                      }
                    }
                  }

                  break;
                case 'tags':
                  // Handle tags field - save as array
                  $tags_array_field_name = $pn_tasks_manager_field['id'] . '_tags_array';
                  if (array_key_exists($tags_array_field_name, $_POST)) {
                    $post_value = isset($_POST[$tags_array_field_name]) ? $_POST[$tags_array_field_name] : '';
                    $tags_json = PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(
                      wp_unslash($post_value),
                      'input',
                      'text',
                      $pn_tasks_manager_field
                    );
                    
                    // Decode JSON and save as array
                    $tags_array = json_decode($tags_json, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($tags_array)) {
                      update_post_meta($post_id, $pn_tasks_manager_field['id'], $tags_array);
                    } else {
                      // Fallback: treat as comma-separated string
                      $post_value = isset($_POST[$pn_tasks_manager_field['id']]) ? $_POST[$pn_tasks_manager_field['id']] : '';
                      $tags_string = PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(
                        wp_unslash($post_value),
                        'input',
                        'text',
                        $pn_tasks_manager_field
                      );
                      $tags_array = array_map('trim', explode(',', $tags_string));
                      $tags_array = array_filter($tags_array); // Remove empty values
                      update_post_meta($post_id, $pn_tasks_manager_field['id'], $tags_array);
                    }
                  } else {
                    // Fallback: save the text input value as comma-separated array
                    $post_value = isset($_POST[$pn_tasks_manager_field['id']]) ? $_POST[$pn_tasks_manager_field['id']] : '';
                    $tags_string = PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(
                      wp_unslash($post_value),
                      'input',
                      'text',
                      $pn_tasks_manager_field
                    );
                    $tags_array = array_map('trim', explode(',', $tags_string));
                    $tags_array = array_filter($tags_array); // Remove empty values
                    update_post_meta($post_id, $pn_tasks_manager_field['id'], $tags_array);
                  }
                  break;
                case 'taxonomy':
                  // Handle taxonomy field - save as WordPress taxonomy terms
                  $taxonomy = !empty($pn_tasks_manager_field['taxonomy']) ? $pn_tasks_manager_field['taxonomy'] : 'category';
                  
                  if (array_key_exists($pn_tasks_manager_field['id'], $_POST)) {
                    $post_value = isset($_POST[$pn_tasks_manager_field['id']]) ? $_POST[$pn_tasks_manager_field['id']] : '';
                    
                    if (array_key_exists('multiple', $pn_tasks_manager_field) && $pn_tasks_manager_field['multiple']) {
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
                  update_post_meta($post_id, $pn_tasks_manager_field['id'], $pn_tasks_manager_value);
                  break;
              }
            }
          } else {
            update_post_meta($post_id, $pn_tasks_manager_field['id'], '');
          }
        }
        
        // After saving all fields, ensure author is assigned if no owners were set
        $pn_tasks_manager_owners = get_post_meta($post_id, 'pn_tasks_manager_task_owners', true);
        $task = get_post($post_id);
        
        if ($task && !empty($task->post_author)) {
          // If owners list is empty or resolves to empty, set author as the default owner
          $owners_empty = false;
          if (empty($pn_tasks_manager_owners)) {
            $owners_empty = true;
          } elseif (is_array($pn_tasks_manager_owners)) {
            $pn_tasks_manager_owners = array_filter($pn_tasks_manager_owners, function($owner) {
              return !empty($owner);
            });
            if (empty($pn_tasks_manager_owners)) {
              $owners_empty = true;
            }
          }

          if ($owners_empty) {
            update_post_meta($post_id, 'pn_tasks_manager_task_owners', [$task->post_author]);
          }
        }
      }
    }
  }

  public function pn_tasks_manager_task_form_save($element_id, $key_value, $pn_tasks_manager_form_type, $pn_tasks_manager_form_subtype) {
    $post_type = !empty(get_post_type($element_id)) ? get_post_type($element_id) : 'pn_tasks_task';

    if ($post_type == 'pn_tasks_task') {
      switch ($pn_tasks_manager_form_type) {
        case 'post':
          switch ($pn_tasks_manager_form_subtype) {
            case 'post_check':
              // Handle task completion form
              if (!empty($element_id)) {
                // Save completed status
                $completed = isset($key_value['pn_tasks_manager_task_completed']) && $key_value['pn_tasks_manager_task_completed'] === 'on';
                
                update_post_meta($element_id, 'pn_tasks_manager_task_completed', $completed ? 'on' : '');

                if ($completed) {
                  // Store completion metadata
                  update_post_meta($element_id, 'pn_tasks_manager_task_completed_at', current_time('mysql'));
                  update_post_meta($element_id, 'pn_tasks_manager_task_completed_by', get_current_user_id());
                }

                // Save comment if provided
                $comment = !empty($key_value['pn_tasks_manager_task_comment']) ? sanitize_textarea_field($key_value['pn_tasks_manager_task_comment']) : '';
                
                if (!empty($comment)) {
                  $task_comments = get_post_meta($element_id, 'pn_tasks_manager_task_comments', true);
                  if (!is_array($task_comments)) {
                    $task_comments = [];
                  }

                  $timestamp = current_time('timestamp');
                  $task_comments[$timestamp] = [
                    'comment' => $comment,
                    'user_id' => get_current_user_id(),
                    'timestamp' => $timestamp,
                  ];

                  update_post_meta($element_id, 'pn_tasks_manager_task_comments', $task_comments);
                }
              }
              break;
            case 'post_new':
              // The post and its meta are already created by the generic handler in ajax-nopriv.php.
              // This hook only handles task-specific logic (taxonomy, owners, notifications).
              $task_id = $element_id;
              $current_user_id = get_current_user_id();

              // Handle taxonomy assignment
              if (!empty($key_value)) {
                foreach ($key_value as $key => $value) {
                  if ($key === 'pn_tasks_manager_task_category') {
                    $term_id = !empty($value) ? intval($value) : 0;
                    if ($term_id > 0) {
                      wp_set_post_terms($task_id, [$term_id], 'pn_tasks_manager_task_category', false);
                    }
                  }
                }
              }

              // Set default owner if not set or empty
              $pn_tasks_manager_owners = get_post_meta($task_id, 'pn_tasks_manager_task_owners', true);
              $owners_empty = false;

              if (empty($pn_tasks_manager_owners)) {
                $owners_empty = true;
              } elseif (is_array($pn_tasks_manager_owners)) {
                // Filter out empty values
                $pn_tasks_manager_owners = array_filter($pn_tasks_manager_owners, function($owner) {
                  return !empty($owner);
                });
                if (empty($pn_tasks_manager_owners)) {
                  $owners_empty = true;
                }
              }

              if ($owners_empty) {
                update_post_meta($task_id, 'pn_tasks_manager_task_owners', [$current_user_id]);
              }

              // Notify all assigned users about the new task
              $owners = $this->pn_tasks_manager_task_owners($task_id);
              if (!empty($owners)) {
                foreach ($owners as $owner_id) {
                  self::pn_tasks_manager_notify_assignment($task_id, intval($owner_id));
                }
              }
              break;
            case 'post_edit':
              // The post meta is already updated by the generic handler in ajax-nopriv.php.
              // This hook only handles task-specific logic (taxonomy, owners, notifications).
              $task_id = $element_id;

              // Capture previous owners before updating
              $previous_owners = $this->pn_tasks_manager_task_owners($task_id);
              $task = get_post($task_id);

              // Handle taxonomy assignment
              if (!empty($key_value)) {
                foreach ($key_value as $key => $value) {
                  if ($key === 'pn_tasks_manager_task_category') {
                    $term_id = !empty($value) ? intval($value) : 0;
                    if ($term_id > 0) {
                      wp_set_post_terms($task_id, [$term_id], 'pn_tasks_manager_task_category', false);
                    }
                  }
                }
              }
              
              // Ensure task author is always included in owners
              $pn_tasks_manager_owners = get_post_meta($task_id, 'pn_tasks_manager_task_owners', true);
              $task_author = $task ? $task->post_author : 0;
              
              if (!empty($task_author)) {
                $owners_empty = false;
                
                if (empty($pn_tasks_manager_owners)) {
                  $owners_empty = true;
                } elseif (is_array($pn_tasks_manager_owners)) {
                  // Filter out empty values
                  $pn_tasks_manager_owners = array_filter($pn_tasks_manager_owners, function($owner) {
                    return !empty($owner);
                  });
                  if (empty($pn_tasks_manager_owners)) {
                    $owners_empty = true;
                  }
                } else {
                  // Single value, convert to array
                  $pn_tasks_manager_owners = [intval($pn_tasks_manager_owners)];
                }
                
                if ($owners_empty) {
                  update_post_meta($task_id, 'pn_tasks_manager_task_owners', [$task_author]);
                }
              }

              // After ensuring owners, compute newly added owners and notify only them
              $current_owners = $this->pn_tasks_manager_task_owners($task_id);
              
              // Normalize both arrays to integers for proper comparison
              $previous_owners = is_array($previous_owners) ? array_map('intval', $previous_owners) : [];
              $current_owners = is_array($current_owners) ? array_map('intval', $current_owners) : [];
              
              // Remove duplicates and ensure unique values
              $previous_owners = array_unique($previous_owners);
              $current_owners = array_unique($current_owners);
              
              // Only notify newly added owners (not existing ones)
              $new_owners = array_diff($current_owners, $previous_owners);
              
              if (!empty($new_owners)) {
                foreach ($new_owners as $owner_id) {
                  $owner_id = intval($owner_id);
                  // Only send if owner ID is valid
                  if ($owner_id > 0) {
                    self::pn_tasks_manager_notify_assignment($task_id, $owner_id);
                  }
                }
              }

              // Repeated tasks are now calculated dynamically in the calendar
              break;
          }
      }
    }
  }

  public function pn_tasks_manager_task_register_scripts() {
    if (!wp_script_is('pn-tasks-manager-aux', 'registered')) {
      wp_register_script('pn-tasks-manager-aux', PN_TASKS_MANAGER_URL . 'assets/js/pn-tasks-manager-aux.js', [], PN_TASKS_MANAGER_VERSION, true);
    }

    if (!wp_script_is('pn-tasks-manager-forms', 'registered')) {
      wp_register_script('pn-tasks-manager-forms', PN_TASKS_MANAGER_URL . 'assets/js/pn-tasks-manager-forms.js', [], PN_TASKS_MANAGER_VERSION, true);
    }
    
    if (!wp_script_is('pn-tasks-manager-selector', 'registered')) {
      wp_register_script('pn-tasks-manager-selector', PN_TASKS_MANAGER_URL . 'assets/js/pn-tasks-manager-selector.js', [], PN_TASKS_MANAGER_VERSION, true);
    }
  }

  public function pn_tasks_manager_task_print_scripts() {
    // Use output buffering to safely capture script output in AJAX context
    ob_start();
    try {
      wp_print_scripts(['pn-tasks-manager-aux', 'pn-tasks-manager-forms', 'pn-tasks-manager-selector']);
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
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- contains script tags printed by WordPress.
      echo $scripts_output;
    }
  }

  public function pn_tasks_manager_task_list_wrapper() {
    ob_start();
    ?>
      <div class="pn-tasks-manager-cpt-list pn-tasks-manager-pn_tasks_task-list pn-tasks-manager-mb-100 pn-tasks-manager-max-width-700 pn-tasks-manager-margin-auto">
        <?php if (!is_user_logged_in()): ?>
          <?php echo do_shortcode('[pn-tasks-manager-call-to-action pn_tasks_manager_call_to_action_icon="error_outline" pn_tasks_manager_call_to_action_title="' . esc_html__('Access denied', 'pn-tasks-manager') . '" pn_tasks_manager_call_to_action_content="' . esc_html__('Log in or create an account to create and manage tasks.', 'pn-tasks-manager') . '" pn_tasks_manager_call_to_action_button_text="' . esc_html__('Create account', 'pn-tasks-manager') . '" pn_tasks_manager_call_to_action_button_link="' . esc_url(wp_registration_url()) . '"]'); ?>
        <?php else: ?>
          <div class="pn-tasks-manager-cpt-search-container pn-tasks-manager-mb-20 pn-tasks-manager-text-align-right">
            <div class="pn-tasks-manager-cpt-search-wrapper pn-tasks-manager-pn_tasks_task-search-wrapper">
              <input type="text" class="pn-tasks-manager-cpt-search-input pn-tasks-manager-pn_tasks_task-search-input pn-tasks-manager-input pn-tasks-manager-display-none" placeholder="<?php esc_attr_e('Filter...', 'pn-tasks-manager'); ?>" />
              <?php
              // Get calendar page URL
              $calendar_url = self::pn_tasks_manager_get_calendar_url();
              if ($calendar_url && $calendar_url !== home_url('/')): ?>
                <a href="<?php echo esc_url($calendar_url); ?>" class="pn-tasks-manager-text-decoration-none pn-tasks-manager-mr-10">
                  <i class="material-icons-outlined pn-tasks-manager-cursor-pointer pn-tasks-manager-font-size-25 pn-tasks-manager-vertical-align-middle pn-tasks-manager-tooltip" title="<?php esc_attr_e('View Calendar', 'pn-tasks-manager'); ?>">calendar_today</i>
                </a>
              <?php endif; ?>
              <i class="material-icons-outlined pn-tasks-manager-cpt-search-toggle pn-tasks-manager-pn_tasks_task-search-toggle pn-tasks-manager-cursor-pointer pn-tasks-manager-font-size-30 pn-tasks-manager-vertical-align-middle pn-tasks-manager-tooltip" title="<?php esc_attr_e('Search Tasks', 'pn-tasks-manager'); ?>">search</i>
              
              <i class="material-icons-outlined pn-tasks-manager-task-sort-toggle pn-tasks-manager-cursor-pointer pn-tasks-manager-font-size-30 pn-tasks-manager-vertical-align-middle pn-tasks-manager-tooltip pn-tasks-manager-mr-10" data-sort-order="date" title="<?php esc_attr_e('Sort alphabetically', 'pn-tasks-manager'); ?>">sort</i>
              
              <a href="#" class="pn-tasks-manager-popup-open-ajax pn-tasks-manager-text-decoration-none" data-pn-tasks-manager-popup-id="pn-tasks-manager-popup-pn_tasks_task-add" data-pn-tasks-manager-ajax-type="pn_tasks_manager_task_new">
                <i class="material-icons-outlined pn-tasks-manager-cursor-pointer pn-tasks-manager-font-size-30 pn-tasks-manager-vertical-align-middle pn-tasks-manager-tooltip" title="<?php esc_attr_e('Add new Task', 'pn-tasks-manager'); ?>">add</i>
              </a>
            </div>
          </div>
  
          <div class="pn-tasks-manager-cpt-list-wrapper pn-tasks-manager-pn_tasks_task-list-wrapper">
            <?php echo wp_kses(self::pn_tasks_manager_task_list(), PN_TASKS_MANAGER_KSES); ?>
          </div>
        <?php endif; ?>

      </div>
    <?php
    $pn_tasks_manager_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $pn_tasks_manager_return_string;
  }

  public function pn_tasks_manager_task_list($orderby = 'date') {
    $task_atts = [
      'fields' => 'ids',
      'numberposts' => -1,
      'post_type' => 'pn_tasks_task',
      'post_status' => 'any', 
      'orderby' => $orderby === 'title' ? 'title' : 'date',
      'order' => $orderby === 'title' ? 'ASC' : 'DESC',
      'meta_query' => [
        [
          'key' => 'pn_tasks_manager_repeated_from',
          'compare' => 'NOT EXISTS'
        ]
      ]
    ];
    
    if (class_exists('Polylang')) {
      $task_atts['lang'] = pll_current_language('slug');
    }

    $task = get_posts($task_atts);

    // Filter assets based on user permissions
    $task = PN_TASKS_MANAGER_Functions_User::pn_tasks_manager_filter_user_posts($task, 'pn_tasks_task');

    ob_start();
    ?>
      <ul class="pn-tasks-manager-tasks pn-tasks-manager-list-style-none pn-tasks-manager-p-0 pn-tasks-manager-margin-auto">
        <?php if (!empty($task)): ?>
          <?php foreach ($task as $task_id): ?>
            <?php 
              $is_completed = get_post_meta($task_id, 'pn_tasks_manager_task_completed', true) === 'on';
              // Get icon and color - task meta has priority over category
              $category_style = self::pn_tasks_manager_get_task_category_style($task_id);
              $task_icon_meta = get_post_meta($task_id, 'pn_tasks_manager_task_icon', true);
              $task_color_meta = get_post_meta($task_id, 'pn_tasks_manager_task_color', true);
              
              // Use task icon if set, otherwise use category icon
              if (!empty($task_icon_meta)) {
                $task_icon = $task_icon_meta;
                // Use task color if set, otherwise use category color
                $task_color = !empty($task_color_meta) ? $task_color_meta : $category_style['color'];
              } else {
                // Use category icon if set, otherwise empty
                $task_icon = !empty($category_style['icon']) && $category_style['icon'] !== 'event' ? $category_style['icon'] : '';
                // Use category color
                $task_color = $category_style['color'];
              }
              
              // Final fallback for color if still empty
              if (empty($task_color)) {
                $task_color = get_option('pn_tasks_manager_color_main') ?: '#b84a00'; // Default color from settings
              }
            ?>
            <li class="pn-tasks-manager-task pn-tasks-manager-pn_tasks_task-list-item pn-tasks-manager-mb-10 <?php echo $is_completed ? 'pn-tasks-manager-completed' : ''; ?>" data-pn_tasks_manager_task-id="<?php echo esc_attr($task_id); ?>">
              <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-80-percent">
                  <a href="#" class="pn-tasks-manager-popup-open-ajax pn-tasks-manager-text-decoration-none" data-pn-tasks-manager-popup-id="pn-tasks-manager-popup-pn_tasks_task-view" data-pn-tasks-manager-ajax-type="pn_tasks_manager_task_view" data-pn_tasks_manager_task-id="<?php echo esc_attr($task_id); ?>">
                    <?php if (!empty($task_icon)): ?>
                      <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle pn-tasks-manager-mr-10" style="color: <?php echo esc_attr($task_color); ?> !important;"><?php echo esc_html($task_icon); ?></i>
                    <?php endif; ?>
                    <span><?php echo esc_html(get_the_title($task_id)); ?></span>
                  </a>
                </div>

                <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-20-percent pn-tasks-manager-text-align-right pn-tasks-manager-position-relative">
                  <a href="#" class="pn-tasks-manager-toggle-completed pn-tasks-manager-ml-10 pn-tasks-manager-tooltip" title="<?php echo $is_completed ? esc_attr__('Mark as not done', 'pn-tasks-manager') : esc_attr__('Mark as done', 'pn-tasks-manager'); ?>" aria-label="<?php echo $is_completed ? esc_attr__('Mark as not done', 'pn-tasks-manager') : esc_attr__('Mark as done', 'pn-tasks-manager'); ?>">
                    <i class="material-icons-outlined pn-tasks-manager-font-size-30 pn-tasks-manager-vertical-align-middle <?php echo $is_completed ? 'pn-tasks-manager-color-green' : ''; ?>"><?php echo $is_completed ? 'task_alt' : 'circle'; ?></i>
                  </a>
                  <i class="material-icons-outlined pn-tasks-manager-menu-more-btn pn-tasks-manager-cursor-pointer pn-tasks-manager-vertical-align-middle pn-tasks-manager-font-size-30">more_vert</i>

                  <div class="pn-tasks-manager-menu-more pn-tasks-manager-z-index-99 pn-tasks-manager-display-none-soft">
                    <ul class="pn-tasks-manager-list-style-none">
                      <li>
                        <a href="#" class="pn-tasks-manager-popup-open-ajax pn-tasks-manager-text-decoration-none" data-pn-tasks-manager-popup-id="pn-tasks-manager-popup-pn_tasks_task-view" data-pn-tasks-manager-ajax-type="pn_tasks_manager_task_view" data-pn_tasks_manager_task-id="<?php echo esc_attr($task_id); ?>">
                          <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                            <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-70-percent">
                              <p><?php esc_html_e('View Task', 'pn-tasks-manager'); ?></p>
                            </div>
                            <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-20-percent pn-tasks-manager-text-align-right">
                              <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle pn-tasks-manager-font-size-30 pn-tasks-manager-ml-30">visibility</i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li>
                        <a href="#" class="pn-tasks-manager-popup-open-ajax pn-tasks-manager-text-decoration-none" data-pn-tasks-manager-popup-id="pn-tasks-manager-popup-pn_tasks_task-check" data-pn-tasks-manager-ajax-type="pn_tasks_manager_task_check">
                          <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                            <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-70-percent">
                              <p><?php esc_html_e('Complete Task', 'pn-tasks-manager'); ?></p>
                            </div>
                            <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-20-percent pn-tasks-manager-text-align-right">
                              <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle pn-tasks-manager-font-size-30 pn-tasks-manager-ml-30">check_circle</i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li>
                        <a href="#" class="pn-tasks-manager-popup-open-ajax pn-tasks-manager-text-decoration-none" data-pn-tasks-manager-popup-id="pn-tasks-manager-popup-pn_tasks_task-edit" data-pn-tasks-manager-ajax-type="pn_tasks_manager_task_edit"> 
                          <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                            <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-70-percent">
                              <p><?php esc_html_e('Edit Task', 'pn-tasks-manager'); ?></p>
                            </div>
                            <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-20-percent pn-tasks-manager-text-align-right">
                              <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle pn-tasks-manager-font-size-30 pn-tasks-manager-ml-30">edit</i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li>
                        <a href="#" class="pn-tasks-manager-pn_tasks_manager_task-duplicate-post">
                          <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                            <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-70-percent">
                              <p><?php esc_html_e('Duplicate Task', 'pn-tasks-manager'); ?></p>
                            </div>
                            <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-20-percent pn-tasks-manager-text-align-right">
                              <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle pn-tasks-manager-font-size-30 pn-tasks-manager-ml-30">copy</i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li>
                        <a href="#" class="pn-tasks-manager-popup-open" data-pn-tasks-manager-popup-id="pn-tasks-manager-popup-pn_tasks_task-remove">
                          <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                            <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-70-percent">
                              <p><?php esc_html_e('Remove Task', 'pn-tasks-manager'); ?></p>
                            </div>
                            <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-20-percent pn-tasks-manager-text-align-right">
                              <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle pn-tasks-manager-font-size-30 pn-tasks-manager-ml-30">delete</i>
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

        <li class="pn-tasks-manager-add-new-cpt pn-tasks-manager-mt-50 pn-tasks-manager-task" data-pn_tasks_manager_task-id="0">
          <?php if (is_user_logged_in()): ?>
            <a href="#" class="pn-tasks-manager-popup-open-ajax pn-tasks-manager-text-decoration-none" data-pn-tasks-manager-popup-id="pn-tasks-manager-popup-pn_tasks_task-add" data-pn-tasks-manager-ajax-type="pn_tasks_manager_task_new">
              <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-20-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-text-align-center">
                  <i class="material-icons-outlined pn-tasks-manager-cursor-pointer pn-tasks-manager-vertical-align-middle pn-tasks-manager-font-size-30 pn-tasks-manager-width-25">add</i>
                </div>
                <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-80-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent">
                  <?php esc_html_e('Add new Task', 'pn-tasks-manager'); ?>
                </div>
              </div>
            </a>
          <?php endif ?>
        </li>
      </ul>
      
      <?php
      // Get calendar page URL
      $calendar_url = self::pn_tasks_manager_get_calendar_url();
      if ($calendar_url && $calendar_url !== home_url('/')): ?>
        <div class="pn-tasks-manager-task-list-footer pn-tasks-manager-text-align-center pn-tasks-manager-mt-30">
          <a href="<?php echo esc_url($calendar_url); ?>" class="pn-tasks-manager-btn pn-tasks-manager-btn-primary">
            <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle pn-tasks-manager-mr-10">calendar_today</i>
            <?php esc_html_e('View Calendar', 'pn-tasks-manager'); ?>
          </a>
        </div>
      <?php endif; ?>
    <?php
    $pn_tasks_manager_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $pn_tasks_manager_return_string;
  }

  public function pn_tasks_manager_task_view($task_id) {
    // Validate task ID
    if (empty($task_id) || !is_numeric($task_id)) {
      return '<div class="pn_tasks_manager_task-view pn-tasks-manager-p-30"><p class="pn-tasks-manager-text-align-center">' . esc_html__('Invalid task ID', 'pn-tasks-manager') . '</p></div>';
    }
    
    // Check if task exists
    $task = get_post($task_id);
    if (!$task || $task->post_type !== 'pn_tasks_task') {
      return '<div class="pn_tasks_manager_task-view pn-tasks-manager-p-30"><p class="pn-tasks-manager-text-align-center">' . esc_html__('Task not found', 'pn-tasks-manager') . '</p></div>';
    }

    // Check if user is owner or administrator
    $current_user_id = get_current_user_id();
    $is_administrator = current_user_can('manage_options') || current_user_can('administrator');
    $is_owner = false;
    
    if ($current_user_id > 0) {
      $task_owners = $this->pn_tasks_manager_task_owners($task_id);
      $is_owner = in_array($current_user_id, $task_owners);
    }

    // Always show view-only mode for pn_tasks_manager_task_view
    // The completion form (pn_tasks_manager_task_check) is accessed separately via the menu
    
    // Start output buffering
    if (ob_get_level() > 0) {
      ob_end_clean();
    }
    ob_start();
    
    // Don't print scripts in AJAX context as they can cause output issues
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
      try {
        self::pn_tasks_manager_task_register_scripts();
        self::pn_tasks_manager_task_print_scripts();
      } catch (Exception $e) {
        // Silently continue if scripts can't be registered/printed
      } catch (Error $e) {
        // Silently continue if scripts can't be registered/printed
      }
    }
    ?>
      <div class="pn_tasks_manager_task-view pn-tasks-manager-p-30" data-pn_tasks_manager_task-id="<?php echo esc_attr($task_id); ?>">
        <a href="#" class="pn-tasks-manager-popup-close pn-tasks-manager-text-decoration-none pn-tasks-manager-close-icon"><i class="material-icons-outlined">close</i></a>
        <h4 class="pn-tasks-manager-text-align-center pn-tasks-manager-mb-30"><?php echo esc_html(get_the_title($task_id)); ?></h4>
        
        <div class="pn-tasks-manager-word-wrap-break-word pn-tasks-manager-mb-30">
          <?php 
          $pn_tasks_manager_content = '';
          if ($task && !empty($task->post_content)) {
            $pn_tasks_manager_content = str_replace(']]>', ']]&gt;', self::pn_tasks_manager_filter_task_content($task->post_content));
          }
          if (!empty($pn_tasks_manager_content)) {
            echo '<p>' . wp_kses($pn_tasks_manager_content, PN_TASKS_MANAGER_KSES) . '</p>';
          }
          ?>
        </div>

        <div class="pn_tasks_manager_task-view-list">
          <?php 
          try {
            $all_fields = self::pn_tasks_manager_task_get_all_fields($task_id);
            
            if (!empty($all_fields) && is_array($all_fields)) {
              foreach ($all_fields as $pn_tasks_manager_field): 
                if (empty($pn_tasks_manager_field) || !is_array($pn_tasks_manager_field)) {
                  continue;
                }
                
                if (empty($pn_tasks_manager_field['id']) || in_array($pn_tasks_manager_field['id'], ['pn_tasks_manager_task_title', 'pn_tasks_manager_task_description', 'pn_tasks_manager_ajax_nonce', 'pn_tasks_manager_task_form'])) {
                  continue;
                }
                
                // Check if field has a value - skip empty fields
                $field_has_value = false;
                
                // Check if field has a parent and if parent is enabled
                if (!empty($pn_tasks_manager_field['parent']) && !empty($pn_tasks_manager_field['parent_option'])) {
                  $parent_value = get_post_meta($task_id, $pn_tasks_manager_field['parent'], true);
                  // If parent is not enabled, skip this field
                  if ($parent_value !== $pn_tasks_manager_field['parent_option']) {
                    continue;
                  }
                }
                
                // Get field value based on input type
                if ($pn_tasks_manager_field['input'] === 'taxonomy') {
                  $taxonomy = !empty($pn_tasks_manager_field['taxonomy']) ? $pn_tasks_manager_field['taxonomy'] : 'category';
                  $terms = wp_get_post_terms($task_id, $taxonomy, ['fields' => 'ids']);
                  $field_has_value = !empty($terms) && !is_wp_error($terms) && is_array($terms) && count($terms) > 0;
                } else {
                  try {
                    $field_value = PN_TASKS_MANAGER_Forms::pn_tasks_manager_get_field_value($pn_tasks_manager_field['id'], 'post', $task_id, 0, 0, $pn_tasks_manager_field);
                    
                    // Check if value is not empty
                    if ($pn_tasks_manager_field['input'] === 'input' && $pn_tasks_manager_field['type'] === 'checkbox') {
                      // For checkboxes, show if value is 'on'
                      $field_has_value = ($field_value === 'on');
                    } elseif ($pn_tasks_manager_field['input'] === 'input' && $pn_tasks_manager_field['type'] === 'url') {
                      // For URLs, check if not empty and valid
                      $field_has_value = !empty($field_value) && filter_var($field_value, FILTER_VALIDATE_URL);
                    } elseif ($pn_tasks_manager_field['input'] === 'input' && $pn_tasks_manager_field['type'] === 'color') {
                      // For colors, check if not empty
                      $field_has_value = !empty($field_value);
                    } elseif ($pn_tasks_manager_field['input'] === 'input' && ($pn_tasks_manager_field['type'] === 'time' || $pn_tasks_manager_field['type'] === 'date' || $pn_tasks_manager_field['type'] === 'datetime-local')) {
                      // For time/date fields, check if not empty
                      $field_has_value = !empty($field_value) && trim($field_value) !== '';
                    } elseif ($pn_tasks_manager_field['input'] === 'input' && ($pn_tasks_manager_field['type'] === 'number' || $pn_tasks_manager_field['type'] === 'text')) {
                      // For number/text fields, check if not empty
                      $field_has_value = !empty($field_value) && trim($field_value) !== '';
                    } elseif ($pn_tasks_manager_field['input'] === 'select') {
                      // For selects, check if value is set and not empty
                      $field_has_value = !empty($field_value) && trim($field_value) !== '';
                    } elseif ($pn_tasks_manager_field['input'] === 'image') {
                      // For images, check if there are images
                      $image_value = get_post_meta($task_id, $pn_tasks_manager_field['id'], true);
                      $field_has_value = !empty($image_value);
                    } elseif ($pn_tasks_manager_field['input'] === 'textarea') {
                      // For textareas, check if not empty
                      $field_has_value = !empty($field_value) && trim($field_value) !== '';
                    } else {
                      // For other fields, check if not empty
                      $field_has_value = !empty($field_value) && trim($field_value) !== '';
                    }
                  } catch (Exception $e) {
                    continue;
                  } catch (Error $e) {
                    continue;
                  }
                }
                
                // Only display field if it has a value
                if ($field_has_value):
                  try {
                    echo wp_kses(PN_TASKS_MANAGER_Forms::pn_tasks_manager_input_display_wrapper($pn_tasks_manager_field, 'post', $task_id), PN_TASKS_MANAGER_KSES);
                  } catch (Exception $e) {
                    // Continue with next field instead of breaking
                    continue;
                  } catch (Error $e) {
                    // Continue with next field instead of breaking
                    continue;
                  }
                endif;
              endforeach;
            }
          } catch (Exception $e) {
            echo '<p class="pn-tasks-manager-text-align-center">' . esc_html__('Error loading task fields', 'pn-tasks-manager') . '</p>';
          } catch (Error $e) {
            echo '<p class="pn-tasks-manager-text-align-center">' . esc_html__('Error loading task fields', 'pn-tasks-manager') . '</p>';
          }
          ?>

          <?php if ($is_owner || $is_administrator): ?>
            <div class="pn-tasks-manager-text-align-right pn-tasks-manager-task" data-pn_tasks_manager_task-id="<?php echo esc_attr($task_id); ?>">
              <a href="#" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-popup-open-ajax" data-pn-tasks-manager-popup-id="pn-tasks-manager-popup-pn_tasks_task-edit" data-pn-tasks-manager-ajax-type="pn_tasks_manager_task_edit"><?php esc_html_e('Edit Task', 'pn-tasks-manager'); ?></a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php
    $pn_tasks_manager_return_string = '';
    
    // Get the output buffer content
    if (ob_get_level() > 0) {
      $pn_tasks_manager_return_string = ob_get_contents();
      ob_end_clean();
    }
    
    // If output is empty, return a basic error message
    if (empty($pn_tasks_manager_return_string) || trim($pn_tasks_manager_return_string) === '') {
      $pn_tasks_manager_return_string = '<div class="pn_tasks_manager_task-view pn-tasks-manager-p-30"><p class="pn-tasks-manager-text-align-center">' . esc_html__('Error loading task content', 'pn-tasks-manager') . '</p></div>';
    }
    
    return $pn_tasks_manager_return_string;
  }

  /**
   * Get task check form fields
   * 
   * @since    1.0.0
   * @param    int    $task_id    Task ID
   * @return   array  Array of field configurations
   */
  public static function pn_tasks_manager_task_get_check_fields($task_id = 0) {
    $is_completed = !empty($task_id) ? (get_post_meta($task_id, 'pn_tasks_manager_task_completed', true) === 'on') : false;
    
    $check_fields = [];
    
    $check_fields['pn_tasks_manager_task_completed'] = [
      'id' => 'pn_tasks_manager_task_completed',
      'class' => 'pn-tasks-manager-input',
      'input' => 'input',
      'type' => 'checkbox',
      'value' => $is_completed ? 'on' : '',
      'label' => __('Mark as completed', 'pn-tasks-manager'),
    ];
    
    $check_fields['pn_tasks_manager_task_comment'] = [
      'id' => 'pn_tasks_manager_task_comment',
      'class' => 'pn-tasks-manager-input pn-tasks-manager-width-100-percent',
      'input' => 'textarea',
      'required' => false,
      'value' => '',
      'label' => __('Comments', 'pn-tasks-manager'),
      'placeholder' => __('Add a comment about this task execution...', 'pn-tasks-manager'),
    ];
    
    $check_fields['pn_tasks_manager_task_form'] = [
      'id' => 'pn_tasks_manager_task_form',
      'input' => 'input',
      'type' => 'hidden',
    ];
    
    $check_fields['pn_tasks_manager_ajax_nonce'] = [
      'id' => 'pn_tasks_manager_ajax_nonce',
      'input' => 'input',
      'type' => 'nonce',
    ];
    
    return apply_filters('pn_tasks_manager_task_check_fields', $check_fields, $task_id);
  }

  /**
   * Show task completion form (for admin panel)
   * 
   * @since    1.0.0
   * @param    int    $task_id    Task ID
   * @return   string HTML form
   */
  public function pn_tasks_manager_task_check($task_id) {
    // Validate task ID
    if (empty($task_id) || !is_numeric($task_id)) {
      return '<div class="pn_tasks_manager_task-check pn-tasks-manager-p-30"><p class="pn-tasks-manager-text-align-center">' . esc_html__('Invalid task ID', 'pn-tasks-manager') . '</p></div>';
    }
    
    // Check if task exists
    $task = get_post($task_id);
    if (!$task || $task->post_type !== 'pn_tasks_task') {
      return '<div class="pn_tasks_manager_task-check pn-tasks-manager-p-30"><p class="pn-tasks-manager-text-align-center">' . esc_html__('Task not found', 'pn-tasks-manager') . '</p></div>';
    }

    // Check if user is administrator or owner
    $current_user_id = get_current_user_id();
    $is_administrator = current_user_can('manage_options') || current_user_can('administrator');
    $is_owner = false;
    
    if ($current_user_id > 0) {
      $task_owners = $this->pn_tasks_manager_task_owners($task_id);
      $is_owner = in_array($current_user_id, $task_owners);
    }
    
    $can_edit = $is_administrator || $is_owner;

    $task_comments = get_post_meta($task_id, 'pn_tasks_manager_task_comments', true);
    if (!is_array($task_comments)) {
      $task_comments = [];
    }
    
    ob_start();
    // Don't print scripts in AJAX context as they can cause output issues
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
      try {
        self::pn_tasks_manager_task_register_scripts();
        self::pn_tasks_manager_task_print_scripts();
      } catch (Exception $e) {
        // Silently continue if scripts can't be registered/printed
      } catch (Error $e) {
        // Silently continue if scripts can't be registered/printed
      }
    }
    ?>
      <div class="pn_tasks_manager_task-check pn-tasks-manager-p-30" data-pn_tasks_manager_task-id="<?php echo esc_attr($task_id); ?>">
        <a href="#" class="pn-tasks-manager-popup-close pn-tasks-manager-text-decoration-none pn-tasks-manager-close-icon"><i class="material-icons-outlined">close</i></a>
        
        <h4 class="pn-tasks-manager-mb-30"><?php echo esc_html(get_the_title($task_id)); ?></h4>

        <form action="" method="post" id="pn-tasks-manager-task-check-form" class="pn-tasks-manager-form">
          <?php 
          $check_fields = self::pn_tasks_manager_task_get_check_fields($task_id);
          foreach ($check_fields as $pn_tasks_manager_field): 
            if (in_array($pn_tasks_manager_field['id'], ['pn_tasks_manager_task_form', 'pn_tasks_manager_ajax_nonce'])) {
              continue;
            }
            echo wp_kses(PN_TASKS_MANAGER_Forms::pn_tasks_manager_input_wrapper_builder($pn_tasks_manager_field, 'post', $task_id), PN_TASKS_MANAGER_KSES);
          endforeach;
          ?>

          <?php if (!empty($task_comments)): ?>
            <div class="pn-tasks-manager-mb-20">
              <h5 class="pn-tasks-manager-mb-20 pn-tasks-manager-font-size-medium"><?php esc_html_e('Previous comments', 'pn-tasks-manager'); ?></h5>
              <div class="pn-tasks-manager-task-comments-list">
                <?php 
                // Sort comments by timestamp descending
                krsort($task_comments);
                foreach ($task_comments as $timestamp => $comment_data): 
                  $comment = is_array($comment_data) && isset($comment_data['comment']) ? $comment_data['comment'] : $comment_data;
                  $user_id = is_array($comment_data) && isset($comment_data['user_id']) ? $comment_data['user_id'] : 0;
                  $user_name = $user_id ? PN_TASKS_MANAGER_Functions_User::pn_tasks_manager_user_get_name($user_id) : __('Unknown', 'pn-tasks-manager');
                  $date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
                ?>
                  <div class="pn-tasks-manager-task-comment">
                    <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent pn-tasks-manager-mb-8">
                      <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-70-percent">
                        <strong class="pn-tasks-manager-font-size-small pn-tasks-manager-color-dark"><?php echo esc_html($user_name); ?></strong>
                      </div>
                      <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-30-percent pn-tasks-manager-text-align-right">
                        <small class="pn-tasks-manager-font-size-small pn-tasks-manager-color-gray"><?php echo esc_html($date); ?></small>
                      </div>
                    </div>
                    <div class="pn-tasks-manager-word-wrap-break-word pn-tasks-manager-task-comment-content">
                      <?php echo wp_kses(wpautop($comment), PN_TASKS_MANAGER_KSES); ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="pn-tasks-manager-text-align-right">
            <?php if ($can_edit): ?>
              <a href="#" class="pn-tasks-manager-popup-open-ajax pn-tasks-manager-mr-50" data-pn-tasks-manager-popup-id="pn-tasks-manager-popup-pn_tasks_task-edit" data-pn-tasks-manager-ajax-type="pn_tasks_manager_task_edit" data-pn_tasks_manager_task-id="<?php echo esc_attr($task_id); ?>">
                <?php esc_html_e('Edit Task', 'pn-tasks-manager'); ?>
              </a>
            <?php endif; ?>
            <input class="pn-tasks-manager-btn" type="submit" data-pn-tasks-manager-type="post" data-pn-tasks-manager-subtype="post_check" data-pn-tasks-manager-post-id="<?php echo esc_attr($task_id); ?>" data-pn-tasks-manager-post-type="pn_tasks_task" value="<?php esc_attr_e('Save', 'pn-tasks-manager'); ?>" />
          </div>
        </form>
      </div>
    <?php
    $pn_tasks_manager_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $pn_tasks_manager_return_string;
  }

  public function pn_tasks_manager_task_new() {
    if (!is_user_logged_in()) {
      wp_die(esc_html__('You must be logged in to create a new asset.', 'pn-tasks-manager'), esc_html__('Access Denied', 'pn-tasks-manager'), ['response' => 403]);
    }

    ob_start();
    self::pn_tasks_manager_task_register_scripts();
    self::pn_tasks_manager_task_print_scripts();
    ?>
      <div class="pn_tasks_manager_task-new pn-tasks-manager-p-30">
        <a href="#" class="pn-tasks-manager-popup-close pn-tasks-manager-text-decoration-none pn-tasks-manager-close-icon"><i class="material-icons-outlined">close</i></a>
        
        <h4 class="pn-tasks-manager-mb-30"><?php esc_html_e('Add new Task', 'pn-tasks-manager'); ?></h4>

        <form action="" method="post" id="pn-tasks-manager-task-form-new" class="pn-tasks-manager-form">      
          <?php foreach (self::pn_tasks_manager_task_get_all_fields(0) as $pn_tasks_manager_field): ?>
            <?php echo wp_kses(PN_TASKS_MANAGER_Forms::pn_tasks_manager_input_wrapper_builder($pn_tasks_manager_field, 'post'), PN_TASKS_MANAGER_KSES); ?>
          <?php endforeach ?>

          <div class="pn-tasks-manager-text-align-right">
            <input class="pn-tasks-manager-btn" data-pn-tasks-manager-type="post" data-pn-tasks-manager-subtype="post_new" data-pn-tasks-manager-post-type="pn_tasks_task" type="submit" value="<?php esc_attr_e('Create Task', 'pn-tasks-manager'); ?>"/>
          </div>
        </form> 
      </div>
    <?php
    $pn_tasks_manager_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $pn_tasks_manager_return_string;
  }

  public function pn_tasks_manager_task_edit($task_id) {
    ob_start();
    self::pn_tasks_manager_task_register_scripts();
    self::pn_tasks_manager_task_print_scripts();
    ?>
      <div class="pn_tasks_manager_task-edit pn-tasks-manager-p-30">
        <a href="#" class="pn-tasks-manager-popup-close pn-tasks-manager-text-decoration-none pn-tasks-manager-close-icon"><i class="material-icons-outlined">close</i></a>
        
        <p class="pn-tasks-manager-text-align-center pn-tasks-manager-mb-0 pn-tasks-manager-font-size-small"><?php esc_html_e('Editing Task', 'pn-tasks-manager'); ?></p>
        
        <h4 class="pn-tasks-manager-text-align-center pn-tasks-manager-mb-30"><?php echo esc_html(get_the_title($task_id)); ?></h4>

        <form action="" method="post" id="pn-tasks-manager-task-form-edit" class="pn-tasks-manager-form">      
          <?php foreach (self::pn_tasks_manager_task_get_all_fields($task_id) as $pn_tasks_manager_field): ?>
            <?php echo wp_kses(PN_TASKS_MANAGER_Forms::pn_tasks_manager_input_wrapper_builder($pn_tasks_manager_field, 'post', $task_id), PN_TASKS_MANAGER_KSES); ?>
          <?php endforeach ?>

          <div class="pn-tasks-manager-text-align-right">
            <input class="pn-tasks-manager-btn" type="submit" data-pn-tasks-manager-type="post" data-pn-tasks-manager-subtype="post_edit" data-pn-tasks-manager-post-type="pn_tasks_task" data-pn-tasks-manager-post-id="<?php echo esc_attr($task_id); ?>" value="<?php esc_attr_e('Save Task', 'pn-tasks-manager'); ?>"/>
          </div>
        </form> 
      </div>
    <?php
    $pn_tasks_manager_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $pn_tasks_manager_return_string;
  }

  public function pn_tasks_manager_task_history_add($task_id) {  
    $pn_tasks_manager_meta = get_post_meta($task_id);
    $pn_tasks_manager_meta_array = [];

    if (!empty($pn_tasks_manager_meta)) {
      foreach ($pn_tasks_manager_meta as $pn_tasks_manager_meta_key => $pn_tasks_manager_meta_value) {
        if (strpos((string)$pn_tasks_manager_meta_key, 'pn_tasks_manager_') !== false && !empty($pn_tasks_manager_meta_value[0])) {
          $pn_tasks_manager_meta_array[$pn_tasks_manager_meta_key] = $pn_tasks_manager_meta_value[0];
        }
      }
    }
    
    if(empty(get_post_meta($task_id, 'pn_tasks_manager_task_history', true))) {
      update_post_meta($task_id, 'pn_tasks_manager_task_history', [strtotime('now') => $pn_tasks_manager_meta_array]);
    } else {
      $pn_tasks_manager_post_meta_new = get_post_meta($task_id, 'pn_tasks_manager_task_history', true);
      $pn_tasks_manager_post_meta_new[strtotime('now')] = $pn_tasks_manager_meta_array;
      update_post_meta($task_id, 'pn_tasks_manager_task_history', $pn_tasks_manager_post_meta_new);
    }
  }

  public function pn_tasks_manager_task_get_next($task_id) {
    // Try new periodicity fields first, fallback to old field
    $pn_tasks_manager_task_periodicity_value = get_post_meta($task_id, 'pn_tasks_manager_task_periodicity_value', true);
    $pn_tasks_manager_task_periodicity_type = get_post_meta($task_id, 'pn_tasks_manager_task_periodicity_type', true);
    
    // Build periodicity string
    $pn_tasks_manager_task_periodicity = '';
    if (!empty($pn_tasks_manager_task_periodicity_value) && !empty($pn_tasks_manager_task_periodicity_type)) {
      switch ($pn_tasks_manager_task_periodicity_type) {
        case 'days':
          $pn_tasks_manager_task_periodicity = $pn_tasks_manager_task_periodicity_value . ' days';
          break;
        case 'weeks':
          $pn_tasks_manager_task_periodicity = ($pn_tasks_manager_task_periodicity_value * 7) . ' days';
          break;
        case 'months':
          $pn_tasks_manager_task_periodicity = $pn_tasks_manager_task_periodicity_value . ' months';
          break;
      }
    } else {
      // Fallback to old field
      $pn_tasks_manager_task_periodicity = get_post_meta($task_id, 'pn_tasks_manager_task_periodicity', true);
    }
    
    $pn_tasks_manager_task_date = get_post_meta($task_id, 'pn_tasks_manager_task_date', true);
    $pn_tasks_manager_task_time = get_post_meta($task_id, 'pn_tasks_manager_task_time', true);

    $pn_tasks_manager_task_timestamp = strtotime($pn_tasks_manager_task_date . ' ' . $pn_tasks_manager_task_time);

    if (!empty($pn_tasks_manager_task_periodicity) && !empty($pn_tasks_manager_task_timestamp)) {
      $now = strtotime('now');

      while ($pn_tasks_manager_task_timestamp < $now) {
        $pn_tasks_manager_task_timestamp = strtotime('+' . $pn_tasks_manager_task_periodicity, $pn_tasks_manager_task_timestamp);
      }

      return $pn_tasks_manager_task_timestamp;
    }
  }

  /**
   * Get users list for select field
   * 
   * @return array Array of user ID => display name
   */
  public static function pn_tasks_manager_get_users_for_select() {
    $users = get_users([
      'orderby' => 'display_name',
      'order' => 'ASC',
    ]);
    
    $users_array = [];
    foreach ($users as $user) {
      $display_name = PN_TASKS_MANAGER_Functions_User::pn_tasks_manager_user_get_name($user->ID);
      $users_array[$user->ID] = $display_name . ' (' . $user->user_email . ')';
    }
    
    return $users_array;
  }

  public function pn_tasks_manager_task_owners($task_id) {
    $pn_tasks_manager_owners = get_post_meta($task_id, 'pn_tasks_manager_task_owners', true);
    $task = get_post($task_id);
    $pn_tasks_manager_owners_array = [];
    
    // Always include the author
    if (!empty($task) && !empty($task->post_author)) {
      $pn_tasks_manager_owners_array[] = $task->post_author;
    }

    if (!empty($pn_tasks_manager_owners)) {
      if (is_array($pn_tasks_manager_owners)) {
        foreach ($pn_tasks_manager_owners as $owner_id) {
          $pn_tasks_manager_owners_array[] = intval($owner_id);
        }
      } else {
        $pn_tasks_manager_owners_array[] = intval($pn_tasks_manager_owners);
      }
    }

    return array_unique($pn_tasks_manager_owners_array);
  }

  /**
   * Toggle task completed state and set metadata like completed_at and completed_by
   *
   * @param int $task_id
   * @param int $user_id The user performing the action
   * @return bool New completed state (true if completed)
   */
  public function pn_tasks_manager_task_toggle_completed($task_id, $user_id = 0) {
    $task_id = intval($task_id);
    if (!$task_id) { return false; }
    $task = get_post($task_id);
    if (!$task || $task->post_type !== 'pn_tasks_task') { return false; }

    $current = get_post_meta($task_id, 'pn_tasks_manager_task_completed', true) === 'on';
    $new = !$current;
    update_post_meta($task_id, 'pn_tasks_manager_task_completed', $new ? 'on' : '');

    if ($new) {
      // Store completion metadata for later reporting
      update_post_meta($task_id, 'pn_tasks_manager_task_completed_at', current_time('mysql'));
      if (!empty($user_id)) {
        update_post_meta($task_id, 'pn_tasks_manager_task_completed_by', intval($user_id));
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
  public function pn_tasks_manager_reset_repeated_tasks() {
    // Get all tasks that are marked as repeat
    $repeated_tasks = get_posts([
      'fields' => 'ids',
      'post_type' => 'pn_tasks_task',
      'post_status' => 'publish',
      'numberposts' => -1,
      'meta_query' => [
        [
          'key' => 'pn_tasks_manager_task_repeat',
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
      $task_date = get_post_meta($task_id, 'pn_tasks_manager_task_date', true);
      $task_time = get_post_meta($task_id, 'pn_tasks_manager_task_time', true);
      $task_completed = get_post_meta($task_id, 'pn_tasks_manager_task_completed', true);
      $pn_tasks_manager_task_periodicity_value = get_post_meta($task_id, 'pn_tasks_manager_task_periodicity_value', true);
      $pn_tasks_manager_task_periodicity_type = get_post_meta($task_id, 'pn_tasks_manager_task_periodicity_type', true);
      $pn_tasks_manager_task_repeat_until = get_post_meta($task_id, 'pn_tasks_manager_task_repeat_until', true);
      
      if (empty($task_date) || $task_completed !== 'on') {
        continue;
      }
      
      // Build periodicity string
      $periodicity = '';
      if (!empty($pn_tasks_manager_task_periodicity_value) && !empty($pn_tasks_manager_task_periodicity_type)) {
        switch ($pn_tasks_manager_task_periodicity_type) {
          case 'days':
            $periodicity = $pn_tasks_manager_task_periodicity_value . ' days';
            break;
          case 'weeks':
            $periodicity = ($pn_tasks_manager_task_periodicity_value * 7) . ' days';
            break;
          case 'months':
            $periodicity = $pn_tasks_manager_task_periodicity_value . ' months';
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
      if (!empty($pn_tasks_manager_task_repeat_until)) {
        $repeat_until_timestamp = strtotime($pn_tasks_manager_task_repeat_until . ' 23:59:59');
        if ($next_timestamp > $repeat_until_timestamp) {
          continue; // Task should no longer repeat
        }
      }
      
      // If current time has passed the next occurrence, reset the task
      if ($current_time >= $next_timestamp) {
        // Mark task as incomplete
        update_post_meta($task_id, 'pn_tasks_manager_task_completed', '');
        delete_post_meta($task_id, 'pn_tasks_manager_task_completed_at');
        delete_post_meta($task_id, 'pn_tasks_manager_task_completed_by');
        
        // Update task date to next occurrence
        $next_date = gmdate('Y-m-d', $next_timestamp);
        update_post_meta($task_id, 'pn_tasks_manager_task_date', $next_date);
        
        // Optionally clear comments for the new occurrence (uncomment if needed)
        // delete_post_meta($task_id, 'pn_tasks_manager_task_comments');
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
  public function pn_tasks_manager_task_generate_repeated($task_id) {
    // Check if this task should be repeated
    $pn_tasks_manager_task_repeat = get_post_meta($task_id, 'pn_tasks_manager_task_repeat', true);
    
    if ($pn_tasks_manager_task_repeat !== 'on') {
      return;
    }

    // Get task data
    $task = get_post($task_id);
    $pn_tasks_manager_task_periodicity_value = get_post_meta($task_id, 'pn_tasks_manager_task_periodicity_value', true);
    $pn_tasks_manager_task_periodicity_type = get_post_meta($task_id, 'pn_tasks_manager_task_periodicity_type', true);
    $pn_tasks_manager_task_date = get_post_meta($task_id, 'pn_tasks_manager_task_date', true);
    $pn_tasks_manager_task_time = get_post_meta($task_id, 'pn_tasks_manager_task_time', true);
    $pn_tasks_manager_task_repeat_until = get_post_meta($task_id, 'pn_tasks_manager_task_repeat_until', true);

    // Build periodicity string from value and type
    $pn_tasks_manager_task_periodicity = '';
    if (!empty($pn_tasks_manager_task_periodicity_value) && !empty($pn_tasks_manager_task_periodicity_type)) {
      // Convert to days for strtotime calculation
      switch ($pn_tasks_manager_task_periodicity_type) {
        case 'days':
          $pn_tasks_manager_task_periodicity = $pn_tasks_manager_task_periodicity_value . ' days';
          break;
        case 'weeks':
          $pn_tasks_manager_task_periodicity = ($pn_tasks_manager_task_periodicity_value * 7) . ' days';
          break;
        case 'months':
          $pn_tasks_manager_task_periodicity = $pn_tasks_manager_task_periodicity_value . ' months';
          break;
      }
    }

    // If no periodicity or date configured, exit
    if (empty($pn_tasks_manager_task_periodicity) || empty($pn_tasks_manager_task_date)) {
      return;
    }

    // If repeat until date is set, use it; otherwise repeat for 1 year
    if (!empty($pn_tasks_manager_task_repeat_until)) {
      $end_timestamp = strtotime($pn_tasks_manager_task_repeat_until);
    } else {
      $end_timestamp = strtotime('+1 year', strtotime($pn_tasks_manager_task_date));
    }

    $current_timestamp = strtotime($pn_tasks_manager_task_date . ' ' . $pn_tasks_manager_task_time);
    
    // Get all existing repeated instances to avoid duplicates
    $existing_repeated = get_posts([
      'fields' => 'ids',
      'post_type' => 'pn_tasks_task',
      'post_status' => 'any',
      'meta_query' => [
        [
          'key' => 'pn_tasks_manager_repeated_from',
          'value' => $task_id,
          'compare' => '='
        ]
      ]
    ]);

    // Calculate next occurrence
    $next_timestamp = strtotime('+' . $pn_tasks_manager_task_periodicity, $current_timestamp);
    
    // Generate tasks until the end date
    $max_instances = 100; // Safety limit to prevent infinite loops
    $instance_count = 0;
    
    while ($next_timestamp <= $end_timestamp && $instance_count < $max_instances) {
      // Check if this instance already exists
      $instance_exists = false;
      foreach ($existing_repeated as $existing_id) {
        $existing_date = get_post_meta($existing_id, 'pn_tasks_manager_task_date', true);
        $existing_time = get_post_meta($existing_id, 'pn_tasks_manager_task_time', true);
        $existing_timestamp = strtotime($existing_date . ' ' . $existing_time);
        
        if ($existing_timestamp == $next_timestamp) {
          $instance_exists = true;
          break;
        }
      }

      // If instance doesn't exist, create it
      if (!$instance_exists) {
        $next_date = gmdate('Y-m-d', $next_timestamp);
        $next_time = !empty($pn_tasks_manager_task_time) ? $pn_tasks_manager_task_time : '00:00';
        
        // Prepare meta data for the new instance
        $all_meta = get_post_meta($task_id);
        $new_meta = [];
        
        // Get all task-specific meta
        foreach ($all_meta as $key => $value) {
          if (strpos((string)$key, 'pn_tasks_manager_') !== false) {
            // Skip the repeat and periodicity fields for the new instance
            if ($key !== 'pn_tasks_manager_task_repeat' && 
                $key !== 'pn_tasks_manager_task_periodicity' && 
                $key !== 'pn_tasks_manager_task_periodicity_value' && 
                $key !== 'pn_tasks_manager_task_periodicity_type' && 
                $key !== 'pn_tasks_manager_task_repeat_until') {
              $new_meta[$key] = maybe_unserialize($value[0]);
              
              // Update date and time for this instance
              if ($key === 'pn_tasks_manager_task_date') {
                $new_meta[$key] = $next_date;
              } elseif ($key === 'pn_tasks_manager_task_time') {
                $new_meta[$key] = $next_time;
              }
            }
          }
        }
        
        // Add metadata to link this instance to the original task
        $new_meta['pn_tasks_manager_repeated_from'] = $task_id;
        
        // Create the new task instance
        $post_functions = new PN_TASKS_MANAGER_Functions_Post();
        $title_with_date = $task->post_title . ' - ' . date_i18n(get_option('date_format'), $next_timestamp);
        
        // Use unique slug for each repeated instance to avoid overwriting
        $unique_slug = sanitize_title($title_with_date) . '-' . $next_timestamp;
        
        $new_task_id = $post_functions->pn_tasks_manager_insert_post(
          $title_with_date,
          $task->post_content,
          $task->post_excerpt,
          $unique_slug,
          'pn_tasks_task',
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
      $next_timestamp = strtotime('+' . $pn_tasks_manager_task_periodicity, $next_timestamp);
    }
  }

  /**
   * Get category icon and color for a task
   * 
   * @param int $task_id Task ID
   * @return array Array with 'icon' and 'color' keys
   */
  public static function pn_tasks_manager_get_task_category_style($task_id) {
    $taxonomy = 'pn_tasks_manager_task_category';
    $terms = wp_get_post_terms($task_id, $taxonomy, ['fields' => 'ids']);
    
    $icon = 'event'; // Default icon
    $color = get_option('pn_tasks_manager_color_main') ?: '#b84a00'; // Default color
    
    if (!empty($terms) && !is_wp_error($terms)) {
      $term_id = $terms[0]; // Get first term
      
      $category_icon = get_term_meta($term_id, 'pn_tasks_manager_category_icon', true);
      $category_color = get_term_meta($term_id, 'pn_tasks_manager_category_color', true);
      
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