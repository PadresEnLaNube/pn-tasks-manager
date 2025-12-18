(function($) {
  'use strict';

  $(document).ready(function() {
    var calendarWrapper = $('.pn-tasks-manager-calendar-wrapper');
    
    if (calendarWrapper.length) {
      // Get current calendar state
      var currentView = calendarWrapper.attr('data-calendar-view') || 'month';
      var currentYear = parseInt(calendarWrapper.attr('data-calendar-year')) || new Date().getFullYear();
      var currentMonth = parseInt(calendarWrapper.attr('data-calendar-month')) || (new Date().getMonth() + 1);
      var currentDay = parseInt(calendarWrapper.attr('data-calendar-day')) || new Date().getDate();

      // View selector
      $(document).on('click', '.pn-tasks-manager-calendar-view-btn', function(e) {
        e.preventDefault();
        var newView = $(this).attr('data-view');
        showLoader();
        changeView(newView, currentYear, currentMonth, currentDay);
      });

      // Filter checkbox handler (for admins)
      $(document).on('change', '.pn-tasks-manager-calendar-filter-checkbox', function(e) {
        e.preventDefault();
        var hideOthers = $(this).is(':checked');
        showLoader();
        changeView(currentView, currentYear, currentMonth, currentDay, hideOthers);
      });

      // Navigation buttons
      $(document).on('click', '.pn-tasks-manager-calendar-nav-btn', function(e) {
        e.preventDefault();
        var action = $(this).attr('data-action');
        showLoader();
        handleNavigation(action);
      });

      // Task click handler (open task view using existing popup system)
      $(document).on('click', '.pn-tasks-manager-calendar-task-item', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var taskId = $(this).attr('data-task-id');
        if (taskId) {
          openTaskView(taskId);
        }
      });

      // Task icon click handler (open task view using existing popup system)
      $(document).on('click', '.pn-tasks-manager-calendar-task-icon, .pn-tasks-manager-calendar-year-task-icon', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var taskId = $(this).attr('data-task-id');
        if (taskId) {
          openTaskView(taskId);
        }
      });

      // Year view day click handler - navigate to day view
      $(document).on('click', '.pn-tasks-manager-calendar-year-day-clickable', function(e) {
        e.preventDefault();
        var year = parseInt($(this).attr('data-calendar-year'));
        var month = parseInt($(this).attr('data-calendar-month'));
        var day = parseInt($(this).attr('data-calendar-day'));
        
        if (year && month && day) {
          changeView('day', year, month, day);
        }
      });

      // Year view month title click handler - navigate to month view
      $(document).on('click', '.pn-tasks-manager-calendar-year-month-title-clickable', function(e) {
        e.preventDefault();
        var year = parseInt($(this).attr('data-calendar-year'));
        var month = parseInt($(this).attr('data-calendar-month'));
        
        if (year && month) {
          changeView('month', year, month, 1);
        }
      });

      // Month view day number click handler - navigate to day view
      $(document).on('click', '.pn-tasks-manager-calendar-day-number-clickable', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent triggering task clicks
        var year = parseInt($(this).attr('data-calendar-year'));
        var month = parseInt($(this).attr('data-calendar-month'));
        var day = parseInt($(this).attr('data-calendar-day'));
        
        if (year && month && day) {
          changeView('day', year, month, day);
        }
      });

      // Handle browser back/forward buttons
      $(window).on('popstate', function(e) {
        if (e.originalEvent.state) {
          var state = e.originalEvent.state;
          var url = new URL(window.location.href);
          var view = url.searchParams.get('calendar_view') || state.view || currentView;
          var year = parseInt(url.searchParams.get('calendar_year')) || state.year || currentYear;
          var month = parseInt(url.searchParams.get('calendar_month')) || state.month || currentMonth;
          var day = parseInt(url.searchParams.get('calendar_day')) || state.day || currentDay;
          var hideOthers = url.searchParams.get('hide_others') === '1' || (state.hide_others === true);
          
          changeView(view, year, month, day, hideOthers);
        } else {
          // Reload from URL params
          var url = new URL(window.location.href);
          var view = url.searchParams.get('calendar_view') || currentView;
          var year = parseInt(url.searchParams.get('calendar_year')) || currentYear;
          var month = parseInt(url.searchParams.get('calendar_month')) || currentMonth;
          var day = parseInt(url.searchParams.get('calendar_day')) || currentDay;
          var hideOthers = url.searchParams.get('hide_others') === '1';
          
          changeView(view, year, month, day, hideOthers);
        }
      });

      function showLoader() {
        var loaderWrapper = calendarWrapper.find('.pn-tasks-manager-calendar-loader-wrapper');
        var calendarContent = calendarWrapper.find('.pn-tasks-manager-calendar-content');
        
        if (loaderWrapper.length) {
          loaderWrapper.find('.pn-tasks-manager-waiting').removeClass('pn-tasks-manager-display-none').addClass('pn-tasks-manager-display-block');
          calendarContent.css('opacity', '0.5').css('pointer-events', 'none');
        }
      }

      function hideLoader() {
        var loaderWrapper = calendarWrapper.find('.pn-tasks-manager-calendar-loader-wrapper');
        var calendarContent = calendarWrapper.find('.pn-tasks-manager-calendar-content');
        
        if (loaderWrapper.length) {
          loaderWrapper.find('.pn-tasks-manager-waiting').removeClass('pn-tasks-manager-display-block').addClass('pn-tasks-manager-display-none');
          calendarContent.css('opacity', '1').css('pointer-events', 'auto');
        }
      }

      function changeView(view, year, month, day, hideOthers) {
        showLoader();
        
        // Get hide_others from parameter or from wrapper data attribute
        if (typeof hideOthers === 'undefined') {
          hideOthers = calendarWrapper.attr('data-hide-others') === '1';
        }
        
        // Update URL without reloading
        var url = new URL(window.location.href);
        url.searchParams.set('calendar_view', view);
        url.searchParams.set('calendar_year', year);
        url.searchParams.set('calendar_month', month);
        url.searchParams.set('calendar_day', day);
        if (hideOthers) {
          url.searchParams.set('hide_others', '1');
        } else {
          url.searchParams.delete('hide_others');
        }
        window.history.pushState({view: view, year: year, month: month, day: day, hide_others: hideOthers}, '', url.toString());
        
        // Update data attributes
        calendarWrapper.attr('data-calendar-view', view);
        calendarWrapper.attr('data-calendar-year', year);
        calendarWrapper.attr('data-calendar-month', month);
        calendarWrapper.attr('data-calendar-day', day);
        calendarWrapper.attr('data-hide-others', hideOthers ? '1' : '0');
        
        // Update current variables
        currentView = view;
        currentYear = year;
        currentMonth = month;
        currentDay = day;
        
        // Update active button
        $('.pn-tasks-manager-calendar-view-btn').removeClass('active');
        $('.pn-tasks-manager-calendar-view-btn[data-view="' + view + '"]').addClass('active');
        
        // Update filter checkbox if it exists
        var filterCheckbox = calendarWrapper.find('.pn-tasks-manager-calendar-filter-checkbox');
        if (filterCheckbox.length) {
          filterCheckbox.prop('checked', hideOthers);
        }
        
        // Make AJAX request
        // Get AJAX URL from pn_tasks_manager_ajax (if available) or pn_tasks_manager_calendar_vars or use default
        var ajax_url = (typeof pn_tasks_manager_ajax !== 'undefined' && pn_tasks_manager_ajax.ajax_url) 
          ? pn_tasks_manager_ajax.ajax_url 
          : ((typeof pn_tasks_manager_calendar_vars !== 'undefined' && pn_tasks_manager_calendar_vars.ajax_url) 
            ? pn_tasks_manager_calendar_vars.ajax_url 
            : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'));
        
        // Determine which handler to use based on nonce availability
        var useNoprivHandler = !(typeof pn_tasks_manager_ajax !== 'undefined' && pn_tasks_manager_ajax.pn_tasks_manager_ajax_nonce);
        var ajax_nonce = (typeof pn_tasks_manager_ajax !== 'undefined' && pn_tasks_manager_ajax.pn_tasks_manager_ajax_nonce) 
          ? pn_tasks_manager_ajax.pn_tasks_manager_ajax_nonce 
          : ((typeof pn_tasks_manager_calendar_vars !== 'undefined' && pn_tasks_manager_calendar_vars.ajax_nonce) 
            ? pn_tasks_manager_calendar_vars.ajax_nonce 
            : '');
        
        // Build data based on handler type
        var ajaxData = {};
        if (useNoprivHandler) {
          ajaxData = {
            action: 'pn_tasks_manager_ajax_nopriv',
            pn_tasks_manager_ajax_nopriv_type: 'pn_tasks_manager_calendar_view',
            calendar_view: view,
            calendar_year: year,
            calendar_month: month,
            calendar_day: day,
            hide_others: hideOthers ? 1 : 0
          };
          if (ajax_nonce) {
            ajaxData.pn_tasks_manager_ajax_nopriv_nonce = ajax_nonce;
          }
        } else {
          ajaxData = {
            action: 'pn_tasks_manager_ajax',
            pn_tasks_manager_ajax_type: 'pn_tasks_manager_calendar_view',
            pn_tasks_manager_ajax_nonce: ajax_nonce,
            calendar_view: view,
            calendar_year: year,
            calendar_month: month,
            calendar_day: day,
            hide_others: hideOthers ? 1 : 0
          };
        }
        
        $.ajax({
          url: ajax_url,
          type: 'POST',
          data: ajaxData,
          success: function(response) {
            hideLoader();
            
            try {
              var response_json = typeof response === 'object' ? response : JSON.parse(response);
              
              if (response_json.error_key) {
                // Don't reload page on error, just show error
                return;
              }
              
              if (response_json.html) {
                var calendarContent = calendarWrapper.find('.pn-tasks-manager-calendar-content');
                
                // Update hide_others if provided in response
                if (typeof response_json.hide_others !== 'undefined') {
                  calendarWrapper.attr('data-hide-others', response_json.hide_others ? '1' : '0');
                }
                
                // Replace content with fade effect
                calendarContent.fadeOut(100, function() {
                  // Destroy existing tooltips before replacing content
                  $('.pn-tasks-manager-tooltip').each(function() {
                    if ($(this).data('tooltipster')) {
                      $(this).tooltipster('destroy');
                    }
                  });
                  
                  $(this).html(response_json.html).fadeIn(300, function() {
                    // Initialize tooltips after content is loaded
                    if ($('.pn-tasks-manager-tooltip').length) {
                      $('.pn-tasks-manager-tooltip').not('.tooltipstered').tooltipster({
                        maxWidth: 300,
                        delayTouch: [0, 4000],
                        customClass: 'pn-tasks-manager-tooltip'
                      });
                    }
                  });
                });
              } else {
              }
            } catch (e) {
              hideLoader();
            }
          },
          error: function(xhr, status, error) {
            
            // If we got a 400 with "0" response and we were using logged in handler, try nopriv handler
            if ((xhr.responseText === '0' || xhr.status === 400) && !useNoprivHandler) {
              
              // Retry with nopriv handler
              var nopriv_nonce = '';
              if (typeof pn_tasks_manager_calendar_vars !== 'undefined' && pn_tasks_manager_calendar_vars.ajax_nonce) {
                nopriv_nonce = pn_tasks_manager_calendar_vars.ajax_nonce;
              }
              
              var noprivData = {
                action: 'pn_tasks_manager_ajax_nopriv',
                pn_tasks_manager_ajax_nopriv_type: 'pn_tasks_manager_calendar_view',
                calendar_view: view,
                calendar_year: year,
                calendar_month: month,
                calendar_day: day
              };
              
              if (nopriv_nonce) {
                noprivData.pn_tasks_manager_ajax_nopriv_nonce = nopriv_nonce;
              }
              
              // Retry with nopriv handler
              $.ajax({
                url: ajax_url,
                type: 'POST',
                data: noprivData,
                cache: false,
                success: function(response) {
                  hideLoader();
                  
                  try {
                    var response_json = typeof response === 'object' ? response : JSON.parse(response);
                    
                    if (response_json.error_key) {
                      return;
                    }
                    
                    if (response_json.html) {
                      var calendarContent = calendarWrapper.find('.pn-tasks-manager-calendar-content');
                      
                      // Replace content with fade effect
                      calendarContent.fadeOut(100, function() {
                        // Destroy existing tooltips before replacing content
                        $('.pn-tasks-manager-tooltip').each(function() {
                          if ($(this).data('tooltipster')) {
                            $(this).tooltipster('destroy');
                          }
                        });
                        
                        $(this).html(response_json.html).fadeIn(300, function() {
                          // Initialize tooltips after content is loaded
                          if ($('.pn-tasks-manager-tooltip').length) {
                            $('.pn-tasks-manager-tooltip').not('.tooltipstered').tooltipster({
                              maxWidth: 300,
                              delayTouch: [0, 4000],
                              customClass: 'pn-tasks-manager-tooltip'
                            });
                          }
                        });
                      });
                    } else {
                    }
                  } catch (e) {
                    hideLoader();
                  }
                },
                error: function(xhr2, status2, error2) {
                  hideLoader();
                  // Show error message instead of reloading
                  var errorMsg = 'Error loading calendar. Please try again.';
                  if (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) {
                    errorMsg = pn_tasks_manager_i18n.an_error_has_occurred;
                  }
                  alert(errorMsg);
                }
              });
              return; // Exit early, we're retrying
            }
            
            hideLoader();
            // Show error message instead of reloading
            var errorMsg = 'Error loading calendar. Please try again.';
            if (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) {
              errorMsg = pn_tasks_manager_i18n.an_error_has_occurred;
            }
            alert(errorMsg);
          }
        });
      }

      function handleNavigation(action) {
        var newYear = currentYear;
        var newMonth = currentMonth;
        var newDay = currentDay;
        var date = new Date(currentYear, currentMonth - 1, currentDay);

        switch (action) {
          case 'prev-month':
            date.setMonth(date.getMonth() - 1);
            newYear = date.getFullYear();
            newMonth = date.getMonth() + 1;
            break;
          case 'next-month':
            date.setMonth(date.getMonth() + 1);
            newYear = date.getFullYear();
            newMonth = date.getMonth() + 1;
            break;
          case 'prev-week':
            date.setDate(date.getDate() - 7);
            newYear = date.getFullYear();
            newMonth = date.getMonth() + 1;
            newDay = date.getDate();
            break;
          case 'next-week':
            date.setDate(date.getDate() + 7);
            newYear = date.getFullYear();
            newMonth = date.getMonth() + 1;
            newDay = date.getDate();
            break;
          case 'prev-day':
            date.setDate(date.getDate() - 1);
            newYear = date.getFullYear();
            newMonth = date.getMonth() + 1;
            newDay = date.getDate();
            break;
          case 'next-day':
            date.setDate(date.getDate() + 1);
            newYear = date.getFullYear();
            newMonth = date.getMonth() + 1;
            newDay = date.getDate();
            break;
          case 'prev-year':
            newYear = currentYear - 1;
            break;
          case 'next-year':
            newYear = currentYear + 1;
            break;
        }

        // Use changeView which now handles AJAX
        changeView(currentView, newYear, newMonth, newDay);
      }

      // Hide loader when page loads (in case it's stuck)
      hideLoader();
      
      // Hide loader when page is fully loaded
      $(window).on('load', function() {
        hideLoader();
        
        // Initialize tooltips on page load (only if not already initialized)
        if ($('.pn-tasks-manager-tooltip').length) {
          $('.pn-tasks-manager-tooltip').not('.tooltipstered').tooltipster({
            maxWidth: 300,
            delayTouch: [0, 4000],
            customClass: 'pn-tasks-manager-tooltip'
          });
        }
      });

      // Refresh calendar when a task is saved
      $(document).on('pn_tasks_manager_task_saved', function(e, taskId) {
        // Get current calendar state from wrapper or URL
        var wrapperView = calendarWrapper.attr('data-calendar-view');
        var wrapperYear = parseInt(calendarWrapper.attr('data-calendar-year'));
        var wrapperMonth = parseInt(calendarWrapper.attr('data-calendar-month'));
        var wrapperDay = parseInt(calendarWrapper.attr('data-calendar-day'));
        
        var url = new URL(window.location.href);
        var urlView = url.searchParams.get('calendar_view');
        var urlYear = parseInt(url.searchParams.get('calendar_year'));
        var urlMonth = parseInt(url.searchParams.get('calendar_month'));
        var urlDay = parseInt(url.searchParams.get('calendar_day'));
        
        // Use wrapper values first, then URL, then defaults
        var refreshView = wrapperView || urlView || 'month';
        var refreshYear = wrapperYear || urlYear || new Date().getFullYear();
        var refreshMonth = wrapperMonth || urlMonth || (new Date().getMonth() + 1);
        var refreshDay = wrapperDay || urlDay || new Date().getDate();
        
        // Refresh calendar view after a short delay to ensure popup is closed
        setTimeout(function() {
          changeView(refreshView, refreshYear, refreshMonth, refreshDay);
        }, 300);
      });

      function openTaskView(taskId) {
        // Use existing popup system
        if (typeof PN_TASKS_MANAGER_Popups !== 'undefined') {
          var ajax_url = (typeof pn_tasks_manager_ajax !== 'undefined' && pn_tasks_manager_ajax.ajax_url) 
            ? pn_tasks_manager_ajax.ajax_url 
            : ((typeof pn_tasks_manager_calendar_vars !== 'undefined' && pn_tasks_manager_calendar_vars.ajax_url) 
              ? pn_tasks_manager_calendar_vars.ajax_url 
              : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'));
          
          // If user is logged in, try to open pn_tasks_manager_task_check first (for task owners)
          // If that fails or user is not logged in, open pn_tasks_manager_task_view
          var isLoggedIn = (typeof pn_tasks_manager_ajax !== 'undefined' && pn_tasks_manager_ajax.pn_tasks_manager_ajax_nonce);
          var popupElement = null;
          var ajaxType = 'pn_tasks_manager_task_view';
          var popupId = 'pn-tasks-manager-popup-pn_tasks_task-view';
          
          if (isLoggedIn) {
            // Try check popup first for logged in users
            var checkPopupElement = $('#pn-tasks-manager-popup-pn_tasks_task-check');
            if (checkPopupElement.length) {
              popupElement = checkPopupElement;
              ajaxType = 'pn_tasks_manager_task_check';
              popupId = 'pn-tasks-manager-popup-pn_tasks_task-check';
            }
          }

          // Fallback to view popup if check popup not found or user not logged in
          if (!popupElement) {
            popupElement = $('#pn-tasks-manager-popup-pn_tasks_task-view');
            ajaxType = 'pn_tasks_manager_task_view';
            popupId = 'pn-tasks-manager-popup-pn_tasks_task-view';
          }
          
          if (popupElement.length) {
            // Determine which handler to use
            var data = {};
            var useNoprivHandler = false;
            
            if (isLoggedIn) {
              // Try logged in handler
              data = {
                action: 'pn_tasks_manager_ajax',
                pn_tasks_manager_ajax_type: ajaxType,
                pn_tasks_manager_ajax_nonce: pn_tasks_manager_ajax.pn_tasks_manager_ajax_nonce,
                pn_tasks_manager_task_id: taskId
              };
            } else {
              // Use nopriv handler (only for view, not check)
              useNoprivHandler = true;
              var nopriv_nonce = '';
              if (typeof pn_tasks_manager_calendar_vars !== 'undefined' && pn_tasks_manager_calendar_vars.ajax_nonce) {
                nopriv_nonce = pn_tasks_manager_calendar_vars.ajax_nonce;
              }
              
              data = {
                action: 'pn_tasks_manager_ajax_nopriv',
                pn_tasks_manager_ajax_nopriv_type: 'pn_tasks_manager_task_view',
                pn_tasks_manager_task_id: taskId
              };
              
              if (nopriv_nonce) {
                data.pn_tasks_manager_ajax_nopriv_nonce = nopriv_nonce;
              }
            }

            // Show loading state
            var loadingText = (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.loading) ? pn_tasks_manager_i18n.loading : 'Loading...';
            popupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + loadingText + '</div>');
            
            PN_TASKS_MANAGER_Popups.open(popupElement, {
              beforeShow: function() {
                // Add timestamp to prevent caching
                var timestamp = new Date().getTime();
                var ajaxUrlWithTimestamp = ajax_url + (ajax_url.indexOf('?') > -1 ? '&' : '?') + '_=' + timestamp;
                
                $.ajax({
                  url: ajaxUrlWithTimestamp,
                  type: 'POST',
                  data: data,
                  dataType: 'json',
                  cache: false,
                  beforeSend: function(xhr) {
                  },
                  success: function(response, textStatus, xhr) {
                    
                    try {
                      // Handle JSON response (may already be parsed or a string)
                      var response_json = typeof response === 'object' ? response : JSON.parse(response);
                      
                      if (response_json.error_key) {
                        // If we tried to open check popup and got an error, try view popup with nopriv handler instead
                        if (ajaxType === 'pn_tasks_manager_task_check' && !useNoprivHandler) {
                          // Switch to view popup with nopriv handler (user is not logged in)
                          var viewPopupElement = $('#pn-tasks-manager-popup-pn_tasks_task-view');
                          if (viewPopupElement.length) {
                            PN_TASKS_MANAGER_Popups.close();
                            viewPopupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + loadingText + '</div>');
                            
                            var nopriv_nonce = '';
                            if (typeof pn_tasks_manager_calendar_vars !== 'undefined' && pn_tasks_manager_calendar_vars.ajax_nonce) {
                              nopriv_nonce = pn_tasks_manager_calendar_vars.ajax_nonce;
                            }
                            
                            var viewData = {
                              action: 'pn_tasks_manager_ajax_nopriv',
                              pn_tasks_manager_ajax_nopriv_type: 'pn_tasks_manager_task_view',
                              pn_tasks_manager_task_id: taskId
                            };
                            
                            if (nopriv_nonce) {
                              viewData.pn_tasks_manager_ajax_nopriv_nonce = nopriv_nonce;
                            }
                            
                            PN_TASKS_MANAGER_Popups.open(viewPopupElement, {
                              beforeShow: function() {
                                var viewTimestamp = new Date().getTime();
                                var viewAjaxUrl = ajax_url + (ajax_url.indexOf('?') > -1 ? '&' : '?') + '_=' + viewTimestamp;
                                
                                $.ajax({
                                  url: viewAjaxUrl,
                                  type: 'POST',
                                  data: viewData,
                                  dataType: 'json',
                                  cache: false,
                                  success: function(viewResponse) {
                                    try {
                                      var viewResponseJson = typeof viewResponse === 'object' ? viewResponse : JSON.parse(viewResponse);
                                      if (viewResponseJson && viewResponseJson.html) {
                                        viewPopupElement.find('.pn-tasks-manager-popup-content').html(viewResponseJson.html);
                                      } else {
                                        var errorText = (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading task';
                                        viewPopupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorText + '</div>');
                                      }
                                    } catch (e) {
                                      var errorText = (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading task';
                                      viewPopupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorText + '</div>');
                                    }
                                  },
                                  error: function() {
                                    var errorText = (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading task';
                                    viewPopupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorText + '</div>');
                                  }
                                });
                              }
                            });
                            return;
                          }
                        }
                        
                        var errorMsg = response_json.error_content || 'An error occurred while loading the task.';
                        popupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorMsg + '</div>');
                        return;
                      }
                      
                      if (response_json && response_json.html) {
                        popupElement.find('.pn-tasks-manager-popup-content').html(response_json.html);
                      } else {
                        var errorText = (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading task';
                        popupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorText + '</div>');
                      }
                    } catch (e) {
                      var errorText = (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading task';
                      popupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorText + '</div>');
                    }
                  },
                  error: function(xhr, status, error) {
                    
                    // Try to parse response as JSON if possible
                    if (xhr.responseText) {
                      try {
                        var errorResponse = JSON.parse(xhr.responseText);
                      } catch (e) {
                      }
                    }
                    
                    // If we got a 400/500, it means user is not logged in or there's an error
                    // Try to fallback to view popup with nopriv handler
                    
                    if ((xhr.responseText === '0' || xhr.status === 400 || xhr.status === 500) && !useNoprivHandler) {
                      // Switch to view popup with nopriv handler (user is not logged in)
                      var viewPopupElement = $('#pn-tasks-manager-popup-pn_tasks_task-view');
                      if (viewPopupElement.length) {
                        PN_TASKS_MANAGER_Popups.close();
                        viewPopupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + loadingText + '</div>');
                        
                        var nopriv_nonce = '';
                        if (typeof pn_tasks_manager_calendar_vars !== 'undefined' && pn_tasks_manager_calendar_vars.ajax_nonce) {
                          nopriv_nonce = pn_tasks_manager_calendar_vars.ajax_nonce;
                        }
                        
                        var viewData = {
                          action: 'pn_tasks_manager_ajax_nopriv',
                          pn_tasks_manager_ajax_nopriv_type: 'pn_tasks_manager_task_view',
                          pn_tasks_manager_task_id: taskId
                        };
                        
                        if (nopriv_nonce) {
                          viewData.pn_tasks_manager_ajax_nopriv_nonce = nopriv_nonce;
                        }
                        
                        PN_TASKS_MANAGER_Popups.open(viewPopupElement, {
                          beforeShow: function() {
                            var viewTimestamp = new Date().getTime();
                            var viewAjaxUrl = ajax_url + (ajax_url.indexOf('?') > -1 ? '&' : '?') + '_=' + viewTimestamp;
                            
                            $.ajax({
                              url: viewAjaxUrl,
                              type: 'POST',
                              data: viewData,
                              dataType: 'json',
                              cache: false,
                              success: function(viewResponse) {
                                try {
                                  var viewResponseJson = typeof viewResponse === 'object' ? viewResponse : JSON.parse(viewResponse);
                                  if (viewResponseJson && viewResponseJson.html) {
                                    viewPopupElement.find('.pn-tasks-manager-popup-content').html(viewResponseJson.html);
                                  } else {
                                    var errorText = (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading task';
                                    viewPopupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorText + '</div>');
                                  }
                                } catch (e) {
                                  var errorText = (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading task';
                                  viewPopupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorText + '</div>');
                                }
                              },
                              error: function() {
                                var errorText = (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading task';
                                viewPopupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorText + '</div>');
                              }
                            });
                          }
                        });
                        return;
                      } else {
                      }
                    } else {
                    }
                    
                    // Legacy fallback (should not be reached if above works)
                    if ((xhr.responseText === '0' || xhr.status === 400 || xhr.status === 500) && !useNoprivHandler && ajaxType === 'pn_tasks_manager_task_view') {
                      
                      // Retry with nopriv handler
                      var nopriv_nonce = '';
                      if (typeof pn_tasks_manager_calendar_vars !== 'undefined' && pn_tasks_manager_calendar_vars.ajax_nonce) {
                        nopriv_nonce = pn_tasks_manager_calendar_vars.ajax_nonce;
                      }
                      
                      var noprivData = {
                        action: 'pn_tasks_manager_ajax_nopriv',
                        pn_tasks_manager_ajax_nopriv_type: 'pn_tasks_manager_task_view',
                        pn_tasks_manager_task_id: taskId
                      };
                      
                      if (nopriv_nonce) {
                        noprivData.pn_tasks_manager_ajax_nopriv_nonce = nopriv_nonce;
                      }
                      
                      
                      // Retry with nopriv handler
                      $.ajax({
                        url: ajax_url,
                        type: 'POST',
                        data: noprivData,
                        dataType: 'json',
                        cache: false,
                        success: function(response, textStatus2, xhr2) {
                          
                          try {
                            var response_json = typeof response === 'object' ? response : JSON.parse(response);
                            
                            if (response_json.error_key) {
                              var errorMsg = response_json.error_content || 'An error occurred while loading the task.';
                              popupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorMsg + '</div>');
                              return;
                            }
                            
                            if (response_json && response_json.html) {
                              popupElement.find('.pn-tasks-manager-popup-content').html(response_json.html);
                            } else {
                              var errorText = (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading task';
                              popupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorText + '</div>');
                            }
                          } catch (e) {
                            var errorText = (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading task';
                            popupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorText + '</div>');
                          }
                        },
                        error: function(xhr2, status2, error2) {
                          
                          var errorText = (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading task';
                          
                          if (xhr2.responseText) {
                            try {
                              var errorResponse = JSON.parse(xhr2.responseText);
                              if (errorResponse.error_content) {
                                errorText = errorResponse.error_content;
                              }
                            } catch (e) {
                            }
                          }
                          
                          popupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorText + '</div>');
                        }
                      });
                      return; // Exit early, we're retrying
                    }
                    
                    var errorText = (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading task';
                    
                    // Try to parse error response if available
                    if (xhr.responseText && xhr.responseText !== '0') {
                      try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.error_content) {
                          errorText = errorResponse.error_content;
                        } else if (errorResponse.error_key) {
                          errorText = 'Error: ' + errorResponse.error_key;
                        }
                      } catch (e) {
                      }
                    }
                    
                    popupElement.find('.pn-tasks-manager-popup-content').html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + errorText + '</div>');
                  }
                });
              }
            });
          }
        } else {
          // Fallback: redirect to task page
          var taskUrl = window.location.origin + '/?p=' + taskId;
          window.location.href = taskUrl;
        }
      }
    }

    // Keyboard navigation
    $(document).on('keydown', function(e) {
      var calendarWrapper = $('.pn-tasks-manager-calendar-wrapper');
      if (!calendarWrapper.length) return;

      var currentView = calendarWrapper.attr('data-calendar-view') || 'month';

      // Only handle if calendar is in focus
      if (calendarWrapper.is(':visible')) {
        switch(e.keyCode) {
          case 37: // Left arrow
            e.preventDefault();
            if (currentView === 'month') {
              $('.pn-tasks-manager-calendar-prev[data-action="prev-month"]').click();
            } else if (currentView === 'week') {
              $('.pn-tasks-manager-calendar-prev[data-action="prev-week"]').click();
            } else if (currentView === 'day') {
              $('.pn-tasks-manager-calendar-prev[data-action="prev-day"]').click();
            }
            break;
          case 39: // Right arrow
            e.preventDefault();
            if (currentView === 'month') {
              $('.pn-tasks-manager-calendar-next[data-action="next-month"]').click();
            } else if (currentView === 'week') {
              $('.pn-tasks-manager-calendar-next[data-action="next-week"]').click();
            } else if (currentView === 'day') {
              $('.pn-tasks-manager-calendar-next[data-action="next-day"]').click();
            }
            break;
          case 38: // Up arrow
            e.preventDefault();
            if (currentView === 'day') {
              $('.pn-tasks-manager-calendar-prev[data-action="prev-day"]').click();
            } else if (currentView === 'week') {
              $('.pn-tasks-manager-calendar-prev[data-action="prev-week"]').click();
            }
            break;
          case 40: // Down arrow
            e.preventDefault();
            if (currentView === 'day') {
              $('.pn-tasks-manager-calendar-next[data-action="next-day"]').click();
            } else if (currentView === 'week') {
              $('.pn-tasks-manager-calendar-next[data-action="next-week"]').click();
            }
            break;
        }
      }
    });
  });
})(jQuery);

