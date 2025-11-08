<?php
/**
 * Calendar functionality.
 *
 * This class defines calendar views and functionality for tasks.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    TASKSPN
 * @subpackage TASKSPN/includes
 * @author     Padres en la Nube
 */
class TASKSPN_Calendar {
  /**
   * Get tasks for a specific date range
   * 
   * @param string $start_date Start date in Y-m-d format
   * @param string $end_date End date in Y-m-d format
   * @return array Array of task posts
   */
  public function taskspn_get_tasks_for_range($start_date, $end_date) {
    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date . ' 23:59:59');
    
    $current_user_id = get_current_user_id();
    
    // Get all original tasks (excluding repeated instances that may exist from old system)
    $all_tasks = get_posts([
      'fields' => 'ids',
      'numberposts' => -1,
      'post_type' => 'taskspn_task',
      'post_status' => 'publish',
      'meta_query' => [
        [
          'key' => 'taskspn_repeated_from',
          'compare' => 'NOT EXISTS'
        ]
      ]
    ]);
    
    // Filter tasks to show those assigned to the current user OR public tasks
    $assigned_tasks = [];
    
    foreach ($all_tasks as $task_id) {
      $task = get_post($task_id);
      if (!$task) {
        continue;
      }
      
      // Check if task is public
      $task_public = get_post_meta($task_id, 'taskspn_task_public', true);
      $is_public = ($task_public === 'on');
      
      // If task is public, include it for everyone
      if ($is_public) {
        $assigned_tasks[] = $task_id;
        continue;
      }
      
      // For logged-in users, check if task is assigned to them
      if ($current_user_id > 0) {
        $task_owners = [];
        
        // Get assigned owners
        if (class_exists('TASKSPN_Post_Type_Task')) {
          $post_type_task = new TASKSPN_Post_Type_Task();
          $task_owners = $post_type_task->taskspn_task_owners($task_id);
        } else {
          // Fallback: check meta directly
          $task_owners = [];
          $task_author = $task->post_author;
          if ($task_author) {
            $task_owners[] = $task_author;
          }
          $owners_meta = get_post_meta($task_id, 'taskspn_task_owners', true);
          if (!empty($owners_meta)) {
            if (is_array($owners_meta)) {
              foreach ($owners_meta as $owner_id) {
                $task_owners[] = intval($owner_id);
              }
            } else {
              $task_owners[] = intval($owners_meta);
            }
          }
          $task_owners = array_unique($task_owners);
        }
        
        // Include task if current user is assigned
        if (in_array($current_user_id, $task_owners)) {
          $assigned_tasks[] = $task_id;
        }
      } else {
        // For non-logged users, only show public tasks (already handled above)
        // Also check permissions for non-public tasks
        if (class_exists('TASKSPN_Functions_User')) {
          $filtered_tasks = TASKSPN_Functions_User::taskspn_filter_user_posts([$task_id], 'taskspn_task');
          if (!empty($filtered_tasks)) {
            $assigned_tasks[] = $task_id;
          }
        }
      }
    }
    
    // Remove duplicates
    $assigned_tasks = array_unique($assigned_tasks);
    
    // Organize tasks by date (including calculated repetitions)
    $tasks_by_date = [];
    
    foreach ($assigned_tasks as $task_id) {
      $task = get_post($task_id);
      if (!$task) {
        continue;
      }
      
      $task_date = get_post_meta($task_id, 'taskspn_task_date', true);
      $task_time = get_post_meta($task_id, 'taskspn_task_time', true);
      $task_repeat = get_post_meta($task_id, 'taskspn_task_repeat', true);
      
      // Add the original task date occurrence
      if (!empty($task_date)) {
        $task_date_formatted = gmdate('Y-m-d', strtotime($task_date));
        
        // Check if this date is within the range
        if ($task_date_formatted >= $start_date && $task_date_formatted <= $end_date) {
          if (!isset($tasks_by_date[$task_date_formatted])) {
            $tasks_by_date[$task_date_formatted] = [];
          }
          
          // Get icon and color from category, fallback to task meta
          $category_style = TASKSPN_Post_Type_Task::taskspn_get_task_category_style($task_id);
          $task_icon = !empty($category_style['icon']) ? $category_style['icon'] : get_post_meta($task_id, 'taskspn_task_icon', true);
          $task_color = !empty($category_style['color']) ? $category_style['color'] : get_post_meta($task_id, 'taskspn_task_color', true);
          if (empty($task_color)) {
            $task_color = get_option('taskspn_color_main') ?: '#d45500'; // Default color from settings
          }
          if (empty($task_icon)) {
            $task_icon = 'event'; // Default icon
          }
          
          $tasks_by_date[$task_date_formatted][] = [
            'id' => $task_id,
            'title' => get_the_title($task_id),
            'time' => !empty($task_time) ? $task_time : '',
            'timestamp' => strtotime($task_date . ' ' . $task_time),
            'icon' => $task_icon,
            'color' => $task_color
          ];
        }
      }
      
      // Calculate repeated instances if task should be repeated
      if ($task_repeat === 'on' && !empty($task_date)) {
        $periodicity_value = get_post_meta($task_id, 'taskspn_task_periodicity_value', true);
        $periodicity_type = get_post_meta($task_id, 'taskspn_task_periodicity_type', true);
        $repeat_until = get_post_meta($task_id, 'taskspn_task_repeat_until', true);
        
        // Build periodicity string
        $periodicity = '';
        if (!empty($periodicity_value) && !empty($periodicity_type)) {
          switch ($periodicity_type) {
            case 'days':
              $periodicity = $periodicity_value . ' days';
              break;
            case 'weeks':
              $periodicity = ($periodicity_value * 7) . ' days';
              break;
            case 'months':
              $periodicity = $periodicity_value . ' months';
              break;
          }
        }
        
        if (!empty($periodicity)) {
          // Determine end date for repetitions
          if (!empty($repeat_until)) {
            $end_repeat_timestamp = strtotime($repeat_until);
          } else {
            $end_repeat_timestamp = strtotime('+1 year', strtotime($task_date));
          }
          
          // Start from the original date
          $current_timestamp = strtotime($task_date . ' ' . (!empty($task_time) ? $task_time : '00:00'));
          
          // Calculate next occurrence (skip the original date as we already added it)
          $next_timestamp = strtotime('+' . $periodicity, $current_timestamp);
          
          // Generate virtual instances within the date range
          $max_instances = 100; // Safety limit
          $instance_count = 0;
          
          while ($next_timestamp <= $end_repeat_timestamp && $next_timestamp <= $end_timestamp && $instance_count < $max_instances) {
            $next_date_formatted = gmdate('Y-m-d', $next_timestamp);
            
            // Only add if within the requested range
            if ($next_date_formatted >= $start_date && $next_date_formatted <= $end_date) {
              if (!isset($tasks_by_date[$next_date_formatted])) {
                $tasks_by_date[$next_date_formatted] = [];
              }
              
              // Use original task ID for repeated instances (virtual)
              // Get icon and color from category, fallback to task meta
              $category_style = TASKSPN_Post_Type_Task::taskspn_get_task_category_style($task_id);
              $task_icon = !empty($category_style['icon']) ? $category_style['icon'] : get_post_meta($task_id, 'taskspn_task_icon', true);
              $task_color = !empty($category_style['color']) ? $category_style['color'] : get_post_meta($task_id, 'taskspn_task_color', true);
              if (empty($task_color)) {
                $task_color = get_option('taskspn_color_main') ?: '#d45500'; // Default color from settings
              }
              if (empty($task_icon)) {
                $task_icon = 'event'; // Default icon
              }
              
              $tasks_by_date[$next_date_formatted][] = [
                'id' => $task_id, // Original task ID
                'title' => get_the_title($task_id),
                'time' => !empty($task_time) ? $task_time : '',
                'timestamp' => $next_timestamp,
                'is_repeated' => true, // Flag to identify repeated instances
                'icon' => $task_icon,
                'color' => $task_color
              ];
            }
            
            // Move to next occurrence
            $next_timestamp = strtotime('+' . $periodicity, $next_timestamp);
            $instance_count++;
          }
        }
      }
    }
    
    // Sort tasks by time within each date
    foreach ($tasks_by_date as $date => $tasks_list) {
      usort($tasks_by_date[$date], function($a, $b) {
        return $a['timestamp'] - $b['timestamp'];
      });
    }
    
    return $tasks_by_date;
  }

  /**
   * Render calendar view content only (for AJAX)
   * 
   * @param string $view View type (day, week, month, year)
   * @param int $year Year
   * @param int $month Month
   * @param int $day Day
   * @return string Calendar content HTML
   */
  public function taskspn_calendar_render_view_content($view, $year, $month, $day) {
    $current_year = intval($year);
    $current_month = intval($month);
    $current_day = intval($day);
    $current_view = sanitize_text_field($view);
    
    ob_start();
    switch ($current_view) {
      case 'month':
        echo wp_kses_post( $this->taskspn_calendar_render_month($current_year, $current_month) );
        break;
      case 'week':
        echo wp_kses_post( $this->taskspn_calendar_render_week($current_year, $current_month, $current_day) );
        break;
      case 'day':
        echo wp_kses_post( $this->taskspn_calendar_render_day($current_year, $current_month, $current_day) );
        break;
      case 'year':
        echo wp_kses_post( $this->taskspn_calendar_render_year($current_year) );
        break;
      default:
        echo wp_kses_post( $this->taskspn_calendar_render_month($current_year, $current_month) );
        break;
    }
    return ob_get_clean();
  }

  /**
   * Render calendar view
   * 
   * @param array $atts Shortcode attributes
   * @return string Calendar HTML
   */
  public function taskspn_calendar_render($atts = []) {
    $a = shortcode_atts([
      'view' => 'month', // month, week, day, year
      'year' => gmdate('Y'),
      'month' => gmdate('m'),
      'day' => gmdate('d'),
    ], $atts);
    
    $current_year = isset($_GET['calendar_year']) ? intval(wp_unslash($_GET['calendar_year'])) : intval($a['year']);
    $current_month = isset($_GET['calendar_month']) ? intval(wp_unslash($_GET['calendar_month'])) : intval($a['month']);
    $current_day = isset($_GET['calendar_day']) ? intval(wp_unslash($_GET['calendar_day'])) : intval($a['day']);
    $current_view = isset($_GET['calendar_view']) ? sanitize_text_field(wp_unslash($_GET['calendar_view'])) : $a['view'];
    
    // Register and enqueue scripts and styles
    wp_enqueue_style('taskspn-calendar', TASKSPN_URL . 'assets/css/taskspn-calendar.css', [], TASKSPN_VERSION);
    wp_enqueue_script('taskspn-calendar', TASKSPN_URL . 'assets/js/taskspn-calendar.js', ['jquery'], TASKSPN_VERSION, true);
    
    // Localize script with AJAX variables (use same nonce as taskspn_ajax for compatibility)
    wp_localize_script('taskspn-calendar', 'taskspn_calendar_vars', [
      'ajax_url' => admin_url('admin-ajax.php'),
      'ajax_nonce' => wp_create_nonce('taskspn-nonce'),
    ]);
    
    // Also make taskspn_ajax available if not already localized
    if (!wp_script_is('taskspn-ajax', 'enqueued')) {
      wp_localize_script('taskspn-calendar', 'taskspn_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'taskspn_ajax_nonce' => wp_create_nonce('taskspn-nonce'),
      ]);
    }
    
    ob_start();
    ?>
    <div class="taskspn-calendar-wrapper" data-calendar-view="<?php echo esc_attr($current_view); ?>" data-calendar-year="<?php echo esc_attr($current_year); ?>" data-calendar-month="<?php echo esc_attr($current_month); ?>" data-calendar-day="<?php echo esc_attr($current_day); ?>">
      <div class="taskspn-calendar-header">
        <div class="taskspn-calendar-view-selector">
          <button class="taskspn-calendar-view-btn <?php echo $current_view === 'day' ? 'active' : ''; ?>" data-view="day">
            <?php esc_html_e('Day', 'taskspn'); ?>
          </button>
          <button class="taskspn-calendar-view-btn <?php echo $current_view === 'week' ? 'active' : ''; ?>" data-view="week">
            <?php esc_html_e('Week', 'taskspn'); ?>
          </button>
          <button class="taskspn-calendar-view-btn <?php echo $current_view === 'month' ? 'active' : ''; ?>" data-view="month">
            <?php esc_html_e('Month', 'taskspn'); ?>
          </button>
          <button class="taskspn-calendar-view-btn <?php echo $current_view === 'year' ? 'active' : ''; ?>" data-view="year">
            <?php esc_html_e('Year', 'taskspn'); ?>
          </button>
        </div>
      </div>
      
      <div class="taskspn-calendar-loader-wrapper">
        <?php TASKSPN_Data::taskspn_loader(false); ?>
      </div>
      
      <div class="taskspn-calendar-content">
        <?php
        switch ($current_view) {
          case 'month':
            echo wp_kses_post( $this->taskspn_calendar_render_month($current_year, $current_month) );
            break;
          case 'week':
            echo wp_kses_post( $this->taskspn_calendar_render_week($current_year, $current_month, $current_day) );
            break;
          case 'day':
            echo wp_kses_post( $this->taskspn_calendar_render_day($current_year, $current_month, $current_day) );
            break;
          case 'year':
            echo wp_kses_post( $this->taskspn_calendar_render_year($current_year) );
            break;
        }
        ?>
      </div>
      
      <?php
      // Find page with taskspn-task-list shortcode
      $task_list_page_url = '';
      $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'publish',
        'numberposts' => -1,
        's' => '[taskspn-task-list',
        'fields' => 'ids',
      ]);
      
      if (!empty($pages)) {
        foreach ($pages as $page_id) {
          $content = get_post_field('post_content', $page_id);
          if ($content && has_shortcode($content, 'taskspn-task-list')) {
            $task_list_page_url = get_permalink($page_id);
            break;
          }
        }
      }
      
      // If no page found, use admin URL for adding new task (if user has permission)
      if (empty($task_list_page_url) && is_user_logged_in() && (current_user_can('administrator') || current_user_can('taskspn_role_manager'))) {
        $task_list_page_url = admin_url('post-new.php?post_type=taskspn_task');
      }
      ?>
      
      <div class="taskspn-calendar-footer taskspn-text-align-center taskspn-mt-30">
        <div class="taskspn-calendar-footer-buttons">
          <?php if (!empty($task_list_page_url) || is_user_logged_in()): ?>
            <?php if (!empty($task_list_page_url)): ?>
              <a href="<?php echo esc_url($task_list_page_url); ?>" class="taskspn-btn taskspn-btn-primary taskspn-mr-10">
                <i class="material-icons-outlined taskspn-vertical-align-middle taskspn-mr-10">add</i>
                <?php esc_html_e('Add new Task', 'taskspn'); ?>
              </a>
            <?php elseif (is_user_logged_in()): ?>
              <a href="#" class="taskspn-btn taskspn-btn-primary taskspn-popup-open-ajax taskspn-mr-10" data-taskspn-popup-id="taskspn-popup-taskspn_task-add" data-taskspn-ajax-type="taskspn_task_new">
                <i class="material-icons-outlined taskspn-vertical-align-middle taskspn-mr-10">add</i>
                <?php esc_html_e('Add new Task', 'taskspn'); ?>
              </a>
            <?php endif; ?>
          <?php endif; ?>
          
          <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=taskspn_download_ics&nonce=' . wp_create_nonce('taskspn-download-ics'))); ?>" class="taskspn-btn taskspn-btn-secondary taskspn-download-ics-btn">
            <i class="material-icons-outlined taskspn-vertical-align-middle taskspn-mr-10">download</i>
            <?php esc_html_e('Download ICS', 'taskspn'); ?>
          </a>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

  /**
   * Render month view
   */
  private function taskspn_calendar_render_month($year, $month) {
    $first_day = mktime(0, 0, 0, $month, 1, $year);
    $month_name = date_i18n('F Y', $first_day);
    $days_in_month = gmdate('t', $first_day);
    $first_day_of_week = gmdate('w', $first_day); // 0 = Sunday, 6 = Saturday
    
    // Adjust for Monday as first day (0 = Monday, 6 = Sunday)
    $first_day_of_week = ($first_day_of_week == 0) ? 6 : ($first_day_of_week - 1);
    
    // Get start and end dates for fetching tasks
    $start_date = gmdate('Y-m-01', $first_day);
    $end_date = gmdate('Y-m-' . $days_in_month, $first_day);
    $tasks_by_date = $this->taskspn_get_tasks_for_range($start_date, $end_date);
    
    ob_start();
    ?>
    <div class="taskspn-calendar-month">
      <div class="taskspn-calendar-month-header">
        <button class="taskspn-calendar-nav-btn taskspn-calendar-prev" data-action="prev-month">
          <i class="material-icons-outlined">chevron_left</i>
        </button>
        <h3 class="taskspn-calendar-month-title"><?php echo esc_html($month_name); ?></h3>
        <button class="taskspn-calendar-nav-btn taskspn-calendar-next" data-action="next-month">
          <i class="material-icons-outlined">chevron_right</i>
        </button>
      </div>
      
      <div class="taskspn-calendar-month-grid">
        <div class="taskspn-calendar-weekdays">
          <?php
          $weekdays = [
            __('Monday', 'taskspn'),
            __('Tuesday', 'taskspn'),
            __('Wednesday', 'taskspn'),
            __('Thursday', 'taskspn'),
            __('Friday', 'taskspn'),
            __('Saturday', 'taskspn'),
            __('Sunday', 'taskspn')
          ];
          $weekdays_short = [
            __('Mon', 'taskspn'),
            __('Tue', 'taskspn'),
            __('Wed', 'taskspn'),
            __('Thu', 'taskspn'),
            __('Fri', 'taskspn'),
            __('Sat', 'taskspn'),
            __('Sun', 'taskspn')
          ];
          foreach ($weekdays as $index => $weekday) {
            echo '<div class="taskspn-calendar-weekday" data-short="' . esc_attr($weekdays_short[$index]) . '">' . esc_html($weekday) . '</div>';
          }
          ?>
        </div>
        
        <div class="taskspn-calendar-days">
          <?php
          // Empty cells for days before month starts
          for ($i = 0; $i < $first_day_of_week; $i++) {
            echo '<div class="taskspn-calendar-day taskspn-calendar-day-empty"></div>';
          }
          
          // Days of the month
          for ($day = 1; $day <= $days_in_month; $day++) {
            $day_date = gmdate('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            $is_today = ($day_date === gmdate('Y-m-d'));
            $day_tasks = isset($tasks_by_date[$day_date]) ? $tasks_by_date[$day_date] : [];
            
            $day_date_parts = explode('-', $day_date);
            $day_year = intval($day_date_parts[0]);
            $day_month = intval($day_date_parts[1]);
            $day_day = intval($day_date_parts[2]);
            
            echo '<div class="taskspn-calendar-day ' . ($is_today ? 'taskspn-calendar-day-today' : '') . '">';
            echo '<div class="taskspn-calendar-day-number taskspn-calendar-day-number-clickable" data-calendar-year="' . esc_attr($day_year) . '" data-calendar-month="' . esc_attr($day_month) . '" data-calendar-day="' . esc_attr($day_day) . '" title="' . esc_attr__('Click to view day', 'taskspn') . '">' . esc_html($day) . '</div>';
            
            if (!empty($day_tasks)) {
              $tasks_count = count($day_tasks);
              
              echo '<div class="taskspn-calendar-day-tasks">';
              foreach ($day_tasks as $task) {
                $task_icon = isset($task['icon']) ? $task['icon'] : 'event';
                $task_color = isset($task['color']) ? $task['color'] : (get_option('taskspn_color_main') ?: '#d45500');
                $task_title = esc_html($task['title']);
                $task_time = !empty($task['time']) ? esc_html($task['time']) : '';
                $tooltip_text = $task_title;
                if (!empty($task_time)) {
                  $tooltip_text = $task_time . ' - ' . $tooltip_text;
                }
                
                echo '<div class="taskspn-calendar-task-icon taskspn-tooltip" data-task-id="' . esc_attr($task['id']) . '" title="' . esc_attr($tooltip_text) . '" style="color: ' . esc_attr($task_color) . ';">';
                echo '<i class="material-icons-outlined">' . esc_html($task_icon) . '</i>';
                echo '</div>';
              }
              echo '</div>';
            }
            
            echo '</div>';
          }
          
          // Fill remaining cells to complete the week
          $total_cells = $first_day_of_week + $days_in_month;
          $remaining_cells = 7 - ($total_cells % 7);
          if ($remaining_cells < 7) {
            for ($i = 0; $i < $remaining_cells; $i++) {
              echo '<div class="taskspn-calendar-day taskspn-calendar-day-empty"></div>';
            }
          }
          ?>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

  /**
   * Render week view
   */
  private function taskspn_calendar_render_week($year, $month, $day) {
    $current_date = mktime(0, 0, 0, $month, $day, $year);
    $current_day_of_week = gmdate('w', $current_date);
    $current_day_of_week = ($current_day_of_week == 0) ? 6 : ($current_day_of_week - 1); // Convert to Monday = 0
    
    // Calculate Monday of current week
    $monday_timestamp = $current_date - ($current_day_of_week * 86400);
    $monday_date = gmdate('Y-m-d', $monday_timestamp);
    $sunday_timestamp = $monday_timestamp + (6 * 86400);
    $sunday_date = gmdate('Y-m-d', $sunday_timestamp);
    
    $tasks_by_date = $this->taskspn_get_tasks_for_range($monday_date, $sunday_date);
    
    ob_start();
    ?>
    <div class="taskspn-calendar-week">
      <div class="taskspn-calendar-week-header">
        <button class="taskspn-calendar-nav-btn taskspn-calendar-prev" data-action="prev-week">
          <i class="material-icons-outlined">chevron_left</i>
        </button>
        <h3 class="taskspn-calendar-week-title">
          <?php echo esc_html(date_i18n(get_option('date_format'), $monday_timestamp)); ?> - <?php echo esc_html(date_i18n(get_option('date_format'), $sunday_timestamp)); ?>
        </h3>
        <button class="taskspn-calendar-nav-btn taskspn-calendar-next" data-action="next-week">
          <i class="material-icons-outlined">chevron_right</i>
        </button>
      </div>
      
      <div class="taskspn-calendar-week-grid">
        <?php
        for ($i = 0; $i < 7; $i++) {
          $day_timestamp = $monday_timestamp + ($i * 86400);
          $day_date = gmdate('Y-m-d', $day_timestamp);
          $day_name = date_i18n('l', $day_timestamp);
          $day_number = gmdate('j', $day_timestamp);
          $is_today = ($day_date === gmdate('Y-m-d'));
          $day_tasks = isset($tasks_by_date[$day_date]) ? $tasks_by_date[$day_date] : [];
          
          echo '<div class="taskspn-calendar-week-day ' . ($is_today ? 'taskspn-calendar-day-today' : '') . '">';
          echo '<div class="taskspn-calendar-week-day-header">';
          echo '<div class="taskspn-calendar-week-day-name">' . esc_html($day_name) . '</div>';
          echo '<div class="taskspn-calendar-week-day-number">' . esc_html($day_number) . '</div>';
          echo '</div>';
          
          echo '<div class="taskspn-calendar-week-day-tasks">';
          if (!empty($day_tasks)) {
            foreach ($day_tasks as $task) {
              $task_icon = isset($task['icon']) ? $task['icon'] : 'event';
              $task_color = isset($task['color']) ? $task['color'] : (get_option('taskspn_color_main') ?: '#d45500');
              echo '<div class="taskspn-calendar-task-item taskspn-calendar-task-item-week" data-task-id="' . esc_attr($task['id']) . '">';
              echo '<span class="taskspn-calendar-task-icon-week" style="color: ' . esc_attr($task_color) . ';">';
              echo '<i class="material-icons-outlined">' . esc_html($task_icon) . '</i>';
              echo '</span>';
              if (!empty($task['time'])) {
                echo '<span class="taskspn-calendar-task-time">' . esc_html($task['time']) . '</span>';
              }
              echo '<span class="taskspn-calendar-task-title">' . esc_html($task['title']) . '</span>';
              echo '</div>';
            }
          }
          echo '</div>';
          
          echo '</div>';
        }
        ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

  /**
   * Render day view
   */
  private function taskspn_calendar_render_day($year, $month, $day) {
    $current_date = mktime(0, 0, 0, $month, $day, $year);
    $date_string = gmdate('Y-m-d', $current_date);
    $day_name = date_i18n('l, F j, Y', $current_date);
    $is_today = ($date_string === gmdate('Y-m-d'));
    
    $tasks_by_date = $this->taskspn_get_tasks_for_range($date_string, $date_string);
    $day_tasks = isset($tasks_by_date[$date_string]) ? $tasks_by_date[$date_string] : [];
    
    ob_start();
    ?>
    <div class="taskspn-calendar-day-view">
      <div class="taskspn-calendar-day-header">
        <button class="taskspn-calendar-nav-btn taskspn-calendar-prev" data-action="prev-day">
          <i class="material-icons-outlined">chevron_left</i>
        </button>
        <h3 class="taskspn-calendar-day-title"><?php echo esc_html($day_name); ?></h3>
        <button class="taskspn-calendar-nav-btn taskspn-calendar-next" data-action="next-day">
          <i class="material-icons-outlined">chevron_right</i>
        </button>
      </div>
      
      <div class="taskspn-calendar-day-content">
        <?php if (!empty($day_tasks)): ?>
          <div class="taskspn-calendar-day-tasks-list">
            <?php foreach ($day_tasks as $task): 
              $task_icon = isset($task['icon']) ? $task['icon'] : 'event';
              $task_color = isset($task['color']) ? $task['color'] : (get_option('taskspn_color_main') ?: '#d45500');
            ?>
              <div class="taskspn-calendar-task-item taskspn-calendar-task-item-day" data-task-id="<?php echo esc_attr($task['id']); ?>">
                <div class="taskspn-calendar-task-icon-day" style="color: <?php echo esc_attr($task_color); ?>;">
                  <i class="material-icons-outlined"><?php echo esc_html($task_icon); ?></i>
                </div>
                <div class="taskspn-calendar-task-time-day">
                  <?php if (!empty($task['time'])): ?>
                    <?php echo esc_html($task['time']); ?>
                  <?php else: ?>
                    <?php esc_html_e('All day', 'taskspn'); ?>
                  <?php endif; ?>
                </div>
                <div class="taskspn-calendar-task-content-day">
                  <h4 class="taskspn-calendar-task-title-day"><?php echo esc_html($task['title']); ?></h4>
                  <?php
                  $taskspn_the_content_hook = 'the_content';
                  $task_content = get_post($task['id'])->post_content;
                  if (!empty($task_content)) {
                    echo '<div class="taskspn-calendar-task-description">' . wp_kses_post(apply_filters($taskspn_the_content_hook, $task_content)) . '</div>';
                  }
                  ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="taskspn-calendar-day-empty">
            <p><?php esc_html_e('No tasks scheduled for this day.', 'taskspn'); ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

  /**
   * Render year view
   */
  private function taskspn_calendar_render_year($year) {
    $start_date = $year . '-01-01';
    $end_date = $year . '-12-31';
    $tasks_by_date = $this->taskspn_get_tasks_for_range($start_date, $end_date);
    
    ob_start();
    ?>
    <div class="taskspn-calendar-year">
      <div class="taskspn-calendar-year-header">
        <button class="taskspn-calendar-nav-btn taskspn-calendar-prev" data-action="prev-year">
          <i class="material-icons-outlined">chevron_left</i>
        </button>
        <h3 class="taskspn-calendar-year-title"><?php echo esc_html($year); ?></h3>
        <button class="taskspn-calendar-nav-btn taskspn-calendar-next" data-action="next-year">
          <i class="material-icons-outlined">chevron_right</i>
        </button>
      </div>
      
      <div class="taskspn-calendar-year-grid">
        <?php
        for ($month = 1; $month <= 12; $month++) {
          $month_timestamp = mktime(0, 0, 0, $month, 1, $year);
          $month_name = date_i18n('F', $month_timestamp);
          $days_in_month = gmdate('t', $month_timestamp);
          $first_day = gmdate('w', $month_timestamp);
          $first_day = ($first_day == 0) ? 6 : ($first_day - 1);
          
          // Count tasks for this month
          $month_tasks_count = 0;
          $month_start = gmdate('Y-m-01', $month_timestamp);
          $month_end = gmdate('Y-m-' . $days_in_month, $month_timestamp);
          
          foreach ($tasks_by_date as $task_date => $tasks) {
            if ($task_date >= $month_start && $task_date <= $month_end) {
              $month_tasks_count += count($tasks);
            }
          }
          
          echo '<div class="taskspn-calendar-year-month">';
          echo '<div class="taskspn-calendar-year-month-header">';
          echo '<h4 class="taskspn-calendar-year-month-title taskspn-text-transform-capitalize taskspn-calendar-year-month-title-clickable" data-calendar-year="' . esc_attr($year) . '" data-calendar-month="' . esc_attr($month) . '" style="cursor: pointer;" title="' . esc_attr__('Click to view month', 'taskspn') . '">' . esc_html($month_name) . '</h4>';
          if ($month_tasks_count > 0) {
            echo '<span class="taskspn-calendar-year-month-tasks-count">' . esc_html($month_tasks_count) . '</span>';
          }
          echo '</div>';
          
          echo '<div class="taskspn-calendar-year-month-grid">';
          // Weekday headers
          $weekdays_short = ['M', 'T', 'W', 'T', 'F', 'S', 'S'];
          foreach ($weekdays_short as $wd) {
            echo '<div class="taskspn-calendar-year-weekday">' . esc_html($wd) . '</div>';
          }
          
          // Empty cells
          for ($i = 0; $i < $first_day; $i++) {
            echo '<div class="taskspn-calendar-year-day taskspn-calendar-year-day-empty"></div>';
          }
          
          // Days
          for ($day = 1; $day <= $days_in_month; $day++) {
            $day_date = gmdate('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            $is_today = ($day_date === gmdate('Y-m-d'));
            $day_tasks = isset($tasks_by_date[$day_date]) ? $tasks_by_date[$day_date] : [];
            $has_tasks = !empty($day_tasks);
            
            $day_date_parts = explode('-', $day_date);
            $day_year = intval($day_date_parts[0]);
            $day_month = intval($day_date_parts[1]);
            $day_day = intval($day_date_parts[2]);
            
            echo '<div class="taskspn-calendar-year-day taskspn-calendar-year-day-clickable ' . ($is_today ? 'taskspn-calendar-day-today' : '') . ($has_tasks ? ' taskspn-calendar-year-day-has-tasks' : '') . '" data-calendar-year="' . esc_attr($day_year) . '" data-calendar-month="' . esc_attr($day_month) . '" data-calendar-day="' . esc_attr($day_day) . '" title="' . esc_attr__('Click to view day', 'taskspn') . '">';
            echo '<span class="taskspn-calendar-year-day-number">' . esc_html($day) . '</span>';
            if ($has_tasks) {
              echo '<div class="taskspn-calendar-year-day-icons">';
              foreach ($day_tasks as $task) {
                $task_icon = isset($task['icon']) ? $task['icon'] : 'event';
                $task_color = isset($task['color']) ? $task['color'] : (get_option('taskspn_color_main') ?: '#d45500');
                $task_title = esc_html($task['title']);
                $task_time = !empty($task['time']) ? esc_html($task['time']) : '';
                $tooltip_text = $task_title;
                if (!empty($task_time)) {
                  $tooltip_text = $task_time . ' - ' . $tooltip_text;
                }
                
                echo '<span class="taskspn-calendar-year-task-icon taskspn-tooltip" data-task-id="' . esc_attr($task['id']) . '" title="' . esc_attr($tooltip_text) . '" style="color: ' . esc_attr($task_color) . ';">';
                echo '<i class="material-icons-outlined">' . esc_html($task_icon) . '</i>';
                echo '</span>';
              }
              echo '</div>';
            }
            echo '</div>';
          }
          
          echo '</div>';
          echo '</div>';
        }
        ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

  /**
   * Generate ICS file content from tasks
   * 
   * @param string $start_date Start date in Y-m-d format (optional, defaults to current year start)
   * @param string $end_date End date in Y-m-d format (optional, defaults to current year end)
   * @return string ICS file content
   */
  public function taskspn_generate_ics($start_date = null, $end_date = null) {
    // Set default date range to current year if not provided
    if (empty($start_date)) {
      $start_date = gmdate('Y-01-01');
    }
    if (empty($end_date)) {
      $end_date = gmdate('Y-12-31');
    }

    // Get all tasks for the date range
    $tasks_by_date = $this->taskspn_get_tasks_for_range($start_date, $end_date);
    
    // Get site URL for UID generation
    $site_url = home_url();
    $site_name = get_bloginfo('name');
    
    // Start ICS content
    $ics_content = "BEGIN:VCALENDAR\r\n";
    $ics_content .= "VERSION:2.0\r\n";
    $ics_content .= "PRODID:-//" . esc_html($site_name) . "//TASKSPN//EN\r\n";
    $ics_content .= "CALSCALE:GREGORIAN\r\n";
    $ics_content .= "METHOD:PUBLISH\r\n";
    $ics_content .= "X-WR-CALNAME:" . esc_html($site_name) . " - Tasks\r\n";
    $ics_content .= "X-WR-TIMEZONE:" . wp_timezone_string() . "\r\n";
    
    // Process all tasks
    foreach ($tasks_by_date as $date => $tasks) {
      foreach ($tasks as $task) {
        $task_id = $task['id'];
        $task_post = get_post($task_id);
        
        if (!$task_post) {
          continue;
        }
        
        $task_title = $task['title'];
        $task_description = !empty($task_post->post_content) ? strip_tags($task_post->post_content) : '';
        $task_date = $date;
        $task_time = !empty($task['time']) ? $task['time'] : '00:00';
        
        // Create datetime string
        $datetime = $task_date . ' ' . $task_time;
        $timestamp = strtotime($datetime);
        $dtstart = gmdate('Ymd\THis\Z', $timestamp);
        
        // For all-day events, use DATE format instead of DATE-TIME
        $is_all_day = empty($task['time']) || $task['time'] === '00:00';
        
        if ($is_all_day) {
          $dtstart = gmdate('Ymd', $timestamp);
          $dtend = gmdate('Ymd', strtotime('+1 day', $timestamp));
        } else {
          // End time: default to 1 hour after start, or use estimated hours if available
          $estimated_hours = get_post_meta($task_id, 'taskspn_task_estimated_hours', true);
          $duration_hours = !empty($estimated_hours) ? floatval($estimated_hours) : 1;
          $dtend = gmdate('Ymd\THis\Z', strtotime('+' . $duration_hours . ' hours', $timestamp));
        }
        
        // Generate unique ID
        $uid = md5($task_id . $datetime . $site_url) . '@' . parse_url($site_url, PHP_URL_HOST);
        
        // Get task URL if available
        $task_url = '';
        if (get_post_status($task_id) === 'publish') {
          $task_url = get_permalink($task_id);
        }
        
        // Get category name if available
        $categories = wp_get_post_terms($task_id, 'taskspn_task_category', ['fields' => 'names']);
        $category_name = !empty($categories) ? implode(', ', $categories) : '';
        
        // Escape text for ICS format
        $escape_ics_text = function($text) {
          $text = str_replace('\\', '\\\\', $text);
          $text = str_replace(',', '\\,', $text);
          $text = str_replace(';', '\\;', $text);
          $text = str_replace("\n", '\\n', $text);
          $text = str_replace("\r", '', $text);
          return $text;
        };
        
        // Add event
        $ics_content .= "BEGIN:VEVENT\r\n";
        $ics_content .= "UID:" . $uid . "\r\n";
        $ics_content .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        
        if ($is_all_day) {
          $ics_content .= "DTSTART;VALUE=DATE:" . $dtstart . "\r\n";
          $ics_content .= "DTEND;VALUE=DATE:" . $dtend . "\r\n";
        } else {
          $ics_content .= "DTSTART:" . $dtstart . "\r\n";
          $ics_content .= "DTEND:" . $dtend . "\r\n";
        }
        
        $ics_content .= "SUMMARY:" . $escape_ics_text($task_title) . "\r\n";
        
        if (!empty($task_description)) {
          $ics_content .= "DESCRIPTION:" . $escape_ics_text($task_description) . "\r\n";
        }
        
        if (!empty($task_url)) {
          $ics_content .= "URL:" . $escape_ics_text($task_url) . "\r\n";
        }
        
        if (!empty($category_name)) {
          $ics_content .= "CATEGORIES:" . $escape_ics_text($category_name) . "\r\n";
        }
        
        // Get location if available (could be added as meta field in the future)
        $location = get_post_meta($task_id, 'taskspn_task_location', true);
        if (!empty($location)) {
          $ics_content .= "LOCATION:" . $escape_ics_text($location) . "\r\n";
        }
        
        // Status
        $is_completed = get_post_meta($task_id, 'taskspn_task_completed', true) === 'on';
        if ($is_completed) {
          $ics_content .= "STATUS:COMPLETED\r\n";
        } else {
          $ics_content .= "STATUS:CONFIRMED\r\n";
        }
        
        $ics_content .= "END:VEVENT\r\n";
        
        // Handle repeated tasks - generate instances for the date range
        $task_repeat = get_post_meta($task_id, 'taskspn_task_repeat', true);
        if ($task_repeat === 'on' && !isset($task['is_repeated'])) {
          $periodicity_value = get_post_meta($task_id, 'taskspn_task_periodicity_value', true);
          $periodicity_type = get_post_meta($task_id, 'taskspn_task_periodicity_type', true);
          $repeat_until = get_post_meta($task_id, 'taskspn_task_repeat_until', true);
          
          // Build periodicity string
          $periodicity = '';
          if (!empty($periodicity_value) && !empty($periodicity_type)) {
            switch ($periodicity_type) {
              case 'days':
                $periodicity = $periodicity_value . ' days';
                break;
              case 'weeks':
                $periodicity = ($periodicity_value * 7) . ' days';
                break;
              case 'months':
                $periodicity = $periodicity_value . ' months';
                break;
            }
          }
          
          if (!empty($periodicity)) {
            // Determine end date for repetitions
            if (!empty($repeat_until)) {
              $end_repeat_timestamp = strtotime($repeat_until);
            } else {
              $end_repeat_timestamp = strtotime('+1 year', $timestamp);
            }
            
            // Start from the original date
            $current_timestamp = $timestamp;
            
            // Calculate next occurrence (skip the original date as we already added it)
            $next_timestamp = strtotime('+' . $periodicity, $current_timestamp);
            
            // Generate instances within the date range
            $max_instances = 500; // Safety limit
            $instance_count = 0;
            $end_range_timestamp = strtotime($end_date . ' 23:59:59');
            
            while ($next_timestamp <= $end_repeat_timestamp && $next_timestamp <= $end_range_timestamp && $instance_count < $max_instances) {
              $next_date = gmdate('Y-m-d', $next_timestamp);
              
              // Only add if within the requested range
              if ($next_date >= $start_date && $next_date <= $end_date) {
                $next_datetime = $next_date . ' ' . $task_time;
                $next_dtstart = $is_all_day ? gmdate('Ymd', $next_timestamp) : gmdate('Ymd\THis\Z', $next_timestamp);
                
                if ($is_all_day) {
                  $next_dtend = gmdate('Ymd', strtotime('+1 day', $next_timestamp));
                } else {
                  $next_dtend = gmdate('Ymd\THis\Z', strtotime('+' . $duration_hours . ' hours', $next_timestamp));
                }
                
                // Generate unique ID for this instance
                $instance_uid = md5($task_id . $next_datetime . $site_url) . '@' . parse_url($site_url, PHP_URL_HOST);
                
                // Add repeated event
                $ics_content .= "BEGIN:VEVENT\r\n";
                $ics_content .= "UID:" . $instance_uid . "\r\n";
                $ics_content .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
                
                if ($is_all_day) {
                  $ics_content .= "DTSTART;VALUE=DATE:" . $next_dtstart . "\r\n";
                  $ics_content .= "DTEND;VALUE=DATE:" . $next_dtend . "\r\n";
                } else {
                  $ics_content .= "DTSTART:" . $next_dtstart . "\r\n";
                  $ics_content .= "DTEND:" . $next_dtend . "\r\n";
                }
                
                $ics_content .= "SUMMARY:" . $escape_ics_text($task_title) . "\r\n";
                
                if (!empty($task_description)) {
                  $ics_content .= "DESCRIPTION:" . $escape_ics_text($task_description) . "\r\n";
                }
                
                if (!empty($task_url)) {
                  $ics_content .= "URL:" . $escape_ics_text($task_url) . "\r\n";
                }
                
                if (!empty($category_name)) {
                  $ics_content .= "CATEGORIES:" . $escape_ics_text($category_name) . "\r\n";
                }
                
                if (!empty($location)) {
                  $ics_content .= "LOCATION:" . $escape_ics_text($location) . "\r\n";
                }
                
                if ($is_completed) {
                  $ics_content .= "STATUS:COMPLETED\r\n";
                } else {
                  $ics_content .= "STATUS:CONFIRMED\r\n";
                }
                
                $ics_content .= "END:VEVENT\r\n";
              }
              
              // Move to next occurrence
              $next_timestamp = strtotime('+' . $periodicity, $next_timestamp);
              $instance_count++;
            }
          }
        }
      }
    }
    
    // End ICS content
    $ics_content .= "END:VCALENDAR\r\n";
    
    return $ics_content;
  }

  /**
   * Handler for ICS file download
   * 
   * @since    1.0.0
   * @return   void
   */
  public function taskspn_download_ics_handler() {
    // Verify nonce
    if (!isset($_GET['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'taskspn-download-ics')) {
      wp_die('Security check failed.', 'Access Denied', ['response' => 403]);
    }

    // Get optional date range from query parameters
    $start_date = isset($_GET['start_date']) ? sanitize_text_field(wp_unslash($_GET['start_date'])) : null;
    $end_date = isset($_GET['end_date']) ? sanitize_text_field(wp_unslash($_GET['end_date'])) : null;

    // Validate date format if provided
    if (!empty($start_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
      $start_date = null;
    }
    if (!empty($end_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
      $end_date = null;
    }

    // Generate ICS content
    $ics_content = $this->taskspn_generate_ics($start_date, $end_date);

    // Set headers for file download
    $filename = 'taskspn-calendar-' . gmdate('Y-m-d') . '.ics';
    
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($ics_content));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

    // Output ICS content
    echo $ics_content;
    exit;
  }
}

