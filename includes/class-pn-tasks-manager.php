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
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/includes
 * @author     Padres en la Nube
 */

class PN_TASKS_MANAGER {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      PN_TASKS_MANAGER_Loader    $pn_tasks_manager_loader    Maintains and registers all hooks for the plugin.
	 */
	protected $pn_tasks_manager_loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $pn_tasks_manager_plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $pn_tasks_manager_plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $pn_tasks_manager_version    The current version of the plugin.
	 */
	protected $pn_tasks_manager_version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin. Load the dependencies, define the locale, and set the hooks for the admin area and the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if (defined('PN_TASKS_MANAGER_VERSION')) {
			$this->pn_tasks_manager_version = PN_TASKS_MANAGER_VERSION;
		} else {
			$this->pn_tasks_manager_version = '1.0.6';
		}

		$this->pn_tasks_manager_plugin_name = 'pn-tasks-manager';

		self::pn_tasks_manager_load_dependencies();
		self::pn_tasks_manager_load_i18n();
		self::pn_tasks_manager_define_common_hooks();
		self::pn_tasks_manager_define_admin_hooks();
		self::pn_tasks_manager_define_public_hooks();
		self::pn_tasks_manager_define_custom_post_types();
		self::pn_tasks_manager_define_taxonomies();
		self::pn_tasks_manager_load_ajax();
		self::pn_tasks_manager_load_ajax_nopriv();
		self::pn_tasks_manager_load_data();
		self::pn_tasks_manager_load_templates();
		self::pn_tasks_manager_load_settings();
		self::pn_tasks_manager_load_shortcodes();
	}
			
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 * - PN_TASKS_MANAGER_Loader. Orchestrates the hooks of the plugin.
	 * - PN_TASKS_MANAGER_i18n. Defines internationalization functionality.
	 * - PN_TASKS_MANAGER_Common. Defines hooks used accross both, admin and public side.
	 * - PN_TASKS_MANAGER_Admin. Defines all hooks for the admin area.
	 * - PN_TASKS_MANAGER_Public. Defines all hooks for the public side of the site.
	 * - PN_TASKS_MANAGER_Post_Type_Task. Defines Task custom post type.
	 * - PN_TASKS_MANAGER_Taxonomies_Task. Defines Task taxonomies.
	 * - PN_TASKS_MANAGER_Templates. Load plugin templates.
	 * - PN_TASKS_MANAGER_Data. Load main usefull data.
	 * - PN_TASKS_MANAGER_Functions_Post. Posts management functions.
	 * - PN_TASKS_MANAGER_Functions_User. Users management functions.
	 * - PN_TASKS_MANAGER_Functions_Attachment. Attachments management functions.
	 * - PN_TASKS_MANAGER_Functions_Settings. Define settings.
	 * - PN_TASKS_MANAGER_Functions_Forms. Forms management functions.
	 * - PN_TASKS_MANAGER_Functions_Ajax. Ajax functions.
	 * - PN_TASKS_MANAGER_Functions_Ajax_Nopriv. Ajax No Private functions.
	 * - PN_TASKS_MANAGER_Popups. Define popups functionality.
	 * - PN_TASKS_MANAGER_Functions_Shortcodes. Define all shortcodes for the platform.
	 * - PN_TASKS_MANAGER_Functions_Validation. Define validation and sanitization.
	 *
	 * Create an instance of the loader which will be used to register the hooks with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function pn_tasks_manager_load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-loader.php';

		/**
		 * The class responsible for defining internationalization functionality of the plugin.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-i18n.php';

		/**
		 * The class responsible for defining all actions that occur both in the admin area and in the public-facing side of the site.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-common.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/admin/class-pn-tasks-manager-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing side of the site.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/public/class-pn-tasks-manager-public.php';

		/**
		 * The class responsible for create the Task custom post type.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-post-type-task.php';

		/**
		 * The class responsible for create the Task custom taxonomies.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-taxonomies-task.php';

		/**
		 * The class responsible for plugin templates.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-templates.php';

		/**
		 * The class getting key data of the platform.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-data.php';

		/**
		 * The class defining posts management functions.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-functions-post.php';

		/**
		 * The class defining users management functions.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-functions-user.php';

		/**
		 * The class defining attahcments management functions.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-functions-attachment.php';

		/**
		 * The class defining settings.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-settings.php';

		/**
		 * The class defining form management.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-forms.php';

		/**
		 * The class defining ajax functions.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-ajax.php';

		/**
		 * The class defining no private ajax functions.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-ajax-nopriv.php';

		/**
		 * The class defining shortcodes.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-shortcodes.php';

		/**
		 * The class defining validation and sanitization.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-validation.php';

		/**
		 * The class responsible for popups functionality.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-popups.php';

		/**
		 * The class managing the custom selector component.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-selector.php';

		/**
		 * The class managing calendar functionality.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-calendar.php';

		/**
		 * Gutenberg blocks.
		 */
		require_once PN_TASKS_MANAGER_DIR . 'includes/class-pn-tasks-manager-blocks.php';

		$this->pn_tasks_manager_loader = new PN_TASKS_MANAGER_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the PN_TASKS_MANAGER_i18n class in order to set the domain and to register the hook with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function pn_tasks_manager_load_i18n() {
		$plugin_i18n = new PN_TASKS_MANAGER_i18n();
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('after_setup_theme', $plugin_i18n, 'pn_tasks_manager_load_plugin_textdomain');

		if (class_exists('Polylang')) {
			$this->pn_tasks_manager_loader->pn_tasks_manager_add_filter('pll_get_post_types', $plugin_i18n, 'pn_tasks_manager_pll_get_post_types', 10, 2);
    }
	}

	/**
	 * Register all of the hooks related to the main functionalities of the plugin, common to public and admin faces.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function pn_tasks_manager_define_common_hooks() {
		$plugin_common = new PN_TASKS_MANAGER_Common(self::pn_tasks_manager_get_plugin_name(), self::pn_tasks_manager_get_version());
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_enqueue_scripts', $plugin_common, 'pn_tasks_manager_enqueue_styles');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_enqueue_scripts', $plugin_common, 'pn_tasks_manager_enqueue_scripts');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('admin_enqueue_scripts', $plugin_common, 'pn_tasks_manager_enqueue_styles');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('admin_enqueue_scripts', $plugin_common, 'pn_tasks_manager_enqueue_scripts');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_filter('body_class', $plugin_common, 'pn_tasks_manager_body_classes');

		$plugin_post_type_task = new PN_TASKS_MANAGER_Post_Type_Task();
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('pn_tasks_manager_form_save', $plugin_post_type_task, 'pn_tasks_manager_task_form_save', 999, 5);
    // Keep Gutenberg enabled while hiding public REST endpoints of the task CPT
    $this->pn_tasks_manager_loader->pn_tasks_manager_add_filter('rest_endpoints', $plugin_post_type_task, 'pn_tasks_manager_hide_task_rest_endpoints');
    
    // Schedule cron job for resetting repeated tasks
    if (!wp_next_scheduled('pn_tasks_manager_reset_repeated_tasks')) {
      wp_schedule_event(time(), 'hourly', 'pn_tasks_manager_reset_repeated_tasks');
    }
    $this->pn_tasks_manager_loader->pn_tasks_manager_add_action('pn_tasks_manager_reset_repeated_tasks', $plugin_post_type_task, 'pn_tasks_manager_reset_repeated_tasks');
    
    // ICS download handler (available for both logged in and non-logged in users)
    $plugin_calendar = new PN_TASKS_MANAGER_Calendar();
    $this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_ajax_pn_tasks_manager_download_ics', $plugin_calendar, 'pn_tasks_manager_download_ics_handler');
    $this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_ajax_nopriv_pn_tasks_manager_download_ics', $plugin_calendar, 'pn_tasks_manager_download_ics_handler');
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function pn_tasks_manager_define_admin_hooks() {
		$plugin_admin = new PN_TASKS_MANAGER_Admin(self::pn_tasks_manager_get_plugin_name(), self::pn_tasks_manager_get_version());
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('admin_enqueue_scripts', $plugin_admin, 'pn_tasks_manager_enqueue_styles');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('admin_enqueue_scripts', $plugin_admin, 'pn_tasks_manager_enqueue_scripts');
    // Admin notice if MailPN is not active
    $this->pn_tasks_manager_loader->pn_tasks_manager_add_action('admin_notices', $plugin_admin, 'pn_tasks_manager_mailpn_notice');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function pn_tasks_manager_define_public_hooks() {
		$plugin_public = new PN_TASKS_MANAGER_Public(self::pn_tasks_manager_get_plugin_name(), self::pn_tasks_manager_get_version());
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_enqueue_scripts', $plugin_public, 'pn_tasks_manager_enqueue_styles');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_enqueue_scripts', $plugin_public, 'pn_tasks_manager_enqueue_scripts');

		$plugin_user = new PN_TASKS_MANAGER_Functions_User();
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_login', $plugin_user, 'pn_tasks_manager_user_wp_login');

		// Blocks (register and editor assets)
		$plugin_blocks = new PN_TASKS_MANAGER_Blocks();
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('init', $plugin_blocks, 'register_blocks');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('enqueue_block_editor_assets', $plugin_blocks, 'enqueue_editor_assets');
	}

	/**
	 * Register all Post Types with meta boxes and templates.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function pn_tasks_manager_define_custom_post_types() {
		$plugin_post_type_task = new PN_TASKS_MANAGER_Post_Type_Task();
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('init', $plugin_post_type_task, 'pn_tasks_manager_task_register_post_type');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('admin_init', $plugin_post_type_task, 'pn_tasks_manager_task_add_meta_box');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('save_post_pn_tasks_task', $plugin_post_type_task, 'pn_tasks_manager_task_save_post', 10, 3);
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_filter('single_template', $plugin_post_type_task, 'pn_tasks_manager_task_single_template', 10, 3);
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_filter('archive_template', $plugin_post_type_task, 'pn_tasks_manager_task_archive_template', 10, 3);
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_shortcode('pn-tasks-manager-task-list', $plugin_post_type_task, 'pn_tasks_manager_task_list_wrapper');
	}

	/**
	 * Register all of the hooks related to Taxonomies.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function pn_tasks_manager_define_taxonomies() {
		$plugin_taxonomies_task = new PN_TASKS_MANAGER_Taxonomies_Task();
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('init', $plugin_taxonomies_task, 'pn_tasks_manager_register_taxonomies');
	}

	/**
	 * Load most common data used on the platform.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function pn_tasks_manager_load_data() {
		$plugin_data = new PN_TASKS_MANAGER_Data();

		if (is_admin()) {
			$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('init', $plugin_data, 'pn_tasks_manager_load_plugin_data');
		} else {
			$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_head', $plugin_data, 'pn_tasks_manager_load_plugin_data');
		}

		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_footer', $plugin_data, 'pn_tasks_manager_flush_rewrite_rules');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('admin_footer', $plugin_data, 'pn_tasks_manager_flush_rewrite_rules');
	}

	/**
	 * Register templates.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function pn_tasks_manager_load_templates() {
		if (!defined('DOING_AJAX')) {
			$plugin_templates = new PN_TASKS_MANAGER_Templates();
			$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_footer', $plugin_templates, 'load_plugin_templates');
			$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('admin_footer', $plugin_templates, 'load_plugin_templates');
		}
	}

	/**
	 * Register settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function pn_tasks_manager_load_settings() {
		$plugin_settings = new PN_TASKS_MANAGER_Settings();
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('admin_menu', $plugin_settings, 'pn_tasks_manager_admin_menu');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('activated_plugin', $plugin_settings, 'pn_tasks_manager_activated_plugin');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('admin_init', $plugin_settings, 'pn_tasks_manager_check_activation');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_filter('plugin_action_links_pn-tasks-manager/pn-tasks-manager.php', $plugin_settings, 'pn_tasks_manager_plugin_action_links');
	}

	/**
	 * Load ajax functions.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function pn_tasks_manager_load_ajax() {
		$plugin_ajax = new PN_TASKS_MANAGER_Ajax();
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_ajax_pn_tasks_manager_ajax', $plugin_ajax, 'pn_tasks_manager_ajax_server');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_ajax_pn_tasks_manager_create_taxonomy_term', $plugin_ajax, 'pn_tasks_manager_create_taxonomy_term_ajax');
	}

	/**
	 * Load no private ajax functions.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function pn_tasks_manager_load_ajax_nopriv() {
		$plugin_ajax_nopriv = new PN_TASKS_MANAGER_Ajax_Nopriv();
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_ajax_pn_tasks_manager_ajax_nopriv', $plugin_ajax_nopriv, 'pn_tasks_manager_ajax_nopriv_server');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_action('wp_ajax_nopriv_pn_tasks_manager_ajax_nopriv', $plugin_ajax_nopriv, 'pn_tasks_manager_ajax_nopriv_server');
	}

	/**
	 * Register shortcodes of the platform.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function pn_tasks_manager_load_shortcodes() {
		$plugin_shortcodes = new PN_TASKS_MANAGER_Shortcodes();
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_shortcode('pn-tasks-manager-task', $plugin_shortcodes, 'pn_tasks_manager_task');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_shortcode('pn-tasks-manager-test', $plugin_shortcodes, 'pn_tasks_manager_test');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_shortcode('pn-tasks-manager-call-to-action', $plugin_shortcodes, 'pn_tasks_manager_call_to_action');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_shortcode('pn-tasks-manager-calendar', $plugin_shortcodes, 'pn_tasks_manager_calendar');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_shortcode('pn-tasks-manager-joinable-tasks', $plugin_shortcodes, 'pn_tasks_manager_joinable_tasks');
		$this->pn_tasks_manager_loader->pn_tasks_manager_add_shortcode('pn-tasks-manager-users-ranking', $plugin_shortcodes, 'pn_tasks_manager_users_ranking');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress. Then it flushes the rewrite rules if needed.
	 *
	 * @since    1.0.0
	 */
	public function pn_tasks_manager_run() {
		$this->pn_tasks_manager_loader->pn_tasks_manager_run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function pn_tasks_manager_get_plugin_name() {
		return $this->pn_tasks_manager_plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    PN_TASKS_MANAGER_Loader    Orchestrates the hooks of the plugin.
	 */
	public function pn_tasks_manager_get_loader() {
		return $this->pn_tasks_manager_loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function pn_tasks_manager_get_version() {
		return $this->pn_tasks_manager_version;
	}
}