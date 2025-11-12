(function($) {
  'use strict';

  $(document).ready(function() {
    $(document).on('submit', '.taskspn-form', function(e){
      var taskspn_form = $(this);
      var taskspn_btn = taskspn_form.find('input[type="submit"]');
      taskspn_btn.addClass('taskspn-link-disabled').siblings('.taskspn-waiting').removeClass('taskspn-display-none');

      var ajax_url = taskspn_ajax.ajax_url;
      var data = {
        action: 'taskspn_ajax_nopriv',
        taskspn_ajax_nopriv_nonce: taskspn_ajax.taskspn_ajax_nonce,
        taskspn_get_nonce: taskspn_action.taskspn_get_nonce,
        taskspn_ajax_nopriv_type: 'taskspn_form_save',
        taskspn_form_id: taskspn_form.attr('id'),
        taskspn_form_type: taskspn_btn.attr('data-taskspn-type'),
        taskspn_form_subtype: taskspn_btn.attr('data-taskspn-subtype'),
        taskspn_form_user_id: taskspn_btn.attr('data-taskspn-user-id'),
        taskspn_form_post_id: taskspn_btn.attr('data-taskspn-post-id'),
        taskspn_form_post_type: taskspn_btn.attr('data-taskspn-post-type'),
        taskspn_ajax_keys: [],
      };

      if (!(typeof window['taskspn_window_vars'] !== 'undefined')) {
        window['taskspn_window_vars'] = [];
      }

      $(taskspn_form.find('input:not([type="submit"]), select, textarea')).each(function(index, element) {
        var is_multiple = $(this).attr('multiple');
        var originalName = element.name;
        var baseName = originalName.replace(/\[\]$/, '');

        if (is_multiple) {
          // Multiple fields should post using the base name without []
          if ($(this).is(':checkbox')) {
            // Collect all checked checkboxes sharing the same base name
            var values = [];
            taskspn_form.find('input[name="' + originalName + '"]').each(function(){
              if ($(this).is(':checked')) {
                values.push($(this).val());
              }
            });
            data[baseName] = values;
          } else {
            // For select[multiple] and others, value is already an array
            var val = $(this).val();
            data[baseName] = Array.isArray(val) ? val : (val !== null && typeof val !== 'undefined' ? [val] : []);
          }
        } else {
          if ($(this).is(':checkbox')) {
            if ($(this).is(':checked')) {
              data[originalName] = $(element).val();
            }else{
              data[originalName] = '';
            }
          }else if ($(this).is(':radio')) {
            if ($(this).is(':checked')) {
              data[originalName] = $(element).val();
            }
          }else{
            data[originalName] = $(element).val();
          }
        }

        data.taskspn_ajax_keys.push({
          id: is_multiple ? baseName : originalName,
          node: element.nodeName,
          type: element.type,
          multiple: (is_multiple == 'multiple' ? true : false),
        });
      });

      $.ajax({
        url: ajax_url,
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
          // jQuery will automatically parse JSON when dataType is 'json'
          var response_json = response;

        if (response_json['error_key'] == 'taskspn_form_save_error_unlogged') {
          taskspn_get_main_message(taskspn_i18n.user_unlogged);

          if (!$('.userspn-profile-wrapper .user-unlogged').length) {
            $('.userspn-profile-wrapper').prepend('<div class="userspn-alert userspn-alert-warning user-unlogged">' + taskspn_i18n.user_unlogged + '</div>');
          }

          TASKSPN_Popups.open($('#userspn-profile-popup'));
          $('#userspn-login input#user_login').focus();
        }else if (response_json['error_key'] != '') {
          taskspn_get_main_message(taskspn_i18n.an_error_has_occurred);
        }else {
          taskspn_get_main_message(taskspn_i18n.saved_successfully);
          
          // Dispatch event when a task is saved successfully
          if (data.taskspn_form_post_type == 'taskspn_task' && 
              (data.taskspn_form_subtype == 'post_new' || data.taskspn_form_subtype == 'post_edit')) {
            var taskId = data.taskspn_form_post_id || (response_json['task_id'] ? response_json['task_id'] : 0);
            $(document).trigger('taskspn_task_saved', [taskId]);
          }
        }

        if (response_json['update_list']) {
          $('.taskspn-' + data.taskspn_form_post_type + '-list').html(response_json['update_html']);
        }

        if (response_json['popup_close']) {
          TASKSPN_Popups.close();
          $('.taskspn-menu-more-overlay').fadeOut('fast');
        }

        if (response_json['check'] == 'post_check') {
          TASKSPN_Popups.close();
          $('.taskspn-menu-more-overlay').fadeOut('fast');
          $('.taskspn-' + data.taskspn_form_post_type + '-list-item[data-' + data.taskspn_form_post_type + '-id="' + data.taskspn_form_post_id + '"] .taskspn-check-wrapper i').text('task_alt');
        }else if (response_json['check'] == 'post_uncheck') {
          TASKSPN_Popups.close();
          $('.taskspn-menu-more-overlay').fadeOut('fast');
          $('.taskspn-' + data.taskspn_form_post_type + '-list-item[data-' + data.taskspn_form_post_type + '-id="' + data.taskspn_form_post_id + '"] .taskspn-check-wrapper i').text('radio_button_unchecked');
        }

        taskspn_btn.removeClass('taskspn-link-disabled').siblings('.taskspn-waiting').addClass('taskspn-display-none');
        },
        error: function(xhr, status, error) {
          console.error('AJAX error:', status, error);
          taskspn_get_main_message(taskspn_i18n.an_error_has_occurred);
          taskspn_btn.removeClass('taskspn-link-disabled').siblings('.taskspn-waiting').addClass('taskspn-display-none');
        }
      });

      delete window['taskspn_window_vars'];
      return false;
    });

    $(document).on('click', '.taskspn-popup-open-ajax', function(e) {
      e.preventDefault();

      var taskspn_btn = $(this);
      var taskspn_ajax_type = taskspn_btn.attr('data-taskspn-ajax-type');
      // Try multiple ways to get the task ID
      var taskspn_task_id = taskspn_btn.attr('data-taskspn_task-id') || 
                            taskspn_btn.closest('.taskspn-task').attr('data-taskspn_task-id') ||
                            taskspn_btn.closest('[data-taskspn_task-id]').attr('data-taskspn_task-id');
      var taskspn_popup_element = $('#' + taskspn_btn.attr('data-taskspn-popup-id'));

      TASKSPN_Popups.open(taskspn_popup_element, {
        beforeShow: function(instance, popup) {
          var ajax_url = taskspn_ajax.ajax_url;
          var data = {
            action: 'taskspn_ajax',
            taskspn_ajax_type: taskspn_ajax_type,
            taskspn_ajax_nonce: taskspn_ajax.taskspn_ajax_nonce,
            taskspn_get_nonce: taskspn_action.taskspn_get_nonce,
            taskspn_task_id: taskspn_task_id ? taskspn_task_id : '',
          };

          // Log the data being sent

          $.ajax({
            url: ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
              try {
                
                // Check if response is already an object (parsed JSON)
                var response_json = typeof response === 'object' ? response : null;
                
                // If not an object, try to parse as JSON
                if (!response_json) {
                  try {
                    response_json = JSON.parse(response);
                  } catch (parseError) {
                    // Destroy existing tooltips before replacing content
                    taskspn_popup_element.find('.taskspn-tooltip').each(function() {
                      if ($(this).data('tooltipster')) {
                        $(this).tooltipster('destroy');
                      }
                    });
                    
                    // If parsing fails, assume it's HTML content
                    taskspn_popup_element.find('.taskspn-popup-content').html(response);
                    
                    // Initialize tooltips on new content
                    taskspn_popup_element.find('.taskspn-tooltip').not('.tooltipstered').tooltipster({
                      maxWidth: 300,
                      delayTouch: [0, 4000],
                      customClass: 'taskspn-tooltip'
                    });
                    
                    // Initialize media uploaders if function exists
                    if (typeof initMediaUpload === 'function') {
                      $('.taskspn-image-upload-wrapper').each(function() {
                        initMediaUpload($(this), 'image');
                      });
                      $('.taskspn-audio-upload-wrapper').each(function() {
                        initMediaUpload($(this), 'audio');
                      });
                      $('.taskspn-video-upload-wrapper').each(function() {
                        initMediaUpload($(this), 'video');
                      });
                    }
                    return;
                  }
                }

                // Handle JSON response with error
                if (response_json.error_key) {
                  var errorMessage = response_json.error_content || response_json.error_message || taskspn_i18n.an_error_has_occurred;
                  taskspn_get_main_message(errorMessage);
                  TASKSPN_Popups.close();
                  return;
                }

                // Handle successful JSON response with HTML content
                if (response_json.html) {
                  // Destroy existing tooltips before replacing content
                  taskspn_popup_element.find('.taskspn-tooltip').each(function() {
                    if ($(this).data('tooltipster')) {
                      $(this).tooltipster('destroy');
                    }
                  });
                  
                  taskspn_popup_element.find('.taskspn-popup-content').html(response_json.html);
                  
                  // Initialize tooltips on new content
                  taskspn_popup_element.find('.taskspn-tooltip').not('.tooltipstered').tooltipster({
                    maxWidth: 300,
                    delayTouch: [0, 4000],
                    customClass: 'taskspn-tooltip'
                  });
                  
                  // Initialize media uploaders if function exists
                  if (typeof initMediaUpload === 'function') {
                    $('.taskspn-image-upload-wrapper').each(function() {
                      initMediaUpload($(this), 'image');
                    });
                    $('.taskspn-audio-upload-wrapper').each(function() {
                      initMediaUpload($(this), 'audio');
                    });
                    $('.taskspn-video-upload-wrapper').each(function() {
                      initMediaUpload($(this), 'video');
                    });
                  }
                } else {
                  taskspn_get_main_message(taskspn_i18n.an_error_has_occurred);
                }
              } catch (e) {
                taskspn_get_main_message(taskspn_i18n.an_error_has_occurred);
              }
            },
            error: function(xhr, status, error) {
              taskspn_get_main_message(taskspn_i18n.an_error_has_occurred);
            }
          });
        },
        afterClose: function() {
          taskspn_popup_element.find('.taskspn-popup-content').html('<div class="taskspn-loader-circle-wrapper"><div class="taskspn-text-align-center"><div class="taskspn-loader-circle"><div></div><div></div><div></div><div></div></div></div></div>');
        },
      });
    });

    // Event listener for simple popups (non-AJAX)
    $(document).on('click', '.taskspn-popup-open', function(e) {
      e.preventDefault();

      var taskspn_btn = $(this);
      var taskspn_popup_element = $('#' + taskspn_btn.attr('data-taskspn-popup-id'));

      if (taskspn_popup_element.length) {
        TASKSPN_Popups.open(taskspn_popup_element);
      }
    });

    // Generate event listeners for duplicate and remove functions based on CPTs
    var taskspn_cpts_mapping = {
      'taskspn_asset': 'assets',
      'taskspn_liability': 'liabilities'
    };

    // Loop through CPTs to create duplicate event listeners
    Object.keys(taskspn_cpts).forEach(function(cpt) {
      var cpt_short = cpt.replace('taskspn_', '');
      var container_class = '.taskspn-' + taskspn_cpts_mapping[cpt];
      
      // Duplicate event listener
      $(document).on('click', '.taskspn-' + cpt + '-duplicate-post', function(e) {
        e.preventDefault();

        $(container_class).fadeOut('fast');
        var taskspn_btn = $(this);
        var taskspn_id = taskspn_btn.closest('.taskspn-' + cpt_short).attr('data-taskspn_' + cpt_short + '-id');

        var ajax_url = taskspn_ajax.ajax_url;
        var data = {
          action: 'taskspn_ajax',
          taskspn_ajax_type: 'taskspn_' + cpt_short + '_duplicate',
          ['taskspn_' + cpt_short + '_id']: taskspn_id,
          taskspn_ajax_nonce: taskspn_ajax.taskspn_ajax_nonce,
        };

        $.post(ajax_url, data, function(response) {
          // Handle response - jQuery may have already parsed JSON automatically
          var response_json = typeof response === 'object' && response !== null ? response : JSON.parse(response);

          if (response_json['error_key'] != '') {
            taskspn_get_main_message(response_json['error_content']);
          }else{
            $(container_class).html(response_json['html']);
          }
          
          $(container_class).fadeIn('slow');
          $('.taskspn-menu-more-overlay').fadeOut('fast');
        });
      });

      // Remove event listener (for popup button)
      $(document).on('click', '.taskspn-' + cpt + '-remove', function(e) {
        e.preventDefault();

        $(container_class).fadeOut('fast');
        var taskspn_id = $('.taskspn-menu-more.taskspn-active').closest('.taskspn-' + cpt_short).attr('data-taskspn_' + cpt_short + '-id');

        var ajax_url = taskspn_ajax.ajax_url;
        var data = {
          action: 'taskspn_ajax',
          taskspn_ajax_type: 'taskspn_' + cpt_short + '_remove',
          ['taskspn_' + cpt_short + '_id']: taskspn_id,
          taskspn_ajax_nonce: taskspn_ajax.taskspn_ajax_nonce,
        };

        $.post(ajax_url, data, function(response) {
          // Handle response - jQuery may have already parsed JSON automatically
          var response_json = typeof response === 'object' && response !== null ? response : JSON.parse(response);
         
          if (response_json['error_key'] != '') {
            taskspn_get_main_message(response_json['error_content']);
          }else{
            $(container_class).html(response_json['html']);
            taskspn_get_main_message(taskspn_i18n.removed_successfully);
          }
          
          $(container_class).fadeIn('slow');
          $('.taskspn-menu-more-overlay').fadeOut('fast');

          TASKSPN_Popups.close();
        });
      });
    });
  });
})(jQuery);
