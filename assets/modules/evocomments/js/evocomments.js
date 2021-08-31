$(function() {
	/* Авторизация через соц.сети */
	$(document).on('click', '[data-evocomments-provider]', function(e){
		e.preventDefault();	
		var provider = $(this).data('evocomments-provider');
		window.addEventListener('message', event => {
    		if(event.data.evocomments) {
				$.ajax({
					url: 'assets/modules/evocomments/ajax.php',
					type: 'post',
					dataType: 'json',
					data: {action: 'login', provider: provider, profile: event.data.evocomments}
				})
				.done(function(result) {
					if(result.token) {
						window.location.reload();
					}
				});
			}
    	});

		var url = 'https://evocomments.online/oauth?provider='+provider;
		var t = (screen.width - 800) / 2,
            i = (screen.height - 600) / 2,
            c = url;
            window.open(c, "EvoComments", "width=840, height=620, top=" + i + ", left=" + t);
	});

	/* Авторизация анонимно по Имя + Почта */
	$(document).on('click', '[data-evocomments-login]', function(e){
		e.preventDefault();
		var form = $(this).closest('[data-evocomments-auth]');
		var provider = 'simple';
		var profile = {'firstName': $(form).find('input[name="ec_name"]').val(), 'email': $(form).find('input[name="ec_email"]').val()};
		
		$.ajax({
			url: 'assets/modules/evocomments/ajax.php',
			type: 'post',
			dataType: 'json',
			data: {action: 'login', provider: provider, profile: profile}
		})
		.done(function(result) {
			if(result.token) {
				window.location.reload();
			} else {
				$(form).find('input[name="ec_'+result.error+'"]').addClass('is-invalid');
			}
		});
	});

	

	$(document).on('click', '[data-evocomments-logout]', function(e){
		e.preventDefault();
		$.ajax({
			url: 'assets/modules/evocomments/ajax.php',
			type: 'post',
			dataType: 'json',
			data: {action: 'logout'}
		})
		.done(function(result) {
			if(result.status=='ok') {
				window.location.reload();
			}
		});
	});

	$(document).on('click', '[data-evocomments-reply]', function(e){
		e.preventDefault();
		if($(this).next('[data-evocomments-form]').length==0) {
			var evocomment_form = $('[data-evocomments-form]').html();
			$(this).after('<div data-evocomments-form>'+evocomment_form+'</div>');
		} else {
			$(this).next('[data-evocomments-form]').toggle();
		}
	});


	/* Публикация комментария */
	$(document).on('click', '[data-evocomments-submit]', function(e){
		e.preventDefault();
		var form = (this).closest('[data-evocomments-form]');
		var parent = $(this).closest('[data-evocomments-comment-id]');
		var parent_id = $(parent).data('evocomments-comment-id') || 0;
		var comment = $(form).find('textarea[name="ec_comment"]').val();
		var page_id = $(this).closest('[data-evocomments-page-id]').data('evocomments-page-id');
		if(!comment || !page_id) return false;
		$.ajax({
			url: 'assets/modules/evocomments/ajax.php',
			type: 'post',
			dataType: 'json',
			data: {action: 'post', parent_id: parent_id, comment: comment, page_id: page_id},
			beforeSend: function(){
				$(form).fadeTo(300, .5);
			}
		})
		.done(function(result) {
			if(result.status=='ok') {
				//window.location.reload();
				//console.log(result);
				var html = result.result.html;
				var parent_id = result.result.parent_id;
				if(parent_id>0) {
					$(form).fadeOut(300, function(){
						$(form).remove();
						if($('[data-evocomments-comment-id="'+parent_id+'"]').find('.ec_childWrapper').length) {
							$('[data-evocomments-comment-id="'+parent_id+'"]').find('.ec_childWrapper').prepend(html);
						} else {
							$('[data-evocomments-comment-id="'+parent_id+'"]').after(html);
						}
						
					});
				} else {
					$('.mycomments').prepend(html);
					$('[name="ec_comment"]').val('');
				}
			}
			$(form).fadeTo(300, 1);
		});
	});

	/* Подгрузка комментариев */
	$(document).on('click', '[data-evocomments-loadmore]', function(e){
		e.preventDefault();
		var offset = $(document).find('[data-evocomments-comment-id]').length;
		var page_id = $(document).find('[data-evocomments-page-id]').data('evocomments-page-id');
		var noForm = $(document).find('[data-evocomments-noform]').data('evocomments-noform') || false;
		$.ajax({
			url: 'assets/modules/evocomments/ajax.php',
			type: 'post',
			dataType: 'json',
			data: {action: 'loadmore', offset: offset, page_id: page_id, noForm:noForm},
			beforeSend: function(){
				$('.ec_moreBtn').fadeTo(300, .5);
			}
		})
		.done(function(result) {
			if(result.status=='ok') {
				$('#evoComments').html(result.result);
			}
		});
	});


	/* Клик по имени автора */
	$(document).on('click', '.ec_reply_name', function(e){
		e.preventDefault();
		var parent_id = $(this).data('evocomments-parent-id');
		var sc = $(document).find('[data-evocomments-comment-id="'+parent_id+'"] > .ec_comment');
		var d = $(sc).offset().top;
		
        $('html, body').animate({scrollTop: d-50}, 500, function(){
        	$(sc).fadeTo(150, 0.25, function(){
				$(sc).fadeTo(150, 1);
			});
        });
	});
	
});
