jQuery(document).ready(function($) {
    "use strict";


	/* Hide / Show Newsletter Email Review Form 
    ======================================================*/ 

	if($('.swr-lu-newsletter').is(':checked')){
		$('.swr-wc-rf-email').show();
	}else{
		$('.swr-wc-rf-email').hide();
	}
	
	$('.swr-lu-newsletter').on('change',function(){
		if ($(this).is(':checked')) {
			$('.swr-wc-rf-email').show();
		}else{
			$('.swr-wc-rf-email').hide();
		}
	});

	
	/* Initialize Raty Plugin for Star Ratings
    ======================================================*/ 
	
	$('.swr-prating').raty({ starType: 'i', readOnly: true });

	
	/* Initialize Masonry View 
    ======================================================*/ 
	
	let masonary_container = $('.swr-masonry-grid');
	masonary_container.masonry({
		itemSelector: '.swr-grid-item'
	});


	/* Show / Hide Post Content 
    ======================================================*/ 

	$('.swr_content_link').on('click',function() {
		let content_link = $(this);
		content_link.parents('.swr-excerpt').hide().delay(5000).fadeIn();
		content_link.parents('.swr-slide-content').find('.swr-show-detail').show().delay(5000).fadeOut();
	});


	/* Initialize Slick Slider Grid View 
    ======================================================*/ 

	if( $('.swr-grid-slider').length ){
		//Fetch DOM object
		let grid_slider = $('.swr-grid-slider');
		//Fetch data attributes values
		let gr_slider_slideShow = parseInt(grid_slider.data("slidesshow"));
		let gr_slider_show_dots = grid_slider.data("dots");
		if( gr_slider_show_dots === 'yes'){
			gr_slider_show_dots = true;	
		}else{
			gr_slider_show_dots = false;	
		}
		let gr_slider_slide_speed = parseInt(grid_slider.data("speed"));
		let gr_slider_enable_autoplay = grid_slider.data("autoplay");
		if( gr_slider_enable_autoplay === 'yes'){
			gr_slider_enable_autoplay = true;	
		}else{
			gr_slider_enable_autoplay = false;	
		}
		let gr_slider_autoplayspeed = parseInt(grid_slider.data("autoplayspeed"));
		let gr_slider_show_arrows = grid_slider.data("arrows");
		if( gr_slider_show_arrows === 'yes'){
			gr_slider_show_arrows = true;	
		}else{
			gr_slider_show_arrows = false;	
		}
		let gr_slider_enable_pauseonfocus = grid_slider.data("pauseonfocus");
		if( gr_slider_enable_pauseonfocus === 'yes'){
			gr_slider_enable_pauseonfocus = true;	
		}else{
			gr_slider_enable_pauseonfocus = false;	
		}
		let gr_slider_enable_pauseonhover = grid_slider.data("pauseonhover");
		if( gr_slider_enable_pauseonhover === 'yes'){
			gr_slider_enable_pauseonhover = true;	
		}else{
			gr_slider_enable_pauseonhover = false;	
		}

		$('.swr-grid-slider').slick({
			dots: gr_slider_show_dots,
			speed: gr_slider_slide_speed,
			slidesToShow: gr_slider_slideShow,
			slidesToScroll: 1,
			autoplay: gr_slider_enable_autoplay,
			autoplaySpeed: gr_slider_autoplayspeed,
			pauseOnHover: gr_slider_enable_pauseonhover,
			pauseOnFocus: gr_slider_enable_pauseonfocus,
			arrows: gr_slider_show_arrows,
			responsive: [
		    {
				breakpoint: 1024,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 1,
				}
        	},
			{
				breakpoint: 900,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2
				}
			},
			{
				breakpoint: 480,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			}

    	]
		});
	}

	
	/* Remove user account profile image 
    ======================================================*/
	
	$('.swr_close_btn a').on('click',function(){
		$(this).parents('.swr-acc-prof-img').find('img').remove();
		$(this).parents('.swr-acc-prof-img').find('#swr_profile_img').val('');
		$(this).hide();
	});

	
	/* Add review popup customer orders page
    ======================================================*/

	if( $(".swr_review_popup_btn").length ){
		$(".swr_review_popup_btn").attr("data-type","ajax");
	}

	$(".swr_review_popup_btn").fancybox({
		baseClass : "swr_order_prod_review",
		afterShow : function() {
			$('.swr-order-prod-rating').raty({ starType: 'i' });
			if($('.swr-prodorder-rat').length){
				$('.swr-prodorder-rat').raty({ starType: 'i', readOnly: true });
			}
		}
    });

		
	/* Review popup form validation and review ajax handler
    ==========================================================*/

	$(document).on('submit','form.swr_order_review_frm',function(event){
		//Prevent event default
		event.preventDefault();
		//Fetch values for form submission
		let form_id = $(this).attr('id');
		let $this = $(this).find('.prod_order_review_btn');
		let swr_btn_text = $this.text();
		let review_comment = $(this).find('.swr-field-wrap .swr_review_feedback').val();
		let review_score_val = $(this).find('input[name="score"]').val();
		let review_prod_id = $(this).find('input[name="swr_review_prod_id"]').val();
		if( review_comment.length === 0 ){
			alert("Please add a review comment as it is a required field!");
		}else if( review_score_val === '' ){
			alert("Please add a rating for the product as it is a required field!");
		}else{
			jQuery.ajax({
				type: "POST",
				dataType: "json",
				url: swr_params.swr_ajax_url,
				data: {
					action: 'swr_save_order_product_review',
					nonce: swr_params.swr_nonce,
					review_score : review_score_val,
					review_comment : review_comment,
					review_prod_id : review_prod_id
				},
				beforeSend : function(){
					$this.text('Saving..');
					$this.attr('disabled', true);
				},
				success: function( response ){
					if( response.success ){
						let comment = response.comment;
						let rating = response.rating;
						let review_html = '';
						setTimeout(() => { 
							$this.text('Review Saved');	
						},2000);
						setTimeout(() => { 
							$("#"+form_id).find('input[name="score"]').val('');
							$("#"+form_id).find('.swr-field-wrap .swr_review_feedback').val('');
							$("#"+form_id).css("display","none");
							review_html += 
							`<div class="swr-order-prod-rat" data-score="`+rating+`"></div>
							<div class="swr_order_review_comment">
								<p>`+comment+`</p>
							</div>`;
							$("#"+form_id).parent('.swr-order-prod-review-form').find('.swr_order_prod_title').after(review_html);
							$('.swr-order-prod-rat').raty({ starType: 'i', readOnly: true });
							$("#"+form_id).remove();
						},3500);
					}else{
						alert("Review cannot be saved! Please try again or contact support");
						$this.text(swr_btn_text);
						$this.removeAttr('disabled', true);	
					}
				}
			});
		}
		

	});
	

	/* Comment review form set enctype 
    ======================================================*/

	$("form#commentform").attr( "enctype", "multipart/form-data" );

	/* Photo Review Comment View
    ======================================================*/
	$('.swr-photo-review-img').fancybox({
		 baseClass:"swr_com_review_images"
	});

});
