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
		if(isset($_REQUEST['lwp-id'])){
			$product = get_post($_REQUEST['lwp-id']);
			return $product->ID > 0 ? $product : false;
		}else{
			return false;
		}
	}
	
	/**
	 * Returns 
	 * @return array
	 */
	public function get_current_products(){
		// TODO: カートを実装したときに変更
		$product = $this->get_current_product();
		if($product){
			return array($product);
		}else{
			return array();
		}
	}
	
	/**
	 * Returns currently specified quantity
	 * 
	 * @param object $post
	 * @return int
	 */
	protected function get_current_quantity($post){
		return isset($post->ID, $_REQUEST['quantity'][$post->ID])
				? max(1, absint($_REQUEST['quantity'][$post->ID]))
				: apply_filters('lwp_cart_initial_quantity', 1, $post, get_current_user_id());
	}
	
	
	
	/**
	 * Get max quantity for specified post
	 * 
	 * @global Literally_WordPress $lwp
	 * @param object $post
	 * @return int
	 */
	 protected function get_available_quantity($post){
		global $lwp;
		$max_quantity = apply_filters('lwp_cart_max_quantity', 100, $post, get_current_user_id());
		// Available quantity
		$available_quantity = apply_filters('lwp_cart_available_quantity', 1, $post, get_current_user_id());
		// Checkstock
		switch($post->post_type){
			case $lwp->event->post_type:
				$max_quantity = min($max_quantity, lwp_get_ticket_stock(false, $post));
				break;
		}
		// Now define max_quantity
		return min($max_quantity, $available_quantity);
	}
	
	
	
	/**
	 * Test if currently specified product is enough.
	 * 
	 * @param ojbect $post
	 * @return boolean
	 */
	protected function test_current_quantity($post){
		$current = $this->get_current_quantity($post);
		$available = $this->get_available_quantity($post);
		return ($current > 0 && $available >= $current);
	}
	
	
	
	/**
	 * Returns products array for price list
	 * 
	 * @param array $products
	 * @param boolean $changable
	 * @return array
	 */
	protected function get_price_list($products = array(), $changable = false){
		$list = array();
		foreach($products as $product){
			$name = $this->get_item_name($product);
			$unit_price = lwp_price($product);
			$quantity = $this->get_current_quantity($product);
			$sub_total = $unit_price * $quantity;
			$available_quantity = $this->get_available_quantity($product);
			$selectable = ($changable && $available_quantity > 1 );
			$list[$product->ID] = compact('name', 'unit_price', 'quantity', 'sub_total', 'available_quantity', 'selectable');
		}
		return $list;
	}
	
	
	
	/**
	 * Render price list
	 * 
	 * @param object $products
	 * @param boolean $changable If true, user can see select product quantity.
	 */
	protected function the_price_list($products = array(), $changable = false){
		$lists = $this->get_price_list($products, $changable);
		?>
		<table class="price-table">
		<caption><?php $this->e('Order Detail'); ?></caption>
		<thead>
			<tr>
				<th scope="col"><?php $this->e('Item'); ?></th>
				<th scope="col"><?php $this->e('@'); ?></th>
				<th scope="col"><?php $this->e('Quantity'); ?></th>
				<th scope="col">&nbsp;</th>
				<th class="price" scope="col"><?php $this->e('Subtotal'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td class="recalculate" colspan="3">
					<?php
						$can_select = false;
						foreach($lists as $list){
							if($list['selectable']){
								$can_select = true;
							}
						}
						if($can_select): 
					?>
						<input class="button button-calculate" type="submit" value="<?php $this->e('Recalculate'); ?>" /><br />
						<span class="description"><?php $this->e('If you change quantity, click recalculate.'); ?></span>
					<?php else: ?>
						&nbsp;
					<?php endif; ?>
				</td>
				<th scope="row"><?php $this->e('Total'); ?></th>
				<td class="price">
					<?php
						$total = 0;
						foreach($lists as $list){
							$total += $list['sub_total'];
						}
						echo number_format_i18n($total)." ".lwp_currency_code(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach($lists as $id => $item): ?>
			<tr>
				<th scope="row">
					<?php echo apply_filters('lwp_cart_product_title', esc_html($item['name']), $id); ?>
				</th>
				<td>
					<?php echo number_format_i18n($item['unit_price'])." ".lwp_currency_code(); ?>
				</td>
				<td class="quantity">
					<?php if($item['selectable']): ?>
						<input type="hidden" class="current_quantity" value="<?php echo $item['quantity']; ?>" />
						<select class="quantity-changer" name="quantity[<?php echo $id; ?>]">
							<?php foreach(lwp_option_steps($item['available_quantity']) as $q): ?>
							<option value="<?php echo $q; ?>"<?php if($q == $item['quantity']) echo ' selected="selected"';?>>
								<?php echo number_format($q); ?>
							</option>
							<?php endforeach; ?>
						</select>
					<?php else: ?>
						<?php printf('<span>%s</span>', number_format($item['quantity'])); ?>
					<?php endif; ?>
				</td>
				<td class="misc"><?php do_action('lwp_cart_row_desc', '', $id, $item['unit_price'], $item['quantity']); ?></td>
				<td class="price">
					<?php echo number_format_i18n($item['sub_total'])." ".lwp_currency_code(); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
		<?php
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