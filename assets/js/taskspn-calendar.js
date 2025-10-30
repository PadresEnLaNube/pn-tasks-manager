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
        if (typeof taskspn_ajax !== 'undefined') {
          $.ajax({
            url: taskspn_ajax.ajax_url,
            type: 'POST',
            data: {
              action: 'taskspn_ajax',
              taskspn_ajax_type: 'taskspn_calendar_view',
              taskspn_ajax_nonce: taskspn_ajax.taskspn_ajax_nonce,
              calendar_view: view,
              calendar_year: year,
              calendar_month: month,
              calendar_day: day
            },
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
                    $(this).html(response_json.html).fadeIn(300);
                  });
                }
              } catch (e) {
                console.error('Error parsing calendar response:', e);
                hideLoader();
              }
            },
            error: function(xhr, status, error) {
              console.error('AJAX error loading calendar view:', error);
              hideLoader();
              // Fallback to page reload
              var url = new URL(window.location.href);
              url.searchParams.set('calendar_view', view);
              url.searchParams.set('calendar_year', year);
              url.searchParams.set('calendar_month', month);
              url.searchParams.set('calendar_day', day);
              window.location.href = url.toString();
            }
          });
        } else {
          // Fallback to page reload if AJAX not available
          hideLoader();
          var url = new URL(window.location.href);
          url.searchParams.set('calendar_view', view);
          url.searchParams.set('calendar_year', year);
          url.searchParams.set('calendar_month', month);
          url.searchParams.set('calendar_day', day);
          window.location.href = url.toString();
        }
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
        if (typeof TASKSPN_Popups !== 'undefined' && typeof taskspn_ajax !== 'undefined') {
          var popupElement = $('#taskspn-popup-taskspn_task-view');
          
          if (popupElement.length) {
            // Make AJAX call to get task view content
            var data = {
              action: 'taskspn_ajax',
              taskspn_ajax_type: 'taskspn_task_view',
              taskspn_ajax_nonce: taskspn_ajax.taskspn_ajax_nonce,
              taskspn_task_id: taskId
            };

            // Show loading state
            var loadingText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.loading) ? taskspn_i18n.loading : 'Loading...';
            popupElement.find('.taskspn-popup-content').html('<div class="taskspn-text-align-center taskspn-p-30">' + loadingText + '</div>');
            
            TASKSPN_Popups.open(popupElement, {
              beforeShow: function() {
                $.ajax({
                  url: taskspn_ajax.ajax_url,
                  type: 'POST',
                  data: data,
                  dataType: 'json',
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
                    console.error('AJAX error loading task view:', error);
                    var errorText = (typeof taskspn_i18n !== 'undefined' && taskspn_i18n.an_error_has_occurred) ? taskspn_i18n.an_error_has_occurred : 'Error loading task';
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

