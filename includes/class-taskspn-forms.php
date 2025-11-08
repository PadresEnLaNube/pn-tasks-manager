<?php
/**
 * Fired from activate() function.
 *
 * This class defines all post types necessary to run during the plugin's life cycle.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    TASKSPN
 * @subpackage TASKSPN/includes
 * @author     Padres en la Nube
 */
class TASKSPN_Forms {
  /**
   * Plaform forms.
   *
   * @since    1.0.0
   */

  /**
   * Get the current value of a field based on its type and storage
   * 
   * @param string $field_id The field ID
   * @param string $taskspn_type The type of field (user, post, option)
   * @param int $taskspn_id The ID of the user/post/option
   * @param int $taskspn_meta_array Whether the field is part of a meta array
   * @param int $taskspn_array_index The index in the meta array
   * @param array $taskspn_input The input array containing field configuration
   * @return mixed The current value of the field
   */
  public static function taskspn_get_field_value($field_id, $taskspn_type, $taskspn_id = 0, $taskspn_meta_array = 0, $taskspn_array_index = 0, $taskspn_input = []) {
    $current_value = '';

    if ($taskspn_meta_array) {
      switch ($taskspn_type) {
        case 'user':
          $meta = get_user_meta($taskspn_id, $field_id, true);
          if (is_array($meta) && isset($meta[$taskspn_array_index])) {
            $current_value = $meta[$taskspn_array_index];
          }
          break;
        case 'post':
          $meta = get_post_meta($taskspn_id, $field_id, true);
          if (is_array($meta) && isset($meta[$taskspn_array_index])) {
            $current_value = $meta[$taskspn_array_index];
          }
          break;
        case 'option':
          $option = get_option($field_id);
          if (is_array($option) && isset($option[$taskspn_array_index])) {
            $current_value = $option[$taskspn_array_index];
          }
          break;
      }
    } else {
      switch ($taskspn_type) {
        case 'user':
          $current_value = get_user_meta($taskspn_id, $field_id, true);
          break;
        case 'post':
          $current_value = get_post_meta($taskspn_id, $field_id, true);
          break;
        case 'option':
          $current_value = get_option($field_id);
          break;
      }
    }

    // If no value is found and there's a default value in the input config, use it
    // BUT NOT for checkboxes in multiple fields, as empty string and 'off' are valid states (unchecked)
    if (empty($current_value) && !empty($taskspn_input['value'])) {
      // For checkboxes in multiple fields, don't override empty values or 'off' with default
      if (!($taskspn_meta_array && isset($taskspn_input['type']) && $taskspn_input['type'] === 'checkbox')) {
        $current_value = $taskspn_input['value'];
      }
    }
    
    // For checkboxes in multiple fields, normalize 'off' to empty string for display
    if ($taskspn_meta_array && isset($taskspn_input['type']) && $taskspn_input['type'] === 'checkbox' && $current_value === 'off') {
      $current_value = '';
    }

    return $current_value;
  }

  public static function taskspn_input_builder($taskspn_input, $taskspn_type, $taskspn_id = 0, $disabled = 0, $taskspn_meta_array = 0, $taskspn_array_index = 0) {
    // Get the current value using the new function
    $taskspn_value = self::taskspn_get_field_value($taskspn_input['id'], $taskspn_type, $taskspn_id, $taskspn_meta_array, $taskspn_array_index, $taskspn_input);

    $taskspn_parent_block = (!empty($taskspn_input['parent']) ? 'data-taskspn-parent="' . $taskspn_input['parent'] . '"' : '') . ' ' . (!empty($taskspn_input['parent_option']) ? 'data-taskspn-parent-option="' . $taskspn_input['parent_option'] . '"' : '');

    switch ($taskspn_input['input']) {
      case 'input':        
        switch ($taskspn_input['type']) {
          case 'file':
            ?>
              <?php if (empty($taskspn_value)): ?>
                <p class="taskspn-m-10"><?php esc_html_e('No file found', 'taskspn'); ?></p>
              <?php else: ?>
                <p class="taskspn-m-10">
                  <a href="<?php echo esc_url(get_post_meta($taskspn_id, $taskspn_input['id'], true)['url']); ?>" target="_blank"><?php echo esc_html(basename(get_post_meta($taskspn_id, $taskspn_input['id'], true)['url'])); ?></a>
                </p>
              <?php endif ?>
            <?php
            break;
          case 'checkbox':
            ?>
              <label class="taskspn-switch">
                <input id="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? '[]' : ''); ?>" class="<?php echo array_key_exists('class', $taskspn_input) ? esc_attr($taskspn_input['class']) : ''; ?> taskspn-checkbox taskspn-checkbox-switch taskspn-field" type="<?php echo esc_attr($taskspn_input['type']); ?>" <?php echo $taskspn_value == 'on' ? 'checked="checked"' : ''; ?> <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> <?php echo ((array_key_exists('required', $taskspn_input) && $taskspn_input['required'] == true) ? 'required' : ''); ?> <?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple'] ? 'multiple' : ''); ?> <?php echo wp_kses_post($taskspn_parent_block); ?>>
                <span class="taskspn-slider taskspn-round"></span>
              </label>
            <?php
            break;
          case 'radio':
            ?>
              <div class="taskspn-input-radio-wrapper">
                <?php if (!empty($taskspn_input['radio_options'])): ?>
                  <?php foreach ($taskspn_input['radio_options'] as $radio_option): ?>
                    <div class="taskspn-input-radio-item">
                      <label for="<?php echo esc_attr($radio_option['id']); ?>">
                        <?php echo wp_kses_post(wp_specialchars_decode($radio_option['label'])); ?>
                        
                        <input type="<?php echo esc_attr($taskspn_input['type']); ?>"
                          id="<?php echo esc_attr($radio_option['id']); ?>"
                          name="<?php echo esc_attr($taskspn_input['id']); ?>"
                          value="<?php echo esc_attr($radio_option['value']); ?>"
                          <?php echo $taskspn_value == $radio_option['value'] ? 'checked="checked"' : ''; ?>
                          <?php echo ((array_key_exists('required', $taskspn_input) && $taskspn_input['required'] == 'true') ? 'required' : ''); ?>>

                        <div class="taskspn-radio-control"></div>
                      </label>
                    </div>
                  <?php endforeach ?>
                <?php endif ?>
              </div>
            <?php
            break;
          case 'range':
            ?>
              <div class="taskspn-input-range-wrapper">
                <div class="taskspn-width-100-percent">
                  <?php if (!empty($taskspn_input['taskspn_label_min'])): ?>
                    <p class="taskspn-input-range-label-min"><?php echo esc_html($taskspn_input['taskspn_label_min']); ?></p>
                  <?php endif ?>

                  <?php if (!empty($taskspn_input['taskspn_label_max'])): ?>
                    <p class="taskspn-input-range-label-max"><?php echo esc_html($taskspn_input['taskspn_label_max']); ?></p>
                  <?php endif ?>
                </div>

                <input type="<?php echo esc_attr($taskspn_input['type']); ?>" id="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? '[]' : ''); ?>" class="taskspn-input-range <?php echo array_key_exists('class', $taskspn_input) ? esc_attr($taskspn_input['class']) : ''; ?>" <?php echo ((array_key_exists('required', $taskspn_input) && $taskspn_input['required'] == true) ? 'required' : ''); ?> <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> <?php echo (isset($taskspn_input['taskspn_max']) ? 'max=' . esc_attr($taskspn_input['taskspn_max']) : ''); ?> <?php echo (isset($taskspn_input['taskspn_min']) ? 'min=' . esc_attr($taskspn_input['taskspn_min']) : ''); ?> <?php echo (((array_key_exists('step', $taskspn_input) && $taskspn_input['step'] != '')) ? 'step="' . esc_attr($taskspn_input['step']) . '"' : ''); ?> <?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple'] ? 'multiple' : ''); ?> value="<?php echo (!empty($taskspn_input['button_text']) ? esc_html($taskspn_input['button_text']) : esc_html($taskspn_value)); ?>"/>
                <h3 class="taskspn-input-range-output"></h3>
              </div>
            <?php
            break;
          case 'stars':
            $taskspn_stars = !empty($taskspn_input['stars_number']) ? $taskspn_input['stars_number'] : 5;
            ?>
              <div class="taskspn-input-stars-wrapper">
                <div class="taskspn-width-100-percent">
                  <?php if (!empty($taskspn_input['taskspn_label_min'])): ?>
                    <p class="taskspn-input-stars-label-min"><?php echo esc_html($taskspn_input['taskspn_label_min']); ?></p>
                  <?php endif ?>

                  <?php if (!empty($taskspn_input['taskspn_label_max'])): ?>
                    <p class="taskspn-input-stars-label-max"><?php echo esc_html($taskspn_input['taskspn_label_max']); ?></p>
                  <?php endif ?>
                </div>

                <div class="taskspn-input-stars taskspn-text-align-center taskspn-pt-20">
                  <?php foreach (range(1, $taskspn_stars) as $index => $star): ?>
                    <i class="material-icons-outlined taskspn-input-star">
                      <?php echo ($index < intval($taskspn_value)) ? 'star' : 'star_outlined'; ?>
                    </i>
                  <?php endforeach ?>
                </div>

                <input type="number" <?php echo ((array_key_exists('required', $taskspn_input) && $taskspn_input['required'] == true) ? 'required' : ''); ?> <?php echo ((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') ? 'disabled' : ''); ?> id="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? '[]' : ''); ?>" class="taskspn-input-hidden-stars <?php echo array_key_exists('class', $taskspn_input) ? esc_attr($taskspn_input['class']) : ''; ?>" min="1" max="<?php echo esc_attr($taskspn_stars) ?>" value="<?php echo esc_attr($taskspn_value); ?>">
              </div>
            <?php
            break;
          case 'submit':
            ?>
              <div class="taskspn-text-align-right">
                <input type="submit" value="<?php echo esc_attr($taskspn_input['value']); ?>" name="<?php echo esc_attr($taskspn_input['id']); ?>" id="<?php echo esc_attr($taskspn_input['id']); ?>" name="<?php echo esc_attr($taskspn_input['id']); ?>" class="taskspn-btn" data-taskspn-type="<?php echo esc_attr($taskspn_type); ?>" data-taskspn-subtype="<?php echo ((array_key_exists('subtype', $taskspn_input)) ? esc_attr($taskspn_input['subtype']) : ''); ?>" data-taskspn-user-id="<?php echo esc_attr($taskspn_id); ?>" data-taskspn-post-id="<?php echo !empty(get_the_ID()) ? esc_attr(get_the_ID()) : ''; ?>"/><?php esc_html(TASKSPN_Data::taskspn_loader()); ?>
              </div>
            <?php
            break;
          case 'hidden':
            ?>
              <input type="hidden" id="<?php echo esc_attr($taskspn_input['id']); ?>" name="<?php echo esc_attr($taskspn_input['id']); ?>" value="<?php echo esc_attr($taskspn_value); ?>" <?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple'] == 'true' ? 'multiple' : ''); ?>>
            <?php
            break;
          case 'nonce':
            ?>
              <input type="hidden" id="<?php echo esc_attr($taskspn_input['id']); ?>" name="<?php echo esc_attr($taskspn_input['id']); ?>" value="<?php echo esc_attr(wp_create_nonce('taskspn-nonce')); ?>">
            <?php
            break;
          case 'password':
            ?>
              <div class="taskspn-password-checker">
                <div class="taskspn-password-input taskspn-position-relative">
                  <input id="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple'] == 'true') ? '[]' : ''); ?>" name="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple'] == 'true') ? '[]' : ''); ?>" <?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple'] == 'true' ? 'multiple' : ''); ?> class="taskspn-field taskspn-password-strength <?php echo array_key_exists('class', $taskspn_input) ? esc_attr($taskspn_input['class']) : ''; ?>" type="<?php echo esc_attr($taskspn_input['type']); ?>" <?php echo ((array_key_exists('required', $taskspn_input) && $taskspn_input['required'] == 'true') ? 'required' : ''); ?> <?php echo ((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') ? 'disabled' : ''); ?> value="<?php echo (!empty($taskspn_input['button_text']) ? esc_html($taskspn_input['button_text']) : esc_attr($taskspn_value)); ?>" placeholder="<?php echo (array_key_exists('placeholder', $taskspn_input) ? esc_attr($taskspn_input['placeholder']) : ''); ?>" <?php echo wp_kses_post($taskspn_parent_block); ?>/>

                  <a href="#" class="taskspn-show-pass taskspn-cursor-pointer taskspn-display-none-soft">
                    <i class="material-icons-outlined taskspn-font-size-20">visibility</i>
                  </a>
                </div>

                <div id="taskspn-popover-pass" class="taskspn-display-none-soft">
                  <div class="taskspn-progress-bar-wrapper">
                    <div class="taskspn-password-strength-bar"></div>
                  </div>

                  <h3 class="taskspn-mt-20"><?php esc_html_e('Password strength checker', 'taskspn'); ?> <i class="material-icons-outlined taskspn-cursor-pointer taskspn-close-icon taskspn-mt-30">close</i></h3>
                  <ul class="taskspn-list-style-none">
                    <li class="low-upper-case">
                      <i class="material-icons-outlined taskspn-font-size-20 taskspn-vertical-align-middle">radio_button_unchecked</i>
                      <span><?php esc_html_e('Lowercase & Uppercase', 'taskspn'); ?></span>
                    </li>
                    <li class="one-number">
                      <i class="material-icons-outlined taskspn-font-size-20 taskspn-vertical-align-middle">radio_button_unchecked</i>
                      <span><?php esc_html_e('Number (0-9)', 'taskspn'); ?></span>
                    </li>
                    <li class="one-special-char">
                      <i class="material-icons-outlined taskspn-font-size-20 taskspn-vertical-align-middle">radio_button_unchecked</i>
                      <span><?php esc_html_e('Special Character (!@#$%^&*)', 'taskspn'); ?></span>
                    </li>
                    <li class="eight-character">
                      <i class="material-icons-outlined taskspn-font-size-20 taskspn-vertical-align-middle">radio_button_unchecked</i>
                      <span><?php esc_html_e('Atleast 8 Character', 'taskspn'); ?></span>
                    </li>
                  </ul>
                </div>
              </div>
            <?php
            break;
          case 'color':
            ?>
              <input id="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? '[]' : ''); ?>" <?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple'] ? 'multiple' : ''); ?> class="taskspn-field <?php echo array_key_exists('class', $taskspn_input) ? esc_attr($taskspn_input['class']) : ''; ?>" type="<?php echo esc_attr($taskspn_input['type']); ?>" <?php echo ((array_key_exists('required', $taskspn_input) && $taskspn_input['required'] == true) ? 'required' : ''); ?> <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> value="<?php echo (!empty($taskspn_value) ? esc_attr($taskspn_value) : '#000000'); ?>" placeholder="<?php echo (array_key_exists('placeholder', $taskspn_input) ? esc_attr($taskspn_input['placeholder']) : ''); ?>" <?php echo wp_kses_post($taskspn_parent_block); ?>/>
            <?php
            break;
          default:
            ?>
              <input 
                <?php /* ID and name attributes */ ?>
                id="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? '[]' : ''); ?>" 
                name="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? '[]' : ''); ?>"
                
                <?php /* Type and styling */ ?>
                class="taskspn-field <?php echo array_key_exists('class', $taskspn_input) ? esc_attr($taskspn_input['class']) : ''; ?>" 
                type="<?php echo esc_attr($taskspn_input['type']); ?>"
                
                <?php /* State attributes */ ?>
                <?php echo ((array_key_exists('required', $taskspn_input) && $taskspn_input['required'] == true) ? 'required' : ''); ?>
                <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
                <?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple'] ? 'multiple' : ''); ?>
                
                <?php /* Validation and limits */ ?>
                <?php echo (((array_key_exists('step', $taskspn_input) && $taskspn_input['step'] != '')) ? 'step="' . esc_attr($taskspn_input['step']) . '"' : ''); ?>
                <?php echo (isset($taskspn_input['max']) ? 'max="' . esc_attr($taskspn_input['max']) . '"' : ''); ?>
                <?php echo (isset($taskspn_input['min']) ? 'min="' . esc_attr($taskspn_input['min']) . '"' : ''); ?>
                <?php echo (isset($taskspn_input['maxlength']) ? 'maxlength="' . esc_attr($taskspn_input['maxlength']) . '"' : ''); ?>
                <?php echo (isset($taskspn_input['pattern']) ? 'pattern="' . esc_attr($taskspn_input['pattern']) . '"' : ''); ?>
                
                <?php /* Content attributes */ ?>
                value="<?php echo (!empty($taskspn_input['button_text']) ? esc_html($taskspn_input['button_text']) : esc_html($taskspn_value)); ?>"
                placeholder="<?php echo (array_key_exists('placeholder', $taskspn_input) ? esc_html($taskspn_input['placeholder']) : ''); ?>"
                
                <?php /* Custom data attributes */ ?>
                <?php echo wp_kses_post($taskspn_parent_block); ?>
              />
            <?php
            break;
        }
        break;
      case 'select':
        if (!empty($taskspn_input['options']) && is_array($taskspn_input['options'])) {
          ?>
          <select 
            id="<?php echo esc_attr($taskspn_input['id']); ?>" 
            name="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? '[]' : ''); ?>" 
            class="taskspn-field <?php echo array_key_exists('class', $taskspn_input) ? esc_attr($taskspn_input['class']) : ''; ?>"
            <?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? 'multiple' : ''; ?>
            <?php echo ((array_key_exists('required', $taskspn_input) && $taskspn_input['required'] == true) ? 'required' : ''); ?>
            <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
            <?php echo wp_kses_post($taskspn_parent_block); ?>
          >
            <?php if (array_key_exists('placeholder', $taskspn_input) && !empty($taskspn_input['placeholder'])): ?>
              <option value=""><?php echo esc_html($taskspn_input['placeholder']); ?></option>
            <?php endif; ?>
            
            <?php 
            $selected_values = array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple'] ? 
              (is_array($taskspn_value) ? $taskspn_value : array()) : 
              array($taskspn_value);
            
            foreach ($taskspn_input['options'] as $value => $label): 
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
          <textarea id="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($taskspn_input['id']) . ((array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? '[]' : ''); ?>" <?php echo wp_kses_post($taskspn_parent_block); ?> class="taskspn-field <?php echo array_key_exists('class', $taskspn_input) ? esc_attr($taskspn_input['class']) : ''; ?>" <?php echo ((array_key_exists('required', $taskspn_input) && $taskspn_input['required'] == true) ? 'required' : ''); ?> <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> <?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple'] ? 'multiple' : ''); ?> placeholder="<?php echo (array_key_exists('placeholder', $taskspn_input) ? esc_attr($taskspn_input['placeholder']) : ''); ?>"><?php echo esc_html($taskspn_value); ?></textarea>
        <?php
        break;
      case 'image':
        ?>
          <div class="taskspn-field taskspn-images-block" <?php echo wp_kses_post($taskspn_parent_block); ?> data-taskspn-multiple="<?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? 'true' : 'false'; ?>">
            <?php if (!empty($taskspn_value)): ?>
              <div class="taskspn-images">
                <?php foreach (explode(',', $taskspn_value) as $taskspn_image): ?>
                  <?php echo wp_get_attachment_image($taskspn_image, 'medium'); ?>
                <?php endforeach ?>
              </div>

              <div class="taskspn-text-align-center taskspn-position-relative"><a href="#" class="taskspn-btn taskspn-btn-mini taskspn-image-btn"><?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? esc_html(__('Edit images', 'taskspn')) : esc_html(__('Edit image', 'taskspn')); ?></a></div>
            <?php else: ?>
              <div class="taskspn-images"></div>

              <div class="taskspn-text-align-center taskspn-position-relative"><a href="#" class="taskspn-btn taskspn-btn-mini taskspn-image-btn"><?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? esc_html(__('Add images', 'taskspn')) : esc_html(__('Add image', 'taskspn')); ?></a></div>
            <?php endif ?>

            <input id="<?php echo esc_attr($taskspn_input['id']); ?>" name="<?php echo esc_attr($taskspn_input['id']); ?>" class="taskspn-display-none taskspn-image-input" type="text" value="<?php echo esc_attr($taskspn_value); ?>"/>
          </div>
        <?php
        break;
      case 'video':
        ?>
        <div class="taskspn-field taskspn-videos-block" <?php echo wp_kses_post($taskspn_parent_block); ?>>
            <?php if (!empty($taskspn_value)): ?>
              <div class="taskspn-videos">
                <?php foreach (explode(',', $taskspn_value) as $taskspn_video): ?>
                  <div class="taskspn-video taskspn-tooltip" title="<?php echo esc_html(get_the_title($taskspn_video)); ?>"><i class="dashicons dashicons-media-video"></i></div>
                <?php endforeach ?>
              </div>

              <div class="taskspn-text-align-center taskspn-position-relative"><a href="#" class="taskspn-btn taskspn-video-btn"><?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? esc_html(__('Edit videos', 'taskspn')) : esc_html(__('Edit video', 'taskspn')); ?></a></div>
            <?php else: ?>
              <div class="taskspn-videos"></div>

              <div class="taskspn-text-align-center taskspn-position-relative"><a href="#" class="taskspn-btn taskspn-video-btn"><?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? esc_html(__('Add videos', 'taskspn')) : esc_html(__('Add video', 'taskspn')); ?></a></div>
            <?php endif ?>

            <input id="<?php echo esc_attr($taskspn_input['id']); ?>" name="<?php echo esc_attr($taskspn_input['id']); ?>" class="taskspn-display-none taskspn-video-input" type="text" value="<?php echo esc_attr($taskspn_value); ?>"/>
          </div>
        <?php
        break;
      case 'audio':
        ?>
          <div class="taskspn-field taskspn-audios-block" <?php echo wp_kses_post($taskspn_parent_block); ?>>
            <?php if (!empty($taskspn_value)): ?>
              <div class="taskspn-audios">
                <?php foreach (explode(',', $taskspn_value) as $taskspn_audio): ?>
                  <div class="taskspn-audio taskspn-tooltip" title="<?php echo esc_html(get_the_title($taskspn_audio)); ?>"><i class="dashicons dashicons-media-audio"></i></div>
                <?php endforeach ?>
              </div>

              <div class="taskspn-text-align-center taskspn-position-relative"><a href="#" class="taskspn-btn taskspn-btn-mini taskspn-audio-btn"><?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? esc_html(__('Edit audios', 'taskspn')) : esc_html(__('Edit audio', 'taskspn')); ?></a></div>
            <?php else: ?>
              <div class="taskspn-audios"></div>

              <div class="taskspn-text-align-center taskspn-position-relative"><a href="#" class="taskspn-btn taskspn-btn-mini taskspn-audio-btn"><?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? esc_html(__('Add audios', 'taskspn')) : esc_html(__('Add audio', 'taskspn')); ?></a></div>
            <?php endif ?>

            <input id="<?php echo esc_attr($taskspn_input['id']); ?>" name="<?php echo esc_attr($taskspn_input['id']); ?>" class="taskspn-display-none taskspn-audio-input" type="text" value="<?php echo esc_attr($taskspn_value); ?>"/>
          </div>
        <?php
        break;
      case 'file':
        ?>
          <div class="taskspn-field taskspn-files-block" <?php echo wp_kses_post($taskspn_parent_block); ?>>
            <?php if (!empty($taskspn_value)): ?>
              <div class="taskspn-files taskspn-text-align-center">
                <?php foreach (explode(',', $taskspn_value) as $taskspn_file): ?>
                  <embed src="<?php echo esc_url(wp_get_attachment_url($taskspn_file)); ?>" type="application/pdf" class="taskspn-embed-file"/>
                <?php endforeach ?>
              </div>

              <div class="taskspn-text-align-center taskspn-position-relative"><a href="#" class="taskspn-btn taskspn-btn-mini taskspn-file-btn"><?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? esc_html(__('Edit files', 'taskspn')) : esc_html(__('Edit file', 'taskspn')); ?></a></div>
            <?php else: ?>
              <div class="taskspn-files"></div>

              <div class="taskspn-text-align-center taskspn-position-relative"><a href="#" class="taskspn-btn taskspn-btn-mini taskspn-btn-mini taskspn-file-btn"><?php echo (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) ? esc_html(__('Add files', 'taskspn')) : esc_html(__('Add file', 'taskspn')); ?></a></div>
            <?php endif ?>

            <input id="<?php echo esc_attr($taskspn_input['id']); ?>" name="<?php echo esc_attr($taskspn_input['id']); ?>" class="taskspn-display-none taskspn-file-input taskspn-btn-mini" type="text" value="<?php echo esc_attr($taskspn_value); ?>"/>
          </div>
        <?php
        break;
      case 'editor':
        ?>
          <div class="taskspn-field" <?php echo wp_kses_post($taskspn_parent_block); ?>>
            <textarea id="<?php echo esc_attr($taskspn_input['id']); ?>" name="<?php echo esc_attr($taskspn_input['id']); ?>" class="taskspn-input taskspn-width-100-percent taskspn-wysiwyg"><?php echo ((empty($taskspn_value)) ? (array_key_exists('placeholder', $taskspn_input) ? esc_attr($taskspn_input['placeholder']) : '') : esc_html($taskspn_value)); ?></textarea>
          </div>
        <?php
        break;
      case 'html':
        ?>
          <div class="taskspn-field" <?php echo wp_kses_post($taskspn_parent_block); ?>>
            <?php echo !empty($taskspn_input['html_content']) ? wp_kses(do_shortcode($taskspn_input['html_content']), TASKSPN_KSES) : ''; ?>
          </div>
        <?php
        break;
      case 'html_multi':
        switch ($taskspn_type) {
          case 'user':
            $html_multi_fields_length = !empty(get_user_meta($taskspn_id, $taskspn_input['html_multi_fields'][0]['id'], true)) ? count(get_user_meta($taskspn_id, $taskspn_input['html_multi_fields'][0]['id'], true)) : 0;
            break;
          case 'post':
            $html_multi_fields_length = !empty(get_post_meta($taskspn_id, $taskspn_input['html_multi_fields'][0]['id'], true)) ? count(get_post_meta($taskspn_id, $taskspn_input['html_multi_fields'][0]['id'], true)) : 0;
            break;
          case 'option':
            $html_multi_fields_length = !empty(get_option($taskspn_input['html_multi_fields'][0]['id'])) ? count(get_option($taskspn_input['html_multi_fields'][0]['id'])) : 0;
        }

        ?>
          <div class="taskspn-field taskspn-html-multi-wrapper taskspn-mb-50" <?php echo wp_kses_post($taskspn_parent_block); ?>>
            <?php if ($html_multi_fields_length): ?>
              <?php foreach (range(0, ($html_multi_fields_length - 1)) as $length_index): ?>
                <div class="taskspn-html-multi-group taskspn-display-table taskspn-width-100-percent taskspn-mb-30">
                  <div class="taskspn-display-inline-table taskspn-width-90-percent">
                    <?php foreach ($taskspn_input['html_multi_fields'] as $index => $html_multi_field): ?>
                      <?php if (isset($html_multi_field['label']) && !empty($html_multi_field['label'])): ?>
                        <label><?php echo esc_html($html_multi_field['label']); ?></label>
                      <?php endif; ?>

                      <?php self::taskspn_input_builder($html_multi_field, $taskspn_type, $taskspn_id, false, true, $length_index); ?>
                    <?php endforeach ?>
                  </div>
                  <div class="taskspn-display-inline-table taskspn-width-10-percent taskspn-text-align-center">
                    <i class="material-icons-outlined taskspn-cursor-move taskspn-multi-sorting taskspn-vertical-align-super taskspn-tooltip" title="<?php esc_html_e('Order element', 'taskspn'); ?>">drag_handle</i>
                  </div>

                  <div class="taskspn-text-align-right">
                    <a href="#" class="taskspn-html-multi-remove-btn"><i class="material-icons-outlined taskspn-cursor-pointer taskspn-tooltip" title="<?php esc_html_e('Remove element', 'taskspn'); ?>">remove</i></a>
                  </div>
                </div>
              <?php endforeach ?>
            <?php else: ?>
              <div class="taskspn-html-multi-group taskspn-mb-50">
                <div class="taskspn-display-inline-table taskspn-width-90-percent">
                  <?php foreach ($taskspn_input['html_multi_fields'] as $html_multi_field): ?>
                    <?php self::taskspn_input_builder($html_multi_field, $taskspn_type); ?>
                  <?php endforeach ?>
                </div>
                <div class="taskspn-display-inline-table taskspn-width-10-percent taskspn-text-align-center">
                  <i class="material-icons-outlined taskspn-cursor-move taskspn-multi-sorting taskspn-vertical-align-super taskspn-tooltip" title="<?php esc_html_e('Order element', 'taskspn'); ?>">drag_handle</i>
                </div>

                <div class="taskspn-text-align-right">
                  <a href="#" class="taskspn-html-multi-remove-btn taskspn-tooltip" title="<?php esc_html_e('Remove element', 'taskspn'); ?>"><i class="material-icons-outlined taskspn-cursor-pointer">remove</i></a>
                </div>
              </div>
            <?php endif ?>

            <div class="taskspn-text-align-right">
              <a href="#" class="taskspn-html-multi-add-btn taskspn-tooltip" title="<?php esc_html_e('Add element', 'taskspn'); ?>"><i class="material-icons-outlined taskspn-cursor-pointer taskspn-font-size-40">add</i></a>
            </div>
          </div>
        <?php
        break;
      case 'audio_recorder':
        // Enqueue CSS and JS files for audio recorder
        wp_enqueue_style('taskspn-audio-recorder', TASKSPN_URL . 'assets/css/taskspn-audio-recorder.css', array(), '1.0.0');
        wp_enqueue_script('taskspn-audio-recorder', TASKSPN_URL . 'assets/js/taskspn-audio-recorder.js', array('jquery'), '1.0.0', true);
        
        // Localize script with AJAX data
        wp_localize_script('taskspn-audio-recorder', 'taskspn_audio_recorder_vars', array(
          'ajax_url' => admin_url('admin-ajax.php'),
          'ajax_nonce' => wp_create_nonce('taskspn_audio_nonce'),
        ));
        
        ?>
          <div class="taskspn-audio-recorder-status taskspn-display-none-soft">
            <p class="taskspn-recording-status"><?php esc_html_e('Ready to record', 'taskspn'); ?></p>
          </div>
          
          <div class="taskspn-audio-recorder-wrapper">
            <div class="taskspn-audio-recorder-controls">
              <div class="taskspn-display-table taskspn-width-100-percent">
                <div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-text-align-center taskspn-mb-20">
                  <button type="button" class="taskspn-btn taskspn-btn-primary taskspn-start-recording" <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                    <i class="material-icons-outlined taskspn-vertical-align-middle">mic</i>
                    <?php esc_html_e('Start recording', 'taskspn'); ?>
                  </button>
                </div>

                <div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-text-align-center taskspn-mb-20">
                  <button type="button" class="taskspn-btn taskspn-btn-secondary taskspn-stop-recording" style="display: none;" <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                    <i class="material-icons-outlined taskspn-vertical-align-middle">stop</i>
                    <?php esc_html_e('Stop recording', 'taskspn'); ?>
                  </button>
                </div>
              </div>

              <div class="taskspn-display-table taskspn-width-100-percent">
                <div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-text-align-center taskspn-mb-20">
                  <button type="button" class="taskspn-btn taskspn-btn-secondary taskspn-play-audio" style="display: none;" <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                    <i class="material-icons-outlined taskspn-vertical-align-middle">play_arrow</i>
                    <?php esc_html_e('Play audio', 'taskspn'); ?>
                  </button>
                </div>

                <div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-text-align-center taskspn-mb-20">
                  <button type="button" class="taskspn-btn taskspn-btn-secondary taskspn-stop-audio" style="display: none;" <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                    <i class="material-icons-outlined taskspn-vertical-align-middle">stop</i>
                    <?php esc_html_e('Stop audio', 'taskspn'); ?>
                  </button>
                </div>
              </div>
            </div>

            <div class="taskspn-audio-recorder-visualizer" style="display: none;">
              <canvas class="taskspn-audio-canvas" width="300" height="60"></canvas>
            </div>

            <div class="taskspn-audio-recorder-timer" style="display: none;">
              <span class="taskspn-recording-time">00:00</span>
            </div>

            <div class="taskspn-audio-transcription-controls taskspn-display-none-soft taskspn-display-table taskspn-width-100-percent taskspn-mb-20">
              <div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-text-align-center">
                <button type="button" class="taskspn-btn taskspn-btn-primary taskspn-transcribe-audio" <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                  <i class="material-icons-outlined taskspn-vertical-align-middle">translate</i>
                  <?php esc_html_e('Transcribe Audio', 'taskspn'); ?>
                </button>
              </div>

              <div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-text-align-center">
                <button type="button" class="taskspn-btn taskspn-btn-secondary taskspn-clear-transcription" <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                  <i class="material-icons-outlined taskspn-vertical-align-middle">clear</i>
                  <?php esc_html_e('Clear', 'taskspn'); ?>
                </button>
              </div>
            </div>

            <div class="taskspn-audio-transcription-loading">
              <?php echo esc_html(TASKSPN_Data::taskspn_loader()); ?>
            </div>

            <div class="taskspn-audio-transcription-result">
              <textarea 
                id="<?php echo esc_attr($taskspn_input['id']); ?>" 
                name="<?php echo esc_attr($taskspn_input['id']); ?>" 
                class="taskspn-field taskspn-transcription-textarea <?php echo array_key_exists('class', $taskspn_input) ? esc_attr($taskspn_input['class']) : ''; ?>" 
                placeholder="<?php echo (array_key_exists('placeholder', $taskspn_input) ? esc_attr($taskspn_input['placeholder']) : esc_attr__('Transcribed text will appear here...', 'taskspn')); ?>"
                <?php echo ((array_key_exists('required', $taskspn_input) && $taskspn_input['required'] == true) ? 'required' : ''); ?>
                <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
                <?php echo wp_kses_post($taskspn_parent_block); ?>
                rows="4"
                style="width: 100%; margin-top: 10px;"
              ><?php echo esc_textarea($taskspn_value); ?></textarea>
            </div>

            <div class="taskspn-audio-transcription-error taskspn-display-none-soft">
              <p class="taskspn-error-message"></p>
            </div>

            <div class="taskspn-audio-transcription-success taskspn-display-none-soft">
              <p class="taskspn-success-message"></p>
            </div>

            <!-- Hidden input to store audio data -->
            <input type="hidden" 
                  id="<?php echo esc_attr($taskspn_input['id']); ?>_audio_data" 
                  name="<?php echo esc_attr($taskspn_input['id']); ?>_audio_data" 
                  value="" />
          </div>
        <?php
        break;
      case 'tags':
        // Get current tags value
        $current_tags = self::taskspn_get_field_value($taskspn_input['id'], $taskspn_type, $taskspn_id, $taskspn_meta_array, $taskspn_array_index, $taskspn_input);
        $tags_array = is_array($current_tags) ? $current_tags : [];
        $tags_string = implode(', ', $tags_array);
        ?>
        <div class="taskspn-tags-wrapper" <?php echo wp_kses_post($taskspn_parent_block); ?>>
          <input type="text" 
            id="<?php echo esc_attr($taskspn_input['id']); ?>" 
            name="<?php echo esc_attr($taskspn_input['id']); ?>" 
            class="taskspn-field taskspn-tags-input <?php echo array_key_exists('class', $taskspn_input) ? esc_attr($taskspn_input['class']) : ''; ?>" 
            value="<?php echo esc_attr($tags_string); ?>" 
            placeholder="<?php echo (array_key_exists('placeholder', $taskspn_input) ? esc_attr($taskspn_input['placeholder']) : ''); ?>"
            <?php echo ((array_key_exists('required', $taskspn_input) && $taskspn_input['required'] == true) ? 'required' : ''); ?>
            <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> />
          
          <div class="taskspn-tags-suggestions" style="display: none;">
            <div class="taskspn-tags-suggestions-list"></div>
          </div>
          
          <div class="taskspn-tags-display">
            <?php if (!empty($tags_array)): ?>
              <?php foreach ($tags_array as $tag): ?>
                <span class="taskspn-tag">
                  <?php echo esc_html($tag); ?>
                  <i class="material-icons-outlined taskspn-tag-remove">close</i>
                </span>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          
          <input type="hidden" 
            id="<?php echo esc_attr($taskspn_input['id']); ?>_tags_array" 
            name="<?php echo esc_attr($taskspn_input['id']); ?>_tags_array" 
            value="<?php echo esc_attr(json_encode($tags_array)); ?>" />
        </div>
        <?php
        break;
      case 'taxonomy':
        $taxonomy = !empty($taskspn_input['taxonomy']) ? $taskspn_input['taxonomy'] : 'category';
        $is_multiple = array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple'];
        
        // Get current taxonomy terms
        $current_terms = [];
        if ($taskspn_type === 'post' && !empty($taskspn_id)) {
          $terms = wp_get_post_terms($taskspn_id, $taxonomy, ['fields' => 'ids']);
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
        <div class="taskspn-taxonomy-wrapper" <?php echo wp_kses_post($taskspn_parent_block); ?>>
          <select 
            id="<?php echo esc_attr($taskspn_input['id']); ?>" 
            name="<?php echo esc_attr($taskspn_input['id']) . ($is_multiple ? '[]' : ''); ?>" 
            class="taskspn-field taskspn-taxonomy-select <?php echo array_key_exists('class', $taskspn_input) ? esc_attr($taskspn_input['class']) : ''; ?>"
            <?php echo $is_multiple ? 'multiple' : ''; ?>
            <?php echo ((array_key_exists('required', $taskspn_input) && $taskspn_input['required'] == true) ? 'required' : ''); ?>
            <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
            data-taxonomy="<?php echo esc_attr($taxonomy); ?>"
            data-taskspn-allow-new="<?php echo (array_key_exists('allow_new', $taskspn_input) && $taskspn_input['allow_new'] == true) ? 'true' : 'false'; ?>"
          >
            <?php if (array_key_exists('placeholder', $taskspn_input) && !empty($taskspn_input['placeholder'])): ?>
              <option value=""><?php echo esc_html($taskspn_input['placeholder']); ?></option>
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
          
          <?php if (array_key_exists('allow_new', $taskspn_input) && $taskspn_input['allow_new'] == true): ?>
            <div class="taskspn-taxonomy-add-new taskspn-mt-10">
              <input 
                type="text" 
                class="taskspn-taxonomy-new-name taskspn-input taskspn-width-70-percent" 
                placeholder="<?php echo esc_attr__('New category name', 'taskspn'); ?>"
                <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
              />
              <button 
                type="button" 
                class="taskspn-btn taskspn-btn-mini taskspn-taxonomy-add-btn taskspn-width-30-percent"
                <?php echo (((array_key_exists('disabled', $taskspn_input) && $taskspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
              >
                <?php esc_html_e('Add', 'taskspn'); ?>
              </button>
            </div>
          <?php endif; ?>
        </div>
        <?php
        break;
    }
  }

  public static function taskspn_input_wrapper_builder($input_array, $type, $taskspn_id = 0, $disabled = 0, $taskspn_format = 'half'){
    ?>
      <?php if (array_key_exists('section', $input_array) && !empty($input_array['section'])): ?>      
        <?php if ($input_array['section'] == 'start'): ?>
          <div class="taskspn-toggle-wrapper taskspn-section-wrapper taskspn-position-relative taskspn-mb-30 <?php echo array_key_exists('class', $input_array) ? esc_attr($input_array['class']) : ''; ?>" id="<?php echo array_key_exists('id', $input_array) ? esc_attr($input_array['id']) : ''; ?>">
            <?php if (array_key_exists('description', $input_array) && !empty($input_array['description'])): ?>
              <i class="material-icons-outlined taskspn-section-helper taskspn-color-main-0 taskspn-tooltip" title="<?php echo wp_kses_post($input_array['description']); ?>">help</i>
            <?php endif ?>

            <a href="#" class="taskspn-toggle taskspn-width-100-percent taskspn-text-decoration-none">
              <div class="taskspn-display-table taskspn-width-100-percent taskspn-mb-20">
                <div class="taskspn-display-inline-table taskspn-width-90-percent">
                  <label class="taskspn-cursor-pointer taskspn-mb-20 taskspn-color-main-0"><?php echo wp_kses_post($input_array['label']); ?></label>
                </div>
                <div class="taskspn-display-inline-table taskspn-width-10-percent taskspn-text-align-right">
                  <i class="material-icons-outlined taskspn-cursor-pointer taskspn-color-main-0">add</i>
                </div>
              </div>
            </a>

            <div class="taskspn-content taskspn-pl-10 taskspn-toggle-content taskspn-mb-20 taskspn-display-none-soft">
        <?php elseif ($input_array['section'] == 'end'): ?>
            </div>
          </div>
        <?php endif ?>
      <?php else: ?>
        <div class="taskspn-input-wrapper <?php echo esc_attr($input_array['id']); ?> <?php echo !empty($input_array['tabs']) ? 'taskspn-input-tabbed' : ''; ?> taskspn-input-field-<?php echo esc_attr($input_array['input']); ?> <?php echo (!empty($input_array['required']) && $input_array['required'] == true) ? 'taskspn-input-field-required' : ''; ?> <?php echo ($disabled) ? 'taskspn-input-field-disabled' : ''; ?> taskspn-mb-30">
          <?php if (array_key_exists('label', $input_array) && !empty($input_array['label'])): ?>
            <div class="taskspn-display-inline-table <?php echo (($taskspn_format == 'half' && !(array_key_exists('type', $input_array) && $input_array['type'] == 'submit')) ? 'taskspn-width-40-percent' : 'taskspn-width-100-percent'); ?> taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-vertical-align-top">
              <div class="taskspn-p-10 <?php echo (array_key_exists('parent', $input_array) && !empty($input_array['parent']) && $input_array['parent'] != 'this') ? 'taskspn-pl-30' : ''; ?>">
                <label class="taskspn-vertical-align-middle taskspn-display-block <?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? 'taskspn-toggle' : ''; ?>" for="<?php echo esc_attr($input_array['id']); ?>"><?php echo wp_kses($input_array['label'], TASKSPN_KSES); ?> <?php echo (array_key_exists('required', $input_array) && !empty($input_array['required']) && $input_array['required'] == true) ? '<span class="taskspn-tooltip" title="' . esc_html(__('Required field', 'taskspn')) . '">*</span>' : ''; ?><?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? '<i class="material-icons-outlined taskspn-cursor-pointer taskspn-float-right">add</i>' : ''; ?></label>

                <?php if (array_key_exists('description', $input_array) && !empty($input_array['description'])): ?>
                  <div class="taskspn-toggle-content taskspn-display-none-soft">
                    <small><?php echo wp_kses_post(wp_specialchars_decode($input_array['description'])); ?></small>
                  </div>
                <?php endif ?>
              </div>
            </div>
          <?php endif ?>

          <div class="taskspn-display-inline-table <?php echo ((array_key_exists('label', $input_array) && empty($input_array['label'])) ? 'taskspn-width-100-percent' : (($taskspn_format == 'half' && !(array_key_exists('type', $input_array) && $input_array['type'] == 'submit')) ? 'taskspn-width-60-percent' : 'taskspn-width-100-percent')); ?> taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-vertical-align-top">
            <div class="taskspn-p-10 <?php echo (array_key_exists('parent', $input_array) && !empty($input_array['parent']) && $input_array['parent'] != 'this') ? 'taskspn-pl-30' : ''; ?>">
              <div class="taskspn-input-field"><?php self::taskspn_input_builder($input_array, $type, $taskspn_id, $disabled); ?></div>
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
   * @param int $taskspn_id The ID of the user/post/option
   * @param int $taskspn_meta_array Whether the field is part of a meta array
   * @param int $taskspn_array_index The index in the meta array
   * @param string $taskspn_format The display format ('half' or 'full')
   * @return string Formatted HTML output
   */
  public static function taskspn_input_display_wrapper($input_array, $type, $taskspn_id = 0, $taskspn_meta_array = 0, $taskspn_array_index = 0, $taskspn_format = 'half') {
    ob_start();
    ?>
    <?php if (array_key_exists('section', $input_array) && !empty($input_array['section'])): ?>      
      <?php if ($input_array['section'] == 'start'): ?>
        <div class="taskspn-toggle-wrapper taskspn-section-wrapper taskspn-position-relative taskspn-mb-30 <?php echo array_key_exists('class', $input_array) ? esc_attr($input_array['class']) : ''; ?>" id="<?php echo array_key_exists('id', $input_array) ? esc_attr($input_array['id']) : ''; ?>">
          <?php if (array_key_exists('description', $input_array) && !empty($input_array['description'])): ?>
            <i class="material-icons-outlined taskspn-section-helper taskspn-color-main-0 taskspn-tooltip" title="<?php echo wp_kses_post($input_array['description']); ?>">help</i>
          <?php endif ?>

          <a href="#" class="taskspn-toggle taskspn-width-100-percent taskspn-text-decoration-none">
            <div class="taskspn-display-table taskspn-width-100-percent taskspn-mb-20">
              <div class="taskspn-display-inline-table taskspn-width-90-percent">
                <label class="taskspn-cursor-pointer taskspn-mb-20 taskspn-color-main-0"><?php echo wp_kses($input_array['label'], TASKSPN_KSES); ?></label>
              </div>
              <div class="taskspn-display-inline-table taskspn-width-10-percent taskspn-text-align-right">
                <i class="material-icons-outlined taskspn-cursor-pointer taskspn-color-main-0">add</i>
              </div>
            </div>
          </a>

          <div class="taskspn-content taskspn-pl-10 taskspn-toggle-content taskspn-mb-20 taskspn-display-none-soft">
      <?php elseif ($input_array['section'] == 'end'): ?>
          </div>
        </div>
      <?php endif ?>
    <?php else: ?>
      <div class="taskspn-input-wrapper <?php echo esc_attr($input_array['id']); ?> taskspn-input-display-<?php echo esc_attr($input_array['input']); ?> <?php echo (!empty($input_array['required']) && $input_array['required'] == true) ? 'taskspn-input-field-required' : ''; ?> taskspn-mb-30">
        <?php if (array_key_exists('label', $input_array) && !empty($input_array['label'])): ?>
          <div class="taskspn-display-inline-table <?php echo ($taskspn_format == 'half' ? 'taskspn-width-40-percent' : 'taskspn-width-100-percent'); ?> taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-vertical-align-top">
            <div class="taskspn-p-10 <?php echo (array_key_exists('parent', $input_array) && !empty($input_array['parent']) && $input_array['parent'] != 'this') ? 'taskspn-pl-30' : ''; ?>">
              <label class="taskspn-vertical-align-middle taskspn-display-block <?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? 'taskspn-toggle' : ''; ?>" for="<?php echo esc_attr($input_array['id']); ?>">
                <?php echo wp_kses($input_array['label'], TASKSPN_KSES); ?>
                <?php echo (array_key_exists('required', $input_array) && !empty($input_array['required']) && $input_array['required'] == true) ? '<span class="taskspn-tooltip" title="' . esc_html(__('Required field', 'taskspn')) . '">*</span>' : ''; ?>
                <?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? '<i class="material-icons-outlined taskspn-cursor-pointer taskspn-float-right">add</i>' : ''; ?>
              </label>

              <?php if (array_key_exists('description', $input_array) && !empty($input_array['description'])): ?>
                <div class="taskspn-toggle-content taskspn-display-none-soft">
                  <small><?php echo wp_kses_post(wp_specialchars_decode($input_array['description'])); ?></small>
                </div>
              <?php endif ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="taskspn-display-inline-table <?php echo ((array_key_exists('label', $input_array) && empty($input_array['label'])) ? 'taskspn-width-100-percent' : ($taskspn_format == 'half' ? 'taskspn-width-60-percent' : 'taskspn-width-100-percent')); ?> taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-vertical-align-top">
          <div class="taskspn-p-10 <?php echo (array_key_exists('parent', $input_array) && !empty($input_array['parent']) && $input_array['parent'] != 'this') ? 'taskspn-pl-30' : ''; ?>">
            <div class="taskspn-input-field">
              <?php self::taskspn_input_display($input_array, $type, $taskspn_id, $taskspn_meta_array, $taskspn_array_index); ?>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
  }

  /**
   * Display formatted values of taskspn_input_builder fields in frontend
   * 
   * @param array $taskspn_input The input array containing field configuration
   * @param string $taskspn_type The type of field (user, post, option)
   * @param int $taskspn_id The ID of the user/post/option
   * @param int $taskspn_meta_array Whether the field is part of a meta array
   * @param int $taskspn_array_index The index in the meta array
   * @return string Formatted HTML output of field values
   */
  public static function taskspn_input_display($taskspn_input, $taskspn_type, $taskspn_id = 0, $taskspn_meta_array = 0, $taskspn_array_index = 0) {
    // Get the current value using the new function
    $current_value = self::taskspn_get_field_value($taskspn_input['id'], $taskspn_type, $taskspn_id, $taskspn_meta_array, $taskspn_array_index, $taskspn_input);

    // Start the field value display
    ?>
      <div class="taskspn-field-value">
        <?php
        switch ($taskspn_input['input']) {
          case 'input':
            switch ($taskspn_input['type']) {
              case 'hidden':
                break;
              case 'nonce':
                break;
              case 'file':
                if (!empty($current_value)) {
                  $file_url = wp_get_attachment_url($current_value);
                  ?>
                    <div class="taskspn-file-display">
                      <a href="<?php echo esc_url($file_url); ?>" target="_blank" class="taskspn-file-link">
                        <?php echo esc_html(basename($file_url)); ?>
                      </a>
                    </div>
                  <?php
                } else {
                  echo '<span class="taskspn-no-file">' . esc_html__('No file uploaded', 'taskspn') . '</span>';
                }
                break;

              case 'checkbox':
                ?>
                  <div class="taskspn-checkbox-display">
                    <span class="taskspn-checkbox-status <?php echo $current_value === 'on' ? 'checked' : 'unchecked'; ?>">
                      <?php echo $current_value === 'on' ? esc_html__('Yes', 'taskspn') : esc_html__('No', 'taskspn'); ?>
                    </span>
                  </div>
                <?php
                break;

              case 'radio':
                if (!empty($taskspn_input['radio_options'])) {
                  foreach ($taskspn_input['radio_options'] as $option) {
                    if ($current_value === $option['value']) {
                      ?>
                        <span class="taskspn-radio-selected"><?php echo esc_html($option['label']); ?></span>
                      <?php
                    }
                  }
                }
                break;

              case 'color':
                $color_value = !empty($current_value) ? trim($current_value) : '#d45500';
                // Ensure color value is valid hex color
                if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color_value)) {
                  $color_value = '#d45500';
                }
                ?>
                  <div class="taskspn-color-display">
                    <span class="taskspn-color-preview" style="background-color: <?php echo esc_attr($color_value); ?> !important; display: inline-block; width: 24px; height: 24px; border-radius: 4px; border: 1px solid #e0e0e0;"></span>
                    <span class="taskspn-color-value"><?php echo esc_html($color_value); ?></span>
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
                          <span class="taskspn-date-value"><?php echo esc_html($formatted_date); ?></span>
                        <?php
                      } else {
                        ?>
                          <span class="taskspn-text-value"><?php echo esc_html($current_value); ?></span>
                        <?php
                      }
                    } else {
                      ?>
                        <span class="taskspn-text-value"><?php echo esc_html($current_value); ?></span>
                      <?php
                    }
                  } catch (Exception $e) {
                    ?>
                      <span class="taskspn-text-value"><?php echo esc_html($current_value); ?></span>
                    <?php
                  }
                } else {
                  ?>
                    <span class="taskspn-text-value"><?php echo esc_html($current_value); ?></span>
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
                          <span class="taskspn-datetime-value"><?php echo esc_html($formatted_datetime); ?></span>
                        <?php
                      } else {
                        ?>
                          <span class="taskspn-text-value"><?php echo esc_html($current_value); ?></span>
                        <?php
                      }
                    } else {
                      ?>
                        <span class="taskspn-text-value"><?php echo esc_html($current_value); ?></span>
                      <?php
                    }
                  } catch (Exception $e) {
                    ?>
                      <span class="taskspn-text-value"><?php echo esc_html($current_value); ?></span>
                    <?php
                  }
                } else {
                  ?>
                    <span class="taskspn-text-value"><?php echo esc_html($current_value); ?></span>
                  <?php
                }
                break;

              case 'time':
                // Time fields can be displayed as-is or formatted
                ?>
                  <span class="taskspn-time-value"><?php echo esc_html($current_value); ?></span>
                <?php
                break;

              default:
                ?>
                  <span class="taskspn-text-value"><?php echo esc_html($current_value); ?></span>
                <?php
                break;
            }
            break;

          case 'select':
            if (!empty($taskspn_input['options']) && is_array($taskspn_input['options'])) {
              if (array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple']) {
                // Handle multiple select
                $selected_values = is_array($current_value) ? $current_value : array();
                if (!empty($selected_values)) {
                  ?>
                  <div class="taskspn-select-values taskspn-select-values-column">
                    <?php foreach ($selected_values as $value): ?>
                      <?php if (isset($taskspn_input['options'][$value])): ?>
                        <div class="taskspn-select-value-item"><?php echo esc_html($taskspn_input['options'][$value]); ?></div>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </div>
                  <?php
                }
              } else {
                // Handle single select
                $current_value = is_scalar($current_value) ? (string)$current_value : '';
                if (isset($taskspn_input['options'][$current_value])) {
                  ?>
                  <span class="taskspn-select-value"><?php echo esc_html($taskspn_input['options'][$current_value]); ?></span>
                  <?php
                }
              }
            }
            break;

          case 'textarea':
            ?>
              <div class="taskspn-textarea-value"><?php echo wp_kses_post(nl2br($current_value)); ?></div>
            <?php
            break;
          case 'image':
            if (!empty($current_value)) {
              $image_ids = is_array($current_value) ? $current_value : explode(',', $current_value);
              ?>
                <div class="taskspn-image-gallery">
                  <?php foreach ($image_ids as $image_id): ?>
                    <div class="taskspn-image-item">
                      <?php echo wp_get_attachment_image($image_id, 'medium'); ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php
            } else {
              ?>
                <span class="taskspn-no-image"><?php esc_html_e('No images uploaded', 'taskspn'); ?></span>
              <?php
            }
            break;
          case 'editor':
            ?>
              <div class="taskspn-editor-content"><?php echo wp_kses_post($current_value); ?></div>
            <?php
            break;
          case 'html':
            if (!empty($taskspn_input['html_content'])) {
              ?>
                <div class="taskspn-html-content"><?php echo wp_kses_post(do_shortcode($taskspn_input['html_content'])); ?></div>
              <?php
            }
            break;
          case 'html_multi':
            switch ($taskspn_type) {
              case 'user':
                $html_multi_fields_length = !empty(get_user_meta($taskspn_id, $taskspn_input['html_multi_fields'][0]['id'], true)) ? count(get_user_meta($taskspn_id, $taskspn_input['html_multi_fields'][0]['id'], true)) : 0;
                break;
              case 'post':
                $html_multi_fields_length = !empty(get_post_meta($taskspn_id, $taskspn_input['html_multi_fields'][0]['id'], true)) ? count(get_post_meta($taskspn_id, $taskspn_input['html_multi_fields'][0]['id'], true)) : 0;
                break;
              case 'option':
                $html_multi_fields_length = !empty(get_option($taskspn_input['html_multi_fields'][0]['id'])) ? count(get_option($taskspn_input['html_multi_fields'][0]['id'])) : 0;
            }

            ?>
              <div class="taskspn-html-multi-content">
                <?php if ($html_multi_fields_length): ?>
                  <?php foreach (range(0, ($html_multi_fields_length - 1)) as $length_index): ?>
                    <div class="taskspn-html-multi-group taskspn-display-table taskspn-width-100-percent taskspn-mb-30">
                      <?php foreach ($taskspn_input['html_multi_fields'] as $index => $html_multi_field): ?>
                          <div class="taskspn-display-inline-table taskspn-width-60-percent">
                            <label><?php echo esc_html($html_multi_field['label']); ?></label>
                          </div>

                          <div class="taskspn-display-inline-table taskspn-width-40-percent">
                            <?php self::taskspn_input_display($html_multi_field, $taskspn_type, $taskspn_id, 1, $length_index); ?>
                          </div>
                      <?php endforeach ?>
                    </div>
                  <?php endforeach ?>
                <?php endif; ?>
              </div>
            <?php
            break;
          case 'taxonomy':
            $taxonomy = !empty($taskspn_input['taxonomy']) ? $taskspn_input['taxonomy'] : 'category';
            $is_multiple = array_key_exists('multiple', $taskspn_input) && $taskspn_input['multiple'];
            
            // Get current taxonomy terms
            $current_terms = [];
            if ($taskspn_type === 'post' && !empty($taskspn_id)) {
              $terms = wp_get_post_terms($taskspn_id, $taxonomy, ['fields' => 'all']);
              $current_terms = is_array($terms) && !is_wp_error($terms) ? $terms : [];
            }
            
            if (!empty($current_terms)) {
              if ($is_multiple) {
                ?>
                <div class="taskspn-taxonomy-values">
                  <?php foreach ($current_terms as $term): ?>
                    <span class="taskspn-taxonomy-term"><?php echo esc_html($term->name); ?></span>
                  <?php endforeach; ?>
                </div>
                <?php
              } else {
                ?>
                <span class="taskspn-taxonomy-term"><?php echo esc_html($current_terms[0]->name); ?></span>
                <?php
              }
            }
            break;
        }
        ?>
      </div>
    <?php
  }

  public static function taskspn_sanitizer($value, $node = '', $type = '', $field_config = []) {
    // Use the new validation system
    $result = TASKSPN_Validation::taskspn_validate_and_sanitize($value, $node, $type, $field_config);
    
    // If validation failed, return empty value and log the error
    if (is_wp_error($result)) {
        return '';
    }
    
    return $result;
  }
}