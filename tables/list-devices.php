<?php
/**
 * Controle Device Table
 * @package literally_wordpress
 */
class LWP_List_Devices extends WP_List_Table{
	
	function __construct() {
		parent::__construct(array(
			'singular' => 'device',
			'plural' => 'devices',
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
				d.*, r.assigned
			FROM {$lwp->devices} AS d
			LEFT JOIN (
				SELECT COUNT(file_id) As assigned,device_id
				FROM {$lwp->file_relationships} 
				GROUP BY device_id
			) AS r
			ON d.ID = r.device_id
EOS;
		//Create Where section
		$wheres = array();
		if(!empty($wheres)){
			$sql .= ' WHERE '.implode(' AND ', $wheres);
		}
		$order_by = 'd.ID';
		if(isset($_GET['order_by'])){
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
		$lwp->e('No matching device found.');
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
			'name' => $lwp->_("Device Name"),
			'slug' => $lwp->_("Slug"),
			'assigned' => $lwp->_("Assigned"),
			'action' => $lwp->_("Action")
		);
	}
	
	/**
	 * @global Literally_WordPress $lwp
	 * @return array
	 */
	function get_sortable_columns() {
		global $lwp;
		return array();
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
			case 'name':
				return $item->name;
				break;
			case 'slug':
				return $item->slug;
				break;
			case 'assigned':
				return intval($item->assigned);
				break;
			case 'action':
				$url = admin_url('admin.php?page=lwp-devices&device='.$item->ID);
				return '<a class="button" href="'.$url.'">'.$lwp->_('Edit').'</a>';
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