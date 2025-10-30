<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current version of the plugin.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    TASKSPN
 * @subpackage TASKSPN/includes
 * @author     Padres en la Nube
 */

class TASKSPN {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      TASKSPN_Loader    $taskspn_loader    Maintains and registers all hooks for the plugin.
	 */
	protected $taskspn_loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $taskspn_plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $taskspn_plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $taskspn_version    The current version of the plugin.
	 */
	protected $taskspn_version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin. Load the dependencies, define the locale, and set the hooks for the admin area and the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if (defined('TASKSPN_VERSION')) {
			$this->taskspn_version = TASKSPN_VERSION;
		} else {
			$this->taskspn_version = '1.0.0';
		}

		$this->taskspn_plugin_name = 'taskspn';

		self::taskspn_load_dependencies();
		self::taskspn_load_i18n();
		self::taskspn_define_common_hooks();
		self::taskspn_define_admin_hooks();
		self::taskspn_define_public_hooks();
		self::taskspn_define_custom_post_types();
		self::taskspn_define_taxonomies();
		self::taskspn_load_ajax();
		self::taskspn_load_ajax_nopriv();
		self::taskspn_load_data();
		self::taskspn_load_templates();
		self::taskspn_load_settings();
		self::taskspn_load_shortcodes();
	}
			
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 * - TASKSPN_Loader. Orchestrates the hooks of the plugin.
	 * - TASKSPN_i18n. Defines internationalization functionality.
	 * - TASKSPN_Common. Defines hooks used accross both, admin and public side.
	 * - TASKSPN_Admin. Defines all hooks for the admin area.
	 * - TASKSPN_Public. Defines all hooks for the public side of the site.
	 * - TASKSPN_Post_Type_Task. Defines Task custom post type.
	 * - TASKSPN_Taxonomies_Task. Defines Task taxonomies.
	 * - TASKSPN_Templates. Load plugin templates.
	 * - TASKSPN_Data. Load main usefull data.
	 * - TASKSPN_Functions_Post. Posts management functions.
	 * - TASKSPN_Functions_User. Users management functions.
	 * - TASKSPN_Functions_Attachment. Attachments management functions.
	 * - TASKSPN_Functions_Settings. Define settings.
	 * - TASKSPN_Functions_Forms. Forms management functions.
	 * - TASKSPN_Functions_Ajax. Ajax functions.
	 * - TASKSPN_Functions_Ajax_Nopriv. Ajax No Private functions.
	 * - TASKSPN_Popups. Define popups functionality.
	 * - TASKSPN_Functions_Shortcodes. Define all shortcodes for the platform.
	 * - TASKSPN_Functions_Validation. Define validation and sanitization.
	 *
	 * Create an instance of the loader which will be used to register the hooks with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function taskspn_load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-loader.php';

		/**
		 * The class responsible for defining internationalization functionality of the plugin.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-i18n.php';

		/**
		 * The class responsible for defining all actions that occur both in the admin area and in the public-facing side of the site.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-common.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once TASKSPN_DIR . 'includes/admin/class-taskspn-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing side of the site.
		 */
		require_once TASKSPN_DIR . 'includes/public/class-taskspn-public.php';

		/**
		 * The class responsible for create the Task custom post type.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-post-type-task.php';

		/**
		 * The class responsible for create the Task custom taxonomies.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-taxonomies-task.php';

		/**
		 * The class responsible for plugin templates.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-templates.php';

		/**
		 * The class getting key data of the platform.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-data.php';

		/**
		 * The class defining posts management functions.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-functions-post.php';

		/**
		 * The class defining users management functions.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-functions-user.php';

		/**
		 * The class defining attahcments management functions.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-functions-attachment.php';

		/**
		 * The class defining settings.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-settings.php';

		/**
		 * The class defining form management.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-forms.php';

		/**
		 * The class defining ajax functions.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-ajax.php';

		/**
		 * The class defining no private ajax functions.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-ajax-nopriv.php';

		/**
		 * The class defining shortcodes.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-shortcodes.php';

		/**
		 * The class defining validation and sanitization.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-validation.php';

		/**
		 * The class responsible for popups functionality.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-popups.php';

		/**
		 * The class managing the custom selector component.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-selector.php';

		/**
		 * The class managing calendar functionality.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-calendar.php';

		/**
		 * Gutenberg blocks.
		 */
		require_once TASKSPN_DIR . 'includes/class-taskspn-blocks.php';

		$this->taskspn_loader = new TASKSPN_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the TASKSPN_i18n class in order to set the domain and to register the hook with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function taskspn_load_i18n() {
		$plugin_i18n = new TASKSPN_i18n();
		$this->taskspn_loader->taskspn_add_action('after_setup_theme', $plugin_i18n, 'taskspn_load_plugin_textdomain');

		if (class_exists('Polylang')) {
			$this->taskspn_loader->taskspn_add_filter('pll_get_post_types', $plugin_i18n, 'taskspn_pll_get_post_types', 10, 2);
    }
	}

	/**
	 * Register all of the hooks related to the main functionalities of the plugin, common to public and admin faces.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function taskspn_define_common_hooks() {
		$plugin_common = new TASKSPN_Common(self::taskspn_get_plugin_name(), self::taskspn_get_version());
		$this->taskspn_loader->taskspn_add_action('wp_enqueue_scripts', $plugin_common, 'taskspn_enqueue_styles');
		$this->taskspn_loader->taskspn_add_action('wp_enqueue_scripts', $plugin_common, 'taskspn_enqueue_scripts');
		$this->taskspn_loader->taskspn_add_action('admin_enqueue_scripts', $plugin_common, 'taskspn_enqueue_styles');
		$this->taskspn_loader->taskspn_add_action('admin_enqueue_scripts', $plugin_common, 'taskspn_enqueue_scripts');
		$this->taskspn_loader->taskspn_add_filter('body_class', $plugin_common, 'taskspn_body_classes');

		$plugin_post_type_task = new TASKSPN_Post_Type_Task();
		$this->taskspn_loader->taskspn_add_action('taskspn_task_form_save', $plugin_post_type_task, 'taskspn_task_form_save', 999, 5);
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function taskspn_define_admin_hooks() {
		$plugin_admin = new TASKSPN_Admin(self::taskspn_get_plugin_name(), self::taskspn_get_version());
		$this->taskspn_loader->taskspn_add_action('admin_enqueue_scripts', $plugin_admin, 'taskspn_enqueue_styles');
		$this->taskspn_loader->taskspn_add_action('admin_enqueue_scripts', $plugin_admin, 'taskspn_enqueue_scripts');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function taskspn_define_public_hooks() {
		$plugin_public = new TASKSPN_Public(self::taskspn_get_plugin_name(), self::taskspn_get_version());
		$this->taskspn_loader->taskspn_add_action('wp_enqueue_scripts', $plugin_public, 'taskspn_enqueue_styles');
		$this->taskspn_loader->taskspn_add_action('wp_enqueue_scripts', $plugin_public, 'taskspn_enqueue_scripts');

		$plugin_user = new TASKSPN_Functions_User();
		$this->taskspn_loader->taskspn_add_action('wp_login', $plugin_user, 'taskspn_user_wp_login');

		// Blocks (register and editor assets)
		$plugin_blocks = new TASKSPN_Blocks();
		$this->taskspn_loader->taskspn_add_action('init', $plugin_blocks, 'register_blocks');
		$this->taskspn_loader->taskspn_add_action('enqueue_block_editor_assets', $plugin_blocks, 'enqueue_editor_assets');
	}

	/**
	 * Register all Post Types with meta boxes and templates.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function taskspn_define_custom_post_types() {
		$plugin_post_type_task = new TASKSPN_Post_Type_Task();
		$this->taskspn_loader->taskspn_add_action('init', $plugin_post_type_task, 'taskspn_task_register_post_type');
		$this->taskspn_loader->taskspn_add_action('admin_init', $plugin_post_type_task, 'taskspn_task_add_meta_box');
		$this->taskspn_loader->taskspn_add_action('save_post_taskspn_task', $plugin_post_type_task, 'taskspn_task_save_post', 10, 3);
		$this->taskspn_loader->taskspn_add_filter('single_template', $plugin_post_type_task, 'taskspn_task_single_template', 10, 3);
		$this->taskspn_loader->taskspn_add_filter('archive_template', $plugin_post_type_task, 'taskspn_task_archive_template', 10, 3);
		$this->taskspn_loader->taskspn_add_shortcode('taskspn-task-list', $plugin_post_type_task, 'taskspn_task_list_wrapper');
	}

	/**
	 * Register all of the hooks related to Taxonomies.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function taskspn_define_taxonomies() {
		$plugin_taxonomies_task = new TASKSPN_Taxonomies_Task();
		$this->taskspn_loader->taskspn_add_action('init', $plugin_taxonomies_task, 'taskspn_register_taxonomies');
	}

	/**
	 * Load most common data used on the platform.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function taskspn_load_data() {
		$plugin_data = new TASKSPN_Data();

		if (is_admin()) {
			$this->taskspn_loader->taskspn_add_action('init', $plugin_data, 'taskspn_load_plugin_data');
		} else {
			$this->taskspn_loader->taskspn_add_action('wp_head', $plugin_data, 'taskspn_load_plugin_data');
		}

		$this->taskspn_loader->taskspn_add_action('wp_footer', $plugin_data, 'taskspn_flush_rewrite_rules');
		$this->taskspn_loader->taskspn_add_action('admin_footer', $plugin_data, 'taskspn_flush_rewrite_rules');
	}

	/**
	 * Register templates.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function taskspn_load_templates() {
		if (!defined('DOING_AJAX')) {
			$plugin_templates = new TASKSPN_Templates();
			$this->taskspn_loader->taskspn_add_action('wp_footer', $plugin_templates, 'load_plugin_templates');
			$this->taskspn_loader->taskspn_add_action('admin_footer', $plugin_templates, 'load_plugin_templates');
		}
	}

	/**
	 * Register settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function taskspn_load_settings() {
		$plugin_settings = new TASKSPN_Settings();
		$this->taskspn_loader->taskspn_add_action('admin_menu', $plugin_settings, 'taskspn_admin_menu');
		$this->taskspn_loader->taskspn_add_action('activated_plugin', $plugin_settings, 'taskspn_activated_plugin');
		$this->taskspn_loader->taskspn_add_action('admin_init', $plugin_settings, 'taskspn_check_activation');
		$this->taskspn_loader->taskspn_add_filter('plugin_action_links_taskpnspn/taskspn.php', $plugin_settings, 'taskspn_plugin_action_links');
	}

	/**
	 * Load ajax functions.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function taskspn_load_ajax() {
		$plugin_ajax = new TASKSPN_Ajax();
		$this->taskspn_loader->taskspn_add_action('wp_ajax_taskspn_ajax', $plugin_ajax, 'taskspn_ajax_server');
	}

	/**
	 * Load no private ajax functions.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function taskspn_load_ajax_nopriv() {
		$plugin_ajax_nopriv = new TASKSPN_Ajax_Nopriv();
		$this->taskspn_loader->taskspn_add_action('wp_ajax_taskspn_ajax_nopriv', $plugin_ajax_nopriv, 'taskspn_ajax_nopriv_server');
		$this->taskspn_loader->taskspn_add_action('wp_ajax_nopriv_taskspn_ajax_nopriv', $plugin_ajax_nopriv, 'taskspn_ajax_nopriv_server');
	}

	/**
	 * Register shortcodes of the platform.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function taskspn_load_shortcodes() {
		$plugin_shortcodes = new TASKSPN_Shortcodes();
		$this->taskspn_loader->taskspn_add_shortcode('taskspn-task', $plugin_shortcodes, 'taskspn_task');
		$this->taskspn_loader->taskspn_add_shortcode('taskspn-test', $plugin_shortcodes, 'taskspn_test');
		$this->taskspn_loader->taskspn_add_shortcode('taskspn-call-to-action', $plugin_shortcodes, 'taskspn_call_to_action');
		$this->taskspn_loader->taskspn_add_shortcode('taskspn-calendar', $plugin_shortcodes, 'taskspn_calendar');
		$this->taskspn_loader->taskspn_add_shortcode('taskspn-joinable-tasks', $plugin_shortcodes, 'taskspn_joinable_tasks');
		$this->taskspn_loader->taskspn_add_shortcode('taskspn-users-ranking', $plugin_shortcodes, 'taskspn_users_ranking');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress. Then it flushes the rewrite rules if needed.
	 *
	 * @since    1.0.0
	 */
	public function taskspn_run() {
		$this->taskspn_loader->taskspn_run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function taskspn_get_plugin_name() {
		return $this->taskspn_plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    TASKSPN_Loader    Orchestrates the hooks of the plugin.
	 */
	public function taskspn_get_loader() {
		return $this->taskspn_loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function taskspn_get_version() {
		return $this->taskspn_version;
	}
}