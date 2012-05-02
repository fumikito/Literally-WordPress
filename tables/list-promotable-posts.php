<?php
/**
 * Description of LWP_List_Request
 *
 * @since 0.9
 */
class LWP_List_Promotable_Posts extends WP_List_Table{
	
	var $post_types = array();
	
	var $user_id = 0;
	
	function __construct($post_types = array(), $user_id = 0) {
		$this->post_types = $post_types;
		$this->user_id = $user_id;
		parent::__construct(array(
			'singular' => 'link',
			'plural' => 'links',
			'ajax' => false
		));
	}
	
	function no_items(){
		global $lwp;
		$lwp->e('No matching post is found.');
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
			SELECT DISTINCT SQL_CALC_FOUND_ROWS
				p.*, pm.meta_value AS price
			FROM {$wpdb->posts} AS p
			LEFT JOIN {$wpdb->postmeta} AS pm
			ON p.ID = pm.post_id AND pm.meta_key = 'lwp_price'
EOS;
		//WHERE
		$where = array(
			"p.post_status = 'publish'",
			"p.post_type IN (".implode(',', array_map(create_function('$post_type', 'return "\'".$post_type."\'";'), $this->post_types)).")"
		);
		//User
		if($this->user_id){
			//$where[] = $wpdb->prepare("(r.user_id = %d)", $this->user_id);
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
		$order_by = 'p.post_date';
		if(isset($_GET['order_by'])){
			switch($_GET['order_by']){
				case 'published':
				case 'price':
				case 'margin':
				case 'sold':	
					break;
			}
		}
		$order = (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'ASC' : 'DESC';
		$sql .= " ORDER BY {$order_by} {$order}";
		$sql .= " LIMIT {$offset}, {$per_page}";
		var_dump($sql);
		$wpdb->show_errors();
		$this->items = $wpdb->get_results($sql);
		$this->set_pagination_args(array(
			'total_items' => (int) $wpdb->get_var('SELECT FOUND_ROWS()'),
			'per_page' => $per_page
		));
	}
	
	
	function get_columns(){
		global $lwp;
		$column = array(
			'name' => $lwp->_('Name'),
			'type' => $lwp->_('Type'),
			'url' => 'URL',
			'price' => $lwp->_('Price'),
			'margin' => $lwp->_('Margin'),
			'published' => $lwp->_('Published'),
			'sold' => $lwp->_('Sold')
		);
		return $column;
	}
	
	function get_sortable_columns() {
		return array(
			'published' => array('published', false),
			'price' => array('price', false),
			'margin' => array('margin', false),
			'sold' => array('sold', false)
		);
	}
	
	function column_default($item, $column_name){
		global $lwp;
		switch($column_name){
			case 'name' :
				return $item->post_title;
				break;
			case 'url':
				return get_permalink($item->ID);
				break;
			case 'price':
				return number_format($item->price);
				break;
			case 'margin':
				return number_format($item->price);
				break;
			case 'published':
				return mysql2date(get_option('date_format'), $item->post_date);
				break;
			case 'type':
				$obj = get_post_type_object($item->post_type);
				return $obj->labels->name;
				break;
			case 'sold':
				return 0;
				break;
		}
	}
	
	function extra_tablenav($which) {
		global $lwp;
		switch($which){
			case 'top': ?>
		<div class="alignleft actions">
			<select name="type">
				<option value="all"<?php if(!isset($_GET['type']) || $_GET['type'] == 'all') echo ' selected="selected"';?>><?php $lwp->e('All Post Types'); ?></option>
				<?php foreach($this->post_types as $post_type): $post_type_obj = get_post_type_object($post_type);?>
					<option value="<?php echo $post_type;?>"<?php if(isset($_GET['type']) && $_GET['type'] == $post_type) echo ' selected="selected"';?>>
						<?php echo esc_html($post_type_obj->labels->name); ?>
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