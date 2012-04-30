<?php
/**
 * Create list table for reward history
 *
 * @since 0.9
 */
class LWP_List_Reward_History extends WP_List_Table{
	
	var $user_id = 0;
	
	var $start = 0;
	
	function __construct($user_id = 0){
		$this->user_id = $user_id;
		parent::__construct(array(
			'singular' => 'history',
			'plural' => 'histories',
			'ajax' => false
		));
	}
	
	function no_items(){
		global $lwp;
		$lwp->e('No matching history is found.');
	}
	
	function get_columns(){
		global $lwp;
		$column = array(
			'user' => $lwp->_('User name'),
			'price' => $lwp->_('Price'),
			'reward' => $lwp->_('Reward'),
			'margin' => $lwp->_('Margin'),
			'registered' => $lwp->_('Registered'),
			'updated' => $lwp->_('Updated'),
			'status' => $lwp->_('Status'),
			'reason' => $lwp->_('Reason'),
			'action' => $lwp->_('Action')
		);
		if($this->user_id){
			unset($column['user']);
		}
		return $column;
	}
	
	function get_sortable_columns() {
		return array(
			'registered' => array('registered', false),
			'updated' => array('updated', false),
			'price' => array('price', false),
			'reward' => array('reward', false),
			'margin' => array('margin', false)
		);
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
		//Create SQL
		$sql = <<<EOS
			SELECT SQL_CALC_FOUND_ROWS
				*
			FROM {$lwp->promotion_logs} AS p
			INNER JOIN {$lwp->transaction} AS t
			ON p.transaction_id = t.ID
			LEFT JOIN {$wpdb->users} AS u
			ON p.user_id = u.ID
EOS;
		//WHERE
		$where = array();
		//User
		if($this->user_id){
			$where[] = $wpdb->prepare("(p.user_id = %d)", $this->user_id);
		}
		//Search query
		if(isset($_GET['s']) && !empty($_GET['s'])){
			$where[] = $wpdb->prepare("(u.display_name LIKE %s)", '%'.(string)$_GET['s'].'%');
		}
		//status
		if(isset($_GET['status']) && $_GET['status'] != 'all'){
			switch($_GET['status']){
				case LWP_Payment_Status::CANCEL:
				case LWP_Payment_Status::REFUND:
				case LWP_Payment_Status::START:
				case LWP_Payment_Status::SUCCESS:
					$where[] = $wpdb->prepare("(t.status = %s)", $_GET['status']);
					break;
			}
		}
		//Type
		if(isset($_GET['reason']) && $_GET['reason'] != "all"){
			switch($_GET['reason']){
				case LWP_Promotion_TYPE::PROMOTION:
				case LWP_Promotion_TYPE::SELL:
					$where[] = $wpdb->prepare("(p.reason = %s)", $_GET['reason']);
					break;
			}
		}
		if(!empty($where)){
			$sql .= ' WHERE '.implode(' AND ', $where);
		}
		//ORDER
		$order_by = 't.registered';
		if(isset($_GET['order_by'])){
			switch($_GET['order_by']){
				case 'updated':
				case 'registered':
				case 'price':
					$order_by = 't.'.$_GET['order_by'];
					break;
				case 'reward':
					$order_by = 'p.estimated_reward';
					break;
				case 'margin':
					$order_by = '(p.estimated_reward / t.price)';
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
	
	function column_default($item, $column_name){
		global $lwp;
		switch($column_name){
			case 'user' :
				return $item->display_name ? '<a href="'.admin_url('user-edit.php?user_id='.$item->user_id).'">'.$item->display_name.'</a>' : $lwp->_('Deleted User');
				break;
			case 'price':
				return number_format($item->price);
				break;
			case 'reward':
				return number_format($item->estimated_reward);
				break;
			case 'margin':
				return  intval($item->estimated_reward / $item->price * 100).'%';
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
			case 'reason':
				return $lwp->_($item->reason);
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
			<select name="reason">
				<option value="all"<?php if(!isset($_GET['reason']) || $_GET['reason'] == 'all') echo ' selected="selected"';?>><?php $lwp->e('All reason'); ?></option>
				<option value="<?php echo LWP_Promotion_TYPE::PROMOTION;?>"<?php if(isset($_GET['reason']) || $_GET['reason'] == LWP_Promotion_TYPE::PROMOTION) echo ' selected="selected"';?>><?php $lwp->e(LWP_Promotion_TYPE::PROMOTION); ?></option>
				<option value="<?php echo LWP_Promotion_TYPE::SELL;?>"<?php if(isset($_GET['reason']) || $_GET['reason'] == LWP_Promotion_TYPE::SELL) echo ' selected="selected"';?>><?php $lwp->e(LWP_Promotion_TYPE::SELL); ?></option>
			</select>
			<select name="per_page">
				<?php foreach(array(20, 50, 100) as $num): ?>
				<option value="<?php echo $num; ?>"<?php if(isset($_GET['per_page']) && $_GET['per_page'] == $num) echo ' selected="selected"';?>>
					<?php printf($lwp->_('%d per 1Page'), $num); ?>
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