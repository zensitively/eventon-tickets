/** 
 * Javascript: Event Tickets Calendar
 * @version  1.3.8
 */
jQuery(document).ready(function($){

	// assign manual WC Product
		$('.evotx_manual_wc_prod').on('click',function(){
			var data_arg = {
				action: 		'evotx_assign_wc_products',
				eid:			$(this).data('eid'),
				wcid:			$(this).data('wcid'),
			};				
			$.ajax({
				beforeSend: function(){},
				type: 'POST',
				url:evotx_admin_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					//console.log(data.status);
					if(data.status=='good'){
						$('.evotx_manual_wc_product').find('.ajde_popup_text').html(data.content);
					}else{
						$('.evotx_manual_wc_product').find('.ajde_popup_text').html('Could not load content');
					}
				},complete:function(){}
			});
		});
		$('.evotx_manual_wc_product').on('click','.evotx_submit_manual_wc_prod',function(){
			FORM = $(this).closest('.evotx_manual_wc_product');
			var data_arg = {
				action: 		'evotx_save_assign_wc_products',
				eid:			$(this).data('eid'),
				wcid:			FORM.find('select').val(),
			};				
			$.ajax({
				beforeSend: function(){ FORM.addClass('evoloading');},
				type: 'POST',
				url:evotx_admin_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					FORM.find('.message').html( data.msg ).show();
				},complete:function(){FORM.removeClass('evoloading');}
			});
		});

	// GET attendee list
		$('#evotx_attendees').on('click',function(){

			RIACTIVE = $(this).data('riactive');
			if(RIACTIVE =='no'){
				var data_arg = {
					action: 		'the_ajax_evotx_a1',
					eid:			$(this).data('eid'),
					wcid:			$(this).data('wcid'),
					postnonce: evotx_admin_ajax_script.postnonce, 
					ri:'all'
				};
				//console.log(data_arg);				
				$.ajax({
					beforeSend: function(){},
					type: 'POST',
					url:evotx_admin_ajax_script.ajaxurl,
					data: data_arg,
					dataType:'json',
					success:function(data){
						//console.log(data.status);
						if(data.status=='0'){
							$('.evotx_lightbox').find('.ajde_popup_text').html(data.content);
						}else{
							$('.evotx_lightbox').find('.ajde_popup_text').html('Could not load attendee list');
						}

					},complete:function(){}
				});
			}else{
				$('body').find('#evotx_view_attendees_list').html('').hide();
			}
		});	
		$('body').on('click','#evotx_VA_submit',function(){
			var data_arg = {
				action: 		'the_ajax_evotx_a1',
				eid:			$(this).data('e_id'),
				wcid:			$(this).data('wcid'),
				ri: $('#evotx_event_repeatInstance').val(),
				postnonce: evotx_admin_ajax_script.postnonce, 
			};
			//console.log(data_arg);			
			$.ajax({
				beforeSend: function(){ $('#evotx_view_attendees').addClass('loading'); },
				type: 'POST',
				url:evotx_admin_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					//alert(data);
					if(data.status=='0'){
						$('body').find('#evotx_view_attendees_list').html(data.content).slideDown();						
					}else{
						$('.evotx_lightbox ').find('.ajde_popup_text').html('Could not load attendee list');
					}
				},complete:function(){ $('#evotx_view_attendees').removeClass('loading');}
			});	
		});

	// CHECK in attendees
		$('.evotx_lightbox').on('click','span.evotx_status', function(){
			var obj = $(this);
			var status = obj.attr('data-status');
			var data_arg = {
				action: 'the_ajax_evotx_a5',
				tid: obj.attr('data-tid'),
				tiid: obj.attr('data-tiid'),
				status:  status
			};
			$.ajax({
				beforeSend: function(){
					obj.html( obj.html()+'...' );
				},
				type: 'POST',
				url:evotx_admin_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					obj.attr({'data-status':data.new_status}).html(data.new_status_lang).removeAttr('class').addClass('evotx_status '+ data.new_status);
				}
			});
		});


	// check in attendees via evo-tix post page
		$('#evotx_ticketItem_tickets').on('click','.tix_status', function(){
			var obj = $(this);
			var data_arg = {
				action: 'the_ajax_evotx_a5',
				tid: obj.attr('data-tid'),
				tiid: obj.attr('data-tiid'),
				status: obj.attr('data-status'),
			};
			$.ajax({
				beforeSend: function(){
					obj.html( obj.html()+'...' );
				},
				type: 'POST',
				url:evotx_admin_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					//alert(data);
					obj.attr({'data-status':data.new_status}).html(data.new_status_lang).removeAttr('class').addClass('tix_status '+ data.new_status);

				}
			});
		});

	// Send attendee list via email
		$('body').on('change','#evotx_emailing_options',function(){
			VAL = $(this).find(":selected").attr('value');
			if(VAL!='someone'){
				$('#evotx_emailing').find('p.text').hide();
			}else{
				$('#evotx_emailing').find('p.text').show();
			}
		});
		$('body').on('click','#evotx_email_submit', function(){
			var obj = $(this);
			CONTENT = $('#evotx_emailing');
			var data_arg = {
				action: 		'the_ajax_evotx_a8',
				eid:			$(this).attr('data-eid'),
				wcid:			$(this).attr('data-wcid'),
				type:			$('#evotx_emailing_options').val(),
				emails:			CONTENT.find('.text input').val(),
				subject:		CONTENT.find('.subject input').val(),
				message:		CONTENT.find('.textarea textarea').val(),
				repeat_interval:$('#evotx_emailing_repeat_interval').val(),
			};	

			if(data_arg.subject == '' ){
				obj.closest('.ajde_popup_text').siblings('.message').addClass('bad').html('Required Fields Missing').show();
			}else{
				obj.closest('.ajde_popup_text').siblings('.message').hide();
				$.ajax({
					beforeSend: function(){
						obj.closest('.ajde_popup_text').addClass('loading');
					},
					type: 'POST',
					url:evotx_admin_ajax_script.ajaxurl,
					data: data_arg,
					dataType:'json',
					success:function(data){
						//console.log(data);
						if(data.status=='0'){
							obj.closest('.ajde_popup_text').siblings('.message').addClass('good').html('Emails Sent').show();
						}else{
							obj.closest('.ajde_popup_text').siblings('.message').addClass('bad').html('Could not send emails. Try again later.').show();
						}
					},complete:function(){obj.closest('.ajde_popup_text').removeClass('loading');}
				});
			}	
		});
	
	// Resend Ticket email
		$('.evoTX_resend_email').on('click',function(){
			var obj = $(this);
			MSG = obj.closest('.evoTX_rc_in').find('p.message');

			var data_arg = {
				action: 'the_ajax_evotx_a55',
				orderid: obj.data('orderid'),
			};

			// send the custom email send value
			if(obj.hasClass('customemail') && obj.siblings('input').val()!='' ){
				data_arg['email'] = obj.siblings('input').val();
			}
						
			$.ajax({
				beforeSend: function(){
					obj.closest('.evoTX_resend_conf').addClass('loading');
				},
				type: 'POST',
				url:evotx_admin_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					
					STR = (data.status=='good')? 's':'f';
					if(STR=='f')	MSG.addClass('error');

					MSG.html( MSG.data(STR));	
					MSG.fadeIn().delay(8000).fadeOut();

				},complete:function(){
					obj.closest('.evoTX_resend_conf').removeClass('loading');
				}
			});
		});


	// view rest repeat occurance 
		$('body').on('click', '.evotx_ri_view_more a', function(){
			$(this).parent().siblings('.evotx_ri_cap_inputs').find('p').fadeIn();
			$(this).parent().fadeOut();
		});
	
	// Toggle completed vs non completed orders
		$('body').on('click','span.separatation',function(){
			$(this).parent().find('span.hidden').toggleClass('bad');
		});
});