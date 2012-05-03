<?php
/**
 * Controller of reward system
 *
 * @package literally_wordpress
 */
class LWP_Reward extends Literally_WordPress_Common{
	
	/**
	 * @var boolean
	 */
	public $promotable = false;
	
	/**
	 * @var int
	 */
	public $promotion_margin = 0;
	
	/**
	 * @var int
	 */
	public $promotion_max = 90;
	
	/**
	 * @var boolean
	 */
	public $rewardable = false;
	
	/**
	 * @var int
	 */
	public $author_margin = 0;
	
	/**
	 * @var int
	 */
	public $author_max = 90;
	
	/**
	 * @var int
	 */
	private $minimum_request = 0;
	
	
	/**
	 * Payment request limit
	 * @var int
	 */
	private $request_limit = 1;
	
	/**
	 * Payment at this month
	 * @var int
	 */
	private $pay_month_after = 0;
	
	/**
	 * Payment at this day
	 * @var int
	 */
	private $pay_at_day = 30;
	
	/**
	 * Metakey of postmeta
	 * @var string
	 */
	public $promotion_margin_key = '_lwp_promotion_margin';
	
	/**
	 * Metakey of usermeta
	 * @var string
	 */
	private $promotion_personal_margin = '_lwp_promotion_personal_margin';
	
	/**
	 * Metakey of usermeta
	 * @var string
	 */
	private $author_personal_margin = '_lwp_author_margin';

	/**
	 * Notice for promoters 
	 * @var string 
	 */
	private $notice = '';
	
	/**
	 * Setup option
	 * @see Literally_WordPress_Common
	 * @param array $option 
	 */
	protected function set_option($option = array()) {
		$option = shortcode_atts(array(
			"reward_promoter" => $this->promotable,
			"reward_promotion_margin" => $this->promotion_margin,
			"reward_promotion_max" => $this->promotion_max,
			"reward_author" => $this->rewardable,
			"reward_author_margin" => $this->author_margin,
			"reward_author_max" => $this->author_max,
			"reward_minimum" => $this->minimum_request,
			"reward_request_limit" => $this->request_limit,
			"reward_pay_at" => $this->pay_at_day,
			"reward_pay_after_month" => $this->pay_month_after,
			"reward_notice" => $this->notice
		), $option);
		$this->promotable = (boolean) $option['reward_promoter'];
		$this->promotion_margin = (int) $option['reward_promotion_margin'];
		$this->promotion_max = (int)$option['reward_promotion_max'];
		$this->rewardable = (boolean)$option['reward_author'];
		$this->author_margin = (int)$option['reward_author_margin'];
		$this->author_max = (int)$option['reward_author_max'];
		$this->minimum_request = (float)$option['reward_minimum'];
		$this->request_limit = (int) $option['reward_request_limit'];
		$this->pay_at_day = (int) $option['reward_pay_at'];
		$this->pay_month_after = (int) $option['reward_pay_after_month'];
		$this->notice = (string) $option['reward_notice'];
		$this->enabled = (boolean)($this->promotable || $this->rewardable);
	}
	
	/**
	 * Register hooks
	 * @see Literally_WordPress_Common 
	 */
	protected function on_construct(){
		if($this->is_enabled()){
			add_action("admin_init", array($this, 'admin_init'));
			add_action('lwp_payable_post_type_metabox', array($this, 'metabox_margin'), 10, 2);
			add_action('save_post', array($this, 'save_post'));
			add_action('edit_user_profile', array($this, 'edit_user_profile'));
			add_action("profile_update", array($this, "profile_update"), 10, 2);
		}
	}
	
	/**
	 * Executed at amin_init
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp 
	 */
	public function admin_init(){
		global $wpdb, $lwp;
		if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'lwp_reward_request_'.get_current_user_id()) && ($amount = $this->user_rest_amount(get_current_user_id(), true))){
			$result = $wpdb->insert(
				$lwp->reward_logs,
				array(
					'user_id' => get_current_user_id(),
					'price' => $amount,
					'status' => LWP_Payment_Status::START,
					'registered' => date_i18n('Y-m-d H:i:s'),
					'updated' => date_i18n('Y-m-d H:i:s')
				),
				array('%d', '%d', '%s', '%s', '%s')
			);
			if($result){
				$lwp->message[] = $this->_('Request is accepted.');
			}else{
				$lwp->message[] = $this->_('Your request is wrong.');
				$lwp->error = true;
			}
		}
	}
	
	/**
	 * Output form in metabox
	 * @param object $post
	 * @param array $metabox 
	 */
	public function metabox_margin($post, $metabox){
		wp_nonce_field('lwp_individual_margin', '_lwpmarginnonce', false);
		?>
			<h4><?php $this->e('Promotion'); ?></h4>
			<table class="form-table">
				<tbody>
					<th valign="top"><?php $this->e("Margin"); ?></th>
					<td>
						<input type="text" class="small-text" name="lwp_post_margin" id="lwp_post_margin" value="<?php echo esc_attr(get_post_meta($post->ID, $this->promotion_margin_key, true)); ?>" />%
					</td>
				</tbody>
			</table>
			<p class="description">
				<?php printf($this->_("You can override this posts margin individually. Defalt is <strong>%d%%</strong>. If set as default, leave it blank."), $this->promotion_margin); ?>
			</p>
		<?php
	}
	
	/**
	 * Executed on saving post to save postmeta
	 * @param int $post_id
	 */
	public function save_post($post_id){
		if(isset($_REQUEST['_lwpmarginnonce'], $_REQUEST['lwp_post_margin']) && wp_verify_nonce($_REQUEST['_lwpmarginnonce'], 'lwp_individual_margin')){
			if(empty($_REQUEST['lwp_post_margin'])){
				delete_post_meta($post_id, $this->promotion_margin_key);
			}else{
				update_post_meta($post_id, $this->promotion_margin_key, (int)$_REQUEST['lwp_post_margin']);
			}
		}
	}
	
	/**
	 * Show setting form on edit user screen
	 * @param WP_User $user 
	 */
	public function edit_user_profile($user){
		if($this->promotable || ($this->rewardable && user_can($user, 'edit_posts'))):
			?>
			<h3><?php $this->e('This user\'s reward setting '); ?></h3>
			<table class="form-table">
				<tbody>
					<tr>
						<th valign="top"><label><?php $this->e('About personal reward setting'); ?></label></th>
						<td>
							<p class="description">
								<?php printf($this->_('You can set personal coefficient for this user. Current promotional margin default is %1$d, so if you set %2$.1f, this user can get %3$d%%. If you want reward for royal user, this setting helps otherwise leave it blank. Maximum margin can be limited on <a href="%4$s">Setting</a>'), $this->promotion_margin, 1.2, 1.2 * $this->promotion_margin, admin_url('admin.php?page=lwp-setting')); ?>
							</p>
						</td>
					</tr>
					<?php if($this->promotable): wp_nonce_field('lwp_personal_promotion_ratio', '_lwppersonalpromotion', false); ?>
					<tr>
						<th valign="top"><label for="reward_personal_coefficient"><?php $this->e('Promotion coefficient'); ?></label></th>
						<td>
							<?php printf($this->_('Default %d%%: '), $this->promotion_margin); ?>
							<input class="small-text" type="text" name="reward_personal_coefficient" id="reward_personal_coefficient" value="<?php echo esc_attr(get_user_meta($user->ID, $this->promotion_personal_margin, true)); ?>" /><strong><?php $this->e('* MUST BE FLOAT'); ?></strong>
						</td>
					</tr>
					<?php endif; ?>
					<?php if($this->rewardable && user_can($user, 'edit_posts')): wp_nonce_field('lwp_personal_author_ratio', '_lwppersonalauthor', false); ?>
					<tr>
						<th valign="top"><label for="reward_author_coefficient"><?php $this->e('Author coefficient'); ?></label></th>
						<td>
							<?php printf($this->_('Default %d%%: '), $this->author_margin); ?>
							<input type="text" class="small-text" name="reward_author_coefficient" id="reward_author_coefficient" value="<?php echo esc_attr(get_user_meta($user->ID, $this->author_personal_margin, true)); ?>" /><strong><?php $this->e('* MUST BE FLOAT'); ?></strong>
						</td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
			<?php
		endif; 
	}
	
	/**
	 * Update user personal setting
	 * @param int $user_id
	 */
	public function profile_update($user_id){
		if(isset($_REQUEST['_lwppersonalpromotion'], $_REQUEST['reward_personal_coefficient']) && wp_verify_nonce($_REQUEST['_lwppersonalpromotion'], 'lwp_personal_promotion_ratio')){
			if(empty($_REQUEST['reward_personal_coefficient'])){
				delete_user_meta($user_id, $this->promotion_personal_margin);
			}else{
				update_user_meta($user_id, $this->promotion_personal_margin, (float)$_REQUEST['reward_personal_coefficient']);
			}
		}
		if(isset($_REQUEST['_lwppersonalauthor'], $_REQUEST['reward_author_coefficient']) && wp_verify_nonce($_REQUEST['_lwppersonalauthor'], 'lwp_personal_author_ratio')){
			if(empty($_REQUEST['reward_author_coefficient'])){
				delete_user_meta($user_id, $this->author_personal_margin);
			}else{
				update_user_meta($user_id, $this->author_personal_margin, (float)$_REQUEST['reward_author_coefficient']);
			}
		}
	}
	
	/**
	 * Return currently set promotion margin
	 * @global object $post
	 * @param object $post
	 * @return int 
	 */
	public function get_current_promotion_margin($post = null){
		if(is_null($post)){
			global $post;
		}else{
			$post = get_post($post);
		}
		$personal_setting = get_post_meta($post->ID, $this->promotion_margin_key, true);
		return (int)($personal_setting ? $personal_setting : $this->promotion_margin);
	}
	
	/**
	 * Save promotion on current margin
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $transaction_id
	 * @param int $user_id 
	 */
	public function save_promotion_log($transaction_id, $user_id, $start_post_id, $referer){
		if($this->promotable){
			global $lwp, $wpdb;
			//TODO: fix if cart is implemented
			$post_ids = array($wpdb->get_var($wpdb->prepare("SELECT book_id FROM {$lwp->transaction} WHERE ID = %d", $transaction_id)));
			//Check if personal settign is registered
			$user_coefficient = get_user_meta($user_id, $this->promotion_personal_margin, true);
			if(!$user_coefficient){
				$user_coefficient = 1;
			}
			//Calculate all promotion
			$total = 0;
			foreach($post_ids as $post_id){
				$ratio = min($this->get_current_promotion_margin($post_id) * $user_coefficient, $this->promotion_max);
				$total += round(lwp_price($post_id) * $ratio / 100);
			}
			//Save promotion log
			$wpdb->insert(
				$lwp->promotion_logs,
				array(
					'transaction_id' => $transaction_id,
					'user_id' => $user_id,
					'reason' => LWP_Promotion_TYPE::PROMOTION,
					'estimated_reward' => $total,
					'start_post_id' => $start_post_id,
					'referer' => $referer
				),
				array('%d', '%d', '%s', '%d', '%d', '%s')
			);
		}
	}
	
	/**
	 * Save author promotion log
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $transaction_id 
	 */
	public function save_author_log($transaction_id){
		if($this->rewardable){
			global $lwp, $wpdb;
			//TODO: fix if cart is implemented
			$post_ids = array($wpdb->get_var($wpdb->prepare("SELECT book_id FROM {$lwp->transaction} WHERE ID = %d", $transaction_id)));
			//Loop on each post
			$result = array();
			foreach($post_ids as $post_id){
				$post_author = $wpdb->get_var($wpdb->prepare("SELECT post_author FROM {$wpdb->posts} WHERE ID = %d", $post_id));
				$personal_margin = get_user_meta($post_author, $this->promotion_personal_margin, true);
				$margin = $personal_margin ? $personal_margin : $this->author_margin;
				if(isset($result[$post_author])){
					$result[$post_author] += (int)(lwp_price($post_id) * $margin / 100);
				}else{
					$result[$post_author] = (int)(lwp_price($post_id) * $margin / 100);
				}
			}
			//Save
			foreach($result as $user_id => $price){
				$wpdb->insert(
					$lwp->promotion_logs,
					array(
						'transaction_id' => $transaction_id,
						'user_id' => $user_id,
						'reason' => LWP_Promotion_TYPE::SELL,
						'estimated_reward' => $price
					),
					array("%d", "%d", "%s", "%d")
				);
			}
		}
	}
	
	/**
	 * Create request and enquuee
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @return int\WP_Error 
	 */
	public function make_request($user_id){
		global $lwp, $wpdb;
		//check if requestable
		if(!$this->is_enabled()){
			return new WP_Error('fail', $this->_('You cannot make payment request.'));
		}
		//Check if sutisfies minimum requirements
		
		if(!$this->is_enabled()){
			return new WP_Error('fail', $this->_('You cannot make payment request.').' '.sprintf($lwp->_('Minimum request must be more than %d (%s)'), $this->minimum_request, lwp_currency_code()));
		}
	}
	
	
	/**
	 * 
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @param string $status
	 * @param string $from
	 * @param string $to
	 * @return int 
	 */
	public function get_total_reward($user_id, $status = LWP_Payment_Status::SUCCESS, $from = null, $to = null){
		global $lwp, $wpdb;
		$sql = <<<EOS
			SELECT SUM(r.estimated_reward) FROM {$lwp->promotion_logs} AS r
			INNER JOIN {$lwp->transaction} AS t
			ON r.transaction_id = t.ID
EOS;
		$where = array(
			$wpdb->prepare("r.user_id = %d", $user_id)
		);
		if(false !== array_search($status, LWP_Payment_Status::get_all_status())){
			$where[] = $wpdb->prepare("t.status = %s", $status);
		}
		if($from){
			$where[] = $wpdb->prepare("t.registered >= %s", $from);
		}
		if($to){
			$where[] = $wpdb->prepare("t.registered <= %s", $to);
		}
		$sql .= ' WHERE '.implode(' AND ', $where);
		return (int)$wpdb->get_var($sql);
	}
	
	/**
	 * Returns total request amount between specified period
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @param string $status
	 * @param string $from
	 * @param string $to
	 * @return int 
	 */
	public function get_requested_reward($user_id, $status = LWP_Payment_Status,$from = null, $to = null){
		global $lwp, $wpdb;
		$sql = <<<EOS
			SELECT SUM(r.price) FROM {$lwp->reward_logs} AS r
			WHERE user_id = %d
EOS;
		if(false !== array_search($status, LWP_Payment_Status::get_all_status())){
			$sql .= $wpdb->prepare(" AND r.status = %s", $status);
		}
		if($from){
			$sql .= $wpdb->prepare(" AND r.registered >= %s", $from);
		}
		if($to){
			$sql .= $wpdb->prepare(" AND r.registered <= %s", $to);
		}
		return (int)$wpdb->get_var($wpdb->prepare($sql, $user_id));
	}
	
	/**
	 * Returns payment notice 
	 * @return string
	 */
	public function get_notice(){
		$notice = $this->notice;
		foreach($this->get_notice_placeholders() as $placeholder => $desc){
			if(false !== strpos($notice, $placeholder)){
				switch($placeholder){
					case '%limit%':
						$replace = $this->request_limit;
						break;
					case '%payment_month%':
						$replace = $this->pay_month_after;
						break;
					case '%payment_day%':
						$replace = $this->pay_at_day;
						break;
					case '%min%':
						$replace = $this->minimum_request;
						break;
					default:
						$replace = false;
						break;
				}
				if($replace){
					$notice = str_replace($placeholder, $replace, $notice);
				}
			}
		}
		return apply_filters('lwp_reward_notice', $notice);
	}
	
	/**
	 * Returns 
	 * @return string
	 */
	public function get_raw_notice(){
		return $this->notice;
	}
	
	/**
	 * Returns array of placeholders for notice
	 * @return array
	 */
	public function get_notice_placeholders(){
		return array(
			'%limit%' => $this->_('Payment request limit'),
			'%payment_month%' => $this->_('Payment after this month'),
			'%payment_day%' => $this->_('Payment at this day'),
			'%min%' => $this->_('Minimum required amount for request')
		);
	}
	
	/**
	 * Returns if user is requesting
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @return boolean 
	 */
	public function is_user_requesting($user_id){
		global $lwp, $wpdb;
		if(date('j') > $this->request_limit){
			//Paytime limit is exceeded, limit is nextmonth
			$month_from = date('n');
			$month_to = date('n') + 1;
		}else{
			//Payment limit isnot exceeded, limit is this month
			$month_from = date('n') - 1;
			$month_to = date('n');
		}
		$year_from = date('Y');
		$year_to = date('Y');
		if($month_from < 1){
			$year_from--;
			$month_from += 12;
		}
		if($month_to > 12){
			$year_to++;
			$month_to -= 12;
		}
		$to = date("Y-m-d H:i:s", mktime(23, 59, 59, $month_to, $this->request_limit, $year_to));
		$from = date("Y-m-d H:i:s", mktime(00, 00, 00, $month_from, $this->request_limit, $year_from));
		$sql = <<<EOS
			SELECT ID FROM {$lwp->reward_logs}
			WHERE user_id = %d
			  AND registered <= %s
			  AND registered >= %s
EOS;
		return (boolean)$wpdb->get_var($wpdb->prepare($sql, $user_id, $to, $from));
	}

	/**
	 * Get user's requestable amount
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @param boolean $exclude_payment_queue
	 * @return float 
	 */
	public function user_rest_amount($user_id, $exclude_payment_queue = false){
		global $lwp, $wpdb;
		//Get payed queue
		$sql = <<<EOS
			SELECT SUM(price) FROM {$lwp->reward_logs}
			WHERE user_id = %d
EOS;
		if($exclude_payment_queue){
			$sql .= $wpdb->prepare(" AND status = %s", LWP_Payment_Status::SUCCESS);
		}
		$payed = $wpdb->get_var($wpdb->prepare($sql, $user_id));
		//Get promotion fee
		$sql = <<<EOS
			SELECT SUM(p.estimated_reward)
			FROM {$lwp->promotion_logs} AS p
			INNER JOIN {$lwp->transaction} AS t
			ON p.transaction_id = t.ID
			WHERE p.user_id = %d
			  AND t.status = %s
EOS;
		$price = $wpdb->get_var($wpdb->prepare($sql, $user_id, LWP_Payment_Status::SUCCESS));
		//Calculate
		return (float)($price - $payed);
	}
	
	/**
	 * Reward amount which user have got
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 * @param int $user_id
	 * @return float 
	 */
	public function user_reward_amount($user_id){
		global $lwp, $wpdb;
		$sql = <<<EOS
			SELECT SUM(price) FROM {$lwp->reward_logs}
			WHERE user_id = %d AND status = %s
EOS;
		return $wpdb->get_var($wpdb->prepare($sql, $user_id, LWP_Payment_Status::SUCCESS));
	}
	
	/**
	 * Rest value to get paid
	 * @param int $user_id
	 * @return float
	 */
	public function required_payment_for_user($user_id){
		return $this->minimum_request - $this->user_rest_amount($user_id);
	}
	
	/**
	 * Returns next pay day
	 * @param string $format Date format
	 * @return string
	 */
	public function next_pay_day($format = null){
		if(is_null($format)){
			$format = get_option('date_format');
		}
		$year = date('Y');
		$month = date('n') + $this->pay_month_after;
		if(date('j') > $this->request_limit){
			$month++;
		}
		if($month > 12){
			$year++;
			$month -= 12;
		}
		return date($format, mktime(0, 0, 0, $month, $this->pay_at_day, $year));
	}
	
	/**
	 * Returns promotion link
	 * @param int $post_id
	 * @param int $user_id
	 * @return string 
	 */
	public function get_promotion_link($post_id, $user_id){
		$base = get_permalink($post_id);
		$glue = (false !== strpos($base, '?')) ? '?' : '&';
		$base .= $glue.'_lwpp='.$user_id;
		return $base;
	}
}