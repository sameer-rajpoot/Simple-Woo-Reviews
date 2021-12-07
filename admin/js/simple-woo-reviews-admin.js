jQuery(document).ready(function($) {
	'use strict';

	

	/* Syncing Reviews Post Type Ajax Based Amin View
    ======================================================*/

	$('#sr_sync_btn').on('click',function(){
		let $this = $(this);
		let swr_btn_text = $this.val();
		
		$.ajax({
			type:"POST",
			datatype:"json",
			url: swr_ajax_url.admin_url,
			data:{
				action: "swr_get_wc_reviews",
			},
			beforeSend:function(){
				$this.val('Please Wait Syncing...');
				$this.attr('disabled', true);
			},
			success:function(response){
				if(response.success){
					$this.val(response.review_count +' Reviews Found');	
					setTimeout(() => { 
						$this.val(response.review_count +' Reviews Successfully Synced');	
					},2000);
					setTimeout(() => { 
						$this.val(swr_btn_text);
						$this.removeAttr('disabled', true);		
					},3500);
				}else{
					alert("No Reviews found or something went wrong! Please try again or contact support");
					$this.val(swr_btn_text);
					$this.removeAttr('disabled', true);	
				}

			}
		});
	});

	
	/* Initialize Raty Star Ratings Ajax Based Content
    ======================================================*/
	
	$(".swr_view_popup").fancybox({
		baseClass : "swr_quick_view",
		afterShow : function() {
			$('.swr-adcomm-rating').raty({ starType: 'i', readOnly: true });
		}
	});

	/* Initialize Raty Star Ratings Ajax Based Content
    ======================================================*/
	$('.swr-adrating-col').raty({ starType: 'i', readOnly: true });


	/* Add WP Media Uploader For File Uploads Admin View
    ======================================================*/
	swrUserAvatar();
	
	/* Hide / Show Admin Settings Fields
    ======================================================*/

	if($('#swr_enable_mailchimp,#swr_enable_captcha').is(':checked')){
		$('.swr-hide').show();
	}else{
		$('.swr-hide').hide();
	}
	
	$('#swr_enable_mailchimp,#swr_enable_captcha').on('change',function(){
		if ($(this).is(':checked')) {
			$('.swr-hide').show();
		}else{
			$('.swr-hide').hide();
		}
	});

	if($('#swr_img_reviews').is(':checked')){
		$('.swr-hide-img-review').show();
	}else{
		$('.swr-hide-img-review').hide();
	}
	
	$('#swr_img_reviews').on('change',function(){
		if ($(this).is(':checked')) {
			$('.swr-hide-img-review').show();
		}else{
			$('.swr-hide-img-review').hide();
		}
	});

	/* WP Color Picker Initialization
    ======================================================*/
	$( ".swr-color-picker" ).wpColorPicker();

	/* Review Comment Popup View
    ======================================================*/
    
    $(".swr-adcom-img-link").fancybox({
    	baseClass : "swr_photo_review"
    });
			

});


/* WP Media Uploader For Uploading File Admin View
======================================================*/

function wpMediaEditor() {
	wp.media.editor.open();
	wp.media.editor.send.attachment = function(props, attachment) {
		if( attachment.sizes !== undefined ){
			if( attachment.sizes.hasOwnProperty('thumbnail') ){
				var imgURL = attachment.sizes.thumbnail.url;
				var imgClass = "swr-thumb";
			}else{
				imgURL = attachment.sizes.full.url;  
				imgClass = "swr-full";
			}
		}else{
			imgURL = attachment.url;  
			imgClass = "swr-full";
		}
		jQuery('input.swr-attachment-id').val(attachment.id);
		jQuery('div.swr-attachment-image img').remove();
		jQuery('div.swr-attachment-image').append(
			jQuery('<img>').attr({
				'src': imgURL,
				'class': imgClass,
				'alt': attachment.title
			})
		);
		jQuery('button.swr-remove-media').fadeIn(250);
	};
}


/* WP Media Uploader Setting User Photo Admin View
======================================================*/

function swrUserAvatar() {

	var buttonAdd = jQuery('button.swr-add-media');
	var buttonRemove = jQuery('button.swr-remove-media');

	buttonAdd.on('click', function(event) {
		event.preventDefault();
		wpMediaEditor();
	});

	buttonRemove.on('click', function(event) {
		event.preventDefault();
		jQuery('input.swr-attachment-id').val(0);
		jQuery('div.swr-attachment-image img').remove();
		jQuery(this).fadeOut(250);
	});

	jQuery(document).on('click', 'div.swr-attachment-image img', function() {
		wpMediaEditor();
	});

	if(
		jQuery('input.swr-attachment-id').val() === 0
		|| !jQuery('div.swr-attachment-image img').length
	){
		buttonRemove.css( 'display', 'none' );
	} 
}

