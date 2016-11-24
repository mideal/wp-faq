jQuery(document).ready(function($) {

	$('#form-mideal-faq').submit(function() {
		return false;
	});


	function midealValidateEmail(email) { 
		var reg = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return reg.test(email);
	}

    $("#form-mideal-faq input").focus(function () {
        $(this).parent().parent().removeClass("has-error");
    });
    $("#form-mideal-faq textarea").focus(function () {
        $(this).parent().parent().removeClass("has-error");
    });


	//----------------добавить Вопрос-----------------
	$(document).on('click', '#form-mideal-faq .sent-mideal-faq', function () {

		var mideal_faq_email_val  = $("#form-mideal-faq input[name$='mideal_faq_email']").val()
		var mideal_faq_email_valid = midealValidateEmail(mideal_faq_email_val);
		if(mideal_faq_email_valid == false) {
			$("#form-mideal-faq input[name$='mideal_faq_email']").parent().parent().addClass("has-error");
		}
		else if(mideal_faq_email_valid == true){
			$("#form-mideal-faq input[name$='mideal_faq_email']").parent().parent().removeClass("has-error");
		}



		var mideal_faq_name_val = $( "#form-mideal-faq input[name$='mideal_faq_name']" ).val();
		var mideal_faq_nam_len = mideal_faq_name_val.length;
		if(mideal_faq_nam_len < 3) {
			$("#form-mideal-faq input[name$='mideal_faq_name']").parent().parent().addClass("has-error");
		}
		else{
			$("#form-mideal-faq input[name$='mideal_faq_name']").parent().parent().removeClass("has-error");
		}


		var question_val = $( "#form-mideal-faq textarea[name$='mideal_faq_question']" ).val();
		var question_len = question_val.length;
		if(question_len < 3) {
			$("#form-mideal-faq textarea[name$='mideal_faq_question']").parent().parent().addClass("has-error");
		}
		else{
			$("#form-mideal-faq textarea[name$='mideal_faq_question']").parent().parent().removeClass("has-error");
		}



        if(mideal_faq_email_valid == true & question_len>2 & mideal_faq_nam_len > 2) {
			var sentdata = "action=mideal_faq_add&nonce="+myajax.nonce+"&"+$("#form-mideal-faq").serialize();
			$.ajax({
				type: "POST",
				url: myajax.url,
				dataType: "html",
				data: sentdata,
				beforeSend:function(){
					//$.fancybox.showLoading();
					$(this).attr('disabled', true);
				},
				error:function(){
					//$.fancybox.hideLoading();
					$(this).attr('disabled', false);
					$("#form-mideal-faq").html('К сожалению, произошла ошибка. Повторите попытку позже');
				},
				success: function(result){
					//$.fancybox.hideLoading();
					$("#form-mideal-faq").html('Спасибо за ваш вопрос. Он появится после модерации');
				}
			});
		}
	});


//----------------удалить Вопрос-----------------
	$(document).on('click', '#mideal-faq-list .mideal-faq-delete-post', function (e) {
		event.preventDefault();
		var ID =$(this).attr('data-id');
		var sentdata = "action=mideal_faq_delete&nonce="+myajax.nonce+"&ID="+ID;
		$.ajax({
			type: "POST",
			url: myajax.url,
			dataType: "html",
			data: sentdata,
			beforeSend:function(){
			},
			error:function(){
				alert('К сожалению, произошла ошибка. Повторите попытку позже');
			},
			success: function(result){
				$("#mideal-faq-list li.media[data-id='"+ID+"']").remove();
			}
		});
	});

	//----------------Опубликовать вопрос-----------------
	$(document).on('click', '#mideal-faq-list .mideal-faq-publish-post', function (event) {
		event.preventDefault();
		var linc =$(this);
		var ID =$(this).attr('data-id');
		var status =$(this).attr('data-status');
		var sentdata = "action=mideal_faq_publish&nonce="+myajax.nonce+"&ID="+ID+"&post_status="+status;
		$.ajax({
			type: "POST",
			url: myajax.url,
			dataType: "html",
			data: sentdata,
			beforeSend:function(){
			},
			error:function(){
				alert('К сожалению, произошла ошибка. Повторите попытку позже');
			},
			success: function(result){
				console.log(result);
				if(status!='publish'){
					$(linc).html('Снять с публикации');
					$(linc).attr('data-status','publish');
					$("#mideal-faq-list li.media[data-id='"+ID+"']").removeClass('no-published');
				} else{
					$(linc).html('Опубликовать');
					$(linc).attr('data-status','pending');
					$("#mideal-faq-list li.media[data-id='"+ID+"']").addClass('no-published');
				}
			}
		});
	});


});
