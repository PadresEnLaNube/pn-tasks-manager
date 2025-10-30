(function($) {
	'use strict';

  $(document).ready(function() {
    if($('.taskspn-tooltip').length) {
      $('.taskspn-tooltip').tooltipster({maxWidth: 300, delayTouch:[0, 4000], customClass: 'taskspn-tooltip'});
    }

    if ($('.taskspn-select').length) {
      $('.taskspn-select').each(function(index) {
        if ($(this).attr('multiple') == 'true') {
          // For a multiple select
          $(this).TASKSPN_Selector({
            multiple: true,
            searchable: true,
            placeholder: taskspn_i18n.select_options,
          });
        } else {
          // For a single select
          $(this).TASKSPN_Selector();
        }
      });
    }

    $.trumbowyg.svgPath = taskspn_trumbowyg.path;
    $('.taskspn-wysiwyg').each(function(index, element) {
      $(this).trumbowyg();
    });
  });
})(jQuery);
