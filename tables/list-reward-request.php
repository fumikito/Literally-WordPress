<?php
/**
 * Description of LWP_List_Request
 *
 * @since 0.9
 */
class LWP_List_Reward_Request extends WP_List_Table{
	
	var $user_id = 0;
	
	function __construct($user_id = 0) {
		$this->user_id = $user_id;
		parent::__construct(array(
			'singular' => 'request',
			'plural' => 'requests',
			'ajax' => false
		));
	}
	
	function no_items(){
		global $lwp;
		$lwp->e('No matching history is found.');
	}
	
	
	/**
	 * 
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb 
	 * @return array
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
		$per_page = $this->get_perpage();
		$page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset = ($page - 1) * $per_page;
		$this->start = $offset;
		//Process Action
		if(isset($_GET['reward'], $_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'bulk-requests') && is_array($_GET['reward']) && false !== array_search($this->current_action(), LWP_Payment_Status::get_all_status())){
			foreach($_GET['reward'] as $reward_id){
				$wpdb->update(
					$lwp->reward_logs,
					array(
						'status' => $this->current_action(),
						'updated' => date_i18n('Y-m-d H:i:s')
					),
					array('ID' => $reward_id),
					array('%s', '%s'),
					array("%d")
				);
			}
		}
		//Create SQL
		$sql = <<<EOS
			SELECT SQL_CALC_FOUND_ROWS
				r.*, u.display_name
			FROM {$lwp->reward_logs} AS r
			LEFT JOIN {$wpdb->users} AS u
			ON r.user_id = u.ID
EOS;
		//WHERE
		$where = array();
		//User
		if($this->user_id){
			$where[] = $wpdb->prepare("(r.user_id = %d)", $this->user_id);
		}
		//status
		if(isset($_GET['status']) && $_GET['status'] != 'all'){
			switch($_GET['status']){
				case LWP_Payment_Status::CANCEL:
				case LWP_Payment_Status::REFUND:
				case LWP_Payment_Status::START:
				case LWP_Payment_Status::SUCCESS:
					$where[] = $wpdb->prepare("(r.status = %s)", $_GET['status']);
					break;
			}
		}
		if(!empty($where)){
			$sql .= ' WHERE '.implode(' AND ', $where);
		}
		//ORDER
		$order_by = 'r.registered';
		if(isset($_GET['order_by'])){
			switch($_GET['order_by']){
				case 'updated':
				case 'registered':
				case 'price':
					$order_by = 'r.'.$_GET['order_by'];
					break;
			}
		}
		$order = (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'ASC' : 'DESC';
		$sql .= " ORDER BY {$order_by} {$order}";
		$sql .= " LIMIT {$offset}, {$per_page}";
		$this->items = $wpdb->get_results($sql);
		$this->set_pagination_args(array(
			'total_items' => (int) $wpdb->get_var('SELECT FOUND_ROWS()'),
			'per_page' => $per_page
		));
	}
	
	
	function get_columns(){
		global $lwp;
		$column = array(
			'cb' => '<input type="checkbox" />',
			'payday' => $lwp->_('Pay Day'),
			'user' => $lwp->_('User name'),
			'contact' => $lwp->_('Contact'),
			'price' => $lwp->_('Price'),
			'registered' => $lwp->_('Registered'),
			'updated' => $lwp->_('Updated'),
			'status' => $lwp->_('Status')
		);
		if($this->user_id){
			unset($column['user']);
			unset($column['contact']);
		}
		return $column;
	}
	
	function get_sortable_columns() {
		return array(
			'registered' => array('registered', false),
			'updated' => array('updated', false),
			'price' => array('price', false)
		);
	}

	function get_bulk_actions() {
		global $lwp;
		return array(
			LWP_Payment_Status::START => $lwp->_(LWP_Payment_Status::START),
			LWP_Payment_Status::SUCCESS => $lwp->_(LWP_Payment_Status::SUCCESS),
			LWP_Payment_Status::CANCEL => $lwp->_(LWP_Payment_Status::CANCEL),
			LWP_Payment_Status::REFUND => $lwp->_(LWP_Payment_Status::REFUND)
		);
	}
	
	function column_cb($item){
		return sprintf('<input type="checkbox" name="%s[]" value="%d" />', 'reward', $item->ID);
	}
	
	function column_default($item, $column_name){
		global $lwp;
		switch($column_name){
			case 'payday':
				return $lwp->reward->next_pay_day(null, $item->registered);
				break;
			case 'user' :
				return $item->display_name ? '<a href="'.admin_url('user-edit.php?user_id='.$item->user_id).'">'.$item->display_name.'</a>' : $lwp->_('Deleted User');
				break;
			case 'contact':
				$contact = $lwp->reward->get_user_contact($item->user_id);
				return !empty($contact) ? nl2br(esc_html($contact)) : '-';
				break;
			case 'price':
				return number_format($item->price).' ('.lwp_currency_code().')';
				break;
			case 'registered':
				return mysql2date(get_option('date_format'), $item->registered);
				break;
			case 'updated':
				return mysql2date(get_option('date_format'), $item->updated);
				break;
			case 'status':
				return $lwp->_($item->status);
				break;
			case 'action':
				return '<a class="button" href="'.admin_url('admin.php?page=lwp-management&transaction_id='.$item->transaction_id).'">'.$lwp->_('Edit').'</a>';
				break;
		}
	}
	
	function extra_tablenav($which) {
		global $lwp;
		switch($which){
			case 'top': ?>
		<div class="alignleft actions">
			<select name="status">
				<option value="all"<?php if(!isset($_GET['status']) || $_GET['status'] == 'all') echo ' selected="selected"';?>><?php $lwp->e('All status'); ?></option>
				<?php foreach(LWP_Payment_Status::get_all_status() as $status): ?>
					<option value="<?php echo $status;?>"<?php if(isset($_GET['status']) && $_GET['status'] == $status) echo ' selected="selected"';?>>
						<?php $lwp->e($status); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php submit_button(__('Filter'), 'secondary', '', false); ?>
		</div>
			<?php break;
		}
	}
	
	function get_perpage(){
		$per_page = 20;
		if(isset($_GET['per_page']) && $_GET['per_page'] != 20){
			$per_page = max($per_page, absint($_GET['per_page']));
		}
		return $per_page;
	}
}