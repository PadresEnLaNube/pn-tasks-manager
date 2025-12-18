(function($) {
  'use strict';

  $(document).ready(function() {
    $(document).on('submit', '.pn-tasks-manager-form', function(e){
      var pn_tasks_manager_form = $(this);
      var pn_tasks_manager_btn = pn_tasks_manager_form.find('input[type="submit"]');
      pn_tasks_manager_btn.addClass('pn-tasks-manager-link-disabled').siblings('.pn-tasks-manager-waiting').removeClass('pn-tasks-manager-display-none');

      var ajax_url = pn_tasks_manager_ajax.ajax_url;
      var data = {
        action: 'pn_tasks_manager_ajax_nopriv',
        pn_tasks_manager_ajax_nopriv_nonce: pn_tasks_manager_ajax.pn_tasks_manager_ajax_nonce,
        pn_tasks_manager_get_nonce: pn_tasks_manager_action.pn_tasks_manager_get_nonce,
        pn_tasks_manager_ajax_nopriv_type: 'pn_tasks_manager_form_save',
        pn_tasks_manager_form_id: pn_tasks_manager_form.attr('id'),
        pn_tasks_manager_form_type: pn_tasks_manager_btn.attr('data-pn-tasks-manager-type'),
        pn_tasks_manager_form_subtype: pn_tasks_manager_btn.attr('data-pn-tasks-manager-subtype'),
        pn_tasks_manager_form_user_id: pn_tasks_manager_btn.attr('data-pn-tasks-manager-user-id'),
        pn_tasks_manager_form_post_id: pn_tasks_manager_btn.attr('data-pn-tasks-manager-post-id'),
        pn_tasks_manager_form_post_type: pn_tasks_manager_btn.attr('data-pn-tasks-manager-post-type'),
        pn_tasks_manager_ajax_keys: [],
      };

      if (!(typeof window['pn_tasks_manager_window_vars'] !== 'undefined')) {
        window['pn_tasks_manager_window_vars'] = [];
      }

      $(pn_tasks_manager_form.find('input:not([type="submit"]), select, textarea')).each(function(index, element) {
        var is_multiple = $(this).attr('multiple');
        var originalName = element.name;
        var baseName = originalName.replace(/\[\]$/, '');

        if (is_multiple) {
          // Multiple fields should post using the base name without []
          if ($(this).is(':checkbox')) {
            // Collect all checked checkboxes sharing the same base name
            var values = [];
            pn_tasks_manager_form.find('input[name="' + originalName + '"]').each(function(){
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

        data.pn_tasks_manager_ajax_keys.push({
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

        if (response_json['error_key'] == 'pn_tasks_manager_form_save_error_unlogged') {
          pn_tasks_manager_get_main_message(pn_tasks_manager_i18n.user_unlogged);

          if (!$('.userspn-profile-wrapper .user-unlogged').length) {
            $('.userspn-profile-wrapper').prepend('<div class="userspn-alert userspn-alert-warning user-unlogged">' + pn_tasks_manager_i18n.user_unlogged + '</div>');
          }

          PN_TASKS_MANAGER_Popups.open($('#userspn-profile-popup'));
          $('#userspn-login input#user_login').focus();
        }else if (response_json['error_key'] != '') {
          pn_tasks_manager_get_main_message(pn_tasks_manager_i18n.an_error_has_occurred);
        }else {
          pn_tasks_manager_get_main_message(pn_tasks_manager_i18n.saved_successfully);
          
          // Dispatch event when a task is saved successfully
          if (data.pn_tasks_manager_form_post_type == 'pn_tasks_task' && 
              (data.pn_tasks_manager_form_subtype == 'post_new' || data.pn_tasks_manager_form_subtype == 'post_edit')) {
            var taskId = data.pn_tasks_manager_form_post_id || (response_json['task_id'] ? response_json['task_id'] : 0);
            $(document).trigger('pn_tasks_manager_task_saved', [taskId]);
          }
        }

        if (response_json['update_list']) {
          $('.pn-tasks-manager-' + data.pn_tasks_manager_form_post_type + '-list').html(response_json['update_html']);
        }

        if (response_json['popup_close']) {
          PN_TASKS_MANAGER_Popups.close();
          $('.pn-tasks-manager-menu-more-overlay').fadeOut('fast');
        }

        if (response_json['check'] == 'post_check') {
          PN_TASKS_MANAGER_Popups.close();
          $('.pn-tasks-manager-menu-more-overlay').fadeOut('fast');
          $('.pn-tasks-manager-' + data.pn_tasks_manager_form_post_type + '-list-item[data-' + data.pn_tasks_manager_form_post_type + '-id="' + data.pn_tasks_manager_form_post_id + '"] .pn-tasks-manager-check-wrapper i').text('task_alt');
        }else if (response_json['check'] == 'post_uncheck') {
          PN_TASKS_MANAGER_Popups.close();
          $('.pn-tasks-manager-menu-more-overlay').fadeOut('fast');
          $('.pn-tasks-manager-' + data.pn_tasks_manager_form_post_type + '-list-item[data-' + data.pn_tasks_manager_form_post_type + '-id="' + data.pn_tasks_manager_form_post_id + '"] .pn-tasks-manager-check-wrapper i').text('radio_button_unchecked');
        }

        pn_tasks_manager_btn.removeClass('pn-tasks-manager-link-disabled').siblings('.pn-tasks-manager-waiting').addClass('pn-tasks-manager-display-none');
        },
        error: function(xhr, status, error) {
          pn_tasks_manager_get_main_message(pn_tasks_manager_i18n.an_error_has_occurred);
          pn_tasks_manager_btn.removeClass('pn-tasks-manager-link-disabled').siblings('.pn-tasks-manager-waiting').addClass('pn-tasks-manager-display-none');
        }
      });

      delete window['pn_tasks_manager_window_vars'];
      return false;
    });

    $(document).on('click', '.pn-tasks-manager-popup-open-ajax', function(e) {
      e.preventDefault();

      var pn_tasks_manager_btn = $(this);
      var pn_tasks_manager_ajax_type = pn_tasks_manager_btn.attr('data-pn-tasks-manager-ajax-type');
      
      // Try multiple ways to get the task ID - be very thorough
      var pn_tasks_manager_task_id = null;
      
      // First, try direct attributes on the button
      pn_tasks_manager_task_id = pn_tasks_manager_btn.attr('data-pn_tasks_manager_task-id') || 
                                 pn_tasks_manager_btn.attr('data-pn-tasks-manager-task-id') ||
                                 pn_tasks_manager_btn.data('pn_tasks_manager_task-id') ||
                                 pn_tasks_manager_btn.data('pn-tasks-manager-task-id');
      
      // If not found, try to get from parent elements
      if (!pn_tasks_manager_task_id) {
        // Try closest li with class pn-tasks-manager-task
        var parentLi = pn_tasks_manager_btn.closest('li.pn-tasks-manager-task');
        if (parentLi.length) {
          pn_tasks_manager_task_id = parentLi.attr('data-pn_tasks_manager_task-id') || 
                                     parentLi.data('pn_tasks_manager_task-id');
        }
      }
      
      // If still not found, try any parent with the attribute
      if (!pn_tasks_manager_task_id) {
        var parentWithId = pn_tasks_manager_btn.closest('[data-pn_tasks_manager_task-id]');
        if (parentWithId.length) {
          pn_tasks_manager_task_id = parentWithId.attr('data-pn_tasks_manager_task-id') || 
                                     parentWithId.data('pn_tasks_manager_task-id');
        }
      }
      
      // If still not found, try parent li with any class
      if (!pn_tasks_manager_task_id) {
        var anyParentLi = pn_tasks_manager_btn.closest('li');
        if (anyParentLi.length) {
          pn_tasks_manager_task_id = anyParentLi.attr('data-pn_tasks_manager_task-id') || 
                                     anyParentLi.data('pn_tasks_manager_task-id');
        }
      }
      
      var pn_tasks_manager_popup_element = $('#' + pn_tasks_manager_btn.attr('data-pn-tasks-manager-popup-id'));

      // Debug: Log what we found
      if (!pn_tasks_manager_task_id && (pn_tasks_manager_ajax_type === 'pn_tasks_manager_task_view' || 
          pn_tasks_manager_ajax_type === 'pn_tasks_manager_task_edit' || 
          pn_tasks_manager_ajax_type === 'pn_tasks_manager_task_check')) {
        console.log('Debug: Button element:', pn_tasks_manager_btn);
        console.log('Debug: Parent li:', pn_tasks_manager_btn.closest('li'));
        console.log('Debug: All parents with data attribute:', pn_tasks_manager_btn.parents('[data-pn_tasks_manager_task-id]'));
        console.error('Task ID is required for ' + pn_tasks_manager_ajax_type + '. Button HTML:', pn_tasks_manager_btn[0].outerHTML);
        
        // Try one more time with a broader search
        var allParents = pn_tasks_manager_btn.parents();
        for (var i = 0; i < allParents.length; i++) {
          var parentId = $(allParents[i]).attr('data-pn_tasks_manager_task-id');
          if (parentId) {
            pn_tasks_manager_task_id = parentId;
            console.log('Found task ID in parent:', parentId);
            break;
          }
        }
      }
      
      // Validate that we have a task ID for view/edit/check operations
      if (!pn_tasks_manager_task_id && (pn_tasks_manager_ajax_type === 'pn_tasks_manager_task_view' || 
          pn_tasks_manager_ajax_type === 'pn_tasks_manager_task_edit' || 
          pn_tasks_manager_ajax_type === 'pn_tasks_manager_task_check')) {
        console.error('Task ID is required for ' + pn_tasks_manager_ajax_type);
        if (typeof pn_tasks_manager_get_main_message === 'function') {
          pn_tasks_manager_get_main_message('Se requiere el ID de la tarea. Por favor, recarga la página.');
        } else {
          alert('Se requiere el ID de la tarea. Por favor, recarga la página.');
        }
        return false;
      }

      PN_TASKS_MANAGER_Popups.open(pn_tasks_manager_popup_element, {
        beforeShow: function(instance, popup) {
          var ajax_url = pn_tasks_manager_ajax.ajax_url;
          var data = {
            action: 'pn_tasks_manager_ajax',
            pn_tasks_manager_ajax_type: pn_tasks_manager_ajax_type,
            pn_tasks_manager_ajax_nonce: pn_tasks_manager_ajax.pn_tasks_manager_ajax_nonce,
            pn_tasks_manager_get_nonce: pn_tasks_manager_action.pn_tasks_manager_get_nonce,
            pn_tasks_manager_task_id: pn_tasks_manager_task_id ? pn_tasks_manager_task_id : '',
          };

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
                    pn_tasks_manager_popup_element.find('.pn-tasks-manager-tooltip').each(function() {
                      if ($(this).data('tooltipster')) {
                        $(this).tooltipster('destroy');
                      }
                    });
                    
                    // If parsing fails, assume it's HTML content
                    pn_tasks_manager_popup_element.find('.pn-tasks-manager-popup-content').html(response);
                    
                    // Initialize tooltips on new content
                    pn_tasks_manager_popup_element.find('.pn-tasks-manager-tooltip').not('.tooltipstered').tooltipster({
                      maxWidth: 300,
                      delayTouch: [0, 4000],
                      customClass: 'pn-tasks-manager-tooltip'
                    });
                    
                    // Initialize media uploaders if function exists
                    if (typeof initMediaUpload === 'function') {
                      $('.pn-tasks-manager-image-upload-wrapper').each(function() {
                        initMediaUpload($(this), 'image');
                      });
                      $('.pn-tasks-manager-audio-upload-wrapper').each(function() {
                        initMediaUpload($(this), 'audio');
                      });
                      $('.pn-tasks-manager-video-upload-wrapper').each(function() {
                        initMediaUpload($(this), 'video');
                      });
                    }
                    return;
                  }
                }

                // Handle JSON response with error
                if (response_json.error_key) {
                  var errorMessage = response_json.error_content || response_json.error_message || pn_tasks_manager_i18n.an_error_has_occurred;
                  pn_tasks_manager_get_main_message(errorMessage);
                  PN_TASKS_MANAGER_Popups.close();
                  return;
                }

                // Handle successful JSON response with HTML content
                if (response_json.html) {
                  // Destroy existing tooltips before replacing content
                  pn_tasks_manager_popup_element.find('.pn-tasks-manager-tooltip').each(function() {
                    if ($(this).data('tooltipster')) {
                      $(this).tooltipster('destroy');
                    }
                  });
                  
                  pn_tasks_manager_popup_element.find('.pn-tasks-manager-popup-content').html(response_json.html);
                  
                  // Initialize tooltips on new content
                  pn_tasks_manager_popup_element.find('.pn-tasks-manager-tooltip').not('.tooltipstered').tooltipster({
                    maxWidth: 300,
                    delayTouch: [0, 4000],
                    customClass: 'pn-tasks-manager-tooltip'
                  });
                  
                  // Initialize media uploaders if function exists
                  if (typeof initMediaUpload === 'function') {
                    $('.pn-tasks-manager-image-upload-wrapper').each(function() {
                      initMediaUpload($(this), 'image');
                    });
                    $('.pn-tasks-manager-audio-upload-wrapper').each(function() {
                      initMediaUpload($(this), 'audio');
                    });
                    $('.pn-tasks-manager-video-upload-wrapper').each(function() {
                      initMediaUpload($(this), 'video');
                    });
                  }
                } else {
                  pn_tasks_manager_get_main_message(pn_tasks_manager_i18n.an_error_has_occurred);
                }
              } catch (e) {
                pn_tasks_manager_get_main_message(pn_tasks_manager_i18n.an_error_has_occurred);
              }
            },
            error: function(xhr, status, error) {
              pn_tasks_manager_get_main_message(pn_tasks_manager_i18n.an_error_has_occurred);
            }
          });
        },
        afterClose: function() {
          pn_tasks_manager_popup_element.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-loader-circle-wrapper"><div class="pn-tasks-manager-text-align-center"><div class="pn-tasks-manager-loader-circle"><div></div><div></div><div></div><div></div></div></div></div>');
        },
      });
    });

    // Event listener for simple popups (non-AJAX)
    $(document).on('click', '.pn-tasks-manager-popup-open', function(e) {
      e.preventDefault();

      var pn_tasks_manager_btn = $(this);
      var pn_tasks_manager_popup_element = $('#' + pn_tasks_manager_btn.attr('data-pn-tasks-manager-popup-id'));

      if (pn_tasks_manager_popup_element.length) {
        PN_TASKS_MANAGER_Popups.open(pn_tasks_manager_popup_element);
      }
    });

    // Generate event listeners for duplicate and remove functions based on CPTs
    var pn_tasks_manager_cpts_mapping = {
      'pn_tasks_manager_asset': 'assets',
      'pn_tasks_manager_liability': 'liabilities'
    };

    // Store task ID when opening remove popup - intercept before popup opens
    $(document).on('click', 'a[data-pn-tasks-manager-popup-id="pn-tasks-manager-popup-pn_tasks_task-remove"]', function(e) {
      // Find the task ID from the parent list item
      var taskLi = $(this).closest('li.pn-tasks-manager-task');
      if (taskLi.length) {
        var taskId = taskLi.attr('data-pn_tasks_manager_task-id') || taskLi.data('pn_tasks_manager_task-id');
        if (taskId) {
          // Store the task ID in the remove button of the popup immediately
          var popupId = $(this).attr('data-pn-tasks-manager-popup-id');
          var popup = $('#' + popupId);
          var removeBtn = popup.find('.pn-tasks-manager-pn_tasks_task-remove');
          if (removeBtn.length) {
            removeBtn.attr('data-pn-tasks-manager-task-id', taskId);
          } else {
            // If button doesn't exist yet, wait a bit and try again
            setTimeout(function() {
              var popup = $('#' + popupId);
              var removeBtn = popup.find('.pn-tasks-manager-pn_tasks_task-remove');
              if (removeBtn.length) {
                removeBtn.attr('data-pn-tasks-manager-task-id', taskId);
              }
            }, 50);
          }
        }
      }
    });

    // Special handler for task removal (pn_tasks_task doesn't follow the same pattern)
    $(document).on('click', '.pn-tasks-manager-pn_tasks_task-remove', function(e) {
      e.preventDefault();

      // Try multiple ways to get the task ID
      var pn_tasks_manager_task_id = null;
      
      // First, try to get from the button's data attribute (stored when popup was opened)
      pn_tasks_manager_task_id = $(this).attr('data-pn-tasks-manager-task-id') || $(this).data('pn-tasks-manager-task-id');
      
      // If not found, try to get from the active menu
      if (!pn_tasks_manager_task_id) {
        var activeMenu = $('.pn-tasks-manager-menu-more.pn-tasks-manager-active');
        if (activeMenu.length) {
          var taskLi = activeMenu.closest('li.pn-tasks-manager-task');
          if (taskLi.length) {
            pn_tasks_manager_task_id = taskLi.attr('data-pn_tasks_manager_task-id') || taskLi.data('pn_tasks_manager_task-id');
          }
        }
      }
      
      // If not found, try to get from any task element in the popup context
      if (!pn_tasks_manager_task_id) {
        var popup = $(this).closest('.pn-tasks-manager-popup');
        if (popup.length) {
          // Look for task ID in the popup content or any related element
          var taskElement = popup.find('[data-pn_tasks_manager_task-id]');
          if (taskElement.length) {
            pn_tasks_manager_task_id = taskElement.first().attr('data-pn_tasks_manager_task-id') || taskElement.first().data('pn_tasks_manager_task-id');
          }
        }
      }
      
      // If still not found, try to get from the list item that was clicked to open the menu
      if (!pn_tasks_manager_task_id) {
        // Look for the most recently active menu item's parent
        var menuParent = $('.pn-tasks-manager-menu-more.pn-tasks-manager-active').closest('li.pn-tasks-manager-task');
        if (menuParent.length) {
          pn_tasks_manager_task_id = menuParent.attr('data-pn_tasks_manager_task-id') || menuParent.data('pn_tasks_manager_task-id');
        }
      }
      
      // If still not found, try to find any visible task list item
      if (!pn_tasks_manager_task_id) {
        var visibleTask = $('li.pn-tasks-manager-task:visible').first();
        if (visibleTask.length) {
          pn_tasks_manager_task_id = visibleTask.attr('data-pn_tasks_manager_task-id') || visibleTask.data('pn_tasks_manager_task-id');
        }
      }

      if (!pn_tasks_manager_task_id) {
        pn_tasks_manager_get_main_message(pn_tasks_manager_i18n.an_error_has_occurred || 'Error: Task ID not found');
        PN_TASKS_MANAGER_Popups.close();
        return false;
      }

      var container_class = '.pn-tasks-manager-pn_tasks_task-list-wrapper';
      $(container_class).fadeOut('fast');

      var ajax_url = pn_tasks_manager_ajax.ajax_url;
      var data = {
        action: 'pn_tasks_manager_ajax',
        pn_tasks_manager_ajax_type: 'pn_tasks_manager_task_remove',
        pn_tasks_manager_task_id: pn_tasks_manager_task_id,
        pn_tasks_manager_ajax_nonce: pn_tasks_manager_ajax.pn_tasks_manager_ajax_nonce,
      };

      $.post(ajax_url, data, function(response) {
        try {
          // Handle response - jQuery may have already parsed JSON automatically
          var response_json = typeof response === 'object' && response !== null ? response : JSON.parse(response);
         
          if (response_json['error_key'] && response_json['error_key'] != '') {
            var errorMsg = response_json['error_content'] || response_json['error_message'] || (pn_tasks_manager_i18n && pn_tasks_manager_i18n.an_error_has_occurred) || 'An error occurred';
            if (typeof pn_tasks_manager_get_main_message === 'function') {
              pn_tasks_manager_get_main_message(errorMsg);
            } else {
              alert(errorMsg);
            }
          } else {
            if (response_json['html']) {
              $(container_class).html(response_json['html']);
            }
            var successMsg = (pn_tasks_manager_i18n && pn_tasks_manager_i18n.removed_successfully) || 'Removed successfully';
            if (typeof pn_tasks_manager_get_main_message === 'function') {
              pn_tasks_manager_get_main_message(successMsg);
            }
          }
          
          $(container_class).fadeIn('slow');
          $('.pn-tasks-manager-menu-more-overlay').fadeOut('fast');

          PN_TASKS_MANAGER_Popups.close();
        } catch (e) {
          console.error('Error parsing response:', e, response);
          var errorMsg = (pn_tasks_manager_i18n && pn_tasks_manager_i18n.an_error_has_occurred) || 'An error occurred';
          if (typeof pn_tasks_manager_get_main_message === 'function') {
            pn_tasks_manager_get_main_message(errorMsg);
          } else {
            alert(errorMsg);
          }
          $(container_class).fadeIn('slow');
          PN_TASKS_MANAGER_Popups.close();
        }
      }).fail(function(xhr, status, error) {
        console.error('AJAX error:', status, error, xhr);
        var errorMsg = (pn_tasks_manager_i18n && pn_tasks_manager_i18n.an_error_has_occurred) || 'An error occurred';
        if (typeof pn_tasks_manager_get_main_message === 'function') {
          pn_tasks_manager_get_main_message(errorMsg);
        } else {
          alert(errorMsg);
        }
        $(container_class).fadeIn('slow');
        PN_TASKS_MANAGER_Popups.close();
      });
    });

    // Loop through CPTs to create duplicate event listeners
    Object.keys(pn_tasks_manager_cpts).forEach(function(cpt) {
      var cpt_short = cpt.replace('pn_tasks_manager_', '');
      var container_class = '.pn-tasks-manager-' + pn_tasks_manager_cpts_mapping[cpt];
      
      // Skip if this CPT is not in the mapping (like pn_tasks_task)
      if (!pn_tasks_manager_cpts_mapping[cpt]) {
        return;
      }
      
      // Duplicate event listener
      $(document).on('click', '.pn-tasks-manager-' + cpt + '-duplicate-post', function(e) {
        e.preventDefault();

        $(container_class).fadeOut('fast');
        var pn_tasks_manager_btn = $(this);
        var pn_tasks_manager_id = pn_tasks_manager_btn.closest('.pn-tasks-manager-' + cpt_short).attr('data-pn_tasks_manager_' + cpt_short + '-id');

        var ajax_url = pn_tasks_manager_ajax.ajax_url;
        var data = {
          action: 'pn_tasks_manager_ajax',
          pn_tasks_manager_ajax_type: 'pn_tasks_manager_' + cpt_short + '_duplicate',
          ['pn_tasks_manager_' + cpt_short + '_id']: pn_tasks_manager_id,
          pn_tasks_manager_ajax_nonce: pn_tasks_manager_ajax.pn_tasks_manager_ajax_nonce,
        };

        $.post(ajax_url, data, function(response) {
          // Handle response - jQuery may have already parsed JSON automatically
          var response_json = typeof response === 'object' && response !== null ? response : JSON.parse(response);

          if (response_json['error_key'] != '') {
            pn_tasks_manager_get_main_message(response_json['error_content']);
          }else{
            $(container_class).html(response_json['html']);
          }
          
          $(container_class).fadeIn('slow');
          $('.pn-tasks-manager-menu-more-overlay').fadeOut('fast');
        });
      });

      // Remove event listener (for popup button)
      $(document).on('click', '.pn-tasks-manager-' + cpt + '-remove', function(e) {
        e.preventDefault();

        $(container_class).fadeOut('fast');
        var pn_tasks_manager_id = $('.pn-tasks-manager-menu-more.pn-tasks-manager-active').closest('.pn-tasks-manager-' + cpt_short).attr('data-pn_tasks_manager_' + cpt_short + '-id');

        var ajax_url = pn_tasks_manager_ajax.ajax_url;
        var data = {
          action: 'pn_tasks_manager_ajax',
          pn_tasks_manager_ajax_type: 'pn_tasks_manager_' + cpt_short + '_remove',
          ['pn_tasks_manager_' + cpt_short + '_id']: pn_tasks_manager_id,
          pn_tasks_manager_ajax_nonce: pn_tasks_manager_ajax.pn_tasks_manager_ajax_nonce,
        };

        $.post(ajax_url, data, function(response) {
          // Handle response - jQuery may have already parsed JSON automatically
          var response_json = typeof response === 'object' && response !== null ? response : JSON.parse(response);
         
          if (response_json['error_key'] != '') {
            pn_tasks_manager_get_main_message(response_json['error_content']);
          }else{
            $(container_class).html(response_json['html']);
            pn_tasks_manager_get_main_message(pn_tasks_manager_i18n.removed_successfully);
          }
          
          $(container_class).fadeIn('slow');
          $('.pn-tasks-manager-menu-more-overlay').fadeOut('fast');

          PN_TASKS_MANAGER_Popups.close();
        });
      });
    });
  });
})(jQuery);
