(function($) {
  'use strict';

  $(document).ready(function() {
    var calendarWrapper = $('.taskspn-calendar-wrapper');
    
    if (calendarWrapper.length) {
      // Get current calendar state
      var currentView = calendarWrapper.attr('data-calendar-view') || 'month';
      var currentYear = parseInt(calendarWrapper.attr('data-calendar-year')) || new Date().getFullYear();
      var currentMonth = parseInt(calendarWrapper.attr('data-calendar-month')) || (new Date().getMonth() + 1);
      var currentDay = parseInt(calendarWrapper.attr('data-calendar-day')) || new Date().getDate();

      // View selector
      $(document).on('click', '.taskspn-calendar-view-btn', function(e) {
        e.preventDefault();
        var newView = $(this).attr('data-view');
        showLoader();
        changeView(newView, currentYear, currentMonth, currentDay);
      });

      // Navigation buttons
      $(document).on('click', '.taskspn-calendar-nav-btn', function(e) {
        e.preventDefault();
        var action = $(this).attr('data-action');
        showLoader();
        handleNavigation(action);
      });

      // Task click handler (open task view using existing popup system)
      $(document).on('click', '.taskspn-calendar-task-item', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var taskId = $(this).attr('data-task-id');
        if (taskId) {
          openTaskView(taskId);
        }
      });

      // Task icon click handler (open task view using existing popup system)
      $(document).on('click', '.taskspn-calendar-task-icon, .taskspn-calendar-year-task-icon', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var taskId = $(this).attr('data-task-id');
        if (taskId) {
          openTaskView(taskId);
        }
      });

      // Year view day click handler - navigate to day view
      $(document).on('click', '.taskspn-calendar-year-day-clickable', function(e) {
        e.preventDefault();
        var year = parseInt($(this).attr('data-calendar-year'));
        var month = parseInt($(this).attr('data-calendar-month'));
        var day = parseInt($(this).attr('data-calendar-day'));
        
        if (year && month && day) {
          changeView('day', year, month, day);
        }
      });

      // Year view month title click handler - navigate to month view
      $(document).on('click', '.taskspn-calendar-year-month-title-clickable', function(e) {
        e.preventDefault();
        var year = parseInt($(this).attr('data-calendar-year'));
        var month = parseInt($(this).attr('data-calendar-month'));
        
        if (year && month) {
          changeView('month', year, month, 1);
        }
      });

      // Month view day number click handler - navigate to day view
      $(document).on('click', '.taskspn-calendar-day-number-clickable', function(e) {
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
          
          changeView(view, year, month, day);
        } else {
          // Reload from URL params
          var url = new URL(window.location.href);
          var view = url.searchParams.get('calendar_view') || currentView;
          var year = parseInt(url.searchParams.get('calendar_year')) || currentYear;
          var month = parseInt(url.searchParams.get('calendar_month')) || currentMonth;
          var day = parseInt(url.searchParams.get('calendar_day')) || currentDay;
          
          changeView(view, year, month, day);
        }
      });

      function showLoader() {
        var loaderWrapper = calendarWrapper.find('.taskspn-calendar-loader-wrapper');
        var calendarContent = calendarWrapper.find('.taskspn-calendar-content');
        
        if (loaderWrapper.length) {
          loaderWrapper.find('.taskspn-waiting').removeClass('taskspn-display-none').addClass('taskspn-display-block');
          calendarContent.css('opacity', '0.5').css('pointer-events', 'none');
        }
      }

      function hideLoader() {
        var loaderWrapper = calendarWrapper.find('.taskspn-calendar-loader-wrapper');
        var calendarContent = calendarWrapper.find('.taskspn-calendar-content');
        
        if (loaderWrapper.length) {
          loaderWrapper.find('.taskspn-waiting').removeClass('taskspn-display-block').addClass('taskspn-display-none');
          calendarContent.css('opacity', '1').css('pointer-events', 'auto');
        }
      }

      function changeView(view, year, month, day) {
        showLoader();
        
        // Update URL without reloading
        var url = new URL(window.location.href);
        url.searchParams.set('calendar_view', view);
        url.searchParams.set('calendar_year', year);
        url.searchParams.set('calendar_month', month);
        url.searchParams.set('calendar_day', day);
        window.history.pushState({view: view, year: year, month: month, day: day}, '', url.toString());
        
        // Update data attributes
        calendarWrapper.attr('data-calendar-view', view);
        calendarWrapper.attr('data-calendar-year', year);
        calendarWrapper.attr('data-calendar-month', month);
        calendarWrapper.attr('data-calendar-day', day);
        
        // Update current variables
        currentView = view;
        currentYear = year;
        currentMonth = month;
        currentDay = day;
        
        // Update active button
        $('.taskspn-calendar-view-btn').removeClass('active');
        $('.taskspn-calendar-view-btn[data-view="' + view + '"]').addClass('active');
        
        // Make AJAX request
        // Get AJAX URL from taskspn_ajax (if available) or taskspn_calendar_vars or use default
        var ajax_url = (typeof taskspn_ajax !== 'undefined' && taskspn_ajax.ajax_url) 
          ? taskspn_ajax.ajax_url 
          : ((typeof taskspn_calendar_vars !== 'undefined' && taskspn_calendar_vars.ajax_url) 
            ? taskspn_calendar_vars.ajax_url 
            : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'));
        
        // Determine which handler to use based on nonce availability
        var useNoprivHandler = !(typeof taskspn_ajax !== 'undefined' && taskspn_ajax.taskspn_ajax_nonce);
        var ajax_nonce = (typeof taskspn_ajax !== 'undefined' && taskspn_ajax.taskspn_ajax_nonce) 
          ? taskspn_ajax.taskspn_ajax_nonce 
          : ((typeof taskspn_calendar_vars !== 'undefined' && taskspn_calendar_vars.ajax_nonce) 
            ? taskspn_calendar_vars.ajax_nonce 
            : '');
        
        // Build data based on handler type
        var ajaxData = {};
        if (useNoprivHandler) {
          ajaxData = {
            action: 'taskspn_ajax_nopriv',
            taskspn_ajax_nopriv_type: 'taskspn_calendar_view',
            calendar_view: view,
            calendar_year: year,
            calendar_month: month,
            calendar_day: day
          };
          if (ajax_nonce) {
            ajaxData.taskspn_ajax_nopriv_nonce = ajax_nonce;
          }
        } else {
          ajaxData = {
            action: 'taskspn_ajax',
            taskspn_ajax_type: 'taskspn_calendar_view',
            taskspn_ajax_nonce: ajax_nonce,
            calendar_view: view,
            calendar_year: year,
            calendar_month: month,
            calendar_day: day
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
                console.error('Calendar view error:', response_json.error_content || 'Unknown error');
                // Don't reload page on error, just show error
                return;
              }
              
              if (response_json.html) {
                var calendarContent = calendarWrapper.find('.taskspn-calendar-content');
                
                // Replace content with fade effect
                calendarContent.fadeOut(100, function() {
                  // Destroy existing tooltips before replacing content
                  $('.taskspn-tooltip').each(function() {
                    if ($(this).data('tooltipster')) {
                      $(this).tooltipster('destroy');
                    }
                  });
                  
                  $(this).html(response_json.html).fadeIn(300, function() {
                    // Initialize tooltips after content is loaded
                    if ($('.taskspn-tooltip').length) {
                      $('.taskspn-tooltip').not('.tooltipstered').tooltipster({
                        maxWidth: 300,
                        delayTouch: [0, 4000],
                        customClass: 'taskspn-tooltip'
                      });
                    }
                  });
                });
              } else {
                console.error('No HTML content in calendar response');
              }
            } catch (e) {
              console.error('Error parsing calendar response:', e);
              hideLoader();
            }
          },
          error: function(xhr, status, error) {
            console.error('AJAX error loading calendar view:', error, xhr);
            console.error('Response:', xhr.responseText);
            console.error('Status:', xhr.status);
            console.error('Using nopriv handler:', useNoprivHandler);
            
            // If we got a 400 with "0" response and we were using logged in handler, try nopriv handler
            if ((xhr.responseText === '0' || xhr.status === 400) && !useNoprivHandler) {
              console.log('Retrying calendar view with nopriv handler...');
              
              // Retry with nopriv handler
              var nopriv_nonce = '';
              if (typeof taskspn_calendar_vars !== 'undefined' && taskspn_calendar_vars.ajax_nonce) {
                nopriv_nonce = taskspn_calendar_vars.ajax_nonce;
              }
              
              var noprivData = {
                action: 'taskspn_ajax_nopriv',
                taskspn_ajax_nopriv_type: 'taskspn_calendar_view',
                calendar_view: view,
                calendar_year: year,
                calendar_month: month,
                calendar_day: day
              };
              
              if (nopriv_nonce) {
                noprivData.taskspn_ajax_nopriv_nonce = nopriv_nonce;
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
                      console.error('Calendar view error:', response_json.error_content || 'Unknown error');
                      return;
                    }
                    
                    if (response_json.html) {
                      var calendarContent = calendarWrapper.find('.taskspn-calendar-content');
                      
                      // Replace content with fade effect
                      calendarContent.fadeOut(100, function() {
                        // Destroy existing tooltips before replacing content
                        $('.taskspn-tooltip').each(function() {
                          if ($(this).data('tooltipster')) {
                            $(this).tooltipster('destroy');
                          }
                        });
                        
                        $(this).html(response_json.html).fadeIn(300, function() {
                          // Initialize tooltips after content is loaded
                          if ($('.taskspn-tooltip').length) {
                            $('.taskspn-tooltip').not('.tooltipstered').tooltipster({
                              maxWidth: 300,
                              delayTouch: [0, 4000],
                              customClass: 'taskspn-tooltip'
                            });
                          }
                        });
                      });
                    } else {
                      console.error('No HTML content in calendar response');
                    }
                  } catch (e) {
                    console.error('Error parsing calendar response:', e);
                    hideLoader();
                  }
                },
                error: function(xhr2, status2, error2) {
                  console.error('AJAX error loading calendar view with nopriv handler:', error2, xhr2);
                  hideLoader();
                  // Show error message instead of reloading
                  var errorMsg = 'Error loading calendar. Please try again.';
                  if (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) {
                    errorMsg = taskspn_i18n.an_error_has_occurred;
                  }
                  alert(errorMsg);
                }
              });
              return; // Exit early, we're retrying
            }
            
            hideLoader();
            // Show error message instead of reloading
            var errorMsg = 'Error loading calendar. Please try again.';
            if (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) {
              errorMsg = taskspn_i18n.an_error_has_occurred;
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
        if ($('.taskspn-tooltip').length) {
          $('.taskspn-tooltip').not('.tooltipstered').tooltipster({
            maxWidth: 300,
            delayTouch: [0, 4000],
            customClass: 'taskspn-tooltip'
          });
        }
      });

      // Refresh calendar when a task is saved
      $(document).on('taskspn_task_saved', function(e, taskId) {
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
        if (typeof TASKSPN_Popups !== 'undefined') {
          var ajax_url = (typeof taskspn_ajax !== 'undefined' && taskspn_ajax.ajax_url) 
            ? taskspn_ajax.ajax_url 
            : ((typeof taskspn_calendar_vars !== 'undefined' && taskspn_calendar_vars.ajax_url) 
              ? taskspn_calendar_vars.ajax_url 
              : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'));
          
          // If user is logged in, try to open taskspn_task_check first (for task owners)
          // If that fails or user is not logged in, open taskspn_task_view
          var isLoggedIn = (typeof taskspn_ajax !== 'undefined' && taskspn_ajax.taskspn_ajax_nonce);
          var popupElement = null;
          var ajaxType = 'taskspn_task_view';
          var popupId = 'taskspn-popup-taskspn_task-view';
          
          if (isLoggedIn) {
            // Try check popup first for logged in users
            var checkPopupElement = $('#taskspn-popup-taskspn_task-check');
            if (checkPopupElement.length) {
              popupElement = checkPopupElement;
              ajaxType = 'taskspn_task_check';
              popupId = 'taskspn-popup-taskspn_task-check';
            }
          }
          
          // Fallback to view popup if check popup not found or user not logged in
          if (!popupElement) {
            popupElement = $('#taskspn-popup-taskspn_task-view');
            ajaxType = 'taskspn_task_view';
            popupId = 'taskspn-popup-taskspn_task-view';
          }
          
          if (popupElement.length) {
            // Determine which handler to use
            var data = {};
            var useNoprivHandler = false;
            
            if (isLoggedIn) {
              // Try logged in handler
              data = {
                action: 'taskspn_ajax',
                taskspn_ajax_type: ajaxType,
                taskspn_ajax_nonce: taskspn_ajax.taskspn_ajax_nonce,
                taskspn_task_id: taskId
              };
            } else {
              // Use nopriv handler (only for view, not check)
              useNoprivHandler = true;
              var nopriv_nonce = '';
              if (typeof taskspn_calendar_vars !== 'undefined' && taskspn_calendar_vars.ajax_nonce) {
                nopriv_nonce = taskspn_calendar_vars.ajax_nonce;
              }
              
              data = {
                action: 'taskspn_ajax_nopriv',
                taskspn_ajax_nopriv_type: 'taskspn_task_view',
                taskspn_task_id: taskId
              };
              
              if (nopriv_nonce) {
                data.taskspn_ajax_nopriv_nonce = nopriv_nonce;
              }
            }

            // Show loading state
            var loadingText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.loading) ? taskspn_i18n.loading : 'Loading...';
            popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + loadingText + '</div>');
            
            TASKSPN_Popups.open(popupElement, {
              beforeShow: function() {
                console.log('=== TASKSPN DEBUG: beforeShow callback ===');
                // Add timestamp to prevent caching
                var timestamp = new Date().getTime();
                var ajaxUrlWithTimestamp = ajax_url + (ajax_url.indexOf('?') > -1 ? '&' : '?') + '_=' + timestamp;
                console.log('AJAX URL with timestamp:', ajaxUrlWithTimestamp);
                console.log('Sending POST request...');
                
                $.ajax({
                  url: ajaxUrlWithTimestamp,
                  type: 'POST',
                  data: data,
                  dataType: 'json',
                  cache: false,
                  beforeSend: function(xhr) {
                    console.log('=== TASKSPN DEBUG: beforeSend ===');
                    console.log('Request headers:', xhr.getAllResponseHeaders ? 'Available' : 'Not available');
                  },
                  success: function(response, textStatus, xhr) {
                    console.log('=== TASKSPN DEBUG: AJAX SUCCESS ===');
                    console.log('Response received:', response);
                    console.log('Response type:', typeof response);
                    console.log('Text status:', textStatus);
                    console.log('XHR status:', xhr.status);
                    console.log('XHR statusText:', xhr.statusText);
                    console.log('Response headers:', xhr.getAllResponseHeaders ? xhr.getAllResponseHeaders() : 'Not available');
                    console.log('Response text (first 500 chars):', xhr.responseText ? xhr.responseText.substring(0, 500) : 'No response text');
                    
                    try {
                      // Handle JSON response (may already be parsed or a string)
                      var response_json = typeof response === 'object' ? response : JSON.parse(response);
                      console.log('Parsed response JSON:', response_json);
                      
                      if (response_json.error_key) {
                        // If we tried to open check popup and got an error, try view popup with nopriv handler instead
                        if (ajaxType === 'taskspn_task_check' && !useNoprivHandler) {
                          // Switch to view popup with nopriv handler (user is not logged in)
                          var viewPopupElement = $('#taskspn-popup-taskspn_task-view');
                          if (viewPopupElement.length) {
                            TASKSPN_Popups.close();
                            viewPopupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + loadingText + '</div>');
                            
                            var nopriv_nonce = '';
                            if (typeof taskspn_calendar_vars !== 'undefined' && taskspn_calendar_vars.ajax_nonce) {
                              nopriv_nonce = taskspn_calendar_vars.ajax_nonce;
                            }
                            
                            var viewData = {
                              action: 'taskspn_ajax_nopriv',
                              taskspn_ajax_nopriv_type: 'taskspn_task_view',
                              taskspn_task_id: taskId
                            };
                            
                            if (nopriv_nonce) {
                              viewData.taskspn_ajax_nopriv_nonce = nopriv_nonce;
                            }
                            
                            TASKSPN_Popups.open(viewPopupElement, {
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
                                        viewPopupElement.find('.taskspn-popup-content').html(viewResponseJson.html);
                                      } else {
                                        var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                                        viewPopupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                                      }
                                    } catch (e) {
                                      var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                                      viewPopupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                                    }
                                  },
                                  error: function() {
                                    var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                                    viewPopupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                                  }
                                });
                              }
                            });
                            return;
                          }
                        }
                        
                        var errorMsg = response_json.error_content || 'An error occurred while loading the task.';
                        popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorMsg + '</div>');
                        return;
                      }
                      
                      if (response_json && response_json.html) {
                        console.log('Response HTML length:', response_json.html.length);
                        console.log('Response HTML (first 500 chars):', response_json.html.substring(0, 500));
                        popupElement.find('.taskspn-popup-content').html(response_json.html);
                        console.log('HTML inserted into popup');
                      } else {
                        console.error('Response missing HTML property');
                        console.error('Response keys:', Object.keys(response_json || {}));
                        var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                        popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                      }
                    } catch (e) {
                      console.error('=== TASKSPN DEBUG: Error parsing response ===');
                      console.error('Error:', e);
                      console.error('Error message:', e.message);
                      console.error('Error stack:', e.stack);
                      console.error('Raw response:', response);
                      console.error('Response text:', xhr.responseText);
                      var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                      popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                    }
                  },
                  error: function(xhr, status, error) {
                    console.error('=== TASKSPN DEBUG: AJAX ERROR ===');
                    console.error('Error:', error);
                    console.error('Status:', status);
                    console.error('XHR status:', xhr.status);
                    console.error('XHR statusText:', xhr.statusText);
                    console.error('Response text (full):', xhr.responseText);
                    console.error('Response text length:', xhr.responseText ? xhr.responseText.length : 0);
                    console.error('Response headers:', xhr.getAllResponseHeaders ? xhr.getAllResponseHeaders() : 'Not available');
                    console.error('Data sent:', JSON.stringify(data, null, 2));
                    console.error('Using nopriv handler:', useNoprivHandler);
                    console.error('AJAX URL:', ajax_url);
                    console.error('AJAX URL with timestamp:', ajaxUrlWithTimestamp);
                    console.error('Ready state:', xhr.readyState);
                    console.error('Response type:', xhr.responseType);
                    
                    // Try to parse response as JSON if possible
                    if (xhr.responseText) {
                      try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        console.error('Parsed error response:', errorResponse);
                      } catch (e) {
                        console.error('Could not parse error response as JSON');
                      }
                    }
                    
                    // If we got a 400/500, it means user is not logged in or there's an error
                    // Try to fallback to view popup with nopriv handler
                    console.log('=== TASKSPN DEBUG: Checking error retry conditions ===');
                    console.log('xhr.responseText === "0":', xhr.responseText === '0');
                    console.log('xhr.status === 400:', xhr.status === 400);
                    console.log('xhr.status === 500:', xhr.status === 500);
                    console.log('!useNoprivHandler:', !useNoprivHandler);
                    console.log('Condition result:', (xhr.responseText === '0' || xhr.status === 400 || xhr.status === 500) && !useNoprivHandler);
                    
                    if ((xhr.responseText === '0' || xhr.status === 400 || xhr.status === 500) && !useNoprivHandler) {
                      console.log('=== TASKSPN DEBUG: Attempting to switch to nopriv handler ===');
                      // Switch to view popup with nopriv handler (user is not logged in)
                      var viewPopupElement = $('#taskspn-popup-taskspn_task-view');
                      console.log('View popup element found:', viewPopupElement.length > 0);
                      if (viewPopupElement.length) {
                        TASKSPN_Popups.close();
                        viewPopupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + loadingText + '</div>');
                        
                        var nopriv_nonce = '';
                        if (typeof taskspn_calendar_vars !== 'undefined' && taskspn_calendar_vars.ajax_nonce) {
                          nopriv_nonce = taskspn_calendar_vars.ajax_nonce;
                        }
                        
                        var viewData = {
                          action: 'taskspn_ajax_nopriv',
                          taskspn_ajax_nopriv_type: 'taskspn_task_view',
                          taskspn_task_id: taskId
                        };
                        
                        if (nopriv_nonce) {
                          viewData.taskspn_ajax_nopriv_nonce = nopriv_nonce;
                        }
                        
                        TASKSPN_Popups.open(viewPopupElement, {
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
                                    viewPopupElement.find('.taskspn-popup-content').html(viewResponseJson.html);
                                  } else {
                                    var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                                    viewPopupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                                  }
                                } catch (e) {
                                  var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                                  viewPopupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                                }
                              },
                              error: function() {
                                var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                                viewPopupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                              }
                            });
                          }
                        });
                        return;
                      } else {
                        console.error('=== TASKSPN DEBUG: View popup element not found ===');
                      }
                    } else {
                      console.log('=== TASKSPN DEBUG: Retry conditions not met, showing error ===');
                    }
                    
                    // Legacy fallback (should not be reached if above works)
                    if ((xhr.responseText === '0' || xhr.status === 400 || xhr.status === 500) && !useNoprivHandler && ajaxType === 'taskspn_task_view') {
                      console.log('=== TASKSPN DEBUG: Retrying with nopriv handler ===');
                      
                      // Retry with nopriv handler
                      var nopriv_nonce = '';
                      if (typeof taskspn_calendar_vars !== 'undefined' && taskspn_calendar_vars.ajax_nonce) {
                        nopriv_nonce = taskspn_calendar_vars.ajax_nonce;
                      }
                      
                      var noprivData = {
                        action: 'taskspn_ajax_nopriv',
                        taskspn_ajax_nopriv_type: 'taskspn_task_view',
                        taskspn_task_id: taskId
                      };
                      
                      if (nopriv_nonce) {
                        noprivData.taskspn_ajax_nopriv_nonce = nopriv_nonce;
                      }
                      
                      console.log('Nopriv data to send:', JSON.stringify(noprivData, null, 2));
                      
                      // Retry with nopriv handler
                      $.ajax({
                        url: ajax_url,
                        type: 'POST',
                        data: noprivData,
                        dataType: 'json',
                        cache: false,
                        success: function(response, textStatus2, xhr2) {
                          console.log('=== TASKSPN DEBUG: NOPRIV AJAX SUCCESS ===');
                          console.log('Response received:', response);
                          console.log('XHR status:', xhr2.status);
                          console.log('Response text (first 500 chars):', xhr2.responseText ? xhr2.responseText.substring(0, 500) : 'No response text');
                          
                          try {
                            var response_json = typeof response === 'object' ? response : JSON.parse(response);
                            console.log('Parsed response JSON:', response_json);
                            
                            if (response_json.error_key) {
                              console.error('Response contains error_key:', response_json.error_key);
                              console.error('Error content:', response_json.error_content);
                              var errorMsg = response_json.error_content || 'An error occurred while loading the task.';
                              popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorMsg + '</div>');
                              return;
                            }
                            
                            if (response_json && response_json.html) {
                              console.log('Response HTML length:', response_json.html.length);
                              popupElement.find('.taskspn-popup-content').html(response_json.html);
                              console.log('HTML inserted into popup (nopriv)');
                            } else {
                              console.error('Response missing HTML property (nopriv)');
                              var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                              popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                            }
                          } catch (e) {
                            console.error('=== TASKSPN DEBUG: Error parsing nopriv response ===');
                            console.error('Error:', e);
                            console.error('Error message:', e.message);
                            console.error('Raw response:', response);
                            console.error('Response text:', xhr2.responseText);
                            var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                            popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                          }
                        },
                        error: function(xhr2, status2, error2) {
                          console.error('=== TASKSPN DEBUG: NOPRIV AJAX ERROR ===');
                          console.error('Error:', error2);
                          console.error('Status:', status2);
                          console.error('XHR status:', xhr2.status);
                          console.error('XHR statusText:', xhr2.statusText);
                          console.error('Response text (full):', xhr2.responseText);
                          console.error('Response text length:', xhr2.responseText ? xhr2.responseText.length : 0);
                          
                          var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                          
                          if (xhr2.responseText) {
                            try {
                              var errorResponse = JSON.parse(xhr2.responseText);
                              console.error('Parsed error response (nopriv):', errorResponse);
                              if (errorResponse.error_content) {
                                errorText = errorResponse.error_content;
                              }
                            } catch (e) {
                              console.error('Could not parse error response as JSON (nopriv)');
                            }
                          }
                          
                          popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                        }
                      });
                      return; // Exit early, we're retrying
                    }
                    
                    console.error('=== TASKSPN DEBUG: Not retrying, showing error ===');
                    var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                    
                    // Try to parse error response if available
                    if (xhr.responseText && xhr.responseText !== '0') {
                      try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        console.error('Parsed final error response:', errorResponse);
                        if (errorResponse.error_content) {
                          errorText = errorResponse.error_content;
                        } else if (errorResponse.error_key) {
                          errorText = 'Error: ' + errorResponse.error_key;
                        }
                      } catch (e) {
                        console.error('Could not parse final error response as JSON');
                      }
                    }
                    
                    popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                    console.log('=== TASKSPN DEBUG: openTaskView END ===');
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
      var calendarWrapper = $('.taskspn-calendar-wrapper');
      if (!calendarWrapper.length) return;

      var currentView = calendarWrapper.attr('data-calendar-view') || 'month';

      // Only handle if calendar is in focus
      if (calendarWrapper.is(':visible')) {
        switch(e.keyCode) {
          case 37: // Left arrow
            e.preventDefault();
            if (currentView === 'month') {
              $('.taskspn-calendar-prev[data-action="prev-month"]').click();
            } else if (currentView === 'week') {
              $('.taskspn-calendar-prev[data-action="prev-week"]').click();
            } else if (currentView === 'day') {
              $('.taskspn-calendar-prev[data-action="prev-day"]').click();
            }
            break;
          case 39: // Right arrow
            e.preventDefault();
            if (currentView === 'month') {
              $('.taskspn-calendar-next[data-action="next-month"]').click();
            } else if (currentView === 'week') {
              $('.taskspn-calendar-next[data-action="next-week"]').click();
            } else if (currentView === 'day') {
              $('.taskspn-calendar-next[data-action="next-day"]').click();
            }
            break;
          case 38: // Up arrow
            e.preventDefault();
            if (currentView === 'day') {
              $('.taskspn-calendar-prev[data-action="prev-day"]').click();
            } else if (currentView === 'week') {
              $('.taskspn-calendar-prev[data-action="prev-week"]').click();
            }
            break;
          case 40: // Down arrow
            e.preventDefault();
            if (currentView === 'day') {
              $('.taskspn-calendar-next[data-action="next-day"]').click();
            } else if (currentView === 'week') {
              $('.taskspn-calendar-next[data-action="next-week"]').click();
            }
            break;
        }
      }
    });
  });
})(jQuery);

