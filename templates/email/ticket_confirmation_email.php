<?php
/**
 * Ticket Confirmation email Template
 * @version 1.6
 *
 * To customize: copy this file to your theme folder as below path
 * path: your-theme-dir/eventon/templates/email/tickets/
 */
	global $eventon, $evotx;

	// $args are passed to this page
	// These are required on this template page to get correct ticket values		
		$email = $args[1];
		$args = $args[0];
		
		$eotx = $evotx->evotx_opt;
		$evo_options = get_option('evcal_options_evcal_1');
		$evo_options_2 = $eo2 = get_option('evcal_options_evcal_2');

	// inline styles
		$__styles_button = "font-size:14px; background-color:#".( !empty($evo_options['evcal_gen_btn_bgc'])? $evo_options['evcal_gen_btn_bgc']: "237ebd")."; color:#".( !empty($evo_options['evcal_gen_btn_fc'])? $evo_options['evcal_gen_btn_fc']: "ffffff")."; padding: 5px 15px; text-decoration:none; border-radius:20px; display:inline-block; box-shadow:none; text-transform:uppercase; font-size:12px;";
	// styles
		$styles = array(
			'000'=>'color:#474747; background-color:#fafafa; border:8px solid #cccccc; text-transform:uppercase; ',
			'001'=>"color:#5e5e5e; font-size:18px; font-style:italic; font-family: 'open sans',helvetica; padding:0px; margin:0px; text-transform:none",
			'002'=>"font-size:30px; font-family: helvetica; padding:0px; margin:0px; font-weight:bold; line-height:38px;",
			'003'=>"color:#1a77bf; font-size:16px; font-style:italic; font-family: 'open sans',helvetica; padding:0px; margin:0px; font-weight:bold; line-height:100%;",
			'004'=>"color:#9e9e9e; font-size:12px; font-style:italic; font-family: 'open sans',helvetica; padding:0px; margin:0px; font-weight:normal; line-height:100%;",
			'005'=>"font-size:14px; font-style:italic; font-family: 'open sans',helvetica; padding:0px; margin:0px; font-weight:bold; line-height:100%; text-transform:none;",
			'006'=>"font-size:14px; font-style:italic; font-family: 'open sans',helvetica; padding:0px; margin:0px; font-weight:bold; line-height:100%;",
			'007'=>"color:#5e5e5e; font-size:18px; font-family: 'open sans',helvetica; padding:0px; margin:0px; font-weight:bold; line-height:100%;", 
			'008'=>"color:#a5a5a5; font-size:10px; font-family: 'open sans',helvetica; padding:0px; margin:0px; font-weight:normal; text-transform:none;",

			'100'=>"padding:15px 20px 10px;",
			'101'=>"text-align:right;",
			'102'=>"margin:0px; padding:0px;",
			'103'=>"padding:10px 20px;",

			'p0'=>'padding:0px;',
			'pb5'=>'padding-bottom:5px;',
			'pb10'=>'padding-bottom:10px;',
			'pt5'=>'padding-top:5px;',
			'pt10'=>'padding-top:10px;',
			'm0'=>'margin:0px;',
			'lh100'=>'line-height:100%;',
		);
?>
<table width='100%' style='width:100%; margin:0' cellspacing='0' cellpadding='0'>
<?php 
$count = 1;
	
if(empty($args['tickets'])) return;

// Store check values
	$_event_id = $_repeat_interval = '';
	$taxMeta = get_option( "evo_tax_meta");


$processed_ticket_ids = array();
$evotx_tix = new evotx_tix();

$order_ticket_holders = $evotx_tix->get_order_ticket_holders($args['orderid']);

$tix_holder_index = 0;

// order items as ticket items - run through each ticket
foreach($args['tickets'] as $ticket_number):

	// WC order item product ID
		$product_id = $evotx_tix->get_product_id_by_ticketnumber($ticket_number);

	// initiate ticket order item class
		$event_id = $evotx_tix->get_event_id_by_product_id($product_id);

		if(empty($event_id)) continue;

		$e_pmv = get_post_custom($event_id);
		$show_add_cal = false;

	// get evo-tix CPT ID	
		$ticket_item_id = $evotx_tix->get_evotix_id_by_ticketnumber($ticket_number);
		$ticket_pmv = get_post_custom($ticket_item_id);

	// event time
		$repeat_interval = !empty($ticket_pmv['repeat_interval'])? $ticket_pmv['repeat_interval'][0]:0;
		$eventTime = $evotx->functions->get_event_time($e_pmv, $repeat_interval);

	// set check values
		if(empty($_event_id) || $_event_id != $event_id){
			$show_add_cal = true;
			$_event_id = $event_id;
			$tix_holder_index = 0;
		}

		if($_event_id == $event_id){
			if(empty($_repeat_interval)){
				$_repeat_interval = $repeat_interval;
			}
			if(!empty($_repeat_interval) && $_repeat_interval != $repeat_interval){
				$show_add_cal = true;
				$_repeat_interval = $repeat_interval; 
			}
		}
	
	// location data
		$location_terms = wp_get_post_terms($event_id, 'event_location');
		$location = false;
		if($location_terms && ! is_wp_error( $location_terms )){
			$locTermMeta = evo_get_term_meta( 'event_location', $location_terms[0]->term_id ,$taxMeta);
			$location = $location_terms[0]->name;
			if(!empty($locTermMeta['location_address']))
				$location .=', '.$locTermMeta['location_address'];
		}

	// organizer
		$organizer_terms = wp_get_post_terms($event_id, 'event_organizer');
		$organizer = false;
		if($organizer_terms && ! is_wp_error( $organizer_terms )){
			$organizer = $organizer_terms[0]->name;
		}
	
	// event ticket image
		$img_src = (!empty($e_pmv['_tix_image_id']))?	 wp_get_attachment_image_src($e_pmv['_tix_image_id'][0],'thumbnail'): false;

	// Add to calendar
	if($show_add_cal):
?>
	<tr><td class='add_to_cal' colspan='3' style='padding:20px 20px 15px'>		
		<p style="<?php echo $styles['102'].$styles['pb10'];?>"><a style='<?php echo $__styles_button;?>' href='<?php echo admin_url();?>admin-ajax.php?action=eventon_ics_download&event_id=<?php echo $event_id;?>&sunix=<?php echo $e_pmv['evcal_srow'][0];?>&eunix=<?php echo $e_pmv['evcal_erow'][0];?>' target='_blank'><?php echo evo_lang_get( 'evcal_evcard_addics', 'Add to calendar','',$eo2);?></a></p>		
	</td></tr>
<?php
	endif;
	
	// Ticket holder name and email
		if(!empty($order_ticket_holders)){
			$add_ticket_holder = $evotx_tix->get_ticket_holders_forevent( $event_id,$order_ticket_holders);		
		}		

		$ticket_purchaser = $evotx_tix->get_ticket_purchaser_info($ticket_number);
		
		$primaryTicketHolderName = (!empty($add_ticket_holder) && !empty($add_ticket_holder[$tix_holder_index]))? 
			$add_ticket_holder[$tix_holder_index]: 
			$ticket_purchaser;
	
?>
 <tr>
 	<td>
	<table style="<?php echo $styles['000'];?> width:100%;" >

	<tr>
		<td colspan='2' style='border-bottom:1px solid #e0e0e0; padding:10px 20px'>
			<table class='evotx_email_ticket_header'>
			<tr>
			<?php if($img_src):?>		
			<td>			
				<p style='padding-right:20px'><img style='border-radius:50%;height:120px; width:auto; max-width:none' src="<?php echo $img_src[0];?>" alt=""></p>
			</td>
			<td>
				<p style="<?php echo $styles['001'];?>"><?php echo evo_lang_get( 'evotxem_001', 'Your Ticket for','',$eo2);?></p>
				<p style="<?php echo $styles['002'].$styles['pb10'];?>"><a style='text-decoration:none;box-shadow:none;color:#5e5e5e' href='<?php echo get_the_permalink($event_id);?>'><?php echo get_the_title($product_id);?></a></p>

			</td>		
			<?php else:?>
			<td>
				<p style="<?php echo $styles['001'];?>"><?php echo evo_lang_get( 'evotxem_001', 'Your Ticket for','',$eo2);?></p>
				<p style="<?php echo $styles['002'].$styles['pb10'];?>"><a style='text-decoration:none;box-shadow:none;color:#5e5e5e' href='<?php echo get_the_permalink($event_id);?>'><?php echo get_the_title($product_id);?></a></p>

			</td>
			<?php endif;?>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td style="<?php echo $styles['100'];?> border-right:1px solid #e0e0e0;" >				
				
			<div style=''>
				<p style="<?php echo $styles['003'].$styles['pb5'];?>"><?php echo $eventTime;?></p>
				<p style="<?php echo $styles['004'].$styles['pb5'];?>"><?php echo evo_lang_get( 'evotxem_002', 'Date','',$eo2);?></p>
			</div>
		
			<?php
				foreach(apply_filters('evotx_confirmation_email_data_ar', array(
					array(
						'data'=>	$primaryTicketHolderName,
						'label'=>	evo_lang_get( 'evoTX_004', 'Ticket Holder\'s Name','',$eo2),
						'type'=>	'normal'
					),array(
						'data'=>	$location,
						'label'=>	evo_lang_get( 'evcal_lang_location', 'Location','',$eo2),
						'type'=>	'normal'
					),array(
						'data'=>	$organizer,
						'label'=>	evo_lang_get( 'evcal_evcard_org', 'Organizer','',$eo2),
						'type'=>	'normal'
					),
				)) as $item){
					if(!empty($item['data'])):?>
					<div style=''>
						<p style="<?php echo $styles['005'].$styles['pb5']; ?>"><?php echo $item['data'];?></p>
						<p style="<?php echo $styles['004'].$styles['pb5']; echo 'font-size:10px'?>"><?php echo $item['label'];?></p>
					</div>
					<?php endif;
				}

		?>

		</td>

		<td style='padding:10px; text-align:center;'>
			<p style="<?php echo $styles['007'];?>"><?php echo apply_filters('evotx_email_tixid_list', $ticket_number);?></p>
			<p style="<?php echo $styles['004'].$styles['pt5'];?>"><?php echo evo_lang_get( 'evotxem_003', 'Ticket Number','',$eo2);?></p>
		</td>
	</tr>

	<tr>
		<td colspan='2' style='border-top:1px solid #e0e0e0; padding:8px 20px'>
		<?php
			// ticket product variations for the ticket order item
			$variation_id = $evotx_tix->get_ticket_variation_id($ticket_number);
			if($variation_id):
				$_product = new WC_Product_Variation($variation_id );
				$variation_description = get_post_meta( $variation_id, '_variation_description', true );

				//wc_get_formatted_variation
				// /$var_data = $_product->get_formatted_variation_attributes(true);
				
				$var_data = wc_get_formatted_variation($_product, true);
				if($var_data):
			?>
				<div style=''>
					<p style="<?php echo $styles['004'].$styles['pb5'];?>"><?php echo $var_data;?></p>
				</div>
			<?php  
				endif;
			endif;

			// pluggable function  for expansion of data
			do_action('evotix_confirmation_email_data', 
				$ticket_item_id, 
				$ticket_pmv, 
				$styles, 
				$ticket_number, 
				$tix_holder_index
			);
		?>
		</td>
	</tr>


	<?php
	// terms and conditions
	if(!empty($eotx['evotx_termsc'])):
	?>	
		<tr><td style="<?php echo $styles['103'];?>">
			<p style="<?php echo $styles['008'];?>"><?php echo $eotx['evotx_termsc'];?></p>
		</td></tr>
	<?php endif;?>
	</table>
	</td>
 </tr>

 <tr><td colspan='3' style='padding-top:3px;'></td></tr>
<?php	
	
	$tix_holder_index++;
	endforeach;

?>

<?php if($email):?>
	<tr>
		<td class='email_footer' colspan='3' style='padding:20px; text-align:left;font-size:12px; color:#ADADAD'>
			<?php
				$__link = (!empty($eotx['evotx_conlink']))? $eotx['evotx_conlink']:site_url();
			?>
			<p style='<?php echo $styles['m0'];?>'><?php echo evo_lang_get( 'evoTX_007', 'We look forward to seeing you!','', $eo2)?></p>
			<p style='<?php echo $styles['m0'];?>'><a style='' href='<?php echo $__link;?>'><?php echo evo_lang_get('evoTX_008', 'Contact Us for questions and concerns','', $eo2)?></a></p>
		</td>
	</tr>
<?php endif;?>
</table>