<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/admin
 * @author     Padres en la Nube
 */
class PN_TASKS_MANAGER_Admin {

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
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function pn_tasks_manager_enqueue_styles() {
		wp_enqueue_style($this->plugin_name . '-admin', PN_TASKS_MANAGER_URL . 'assets/css/admin/pn-tasks-manager-admin.css', [], $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function pn_tasks_manager_enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_script($this->plugin_name . '-admin', PN_TASKS_MANAGER_URL . 'assets/js/admin/pn-tasks-manager-admin.js', ['jquery'], $this->version, false);
	}

	/**
	 * Show admin notice if MailPN is not active to inform about required email plugin
	 */
	public function pn_tasks_manager_mailpn_notice() {
		// Only show this notice on the PN Tasks Manager settings page
		if (!is_admin()) { 
			return; 
		}

		// Check current screen (more reliable when available)
		if (function_exists('get_current_screen')) {
			$screen = get_current_screen();
			if ($screen && $screen->id !== 'toplevel_page_pn_tasks_manager_options' && $screen->id !== 'pn_tasks_manager_page_pn_tasks_manager_options') {
				return;
			}
		} else {
			// Fallback to checking the "page" query arg
			if (!isset($_GET['page']) || $_GET['page'] !== 'pn_tasks_manager_options') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}
		}

		if (!current_user_can('activate_plugins')) { 
			return; 
		}
		
		// If MailPN available, no notice
		if (class_exists('MAILPN_Mailing') || shortcode_exists('mailpn-sender')) { return; }

		$install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=mailpn'), 'install-plugin_mailpn');
		$plugins_url = admin_url('plugin-install.php?tab=search&type=term&s=mailpn');
		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php echo esc_html__('PN Tasks Manager notice:', 'pn-tasks-manager'); ?></strong>
				<?php echo esc_html__('To send task assignment emails, please install and activate', 'pn-tasks-manager'); ?>
				<a href="<?php echo esc_url('https://wordpress.org/plugins/mailpn'); ?>" target="_blank" rel="noopener noreferrer">Mailing Manager – PN</a>.
			</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url($install_url); ?>"><?php echo esc_html__('Install MailPN', 'pn-tasks-manager'); ?></a>
				<a class="button" href="<?php echo esc_url($plugins_url); ?>"><?php echo esc_html__('Search in plugins', 'pn-tasks-manager'); ?></a>
			</p>
		</div>
		<?php
	}
}
