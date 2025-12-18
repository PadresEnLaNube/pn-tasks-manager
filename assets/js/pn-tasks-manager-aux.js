(function($) {
	'use strict';

  $(document).ready(function() {
    if($('.pn-tasks-manager-tooltip').length) {
      // Only initialize tooltips that haven't been initialized yet
      $('.pn-tasks-manager-tooltip').not('.tooltipstered').tooltipster({maxWidth: 300, delayTouch:[0, 4000], customClass: 'pn-tasks-manager-tooltip'});
    }

    if ($('.pn-tasks-manager-select').length) {
      $('.pn-tasks-manager-select').each(function(index) {
        if ($(this).attr('multiple') == 'true') {
          // For a multiple select
          $(this).PN_TASKS_MANAGER_Selector({
            multiple: true,
            searchable: true,
            placeholder: pn_tasks_manager_i18n.select_options,
          });
        } else {
          // For a single select
          $(this).PN_TASKS_MANAGER_Selector();
        }
      });
    }

    $.trumbowyg.svgPath = pn_tasks_manager_trumbowyg.path;
    $('.pn-tasks-manager-wysiwyg').each(function(index, element) {
      $(this).trumbowyg();
    });
  });
})(jQuery);
