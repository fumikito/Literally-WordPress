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
	
	function get_table_classes() {
			return array( 'widefat', $this->_args['plural'] );
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
		
		//TODO: Fix sold count if cart system is implemented
		//Create SQL
		$sql = <<<EOS
			SELECT DISTINCT SQL_CALC_FOUND_ROWS
				p.*, pm1.meta_value AS price, COALESCE(pm2.meta_value, %d) AS margin, t.sold
			FROM {$wpdb->posts} AS p
			LEFT JOIN {$wpdb->postmeta} AS pm1
			ON p.ID = pm1.post_id AND pm1.meta_key = 'lwp_price'
			LEFT JOIN {$wpdb->postmeta} AS pm2
			ON p.ID = pm2.post_id AND pm2.meta_key = '{$lwp->reward->promotion_margin_key}'
			LEFT JOIN (
				SELECT book_id, COUNT(book_id) AS sold FROM {$lwp->transaction}
				WHERE status = %s
				GROUP BY book_id
			) AS t
			ON t.book_id = p.ID
EOS;
		$sql = $wpdb->prepare($sql, $lwp->reward->promotion_margin, LWP_Payment_Status::SUCCESS);
		//WHERE
		$where = array(
			"p.post_status = 'publish'",
			"pm1.meta_value IS NOT NULL",
			"pm1.meta_value > 0"
		);
		//User
		if($this->user_id){
			//$where[] = $wpdb->prepare("(r.user_id = %d)", $this->user_id);
		}
		//post_type
		if(isset($_GET['type']) && (false !== array_search((string)$_GET['type'], $this->post_types))){
			$where[] = $wpdb->prepare("(p.post_type = %s)", (string)$_GET['type']);
		}else{
			$where[] = "p.post_type IN (".implode(',', array_map(create_function('$post_type', 'return "\'".$post_type."\'";'), $this->post_types)).")";
		}
		//Search
		if(isset($_GET['s']) && !empty($_GET['s'])){
			$where[] = $wpdb->prepare("p.post_title LIKE %s", '%'.(string)$_GET['s'].'%');
		}
		//Create where section
		if(!empty($where)){
			$sql .= ' WHERE '.implode(' AND ', $where);
		}
		//ORDER
		$order_by = 'p.post_date';
		if(isset($_GET['orderby'])){
			switch($_GET['orderby']){
				case 'published':
					$order_by = "p.post_date";
					break;
				case 'price':
					$order_by = "CAST(pm1.meta_value AS UNSIGNED)";
					break;
				case 'margin':
					$order_by = $wpdb->prepare("CAST(COALESCE(pm2.meta_value, %d) AS UNSIGNED)",  $lwp->reward->promotion_margin);
					break;
				case 'reward':
					$order_by = $wpdb->prepare("(CAST(pm1.meta_value AS UNSIGNED) * CAST(COALESCE(pm2.meta_value, %d) AS UNSIGNED) / 100)",  $lwp->reward->promotion_margin);
					break;
				case 'sold':
					$order_by = "t.sold";
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
			'type' => $lwp->_('Type'),
			'name' => $lwp->_('Name'),
			'price' => $lwp->_('Price'),
			'margin' => $lwp->_('Margin'),
			'reward' => $lwp->_('Reward'),
			'published' => $lwp->_('Published'),
			'sold' => $lwp->_('Sold'),
			'url' => 'URL'
		);
		return $column;
	}
	
	function get_sortable_columns() {
		return array(
			'published' => array('published', false),
			'price' => array('price', false),
			'margin' => array('margin', false),
			'sold' => array('sold', false),
			'reward' => array('reward', false)
		);
	}
	
	function column_default($item, $column_name){
		global $lwp;
		switch($column_name){
			case 'type':
				$obj = get_post_type_object($item->post_type);
				return $obj->labels->name;
			case 'name' :
				return '<a href="'.get_permalink($item->ID).'">'.apply_filters('the_title', $item->post_title, $item->ID).'</a>';
				break;
			case 'price':
				return number_format($item->price);
				break;
			case 'margin':
				return number_format(min($item->margin, $lwp->reward->promotion_max));
				break;
			case 'reward':
				return number_format($item->price * min($item->margin, $lwp->reward->promotion_max) / 100);
				break;
			case 'published':
				return mysql2date(get_option('date_format'), $item->post_date);
				break;
				break;
			case 'sold':
				return number_format($item->sold);
				break;
			case 'url':
				return '<input type="text" class="small-text" value="'.$lwp->reward->get_promotion_link($item->ID, get_current_user_id()).'" onclick="this.select();" />';
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