<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin so that it is ready for translation.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    TASKSPN
 * @subpackage TASKSPN/includes
 * @author     Padres en la Nube
 */
class TASKSPN_Data {
	/**
	 * The main data array.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      TASKSPN_Data    $data    Empty array.
	 */
	protected $data = [];

	/**
	 * Load the plugin most usefull data.
	 *
	 * @since    1.0.0
	 */
	public function taskspn_load_plugin_data() {
		$this->data['user_id'] = get_current_user_id();

		if (is_admin()) {
			$this->data['post_id'] = !empty($GLOBALS['_REQUEST']['post']) ? $GLOBALS['_REQUEST']['post'] : 0;
		} else {
			$this->data['post_id'] = get_the_ID();
		}

		$GLOBALS['taskspn_data'] = $this->data;
	}

	/**
	 * Flush wp rewrite rules.
	 *
	 * @since    1.0.0
	 */
	public function taskspn_flush_rewrite_rules() {
    if (get_option('taskspn_options_changed')) {
      flush_rewrite_rules();
      update_option('taskspn_options_changed', false);
    }
  }

  /**
	 * Gets the mini loader.
	 *
	 * @since    1.0.0
	 */
	public static function taskspn_loader($display = false) {
		?>
			<div class="taskspn-waiting <?php echo ($display) ? 'taskspn-display-block' : 'taskspn-display-none'; ?>">
				<div class="taskspn-loader-circle-waiting"><div></div><div></div><div></div><div></div></div>
			</div>
		<?php
  }

  /**
	 * Load popup loader.
	 *
	 * @since    1.0.0
	 */
	public static function taskspn_popup_loader() {
		?>
			<div class="taskspn-popup-content">
				<div class="taskspn-loader-circle-wrapper"><div class="taskspn-text-align-center"><div class="taskspn-loader-circle"><div></div><div></div><div></div><div></div></div></div></div>
			</div>
		<?php
	}
}