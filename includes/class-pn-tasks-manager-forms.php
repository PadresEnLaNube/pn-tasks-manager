<?php
/**
 * Fired from activate() function.
 *
 * This class defines all post types necessary to run during the plugin's life cycle.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/includes
 * @author     Padres en la Nube
 */
class PN_TASKS_MANAGER_Forms {
  /**
   * Plaform forms.
   *
   * @since    1.0.0
   */

  /**
   * Get the current value of a field based on its type and storage
   * 
   * @param string $field_id The field ID
   * @param string $pn_tasks_manager_type The type of field (user, post, option)
   * @param int $pn_tasks_manager_id The ID of the user/post/option
   * @param int $pn_tasks_manager_meta_array Whether the field is part of a meta array
   * @param int $pn_tasks_manager_array_index The index in the meta array
   * @param array $pn_tasks_manager_input The input array containing field configuration
   * @return mixed The current value of the field
   */
  public static function pn_tasks_manager_get_field_value($field_id, $pn_tasks_manager_type, $pn_tasks_manager_id = 0, $pn_tasks_manager_meta_array = 0, $pn_tasks_manager_array_index = 0, $pn_tasks_manager_input = []) {
    $current_value = '';

    if ($pn_tasks_manager_meta_array) {
      switch ($pn_tasks_manager_type) {
        case 'user':
          $meta = get_user_meta($pn_tasks_manager_id, $field_id, true);
          if (is_array($meta) && isset($meta[$pn_tasks_manager_array_index])) {
            $current_value = $meta[$pn_tasks_manager_array_index];
          }
          break;
        case 'post':
          $meta = get_post_meta($pn_tasks_manager_id, $field_id, true);
          if (is_array($meta) && isset($meta[$pn_tasks_manager_array_index])) {
            $current_value = $meta[$pn_tasks_manager_array_index];
          }
          break;
        case 'option':
          $option = get_option($field_id);
          if (is_array($option) && isset($option[$pn_tasks_manager_array_index])) {
            $current_value = $option[$pn_tasks_manager_array_index];
          }
          break;
      }
    } else {
      switch ($pn_tasks_manager_type) {
        case 'user':
          $current_value = get_user_meta($pn_tasks_manager_id, $field_id, true);
          break;
        case 'post':
          $current_value = get_post_meta($pn_tasks_manager_id, $field_id, true);
          break;
        case 'option':
          $current_value = get_option($field_id);
          break;
      }
    }

    // If no value is found and there's a default value in the input config, use it
    // BUT NOT for checkboxes in multiple fields, as empty string and 'off' are valid states (unchecked)
    if (empty($current_value) && !empty($pn_tasks_manager_input['value'])) {
      // For checkboxes in multiple fields, don't override empty values or 'off' with default
      if (!($pn_tasks_manager_meta_array && isset($pn_tasks_manager_input['type']) && $pn_tasks_manager_input['type'] === 'checkbox')) {
        $current_value = $pn_tasks_manager_input['value'];
      }
    }
    
    // For checkboxes in multiple fields, normalize 'off' to empty string for display
    if ($pn_tasks_manager_meta_array && isset($pn_tasks_manager_input['type']) && $pn_tasks_manager_input['type'] === 'checkbox' && $current_value === 'off') {
      $current_value = '';
    }

    return $current_value;
  }

  public static function pn_tasks_manager_input_builder($pn_tasks_manager_input, $pn_tasks_manager_type, $pn_tasks_manager_id = 0, $disabled = 0, $pn_tasks_manager_meta_array = 0, $pn_tasks_manager_array_index = 0) {
    // Get the current value using the new function
    $pn_tasks_manager_value = self::pn_tasks_manager_get_field_value($pn_tasks_manager_input['id'], $pn_tasks_manager_type, $pn_tasks_manager_id, $pn_tasks_manager_meta_array, $pn_tasks_manager_array_index, $pn_tasks_manager_input);

    $pn_tasks_manager_parent_block = (!empty($pn_tasks_manager_input['parent']) ? 'data-pn-tasks-manager-parent="' . $pn_tasks_manager_input['parent'] . '"' : '') . ' ' . (!empty($pn_tasks_manager_input['parent_option']) ? 'data-pn-tasks-manager-parent-option="' . $pn_tasks_manager_input['parent_option'] . '"' : '');

    switch ($pn_tasks_manager_input['input']) {
      case 'input':        
        switch ($pn_tasks_manager_input['type']) {
          case 'file':
            ?>
              <?php if (empty($pn_tasks_manager_value)): ?>
                <p class="pn-tasks-manager-m-10"><?php esc_html_e('No file found', 'pn-tasks-manager'); ?></p>
              <?php else: ?>
                <p class="pn-tasks-manager-m-10">
                  <a href="<?php echo esc_url(get_post_meta($pn_tasks_manager_id, $pn_tasks_manager_input['id'], true)['url']); ?>" target="_blank"><?php echo esc_html(basename(get_post_meta($pn_tasks_manager_id, $pn_tasks_manager_input['id'], true)['url'])); ?></a>
                </p>
              <?php endif ?>
            <?php
            break;
          case 'checkbox':
            ?>
              <label class="pn-tasks-manager-switch">
                <input id="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? '[]' : ''); ?>" class="<?php echo array_key_exists('class', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['class']) : ''; ?> pn-tasks-manager-checkbox pn-tasks-manager-checkbox-switch pn-tasks-manager-field" type="<?php echo esc_attr($pn_tasks_manager_input['type']); ?>" <?php echo $pn_tasks_manager_value == 'on' ? 'checked="checked"' : ''; ?> <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> <?php echo ((array_key_exists('required', $pn_tasks_manager_input) && $pn_tasks_manager_input['required'] == true) ? 'required' : ''); ?> <?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple'] ? 'multiple' : ''); ?> <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>>
                <span class="pn-tasks-manager-slider pn-tasks-manager-round"></span>
              </label>
            <?php
            break;
          case 'radio':
            ?>
              <div class="pn-tasks-manager-input-radio-wrapper">
                <?php if (!empty($pn_tasks_manager_input['radio_options'])): ?>
                  <?php foreach ($pn_tasks_manager_input['radio_options'] as $radio_option): ?>
                    <div class="pn-tasks-manager-input-radio-item">
                      <label for="<?php echo esc_attr($radio_option['id']); ?>">
                        <?php echo wp_kses_post(wp_specialchars_decode($radio_option['label'])); ?>
                        
                        <input type="<?php echo esc_attr($pn_tasks_manager_input['type']); ?>"
                          id="<?php echo esc_attr($radio_option['id']); ?>"
                          name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>"
                          value="<?php echo esc_attr($radio_option['value']); ?>"
                          <?php echo $pn_tasks_manager_value == $radio_option['value'] ? 'checked="checked"' : ''; ?>
                          <?php echo ((array_key_exists('required', $pn_tasks_manager_input) && $pn_tasks_manager_input['required'] == 'true') ? 'required' : ''); ?>>

                        <div class="pn-tasks-manager-radio-control"></div>
                      </label>
                    </div>
                  <?php endforeach ?>
                <?php endif ?>
              </div>
            <?php
            break;
          case 'range':
            ?>
              <div class="pn-tasks-manager-input-range-wrapper">
                <div class="pn-tasks-manager-width-100-percent">
                  <?php if (!empty($pn_tasks_manager_input['pn_tasks_manager_label_min'])): ?>
                    <p class="pn-tasks-manager-input-range-label-min"><?php echo esc_html($pn_tasks_manager_input['pn_tasks_manager_label_min']); ?></p>
                  <?php endif ?>

                  <?php if (!empty($pn_tasks_manager_input['pn_tasks_manager_label_max'])): ?>
                    <p class="pn-tasks-manager-input-range-label-max"><?php echo esc_html($pn_tasks_manager_input['pn_tasks_manager_label_max']); ?></p>
                  <?php endif ?>
                </div>

                <input type="<?php echo esc_attr($pn_tasks_manager_input['type']); ?>" id="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? '[]' : ''); ?>" class="pn-tasks-manager-input-range <?php echo array_key_exists('class', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['class']) : ''; ?>" <?php echo ((array_key_exists('required', $pn_tasks_manager_input) && $pn_tasks_manager_input['required'] == true) ? 'required' : ''); ?> <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> <?php echo (isset($pn_tasks_manager_input['pn_tasks_manager_max']) ? 'max=' . esc_attr($pn_tasks_manager_input['pn_tasks_manager_max']) : ''); ?> <?php echo (isset($pn_tasks_manager_input['pn_tasks_manager_min']) ? 'min=' . esc_attr($pn_tasks_manager_input['pn_tasks_manager_min']) : ''); ?> <?php echo (((array_key_exists('step', $pn_tasks_manager_input) && $pn_tasks_manager_input['step'] != '')) ? 'step="' . esc_attr($pn_tasks_manager_input['step']) . '"' : ''); ?> <?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple'] ? 'multiple' : ''); ?> value="<?php echo (!empty($pn_tasks_manager_input['button_text']) ? esc_html($pn_tasks_manager_input['button_text']) : esc_html($pn_tasks_manager_value)); ?>"/>
                <h3 class="pn-tasks-manager-input-range-output"></h3>
              </div>
            <?php
            break;
          case 'stars':
            $pn_tasks_manager_stars = !empty($pn_tasks_manager_input['stars_number']) ? $pn_tasks_manager_input['stars_number'] : 5;
            ?>
              <div class="pn-tasks-manager-input-stars-wrapper">
                <div class="pn-tasks-manager-width-100-percent">
                  <?php if (!empty($pn_tasks_manager_input['pn_tasks_manager_label_min'])): ?>
                    <p class="pn-tasks-manager-input-stars-label-min"><?php echo esc_html($pn_tasks_manager_input['pn_tasks_manager_label_min']); ?></p>
                  <?php endif ?>

                  <?php if (!empty($pn_tasks_manager_input['pn_tasks_manager_label_max'])): ?>
                    <p class="pn-tasks-manager-input-stars-label-max"><?php echo esc_html($pn_tasks_manager_input['pn_tasks_manager_label_max']); ?></p>
                  <?php endif ?>
                </div>

                <div class="pn-tasks-manager-input-stars pn-tasks-manager-text-align-center pn-tasks-manager-pt-20">
                  <?php foreach (range(1, $pn_tasks_manager_stars) as $index => $star): ?>
                    <i class="material-icons-outlined pn-tasks-manager-input-star">
                      <?php echo ($index < intval($pn_tasks_manager_value)) ? 'star' : 'star_outlined'; ?>
                    </i>
                  <?php endforeach ?>
                </div>

                <input type="number" <?php echo ((array_key_exists('required', $pn_tasks_manager_input) && $pn_tasks_manager_input['required'] == true) ? 'required' : ''); ?> <?php echo ((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') ? 'disabled' : ''); ?> id="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? '[]' : ''); ?>" class="pn-tasks-manager-input-hidden-stars <?php echo array_key_exists('class', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['class']) : ''; ?>" min="1" max="<?php echo esc_attr($pn_tasks_manager_stars) ?>" value="<?php echo esc_attr($pn_tasks_manager_value); ?>">
              </div>
            <?php
            break;
          case 'submit':
            ?>
              <div class="pn-tasks-manager-text-align-right">
                <input type="submit" value="<?php echo esc_attr($pn_tasks_manager_input['value']); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" class="pn-tasks-manager-btn" data-pn-tasks-manager-type="<?php echo esc_attr($pn_tasks_manager_type); ?>" data-pn-tasks-manager-subtype="<?php echo ((array_key_exists('subtype', $pn_tasks_manager_input)) ? esc_attr($pn_tasks_manager_input['subtype']) : ''); ?>" data-pn-tasks-manager-user-id="<?php echo esc_attr($pn_tasks_manager_id); ?>" data-pn-tasks-manager-post-id="<?php echo !empty(get_the_ID()) ? esc_attr(get_the_ID()) : ''; ?>"/><?php esc_html(PN_TASKS_MANAGER_Data::pn_tasks_manager_loader()); ?>
              </div>
            <?php
            break;
          case 'hidden':
            ?>
              <input type="hidden" id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" value="<?php echo esc_attr($pn_tasks_manager_value); ?>" <?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple'] == 'true' ? 'multiple' : ''); ?>>
            <?php
            break;
          case 'nonce':
            ?>
              <input type="hidden" id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" value="<?php echo esc_attr(wp_create_nonce('pn-tasks-manager-nonce')); ?>">
            <?php
            break;
          case 'password':
            ?>
              <div class="pn-tasks-manager-password-checker">
                <div class="pn-tasks-manager-password-input pn-tasks-manager-position-relative">
                  <input id="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple'] == 'true') ? '[]' : ''); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple'] == 'true') ? '[]' : ''); ?>" <?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple'] == 'true' ? 'multiple' : ''); ?> class="pn-tasks-manager-field pn-tasks-manager-password-strength <?php echo array_key_exists('class', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['class']) : ''; ?>" type="<?php echo esc_attr($pn_tasks_manager_input['type']); ?>" <?php echo ((array_key_exists('required', $pn_tasks_manager_input) && $pn_tasks_manager_input['required'] == 'true') ? 'required' : ''); ?> <?php echo ((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') ? 'disabled' : ''); ?> value="<?php echo (!empty($pn_tasks_manager_input['button_text']) ? esc_html($pn_tasks_manager_input['button_text']) : esc_attr($pn_tasks_manager_value)); ?>" placeholder="<?php echo (array_key_exists('placeholder', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['placeholder']) : ''); ?>" <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>/>

                  <a href="#" class="pn-tasks-manager-show-pass pn-tasks-manager-cursor-pointer pn-tasks-manager-display-none-soft">
                    <i class="material-icons-outlined pn-tasks-manager-font-size-20">visibility</i>
                  </a>
                </div>

                <div id="pn-tasks-manager-popover-pass" class="pn-tasks-manager-display-none-soft">
                  <div class="pn-tasks-manager-progress-bar-wrapper">
                    <div class="pn-tasks-manager-password-strength-bar"></div>
                  </div>

                  <h3 class="pn-tasks-manager-mt-20"><?php esc_html_e('Password strength checker', 'pn-tasks-manager'); ?> <i class="material-icons-outlined pn-tasks-manager-cursor-pointer pn-tasks-manager-close-icon pn-tasks-manager-mt-30">close</i></h3>
                  <ul class="pn-tasks-manager-list-style-none">
                    <li class="low-upper-case">
                      <i class="material-icons-outlined pn-tasks-manager-font-size-20 pn-tasks-manager-vertical-align-middle">radio_button_unchecked</i>
                      <span><?php esc_html_e('Lowercase & Uppercase', 'pn-tasks-manager'); ?></span>
                    </li>
                    <li class="one-number">
                      <i class="material-icons-outlined pn-tasks-manager-font-size-20 pn-tasks-manager-vertical-align-middle">radio_button_unchecked</i>
                      <span><?php esc_html_e('Number (0-9)', 'pn-tasks-manager'); ?></span>
                    </li>
                    <li class="one-special-char">
                      <i class="material-icons-outlined pn-tasks-manager-font-size-20 pn-tasks-manager-vertical-align-middle">radio_button_unchecked</i>
                      <span><?php esc_html_e('Special Character (!@#$%^&*)', 'pn-tasks-manager'); ?></span>
                    </li>
                    <li class="eight-character">
                      <i class="material-icons-outlined pn-tasks-manager-font-size-20 pn-tasks-manager-vertical-align-middle">radio_button_unchecked</i>
                      <span><?php esc_html_e('Atleast 8 Character', 'pn-tasks-manager'); ?></span>
                    </li>
                  </ul>
                </div>
              </div>
            <?php
            break;
          case 'color':
            ?>
              <input id="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? '[]' : ''); ?>" <?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple'] ? 'multiple' : ''); ?> class="pn-tasks-manager-field <?php echo array_key_exists('class', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['class']) : ''; ?>" type="<?php echo esc_attr($pn_tasks_manager_input['type']); ?>" <?php echo ((array_key_exists('required', $pn_tasks_manager_input) && $pn_tasks_manager_input['required'] == true) ? 'required' : ''); ?> <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> value="<?php echo (!empty($pn_tasks_manager_value) ? esc_attr($pn_tasks_manager_value) : '#000000'); ?>" placeholder="<?php echo (array_key_exists('placeholder', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['placeholder']) : ''); ?>" <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>/>
            <?php
            break;
          default:
            ?>
              <input 
                <?php /* ID and name attributes */ ?>
                id="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? '[]' : ''); ?>" 
                name="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? '[]' : ''); ?>"
                
                <?php /* Type and styling */ ?>
                class="pn-tasks-manager-field <?php echo array_key_exists('class', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['class']) : ''; ?>" 
                type="<?php echo esc_attr($pn_tasks_manager_input['type']); ?>"
                
                <?php /* State attributes */ ?>
                <?php echo ((array_key_exists('required', $pn_tasks_manager_input) && $pn_tasks_manager_input['required'] == true) ? 'required' : ''); ?>
                <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
                <?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple'] ? 'multiple' : ''); ?>
                
                <?php /* Validation and limits */ ?>
                <?php echo (((array_key_exists('step', $pn_tasks_manager_input) && $pn_tasks_manager_input['step'] != '')) ? 'step="' . esc_attr($pn_tasks_manager_input['step']) . '"' : ''); ?>
                <?php echo (isset($pn_tasks_manager_input['max']) ? 'max="' . esc_attr($pn_tasks_manager_input['max']) . '"' : ''); ?>
                <?php echo (isset($pn_tasks_manager_input['min']) ? 'min="' . esc_attr($pn_tasks_manager_input['min']) . '"' : ''); ?>
                <?php echo (isset($pn_tasks_manager_input['maxlength']) ? 'maxlength="' . esc_attr($pn_tasks_manager_input['maxlength']) . '"' : ''); ?>
                <?php echo (isset($pn_tasks_manager_input['pattern']) ? 'pattern="' . esc_attr($pn_tasks_manager_input['pattern']) . '"' : ''); ?>
                
                <?php /* Content attributes */ ?>
                value="<?php echo (!empty($pn_tasks_manager_input['button_text']) ? esc_html($pn_tasks_manager_input['button_text']) : esc_html($pn_tasks_manager_value)); ?>"
                placeholder="<?php echo (array_key_exists('placeholder', $pn_tasks_manager_input) ? esc_html($pn_tasks_manager_input['placeholder']) : ''); ?>"
                
                <?php /* Custom data attributes */ ?>
                <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>
              />
            <?php
            break;
        }
        break;
      case 'select':
        if (!empty($pn_tasks_manager_input['options']) && is_array($pn_tasks_manager_input['options'])) {
          ?>
          <select 
            id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" 
            name="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? '[]' : ''); ?>" 
            class="pn-tasks-manager-field <?php echo array_key_exists('class', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['class']) : ''; ?>"
            <?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? 'multiple' : ''; ?>
            <?php echo ((array_key_exists('required', $pn_tasks_manager_input) && $pn_tasks_manager_input['required'] == true) ? 'required' : ''); ?>
            <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
            <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>
          >
            <?php if (array_key_exists('placeholder', $pn_tasks_manager_input) && !empty($pn_tasks_manager_input['placeholder'])): ?>
              <option value=""><?php echo esc_html($pn_tasks_manager_input['placeholder']); ?></option>
            <?php endif; ?>
            
            <?php 
            $selected_values = array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple'] ? 
              (is_array($pn_tasks_manager_value) ? $pn_tasks_manager_value : array()) : 
              array($pn_tasks_manager_value);
            
            foreach ($pn_tasks_manager_input['options'] as $value => $label): 
              $is_selected = in_array($value, $selected_values);
            ?>
              <option 
                value="<?php echo esc_attr($value); ?>"
                <?php echo $is_selected ? 'selected="selected"' : ''; ?>
              >
                <?php echo esc_html($label); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php
        }
        break;
      case 'textarea':
        ?>
          <textarea id="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']) . ((array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? '[]' : ''); ?>" <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?> class="pn-tasks-manager-field <?php echo array_key_exists('class', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['class']) : ''; ?>" <?php echo ((array_key_exists('required', $pn_tasks_manager_input) && $pn_tasks_manager_input['required'] == true) ? 'required' : ''); ?> <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> <?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple'] ? 'multiple' : ''); ?> placeholder="<?php echo (array_key_exists('placeholder', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['placeholder']) : ''); ?>"><?php echo esc_html($pn_tasks_manager_value); ?></textarea>
        <?php
        break;
      case 'image':
        ?>
          <div class="pn-tasks-manager-field pn-tasks-manager-images-block" <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?> data-pn-tasks-manager-multiple="<?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? 'true' : 'false'; ?>">
            <?php if (!empty($pn_tasks_manager_value)): ?>
              <div class="pn-tasks-manager-images">
                <?php foreach (explode(',', $pn_tasks_manager_value) as $pn_tasks_manager_image): ?>
                  <?php echo wp_get_attachment_image($pn_tasks_manager_image, 'medium'); ?>
                <?php endforeach ?>
              </div>

              <div class="pn-tasks-manager-text-align-center pn-tasks-manager-position-relative"><a href="#" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-image-btn"><?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? esc_html(__('Edit images', 'pn-tasks-manager')) : esc_html(__('Edit image', 'pn-tasks-manager')); ?></a></div>
            <?php else: ?>
              <div class="pn-tasks-manager-images"></div>

              <div class="pn-tasks-manager-text-align-center pn-tasks-manager-position-relative"><a href="#" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-image-btn"><?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? esc_html(__('Add images', 'pn-tasks-manager')) : esc_html(__('Add image', 'pn-tasks-manager')); ?></a></div>
            <?php endif ?>

            <input id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" class="pn-tasks-manager-display-none pn-tasks-manager-image-input" type="text" value="<?php echo esc_attr($pn_tasks_manager_value); ?>"/>
          </div>
        <?php
        break;
      case 'video':
        ?>
        <div class="pn-tasks-manager-field pn-tasks-manager-videos-block" <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>>
            <?php if (!empty($pn_tasks_manager_value)): ?>
              <div class="pn-tasks-manager-videos">
                <?php foreach (explode(',', $pn_tasks_manager_value) as $pn_tasks_manager_video): ?>
                  <div class="pn-tasks-manager-video pn-tasks-manager-tooltip" title="<?php echo esc_html(get_the_title($pn_tasks_manager_video)); ?>"><i class="dashicons dashicons-media-video"></i></div>
                <?php endforeach ?>
              </div>

              <div class="pn-tasks-manager-text-align-center pn-tasks-manager-position-relative"><a href="#" class="pn-tasks-manager-btn pn-tasks-manager-video-btn"><?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? esc_html(__('Edit videos', 'pn-tasks-manager')) : esc_html(__('Edit video', 'pn-tasks-manager')); ?></a></div>
            <?php else: ?>
              <div class="pn-tasks-manager-videos"></div>

              <div class="pn-tasks-manager-text-align-center pn-tasks-manager-position-relative"><a href="#" class="pn-tasks-manager-btn pn-tasks-manager-video-btn"><?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? esc_html(__('Add videos', 'pn-tasks-manager')) : esc_html(__('Add video', 'pn-tasks-manager')); ?></a></div>
            <?php endif ?>

            <input id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" class="pn-tasks-manager-display-none pn-tasks-manager-video-input" type="text" value="<?php echo esc_attr($pn_tasks_manager_value); ?>"/>
          </div>
        <?php
        break;
      case 'audio':
        ?>
          <div class="pn-tasks-manager-field pn-tasks-manager-audios-block" <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>>
            <?php if (!empty($pn_tasks_manager_value)): ?>
              <div class="pn-tasks-manager-audios">
                <?php foreach (explode(',', $pn_tasks_manager_value) as $pn_tasks_manager_audio): ?>
                  <div class="pn-tasks-manager-audio pn-tasks-manager-tooltip" title="<?php echo esc_html(get_the_title($pn_tasks_manager_audio)); ?>"><i class="dashicons dashicons-media-audio"></i></div>
                <?php endforeach ?>
              </div>

              <div class="pn-tasks-manager-text-align-center pn-tasks-manager-position-relative"><a href="#" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-audio-btn"><?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? esc_html(__('Edit audios', 'pn-tasks-manager')) : esc_html(__('Edit audio', 'pn-tasks-manager')); ?></a></div>
            <?php else: ?>
              <div class="pn-tasks-manager-audios"></div>

              <div class="pn-tasks-manager-text-align-center pn-tasks-manager-position-relative"><a href="#" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-audio-btn"><?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? esc_html(__('Add audios', 'pn-tasks-manager')) : esc_html(__('Add audio', 'pn-tasks-manager')); ?></a></div>
            <?php endif ?>

            <input id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" class="pn-tasks-manager-display-none pn-tasks-manager-audio-input" type="text" value="<?php echo esc_attr($pn_tasks_manager_value); ?>"/>
          </div>
        <?php
        break;
      case 'file':
        ?>
          <div class="pn-tasks-manager-field pn-tasks-manager-files-block" <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>>
            <?php if (!empty($pn_tasks_manager_value)): ?>
              <div class="pn-tasks-manager-files pn-tasks-manager-text-align-center">
                <?php foreach (explode(',', $pn_tasks_manager_value) as $pn_tasks_manager_file): ?>
                  <embed src="<?php echo esc_url(wp_get_attachment_url($pn_tasks_manager_file)); ?>" type="application/pdf" class="pn-tasks-manager-embed-file"/>
                <?php endforeach ?>
              </div>

              <div class="pn-tasks-manager-text-align-center pn-tasks-manager-position-relative"><a href="#" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-file-btn"><?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? esc_html(__('Edit files', 'pn-tasks-manager')) : esc_html(__('Edit file', 'pn-tasks-manager')); ?></a></div>
            <?php else: ?>
              <div class="pn-tasks-manager-files"></div>

              <div class="pn-tasks-manager-text-align-center pn-tasks-manager-position-relative"><a href="#" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-btn-mini pn-tasks-manager-file-btn"><?php echo (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) ? esc_html(__('Add files', 'pn-tasks-manager')) : esc_html(__('Add file', 'pn-tasks-manager')); ?></a></div>
            <?php endif ?>

            <input id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" class="pn-tasks-manager-display-none pn-tasks-manager-file-input pn-tasks-manager-btn-mini" type="text" value="<?php echo esc_attr($pn_tasks_manager_value); ?>"/>
          </div>
        <?php
        break;
      case 'editor':
        ?>
          <div class="pn-tasks-manager-field" <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>>
            <textarea id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" class="pn-tasks-manager-input pn-tasks-manager-width-100-percent pn-tasks-manager-wysiwyg"><?php echo ((empty($pn_tasks_manager_value)) ? (array_key_exists('placeholder', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['placeholder']) : '') : esc_html($pn_tasks_manager_value)); ?></textarea>
          </div>
        <?php
        break;
      case 'html':
        ?>
          <div class="pn-tasks-manager-field" <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>>
            <?php echo !empty($pn_tasks_manager_input['html_content']) ? wp_kses(do_shortcode($pn_tasks_manager_input['html_content']), PN_TASKS_MANAGER_KSES) : ''; ?>
          </div>
        <?php
        break;
      case 'html_multi':
        switch ($pn_tasks_manager_type) {
          case 'user':
            $html_multi_fields_length = !empty(get_user_meta($pn_tasks_manager_id, $pn_tasks_manager_input['html_multi_fields'][0]['id'], true)) ? count(get_user_meta($pn_tasks_manager_id, $pn_tasks_manager_input['html_multi_fields'][0]['id'], true)) : 0;
            break;
          case 'post':
            $html_multi_fields_length = !empty(get_post_meta($pn_tasks_manager_id, $pn_tasks_manager_input['html_multi_fields'][0]['id'], true)) ? count(get_post_meta($pn_tasks_manager_id, $pn_tasks_manager_input['html_multi_fields'][0]['id'], true)) : 0;
            break;
          case 'option':
            $html_multi_fields_length = !empty(get_option($pn_tasks_manager_input['html_multi_fields'][0]['id'])) ? count(get_option($pn_tasks_manager_input['html_multi_fields'][0]['id'])) : 0;
        }

        ?>
          <div class="pn-tasks-manager-field pn-tasks-manager-html-multi-wrapper pn-tasks-manager-mb-50" <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>>
            <?php if ($html_multi_fields_length): ?>
              <?php foreach (range(0, ($html_multi_fields_length - 1)) as $length_index): ?>
                <div class="pn-tasks-manager-html-multi-group pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                  <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-90-percent">
                    <?php foreach ($pn_tasks_manager_input['html_multi_fields'] as $index => $html_multi_field): ?>
                      <?php if (isset($html_multi_field['label']) && !empty($html_multi_field['label'])): ?>
                        <label><?php echo esc_html($html_multi_field['label']); ?></label>
                      <?php endif; ?>

                      <?php self::pn_tasks_manager_input_builder($html_multi_field, $pn_tasks_manager_type, $pn_tasks_manager_id, false, true, $length_index); ?>
                    <?php endforeach ?>
                  </div>
                  <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-10-percent pn-tasks-manager-text-align-center">
                    <i class="material-icons-outlined pn-tasks-manager-cursor-move pn-tasks-manager-multi-sorting pn-tasks-manager-vertical-align-super pn-tasks-manager-tooltip" title="<?php esc_html_e('Order element', 'pn-tasks-manager'); ?>">drag_handle</i>
                  </div>

                  <div class="pn-tasks-manager-text-align-right">
                    <a href="#" class="pn-tasks-manager-html-multi-remove-btn"><i class="material-icons-outlined pn-tasks-manager-cursor-pointer pn-tasks-manager-tooltip" title="<?php esc_html_e('Remove element', 'pn-tasks-manager'); ?>">remove</i></a>
                  </div>
                </div>
              <?php endforeach ?>
            <?php else: ?>
              <div class="pn-tasks-manager-html-multi-group pn-tasks-manager-mb-50">
                <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-90-percent">
                  <?php foreach ($pn_tasks_manager_input['html_multi_fields'] as $html_multi_field): ?>
                    <?php self::pn_tasks_manager_input_builder($html_multi_field, $pn_tasks_manager_type); ?>
                  <?php endforeach ?>
                </div>
                <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-10-percent pn-tasks-manager-text-align-center">
                  <i class="material-icons-outlined pn-tasks-manager-cursor-move pn-tasks-manager-multi-sorting pn-tasks-manager-vertical-align-super pn-tasks-manager-tooltip" title="<?php esc_html_e('Order element', 'pn-tasks-manager'); ?>">drag_handle</i>
                </div>

                <div class="pn-tasks-manager-text-align-right">
                  <a href="#" class="pn-tasks-manager-html-multi-remove-btn pn-tasks-manager-tooltip" title="<?php esc_html_e('Remove element', 'pn-tasks-manager'); ?>"><i class="material-icons-outlined pn-tasks-manager-cursor-pointer">remove</i></a>
                </div>
              </div>
            <?php endif ?>

            <div class="pn-tasks-manager-text-align-right">
              <a href="#" class="pn-tasks-manager-html-multi-add-btn pn-tasks-manager-tooltip" title="<?php esc_html_e('Add element', 'pn-tasks-manager'); ?>"><i class="material-icons-outlined pn-tasks-manager-cursor-pointer pn-tasks-manager-font-size-40">add</i></a>
            </div>
          </div>
        <?php
        break;
      case 'audio_recorder':
        // Enqueue CSS and JS files for audio recorder
        wp_enqueue_style('pn-tasks-manager-audio-recorder', PN_TASKS_MANAGER_URL . 'assets/css/pn-tasks-manager-audio-recorder.css', array(), '1.0.0');
        wp_enqueue_script('pn-tasks-manager-audio-recorder', PN_TASKS_MANAGER_URL . 'assets/js/pn-tasks-manager-audio-recorder.js', array('jquery'), '1.0.0', true);
        
        // Localize script with AJAX data
        wp_localize_script('pn-tasks-manager-audio-recorder', 'pn_tasks_manager_audio_recorder_vars', array(
          'ajax_url' => admin_url('admin-ajax.php'),
          'ajax_nonce' => wp_create_nonce('pn_tasks_manager_audio_nonce'),
        ));
        
        ?>
          <div class="pn-tasks-manager-audio-recorder-status pn-tasks-manager-display-none-soft">
            <p class="pn-tasks-manager-recording-status"><?php esc_html_e('Ready to record', 'pn-tasks-manager'); ?></p>
          </div>
          
          <div class="pn-tasks-manager-audio-recorder-wrapper">
            <div class="pn-tasks-manager-audio-recorder-controls">
              <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-text-align-center pn-tasks-manager-mb-20">
                  <button type="button" class="pn-tasks-manager-btn pn-tasks-manager-btn-transparent pn-tasks-manager-start-recording" <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                    <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle">mic</i>
                    <?php esc_html_e('Start recording', 'pn-tasks-manager'); ?>
                  </button>
                </div>

                <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-text-align-center pn-tasks-manager-mb-20">
                  <button type="button" class="pn-tasks-manager-btn pn-tasks-manager-btn-secondary pn-tasks-manager-stop-recording" style="display: none;" <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                    <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle">stop</i>
                    <?php esc_html_e('Stop recording', 'pn-tasks-manager'); ?>
                  </button>
                </div>
              </div>

              <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-text-align-center pn-tasks-manager-mb-20">
                  <button type="button" class="pn-tasks-manager-btn pn-tasks-manager-btn-secondary pn-tasks-manager-play-audio" style="display: none;" <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                    <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle">play_arrow</i>
                    <?php esc_html_e('Play audio', 'pn-tasks-manager'); ?>
                  </button>
                </div>

                <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-text-align-center pn-tasks-manager-mb-20">
                  <button type="button" class="pn-tasks-manager-btn pn-tasks-manager-btn-secondary pn-tasks-manager-stop-audio" style="display: none;" <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                    <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle">stop</i>
                    <?php esc_html_e('Stop audio', 'pn-tasks-manager'); ?>
                  </button>
                </div>
              </div>
            </div>

            <div class="pn-tasks-manager-audio-recorder-visualizer" style="display: none;">
              <canvas class="pn-tasks-manager-audio-canvas" width="300" height="60"></canvas>
            </div>

            <div class="pn-tasks-manager-audio-recorder-timer" style="display: none;">
              <span class="pn-tasks-manager-recording-time">00:00</span>
            </div>

            <div class="pn-tasks-manager-audio-transcription-controls pn-tasks-manager-display-none-soft pn-tasks-manager-display-table pn-tasks-manager-width-100-percent pn-tasks-manager-mb-20">
              <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-text-align-center">
                <button type="button" class="pn-tasks-manager-btn pn-tasks-manager-btn-transparent pn-tasks-manager-transcribe-audio" <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                  <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle">translate</i>
                  <?php esc_html_e('Transcribe Audio', 'pn-tasks-manager'); ?>
                </button>
              </div>

              <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-text-align-center">
                <button type="button" class="pn-tasks-manager-btn pn-tasks-manager-btn-secondary pn-tasks-manager-clear-transcription" <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                  <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle">clear</i>
                  <?php esc_html_e('Clear', 'pn-tasks-manager'); ?>
                </button>
              </div>
            </div>

            <div class="pn-tasks-manager-audio-transcription-loading">
              <?php echo esc_html(PN_TASKS_MANAGER_Data::pn_tasks_manager_loader()); ?>
            </div>

            <div class="pn-tasks-manager-audio-transcription-result">
              <textarea 
                id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" 
                name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" 
                class="pn-tasks-manager-field pn-tasks-manager-transcription-textarea <?php echo array_key_exists('class', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['class']) : ''; ?>" 
                placeholder="<?php echo (array_key_exists('placeholder', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['placeholder']) : esc_attr__('Transcribed text will appear here...', 'pn-tasks-manager')); ?>"
                <?php echo ((array_key_exists('required', $pn_tasks_manager_input) && $pn_tasks_manager_input['required'] == true) ? 'required' : ''); ?>
                <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
                <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>
                rows="4"
                style="width: 100%; margin-top: 10px;"
              ><?php echo esc_textarea($pn_tasks_manager_value); ?></textarea>
            </div>

            <div class="pn-tasks-manager-audio-transcription-error pn-tasks-manager-display-none-soft">
              <p class="pn-tasks-manager-error-message"></p>
            </div>

            <div class="pn-tasks-manager-audio-transcription-success pn-tasks-manager-display-none-soft">
              <p class="pn-tasks-manager-success-message"></p>
            </div>

            <!-- Hidden input to store audio data -->
            <input type="hidden" 
                  id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>_audio_data" 
                  name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>_audio_data" 
                  value="" />
          </div>
        <?php
        break;
      case 'tags':
        // Get current tags value
        $current_tags = self::pn_tasks_manager_get_field_value($pn_tasks_manager_input['id'], $pn_tasks_manager_type, $pn_tasks_manager_id, $pn_tasks_manager_meta_array, $pn_tasks_manager_array_index, $pn_tasks_manager_input);
        $tags_array = is_array($current_tags) ? $current_tags : [];
        $tags_string = implode(', ', $tags_array);
        ?>
        <div class="pn-tasks-manager-tags-wrapper" <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>>
          <input type="text" 
            id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" 
            name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" 
            class="pn-tasks-manager-field pn-tasks-manager-tags-input <?php echo array_key_exists('class', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['class']) : ''; ?>" 
            value="<?php echo esc_attr($tags_string); ?>" 
            placeholder="<?php echo (array_key_exists('placeholder', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['placeholder']) : ''); ?>"
            <?php echo ((array_key_exists('required', $pn_tasks_manager_input) && $pn_tasks_manager_input['required'] == true) ? 'required' : ''); ?>
            <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> />
          
          <div class="pn-tasks-manager-tags-suggestions" style="display: none;">
            <div class="pn-tasks-manager-tags-suggestions-list"></div>
          </div>
          
          <div class="pn-tasks-manager-tags-display">
            <?php if (!empty($tags_array)): ?>
              <?php foreach ($tags_array as $tag): ?>
                <span class="pn-tasks-manager-tag">
                  <?php echo esc_html($tag); ?>
                  <i class="material-icons-outlined pn-tasks-manager-tag-remove">close</i>
                </span>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          
          <input type="hidden" 
            id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>_tags_array" 
            name="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>_tags_array" 
            value="<?php echo esc_attr(json_encode($tags_array)); ?>" />
        </div>
        <?php
        break;
      case 'taxonomy':
        $taxonomy = !empty($pn_tasks_manager_input['taxonomy']) ? $pn_tasks_manager_input['taxonomy'] : 'category';
        $is_multiple = array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple'];
        
        // Get current taxonomy terms
        $current_terms = [];
        if ($pn_tasks_manager_type === 'post' && !empty($pn_tasks_manager_id)) {
          $terms = wp_get_post_terms($pn_tasks_manager_id, $taxonomy, ['fields' => 'ids']);
          $current_terms = is_array($terms) ? $terms : [];
        }
        
        // Get all terms for this taxonomy
        $all_terms = get_terms([
          'taxonomy' => $taxonomy,
          'hide_empty' => false,
        ]);
        
        // Get selected values
        $selected_values = $is_multiple ? $current_terms : (!empty($current_terms) ? [$current_terms[0]] : []);
        ?>
        <div class="pn-tasks-manager-taxonomy-wrapper" <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>>
          <select 
            id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" 
            name="<?php echo esc_attr($pn_tasks_manager_input['id']) . ($is_multiple ? '[]' : ''); ?>" 
            class="pn-tasks-manager-field pn-tasks-manager-taxonomy-select <?php echo array_key_exists('class', $pn_tasks_manager_input) ? esc_attr($pn_tasks_manager_input['class']) : ''; ?>"
            <?php echo $is_multiple ? 'multiple' : ''; ?>
            <?php echo ((array_key_exists('required', $pn_tasks_manager_input) && $pn_tasks_manager_input['required'] == true) ? 'required' : ''); ?>
            <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
            data-taxonomy="<?php echo esc_attr($taxonomy); ?>"
            data-pn-tasks-manager-allow-new="<?php echo (array_key_exists('allow_new', $pn_tasks_manager_input) && $pn_tasks_manager_input['allow_new'] == true) ? 'true' : 'false'; ?>"
          >
            <?php if (array_key_exists('placeholder', $pn_tasks_manager_input) && !empty($pn_tasks_manager_input['placeholder'])): ?>
              <option value=""><?php echo esc_html($pn_tasks_manager_input['placeholder']); ?></option>
            <?php endif; ?>
            
            <?php if (!empty($all_terms) && !is_wp_error($all_terms)): ?>
              <?php foreach ($all_terms as $term): ?>
                <?php $is_selected = in_array($term->term_id, $selected_values); ?>
                <option 
                  value="<?php echo esc_attr($term->term_id); ?>"
                  <?php echo $is_selected ? 'selected="selected"' : ''; ?>
                >
                  <?php echo esc_html($term->name); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
          
          <?php if (array_key_exists('allow_new', $pn_tasks_manager_input) && $pn_tasks_manager_input['allow_new'] == true): ?>
            <div class="pn-tasks-manager-taxonomy-add-new pn-tasks-manager-mt-10">
              <input 
                type="text" 
                class="pn-tasks-manager-taxonomy-new-name pn-tasks-manager-input pn-tasks-manager-width-100-percent" 
                placeholder="<?php echo esc_attr__('New category name', 'pn-tasks-manager'); ?>"
                <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
              />
              <button 
                type="button" 
                class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-btn-transparent pn-tasks-manager-taxonomy-add-btn"
                <?php echo (((array_key_exists('disabled', $pn_tasks_manager_input) && $pn_tasks_manager_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
              >
                <?php esc_html_e('Add', 'pn-tasks-manager'); ?>
              </button>
            </div>
          <?php endif; ?>
        </div>
        <?php
        break;
      case 'page_manager':
        if (!current_user_can('manage_options')) {
          ?><div class="pn-tasks-manager-field"><p class="pn-tasks-manager-color-error"><?php esc_html_e('You do not have permission to manage plugin pages.', 'pn-tasks-manager'); ?></p></div><?php
          break;
        }
        $page_option = isset($pn_tasks_manager_input['page_option']) ? $pn_tasks_manager_input['page_option'] : '';
        $block_name = isset($pn_tasks_manager_input['block_name']) ? $pn_tasks_manager_input['block_name'] : '';
        $page_id = !empty($page_option) ? intval(get_option($page_option)) : 0;
        $page = $page_id ? get_post($page_id) : null;
        $page_exists = $page && $page->post_status !== 'trash';
        ?>
        <div class="pn-tasks-manager-page-manager-wrapper" data-page-option="<?php echo esc_attr($page_option); ?>" data-block-name="<?php echo esc_attr($block_name); ?>">
          <?php if ($page_exists): ?>
            <div class="pn-tasks-manager-page-manager-info">
              <div class="pn-tasks-manager-page-manager-status pn-tasks-manager-mb-10">
                <i class="material-icons-outlined pn-tasks-manager-vertical-align-middle pn-tasks-manager-color-green">check_circle</i>
                <strong><?php echo esc_html($page->post_title); ?></strong>
                <span class="pn-tasks-manager-page-manager-badge pn-tasks-manager-ml-10"><?php echo esc_html(ucfirst($page->post_status)); ?></span>
              </div>
              <div class="pn-tasks-manager-page-manager-actions">
                <a href="<?php echo esc_url(get_permalink($page_id)); ?>" target="_blank" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-btn-transparent pn-tasks-manager-mr-10"><i class="material-icons-outlined pn-tasks-manager-vertical-align-middle">visibility</i> <?php esc_html_e('View', 'pn-tasks-manager'); ?></a>
                <a href="<?php echo esc_url(get_edit_post_link($page_id)); ?>" target="_blank" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-btn-transparent pn-tasks-manager-mr-10"><i class="material-icons-outlined pn-tasks-manager-vertical-align-middle">edit</i> <?php esc_html_e('Edit', 'pn-tasks-manager'); ?></a>
                <button type="button" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-btn-transparent pn-tasks-manager-page-manager-unlink-btn"><i class="material-icons-outlined pn-tasks-manager-vertical-align-middle">link_off</i> <?php esc_html_e('Unlink', 'pn-tasks-manager'); ?></button>
              </div>
            </div>
          <?php else: ?>
            <div class="pn-tasks-manager-page-manager-create">
              <div class="pn-tasks-manager-page-manager-create-form">
                <input type="text" class="pn-tasks-manager-input pn-tasks-manager-page-manager-title-input pn-tasks-manager-width-100-percent pn-tasks-manager-mb-10" placeholder="<?php esc_attr_e('Page title', 'pn-tasks-manager'); ?>" value="<?php echo esc_attr(isset($pn_tasks_manager_input['label']) ? $pn_tasks_manager_input['label'] : ''); ?>">
                <button type="button" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-page-manager-create-btn"><i class="material-icons-outlined pn-tasks-manager-vertical-align-middle">add_circle</i> <?php esc_html_e('Create page', 'pn-tasks-manager'); ?></button>
              </div>
            </div>
          <?php endif; ?>
          <div class="pn-tasks-manager-page-manager-message pn-tasks-manager-mt-10 pn-tasks-manager-display-none-soft"></div>
        </div>
        <?php
        break;
      case 'user_role_selector':
        if (!current_user_can('manage_options')) {
          ?><div class="pn-tasks-manager-field"><p class="pn-tasks-manager-color-error"><?php esc_html_e('You do not have permission to manage user roles.', 'pn-tasks-manager'); ?></p></div><?php
          break;
        }
        $users = get_users(['orderby' => 'display_name', 'order' => 'ASC']);
        $target_role = isset($pn_tasks_manager_input['role']) ? $pn_tasks_manager_input['role'] : 'pn_tasks_manager_role_manager';
        $role_label = isset($pn_tasks_manager_input['role_label']) ? $pn_tasks_manager_input['role_label'] : __('PN Tasks Manager', 'pn-tasks-manager');
        $users_with_role = array_filter($users, function ($user) use ($target_role) { return in_array($target_role, (array) $user->roles); });
        ?>
        <div class="pn-tasks-manager-user-role-selector-wrapper" <?php echo wp_kses_post($pn_tasks_manager_parent_block); ?>>
          <?php if (!empty($users_with_role)): ?>
            <div class="pn-tasks-manager-mb-20 pn-tasks-manager-p-15 pn-tasks-manager-users-with-role-box">
              <h4 class="pn-tasks-manager-mb-10"><?php echo esc_html(sprintf(__('Users with %s Role', 'pn-tasks-manager'), $role_label)); ?> <span class="pn-tasks-manager-role-badge"><?php echo count($users_with_role); ?></span></h4>
              <div class="pn-tasks-manager-users-with-role-list">
                <?php foreach ($users_with_role as $user): ?>
                  <div class="pn-tasks-manager-user-role-item"><i class="material-icons-outlined">person</i> <strong><?php echo esc_html($user->display_name); ?></strong> <span class="pn-tasks-manager-color-gray">(<?php echo esc_html($user->user_email); ?>)</span></div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="pn-tasks-manager-mb-20 pn-tasks-manager-p-15 pn-tasks-manager-alert-warning"><p><i class="material-icons-outlined pn-tasks-manager-vertical-align-middle">info</i> <?php echo esc_html(sprintf(__('No users currently have the %s role.', 'pn-tasks-manager'), $role_label)); ?></p></div>
          <?php endif; ?>
          <div class="pn-tasks-manager-mb-20">
            <label for="pn_tasks_manager_user_select_<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" class="pn-tasks-manager-mb-10 pn-tasks-manager-display-block"><?php esc_html_e('Select Users', 'pn-tasks-manager'); ?></label>
            <select id="pn_tasks_manager_user_select_<?php echo esc_attr($pn_tasks_manager_input['id']); ?>" class="pn-tasks-manager-select pn-tasks-manager-width-100-percent pn-tasks-manager-user-role-select" multiple size="10" data-role="<?php echo esc_attr($target_role); ?>" data-role-label="<?php echo esc_attr($role_label); ?>">
              <?php foreach ($users as $user): $has_role = in_array($target_role, (array) $user->roles); ?>
                <option value="<?php echo esc_attr($user->ID); ?>" <?php echo $has_role ? 'data-has-role="true"' : ''; ?>><?php echo esc_html($user->display_name . ' (' . $user->user_email . ')'); ?><?php if ($has_role): ?> ✓<?php endif; ?></option>
              <?php endforeach; ?>
            </select>
            <p class="pn-tasks-manager-font-size-small pn-tasks-manager-color-gray pn-tasks-manager-mt-5"><?php esc_html_e('Hold Ctrl (Windows) or Cmd (Mac) to select multiple users. Users with ✓ already have this role.', 'pn-tasks-manager'); ?></p>
          </div>
          <div class="pn-tasks-manager-role-actions pn-tasks-manager-mb-20">
            <input type="hidden" class="pn-tasks-manager-role-nonce" value="<?php echo esc_attr(wp_create_nonce('pn-tasks-manager-role-assignment')); ?>">
            <div class="pn-tasks-manager-display-inline-block pn-tasks-manager-mr-10"><button type="button" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-assign-role-btn" data-input-id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>"><i class="material-icons-outlined pn-tasks-manager-vertical-align-middle">person_add</i> <?php echo esc_html(sprintf(__('Assign %s Role', 'pn-tasks-manager'), $role_label)); ?></button></div>
            <div class="pn-tasks-manager-display-inline-block"><button type="button" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-remove-role-btn" data-input-id="<?php echo esc_attr($pn_tasks_manager_input['id']); ?>"><i class="material-icons-outlined pn-tasks-manager-vertical-align-middle">person_remove</i> <?php echo esc_html(sprintf(__('Remove %s Role', 'pn-tasks-manager'), $role_label)); ?></button></div>
          </div>
          <div class="pn-tasks-manager-role-message pn-tasks-manager-mt-20 pn-tasks-manager-display-none-soft"></div>
        </div>
        <?php
        break;
    }
  }

  public static function pn_tasks_manager_input_wrapper_builder($input_array, $type, $pn_tasks_manager_id = 0, $disabled = 0, $pn_tasks_manager_format = 'half'){
    ?>
      <?php if (array_key_exists('section', $input_array) && !empty($input_array['section'])): ?>      
        <?php if ($input_array['section'] == 'start'): ?>
          <div class="pn-tasks-manager-toggle-wrapper pn-tasks-manager-section-wrapper pn-tasks-manager-position-relative <?php echo array_key_exists('class', $input_array) ? esc_attr($input_array['class']) : ''; ?>" id="<?php echo array_key_exists('id', $input_array) ? esc_attr($input_array['id']) : ''; ?>">
            <a href="#" class="pn-tasks-manager-toggle pn-tasks-manager-width-100-percent pn-tasks-manager-text-decoration-none">
              <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent pn-tasks-manager-mb-20">
                <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-90-percent">
                  <label class="pn-tasks-manager-cursor-pointer pn-tasks-manager-mb-20 pn-tasks-manager-color-main-0"><?php echo wp_kses_post($input_array['label']); ?></label>
                </div>
                <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-10-percent pn-tasks-manager-text-align-right">
                  <i class="material-icons-outlined pn-tasks-manager-cursor-pointer pn-tasks-manager-color-main-0">add</i>
                </div>
              </div>
            </a>

            <div class="pn-tasks-manager-content pn-tasks-manager-pl-10 pn-tasks-manager-toggle-content pn-tasks-manager-mb-20 pn-tasks-manager-display-none-soft">
              <?php if (array_key_exists('description', $input_array) && !empty($input_array['description'])): ?>
                <div class="pn-tasks-manager-section-info-block pn-tasks-manager-mb-20">
                  <i class="material-icons-outlined pn-tasks-manager-section-info-icon">info_outline</i>
                  <small><?php echo wp_kses_post($input_array['description']); ?></small>
                </div>
              <?php endif ?>
        <?php elseif ($input_array['section'] == 'end'): ?>
            </div>
          </div>
        <?php endif ?>
      <?php else: ?>
        <div class="pn-tasks-manager-input-wrapper <?php echo esc_attr($input_array['id']); ?> <?php echo !empty($input_array['tabs']) ? 'pn-tasks-manager-input-tabbed' : ''; ?> pn-tasks-manager-input-field-<?php echo esc_attr($input_array['input']); ?> <?php echo (!empty($input_array['required']) && $input_array['required'] == true) ? 'pn-tasks-manager-input-field-required' : ''; ?> <?php echo ($disabled) ? 'pn-tasks-manager-input-field-disabled' : ''; ?>">
          <?php if (array_key_exists('label', $input_array) && !empty($input_array['label'])): ?>
            <div class="pn-tasks-manager-display-inline-table <?php echo (($pn_tasks_manager_format == 'half' && !(array_key_exists('type', $input_array) && $input_array['type'] == 'submit')) ? 'pn-tasks-manager-width-40-percent' : 'pn-tasks-manager-width-100-percent'); ?> pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-vertical-align-top">
              <div class="pn-tasks-manager-p-10 <?php echo (array_key_exists('parent', $input_array) && !empty($input_array['parent']) && $input_array['parent'] != 'this') ? 'pn-tasks-manager-pl-30' : ''; ?>">
                <label class="pn-tasks-manager-vertical-align-middle pn-tasks-manager-display-block <?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? 'pn-tasks-manager-toggle' : ''; ?>" for="<?php echo esc_attr($input_array['id']); ?>"><?php echo wp_kses($input_array['label'], PN_TASKS_MANAGER_KSES); ?> <?php echo (array_key_exists('required', $input_array) && !empty($input_array['required']) && $input_array['required'] == true) ? '<span class="pn-tasks-manager-tooltip" title="' . esc_html(__('Required field', 'pn-tasks-manager')) . '">*</span>' : ''; ?><?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? '<i class="material-icons-outlined pn-tasks-manager-cursor-pointer pn-tasks-manager-float-right">add</i>' : ''; ?></label>

                <?php if (array_key_exists('description', $input_array) && !empty($input_array['description'])): ?>
                  <div class="pn-tasks-manager-toggle-content pn-tasks-manager-display-none-soft">
                    <small><?php echo wp_kses_post(wp_specialchars_decode($input_array['description'])); ?></small>
                  </div>
                <?php endif ?>
              </div>
            </div>
          <?php endif ?>

          <div class="pn-tasks-manager-display-inline-table <?php echo ((array_key_exists('label', $input_array) && empty($input_array['label'])) ? 'pn-tasks-manager-width-100-percent' : (($pn_tasks_manager_format == 'half' && !(array_key_exists('type', $input_array) && $input_array['type'] == 'submit')) ? 'pn-tasks-manager-width-60-percent' : 'pn-tasks-manager-width-100-percent')); ?> pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-vertical-align-top">
            <div class="pn-tasks-manager-p-10 <?php echo (array_key_exists('parent', $input_array) && !empty($input_array['parent']) && $input_array['parent'] != 'this') ? 'pn-tasks-manager-pl-30' : ''; ?>">
              <div class="pn-tasks-manager-input-field"><?php self::pn_tasks_manager_input_builder($input_array, $type, $pn_tasks_manager_id, $disabled); ?></div>
            </div>
          </div>
        </div>
      <?php endif ?>
    <?php
  }

  /**
   * Display wrapper for field values with format control
   * 
   * @param array $input_array The input array containing field configuration
   * @param string $type The type of field (user, post, option)
   * @param int $pn_tasks_manager_id The ID of the user/post/option
   * @param int $pn_tasks_manager_meta_array Whether the field is part of a meta array
   * @param int $pn_tasks_manager_array_index The index in the meta array
   * @param string $pn_tasks_manager_format The display format ('half' or 'full')
   * @return string Formatted HTML output
   */
  public static function pn_tasks_manager_input_display_wrapper($input_array, $type, $pn_tasks_manager_id = 0, $pn_tasks_manager_meta_array = 0, $pn_tasks_manager_array_index = 0, $pn_tasks_manager_format = 'half') {
    ob_start();
    ?>
    <?php if (array_key_exists('section', $input_array) && !empty($input_array['section'])): ?>      
      <?php if ($input_array['section'] == 'start'): ?>
        <div class="pn-tasks-manager-toggle-wrapper pn-tasks-manager-section-wrapper pn-tasks-manager-position-relative <?php echo array_key_exists('class', $input_array) ? esc_attr($input_array['class']) : ''; ?>" id="<?php echo array_key_exists('id', $input_array) ? esc_attr($input_array['id']) : ''; ?>">
          <a href="#" class="pn-tasks-manager-toggle pn-tasks-manager-width-100-percent pn-tasks-manager-text-decoration-none">
            <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent pn-tasks-manager-mb-20">
              <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-90-percent">
                <label class="pn-tasks-manager-cursor-pointer pn-tasks-manager-mb-20 pn-tasks-manager-color-main-0"><?php echo wp_kses($input_array['label'], PN_TASKS_MANAGER_KSES); ?></label>
              </div>
              <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-10-percent pn-tasks-manager-text-align-right">
                <i class="material-icons-outlined pn-tasks-manager-cursor-pointer pn-tasks-manager-color-main-0">add</i>
              </div>
            </div>
          </a>

          <div class="pn-tasks-manager-content pn-tasks-manager-pl-10 pn-tasks-manager-toggle-content pn-tasks-manager-mb-20 pn-tasks-manager-display-none-soft">
            <?php if (array_key_exists('description', $input_array) && !empty($input_array['description'])): ?>
              <div class="pn-tasks-manager-section-info-block pn-tasks-manager-mb-20">
                <i class="material-icons-outlined pn-tasks-manager-section-info-icon">info_outline</i>
                <small><?php echo wp_kses_post($input_array['description']); ?></small>
              </div>
            <?php endif ?>
      <?php elseif ($input_array['section'] == 'end'): ?>
          </div>
        </div>
      <?php endif ?>
    <?php else: ?>
      <div class="pn-tasks-manager-input-wrapper <?php echo esc_attr($input_array['id']); ?> pn-tasks-manager-input-display-<?php echo esc_attr($input_array['input']); ?> <?php echo (!empty($input_array['required']) && $input_array['required'] == true) ? 'pn-tasks-manager-input-field-required' : ''; ?>">
        <?php if (array_key_exists('label', $input_array) && !empty($input_array['label'])): ?>
          <div class="pn-tasks-manager-display-inline-table <?php echo ($pn_tasks_manager_format == 'half' ? 'pn-tasks-manager-width-40-percent' : 'pn-tasks-manager-width-100-percent'); ?> pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-vertical-align-top">
            <div class="pn-tasks-manager-p-10 <?php echo (array_key_exists('parent', $input_array) && !empty($input_array['parent']) && $input_array['parent'] != 'this') ? 'pn-tasks-manager-pl-30' : ''; ?>">
              <label class="pn-tasks-manager-vertical-align-middle pn-tasks-manager-display-block <?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? 'pn-tasks-manager-toggle' : ''; ?>" for="<?php echo esc_attr($input_array['id']); ?>">
                <?php echo wp_kses($input_array['label'], PN_TASKS_MANAGER_KSES); ?>
                <?php echo (array_key_exists('required', $input_array) && !empty($input_array['required']) && $input_array['required'] == true) ? '<span class="pn-tasks-manager-tooltip" title="' . esc_html(__('Required field', 'pn-tasks-manager')) . '">*</span>' : ''; ?>
                <?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? '<i class="material-icons-outlined pn-tasks-manager-cursor-pointer pn-tasks-manager-float-right">add</i>' : ''; ?>
              </label>

              <?php if (array_key_exists('description', $input_array) && !empty($input_array['description'])): ?>
                <div class="pn-tasks-manager-toggle-content pn-tasks-manager-display-none-soft">
                  <small><?php echo wp_kses_post(wp_specialchars_decode($input_array['description'])); ?></small>
                </div>
              <?php endif ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="pn-tasks-manager-display-inline-table <?php echo ((array_key_exists('label', $input_array) && empty($input_array['label'])) ? 'pn-tasks-manager-width-100-percent' : ($pn_tasks_manager_format == 'half' ? 'pn-tasks-manager-width-60-percent' : 'pn-tasks-manager-width-100-percent')); ?> pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-vertical-align-top">
          <div class="pn-tasks-manager-p-10 <?php echo (array_key_exists('parent', $input_array) && !empty($input_array['parent']) && $input_array['parent'] != 'this') ? 'pn-tasks-manager-pl-30' : ''; ?>">
            <div class="pn-tasks-manager-input-field">
              <?php self::pn_tasks_manager_input_display($input_array, $type, $pn_tasks_manager_id, $pn_tasks_manager_meta_array, $pn_tasks_manager_array_index); ?>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
  }

  /**
   * Display formatted values of pn_tasks_manager_input_builder fields in frontend
   * 
   * @param array $pn_tasks_manager_input The input array containing field configuration
   * @param string $pn_tasks_manager_type The type of field (user, post, option)
   * @param int $pn_tasks_manager_id The ID of the user/post/option
   * @param int $pn_tasks_manager_meta_array Whether the field is part of a meta array
   * @param int $pn_tasks_manager_array_index The index in the meta array
   * @return string Formatted HTML output of field values
   */
  public static function pn_tasks_manager_input_display($pn_tasks_manager_input, $pn_tasks_manager_type, $pn_tasks_manager_id = 0, $pn_tasks_manager_meta_array = 0, $pn_tasks_manager_array_index = 0) {
    // Get the current value using the new function
    $current_value = self::pn_tasks_manager_get_field_value($pn_tasks_manager_input['id'], $pn_tasks_manager_type, $pn_tasks_manager_id, $pn_tasks_manager_meta_array, $pn_tasks_manager_array_index, $pn_tasks_manager_input);

    // Start the field value display
    ?>
      <div class="pn-tasks-manager-field-value">
        <?php
        switch ($pn_tasks_manager_input['input']) {
          case 'input':
            switch ($pn_tasks_manager_input['type']) {
              case 'hidden':
                break;
              case 'nonce':
                break;
              case 'file':
                if (!empty($current_value)) {
                  $file_url = wp_get_attachment_url($current_value);
                  ?>
                    <div class="pn-tasks-manager-file-display">
                      <a href="<?php echo esc_url($file_url); ?>" target="_blank" class="pn-tasks-manager-file-link">
                        <?php echo esc_html(basename($file_url)); ?>
                      </a>
                    </div>
                  <?php
                } else {
                  echo '<span class="pn-tasks-manager-no-file">' . esc_html__('No file uploaded', 'pn-tasks-manager') . '</span>';
                }
                break;

              case 'checkbox':
                ?>
                  <div class="pn-tasks-manager-checkbox-display">
                    <span class="pn-tasks-manager-checkbox-status <?php echo $current_value === 'on' ? 'checked' : 'unchecked'; ?>">
                      <?php echo $current_value === 'on' ? esc_html__('Yes', 'pn-tasks-manager') : esc_html__('No', 'pn-tasks-manager'); ?>
                    </span>
                  </div>
                <?php
                break;

              case 'radio':
                if (!empty($pn_tasks_manager_input['radio_options'])) {
                  foreach ($pn_tasks_manager_input['radio_options'] as $option) {
                    if ($current_value === $option['value']) {
                      ?>
                        <span class="pn-tasks-manager-radio-selected"><?php echo esc_html($option['label']); ?></span>
                      <?php
                    }
                  }
                }
                break;

              case 'color':
                $color_value = !empty($current_value) ? trim($current_value) : '#b84a00';
                // Ensure color value is valid hex color
                if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color_value)) {
                  $color_value = '#b84a00';
                }
                ?>
                  <div class="pn-tasks-manager-color-display">
                    <span class="pn-tasks-manager-color-preview" style="background-color: <?php echo esc_attr($color_value); ?> !important; display: inline-block; width: 24px; height: 24px; border-radius: 4px; border: 1px solid #e0e0e0;"></span>
                    <span class="pn-tasks-manager-color-value"><?php echo esc_html($color_value); ?></span>
                  </div>
                <?php
                break;

              case 'date':
                if (!empty($current_value) && is_string($current_value)) {
                  try {
                    // Convert date string to timestamp
                    $date_timestamp = strtotime(trim($current_value));
                    if ($date_timestamp !== false && $date_timestamp > 0) {
                      // Format using WordPress date format setting
                      $date_format = get_option('date_format');
                      if (empty($date_format)) {
                        $date_format = 'Y-m-d'; // Default format
                      }
                      $formatted_date = date_i18n($date_format, $date_timestamp);
                      if (!empty($formatted_date)) {
                        ?>
                          <span class="pn-tasks-manager-date-value"><?php echo esc_html($formatted_date); ?></span>
                        <?php
                      } else {
                        ?>
                          <span class="pn-tasks-manager-text-value"><?php echo esc_html($current_value); ?></span>
                        <?php
                      }
                    } else {
                      ?>
                        <span class="pn-tasks-manager-text-value"><?php echo esc_html($current_value); ?></span>
                      <?php
                    }
                  } catch (Exception $e) {
                    ?>
                      <span class="pn-tasks-manager-text-value"><?php echo esc_html($current_value); ?></span>
                    <?php
                  }
                } else {
                  ?>
                    <span class="pn-tasks-manager-text-value"><?php echo esc_html($current_value); ?></span>
                  <?php
                }
                break;

              case 'datetime-local':
                if (!empty($current_value) && is_string($current_value)) {
                  try {
                    // Convert datetime string to timestamp
                    $datetime_timestamp = strtotime(trim($current_value));
                    if ($datetime_timestamp !== false && $datetime_timestamp > 0) {
                      // Format using WordPress date and time format settings
                      $date_format = get_option('date_format');
                      $time_format = get_option('time_format');
                      if (empty($date_format)) {
                        $date_format = 'Y-m-d'; // Default format
                      }
                      if (empty($time_format)) {
                        $time_format = 'H:i'; // Default format
                      }
                      $formatted_datetime = date_i18n($date_format . ' ' . $time_format, $datetime_timestamp);
                      if (!empty($formatted_datetime)) {
                        ?>
                          <span class="pn-tasks-manager-datetime-value"><?php echo esc_html($formatted_datetime); ?></span>
                        <?php
                      } else {
                        ?>
                          <span class="pn-tasks-manager-text-value"><?php echo esc_html($current_value); ?></span>
                        <?php
                      }
                    } else {
                      ?>
                        <span class="pn-tasks-manager-text-value"><?php echo esc_html($current_value); ?></span>
                      <?php
                    }
                  } catch (Exception $e) {
                    ?>
                      <span class="pn-tasks-manager-text-value"><?php echo esc_html($current_value); ?></span>
                    <?php
                  }
                } else {
                  ?>
                    <span class="pn-tasks-manager-text-value"><?php echo esc_html($current_value); ?></span>
                  <?php
                }
                break;

              case 'time':
                // Time fields can be displayed as-is or formatted
                ?>
                  <span class="pn-tasks-manager-time-value"><?php echo esc_html($current_value); ?></span>
                <?php
                break;

              default:
                ?>
                  <span class="pn-tasks-manager-text-value"><?php echo esc_html($current_value); ?></span>
                <?php
                break;
            }
            break;

          case 'select':
            if (!empty($pn_tasks_manager_input['options']) && is_array($pn_tasks_manager_input['options'])) {
              if (array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple']) {
                // Handle multiple select
                $selected_values = is_array($current_value) ? $current_value : array();
                if (!empty($selected_values)) {
                  ?>
                  <div class="pn-tasks-manager-select-values pn-tasks-manager-select-values-column">
                    <?php foreach ($selected_values as $value): ?>
                      <?php if (isset($pn_tasks_manager_input['options'][$value])): ?>
                        <div class="pn-tasks-manager-select-value-item"><?php echo esc_html($pn_tasks_manager_input['options'][$value]); ?></div>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </div>
                  <?php
                }
              } else {
                // Handle single select
                $current_value = is_scalar($current_value) ? (string)$current_value : '';
                if (isset($pn_tasks_manager_input['options'][$current_value])) {
                  ?>
                  <span class="pn-tasks-manager-select-value"><?php echo esc_html($pn_tasks_manager_input['options'][$current_value]); ?></span>
                  <?php
                }
              }
            }
            break;

          case 'textarea':
            ?>
              <div class="pn-tasks-manager-textarea-value"><?php echo wp_kses_post(nl2br($current_value)); ?></div>
            <?php
            break;
          case 'image':
            if (!empty($current_value)) {
              $image_ids = is_array($current_value) ? $current_value : explode(',', $current_value);
              ?>
                <div class="pn-tasks-manager-image-gallery">
                  <?php foreach ($image_ids as $image_id): ?>
                    <div class="pn-tasks-manager-image-item">
                      <?php echo wp_get_attachment_image($image_id, 'medium'); ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php
            } else {
              ?>
                <span class="pn-tasks-manager-no-image"><?php esc_html_e('No images uploaded', 'pn-tasks-manager'); ?></span>
              <?php
            }
            break;
          case 'editor':
            ?>
              <div class="pn-tasks-manager-editor-content"><?php echo wp_kses_post($current_value); ?></div>
            <?php
            break;
          case 'html':
            if (!empty($pn_tasks_manager_input['html_content'])) {
              ?>
                <div class="pn-tasks-manager-html-content"><?php echo wp_kses_post(do_shortcode($pn_tasks_manager_input['html_content'])); ?></div>
              <?php
            }
            break;
          case 'html_multi':
            switch ($pn_tasks_manager_type) {
              case 'user':
                $html_multi_fields_length = !empty(get_user_meta($pn_tasks_manager_id, $pn_tasks_manager_input['html_multi_fields'][0]['id'], true)) ? count(get_user_meta($pn_tasks_manager_id, $pn_tasks_manager_input['html_multi_fields'][0]['id'], true)) : 0;
                break;
              case 'post':
                $html_multi_fields_length = !empty(get_post_meta($pn_tasks_manager_id, $pn_tasks_manager_input['html_multi_fields'][0]['id'], true)) ? count(get_post_meta($pn_tasks_manager_id, $pn_tasks_manager_input['html_multi_fields'][0]['id'], true)) : 0;
                break;
              case 'option':
                $html_multi_fields_length = !empty(get_option($pn_tasks_manager_input['html_multi_fields'][0]['id'])) ? count(get_option($pn_tasks_manager_input['html_multi_fields'][0]['id'])) : 0;
            }

            ?>
              <div class="pn-tasks-manager-html-multi-content">
                <?php if ($html_multi_fields_length): ?>
                  <?php foreach (range(0, ($html_multi_fields_length - 1)) as $length_index): ?>
                    <div class="pn-tasks-manager-html-multi-group pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
                      <?php foreach ($pn_tasks_manager_input['html_multi_fields'] as $index => $html_multi_field): ?>
                          <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-60-percent">
                            <label><?php echo esc_html($html_multi_field['label']); ?></label>
                          </div>

                          <div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-40-percent">
                            <?php self::pn_tasks_manager_input_display($html_multi_field, $pn_tasks_manager_type, $pn_tasks_manager_id, 1, $length_index); ?>
                          </div>
                      <?php endforeach ?>
                    </div>
                  <?php endforeach ?>
                <?php endif; ?>
              </div>
            <?php
            break;
          case 'taxonomy':
            $taxonomy = !empty($pn_tasks_manager_input['taxonomy']) ? $pn_tasks_manager_input['taxonomy'] : 'category';
            $is_multiple = array_key_exists('multiple', $pn_tasks_manager_input) && $pn_tasks_manager_input['multiple'];
            
            // Get current taxonomy terms
            $current_terms = [];
            if ($pn_tasks_manager_type === 'post' && !empty($pn_tasks_manager_id)) {
              $terms = wp_get_post_terms($pn_tasks_manager_id, $taxonomy, ['fields' => 'all']);
              $current_terms = is_array($terms) && !is_wp_error($terms) ? $terms : [];
            }
            
            if (!empty($current_terms)) {
              if ($is_multiple) {
                ?>
                <div class="pn-tasks-manager-taxonomy-values">
                  <?php foreach ($current_terms as $term): ?>
                    <span class="pn-tasks-manager-taxonomy-term"><?php echo esc_html($term->name); ?></span>
                  <?php endforeach; ?>
                </div>
                <?php
              } else {
                ?>
                <span class="pn-tasks-manager-taxonomy-term"><?php echo esc_html($current_terms[0]->name); ?></span>
                <?php
              }
            }
            break;
        }
        ?>
      </div>
    <?php
  }

  public static function pn_tasks_manager_sanitizer($value, $node = '', $type = '', $field_config = []) {
    // Use the new validation system
    $result = PN_TASKS_MANAGER_Validation::pn_tasks_manager_validate_and_sanitize($value, $node, $type, $field_config);
    
    // If validation failed, return empty value and log the error
    if (is_wp_error($result)) {
        return '';
    }
    
    return $result;
  }
}