<?php
/**
 * Table of events list
 * @package literally_wordpress
 */
class LWP_List_Ticket extends WP_List_Table {
	
	function __construct() {
		parent::__construct(array(
			'singular' => 'ticket',
			'plural' => 'tickets',
			'ajax' => false
		));
	}
	
	/**
	 *
	 * @global Literally_WordPress $lwp 
	 */
	function no_items(){
		global $lwp;
		$lwp->e("No matching ticket is found.");
	}
	
	/**
	 *
	 * @global Literally_WordPress $lwp
	 * @return array 
	 */
	function get_columns() {
		global $lwp;
		$column = array(
			'ticket_name' => $lwp->_("Ticket Name"),
			'user' => $lwp->_('User Name'),
			'price' => $lwp->_('Price'),
			'status' => $lwp->_("Status"),
			'updated' => $lwp->_('Updated'),
			'number' => $lwp->_('Quantity'),
			'consumed' => $lwp->_('Consumed'),
			'actions' => $lwp->_('Actions')
		);
		return $column;
	}
	
	/**
	 * 
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb 
	 */
	function prepare_items() {
		global $lwp, $wpdb;
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
				t.*, p.post_title, u.display_name
			FROM {$lwp->transaction} AS t
			INNER JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
			INNER JOIN {$wpdb->users} AS u
			ON t.user_id = u.ID
EOS;
		//WHERE
		$where = array(
			$wpdb->prepare('p.post_type = %s', $lwp->event->post_type),
			$wpdb->prepare('p.post_parent = %d', $_GET['event_id'])
		);
		if(isset($_GET['s']) && !empty($_GET["s"])){
			$like_string = preg_replace("/^'(.+)'$/", '$1', $wpdb->prepare("%s", $_GET['s']));
			$where[] = <<<EOS
				((p.post_title LIKE '%{$like_string}%')
					OR
				 (t.transaction_key LIKE '%{$like_string}%')
					OR
				 (u.user_login LIKE '%{$like_string}%')
					OR
				 (u.display_name LIKE '%{$like_string}%')
					OR
				 (u.user_email LIKE '%{$like_string}%')
				)
EOS;
		}
		if(isset($_GET['ticket']) && $_GET['ticket'] != 'all'){
			$where[] = $wpdb->prepare("(t.book_id = %d)", $_GET['ticket']);
		}
		if(isset($_GET['status']) && $_GET['status'] != 'all'){
			$where[] = $wpdb->prepare("(t.status = %s)", $_GET['status']);
		}
		$sql .= ' WHERE '.implode(' AND ', $where);
		//ORDER
		$order_by = 't.updated';
		if(isset($_GET['orderby'])){
			switch($_GET['orderby']){
				
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
		global $lwp;
		switch($column_name){
			case 'ticket_name':
				return $item->post_title;
				break;
			case 'user':
				if($item->display_name){
					return sprintf('<a href="%2$s">%1$s</a> <code><a href="%3$s">%4$s</a></code>',
									$item->display_name, admin_url('user-edit.php?user_id='.$item->user_id),
									admin_url('admin.php?page=lwp-management&view=list&user_id='.$item->user_id), $lwp->_('Transactions'));
				}else{
					return $lwp->_('Deleted User');
				}
				break;
			case 'updated':
				return mysql2date(get_option('date_format').' H:i', get_date_from_gmt($item->updated));
				break;
			case 'status':
				switch($item->status){
					case LWP_Payment_Status::SUCCESS:
						$placeholder = '<strong style="color:green">%s</strong>';
						break;
					case LWP_Payment_Status::CANCEL:
					case LWP_Payment_Status::REFUND:
						$placeholder = '<strong style="color:#999999">%s</strong>';
						break;
					case LWP_Payment_Status::REFUND_REQUESTING:
						$placeholder = '<strong style="color:#f00">%s</strong>';
						break;
					default:
						$placeholder = '%s';
						break;
				}
				return sprintf($placeholder, $lwp->_($item->status));
				break;
			case 'number':
				return number_format_i18n($item->num);
				break;
			case 'consumed':
				return number_format_i18n($item->consumed);
				break;
			case 'price':
				return number_format_i18n($item->num * $item->price).' '.  lwp_currency_code();
				break;
			case 'actions':
				return '<a class="button" href="'.admin_url('admin.php?page=lwp-management&transaction_id='.$item->ID).'">'.$lwp->_('Transaction').'</a>';
				break;
		}
	}
	
	/**
	 * @global Literally_WordPress $lwp
	 * @return array
	 */
	function get_sortable_columns() {
		return array(
			'updated' => array('updated', false)
		);
	}
	
	
	/**
	 * Get current page
	 * @return int
	 */
	function get_pagenum() {
		return isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
	}
	
	
	function get_status(){
		global $lwp;
		$filter = 'all';
		if(isset($_GET['status']) && !$_GET['status'] != 'all'){
			$filter = (string)$_GET['status'];
		}
		return $filter;
	}
	
	function get_ticket(){
		$filter = 'all';
		if(isset($_GET['ticket']) && !$_GET['ticket'] != 'all'){
			$filter = intval($_GET['ticket']);
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
		global $lwp, $wpdb;
		if($which == 'top'):
		?>
		<div class="alignleft acitions">
			<select name="status">
				<?php
				$status = array('all' => $lwp->_('All Status'));
				foreach(LWP_Payment_Status::get_all_status() as $s){
					$status[$s] = $lwp->_($s);
				}
				foreach($status as $key => $val): ?>
					<option value="<?php echo $key; if($key == $this->get_status()) echo '" selected="selected';?>"><?php echo $val; ?></option>
				<?php endforeach; ?>
			</select>
			<select name="ticket">
				<?php
					$option = array('all' => $lwp->_('All Tickets'));
					foreach($wpdb->get_results($wpdb->prepare("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = %s", $_GET['event_id'], $lwp->event->post_type)) as $post){
						$option[$post->ID] = $post->post_title;
					}
					foreach($option as $id => $title){
						echo '<option value="'.$id.'"'.($this->get_ticket() == $id ? ' selected="selected"' : '').">".$title.'</option>';
					}
				?>
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