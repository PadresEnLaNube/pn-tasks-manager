(function($) {
	'use strict';

	function taskspn_timer(step) {
		var step_timer = $('.taskspn-player-step[data-taskspn-step="' + step + '"] .taskspn-player-timer');
		var step_icon = $('.taskspn-player-step[data-taskspn-step="' + step + '"] .taskspn-player-timer-icon');
		
		if (!step_timer.hasClass('timing')) {
			step_timer.addClass('timing');

      setInterval(function() {
      	step_icon.fadeOut('fast').fadeIn('slow').fadeOut('fast').fadeIn('slow');
      }, 5000);

      setInterval(function() {
      	step_timer.text(Math.max(0, parseInt(step_timer.text()) - 1)).fadeOut('fast').fadeIn('slow').fadeOut('fast').fadeIn('slow');
      }, 60000);
		}
	}

	$(document).on('click', '.taskspn-popup-player-btn', function(e){
  	taskspn_timer(1);
	});

	$('.taskspn-carousel-main-images .owl-carousel').owlCarousel({
    margin: 10,
    center: true,
    nav: false, 
    autoplay: true, 
    autoplayTimeout: 5000, 
    autoplaySpeed: 2000, 
    pagination: true, 
    responsive:{
      0:{
        items: 2,
      },
      600:{
        items: 3,
      },
      1000:{
        items: 4,
      }
    }, 
  });
})(jQuery);
