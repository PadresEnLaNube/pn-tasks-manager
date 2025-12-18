(function($) {
    'use strict';
  
    window.PN_TASKS_MANAGER_Popups = {
      open: function(popup, options = {}) {
        var popupElement = typeof popup === 'string' ? $('#' + popup) : popup;
        
        if (!popupElement.length) {
          return;
        }
  
        if (typeof options.beforeShow === 'function') {
          options.beforeShow();
        }
  
        // Show overlay - Remove any inline styles and add active class
        $('.pn-tasks-manager-popup-overlay').removeClass('pn-tasks-manager-display-none-soft').addClass('pn-tasks-manager-popup-overlay-active').css('display', '');
  
        // Show popup - Remove any inline styles and add active class
        popupElement.removeClass('pn-tasks-manager-display-none-soft').addClass('pn-tasks-manager-popup-active').css('display', '');
  
        // Add close button if not present
        if (!popupElement.find('.pn-tasks-manager-popup-close').length) {
          var closeButton = $('<button class="pn-tasks-manager-popup-close-wrapper"><i class="material-icons-outlined">close</i></button>');
          closeButton.on('click', function() {
            PN_TASKS_MANAGER_Popups.close();
          });
          popupElement.find('.pn-tasks-manager-popup-content').append(closeButton);
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
        $('.pn-tasks-manager-popup').each(function() {
          $(this).removeClass('pn-tasks-manager-popup-active').addClass('pn-tasks-manager-display-none-soft').css('display', 'none');
        });
  
        // Hide overlay - Remove classes and set inline display:none
        $('.pn-tasks-manager-popup-overlay').removeClass('pn-tasks-manager-popup-overlay-active').addClass('pn-tasks-manager-display-none-soft').css('display', 'none');
  
        // Call afterClose callback if exists
        $('.pn-tasks-manager-popup').each(function() {
          const afterClose = $(this).data('afterClose');
          if (typeof afterClose === 'function') {
            afterClose();
            $(this).removeData('afterClose');
          }
        });

        document.body.classList.remove('pn-tasks-manager-popup-open');
      }
    };
  
    // Initialize popup functionality
    $(document).ready(function() {
      // Close popup when clicking overlay
      $(document).on('click', '.pn-tasks-manager-popup-overlay', function(e) {
        // Only close if the click was directly on the overlay
        if (e.target === this) {
          PN_TASKS_MANAGER_Popups.close();
        }
      });
  
      // Prevent clicks inside popup from bubbling up to the overlay
      $(document).on('click', '.pn-tasks-manager-popup', function(e) {
        e.stopPropagation();
      });
  
      // Close popup when pressing ESC key
      $(document).on('keyup', function(e) {
        if (e.keyCode === 27) { // ESC key
          PN_TASKS_MANAGER_Popups.close();
        }
      });
  
      // Close popup when clicking close button
      $(document).on('click', '.pn-tasks-manager-popup-close, .pn-tasks-manager-popup-close-wrapper', function(e) {
        e.preventDefault();
        PN_TASKS_MANAGER_Popups.close();
      });
    });
  })(jQuery); 