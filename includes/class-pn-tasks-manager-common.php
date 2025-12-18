<?php
/**
 * The-global functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to enqueue the-global stylesheet and JavaScript.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/includes
 * @author     Padres en la Nube
 */
class PN_TASKS_MANAGER_Common {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets.
	 *
	 * @since    1.0.0
	 */
	public function pn_tasks_manager_enqueue_styles() {
		if (!wp_style_is($this->plugin_name . '-material-icons-outlined', 'enqueued')) {
			wp_enqueue_style($this->plugin_name . '-material-icons-outlined', PN_TASKS_MANAGER_URL . 'assets/css/material-icons-outlined.min.css', [], $this->version, 'all');
		}

		$enqueue_front_assets = is_admin();

		if (!$enqueue_front_assets) {
			if (is_singular('pn_tasks_task') || is_post_type_archive('pn_tasks_task')) {
				$enqueue_front_assets = true;
			} else if (is_singular()) {
				$post = get_post();
				if ($post) {
					$content = $post->post_content;
					$has_shortcode = false;
					$shortcodes = ['pn-tasks-manager-joinable-tasks','pn-tasks-manager-users-ranking','pn-tasks-manager-calendar','pn-tasks-manager-task','pn-tasks-manager-task-list'];
					foreach ($shortcodes as $sc) {
						if (has_shortcode($content, $sc)) { $has_shortcode = true; break; }
					}
					$has_block = function_exists('has_block') && (
						has_block('pn-tasks-manager/joinable-tasks', $post) ||
						has_block('pn-tasks-manager/users-ranking', $post) ||
						has_block('pn-tasks-manager/calendar', $post) ||
						has_block('pn-tasks-manager/task', $post) ||
						has_block('pn-tasks-manager/task-list', $post)
					);
					$enqueue_front_assets = $has_shortcode || $has_block;
				}
			}
		}

		if ($enqueue_front_assets) {
			if (!wp_style_is($this->plugin_name . '-popups', 'enqueued')) {
					wp_enqueue_style($this->plugin_name . '-popups', PN_TASKS_MANAGER_URL . 'assets/css/pn-tasks-manager-popups.css', [], $this->version, 'all');
			}

			if (!wp_style_is($this->plugin_name . '-selector', 'enqueued')) {
					wp_enqueue_style($this->plugin_name . '-selector', PN_TASKS_MANAGER_URL . 'assets/css/pn-tasks-manager-selector.css', [], $this->version, 'all');
			}

			if (!wp_style_is($this->plugin_name . '-trumbowyg', 'enqueued')) {
					wp_enqueue_style($this->plugin_name . '-trumbowyg', PN_TASKS_MANAGER_URL . 'assets/css/trumbowyg.min.css', [], $this->version, 'all');
			}

			if (!wp_style_is($this->plugin_name . '-tooltipster', 'enqueued')) {
					wp_enqueue_style($this->plugin_name . '-tooltipster', PN_TASKS_MANAGER_URL . 'assets/css/tooltipster.min.css', [], $this->version, 'all');
			}

			if (!wp_style_is($this->plugin_name . '-owl', 'enqueued')) {
					wp_enqueue_style($this->plugin_name . '-owl', PN_TASKS_MANAGER_URL . 'assets/css/owl.min.css', [], $this->version, 'all');
			}

			wp_enqueue_style($this->plugin_name, PN_TASKS_MANAGER_URL . 'assets/css/pn-tasks-manager.css', [], $this->version, 'all');
		}

		// Inject dynamic color variables from options into :root
		$colors_map = [
			'--pn-tasks-manager-color-main' => get_option('pn_tasks_manager_color_main'),
			'--pn-tasks-manager-bg-color-main' => get_option('pn_tasks_manager_bg_color_main'),
			'--pn-tasks-manager-border-color-main' => get_option('pn_tasks_manager_border_color_main'),
			'--pn-tasks-manager-color-main-alt' => get_option('pn_tasks_manager_color_main_alt'),
			'--pn-tasks-manager-bg-color-main-alt' => get_option('pn_tasks_manager_bg_color_main_alt'),
			'--pn-tasks-manager-border-color-main-alt' => get_option('pn_tasks_manager_border_color_main_alt'),
			'--pn-tasks-manager-color-main-blue' => get_option('pn_tasks_manager_color_main_blue'),
			'--pn-tasks-manager-color-main-grey' => get_option('pn_tasks_manager_color_main_grey'),
		];

		$vars = [];
		foreach ($colors_map as $var => $val) {
			if (!empty($val) && is_string($val)) {
				$vars[] = $var . ':' . $val;
			}
		}
		if (!empty($vars)) {
			$inline_css = ':root{' . implode(';', $vars) . ';}';
			wp_add_inline_style($this->plugin_name, $inline_css);
		}
	}

	/**
	 * Register the JavaScript.
	 *
	 * @since    1.0.0
	 */
	public function pn_tasks_manager_enqueue_scripts() {
    if(!wp_script_is('jquery-ui-sortable', 'enqueued')) {
			wp_enqueue_script('jquery-ui-sortable');
    }

    if(!wp_script_is($this->plugin_name . '-trumbowyg', 'enqueued')) {
			wp_enqueue_script($this->plugin_name . '-trumbowyg', PN_TASKS_MANAGER_URL . 'assets/js/trumbowyg.min.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
    }

		wp_localize_script($this->plugin_name . '-trumbowyg', 'pn_tasks_manager_trumbowyg', [
			'path' => PN_TASKS_MANAGER_URL . 'assets/media/trumbowyg-icons.svg',
		]);

    if(!wp_script_is($this->plugin_name . '-popups', 'enqueued')) {
      wp_enqueue_script($this->plugin_name . '-popups', PN_TASKS_MANAGER_URL . 'assets/js/pn-tasks-manager-popups.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
    }

    if(!wp_script_is($this->plugin_name . '-selector', 'enqueued')) {
      wp_enqueue_script($this->plugin_name . '-selector', PN_TASKS_MANAGER_URL . 'assets/js/pn-tasks-manager-selector.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
    }

    if(!wp_script_is($this->plugin_name . '-tooltipster', 'enqueued')) {
			wp_enqueue_script($this->plugin_name . '-tooltipster', PN_TASKS_MANAGER_URL . 'assets/js/tooltipster.min.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
    }

    if(!wp_script_is($this->plugin_name . '-owl', 'enqueued')) {
			wp_enqueue_script($this->plugin_name . '-owl', PN_TASKS_MANAGER_URL . 'assets/js/owl.min.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
    }

		wp_enqueue_script($this->plugin_name, PN_TASKS_MANAGER_URL . 'assets/js/pn-tasks-manager.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		wp_enqueue_script($this->plugin_name . '-aux', PN_TASKS_MANAGER_URL . 'assets/js/pn-tasks-manager-aux.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		wp_enqueue_script($this->plugin_name . '-forms', PN_TASKS_MANAGER_URL . 'assets/js/pn-tasks-manager-forms.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		wp_enqueue_script($this->plugin_name . '-ajax', PN_TASKS_MANAGER_URL . 'assets/js/pn-tasks-manager-ajax.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		wp_enqueue_script($this->plugin_name . '-shortcodes', PN_TASKS_MANAGER_URL . 'assets/js/pn-tasks-manager-shortcodes.js', [$this->plugin_name . '-ajax'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);

		wp_localize_script($this->plugin_name . '-ajax', 'pn_tasks_manager_ajax', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'pn_tasks_manager_ajax_nonce' => wp_create_nonce('pn-tasks-manager-nonce'),
		]);

		// Add CPTs data to JavaScript
		wp_localize_script($this->plugin_name . '-forms', 'pn_tasks_manager_cpts', PN_TASKS_MANAGER_CPTS);
		wp_localize_script($this->plugin_name . '-ajax', 'pn_tasks_manager_cpts', PN_TASKS_MANAGER_CPTS);

		// Verify nonce for GET parameters
		$nonce_verified = false;
		if (!empty($_GET['pn_tasks_manager_nonce'])) {
			$nonce_verified = wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['pn_tasks_manager_nonce'])), 'pn-tasks-manager-get-nonce');
		}

		// Only process GET parameters if nonce is verified
		$pn_tasks_manager_action = '';
		$pn_tasks_manager_btn_id = '';
		$pn_tasks_manager_popup = '';
		$pn_tasks_manager_tab = '';

		if ($nonce_verified) {
			$pn_tasks_manager_action = isset($_GET['pn_tasks_manager_action'])
				? PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(sanitize_text_field(wp_unslash($_GET['pn_tasks_manager_action'])))
				: '';
			$pn_tasks_manager_btn_id = isset($_GET['pn_tasks_manager_btn_id'])
				? PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(sanitize_text_field(wp_unslash($_GET['pn_tasks_manager_btn_id'])))
				: '';
			$pn_tasks_manager_popup = isset($_GET['pn_tasks_manager_popup'])
				? PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(sanitize_text_field(wp_unslash($_GET['pn_tasks_manager_popup'])))
				: '';
			$pn_tasks_manager_tab = isset($_GET['pn_tasks_manager_tab'])
				? PN_TASKS_MANAGER_Forms::pn_tasks_manager_sanitizer(sanitize_text_field(wp_unslash($_GET['pn_tasks_manager_tab'])))
				: '';
		}
		
		wp_localize_script($this->plugin_name, 'pn_tasks_manager_action', [
			'action' => $pn_tasks_manager_action,
			'btn_id' => $pn_tasks_manager_btn_id,
			'popup' => $pn_tasks_manager_popup,
			'tab' => $pn_tasks_manager_tab,
			'pn_tasks_manager_get_nonce' => wp_create_nonce('pn-tasks-manager-get-nonce'),
		]);

		wp_localize_script($this->plugin_name, 'pn_tasks_manager_path', [
			'main' => PN_TASKS_MANAGER_URL,
			'assets' => PN_TASKS_MANAGER_URL . 'assets/',
			'css' => PN_TASKS_MANAGER_URL . 'assets/css/',
			'js' => PN_TASKS_MANAGER_URL . 'assets/js/',
			'media' => PN_TASKS_MANAGER_URL . 'assets/media/',
		]);

		wp_localize_script($this->plugin_name, 'pn_tasks_manager_i18n', [
			'an_error_has_occurred' => esc_html(__('An error has occurred. Please try again in a few minutes.', 'pn-tasks-manager')),
			'user_unlogged' => esc_html(__('Please create a new user or login to save the information.', 'pn-tasks-manager')),
			'saved_successfully' => esc_html(__('Saved successfully', 'pn-tasks-manager')),
			'removed_successfully' => esc_html(__('Removed successfully', 'pn-tasks-manager')),
			'loading' => esc_html(__('Loading...', 'pn-tasks-manager')),
			'edit_image' => esc_html(__('Edit image', 'pn-tasks-manager')),
			'edit_images' => esc_html(__('Edit images', 'pn-tasks-manager')),
			'select_image' => esc_html(__('Select image', 'pn-tasks-manager')),
			'select_images' => esc_html(__('Select images', 'pn-tasks-manager')),
			'edit_video' => esc_html(__('Edit video', 'pn-tasks-manager')),
			'edit_videos' => esc_html(__('Edit videos', 'pn-tasks-manager')),
			'select_video' => esc_html(__('Select video', 'pn-tasks-manager')),
			'select_videos' => esc_html(__('Select videos', 'pn-tasks-manager')),
			'edit_audio' => esc_html(__('Edit audio', 'pn-tasks-manager')),
			'edit_audios' => esc_html(__('Edit audios', 'pn-tasks-manager')),
			'select_audio' => esc_html(__('Select audio', 'pn-tasks-manager')),
			'select_audios' => esc_html(__('Select audios', 'pn-tasks-manager')),
			'edit_file' => esc_html(__('Edit file', 'pn-tasks-manager')),
			'edit_files' => esc_html(__('Edit files', 'pn-tasks-manager')),
			'select_file' => esc_html(__('Select file', 'pn-tasks-manager')),
			'select_files' => esc_html(__('Select files', 'pn-tasks-manager')),
			'ordered_element' => esc_html(__('Ordered element', 'pn-tasks-manager')),
			'select_option' => esc_html(__('Select option', 'pn-tasks-manager')),
			'select_options' => esc_html(__('Select options', 'pn-tasks-manager')),
			'copied' => esc_html(__('Copied', 'pn-tasks-manager')),
			'please_enter_category_name' => esc_html(__('Please enter a category name', 'pn-tasks-manager')),
			'error_creating_category' => esc_html(__('Error creating category', 'pn-tasks-manager')),
			'error_creating_category_try_again' => esc_html(__('Error creating category. Please try again.', 'pn-tasks-manager')),
			'category_created_successfully' => esc_html(__('Category created successfully', 'pn-tasks-manager')),
			'category_already_exists' => esc_html(__('Category already exists', 'pn-tasks-manager')),

			// Audio recorder translations
			'ready_to_record' => esc_html(__('Ready to record', 'pn-tasks-manager')),
			'recording' => esc_html(__('Recording...', 'pn-tasks-manager')),
			'recording_stopped' => esc_html(__('Recording stopped. Ready to play or transcribe.', 'pn-tasks-manager')),
			'recording_completed' => esc_html(__('Recording completed. Ready to transcribe.', 'pn-tasks-manager')),
			'microphone_error' => esc_html(__('Error: Could not access microphone', 'pn-tasks-manager')),
			'no_audio_to_transcribe' => esc_html(__('No audio to transcribe', 'pn-tasks-manager')),
			'invalid_response_format' => esc_html(__('Invalid server response format', 'pn-tasks-manager')),
			'invalid_server_response' => esc_html(__('Invalid server response', 'pn-tasks-manager')),
			'transcription_completed' => esc_html(__('Transcription completed', 'pn-tasks-manager')),
			'no_transcription_received' => esc_html(__('No transcription received from server', 'pn-tasks-manager')),
			'transcription_error' => esc_html(__('Error in transcription', 'pn-tasks-manager')),
			'connection_error' => esc_html(__('Connection error', 'pn-tasks-manager')),
			'connection_error_server' => esc_html(__('Connection error: Could not connect to server', 'pn-tasks-manager')),
			'permission_error' => esc_html(__('Permission error: Security verification failed', 'pn-tasks-manager')),
			'server_error' => esc_html(__('Server error: Internal server problem', 'pn-tasks-manager')),
			'unknown_error' => esc_html(__('Unknown error', 'pn-tasks-manager')),
			'processing_error' => esc_html(__('Error processing audio', 'pn-tasks-manager')),
		]);

		// Initialize popups
		PN_TASKS_MANAGER_Popups::instance();

		// Initialize selectors
		PN_TASKS_MANAGER_Selector::instance();
	}

  public function pn_tasks_manager_body_classes($classes) {
	  $classes[] = 'pn-tasks-manager-body';

	  if (!is_user_logged_in()) {
      $classes[] = 'pn-tasks-manager-body-unlogged';
    } else {
      $classes[] = 'pn-tasks-manager-body-logged-in';

      $user = new WP_User(get_current_user_id());
      foreach ($user->roles as $role) {
        $classes[] = 'pn-tasks-manager-body-' . $role;
      }
    }

	  return $classes;
  }
}
