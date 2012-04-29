<?php
/**
 * Create list table for reward history
 *
 * @since 0.9
 */
class LWP_List_Reward_History extends WP_List_Table{
	
	function __construct(){
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
			'registered' => $lwp->_('Registered'),
			'updated' => $lwp->_('Updated'),
			'status' => $lwp->_('Status'),
			'reason' => $lwp->_('Reason'),
			'action' => $lwp->_('Action')
		);
		return $column;
	}
	
	function get_sortable_columns() {
		global $lwp;
		return array(
			'registered' => array('registered', false),
			'updated' => array('updated', false),
			'price' => array('price', false),
			'reward' => array('reward', false)
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
		$per_page = 20;
		if(isset($_GET['per_page']) && $_GET['per_page'] != 20){
			$per_page = max($per_page, absint($_GET['per_page']));
		}
		$page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		$offset = ($page - 1) * $per_page;
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
		$where = array(
		);
		if(!empty($where)){
			$sql .= ' WHERE '.implode(' AND ', $where);
		}
		//ORDER
		$order_by = 't.registered';
		if(isset($_GET['order_by'])){
			
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
				return '<a href="'.admin_url('user-edit.php?user_id='.$item->user_id).'">'.$item->display_name.'</a>';
				break;
			case 'price':
				return number_format($item->price);
				break;
			case 'reward':
				return number_format($item->estimated_reward);
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
}