<?php
/**
 * Gutenberg Blocks registration.
 *
 * Registers simple server-side blocks that render existing shortcodes.
 *
 * @since 1.0.0
 */
class TASKSPN_Blocks {
  public function register_blocks() {
    // Ensure block category exists (WP 5.8 fallback)
    add_filter('block_categories_all', function($categories) {
      $slug = 'taskspn';
      foreach ($categories as $cat) {
        if (!empty($cat['slug']) && $cat['slug'] === $slug) {
          return $categories;
        }
      }
      $categories[] = [
        'slug' => $slug,
        'title' => __('Tasks Manager - PN', 'taskspn'),
        'icon' => null,
      ];
      return $categories;
    });

    $this->register_shortcode_block('taskspn/joinable-tasks', __('Joinable Tasks (Taskspn)', 'taskspn'), '[taskspn-joinable-tasks]');
    $this->register_shortcode_block('taskspn/users-ranking', __('Users Ranking (Taskspn)', 'taskspn'), '[taskspn-users-ranking]');
    $this->register_shortcode_block('taskspn/calendar', __('Calendar (Taskspn)', 'taskspn'), '[taskspn-calendar]');
    $this->register_shortcode_block('taskspn/task', __('Task (Taskspn)', 'taskspn'), '[taskspn-task]');
    $this->register_shortcode_block('taskspn/task-list', __('Task List (Taskspn)', 'taskspn'), '[taskspn-task-list]');
  }

  private function register_shortcode_block($name, $title, $shortcode) {
    register_block_type($name, [
      'api_version' => 2,
      'render_callback' => function($attributes = []) use ($shortcode) {
        return do_shortcode($shortcode);
      },
      'category' => 'taskspn',
      'title' => $title,
      'icon' => 'excerpt-view',
      'supports' => [
        'html' => false,
      ],
    ]);
  }

  public function enqueue_editor_assets() {
    // Minimal editor script to make blocks discoverable with descriptions
    wp_enqueue_script(
      'taskspn-blocks',
      TASKSPN_URL . 'assets/js/taskspn-blocks.js',
      ['wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor', 'wp-components'],
      TASKSPN_VERSION,
      true
    );
  }
}


