<?php
/**
 * Output Table for Transfer
 *
 * @author Takahshi Fumiki
 * @package literally_wordpress
 */
class LWP_List_Download_Logs extends WP_List_Table{
	
	function __construct() {
		parent::__construct(array(
			'singular' => 'download-log',
			'plural' => 'download-logs',
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
		
		//Create Where section
		$wheres = array();
		if(isset($_GET['s']) && !empty($_GET['s'])){
			$like_string = preg_replace("/^'(.+)'$/", '$1', $wpdb->prepare("%s", $_GET['s']));
			$wheres[] = <<<EOS
				((f.name LIKE '%{$like_string}%')
					OR
				 (f.detail LIKE '%{$like_string}%')
					OR
				 (u.display_name LIKE '%{$like_string}%')
					OR
				 (fl.user_agent LIKE '%{$like_string}%')
					OR
				 (u.user_login LIKE '%{$like_string}%')
					OR
				 (u.user_email LIKE '%{$like_string}%'))
EOS;
		}
		$where_clause = empty($wheres) ? '' : ' WHERE '.implode(' AND ', $wheres);
		//Create orderby
		$order_by = 'fl.updated';
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
		//Create SQL
		$sql = <<<EOS
			SELECT DISTINCT SQL_CALC_FOUND_ROWS
				fl.updated AS downloaded, fl.user_id, fl.file_id, fl.ip_address, fl.user_agent,
				f.name, f.file, f.public, f.free, f.book_id, 
				u.ID AS registered_user_id, u.display_name
			FROM {$lwp->file_logs} AS fl
			LEFT JOIN {$lwp->files} AS f
			ON fl.file_id = f.ID
			LEFT JOIN {$wpdb->users} AS u
			ON fl.user_id = u.ID
			{$where_clause}
			ORDER BY {$order_by} {$order}
			LIMIT {$offset}, {$per_page}
EOS;
		$this->items = $wpdb->get_results($sql);
		//Do query
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
			'file_name' => $lwp->_('File Name'),
			'user' => $lwp->_("User Name"),
			'accessibility' => $lwp->_('Accessiblity'),
			'user_agent' => $lwp->_("User Agent"),
			'ip_address' => $lwp->_("IP Address"),
			'downloaded' => $lwp->_("Downloaded"),
		);
	}
	
	/**
	 * @return array
	 */
	function get_sortable_columns() {
		return array(
			'downloaded' => array('downloaded', false)
		);
	}
	
	/**
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param Object $item
	 * @param string $column_name
	 * @return string
	 */
	function column_default($item, $column_name){
		global $lwp, $wpdb;
		switch($column_name){
			case 'item_name':
				return sprintf(
					'<a href="%1$s">%2$s</a> -- <strong>%3$s</strong>',
					admin_url('post.php?post='.intval($item->book_id)."&action=edit"),
					get_the_title($item->book_id),
					get_post_type_object(get_post_type($item->book_id))->labels->name
				);
				break;
			case 'file_name':
				return $item->name ?
						sprintf('%s <code>%s</code>', $item->name, $lwp->post->detect_mime($item->file)) :
						$lwp->_('Deleted File');
				break;
			case 'accessibility';
				switch($item->free){
					case 2:
						return sprintf('<span class="lwp-transaction-ok">%s<small>OK</small></span>', $lwp->_('Anyone'));
						break;
					case 1:
						return sprintf('<span class="lwp-transaction-ok">%s<small>OK</small></span>', $lwp->_('Members Only'));
						break;
					default:
						$return = $lwp->_('Purchasers Only');
						$transaction = $wpdb->get_row($wpdb->prepare(
								"SELECT * FROM {$lwp->transaction} WHERE book_id = %d AND user_id = %d AND status = %s",
								$item->book_id, $item->user_id, LWP_Payment_Status::SUCCESS));
						if($transaction){
							return sprintf('<span class="lwp-transaction-ok">%1$s<small><a href="%2$s">%3$s</a></small></span>', $return, admin_url('admin.php?page=lwp-management&transaction_id='.$transaction->ID),$lwp->_('detail'));
						}else{
							return sprintf('<span class="lwp-transaction-error">%s<small>%s</small></span>', $return, $lwp->_('No Transaction data'));
						}
						break;
				}
				break;
			case 'user':
				if($item->user_id == 0){
					return $lwp->_('Guest');
				}elseif($item->registered_user_id){
					return '<a href="'.admin_url('user_edit.php?user_id='.intval($item->user_id)).'">'.$item->display_name.'</a>';
				}else{
					return $lwp->_('Deleted User');
				}
				break;
			case 'user_agent':
				return '<span>'.$item->user_agent.'</span>';
				break;
			case 'ip_address':
				return $item->ip_address;
				break;
			case 'downloaded':
				return date('Y-m-d H:i:s', strtotime($item->downloaded) + 60 * 60 * get_option('gmt_offset'));
				break;
		}
	}
	
	function get_table_classes() {
		return str_replace('fixed', '', parent::get_table_classes());
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