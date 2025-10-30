<?php
/**
 * Class TASKSPN_Popups
 * Handles popup functionality for the TASKSPN plugin
 */
class TASKSPN_Popups {
    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * Main TASKSPN_Popups Instance
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
            'id' => uniqid('taskspn-popup-'),
            'class' => '',
            'closeButton' => true,
            'overlayClose' => true,
            'escClose' => true
        );

        $options = wp_parse_args($options, $defaults);

        ob_start();
        ?>
        <div id="<?php echo esc_attr($options['id']); ?>" class="taskspn-popup taskspn-display-none-soft <?php echo esc_attr($options['class']); ?>">
            <div class="taskspn-popup-overlay"></div>
            <div class="taskspn-popup-content">
                <?php if ($options['closeButton']) : ?>
                    <button type="button" class="taskspn-popup-close-wrapper"><i class="material-icons-outlined">close</i></button>
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
            ? "TASKSPN_Popups.close('" . esc_js($id) . "');"
            : "TASKSPN_Popups.close();";
            
        wp_add_inline_script('taskspn-popups', $script);
        return '';
    }
} 