<?php
/**
 * Ticket extension of the event
 * @version 0.1
 */

class evotx_event extends EVO_Event{

	public function __construct($event_id, $event_pmv='', $RI=0){
		parent::__construct($event_id, $event_pmv);
		$this->wcid = $this->get_wcid();
		$this->wcmeta = $this->wcid? get_post_custom($this->wcid): false;	
		$this->ri = $RI;	
	}

	function get_wcid(){
		return $this->get_prop('tx_woocommerce_product_id')? (int)$this->get_prop('tx_woocommerce_product_id'):false;
	}
	
	function next_available_ri($current_ri_index){
		$current_ri_index = empty($current_ri_index)? 0:$current_ri_index;
		$stock_status = $this->get_ticket_stock_status();
		if($stock_status=='outofstock') return false;
		return $this->get_next_current_repeat($current_ri_index);
	}

// tickets
	function has_tickets(){
		// check if tickets are enabled for the event
			if( !$this->check_yn('evotx_tix')) return false;

		// if tickets set to out of stock 
			if(!empty($this->wcmeta['_stock_status']) && $this->wcmeta['_stock_status'][0]=='outofstock') return false;
		
		// if manage capacity separate for Repeats
		$ri_count_active = $this->is_ri_count_active();

		if($ri_count_active){
			$ri_capacity = $this->get_prop('ri_capacity');
				$capacity_of_this_repeat = 
					(isset($ri_capacity[ $this->ri ]) )? 
						$ri_capacity[ $this->ri ]
						:0;
				return ($capacity_of_this_repeat==0)? false : $capacity_of_this_repeat;
		}else{
			// check if overall capacity for ticket is more than 0
			$manage_stock = (!empty($this->wcmeta['_manage_stock']) && $this->wcmeta['_manage_stock'][0]=='yes')? true:false;
			$stock_count = (!empty($this->wcmeta['_stock']) && $this->wcmeta['_stock'][0]>0)? $this->wcmeta['_stock'][0]: false;
			
			// return correct
			if($manage_stock && !$stock_count){
				return false;
			}elseif($manage_stock && $stock_count){	return $stock_count;
			}elseif(!$manage_stock){ return true;}
		}
	}
	function is_stop_selling_now(){
		$stop_sell = $this->get_prop('_xmin_stopsell');
		if($stop_sell ){

			//date_default_timezone_set('UTC');	
			$current_time = current_time('timestamp');

			$start_unix = $this->get_event_time();
			
			$timeBefore = (int)($this->get_prop('_xmin_stopsell'))*60;	

			$cutoffTime = $start_unix -$timeBefore;

			return ($cutoffTime < $current_time)? true: false;
		}else{
			return false;
		}
	}

// stock
	function get_ticket_stock_status(){
		return (!empty($this->wcmeta['_stock_status']))? $this->wcmeta['_stock_status'][0]: false;
	}
	function is_ri_count_active(){
		return (!empty($this->wcmeta['_manage_stock']) && $this->wcmeta['_manage_stock'][0]=='yes'
		&& ($this->get_prop('_manage_repeat_cap')) && $this->get_prop('_manage_repeat_cap')=='yes'
		&& ($this->get_prop('ri_capacity'))
		&& $this->is_repeating_event()
		)? true: false;
	}

}