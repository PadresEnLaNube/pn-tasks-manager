<?php
/**
 * Gutenberg Blocks registration.
 *
 * Registers simple server-side blocks that render existing shortcodes.
 *
 * @since 1.0.0
 */
class PN_TASKS_MANAGER_Blocks {
  public function register_blocks() {
    // Ensure block category exists (WP 5.8 fallback)
    add_filter('block_categories_all', function($categories) {
      $slug = 'pn-tasks-manager';
      foreach ($categories as $cat) {
        if (!empty($cat['slug']) && $cat['slug'] === $slug) {
          return $categories;
        }
      }
      $categories[] = [
        'slug' => $slug,
        'title' => __('PN Tasks Manager', 'pn-tasks-manager'),
        'icon' => null,
      ];
      return $categories;
    });

    $this->register_shortcode_block('pn-tasks-manager/joinable-tasks', __('Joinable Tasks (PN Tasks Manager)', 'pn-tasks-manager'), '[pn-tasks-manager-joinable-tasks]');
    $this->register_shortcode_block('pn-tasks-manager/users-ranking', __('Users Ranking (PN Tasks Manager)', 'pn-tasks-manager'), '[pn-tasks-manager-users-ranking]');
    $this->register_shortcode_block('pn-tasks-manager/calendar', __('Calendar (PN Tasks Manager)', 'pn-tasks-manager'), '[pn-tasks-manager-calendar]');
    $this->register_shortcode_block('pn-tasks-manager/task', __('Task (PN Tasks Manager)', 'pn-tasks-manager'), '[pn-tasks-manager-task]');
    $this->register_shortcode_block('pn-tasks-manager/task-list', __('Task List (PN Tasks Manager)', 'pn-tasks-manager'), '[pn-tasks-manager-task-list]');
  }

  private function register_shortcode_block($name, $title, $shortcode) {
    register_block_type($name, [
      'api_version' => 2,
      'render_callback' => function($attributes = []) use ($shortcode) {
        return do_shortcode($shortcode);
      },
      'category' => 'pn-tasks-manager',
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
      'pn-tasks-manager-blocks',
      PN_TASKS_MANAGER_URL . 'assets/js/pn-tasks-manager-blocks.js',
      ['wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor', 'wp-components'],
      PN_TASKS_MANAGER_VERSION,
      true
    );
  }
}


