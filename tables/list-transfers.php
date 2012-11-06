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
		$wheres = array(
			"t.method = '".LWP_Payment_Methods::TRANSFER."'",
			$wpdb->prepare("t.status = %s", LWP_Payment_Status::START)
		);
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
			'updated' => $lwp->_("Payment Limit"),
			'left_days' => $lwp->_('Left Days'),
			'status' => $lwp->_("Status"),
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
				return '<a href="'.admin_url('user-edit.php?user_id='.intval($item->user_id)).'">'.$item->display_name.'</a>';
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
				$date = $lwp->notifier->get_limit_date($item->registered, get_option('date_format'));
				return $date;
				break;
			case 'left_days':
				$left = $lwp->notifier->get_left_days($item->registered);
				if($left > 10){
					$color = 'black';
					$tag = 'span';
				}elseif($left > 5){
					$color = 'balck';
					$tag = 'strong';
				}elseif($left >= 2){
					$color = 'orange';
					$tag = 'strong';
				}else{
					$color = 'crimson';
					$tag = 'strong';
				}
				return sprintf('<%1$s style="color: %3$s;">%2$s</%1$s>', $tag, sprintf($lwp->_('%d days left'), $left), $color); 
				break;
			case 'status':
				return ($item->status == LWP_Payment_Status::START)
					? $lwp->_('Waiting for Payment')
					: $lwp->_($item->status);
				break;
			case 'action':
				return sprintf('<a href="%s" class="button">%s</a>', admin_url('admin.php?page=lwp-management&transaction_id='.$item->ID), $lwp->_('Detail'));
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
	
	function get_table_classes() {
		return array( 'widefat', $this->_args['plural'] );
	}
	
	function extra_tablenav($which) {
		global $lwp;
		$nombre = ($which == 'top') ?  '' : "2";
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