(function($) {
    'use strict';
  
    window.TASKSPN_Popups = {
      open: function(popup, options = {}) {
        var popupElement = typeof popup === 'string' ? $('#' + popup) : popup;
        
        if (!popupElement.length) {
          return;
        }
  
        if (typeof options.beforeShow === 'function') {
          options.beforeShow();
        }
  
        // Show overlay - Remove any inline styles and add active class
        $('.taskspn-popup-overlay').removeClass('taskspn-display-none-soft').addClass('taskspn-popup-overlay-active').css('display', '');
  
        // Show popup - Remove any inline styles and add active class
        popupElement.removeClass('taskspn-display-none-soft').addClass('taskspn-popup-active').css('display', '');
  
        // Add close button if not present
        if (!popupElement.find('.taskspn-popup-close').length) {
          var closeButton = $('<button class="taskspn-popup-close-wrapper"><i class="material-icons-outlined">close</i></button>');
          closeButton.on('click', function() {
            TASKSPN_Popups.close();
          });
          popupElement.find('.taskspn-popup-content').append(closeButton);
        }
  
        // Store and call callbacks if provided
        if (options.beforeShow) {
          popupElement.data('beforeShow', options.beforeShow);
        }
        if (options.afterClose) {
          popupElement.data('afterClose', options.afterClose);
        }
      },
  
      close: function() {
        // Hide all popups - Remove classes and set inline display:none
        $('.taskspn-popup').each(function() {
          $(this).removeClass('taskspn-popup-active').addClass('taskspn-display-none-soft').css('display', 'none');
        });
  
        // Hide overlay - Remove classes and set inline display:none
        $('.taskspn-popup-overlay').removeClass('taskspn-popup-overlay-active').addClass('taskspn-display-none-soft').css('display', 'none');
  
        // Call afterClose callback if exists
        $('.taskspn-popup').each(function() {
          const afterClose = $(this).data('afterClose');
          if (typeof afterClose === 'function') {
            afterClose();
            $(this).removeData('afterClose');
          }
        });

        document.body.classList.remove('taskspn-popup-open');
      }
    };
  
    // Initialize popup functionality
    $(document).ready(function() {
      // Close popup when clicking overlay
      $(document).on('click', '.taskspn-popup-overlay', function(e) {
        // Only close if the click was directly on the overlay
        if (e.target === this) {
          TASKSPN_Popups.close();
        }
      });
  
      // Prevent clicks inside popup from bubbling up to the overlay
      $(document).on('click', '.taskspn-popup', function(e) {
        e.stopPropagation();
      });
  
      // Close popup when pressing ESC key
      $(document).on('keyup', function(e) {
        if (e.keyCode === 27) { // ESC key
          TASKSPN_Popups.close();
        }
      });
  
      // Close popup when clicking close button
      $(document).on('click', '.taskspn-popup-close, .taskspn-popup-close-wrapper', function(e) {
        e.preventDefault();
        TASKSPN_Popups.close();
      });
    });
  })(jQuery); 