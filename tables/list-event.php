<?php
/**
 * Table of events list
 * @package literally_wordpress
 */
class LWP_List_Event extends WP_List_Table {
	
	function __construct() {
		parent::__construct(array(
			'singular' => 'event',
			'plural' => 'events',
			'ajax' => false
		));
	}
	
	/**
	 *
	 * @global Literally_WordPress $lwp 
	 */
	function no_items(){
		global $lwp;
		$lwp->e("No matching event is found.");
	}
	
	/**
	 *
	 * @global Literally_WordPress $lwp
	 * @return array 
	 */
	function get_columns() {
		global $lwp;
		$column = array(
			'event_name' => $lwp->_("Event Name"),
			'published' => $lwp->_('Published'),
			'selling_limit' => $lwp->_("Selling Limit"),
			'event_starts' => $lwp->_('Event Starts'),
			'tickets' => $lwp->_('Tickets'),
			'actions' => ''
		);
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
			SELECT DISTINCT SQL_CALC_FOUND_ROWS
				p.*, pm.meta_value AS limit_date, pm2.meta_value AS start_date
			FROM {$wpdb->posts} AS p
			LEFT JOIN {$wpdb->postmeta} AS pm
			ON p.ID = pm.post_id AND pm.meta_key = '{$lwp->event->meta_selling_limit}'
			LEFT JOIN {$wpdb->postmeta} AS pm2
			ON p.ID = pm2.post_id AND pm2.meta_key = '{$lwp->event->meta_start}'
			INNER JOIN {$wpdb->posts} AS c
			ON c.post_parent = p.ID AND c.post_type = '{$lwp->event->post_type}'
EOS;
		//WHERE
		$where = array();
		if($this->get_post_type() != 'all'){
			$where[] = $wpdb->prepare("p.post_type = %s", $this->get_post_type());
		}else{
			$where[] = "p.post_type IN (".implode(',', array_map(create_function('$a', 'return "\'".$a."\'";'), $lwp->event->post_types)).")";
		}
		if(isset($_GET['s']) && !empty($_GET["s"])){
			$where[] = $wpdb->prepare("((p.post_title LIKE %s) OR (p.post_content LIKE %s) OR (p.post_excerpt LIKE %s))", '%'.$_GET["s"].'%', '%'.$_GET["s"].'%', '%'.$_GET["s"].'%');
		}
		if(isset($_REQUEST['post_status']) && $_REQUEST['post_status'] != 'all'){
			$where[] = $wpdb->prepare("(p.post_status = %s)", $_REQUEST['post_status']);
		}
		$sql .= ' WHERE '.implode(' AND ', $where);
		//ORDER
		$order_by = 'CAST(pm2.meta_value AS DATE)';
		if(isset($_GET['orderby'])){
			switch($_GET['orderby']){
				case 'selling_limit':
					$order_by = 'CAST(pm.meta_value AS DATE)';
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
	 * @param Object $item
	 * @param string $column_name
	 * @return string
	 */
	function column_default($item, $column_name){
		global $lwp, $wpdb;
		switch($column_name){
			case 'event_name':
				switch($item->post_status){
					case 'future':
						$status = '<strong style="color: green;">- '.__(ucfirst($item->post_status)).'</strong>';
						break;
					case 'draft':
						$status = '<strong style="color: #999999;">- '.__(ucfirst($item->post_status)).'</strong>';
						break;
					case 'pending':
						$status = '<strong style="color: #e66f00;">- '.__(ucfirst($item->post_status)).'</strong>';
						break;
					case 'publish':
						$status = '';
						break;
					default:
						$status = '<strong>- '.__(ucfirst($item->post_status)).'</strong>';
						break;
				}
				return sprintf('<span class="description">%4$s: </span><a href="%1$s">%2$s</a>%3$s', admin_url('post.php?post='.$item->ID.'&amp;action=edit'),
											 $item->post_title, $status, get_post_type_object($item->post_type)->labels->name);
				break;
			case 'published':
				return mysql2date(get_option('date_format'), $item->post_date);
				break;
			case 'selling_limit':
				if($item->limit_date){
					$limit = $item->limit_date.' 23:59:59';
					if(strtotime($limit) < current_time('timestamp')){
						$style = ' style="color:darkgray;"';
						$time = sprintf($lwp->_('%s before'), human_time_diff(strtotime($limit)));
					}else{
						$style = '';
						$time = sprintf($lwp->_('%s later'), human_time_diff(strtotime($limit)));
					}
					return "<span{$style}>".mysql2date(get_option('date_format'), $limit)."<br /><small>".$time."</small></span>";
				}else{
					return '-';
				}
				break;
			case 'event_starts':
				if($item->start_date){
					if(strtotime($item->start_date) < current_time('timestamp')){
						$style = ' style="color:darkgray;"';
						$time = sprintf($lwp->_('%s before'), human_time_diff(strtotime($item->start_date)));
					}else{
						$style = '';
						$time = sprintf($lwp->_('%s later'), human_time_diff(strtotime($item->start_date)));
					}
					return  "<span{$style}>".mysql2date(get_option('date_format'), $item->start_date).'<br /><small>'.$time.'</small></span>';
				}else{
					return '-';
				}
				break;
			case 'tickets':
				$tickets = get_posts("post_parent={$item->ID}&post_type={$lwp->event->post_type}");
				if(empty($tickets)){
					return '-';
				}else{
					$return = '<ol class="lwp-event-table-list">';
					foreach($tickets as $ticket){
						$stock = lwp_get_ticket_stock(true, $ticket);
						$sold = lwp_get_ticket_sold($ticket);
						if($stock > 0){
							$ratio = intval(255 * ($sold / $stock));
							$style = ' style="color:rgb('.$ratio.', 0, 0);"';
						}else{
							$style = ' style="color: darkgray; "';
						}
						$waiting = lwp_waiting_user_count($ticket);
						//Apply filter
						$list = apply_filters('lwp_event_list_ticket_info', "<li{$style}>{$ticket->post_title} ({$sold}/{$stock} + {$waiting})</li>", $sold, $stock, $ticket, $item, $waiting);
						$return .= $list;
					}
					$return .= '</ol>';
					return $return;
				}
				break;
			case 'actions':
				return '<p><a class="button" href="'.admin_url('admin.php?page=lwp-event&amp;event_id='.$item->ID).'">'.$lwp->_('Detail').'</a></p>';
				break;
		}
	}
	
	/**
	 * @global Literally_WordPress $lwp
	 * @return array
	 */
	function get_sortable_columns() {
		return array(
			'published' => array('published', false),
			'selling_limit' => array('selling_limit', false)
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
				foreach($lwp->event->post_types as $p){
					$object = get_post_types(array('name' => $p), 'objects');
					foreach($object as $post_type){
						$post_types[$p] = $post_type->labels->name;
					}
				}
				foreach($post_types as $post_type => $label): ?>
					<option value="<?php echo $post_type; if($post_type == $this->get_post_type()) echo '" selected="selected'?>"><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
			<select name="post_status">
				<option value="all"<?php if(!isset($_REQUEST['post_status']) || $_REQUEST['post_status'] == 'all') echo ' selected="selected"';?>><?php $lwp->e('All Status'); ?></option>
				<?php foreach(array('publish', 'future', 'pending', 'draft', 'private') as $status):  ?>
					<option value="<?php echo $status; ?>"<?php if(isset($_REQUEST['post_status']) && $status == $_REQUEST['post_status']) echo ' selected="selected"'; ?>><?php _e(ucfirst($status)); ?></option>
				<?php endforeach; ?>
			</select>
			<select name="per_page">
				<?php foreach(array(20, 50, 100) as $num): ?>
				<option value="<?php echo $num; ?>"<?php if($this->get_per_page() == $num) echo ' selected="selected"';?>>
					<?php printf($lwp->_('%d per 1Page'), $num); ?>
				</option>
				<?php endforeach; ?>
			</select>
			
			<?php submit_button(__('Filter'), 'secondary', '', false); ?>
		</div>
		<?php
		endif;
	}
	
	function get_table_classes() {
		return array( 'widefat', 'lwp-table', $this->_args['plural'] );
	}
}