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
          var popupElement = $('#taskspn-popup-taskspn_task-view');
          
          if (popupElement.length) {
            // Determine if user is logged in and which handler to use
            // Try to use taskspn_ajax handler first (for logged in users)
            // If that fails, fallback to nopriv handler
            var ajax_url = (typeof taskspn_ajax !== 'undefined' && taskspn_ajax.ajax_url) 
              ? taskspn_ajax.ajax_url 
              : ((typeof taskspn_calendar_vars !== 'undefined' && taskspn_calendar_vars.ajax_url) 
                ? taskspn_calendar_vars.ajax_url 
                : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'));
            
            // Try logged in handler first if nonce is available
            var data = {};
            var useNoprivHandler = false;
            
            if (typeof taskspn_ajax !== 'undefined' && taskspn_ajax.taskspn_ajax_nonce) {
              // Try logged in handler
              data = {
                action: 'taskspn_ajax',
                taskspn_ajax_type: 'taskspn_task_view',
                taskspn_ajax_nonce: taskspn_ajax.taskspn_ajax_nonce,
                taskspn_task_id: taskId
              };
            } else {
              // Use nopriv handler
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
                // Add timestamp to prevent caching
                var timestamp = new Date().getTime();
                var ajaxUrlWithTimestamp = ajax_url + (ajax_url.indexOf('?') > -1 ? '&' : '?') + '_=' + timestamp;
                
                $.ajax({
                  url: ajaxUrlWithTimestamp,
                  type: 'POST',
                  data: data,
                  dataType: 'json',
                  cache: false,
                  success: function(response) {
                    try {
                      // Handle JSON response (may already be parsed or a string)
                      var response_json = typeof response === 'object' ? response : JSON.parse(response);
                      
                      if (response_json.error_key) {
                        var errorMsg = response_json.error_content || 'An error occurred while loading the task.';
                        popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorMsg + '</div>');
                        return;
                      }
                      
                      if (response_json && response_json.html) {
                        popupElement.find('.taskspn-popup-content').html(response_json.html);
                      } else {
                        var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                        popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                      }
                    } catch (e) {
                      console.error('Error parsing task view response:', e);
                      var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                      popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                    }
                  },
                  error: function(xhr, status, error) {
                    console.error('AJAX error loading task view:', error, xhr);
                    console.error('Response:', xhr.responseText);
                    console.error('Status:', xhr.status);
                    console.error('Data sent:', data);
                    console.error('Using nopriv handler:', useNoprivHandler);
                    console.error('AJAX URL:', ajax_url);
                    
                    // If we got a 400 with "0" response and we were using logged in handler, try nopriv handler
                    if ((xhr.responseText === '0' || xhr.status === 400) && !useNoprivHandler) {
                      console.log('Retrying with nopriv handler...');
                      
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
                      
                      // Retry with nopriv handler
                      $.ajax({
                        url: ajax_url,
                        type: 'POST',
                        data: noprivData,
                        dataType: 'json',
                        cache: false,
                        success: function(response) {
                          try {
                            var response_json = typeof response === 'object' ? response : JSON.parse(response);
                            
                            if (response_json.error_key) {
                              var errorMsg = response_json.error_content || 'An error occurred while loading the task.';
                              popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorMsg + '</div>');
                              return;
                            }
                            
                            if (response_json && response_json.html) {
                              popupElement.find('.taskspn-popup-content').html(response_json.html);
                            } else {
                              var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                              popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                            }
                          } catch (e) {
                            console.error('Error parsing task view response:', e);
                            var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                            popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                          }
                        },
                        error: function(xhr2, status2, error2) {
                          console.error('AJAX error with nopriv handler:', error2, xhr2);
                          var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                          
                          if (xhr2.responseText) {
                            try {
                              var errorResponse = JSON.parse(xhr2.responseText);
                              if (errorResponse.error_content) {
                                errorText = errorResponse.error_content;
                              }
                            } catch (e) {
                              // Not JSON, use default error
                            }
                          }
                          
                          popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
                        }
                      });
                      return; // Exit early, we're retrying
                    }
                    
                    var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
                    
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
                        // Not JSON, use default error
                      }
                    }
                    
                    popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + errorText + '</div>');
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

