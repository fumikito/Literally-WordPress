<?php
/**
 * Output Table for Transfer
 *
 * @author Takahshi Fumiki
 * @package literally_wordpress
 */
class LWP_List_Transfer extends WP_List_Table{
	
	function __construct() {
		parent::__construct(array(
			'singular' => 'transfer',
			'plural' => 'transfers',
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
		
		//If action is specified, do it.
		if($this->current_action() && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], "bulk-{$this->_args['plural']}")){
			if(isset($_GET['transfer']) && is_array($_GET['transfer'])){
				$transfers = $_GET['transfer'];
				switch($this->current_action()){
					case LWP_Payment_Status::SUCCESS:
					case LWP_Payment_Status::CANCEL:
					case LWP_Payment_Status::START:
					case LWP_Payment_Status::REFUND:
						//Update Status
						foreach($transfers as $transaction_id){
							$to_update = array(
								'updated' => gmdate('Y-m-d H:i:s'),
								'status' => $this->current_action()
							);
							$where = array('%s', '%s');
							if($this->current_action() == LWP_Payment_Status::SUCCESS){
								$to_update['expires'] = lwp_expires_date((int)$wpdb->get_var($wpdb->prepare("SELECT book_id FROM {$lwp->transaction} WHERE ID = %d", $transaction_id)));
								$where[] = '%s';
							}
							$wpdb->update(
								$lwp->transaction,
								$to_update,
								array(
									'ID' => $transaction_id,
									'method' => LWP_Payment_Methods::TRANSFER
								),
								$where,
								array('%d', "%s")
							);
							if($this->current_action() == LWP_Payment_Status::SUCCESS){
								$lwp->notifier->notify($wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->transaction} WHERE ID = %d", $transaction_id)), 'confirmed');
								//Do action hook
								do_action('lwp_update_transaction', $transaction_id);
							}
						}
						$lwp->message[] = $lwp->_('Status updated.');
						break;
				}
			}else{
				$lwp->error = true;
				$lwp->message[] = $lwp->_('No transaction is specified.');
			}
		}
		
		
		//Set up paging offset
		$per_page = $this->get_per_page();
		$page = $this->get_pagenum(); 
		$offset = ($page - 1) * $per_page;
		
		//$wpdb->show_errors();
		
		//Create Basic SQL
		$sql = <<<EOS
			SELECT SQL_CALC_FOUND_ROWS
				t.*, u.display_name, u.user_email, p.post_title
			FROM {$lwp->transaction} AS t
			LEFT JOIN {$wpdb->users} AS u
			ON t.user_id = u.ID
			LEFT JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
EOS;
		//Create Where section
		$wheres = array("t.method = '".LWP_Payment_Methods::TRANSFER."'");
		$filter = $this->get_filter();
		if($filter != 'all'){
			$wheres[] = $wpdb->prepare("t.status = %s", $filter);
		}
		if(isset($_GET['s']) && !empty($_GET['s'])){
			$like_string = preg_replace("/^'(.+)'$/", '$1', $wpdb->prepare("%s", $_GET['s']));
			$wheres[] = <<<EOS
				((p.post_title LIKE '%{$like_string}%')
					OR
				 (u.display_name LIKE '%{$like_string}%')
					OR
				 (t.transaction_key LIKE '%{$like_string}%'))
EOS;
		}
		$sql .= ' WHERE '.implode(' AND ', $wheres);
		$order_by = 't.registered';
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
			'cb' => '<input type="checkbox" />',
			'user' => $lwp->_("User Name"),
			'item_name' => $lwp->_("Item Name"),
			'price' => $lwp->_("Price"),
			'notification' => $lwp->_("Notification Code"),
			'registered' => $lwp->_("Registered"),
			'updated' => $lwp->_("Updated"),
			'status' => $lwp->_("Status"),
			'notice' => $lwp->_('Notice')
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
			'price' => array('price', false)
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
			case 'user':
				return '<a href="'.admin_url('user_edit.php?user_id='.intval($item->user_id)).'">'.$item->display_name.'</a>';
				break;
			case 'item_name':
				return '<a href="'.admin_url('post.php?post='.intval($item->book_id)."&action=edit").'">'.$item->post_title.'</a>';
				break;
			case 'price':
				return number_format($item->price)." ({$lwp->option['currency_code']})";
				break;
			case 'notification':
				return $item->transaction_key;
				break;
			case 'registered':
				return mysql2date(get_option('date_format'), $item->registered, false);
				break;
			case 'updated':
				return mysql2date(get_option('date_format'), $item->updated, false);
				break;
			case 'status':
				return $lwp->_($item->status);
				break;
			case 'notice';
				switch($item->status){
					case LWP_Payment_Status::CANCEL:
					case LWP_Payment_Status::REFUND:
					case LWP_Payment_Status::SUCCESS:
						return '--';
						break;
					case LWP_Payment_Status::REFUND_REQUESTING:
						$condition = $lwp->event->get_current_cancel_condition($lwp->event->get_event_from_ticket_id($item->book_id), strtotime($item->updated));
						$ratio = $condition ? $condition['ratio'] : 0;
						return $lwp->_('Refund :')." ".number_format_i18n($item->price * $ratio / 100)." ".lwp_currency_code().' <small>('.$ratio.'%)</small>';
						break;
					case LWP_Payment_Status::START:
						if($lwp->option['notification_limit'] < 1){
							return '--';;
						}else{
							$past = ceil((time() - strtotime($item->updated)) / 60 / 60 / 24);
							$diff = $lwp->option['notification_limit'] - $past;
							if($diff > 0){
								return sprintf($lwp->_('%d days left'), $diff);
							}else{
								return sprintf("<strong>%s</strong>", $lwp->_('Expired'));
							}
						}
						break;
				}
				break;
		}
	}
	
	/**
	 * Returns check box
	 * @param Object $item
	 * @return string
	 */
	function column_cb($item){
		return sprintf('<input type="checkbox" name="%s[]" value="%d" />', $this->_args['singular'], $item->ID);
	}
	
	/**
	 * Get current page
	 * @return int
	 */
	function get_pagenum() {
		return isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
	}
	
	/**
	 * Get current filter
	 * @return string
	 */
	function get_filter(){
		$filter = 'all';
		if(isset($_GET['status']) && !$_GET['status'] != 'all'){
			$target = $_GET['status'];
		}elseif(isset($_GET['status2']) && !$_GET['status2'] != 'all'){
			$target = $_GET['status2'];
		}else{
			$target = '';
		}
		switch($target){
			case LWP_Payment_Status::CANCEL:
			case LWP_Payment_Status::REFUND:
			case LWP_Payment_Status::START:
			case LWP_Payment_Status::SUCCESS:
			case LWP_Payment_Status::REFUND_REQUESTING:
				return $filter = $target;
				break;
		}
		return $filter;
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
	
	/**
	 * Set Bulk Action
	 * @global Literally_WordPress $lwp
	 * @return array
	 */
	function get_bulk_actions() {
		global $lwp;
		return array(
			LWP_Payment_Status::SUCCESS => $lwp->_(LWP_Payment_Status::SUCCESS),
			LWP_Payment_Status::CANCEL => $lwp->_(LWP_Payment_Status::CANCEL),
			LWP_Payment_Status::START => $lwp->_(LWP_Payment_Status::START),
			LWP_Payment_Status::REFUND	 => $lwp->_(LWP_Payment_Status::REFUND)
		);
	}
	
	function extra_tablenav($which) {
		global $lwp;
		$nombre = ($which == 'top') ?  '' : "2";
		?>
		<div class="alignleft acitions">
			<select name="status<?php echo $nombre; ?>">
				<?php
				$status = array(
					'all' => $lwp->_('All Status'),
					LWP_Payment_Status::START => $lwp->_(LWP_Payment_Status::START),
					LWP_Payment_Status::CANCEL => $lwp->_(LWP_Payment_Status::CANCEL),
					LWP_Payment_Status::SUCCESS => $lwp->_(LWP_Payment_Status::SUCCESS),
					LWP_Payment_Status::REFUND => $lwp->_(LWP_Payment_Status::REFUND),
					LWP_Payment_Status::REFUND_REQUESTING => $lwp->_(LWP_Payment_Status::REFUND_REQUESTING)
				);
				foreach($status as $s => $val): ?>
				<option value="<?php echo $s; if($s == $this->get_filter()) echo '" selected="selected'?>"><?php echo $val; ?></option>
				<?php endforeach; ?>
			</select>
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