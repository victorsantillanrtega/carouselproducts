/**
* @author Victor Santillan <santillan-15@live.com>
* @copyright 2019-2025 Victor Santillan
* @license Property Victor Santillan
*/
$(document).ready(function(){
	if(isCarousel){
		if($(window).width() >= 1200){
			addSlick(dots,infinite,numberProducts,toscroll,speed,centermode,autoplay);
		}
		if($(window).width() >= 992 && $(window).width() <= 1199){
			addSlick(dots,infinite,mdss,mdsc,speed,centermode,autoplay);
		}
		if($(window).width() >= 768 && $(window).width() <= 991){
			addSlick(dots,infinite,smss,smsc,speed,centermode,autoplay);
		}
		if($(window).width() <= 767){
			addSlick(dots,infinite,xsss,xssc,speed,centermode,autoplay);
		}
	}

	$(window).on('resize', function(e) {
		console.log($(window).width());
		if(isCarousel){
			if($(window).width() >= 1200){
				$("#carouselproducts").slick('unslick');
				addSlick(dots,infinite,numberProducts,toscroll,speed,centermode,autoplay);
			}

			if($(window).width() >= 992 && $(window).width() <= 1199){
				$("#carouselproducts").slick('unslick');
				addSlick(dots,infinite,mdss,mdsc,speed,centermode,autoplay);
			}

			if($(window).width() >= 768 && $(window).width() <= 991){
				$("#carouselproducts").slick('unslick');
				addSlick(dots,infinite,smss,smsc,speed,centermode,autoplay);
			}

			if($(window).width() <= 767){
				$("#carouselproducts").slick('unslick');
				addSlick(dots,infinite,xsss,xssc,speed,centermode,autoplay);
			}

		}

	});
});

function addSlick(dots,infinite,slidetoshow,slidestoscroll,speed,centermode,autoplay){
	console.log('carouselproducts');
	$("#carouselproducts").slick({
		dots: dots,
		infinite: infinite,
		slidesToShow: slidetoshow,
		slidesToScroll: slidestoscroll,
		speed: speed,
		centerMode: centermode,
		autoplay: autoplay
	});
}