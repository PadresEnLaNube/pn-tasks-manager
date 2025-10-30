(function($) {
  'use strict';

  $(document).ready(function() {
    if ($('.taskspn-password-checker').length) {
      var pass_view_state = false;

      function taskspn_pass_check_strength(pass) {
        var strength = 0;
        var password = $('.taskspn-password-strength');
        var low_upper_case = password.closest('.taskspn-password-checker').find('.low-upper-case i');
        var number = password.closest('.taskspn-password-checker').find('.one-number i');
        var special_char = password.closest('.taskspn-password-checker').find('.one-special-char i');
        var eight_chars = password.closest('.taskspn-password-checker').find('.eight-character i');

        //If pass contains both lower and uppercase characters
        if (pass.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
          strength += 1;
          low_upper_case.text('task_alt');
        } else {
          low_upper_case.text('radio_button_unchecked');
        }

        //If it has numbers and characters
        if (pass.match(/([0-9])/)) {
          strength += 1;
          number.text('task_alt');
        } else {
          number.text('radio_button_unchecked');
        }

        //If it has one special character
        if (pass.match(/([!,%,&,@,#,$,^,*,?,_,~,|,¬,+,ç,-,€])/)) {
          strength += 1;
          special_char.text('task_alt');
        } else {
          special_char.text('radio_button_unchecked');
        }

        //If pass is greater than 7
        if (pass.length > 7) {
          strength += 1;
          eight_chars.text('task_alt');
        } else {
          eight_chars.text('radio_button_unchecked');
        }

        // If value is less than 2
        if (strength < 2) {
          $('.taskspn-password-strength-bar').removeClass('taskspn-progress-bar-warning taskspn-progress-bar-success').addClass('taskspn-progress-bar-danger').css('width', '10%');
        } else if (strength == 3) {
          $('.taskspn-password-strength-bar').removeClass('taskspn-progress-bar-success taskspn-progress-bar-danger').addClass('taskspn-progress-bar-warning').css('width', '60%');
        } else if (strength == 4) {
          $('.taskspn-password-strength-bar').removeClass('taskspn-progress-bar-warning taskspn-progress-bar-danger').addClass('taskspn-progress-bar-success').css('width', '100%');
        }
      }

      $(document).on('click', '.taskspn-show-pass', function(e){
        e.preventDefault();
        var taskspn_btn = $(this);
        var password_input = taskspn_btn.siblings('.taskspn-password-strength');

        if (pass_view_state) {
          password_input.attr('type', 'password');
          taskspn_btn.find('i').text('visibility');
          pass_view_state = false;
        } else {
          password_input.attr('type', 'text');
          taskspn_btn.find('i').text('visibility_off');
          pass_view_state = true;
        }
      });

      $(document).on('keyup', ('.taskspn-password-strength'), function(e){
        taskspn_pass_check_strength($('.taskspn-password-strength').val());

        if (!$('#taskspn-popover-pass').is(':visible')) {
          $('#taskspn-popover-pass').fadeIn('slow');
        }

        if (!$('.taskspn-show-pass').is(':visible')) {
          $('.taskspn-show-pass').fadeIn('slow');
        }
      });
    }
    
    $(document).on('mouseover', '.taskspn-input-star', function(e){
      if (!$(this).closest('.taskspn-input-stars').hasClass('clicked')) {
        $(this).text('star');
        $(this).prevAll('.taskspn-input-star').text('star');
      }
    });

    $(document).on('mouseout', '.taskspn-input-stars', function(e){
      if (!$(this).hasClass('clicked')) {
        $(this).find('.taskspn-input-star').text('star_outlined');
      }
    });

    $(document).on('click', '.taskspn-input-star', function(e){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      $(this).closest('.taskspn-input-stars').addClass('clicked');
      $(this).closest('.taskspn-input-stars').find('.taskspn-input-star').text('star_outlined');
      $(this).text('star');
      $(this).prevAll('.taskspn-input-star').text('star');
      $(this).closest('.taskspn-input-stars').siblings('.taskspn-input-hidden-stars').val($(this).prevAll('.taskspn-input-star').length + 1);
    });

    $(document).on('change', '.taskspn-input-hidden-stars', function(e){
      $(this).siblings('.taskspn-input-stars').find('.taskspn-input-star').text('star_outlined');
      $(this).siblings('.taskspn-input-stars').find('.taskspn-input-star').slice(0, $(this).val()).text('star');
    });

    if ($('.taskspn-field[data-taskspn-parent]').length) {
      taskspn_form_update();

      $(document).on('change', '.taskspn-field[data-taskspn-parent~="this"]', function(e) {
        taskspn_form_update();
      });
    }

    if ($('.taskspn-html-multi-group').length) {
      $(document).on('click', '.taskspn-html-multi-remove-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        var taskspn_users_btn = $(this);

        if (taskspn_users_btn.closest('.taskspn-html-multi-wrapper').find('.taskspn-html-multi-group').length > 1) {
          $(this).closest('.taskspn-html-multi-group').remove();
        } else {
          $(this).closest('.taskspn-html-multi-group').find('input, select, textarea').val('');
        }
      });

      $(document).on('click', '.taskspn-html-multi-add-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        $(this).closest('.taskspn-html-multi-wrapper').find('.taskspn-html-multi-group:first').clone().insertAfter($(this).closest('.taskspn-html-multi-wrapper').find('.taskspn-html-multi-group:last'));
        $(this).closest('.taskspn-html-multi-wrapper').find('.taskspn-html-multi-group:last').find('input, select, textarea').val('');

        $(this).closest('.taskspn-html-multi-wrapper').find('.taskspn-input-range').each(function(index, element) {
          $(this).siblings('.taskspn-input-range-output').html($(this).val());
        });
      });

      $('.taskspn-html-multi-wrapper').sortable({handle: '.taskspn-multi-sorting'});

      $(document).on('sortstop', '.taskspn-html-multi-wrapper', function(event, ui){
        taskspn_get_main_message(taskspn_i18n.ordered_element);
      });
    }

    if ($('.taskspn-input-range').length) {
      $('.taskspn-input-range').each(function(index, element) {
        $(this).siblings('.taskspn-input-range-output').html($(this).val());
      });

      $(document).on('input', '.taskspn-input-range', function(e) {
        $(this).siblings('.taskspn-input-range-output').html($(this).val());
      });
    }

    if ($('.taskspn-image-btn').length) {
      var image_frame;

      $(document).on('click', '.taskspn-image-btn', function(e){
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        if (image_frame){
          image_frame.open();
          return;
        }

        var taskspn_input_btn = $(this);
        var taskspn_images_block = taskspn_input_btn.closest('.taskspn-images-block').find('.taskspn-images');
        var taskspn_images_input = taskspn_input_btn.closest('.taskspn-images-block').find('.taskspn-image-input');

        var image_frame = wp.media({
          title: (taskspn_images_block.attr('data-taskspn-multiple') == 'true') ? taskspn_i18n.select_images : taskspn_i18n.select_image,
          library: {
            type: 'image'
          },
          multiple: (taskspn_images_block.attr('data-taskspn-multiple') == 'true') ? 'true' : 'false',
        });

        image_frame.states.add([
          new wp.media.controller.Library({
            id: 'post-gallery',
            title: (taskspn_images_block.attr('data-taskspn-multiple') == 'true') ? taskspn_i18n.edit_images : taskspn_i18n.edit_image,
            priority: 20,
            toolbar: 'main-gallery',
            filterable: 'uploaded',
            library: wp.media.query(image_frame.options.library),
            multiple: (taskspn_images_block.attr('data-taskspn-multiple') == 'true') ? 'true' : 'false',
            editable: true,
            allowLocalEdits: true,
            displaySettings: true,
            displayUserSettings: true
          })
        ]);

        image_frame.open();

        image_frame.on('select', function() {
          var ids = [];
          var attachments_arr = [];

          attachments_arr = image_frame.state().get('selection').toJSON();
          taskspn_images_block.html('');

          $(attachments_arr).each(function(e){
            var sep = (e != (attachments_arr.length - 1))  ? ',' : '';
            ids += $(this)[0].id + sep;
            taskspn_images_block.append('<img src="' + $(this)[0].url + '" class="">');
          });

          taskspn_input_btn.text((taskspn_images_block.attr('data-taskspn-multiple') == 'true') ? taskspn_i18n.select_images : taskspn_i18n.select_image);
          taskspn_images_input.val(ids);
        });
      });
    }

    if ($('.taskspn-audio-btn').length) {
      var audio_frame;

      $(document).on('click', '.taskspn-audio-btn', function(e){
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        if (audio_frame){
          audio_frame.open();
          return;
        }

        var taskspn_input_btn = $(this);
        var taskspn_audios_block = taskspn_input_btn.closest('.taskspn-audios-block').find('.taskspn-audios');
        var taskspn_audios_input = taskspn_input_btn.closest('.taskspn-audios-block').find('.taskspn-audio-input');

        var audio_frame = wp.media({
          title: (taskspn_audios_block.attr('data-taskspn-multiple') == 'true') ? taskspn_i18n.select_audios : taskspn_i18n.select_audio,
          library : {
            type : 'audio'
          },
          multiple: (taskspn_audios_block.attr('data-taskspn-multiple') == 'true') ? 'true' : 'false',
        });

        audio_frame.states.add([
          new wp.media.controller.Library({
            id: 'post-gallery',
            title: (taskspn_audios_block.attr('data-taskspn-multiple') == 'true') ? taskspn_i18n.select_audios : taskspn_i18n.select_audio,
            priority: 20,
            toolbar: 'main-gallery',
            filterable: 'uploaded',
            library: wp.media.query(audio_frame.options.library),
            multiple: (taskspn_audios_block.attr('data-taskspn-multiple') == 'true') ? 'true' : 'false',
            editable: true,
            allowLocalEdits: true,
            displaySettings: true,
            displayUserSettings: true
          })
        ]);

        audio_frame.open();

        audio_frame.on('select', function() {
          var ids = [];
          var attachments_arr = [];

          attachments_arr = audio_frame.state().get('selection').toJSON();
          taskspn_audios_block.html('');

          $(attachments_arr).each(function(e){
            var sep = (e != (attachments_arr.length - 1))  ? ',' : '';
            ids += $(this)[0].id + sep;
            taskspn_audios_block.append('<div class="taskspn-audio taskspn-tooltip" title="' + $(this)[0].title + '"><i class="dashicons dashicons-media-audio"></i></div>');
          });

          $('.taskspn-tooltip').tooltipster({maxWidth: 300,delayTouch:[0, 4000], customClass: 'taskspn-tooltip'});
          taskspn_input_btn.text((taskspn_audios_block.attr('data-taskspn-multiple') == 'true') ? taskspn_i18n.select_audios : taskspn_i18n.select_audio);
          taskspn_audios_input.val(ids);
        });
      });
    }

    if ($('.taskspn-video-btn').length) {
      var video_frame;

      $(document).on('click', '.taskspn-video-btn', function(e){
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        if (video_frame){
          video_frame.open();
          return;
        }

        var taskspn_input_btn = $(this);
        var taskspn_videos_block = taskspn_input_btn.closest('.taskspn-videos-block').find('.taskspn-videos');
        var taskspn_videos_input = taskspn_input_btn.closest('.taskspn-videos-block').find('.taskspn-video-input');

        var video_frame = wp.media({
          title: (taskspn_videos_block.attr('data-taskspn-multiple') == 'true') ? taskspn_i18n.select_videos : taskspn_i18n.select_video,
          library : {
            type : 'video'
          },
          multiple: (taskspn_videos_block.attr('data-taskspn-multiple') == 'true') ? 'true' : 'false',
        });

        video_frame.states.add([
          new wp.media.controller.Library({
            id: 'post-gallery',
            title: (taskspn_videos_block.attr('data-taskspn-multiple') == 'true') ? taskspn_i18n.select_videos : taskspn_i18n.select_video,
            priority: 20,
            toolbar: 'main-gallery',
            filterable: 'uploaded',
            library: wp.media.query(video_frame.options.library),
            multiple: (taskspn_videos_block.attr('data-taskspn-multiple') == 'true') ? 'true' : 'false',
            editable: true,
            allowLocalEdits: true,
            displaySettings: true,
            displayUserSettings: true
          })
        ]);

        video_frame.open();

        video_frame.on('select', function() {
          var ids = [];
          var attachments_arr = [];

          attachments_arr = video_frame.state().get('selection').toJSON();
          taskspn_videos_block.html('');

          $(attachments_arr).each(function(e){
            var sep = (e != (attachments_arr.length - 1))  ? ',' : '';
            ids += $(this)[0].id + sep;
            taskspn_videos_block.append('<div class="taskspn-video taskspn-tooltip" title="' + $(this)[0].title + '"><i class="dashicons dashicons-media-video"></i></div>');
          });

          $('.taskspn-tooltip').tooltipster({maxWidth: 300,delayTouch:[0, 4000], customClass: 'taskspn-tooltip'});
          taskspn_input_btn.text((taskspn_videos_block.attr('data-taskspn-multiple') == 'true') ? taskspn_i18n.select_videos : taskspn_i18n.select_video);
          taskspn_videos_input.val(ids);
        });
      });
    }

    if ($('.taskspn-file-btn').length) {
      var file_frame;

      $(document).on('click', '.taskspn-file-btn', function(e){
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        if (file_frame){
          file_frame.open();
          return;
        }

        var taskspn_input_btn = $(this);
        var taskspn_files_block = taskspn_input_btn.closest('.taskspn-files-block').find('.taskspn-files');
        var taskspn_files_input = taskspn_input_btn.closest('.taskspn-files-block').find('.taskspn-file-input');

        var file_frame = wp.media({
          title: (taskspn_files_block.attr('data-taskspn-multiple') == 'true') ? taskspn_i18n.select_files : taskspn_i18n.select_file,
          multiple: (taskspn_files_block.attr('data-taskspn-multiple') == 'true') ? 'true' : 'false',
        });

        file_frame.states.add([
          new wp.media.controller.Library({
            id: 'post-gallery',
            title: (taskspn_files_block.attr('data-taskspn-multiple') == 'true') ? taskspn_i18n.select_files : taskspn_i18n.select_file,
            priority: 20,
            toolbar: 'main-gallery',
            filterable: 'uploaded',
            library: wp.media.query(file_frame.options.library),
            multiple: (taskspn_files_block.attr('data-taskspn-multiple') == 'true') ? 'true' : 'false',
            editable: true,
            allowLocalEdits: true,
            displaySettings: true,
            displayUserSettings: true
          })
        ]);

        file_frame.open();

        file_frame.on('select', function() {
          var ids = [];
          var attachments_arr = [];

          attachments_arr = file_frame.state().get('selection').toJSON();
          taskspn_files_block.html('');

          $(attachments_arr).each(function(e){
            var sep = (e != (attachments_arr.length - 1))  ? ',' : '';
            ids += $(this)[0].id + sep;
            taskspn_files_block.append('<embed src="' + $(this)[0].url + '" type="application/pdf" class="taskspn-embed-file"/>');
          });

          taskspn_input_btn.text((taskspn_files_block.attr('data-taskspn-multiple') == 'true') ? taskspn_i18n.edit_files : taskspn_i18n.edit_file);
          taskspn_files_input.val(ids);
        });
      });
    }

    // CPT SEARCH FUNCTIONALITY
    if (typeof taskspn_cpts !== 'undefined') {
      // Initialize search functionality for each CPT
      Object.keys(taskspn_cpts).forEach(function(cptKey) {
        var cptName = taskspn_cpts[cptKey];
        var searchToggleSelector = '.taskspn-' + cptKey + '-search-toggle';
        var searchInputSelector = '.taskspn-' + cptKey + '-search-input';
        var searchWrapperSelector = '.taskspn-' + cptKey + '-search-wrapper';
        var listSelector = '.taskspn-' + cptKey + '-list';
        var listWrapperSelector = '.taskspn-' + cptKey + '-list-wrapper';
        var addNewSelector = '.taskspn-add-new-cpt';

        // Only initialize if elements exist
        if ($(searchToggleSelector).length) {
          
          // Toggle search input visibility
          $(document).on('click', searchToggleSelector, function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            var searchToggle = $(this);
            var searchInput = searchToggle.siblings(searchInputSelector);
            var searchWrapper = searchToggle.closest(searchWrapperSelector);
            var list = searchToggle.closest(listSelector);
            var listWrapper = list.find(listWrapperSelector);
            var itemsList = listWrapper.find('ul');

            if (searchInput.hasClass('taskspn-display-none')) {
              // Show search input
              searchInput.removeClass('taskspn-display-none').focus();
              searchToggle.text('close');
              searchWrapper.addClass('taskspn-search-active');
            } else {
              // Hide search input and clear filter
              searchInput.addClass('taskspn-display-none').val('');
              searchToggle.text('search');
              searchWrapper.removeClass('taskspn-search-active');
              
              // Show all items
              itemsList.find('li').show();
            }
          });

          // Filter items on keyup
          $(document).on('keyup', searchInputSelector, function(e) {
            var searchInput = $(this);
            var searchTerm = searchInput.val().toLowerCase().trim();
            var list = searchInput.closest(listSelector);
            var listWrapper = list.find(listWrapperSelector);
            var itemsList = listWrapper.find('ul');
            var items = itemsList.find('li:not(' + addNewSelector + ')');

            if (searchTerm === '') {
              // Show all items when search is empty
              items.show();
            } else {
              // Filter items based on title
              items.each(function() {
                var itemTitle = $(this).find('.taskspn-display-inline-table a span').first().text().toLowerCase();
                if (itemTitle.includes(searchTerm)) {
                  $(this).show();
                } else {
                  $(this).hide();
                }
              });
            }

            // Always show the "Add new" item
            itemsList.find(addNewSelector).show();
          });

          // Close search on escape key
          $(document).on('keydown', searchInputSelector, function(e) {
            if (e.keyCode === 27) { // Escape key
              var searchInput = $(this);
              var searchToggle = searchInput.siblings(searchToggleSelector);
              var searchWrapper = searchInput.closest(searchWrapperSelector);
              var list = searchInput.closest(listSelector);
              var listWrapper = list.find(listWrapperSelector);
              var itemsList = listWrapper.find('ul');

              searchInput.addClass('taskspn-display-none').val('');
              searchToggle.text('search');
              searchWrapper.removeClass('taskspn-search-active');
              
              // Show all items
              itemsList.find('li').show();
            }
          });
                }
      });

      // Single unified click outside handler for all search wrappers
      $(document).on('click', function(e) {
        var clickedInsideSearch = false;
        var activeSearchInput = null;
        var activeSearchToggle = null;
        var activeSearchWrapper = null;
        var activeList = null;
        var activeListWrapper = null;
        var activeItemsList = null;

        // Check if clicked inside any search wrapper
        Object.keys(taskspn_cpts).forEach(function(cptKey) {
          var searchWrapperSelector = '.taskspn-' + cptKey + '-search-wrapper';
          var searchInputSelector = '.taskspn-' + cptKey + '-search-input';
          var searchToggleSelector = '.taskspn-' + cptKey + '-search-toggle';
          var listSelector = '.taskspn-taskspn_' + cptKey + '-list';
          var listWrapperSelector = '.taskspn-taskspn_' + cptKey + '-list-wrapper';

          if ($(e.target).closest(searchWrapperSelector).length) {
            clickedInsideSearch = true;
          }

          // Find active search input
          var searchInput = $(searchInputSelector + ':not(.taskspn-display-none)');
          if (searchInput.length && !activeSearchInput) {
            activeSearchInput = searchInput;
            activeSearchToggle = searchInput.siblings(searchToggleSelector);
            activeSearchWrapper = searchInput.closest(searchWrapperSelector);
            activeList = searchInput.closest(listSelector);
            activeListWrapper = activeList.find(listWrapperSelector);
            activeItemsList = activeListWrapper.find('ul');
          }
        });

        // Close search if clicked outside
        if (!clickedInsideSearch && activeSearchInput) {
          activeSearchInput.addClass('taskspn-display-none').val('');
          activeSearchToggle.text('search');
          activeSearchWrapper.removeClass('taskspn-search-active');
          
          // Show all items
          activeItemsList.find('li').show();
        }
      });
    }
  });

  $(document).on('click', '.taskspn-toggle', function(e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    var taskspn_toggle = $(this);

    if (taskspn_toggle.find('i').length) {
      if (taskspn_toggle.siblings('.taskspn-toggle-content').is(':visible')) {
        taskspn_toggle.find('i').text('add');
      } else {
        taskspn_toggle.find('i').text('clear');
      }
    }

    taskspn_toggle.siblings('.taskspn-toggle-content').fadeToggle();
  });
})(jQuery);
