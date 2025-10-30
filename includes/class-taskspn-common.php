<?php
/**
 * The-global functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to enqueue the-global stylesheet and JavaScript.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    TASKSPN
 * @subpackage TASKSPN/includes
 * @author     Padres en la Nube
 */
class TASKSPN_Common {

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
	public function taskspn_enqueue_styles() {
		if (!wp_style_is($this->plugin_name . '-material-icons-outlined', 'enqueued')) {
			wp_enqueue_style($this->plugin_name . '-material-icons-outlined', TASKSPN_URL . 'assets/css/material-icons-outlined.min.css', [], $this->version, 'all');
		}

		$enqueue_front_assets = is_admin();

		if (!$enqueue_front_assets) {
			if (is_singular('taskspn_task') || is_post_type_archive('taskspn_task')) {
				$enqueue_front_assets = true;
			} else if (is_singular()) {
				$post = get_post();
				if ($post) {
					$content = $post->post_content;
					$has_shortcode = false;
					$shortcodes = ['taskspn-joinable-tasks','taskspn-users-ranking','taskspn-calendar','taskspn-task','taskspn-task-list'];
					foreach ($shortcodes as $sc) {
						if (has_shortcode($content, $sc)) { $has_shortcode = true; break; }
					}
					$has_block = function_exists('has_block') && (
						has_block('taskspn/joinable-tasks', $post) ||
						has_block('taskspn/users-ranking', $post) ||
						has_block('taskspn/calendar', $post) ||
						has_block('taskspn/task', $post) ||
						has_block('taskspn/task-list', $post)
					);
					$enqueue_front_assets = $has_shortcode || $has_block;
				}
			}
		}

		if ($enqueue_front_assets) {
			if (!wp_style_is($this->plugin_name . '-popups', 'enqueued')) {
					wp_enqueue_style($this->plugin_name . '-popups', TASKSPN_URL . 'assets/css/taskspn-popups.css', [], $this->version, 'all');
			}

			if (!wp_style_is($this->plugin_name . '-selector', 'enqueued')) {
					wp_enqueue_style($this->plugin_name . '-selector', TASKSPN_URL . 'assets/css/taskspn-selector.css', [], $this->version, 'all');
			}

			if (!wp_style_is($this->plugin_name . '-trumbowyg', 'enqueued')) {
					wp_enqueue_style($this->plugin_name . '-trumbowyg', TASKSPN_URL . 'assets/css/trumbowyg.min.css', [], $this->version, 'all');
			}

			if (!wp_style_is($this->plugin_name . '-tooltipster', 'enqueued')) {
					wp_enqueue_style($this->plugin_name . '-tooltipster', TASKSPN_URL . 'assets/css/tooltipster.min.css', [], $this->version, 'all');
			}

			if (!wp_style_is($this->plugin_name . '-owl', 'enqueued')) {
					wp_enqueue_style($this->plugin_name . '-owl', TASKSPN_URL . 'assets/css/owl.min.css', [], $this->version, 'all');
			}

			wp_enqueue_style($this->plugin_name, TASKSPN_URL . 'assets/css/taskspn.css', [], $this->version, 'all');
		}

		// Inject dynamic color variables from options into :root
		$colors_map = [
			'--taskspn-color-main' => get_option('taskspn_color_main'),
			'--taskspn-bg-color-main' => get_option('taskspn_bg_color_main'),
			'--taskspn-border-color-main' => get_option('taskspn_border_color_main'),
			'--taskspn-color-main-alt' => get_option('taskspn_color_main_alt'),
			'--taskspn-bg-color-main-alt' => get_option('taskspn_bg_color_main_alt'),
			'--taskspn-border-color-main-alt' => get_option('taskspn_border_color_main_alt'),
			'--taskspn-color-main-blue' => get_option('taskspn_color_main_blue'),
			'--taskspn-color-main-grey' => get_option('taskspn_color_main_grey'),
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
	public function taskspn_enqueue_scripts() {
    if(!wp_script_is('jquery-ui-sortable', 'enqueued')) {
			wp_enqueue_script('jquery-ui-sortable');
    }

    if(!wp_script_is($this->plugin_name . '-trumbowyg', 'enqueued')) {
			wp_enqueue_script($this->plugin_name . '-trumbowyg', TASKSPN_URL . 'assets/js/trumbowyg.min.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
    }

		wp_localize_script($this->plugin_name . '-trumbowyg', 'taskspn_trumbowyg', [
			'path' => TASKSPN_URL . 'assets/media/trumbowyg-icons.svg',
		]);

    if(!wp_script_is($this->plugin_name . '-popups', 'enqueued')) {
      wp_enqueue_script($this->plugin_name . '-popups', TASKSPN_URL . 'assets/js/taskspn-popups.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
    }

    if(!wp_script_is($this->plugin_name . '-selector', 'enqueued')) {
      wp_enqueue_script($this->plugin_name . '-selector', TASKSPN_URL . 'assets/js/taskspn-selector.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
    }

    if(!wp_script_is($this->plugin_name . '-tooltipster', 'enqueued')) {
			wp_enqueue_script($this->plugin_name . '-tooltipster', TASKSPN_URL . 'assets/js/tooltipster.min.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
    }

    if(!wp_script_is($this->plugin_name . '-owl', 'enqueued')) {
			wp_enqueue_script($this->plugin_name . '-owl', TASKSPN_URL . 'assets/js/owl.min.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
    }

		wp_enqueue_script($this->plugin_name, TASKSPN_URL . 'assets/js/taskspn.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		wp_enqueue_script($this->plugin_name . '-aux', TASKSPN_URL . 'assets/js/taskspn-aux.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		wp_enqueue_script($this->plugin_name . '-forms', TASKSPN_URL . 'assets/js/taskspn-forms.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		wp_enqueue_script($this->plugin_name . '-ajax', TASKSPN_URL . 'assets/js/taskspn-ajax.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);

		wp_localize_script($this->plugin_name . '-ajax', 'taskspn_ajax', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'taskspn_ajax_nonce' => wp_create_nonce('taskspn-nonce'),
		]);

		// Add CPTs data to JavaScript
		wp_localize_script($this->plugin_name . '-ajax', 'taskspn_cpts', TASKSPN_CPTS);

		// Verify nonce for GET parameters
		$nonce_verified = false;
		if (!empty($_GET['taskspn_nonce'])) {
			$nonce_verified = wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['taskspn_nonce'])), 'taskspn-get-nonce');
		}

		// Only process GET parameters if nonce is verified
		$taskspn_action = '';
		$taskspn_btn_id = '';
		$taskspn_popup = '';
		$taskspn_tab = '';

		if ($nonce_verified) {
			$taskspn_action = !empty($_GET['taskspn_action']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_GET['taskspn_action'])) : '';
			$taskspn_btn_id = !empty($_GET['taskspn_btn_id']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_GET['taskspn_btn_id'])) : '';
			$taskspn_popup = !empty($_GET['taskspn_popup']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_GET['taskspn_popup'])) : '';
			$taskspn_tab = !empty($_GET['taskspn_tab']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_GET['taskspn_tab'])) : '';
		}
		
		wp_localize_script($this->plugin_name, 'taskspn_action', [
			'action' => $taskspn_action,
			'btn_id' => $taskspn_btn_id,
			'popup' => $taskspn_popup,
			'tab' => $taskspn_tab,
			'taskspn_get_nonce' => wp_create_nonce('taskspn-get-nonce'),
		]);

		wp_localize_script($this->plugin_name, 'taskspn_path', [
			'main' => TASKSPN_URL,
			'assets' => TASKSPN_URL . 'assets/',
			'css' => TASKSPN_URL . 'assets/css/',
			'js' => TASKSPN_URL . 'assets/js/',
			'media' => TASKSPN_URL . 'assets/media/',
		]);

		wp_localize_script($this->plugin_name, 'taskspn_i18n', [
			'an_error_has_occurred' => esc_html(__('An error has occurred. Please try again in a few minutes.', 'taskspn')),
			'user_unlogged' => esc_html(__('Please create a new user or login to save the information.', 'taskspn')),
			'saved_successfully' => esc_html(__('Saved successfully', 'taskspn')),
			'removed_successfully' => esc_html(__('Removed successfully', 'taskspn')),
			'loading' => esc_html(__('Loading...', 'taskspn')),
			'edit_image' => esc_html(__('Edit image', 'taskspn')),
			'edit_images' => esc_html(__('Edit images', 'taskspn')),
			'select_image' => esc_html(__('Select image', 'taskspn')),
			'select_images' => esc_html(__('Select images', 'taskspn')),
			'edit_video' => esc_html(__('Edit video', 'taskspn')),
			'edit_videos' => esc_html(__('Edit videos', 'taskspn')),
			'select_video' => esc_html(__('Select video', 'taskspn')),
			'select_videos' => esc_html(__('Select videos', 'taskspn')),
			'edit_audio' => esc_html(__('Edit audio', 'taskspn')),
			'edit_audios' => esc_html(__('Edit audios', 'taskspn')),
			'select_audio' => esc_html(__('Select audio', 'taskspn')),
			'select_audios' => esc_html(__('Select audios', 'taskspn')),
			'edit_file' => esc_html(__('Edit file', 'taskspn')),
			'edit_files' => esc_html(__('Edit files', 'taskspn')),
			'select_file' => esc_html(__('Select file', 'taskspn')),
			'select_files' => esc_html(__('Select files', 'taskspn')),
			'ordered_element' => esc_html(__('Ordered element', 'taskspn')),
			'select_option' => esc_html(__('Select option', 'taskspn')),
			'select_options' => esc_html(__('Select options', 'taskspn')),
			'copied' => esc_html(__('Copied', 'taskspn')),

			// Audio recorder translations
			'ready_to_record' => esc_html(__('Ready to record', 'taskspn')),
			'recording' => esc_html(__('Recording...', 'taskspn')),
			'recording_stopped' => esc_html(__('Recording stopped. Ready to play or transcribe.', 'taskspn')),
			'recording_completed' => esc_html(__('Recording completed. Ready to transcribe.', 'taskspn')),
			'microphone_error' => esc_html(__('Error: Could not access microphone', 'taskspn')),
			'no_audio_to_transcribe' => esc_html(__('No audio to transcribe', 'taskspn')),
			'invalid_response_format' => esc_html(__('Invalid server response format', 'taskspn')),
			'invalid_server_response' => esc_html(__('Invalid server response', 'taskspn')),
			'transcription_completed' => esc_html(__('Transcription completed', 'taskspn')),
			'no_transcription_received' => esc_html(__('No transcription received from server', 'taskspn')),
			'transcription_error' => esc_html(__('Error in transcription', 'taskspn')),
			'connection_error' => esc_html(__('Connection error', 'taskspn')),
			'connection_error_server' => esc_html(__('Connection error: Could not connect to server', 'taskspn')),
			'permission_error' => esc_html(__('Permission error: Security verification failed', 'taskspn')),
			'server_error' => esc_html(__('Server error: Internal server problem', 'taskspn')),
			'unknown_error' => esc_html(__('Unknown error', 'taskspn')),
			'processing_error' => esc_html(__('Error processing audio', 'taskspn')),
		]);

		// Initialize popups
		TASKSPN_Popups::instance();

		// Initialize selectors
		TASKSPN_Selector::instance();
	}

  public function taskspn_body_classes($classes) {
	  $classes[] = 'taskspn-body';

	  if (!is_user_logged_in()) {
      $classes[] = 'taskspn-body-unlogged';
    } else {
      $classes[] = 'taskspn-body-logged-in';

      $user = new WP_User(get_current_user_id());
      foreach ($user->roles as $role) {
        $classes[] = 'taskspn-body-' . $role;
      }
    }

	  return $classes;
  }
}
