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
	private $promotable = false;
	
	/**
	 * @var int
	 */
	private $promotion_margin = 0;
	
	/**
	 * @var int
	 */
	private $promotion_max = 90;
	
	/**
	 * @var boolean
	 */
	private $rewardable = false;
	
	/**
	 * @var int
	 */
	private $author_margin = 0;
	
	/**
	 * @var int
	 */
	private $author_max = 90;
	
	/**
	 * @var int
	 */
	private $minimum_request = 0;
	
	/**
	 * Metakey of postmeta
	 * @var string
	 */
	private $promotion_margin_key = '_lwp_promotion_margin';
	
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
			"reward_minimum" => $this->minimum_request
		), $option);
		$this->promotable = (boolean) $option['reward_promoter'];
		$this->promotion_margin = (int) $option['reward_promotion_margin'];
		$this->promotion_max = $option['reward_promotion_max'];
		$this->rewardable = $option['reward_author_margin'];
		$this->author_margin = $option['reward_author'];
		$this->author_max = $option['reward_author_max'];
		$this->minimum_request = $option['reward_minimum'];
		$this->enabled = (boolean)($this->promotable || $this->rewardable);
	}
	
	/**
	 * Register hooks
	 * @see Literally_WordPress_Common 
	 */
	protected function on_construct(){
		if($this->is_enabled()){
			add_action('lwp_payable_post_type_metabox', array($this, 'metabox_margin'), 10, 2);
			add_action('save_post', array($this, 'save_post'));
			add_action('edit_user_profile', array($this, 'edit_user_profile'));
			add_action("profile_update", array($this, "profile_update"), 10, 2);
		}
	}
	
	public function admin_init(){
		
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
							<input class="small-text" type="text" name="reward_personal_coefficient" id="reward_personal_coefficient" value="<?php echo esc_attr(get_user_meta($user->ID, $this->promotion_personal_margin, true)); ?>" /><strong><?php $this->e('* MUST BE FLOAT'); ?></strong>
						</td>
					</tr>
					<?php endif; ?>
					<?php if($this->rewardable && user_can($user, 'edit_posts')): wp_nonce_field('lwp_personal_author_ratio', '_lwppersonalauthor', false); ?>
					<tr>
						<th valign="top"><label for="reward_author_coefficient"><?php $this->e('Author coefficient'); ?></label></th>
						<td>
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
	 * @param WP_User $old_data 
	 */
	public function profile_update($user_id, $old_data){
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
}
