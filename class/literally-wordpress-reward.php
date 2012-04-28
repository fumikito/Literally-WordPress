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
	 * Setup option
	 * @see Literally_WordPress_Common
	 * @param array $option 
	 */
	protected function set_option($option = array()) {
		$option = shortcode_atts(array(
			"reward_promoter" => $this->promotable,
			"reward_promotion_margin" => $this->promotion_margin,
			"reward_author" => $this->rewardable,
			"reward_author_margin" => $this->author_margin
		), $option);
		$this->promotable = (boolean) $option['reward_promoter'];
		$this->promotion_margin = (int) $option['reward_promotion_margin'];
		$this->rewardable = $option['reward_author_margin'];
		$this->author_margin = $option['reward_author'];
		$this->enabled = (boolean)($this->promotable || $this->rewardable);
	}
	
	/**
	 * Register hooks
	 * @see Literally_WordPress_Common 
	 */
	protected function on_construct(){
		if($this->is_enabled()){
			
		}
	}
	
	public function admin_init(){
		
	}
}
