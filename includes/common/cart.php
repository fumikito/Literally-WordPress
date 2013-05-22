<?php
/**
 * Abstract class for form object
 * 
 * This class has product related functions.
 * 
 * @since 0.9.3.1
 */
abstract class LWP_Cart extends Literally_WordPress_Common{
	
	/**
	 * Returns current product if specified.
	 * 
	 * @return false|Object
	 */
	public function get_current_product(){
		if(isset($_GET['lwp-id'])){
			$product = get_post($_GET['lwp-id']);
			return $product->ID > 0 ? $product : false;
		}else{
			return false;
		}
	}
	
	
	
	/**
	 * Check if specified post can be bought
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $id
	 * @return object
	 */
	protected function test_post_id($id){
		global $lwp, $wpdb;
		$post_types = $lwp->post->post_types;
		if($lwp->event->is_enabled()){
			$post_types[] = $lwp->event->post_type;
		}
		if($lwp->subscription->is_enabled()){
			$post_types[] = $lwp->subscription->post_type;
		}
		$book = get_post($id);
		if(!$book){
			//If specified content doesn't exist, die.
			$this->kill($this->_("No content is specified."), 404);
		}
		if(false === array_search($book->post_type, $post_types)){
			//If specified content doesn't exist, die.
			$this->kill(sprintf($this->_('You cannot buy "%s".'), esc_html($book->post_title)), 403);
		}
		//If ticket is specified, check selling limit
		if($book->post_type == $lwp->event->post_type){
			$selling_limit = get_post_meta($book->post_parent, $lwp->event->meta_selling_limit, true);
			if($selling_limit && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $selling_limit)){
				//Selling limit is found, so check if it's oudated
				$limit = strtotime($selling_limit.' 23:59:59');
				$current = current_time('timestamp');
				if($limit < $current){
					$this->kill($this->_("Selling limit has been past. There is no ticket available."), 404);
				}
			}
			//Check if stock is enough
			$stock = lwp_get_ticket_stock(false, $book);
			if($stock <= 0){
				$this->kill($this->_("Sorry, but this ticket is sold out."), 403);
			}
		}
		return $book;
	}
	
	
	
	/**
	 * Create pseudo ticket
	 * 
	 * @global wpdb $wpdb
	 * @return \stdClass 
	 */
	protected function get_random_ticket(){
		$ticket = new stdClass();
		$ticket->post_title = $this->_('Dammy Ticket');
		$ticket->updated = date('Y-m-d H:i:s');
		$ticket->price = 1000;
		$ticket->ID = 100;
		$ticket->num = 1;
		$ticket->consumed = 0;
		return $ticket;
	}
	
	
	
	/**
	 * Get post object as event
	 * 
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @return object 
	 */
	protected function get_random_event(){
		global $wpdb, $lwp;
		$post_types = implode(',', array_map(create_function('$row', 'return "\'".$row."\'"; '), $lwp->event->post_types));
		$event = $wpdb->get_row("SELECT * FROM {$wpdb->posts} WHERE post_type IN ({$post_types}) ORDER BY RAND()");
		if(!$event){
			$this->kill($this->_('Sorry, but event is not found.'), 404);
		}
		return $event;
	}
	
	
	
	/**
	 * Returns random post if exists for sand box
	 * 
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp 
	 * @return object
	 */
	protected function get_random_post(){
		global $wpdb, $lwp;
		$post_types = $lwp->option['payable_post_types'];
		if($lwp->event->is_enabled()){
			$post_types[] = $lwp->event->post_type;
		}
		if($lwp->subscription->is_enabled()){
			$post_types[] = $lwp->subscription->post_type;
		}
		$post_types_in = implode(',', array_map(create_function('$a', 'return "\'".$a."\'";'), $post_types));
		$sql = <<<EOS
			SELECT p.* FROM {$wpdb->posts} AS p
			INNER JOIN {$wpdb->postmeta} AS pm
			ON p.ID = pm.post_id AND pm.meta_key = 'lwp_price'
			WHERE p.post_status IN ('draft', 'publish', 'future') AND p.post_type IN ({$post_types_in}) AND CAST(pm.meta_value AS signed) > 0
			ORDER BY RAND()
EOS;
		return $wpdb->get_row($sql);
	}
}