<?php
/**
 * Controle Management Table
 * @package literally_wordpress
 */
class LWP_List_Management extends WP_List_Table{
	
	function __construct() {
		parent::__construct(array(
			'singular' => 'transaction',
			'plural' => 'transactions',
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
		
		//Set up paging offset
		$per_page = $this->get_per_page();
		$page = $this->get_pagenum(); 
		$offset = ($page - 1) * $per_page;
		
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
		$wheres = array();
		$filter = $this->get_filter();
		if($filter != 'all'){
			$wheres[] = $wpdb->prepare("t.status = %s", $filter);
		}
		$post_type = $this->get_post_type();
		if($post_type != 'all'){
			$wheres[] = $wpdb->prepare('p.post_type = %s', $post_type);
		}
		if(isset($_REQUEST['from']) && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $_REQUEST['from'])){
			$wheres[] = $wpdb->prepare('t.registered >= %s', $_REQUEST['from'].' 00:00:00');
		}
		if(isset($_REQUEST['to']) && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $_REQUEST['to'])){
			$wheres[] = $wpdb->prepare('t.registered <= %s', $_REQUEST['to'].' 23:59:59');
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
		if(!empty($wheres)){
			$sql .= ' WHERE '.implode(' AND ', $wheres);
		}
		$order_by = 't.registered';
		if(isset($_GET['orderby'])){
			switch($_GET['orderby']){
				case 'updated':
				case 'registered':
				case 'expires':
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
			'item_name' => $lwp->_("Item Name"),
			'user' => $lwp->_("User Name"),
			'price' => $lwp->_("Purchased Price"),
			'status' => $lwp->_("Status"),
			'method' => $lwp->_('Method'),
			'expires' => $lwp->_('Expires'),
			'registered' => $lwp->_("Registered"),
			'updated' => $lwp->_("Last Updated"),
			'detail' => $lwp->_('Detail')
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
	
	function get_filter(){
		$filter = 'all';
		if(isset($_GET['status']) && !$_GET['status'] != 'all'){
			$target = $_GET['status'];
		}elseif(isset($_GET['status2']) && !$_GET['status2'] != 'all'){
			$target = $_GET['status2'];
		}else{
			$target = '';
		}
		if(false !== array_search($target, LWP_Payment_Status::get_all_status())){
			$filter = $target;
		}
		return $filter;
	}
	
	function get_post_type(){
		global $lwp;
		$filter = 'all';
		if(isset($_GET['post_types']) && !$_GET['post_types'] != 'all'){
			$target = $_GET['post_types'];
		}elseif(isset($_GET['post_types2']) && !$_GET['post_types2'] != 'all'){
			$target = $_GET['post_types2'];
		}else{
			$target = '';
		}
		if(false !== array_search($target, $lwp->option['payable_post_types'])){
			$filter = $target;
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
	 * @global Literally_WordPress $lwp
	 * @param Object $item
	 * @param string $column_name
	 * @return string
	 */
	function column_default($item, $column_name){
		global $lwp;
		switch($column_name){
			case 'user':
				return $item->display_name ? '<a href="'.admin_url('user-edit.php?user_id='.intval($item->user_id)).'">'.$item->display_name.'</a>' : $lwp->_('Deleted User');
				break;
			case 'item_name':
				return '<a href="'.admin_url('post.php?post='.intval($item->book_id)."&action=edit").'">'.$item->post_title.'</a>';
				break;
			case 'price':
				return number_format_i18n($item->price)." ({$lwp->option['currency_code']})";
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
				return mysql2date(get_option('date_format'), get_date_from_gmt($item->registered), false);
				break;
			case 'updated':
				return mysql2date(get_option('date_format'), get_date_from_gmt($item->updated), false);
				break;
			case 'status':
				return $lwp->_($item->status);
				break;
			case 'detail';
				return '<p><a class="button" href="'.admin_url("admin.php?page=lwp-management&transaction_id={$item->ID}").'">'.$lwp->_("Detail").'</a></p>';
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
	
	function extra_tablenav($which) {
		global $lwp;
		if($which != 'top') return;
		?>
		<div class="alignleft acitions">
			<select name="status<?php echo $nombre; ?>">
				<?php
				$status = array(
					'all' => $lwp->_('All Status'),
				 LWP_Payment_Status::START => $lwp->_('Start'),
				 LWP_Payment_Status::CANCEL => $lwp->_('Cancel'),
				 LWP_Payment_Status::SUCCESS => $lwp->_('Success'),
				 LWP_Payment_Status::REFUND => $lwp->_('Refund')
				);
				foreach($status as $s => $val): ?>
				<option value="<?php echo $s; if($s == $this->get_filter()) echo '" selected="selected'?>"><?php echo $val; ?></option>
				<?php endforeach; ?>
			</select>
			<select name="post_types<?php echo $nombre; ?>">
				<?php
				$post_types = array('all' => $lwp->_('All Post Types'));
				$post_type_labels = $lwp->option['payable_post_types'];
				if($lwp->event->is_enabled()){
					$post_type_labels[] = $lwp->event->post_type;
				}
				if($lwp->subscription->is_enabled()){
					$post_type_labels[] = $lwp->subscription->post_type;
				}
				foreach($post_type_labels as $p){
					$object = get_post_types(array('name' => $p), 'objects');
					foreach($object as $post_type){
						$post_types[$p] = $post_type->labels->name;
					}
				}
				foreach($post_types as $post_type => $label): ?>
				<option value="<?php echo $post_type; if($post_type == $this->get_post_type()) echo '" selected="selected'?>"><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
			<input style="width: 6em;" placeholder="<?php $lwp->e('Date From'); ?>" type="text" name="from" class="date-picker" value="<?php if(isset($_REQUEST['from'])) echo esc_attr($_REQUEST['from']); ?>" />
			<input style="width: 6em;" placeholder="<?php $lwp->e('Date To'); ?>" type="text" name="to" class="date-picker" value="<?php if(isset($_REQUEST['to'])) echo esc_attr($_REQUEST['to']); ?>" />
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