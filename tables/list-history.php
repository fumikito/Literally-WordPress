<?php

if(!function_exists('get_current_screen')){
	function get_current_screen(){
		
	}
}

/**
 * Controle user purchase history
 * @package literally_wordpress
 */
class LWP_List_History extends WP_List_Table {
	
	function __construct() {
		parent::__construct(array(
			'singular' => 'history',
			'plural' => 'histories',
			'ajax' => false
		));
	}
	
	/**
	 *
	 * @global Literally_WordPress $lwp 
	 */
	function no_items(){
		global $lwp;
		$lwp->e("No matching history is found.");
	}
	
	/**
	 *
	 * @global Literally_WordPress $lwp
	 * @return array 
	 */
	function get_columns() {
		global $lwp;
		$column = array(
			'item_type' => $lwp->_('Item Type'),
			'item_name' => $lwp->_("Item Name"),
			'price' => $lwp->_("Purchased Price"),
			'method' => $lwp->_('Method'),
			'expires' => $lwp->_('Expires'),
			'registered' => $lwp->_("Registered"),
		);
		if(is_admin()){
			$column['updated'] = $lwp->_("Last Updated");
		}
		return $column;
	}
	
	/**
	 *
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb 
	 */
	function prepare_items() {
		global $lwp, $wpdb, $user_ID;
		//Set column header
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns()
		);
		
		//Set up paging offset
		$per_page = $this->get_per_page();
		$page = $this->get_pagenum(); 
		$offset = ($page - 1) * $per_page;
		
		$sql = <<<EOS
			SELECT SQL_CALC_FOUND_ROWS 
				t.*, p.post_title, p.post_type, pm.meta_value AS price
			FROM {$lwp->transaction} AS t
			INNER JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
			INNER JOIN {$wpdb->postmeta} AS pm
			ON p.ID = pm.post_id AND pm.meta_key = 'lwp_price'
EOS;
		//WHERE
		$where = array(
			$wpdb->prepare('t.user_id = %d', $user_ID),
			$wpdb->prepare('t.status = %s', LWP_Payment_Status::SUCCESS)
		);
		if($this->get_post_type() != 'all'){
			$where[] = $wpdb->prepare("p.post_type = %s", $this->get_post_type());
		}
		if(isset($_GET['s']) && !empty($_GET["s"])){
			$where[] = $wpdb->prepare("((p.post_title LIKE %s) OR (p.post_content LIKE %s) OR (p.post_excerpt LIKE %s))", '%'.$_GET["s"].'%', '%'.$_GET["s"].'%', '%'.$_GET["s"].'%');
		}
		$sql .= ' WHERE '.implode(' AND ', $where);
		//ORDER
		$order_by = 't.registered';
		if(isset($_GET['order_by'])){
			switch($_GET['order_by']){
				case 'updated':
				case 'registered':
				case 'expires':
				case 'price':
					$order_by = 't.'.(string)$_GET['order_by'];
					break;
			}
		}
		$order = (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'ASC' : 'DESC';
		$sql .= " ORDER BY {$order_by} {$order}";
		$sql .= " LIMIT {$offset}, {$per_page}";
		$this->items = $wpdb->get_results($sql);
			
		$this->set_pagination_args(array(
			'total_items' => (int) $wpdb->get_var("SELECT FOUND_ROWS()"),
			'per_page' => $per_page
		));
	}
	
	/**
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param Object $item
	 * @param string $column_name
	 * @return string
	 */
	function column_default($item, $column_name){
		global $lwp, $wpdb;
		switch($column_name){
			case 'item_type':
				if($item->post_type == $lwp->subscription->post_type){
					return $lwp->_('Subscription');
				}else{
					return get_post_type_object($item->post_type)->labels->name;
				}
				break;
			case 'item_name':
				if($item->post_type == $lwp->subscription->post_type){
					$url = $lwp->subscription->get_subscription_archive();
				}elseif($item->post_type == $lwp->event->post_type){
					$url = lwp_ticket_url($wpdb->get_var($wpdb->prepare("SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d", $item->book_id)));
				}else{
					$url = get_permalink($item->book_id);
				}
				return '<a href="'.$url.'">'.$item->post_title.'</a>';
				break;
			case 'price':
				return number_format($item->price)." ({$lwp->option['currency_code']})";
				break;
			case 'method':
				return $lwp->_($item->method);
				break;
			case 'expires':
				if($item->expires == '0000-00-00 00:00:00'){
					return $lwp->_('No Limit');
				}else{
					$remain = ceil((strtotime($item->expires) - time()) / 60 / 60 / 24);
					$string;
					if($remain < 0){
						$string = $lwp->_('Expired');
					}else{
						$string = sprintf($lwp->_('%d days left'), $remain);
					}
					return $string.'<br /><small>'.mysql2date(get_option('date_fomrat'), $item->expires).'</small>';
				}
				break;
			case 'registered':
				return mysql2date(get_option('date_format'), $item->registered, false);
				break;
			case 'updated':
				return mysql2date(get_option('date_format'), $item->updated, false);
				break;
		}
	}
	
	/**
	 * @global Literally_WordPress $lwp
	 * @return array
	 */
	function get_sortable_columns() {
		global $lwp;
		return array(
			'registered' => array('registered', false),
			'updated' => array('updated', false),
			'price' => array('price', false),
			'expires' => array('expires', false)
		);
	}
	
	
	/**
	 * Get current page
	 * @return int
	 */
	function get_pagenum() {
		return isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
	}
	
	
	function get_post_type(){
		global $lwp;
		$filter = 'all';
		if(isset($_GET['post_types']) && !$_GET['post_types'] != 'all'){
			$filter = (string)$_GET['post_types'];
		}
		return $filter;
	}
	
	/**
	 *
	 * @return int
	 */
	function get_per_page(){
		$per_page = 20;
		if(isset($_GET['per_page']) && $_GET['per_page'] != 20){
			$per_page = max($per_page, absint($_GET['per_page']));
		}
		return $per_page;
	}
	
	
	function extra_tablenav($which) {
		global $lwp;
		if($which == 'top'):
		?>
		<div class="alignleft acitions">
			<select name="post_types">
				<?php
				$post_types = array('all' => $lwp->_('All Post Types'));
				foreach($lwp->option['payable_post_types'] as $p){
					$object = get_post_types(array('name' => $p), 'objects');
					foreach($object as $post_type){
						$post_types[$p] = $post_type->labels->name;
					}
				}
				foreach($post_types as $post_type => $label): ?>
				<option value="<?php echo $post_type; if($post_type == $this->get_post_type()) echo '" selected="selected'?>"><?php echo $label; ?></option>
				<?php endforeach; ?>
				<?php if($lwp->subscription->is_enabled()): ?>
				<option value="<?php echo $lwp->subscription->post_type; if($lwp->subscription->post_type == $this->get_post_type()) echo '" selected="selected'?>"><?php $lwp->e('Subscription'); ?></option>
				<?php endif; ?>
			</select>
			<select name="per_page">
				<?php foreach(array(20, 50, 100) as $num): ?>
				<option value="<?php echo $num; ?>"<?php if($this->get_per_page() == $num) echo ' selected="selected"';?>>
					<?php printf($lwp->_('%d per 1Page'), $num); ?>
				</option>
				<?php endforeach; ?>
			</select>
			
			<input type="submit" class="button-secondary" value="<?php _e('Filter'); ?>" />
		</div>
		<?php
		endif;
	}
	
	function get_table_classes() {
		return array_merge(parent::get_table_classes(), array('lwp-table'));
	}
}