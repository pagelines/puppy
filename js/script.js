jQuery(document).ready(function($){
	
	$(window).scroll(function () {
		if ($(window).scrollTop() >= $(document).height() - $(window).height() - puppy_script_params.scroll) {
			$('#puppy-container').animate({
				'bottom': 10
			}, 2000, 'easeOutBounce').css({'visibility':'visible'});
		}
		$('.puppy-close').click(function () {
			$('#puppy-container').css({'visibility':'hidden','opacity':'0','transition':'visibility 0s 1s, opacity 1s linear'});
		});
	});
})