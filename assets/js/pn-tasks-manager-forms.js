(function($) {
  'use strict';

  $(document).ready(function() {
    if ($('.pn-tasks-manager-password-checker').length) {
      var pass_view_state = false;

      function pn_tasks_manager_pass_check_strength(pass) {
        var strength = 0;
        var password = $('.pn-tasks-manager-password-strength');
        var low_upper_case = password.closest('.pn-tasks-manager-password-checker').find('.low-upper-case i');
        var number = password.closest('.pn-tasks-manager-password-checker').find('.one-number i');
        var special_char = password.closest('.pn-tasks-manager-password-checker').find('.one-special-char i');
        var eight_chars = password.closest('.pn-tasks-manager-password-checker').find('.eight-character i');

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
          $('.pn-tasks-manager-password-strength-bar').removeClass('pn-tasks-manager-progress-bar-warning pn-tasks-manager-progress-bar-success').addClass('pn-tasks-manager-progress-bar-danger').css('width', '10%');
        } else if (strength == 3) {
          $('.pn-tasks-manager-password-strength-bar').removeClass('pn-tasks-manager-progress-bar-success pn-tasks-manager-progress-bar-danger').addClass('pn-tasks-manager-progress-bar-warning').css('width', '60%');
        } else if (strength == 4) {
          $('.pn-tasks-manager-password-strength-bar').removeClass('pn-tasks-manager-progress-bar-warning pn-tasks-manager-progress-bar-danger').addClass('pn-tasks-manager-progress-bar-success').css('width', '100%');
        }
      }

      $(document).on('click', '.pn-tasks-manager-show-pass', function(e){
        e.preventDefault();
        var pn_tasks_manager_btn = $(this);
        var password_input = pn_tasks_manager_btn.siblings('.pn-tasks-manager-password-strength');

        if (pass_view_state) {
          password_input.attr('type', 'password');
          pn_tasks_manager_btn.find('i').text('visibility');
          pass_view_state = false;
        } else {
          password_input.attr('type', 'text');
          pn_tasks_manager_btn.find('i').text('visibility_off');
          pass_view_state = true;
        }
      });

      $(document).on('keyup', ('.pn-tasks-manager-password-strength'), function(e){
        pn_tasks_manager_pass_check_strength($('.pn-tasks-manager-password-strength').val());

        if (!$('#pn-tasks-manager-popover-pass').is(':visible')) {
          $('#pn-tasks-manager-popover-pass').fadeIn('slow');
        }

        if (!$('.pn-tasks-manager-show-pass').is(':visible')) {
          $('.pn-tasks-manager-show-pass').fadeIn('slow');
        }
      });
    }
    
    $(document).on('mouseover', '.pn-tasks-manager-input-star', function(e){
      if (!$(this).closest('.pn-tasks-manager-input-stars').hasClass('clicked')) {
        $(this).text('star');
        $(this).prevAll('.pn-tasks-manager-input-star').text('star');
      }
    });

    $(document).on('mouseout', '.pn-tasks-manager-input-stars', function(e){
      if (!$(this).hasClass('clicked')) {
        $(this).find('.pn-tasks-manager-input-star').text('star_outlined');
      }
    });

    $(document).on('click', '.pn-tasks-manager-input-star', function(e){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      $(this).closest('.pn-tasks-manager-input-stars').addClass('clicked');
      $(this).closest('.pn-tasks-manager-input-stars').find('.pn-tasks-manager-input-star').text('star_outlined');
      $(this).text('star');
      $(this).prevAll('.pn-tasks-manager-input-star').text('star');
      $(this).closest('.pn-tasks-manager-input-stars').siblings('.pn-tasks-manager-input-hidden-stars').val($(this).prevAll('.pn-tasks-manager-input-star').length + 1);
    });

    $(document).on('change', '.pn-tasks-manager-input-hidden-stars', function(e){
      $(this).siblings('.pn-tasks-manager-input-stars').find('.pn-tasks-manager-input-star').text('star_outlined');
      $(this).siblings('.pn-tasks-manager-input-stars').find('.pn-tasks-manager-input-star').slice(0, $(this).val()).text('star');
    });

    if ($('.pn-tasks-manager-field[data-pn-tasks-manager-parent]').length) {
      pn_tasks_manager_form_update();

      $(document).on('change', '.pn-tasks-manager-field[data-pn-tasks-manager-parent~="this"]', function(e) {
        pn_tasks_manager_form_update();
      });
    }

    if ($('.pn-tasks-manager-html-multi-group').length) {
      $(document).on('click', '.pn-tasks-manager-html-multi-remove-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        var pn_tasks_manager_users_btn = $(this);

        if (pn_tasks_manager_users_btn.closest('.pn-tasks-manager-html-multi-wrapper').find('.pn-tasks-manager-html-multi-group').length > 1) {
          $(this).closest('.pn-tasks-manager-html-multi-group').remove();
        } else {
          $(this).closest('.pn-tasks-manager-html-multi-group').find('input, select, textarea').val('');
        }
      });

      $(document).on('click', '.pn-tasks-manager-html-multi-add-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        $(this).closest('.pn-tasks-manager-html-multi-wrapper').find('.pn-tasks-manager-html-multi-group:first').clone().insertAfter($(this).closest('.pn-tasks-manager-html-multi-wrapper').find('.pn-tasks-manager-html-multi-group:last'));
        $(this).closest('.pn-tasks-manager-html-multi-wrapper').find('.pn-tasks-manager-html-multi-group:last').find('input, select, textarea').val('');

        $(this).closest('.pn-tasks-manager-html-multi-wrapper').find('.pn-tasks-manager-input-range').each(function(index, element) {
          $(this).siblings('.pn-tasks-manager-input-range-output').html($(this).val());
        });
      });

      $('.pn-tasks-manager-html-multi-wrapper').sortable({handle: '.pn-tasks-manager-multi-sorting'});

      $(document).on('sortstop', '.pn-tasks-manager-html-multi-wrapper', function(event, ui){
        pn_tasks_manager_get_main_message(pn_tasks_manager_i18n.ordered_element);
      });
    }

    if ($('.pn-tasks-manager-input-range').length) {
      $('.pn-tasks-manager-input-range').each(function(index, element) {
        $(this).siblings('.pn-tasks-manager-input-range-output').html($(this).val());
      });

      $(document).on('input', '.pn-tasks-manager-input-range', function(e) {
        $(this).siblings('.pn-tasks-manager-input-range-output').html($(this).val());
      });
    }

    if ($('.pn-tasks-manager-image-btn').length) {
      var image_frame;

      $(document).on('click', '.pn-tasks-manager-image-btn', function(e){
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        if (image_frame){
          image_frame.open();
          return;
        }

        var pn_tasks_manager_input_btn = $(this);
        var pn_tasks_manager_images_block = pn_tasks_manager_input_btn.closest('.pn-tasks-manager-images-block').find('.pn-tasks-manager-images');
        var pn_tasks_manager_images_input = pn_tasks_manager_input_btn.closest('.pn-tasks-manager-images-block').find('.pn-tasks-manager-image-input');

        var image_frame = wp.media({
          title: (pn_tasks_manager_images_block.attr('data-pn-tasks-manager-multiple') == 'true') ? pn_tasks_manager_i18n.select_images : pn_tasks_manager_i18n.select_image,
          library: {
            type: 'image'
          },
          multiple: (pn_tasks_manager_images_block.attr('data-pn-tasks-manager-multiple') == 'true') ? 'true' : 'false',
        });

        image_frame.states.add([
          new wp.media.controller.Library({
            id: 'post-gallery',
            title: (pn_tasks_manager_images_block.attr('data-pn-tasks-manager-multiple') == 'true') ? pn_tasks_manager_i18n.edit_images : pn_tasks_manager_i18n.edit_image,
            priority: 20,
            toolbar: 'main-gallery',
            filterable: 'uploaded',
            library: wp.media.query(image_frame.options.library),
            multiple: (pn_tasks_manager_images_block.attr('data-pn-tasks-manager-multiple') == 'true') ? 'true' : 'false',
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
          pn_tasks_manager_images_block.html('');

          $(attachments_arr).each(function(e){
            var sep = (e != (attachments_arr.length - 1))  ? ',' : '';
            ids += $(this)[0].id + sep;
            pn_tasks_manager_images_block.append('<img src="' + $(this)[0].url + '" class="">');
          });

          pn_tasks_manager_input_btn.text((pn_tasks_manager_images_block.attr('data-pn-tasks-manager-multiple') == 'true') ? pn_tasks_manager_i18n.select_images : pn_tasks_manager_i18n.select_image);
          pn_tasks_manager_images_input.val(ids);
        });
      });
    }

    if ($('.pn-tasks-manager-audio-btn').length) {
      var audio_frame;

      $(document).on('click', '.pn-tasks-manager-audio-btn', function(e){
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        if (audio_frame){
          audio_frame.open();
          return;
        }

        var pn_tasks_manager_input_btn = $(this);
        var pn_tasks_manager_audios_block = pn_tasks_manager_input_btn.closest('.pn-tasks-manager-audios-block').find('.pn-tasks-manager-audios');
        var pn_tasks_manager_audios_input = pn_tasks_manager_input_btn.closest('.pn-tasks-manager-audios-block').find('.pn-tasks-manager-audio-input');

        var audio_frame = wp.media({
          title: (pn_tasks_manager_audios_block.attr('data-pn-tasks-manager-multiple') == 'true') ? pn_tasks_manager_i18n.select_audios : pn_tasks_manager_i18n.select_audio,
          library : {
            type : 'audio'
          },
          multiple: (pn_tasks_manager_audios_block.attr('data-pn-tasks-manager-multiple') == 'true') ? 'true' : 'false',
        });

        audio_frame.states.add([
          new wp.media.controller.Library({
            id: 'post-gallery',
            title: (pn_tasks_manager_audios_block.attr('data-pn-tasks-manager-multiple') == 'true') ? pn_tasks_manager_i18n.select_audios : pn_tasks_manager_i18n.select_audio,
            priority: 20,
            toolbar: 'main-gallery',
            filterable: 'uploaded',
            library: wp.media.query(audio_frame.options.library),
            multiple: (pn_tasks_manager_audios_block.attr('data-pn-tasks-manager-multiple') == 'true') ? 'true' : 'false',
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
          pn_tasks_manager_audios_block.html('');

          $(attachments_arr).each(function(e){
            var sep = (e != (attachments_arr.length - 1))  ? ',' : '';
            ids += $(this)[0].id + sep;
            pn_tasks_manager_audios_block.append('<div class="pn-tasks-manager-audio pn-tasks-manager-tooltip" title="' + $(this)[0].title + '"><i class="dashicons dashicons-media-audio"></i></div>');
          });

          // Only initialize tooltips on newly added elements
          pn_tasks_manager_audios_block.find('.pn-tasks-manager-tooltip').not('.tooltipstered').tooltipster({maxWidth: 300,delayTouch:[0, 4000], customClass: 'pn-tasks-manager-tooltip'});
          pn_tasks_manager_input_btn.text((pn_tasks_manager_audios_block.attr('data-pn-tasks-manager-multiple') == 'true') ? pn_tasks_manager_i18n.select_audios : pn_tasks_manager_i18n.select_audio);
          pn_tasks_manager_audios_input.val(ids);
        });
      });
    }

    if ($('.pn-tasks-manager-video-btn').length) {
      var video_frame;

      $(document).on('click', '.pn-tasks-manager-video-btn', function(e){
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        if (video_frame){
          video_frame.open();
          return;
        }

        var pn_tasks_manager_input_btn = $(this);
        var pn_tasks_manager_videos_block = pn_tasks_manager_input_btn.closest('.pn-tasks-manager-videos-block').find('.pn-tasks-manager-videos');
        var pn_tasks_manager_videos_input = pn_tasks_manager_input_btn.closest('.pn-tasks-manager-videos-block').find('.pn-tasks-manager-video-input');

        var video_frame = wp.media({
          title: (pn_tasks_manager_videos_block.attr('data-pn-tasks-manager-multiple') == 'true') ? pn_tasks_manager_i18n.select_videos : pn_tasks_manager_i18n.select_video,
          library : {
            type : 'video'
          },
          multiple: (pn_tasks_manager_videos_block.attr('data-pn-tasks-manager-multiple') == 'true') ? 'true' : 'false',
        });

        video_frame.states.add([
          new wp.media.controller.Library({
            id: 'post-gallery',
            title: (pn_tasks_manager_videos_block.attr('data-pn-tasks-manager-multiple') == 'true') ? pn_tasks_manager_i18n.select_videos : pn_tasks_manager_i18n.select_video,
            priority: 20,
            toolbar: 'main-gallery',
            filterable: 'uploaded',
            library: wp.media.query(video_frame.options.library),
            multiple: (pn_tasks_manager_videos_block.attr('data-pn-tasks-manager-multiple') == 'true') ? 'true' : 'false',
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
          pn_tasks_manager_videos_block.html('');

          $(attachments_arr).each(function(e){
            var sep = (e != (attachments_arr.length - 1))  ? ',' : '';
            ids += $(this)[0].id + sep;
            pn_tasks_manager_videos_block.append('<div class="pn-tasks-manager-video pn-tasks-manager-tooltip" title="' + $(this)[0].title + '"><i class="dashicons dashicons-media-video"></i></div>');
          });

          // Only initialize tooltips on newly added elements
          pn_tasks_manager_videos_block.find('.pn-tasks-manager-tooltip').not('.tooltipstered').tooltipster({maxWidth: 300,delayTouch:[0, 4000], customClass: 'pn-tasks-manager-tooltip'});
          pn_tasks_manager_input_btn.text((pn_tasks_manager_videos_block.attr('data-pn-tasks-manager-multiple') == 'true') ? pn_tasks_manager_i18n.select_videos : pn_tasks_manager_i18n.select_video);
          pn_tasks_manager_videos_input.val(ids);
        });
      });
    }

    if ($('.pn-tasks-manager-file-btn').length) {
      var file_frame;

      $(document).on('click', '.pn-tasks-manager-file-btn', function(e){
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        if (file_frame){
          file_frame.open();
          return;
        }

        var pn_tasks_manager_input_btn = $(this);
        var pn_tasks_manager_files_block = pn_tasks_manager_input_btn.closest('.pn-tasks-manager-files-block').find('.pn-tasks-manager-files');
        var pn_tasks_manager_files_input = pn_tasks_manager_input_btn.closest('.pn-tasks-manager-files-block').find('.pn-tasks-manager-file-input');

        var file_frame = wp.media({
          title: (pn_tasks_manager_files_block.attr('data-pn-tasks-manager-multiple') == 'true') ? pn_tasks_manager_i18n.select_files : pn_tasks_manager_i18n.select_file,
          multiple: (pn_tasks_manager_files_block.attr('data-pn-tasks-manager-multiple') == 'true') ? 'true' : 'false',
        });

        file_frame.states.add([
          new wp.media.controller.Library({
            id: 'post-gallery',
            title: (pn_tasks_manager_files_block.attr('data-pn-tasks-manager-multiple') == 'true') ? pn_tasks_manager_i18n.select_files : pn_tasks_manager_i18n.select_file,
            priority: 20,
            toolbar: 'main-gallery',
            filterable: 'uploaded',
            library: wp.media.query(file_frame.options.library),
            multiple: (pn_tasks_manager_files_block.attr('data-pn-tasks-manager-multiple') == 'true') ? 'true' : 'false',
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
          pn_tasks_manager_files_block.html('');

          $(attachments_arr).each(function(e){
            var sep = (e != (attachments_arr.length - 1))  ? ',' : '';
            ids += $(this)[0].id + sep;
            pn_tasks_manager_files_block.append('<embed src="' + $(this)[0].url + '" type="application/pdf" class="pn-tasks-manager-embed-file"/>');
          });

          pn_tasks_manager_input_btn.text((pn_tasks_manager_files_block.attr('data-pn-tasks-manager-multiple') == 'true') ? pn_tasks_manager_i18n.edit_files : pn_tasks_manager_i18n.edit_file);
          pn_tasks_manager_files_input.val(ids);
        });
      });
    }

    // CPT SEARCH FUNCTIONALITY
    if (typeof pn_tasks_manager_cpts !== 'undefined') {
      // Initialize search functionality for each CPT
      Object.keys(pn_tasks_manager_cpts).forEach(function(cptKey) {
        var cptName = pn_tasks_manager_cpts[cptKey];
        var searchToggleSelector = '.pn-tasks-manager-' + cptKey + '-search-toggle';
        var searchInputSelector = '.pn-tasks-manager-' + cptKey + '-search-input';
        var searchWrapperSelector = '.pn-tasks-manager-' + cptKey + '-search-wrapper';
        var listSelector = '.pn-tasks-manager-' + cptKey + '-list';
        var listWrapperSelector = '.pn-tasks-manager-' + cptKey + '-list-wrapper';
        var addNewSelector = '.pn-tasks-manager-add-new-cpt';

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

            if (searchInput.hasClass('pn-tasks-manager-display-none')) {
              // Show search input
              searchInput.removeClass('pn-tasks-manager-display-none').focus();
              searchToggle.text('close');
              searchWrapper.addClass('pn-tasks-manager-search-active');
            } else {
              // Hide search input and clear filter
              searchInput.addClass('pn-tasks-manager-display-none').val('');
              searchToggle.text('search');
              searchWrapper.removeClass('pn-tasks-manager-search-active');
              
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
                var itemTitle = $(this).find('.pn-tasks-manager-display-inline-table a span').first().text().toLowerCase();
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

              searchInput.addClass('pn-tasks-manager-display-none').val('');
              searchToggle.text('search');
              searchWrapper.removeClass('pn-tasks-manager-search-active');
              
              // Show all items
              itemsList.find('li').show();
            }
          });
        }

        // Task sort functionality
        if (cptKey === 'pn_tasks_task') {
          var sortToggleSelector = '.pn-tasks-manager-task-sort-toggle';
          
          $(document).on('click', sortToggleSelector, function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var sortToggle = $(this);
            var currentOrder = sortToggle.attr('data-sort-order') || 'date';
            var newOrder = currentOrder === 'date' ? 'title' : 'date';
            var list = sortToggle.closest(listSelector);
            var listWrapper = list.find(listWrapperSelector);
            
            // Update icon and tooltip
            sortToggle.attr('data-sort-order', newOrder);
            if (newOrder === 'title') {
              sortToggle.attr('title', (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.sort_by_date) ? pn_tasks_manager_i18n.sort_by_date : 'Sort by date');
              sortToggle.text('sort_by_alpha');
            } else {
              sortToggle.attr('title', (typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.sort_by_title) ? pn_tasks_manager_i18n.sort_by_title : 'Sort by title');
              sortToggle.text('sort');
            }
            
            // Show loading
            listWrapper.html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30"><div class="pn-tasks-manager-loader-circle"><div></div><div></div><div></div><div></div></div></div>');
            
            // Make AJAX request to get sorted list
            $.ajax({
              url: pn_tasks_manager_ajax.ajax_url,
              type: 'POST',
              data: {
                action: 'pn_tasks_manager_ajax',
                pn_tasks_manager_ajax_type: 'pn_tasks_manager_task_list_sort',
                pn_tasks_manager_ajax_nonce: pn_tasks_manager_ajax.pn_tasks_manager_ajax_nonce,
                orderby: newOrder
              },
              dataType: 'json',
              success: function(response) {
                if (response && response.html) {
                  listWrapper.html(response.html);
                  
                  // Reinitialize tooltips
                  if ($('.pn-tasks-manager-tooltip').length) {
                    $('.pn-tasks-manager-tooltip').not('.tooltipstered').tooltipster({
                      maxWidth: 300,
                      delayTouch: [0, 4000],
                      customClass: 'pn-tasks-manager-tooltip'
                    });
                  }
                } else {
                  listWrapper.html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + ((typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading tasks') + '</div>');
                }
              },
              error: function() {
                listWrapper.html('<div class="pn-tasks-manager-text-align-center pn-tasks-manager-p-30">' + ((typeof pn_tasks_manager_i18n !== 'undefined' && pn_tasks_manager_i18n.an_error_has_occurred) ? pn_tasks_manager_i18n.an_error_has_occurred : 'Error loading tasks') + '</div>');
              }
            });
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
        Object.keys(pn_tasks_manager_cpts).forEach(function(cptKey) {
          var searchWrapperSelector = '.pn-tasks-manager-' + cptKey + '-search-wrapper';
          var searchInputSelector = '.pn-tasks-manager-' + cptKey + '-search-input';
          var searchToggleSelector = '.pn-tasks-manager-' + cptKey + '-search-toggle';
          var listSelector = '.pn-tasks-manager-pn_tasks_manager_' + cptKey + '-list';
          var listWrapperSelector = '.pn-tasks-manager-pn_tasks_manager_' + cptKey + '-list-wrapper';

          if ($(e.target).closest(searchWrapperSelector).length) {
            clickedInsideSearch = true;
          }

          // Find active search input
          var searchInput = $(searchInputSelector + ':not(.pn-tasks-manager-display-none)');
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
          activeSearchInput.addClass('pn-tasks-manager-display-none').val('');
          activeSearchToggle.text('search');
          activeSearchWrapper.removeClass('pn-tasks-manager-search-active');
          
          // Show all items
          activeItemsList.find('li').show();
        }
      });
    }

    // Taxonomy field: Add new category functionality
    $(document).on('click', '.pn-tasks-manager-taxonomy-add-btn', function(e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      var $btn = $(this);
      var $wrapper = $btn.closest('.pn-tasks-manager-taxonomy-wrapper');
      var $input = $wrapper.find('.pn-tasks-manager-taxonomy-new-name');
      var $select = $wrapper.find('.pn-tasks-manager-taxonomy-select');
      var $termsList = $wrapper.find('.pn-tasks-manager-taxonomy-terms-list');
      var categoryName = $input.val().trim();
      var taxonomy = $select.data('taxonomy');

      if (!categoryName) {
        if (typeof pn_tasks_manager_get_main_message === 'function') {
          pn_tasks_manager_get_main_message(pn_tasks_manager_i18n.please_enter_category_name);
        } else {
          alert(pn_tasks_manager_i18n.please_enter_category_name);
        }
        return;
      }

      // Disable button while processing
      $btn.prop('disabled', true).text('Adding...');

      // Create new term via AJAX
      $.ajax({
        url: pn_tasks_manager_ajax.ajax_url,
        type: 'POST',
        data: {
          action: 'pn_tasks_manager_create_taxonomy_term',
          taxonomy: taxonomy,
          term_name: categoryName,
          nonce: pn_tasks_manager_ajax.pn_tasks_manager_ajax_nonce
        },
        success: function(response) {
          if (response.success && response.data.term_id) {
            // Add option to select
            var $option = $('<option>', {
              value: response.data.term_id,
              text: categoryName,
              selected: true
            });
            $select.append($option);

            // Add badge to terms list
            var $badge = $('<span>', {
              class: 'pn-tasks-manager-taxonomy-term-badge',
              'data-term-id': response.data.term_id,
              text: categoryName
            });
            $termsList.append($badge);

            // Clear input
            $input.val('');

            // Show success message
            if (typeof pn_tasks_manager_get_main_message === 'function') {
              var successMessage = response.data && response.data.message ? response.data.message : pn_tasks_manager_i18n.category_created_successfully;
              pn_tasks_manager_get_main_message(successMessage);
            }
          } else {
            var errorMessage = response.data && response.data.message ? response.data.message : pn_tasks_manager_i18n.error_creating_category;
            if (typeof pn_tasks_manager_get_main_message === 'function') {
              pn_tasks_manager_get_main_message(errorMessage);
            } else {
              alert(errorMessage);
            }
          }
        },
        error: function() {
          if (typeof pn_tasks_manager_get_main_message === 'function') {
            pn_tasks_manager_get_main_message(pn_tasks_manager_i18n.error_creating_category_try_again);
          } else {
            alert(pn_tasks_manager_i18n.error_creating_category_try_again);
          }
        },
        complete: function() {
          $btn.prop('disabled', false).text('Add');
        }
      });
    });

    // Allow Enter key to add category
    $(document).on('keypress', '.pn-tasks-manager-taxonomy-new-name', function(e) {
      if (e.which === 13) { // Enter key
        e.preventDefault();
        $(this).siblings('.pn-tasks-manager-taxonomy-add-btn').click();
      }
    });
  });

  $(document).on('click', '.pn-tasks-manager-toggle', function(e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    var pn_tasks_manager_toggle = $(this);

    if (pn_tasks_manager_toggle.find('i').length) {
      if (pn_tasks_manager_toggle.siblings('.pn-tasks-manager-toggle-content').is(':visible')) {
        pn_tasks_manager_toggle.find('i').text('add');
      } else {
        pn_tasks_manager_toggle.find('i').text('clear');
      }
    }

    pn_tasks_manager_toggle.siblings('.pn-tasks-manager-toggle-content').fadeToggle();
  });
})(jQuery);
