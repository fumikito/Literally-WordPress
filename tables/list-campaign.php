<?php
/**
 * Controle Campaign Table
 * @package literally_wordpress
 */
class LWP_List_Campaigns extends WP_List_Table{
	
	function __construct() {
		parent::__construct(array(
			'singular' => 'campaign',
			'plural' => 'campaigns',
			'ajax' => false
		));
	}
	
	function get_table_classes() {
		return array( 'widefat', $this->_args['plural'] );
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
				c.*, p.post_title, p.post_date, p.post_parent, pm.meta_value AS original_price, ((pm.meta_value - c.price) / pm.meta_value) AS discount
			FROM {$lwp->campaign} AS c
			LEFT JOIN {$wpdb->posts} AS p
			ON c.book_id = p.ID
			LEFT JOIN {$wpdb->postmeta} AS pm
			ON p.ID = pm.post_id AND pm.meta_key = 'lwp_price'
EOS;
		//Create Where section
		$wheres = array();
		if(!empty($wheres)){
			$sql .= ' WHERE '.implode(' AND ', $wheres);
		}
		$order_by = 'c.end';
		if(isset($_GET['orderby'])){
			switch ($_GET['orderby']) {
				case 'start':
				case 'end':
					$order_by = "c.".$_GET['orderby'];
					break;
			}
		}
		$order = (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'ASC' : 'DESC';
		$sql .= <<<EOS
			ORDER BY {$order_by} {$order}, c.ID DESC
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
		$lwp->e('No matching Campaign found.');
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
			'status' => "",
			'item' => $lwp->_("Item"),
			'start' => $lwp->_("Start"),
			'end' => $lwp->_('End'),
			'price' => $lwp->_('Sale Price'),
//			'coupon' => $lwp->_('Coupon')
		);
	}
	
	/**
	 * @global Literally_WordPress $lwp
	 * @return array
	 */
	function get_sortable_columns() {
		global $lwp;
		return array(
			'start' => array('start', false),
			'end' => array('end', false)
		);
	}
	
	function get_bulk_actions() {
		global $lwp;
		return array(
			'delete' => $lwp->_('Delete')
		);
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
	 * @global Literally_WordPress $lwp
	 * @param Object $item
	 * @param string $column_name
	 * @return string
	 */
	function column_default($item, $column_name){
		global $lwp;
		switch($column_name){
			case 'item':
				$titles = array();
				if($item->type == LWP_Campaign_Type::SINGULAR){
					$titles[$item->book_id] = $item->post_title; 
				}else{
					foreach($lwp->campaign_manager->get_campaign_posts($item->ID) as $post_id){
						$titles[$post_id] = get_the_title($post_id);
					}
				}
				$title_str = array();
				foreach($titles as $id => $title){
					switch(get_post_type($id)){
						case $lwp->subscription->post_type:
							$title_str[] = $lwp->_('Subscription')." ".$title;
							break;
						case $lwp->event->post_type:
							$title_str[] = get_the_title($lwp->event->get_event_from_ticket_id($id)).' '.$title;
							break;
						default:
							$title_str[] = $title;
							break;
					}
				}
				if(count($title_str) > 1){
					$title_str = implode(', ', $title_str);
					if(mb_strlen($title_str, 'utf-8') > 30){
						$title_str = mb_substr($title_str, 0, 30, 'utf-8').'&hellip;';
					}
					$title = sprintf($lwp->_('%d items<small>(%s)</small>'), count($titles), $title_str);
				}else{
					$title = current($title_str);
				}
				return sprintf('%s<br /><strong>--%s</strong> | <a href="%s">%s</a>',
						$title, $lwp->_($item->type), admin_url('admin.php?page=lwp-campaign&campaign='.$item->ID),
						$lwp->_('Edit'));
				break;
			case 'start':
				return mysql2date('Y-m-d H:i', $item->start, false);
				break;
			case 'end':
				return mysql2date('Y-m-d H:i', $item->end, false);
				break;
			case 'price':
				switch($item->calculation){
					case LWP_Campaign_Calculation::SPECIAL_PRICE:
						return number_format($item->price).lwp_currency_code();
						break;
					case LWP_Campaign_Calculation::DISCOUNT:
						return sprintf($lwp->_('%s discount'), number_format($item->price).lwp_currency_code());
						break;
					case LWP_Campaign_Calculation::PERCENT:
						return sprintf('%d%%', $item->price);
						break;
				}
				break;
			case 'coupon':
				if(empty($item->coupon)){
					return __('No');
				}else{
					return __('Yes');
				}
				break;
			case 'status':
				$now = strtotime(date_i18n('Y-m-d H:i:s'));
				$src = ($now <= strtotime($item->end) && $now >= strtotime($item->start)) ? 'lightbulb_on_16.png' : 'lightbulb_off_16.png';
				$src = $lwp->url."/assets/".$src;
				return '<img src="'.$src.'" width="16" height="16" alt="" />';
				break;
		}
	}
	
	
	
	/**
	 * Returns check box
	 * @param Object $item
	 * @return string
	 */
	function column_cb($item){
		return sprintf('<input type="checkbox" name="%s[]" value="%d" />', $this->_args['plural'], $item->ID);
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