<?php
/**
 * Class PN_TASKS_MANAGER_Popups
 * Handles popup functionality for the PN TASKS MANAGER plugin
 */
class PN_TASKS_MANAGER_Popups {
    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * Main PN_TASKS_MANAGER_Popups Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Open a popup
     */
    public static function open($content, $options = array()) {
        $defaults = array(
            'id' => uniqid('pn-tasks-manager-popup-'),
            'class' => '',
            'closeButton' => true,
            'overlayClose' => true,
            'escClose' => true
        );

        $options = wp_parse_args($options, $defaults);

        ob_start();
        ?>
        <div id="<?php echo esc_attr($options['id']); ?>" class="pn-tasks-manager-popup pn-tasks-manager-display-none-soft <?php echo esc_attr($options['class']); ?>">
            <div class="pn-tasks-manager-popup-overlay"></div>
            <div class="pn-tasks-manager-popup-content">
                <?php if ($options['closeButton']) : ?>
                    <button type="button" class="pn-tasks-manager-popup-close-wrapper"><i class="material-icons-outlined">close</i></button>
                <?php endif; ?>
                <?php echo wp_kses_post($content); ?>
            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    /**
     * Close a popup
     */
    public static function close($id = null) {
        $script = $id 
            ? "PN_TASKS_MANAGER_Popups.close('" . esc_js($id) . "');"
            : "PN_TASKS_MANAGER_Popups.close();";
            
        wp_add_inline_script('pn-tasks-manager-popups', $script);
        return '';
    }
} 