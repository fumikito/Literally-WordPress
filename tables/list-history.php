<?php

// If History talbe is on public page, 
// Load WP_Screen class which is required by WP_Lit_Table
global $wp_version;
if(!function_exists('convert_to_screen') && version_compare('3.5', $wp_version) < 1){
	require_once ABSPATH.'wp-admin/includes/screen.php';
	function convert_to_screen(){
		return WP_Screen::get('histories');
	}
}

// Under 3.4, this function is required.
if(!function_exists('get_current_screen')){
	function get_current_screen(){
		return null;
	}
}



/**
 * Controle user purchase history
 * @package literally_wordpress
 */
class LWP_List_History extends WP_List_Table {
	
	function __construct() {
		parent::__construct(array(
			'singular' => 'history',
			'plural' => 'histories',
			'ajax' => false
		));
	}
	
	/**
	 *
	 * @global Literally_WordPress $lwp 
	 */
	function no_items(){
		global $lwp;
		$lwp->e("No matching history is found.");
	}
	
	/**
	 *
	 * @global Literally_WordPress $lwp
	 * @return array 
	 */
	function get_columns() {
		global $lwp;
		$column = array(
			'item_name' => $lwp->_("Item Name"),
			'quantity' => $lwp->_('Quantity'),
			'price' => $lwp->_("Purchased Price"),
			'method' => $lwp->_('Method'),
			'expires' => $lwp->_('Expires'),
			'registered' => $lwp->_("Registered"),
		);
		if(is_admin()){
			$column['updated'] = $lwp->_("Last Updated");
		}
		$column['status'] = $lwp->_('Status');
		return apply_filters('lwp_history_table_header', $column);
	}
	
	/**
	 *
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb 
	 */
	function prepare_items() {
		global $lwp, $wpdb, $user_ID;
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
		
		$sql = <<<EOS
			SELECT SQL_CALC_FOUND_ROWS 
				t.*, p.post_title, p.post_type,p.post_parent
			FROM {$lwp->transaction} AS t
			INNER JOIN {$wpdb->posts} AS p
			ON t.book_id = p.ID
			INNER JOIN {$wpdb->postmeta} AS pm
			ON p.ID = pm.post_id AND pm.meta_key = 'lwp_price'
			LEFT JOIN {$wpdb->postmeta} AS pm2
			ON p.post_parent = pm2.post_id AND pm2.meta_key = '{$lwp->event->meta_selling_limit}'
EOS;
		//Create where clause, user ID
		$where = array( $wpdb->prepare('t.user_id = %d', $user_ID));
		//Status
		$where[] = $wpdb->prepare('(t.status IN (%s, %s, %s, %s, %s) OR (t.status = %s AND t.method IN (%s, %s, %s, %s, %s)) OR ( (t.status = %s) AND (pm2.meta_value IS NOT NULL) AND (TO_DAYS(NOW()) <= TO_DAYS(pm2.meta_value))) )',
					LWP_Payment_Status::SUCCESS, LWP_Payment_Status::REFUND, LWP_Payment_Status::REFUND_REQUESTING, LWP_Payment_Status::AUTH, LWP_Payment_Status::WAITING_REVIEW,
					LWP_Payment_Status::START,
					LWP_Payment_Methods::TRANSFER, LWP_Payment_Methods::SOFTBANK_PAYEASY, LWP_Payment_Methods::SOFTBANK_WEB_CVS, LWP_Payment_Methods::GMO_WEB_CVS, LWP_Payment_Methods::GMO_PAYEASY,
					LWP_Payment_Status::WAITING_CANCELLATION);
		//Post type
		if($this->get_post_type() != 'all'){
			$where[] = $wpdb->prepare("p.post_type = %s", $this->get_post_type());
		}
		//Search
		if(isset($_GET['s']) && !empty($_GET["s"])){
			$where[] = $wpdb->prepare("((p.post_title LIKE %s) OR (p.post_content LIKE %s) OR (p.post_excerpt LIKE %s))", '%'.$_GET["s"].'%', '%'.$_GET["s"].'%', '%'.$_GET["s"].'%');
		}
		$sql .= ' WHERE '.implode(' AND ', $where);
		//ORDER
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
		$sql .= " ORDER BY {$order_by} {$order}";
		$sql .= " LIMIT {$offset}, {$per_page}";
		$this->items = $wpdb->get_results($sql);
		$this->set_pagination_args(array(
			'total_items' => (int) $wpdb->get_var("SELECT FOUND_ROWS()"),
			'per_page' => $per_page
		));
	}
	
	/**
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param Object $item
	 * @param string $column_name
	 * @return string
	 */
	function column_default($item, $column_name){
		global $lwp, $wpdb;
		$tag = '';
		switch($column_name){
			case 'item_name':
				if($item->post_type == $lwp->subscription->post_type){
					$url = $lwp->subscription->get_subscription_archive();
					$title = $item->post_title;
				}elseif($item->post_type == $lwp->event->post_type){
					$url = lwp_ticket_url($item->post_parent);
					$title = get_the_title($item->post_parent).'&nbsp;'.$item->post_title;
				}else{
					$url = lwp_unsslize(get_permalink($item->book_id));
					$title = $item->post_title;
				}
				if($item->post_type == $lwp->subscription->post_type){
					$post_type = $lwp->_('Subscription');
				}else{
					$post_type = get_post_type_object($item->post_type)->labels->name;
				}
				$tag = '<a href="'.$url.'">'.$title.'</a>&nbsp;-&nbsp;<strong>'.$post_type.'</strong>';
				break;
			case 'quantity':
				$tag = number_format_i18n($item->num);
				break;
			case 'price':
				switch($item->status){
					case LWP_Payment_Status::REFUND:
					case LWP_Payment_Status::REFUND_REQUESTING:
						return sprintf('%2$s<br /><del>%1$s</del>',
							number_format($item->price)." ".lwp_currency_code(),
							number_format($item->price - $lwp->refund_manager->detect_refund_price($item))." ".lwp_currency_code());
						break;
					default:
						return number_format($item->price)." ".lwp_currency_code();
						break;
				}
				break;
			case 'method':
				if(false === array_search($item->status, array(LWP_Payment_Status::WAITING_CANCELLATION, LWP_Payment_Status::QUIT_WAITNG_CANCELLATION))){
					$tag = $lwp->_($item->method);
				}else{
					$tag = '-';
				}
				break;
			case 'expires':
				if($item->expires == '0000-00-00 00:00:00'){
					return $lwp->_('No Limit');
				}else{
					$date = get_date_from_gmt($item->expires);
					$remain = ceil((strtotime($date) - current_time('timestamp')) / 60 / 60 / 24);
					if($remain < 0){
						$string = $lwp->_('Expired');
					}else{
						$string = sprintf($lwp->_('%d days left'), $remain);
					}
					$tag = $string.'<br /><small>'.mysql2date(get_option('date_fomrat'), $date).'</small>';
				}
				break;
			case 'registered':
				$tag = mysql2date(get_option('date_format'), get_date_from_gmt($item->registered), false);
				break;
			case 'updated':
				$tag = mysql2date(get_option('date_format'), get_date_from_gmt($item->updated), false);
				break;
			case 'status':
				switch($item->status){
					case LWP_Payment_Status::START:
						$text = $lwp->_('Waiting for Payment');
						break;
					case LWP_Payment_Status::REFUND:
						$refund = $lwp->refund_manager->detect_refund_price($item);
						$text = ($refund == $item->price) ? $lwp->_($item->status) : $lwp->_('Partial Refund');
						break;
					default:
						$text = $lwp->_($item->status);
						break;
				}
				$tag = sprintf('<span class="lwp-%2$s"><i></i>%1$s</span>', $text, strtolower($item->status));
				if($item->status == LWP_Payment_Status::START && false === array_search($item->method, array(LWP_Payment_Methods::PAYPAL, LWP_Payment_Methods::GMO_CC, LWP_Payment_Methods::SOFTBANK_CC))){
					$tag .= '<a class="lwp-action" href="'.lwp_endpoint('payment-info', array('transaction' => $item->ID)).'">'.$lwp->_ ('Detail').'</a>';
				}
				if($item->post_type == $lwp->event->post_type){
					switch ($item->status){
						case LWP_Payment_Status::SUCCESS:
						case LWP_Payment_Status::AUTH:
							if(lwp_is_cancelable($item->post_parent)){
								$tag .= '<a class="lwp-action" href="'.lwp_cancel_url($item->post_parent).'">'.$lwp->_('Cancel').'</a>';
							}else{
								$tag .= '<small> - '.$lwp->_('Uncancelable').'</small>';
							}
							break;
						case LWP_Payment_Status::WAITING_CANCELLATION:
							if(lwp_is_event_available($item->post_parent) && $lwp->event->has_cancel_list($item->post_parent)){
								$tag .= sprintf('<a class="lwp-action" href="%s" onclick="if(!confirm(\'%s\')) return false;">%s</a>', 
										lwp_cancel_list_dequeue_url($item->book_id), 
										esc_js(sprintf($lwp->_('Are you sure to deregister from cancel list of %s?'), get_the_title($item->post_parent).' '.$item->post_title)), 
										$lwp->_('Deregister'));
							}
							break;
					}
				}
				break;
		}
		return apply_filters('lwp_history_column_value', $tag, $column_name, $item);
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
		if(is_admin()){
			return isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
		}else{
			global $wp_query;
			$paged = (int)$wp_query->get('paged');
			return max(1, $paged);
		}
	}
	
	
	function get_post_type(){
		global $lwp;
		$filter = 'all';
		if(isset($_GET['post_types']) && !$_GET['post_types'] != 'all'){
			$filter = (string)$_GET['post_types'];
		}
		return $filter;
	}
	
	/**
	 *
	 * @return int
	 */
	function get_per_page(){
		$per_page = 20;
		if(isset($_GET['per_page']) && $_GET['per_page'] != 20){
			$per_page = max($per_page, absint($_GET['per_page']));
		}
		return $per_page;
	}
	
	
	function extra_tablenav($which) {
		global $lwp;
		if($which == 'top'):
			if($lwp->refund_manager->is_user_on_refund_queue(get_current_user_id()) && !$lwp->refund_manager->did_user_register_account(get_current_user_id())){
				if(is_admin()){
					$tag = '<div class="error"><p>%s</p></div>';
				}else{
					$tag = '<p class="lwp-error-message">%s</p>';
				}
				printf($tag, sprintf($lwp->_('You are now on the refund request queueue, but have no account registered. Please register your bank account information <a href="%s">here</a>.'), lwp_refund_account_url()));
			}
		?>
		<div class="alignleft acitions">
			<?php
				$post_types = array('all' => $lwp->_('All Post Types'));
				$post_type_labels = $lwp->option['payable_post_types'];
				if($lwp->subscription->is_enabled()){
					$post_type_labels[] = $lwp->subscription->post_type;
				}
				if($lwp->event->is_enabled()){
					$post_type_labels[] = $lwp->event->post_type;
				}
				if(count($post_type_labels) > 1):
			?>
			<select name="post_types">
				<?php
					foreach($post_type_labels as $p){
						$object = get_post_types(array('name' => $p), 'objects');
						foreach($object as $post_type){
							$post_types[$p] = $post_type->labels->name;
						}
					}
					foreach($post_types as $post_type => $label):
				?>
					<option value="<?php echo $post_type; if($post_type == $this->get_post_type()) echo '" selected="selected'?>"><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
			<?php endif; ?>
			<select name="per_page">
				<?php foreach(array(20, 50, 100) as $num): ?>
				<option value="<?php echo $num; ?>"<?php if($this->get_per_page() == $num) echo ' selected="selected"';?>>
					<?php printf($lwp->_('%d per 1Page'), $num); ?>
				</option>
				<?php endforeach; ?>
			</select>
			
			<input type="submit" class="button-secondary" value="<?php $lwp->e('Filter'); ?>" />
		</div>
		<?php
		endif;
	}
	
	function get_table_classes() {
		return array_merge(parent::get_table_classes(), array('lwp-table'));
	}
	
	/**
	 * 
	 * @global Literally_WordPress $lwp
	 * @param string $which
	 * @return string
	 */
	function pagination( $which ) {
		global $lwp;
		if(is_admin()){
			parent::pagination($which);
		}else{
			if ( empty( $this->_pagination_args ) )
			return;

			extract( $this->_pagination_args );

			$output = '<span class="displaying-num">' . sprintf( $lwp->n($lwp->_('1 item'), $lwp->_('%s items'), $total_items ), number_format_i18n( $total_items ) ) . '</span>';

			$current = $this->get_pagenum();

			$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

			$current_url = preg_replace("/page\/[0-9].*\/?$/", "", remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url ));

			$page_links = array();

			$disable_first = $disable_last = '';
			if ( $current == 1 )
				$disable_first = ' disabled';
			if ( $current == $total_pages )
				$disable_last = ' disabled';

			$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
				'first-page' . $disable_first,
				esc_attr__( 'Go to the first page' ),
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				'&laquo;'
			);

			$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
				'prev-page' . $disable_first,
				esc_attr__( 'Go to the previous page' ),
				esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
				'&lsaquo;'
			);

			if ( 'bottom' == $which )
				$html_current_page = $current;
			else
				$html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='%s' value='%s' size='%d' />",
					esc_attr__( 'Current page' ),
					esc_attr( 'paged' ),
					$current,
					strlen( $total_pages )
				);

			$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
			$page_links[] = '<span class="paging-input">' . sprintf( $lwp->_( '%1$s of %2$s'), $html_current_page, $html_total_pages ) . '</span>';

			$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
				'next-page' . $disable_last,
				esc_attr__( 'Go to the next page' ),
				esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
				'&rsaquo;'
			);

			$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
				'last-page' . $disable_last,
				esc_attr__( 'Go to the last page' ),
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				'&raquo;'
			);

			$output .= "\n<span class='pagination-links'>" . join( "\n", $page_links ) . '</span>';

			if ( $total_pages )
				$page_class = $total_pages < 2 ? ' one-page' : '';
			else
				$page_class = ' no-pages';

			$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

			echo $this->_pagination;
		}
	}
}