<?php
/**
 * Output Table for Transfer
 *
 * @author Takahshi Fumiki
 * @package literally_wordpress
 */
class LWP_List_Refund extends WP_List_Table{
	
	function __construct() {
		parent::__construct(array(
			'singular' => 'refund',
			'plural' => 'refunds',
			'ajax' => false
		));
	}
	
	/**
	 * Set up items
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
		
		//Do action
		
		//Set up paging offset
		$per_page = $this->get_per_page();
		$page = $this->get_pagenum(); 
		$offset = ($page - 1) * $per_page;
		
		//$wpdb->show_errors();
		
		//Create Basic SQL
		$sql = <<<EOS
			SELECT SQL_CALC_FOUND_ROWS
				t.*, u.display_name, u.user_email,
				p.post_title, p.post_type, p.post_parent
			FROM {$lwp->transaction} AS t
			LEFT JOIN {$wpdb->users} AS u
			ON t.user_id = u.ID
			LEFT JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
EOS;
		//Create Where section
		$wheres = array(
			$wpdb->prepare("t.status = %s", ((isset($_GET['status']) && $_GET['status'] == LWP_Payment_Status::REFUND) ? LWP_Payment_Status::REFUND : LWP_Payment_Status::REFUND_REQUESTING))
		);
		if(isset($_GET['s']) && !empty($_GET['s'])){
			$like_string = preg_replace("/^'(.+)'$/", '$1', $wpdb->prepare("%s", $_GET['s']));
			$wheres[] = <<<EOS
				((p.post_title LIKE '%{$like_string}%')
					OR
				 (u.display_name LIKE '%{$like_string}%')
					OR
				 (u.user_email LIKE '%{$like_string}%')
					OR
				 (u.user_login LIKE '%{$like_string}%')
					OR
				 (t.transaction_key LIKE '%{$like_string}%'))
EOS;
		}
		$sql .= ' WHERE '.implode(' AND ', $wheres);
		$order_by = 't.updated';
		if(isset($_GET['orderby'])){
			switch($_GET['orderby']){
				case 'updated':
				case 'registered':
				case 'price':
					$order_by = 't.'.(string)$_GET['orderby'];
					break;
			}
		}
		$order = (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'ASC' : 'DESC';
		$sql .= <<<EOS
			ORDER BY {$order_by} {$order}
			LIMIT {$offset}, {$per_page}
EOS;
		$this->items = $wpdb->get_results($sql);
	
		$this->set_pagination_args(array(
			'total_items' => (int) $wpdb->get_var("SELECT FOUND_ROWS()"),
			'per_page' => $per_page
		));
	}
	
	/**
	 * Returns string if nothing found
	 * @global Literally_WordPress $lwp
	 * @return string
	 */
	function no_items(){
		global $lwp;
		$lwp->e('No matching transaction found.');
	}
	
	/**
	 * Returns name of columns
	 * @global Literally_WordPress $lwp
	 * @return array
	 */
	function get_columns() {
		global $lwp;
		return array(
			'item_name' => $lwp->_("Item Name"),
			'user' => $lwp->_("User Name"),
			'account' => $lwp->_('Account'),
			'paid' => $lwp->_("Paid"),
			'refunds' => $lwp->_("Refunds"),
			'registered' => $lwp->_("Registered"),
			'updated' => $lwp->_("Updated"),
			'action' => $lwp->_('Action')
		);
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
			'paid' => array('paid', false)
		);
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
			case 'item_name':
				$item_name = $lwp->refund_manager->get_item_name($item->book_id);
				switch($item->post_type){
					case $lwp->event->post_type:
						$url = admin_url('post.php?post='.intval($item->post_parent)."&action=edit");
						break;
					default:
						$url = admin_url('post.php?post='.intval($item->book_id)."&action=edit");
						break;
				}
				return sprintf('<a href="%s">%s</a> <strong> - %s</strong>', $url, $item_name, get_post_type_object($item->post_type)->labels->name);
				break;
			case 'user':
				if($item->display_name){
						return sprintf('<a href="%2$s">%1$s</a> <code><a href="%3$s">%4$s</a></code><br /><a href="mailto:%5$s">%5$s</a>',
										$item->display_name, admin_url('user-edit.php?user_id='.$item->user_id),
										admin_url('admin.php?page=lwp-management&view=list&user_id='.$item->user_id), $lwp->_('Transactions'),
										$item->user_email);
				}else{
					return $lwp->_('Deleted User');
				}
				break;
			case 'account':
				$account = $lwp->refund_manager->get_user_account($item->user_id);
				return $account ? $account : '---';
				break;
			case 'paid':
				return sprintf('%1$s<br /><strong>%2$s</strong>', number_format($item->price)." ({$lwp->option['currency_code']})", $lwp->_($item->method));
				break;
			case 'refunds':
				$refund = $lwp->refund_manager->detect_refund_price($item);
				if($lwp->refund_manager->is_suspicious_transaction($item)){
					
				}else{
					$suffix = '';
				}
				return number_format_i18n($refund).'('.lwp_currency_code().')'.$suffix;
				break;
			case 'registered':
				return mysql2date(get_option('date_format'), $item->registered, false);
				break;
			case 'updated':
				$date = $lwp->notifier->get_limit_date($item->registered, get_option('date_format'));
				$now = gmdate('Y-m-d H:i:s');
				if($item->status == LWP_Payment_Status::REFUND){
					return $date.'<br />'.sprintf($lwp->_('%s before'), human_time_diff(strtotime($item->updated), strtotime($now)));
				}else{
					$past = (strtotime($now) - strtotime($item->updated)) / 60 / 60 / 24;
					if($past > 30){
						$color = 'crimson';
						$tag = 'strong';
					}elseif($past >= 7){
						$color = 'orange';
						$tag = 'strong';
					}else{
						$color = 'black';
						$tag = 'span';
					}
					return $date.'<br />'.sprintf('<%1$s style="color: %3$s;">%2$s</%1$s>', $tag, sprintf($lwp->_('%d days past'), floor($past)), $color); 
				}
				break;
			case 'action':
				return sprintf('<a href="%s" class="button">%s</a>', admin_url('admin.php?page=lwp-management&transaction_id='.$item->ID), $lwp->_('Detail'));
				break;
		}
	}
	
	/**
	 * Get current page
	 * @return int
	 */
	function get_pagenum() {
		return isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
	}
	
	
	function get_per_page(){
		$per_page = 20;
		if(isset($_GET['per_page']) && $_GET['per_page'] != 20){
			$per_page = max($per_page, absint($_GET['per_page']));
		}elseif(isset($_GET['per_page2']) && $_GET['per_page2'] != 20){
			$per_page = max($per_page, absint($_GET['per_page2']));
		}
		return $per_page;
	}
	
	function get_table_classes() {
		return array( 'widefat', $this->_args['plural'] );
	}
	
	function extra_tablenav($which) {
		global $lwp;
		if($which != 'top'){
			return;
		}
		?>
		<div class="alignleft acitions">
			<select name="per_page<?php echo $nombre; ?>">
				<?php foreach(array(20, 50, 100) as $num): ?>
				<option value="<?php echo $num; ?>"<?php if($this->get_per_page() == $num) echo ' selected="selected"';?>>
					<?php printf($lwp->_('%d per 1Page'), $num); ?>
				</option>
				<?php endforeach; ?>
			</select>
			<?php submit_button(__('Filter'), 'secondary', '', false); ?>
		</div>
		<?php
	}
}