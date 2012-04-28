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
			"reward_author" => $this->rewardable,
			"reward_author_margin" => $this->author_margin,
			"reward_minimum" => $this->minimum_request
		), $option);
		$this->promotable = (boolean) $option['reward_promoter'];
		$this->promotion_margin = (int) $option['reward_promotion_margin'];
		$this->rewardable = $option['reward_author_margin'];
		$this->author_margin = $option['reward_author'];
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
}
