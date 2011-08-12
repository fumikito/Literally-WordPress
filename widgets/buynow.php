<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of buynow
 *
 * @author guy
 */
class lwpBuyNow extends WP_Widget{
	
	/**
	 * コンストラクタ
	 * @global Literally_WordPress $lwp 
	 */
	public function lwpBuyNow(){
		global $lwp;
		parent::WP_Widget(false, $lwp->_('LWP Buy Now Widget'), array(
			'description' => $lwp->_('Displays BuyNow button if post is payable.')
		));
	}
	
	/**
	 * ウィジェットを出力する
	 * @global Literally_WordPress $lwp
	 * @param array $args
	 * @param array $instance 
	 * @return void
	 */
	public function widget($args, $instance){
		global $lwp;
		extract($args);
		extract($instance);
		if($post_id && !empty($post_id)){
			$post = wp_get_single_post($post_id);
		}elseif(!is_singular()){
			return;
		}else{
			global $post;
		}
		//購入可能じゃなかったら終了
		if(lwp_is_free(true, $post)){
			return;
		}
		
		echo <<<EOS
			$before_widget
			{$before_title}{$title}{$after_title}
			<div class="lwpwidget-buynow">
EOS;
		?>
		<strong class="lwp-buynow-title"><?php echo apply_filters('the_title', $post->post_title); ?></strong>
		<?php if($timer): ?>
			<?php echo lwp_campaign_timer($post); ?>
		<?php endif; ?>
		<div class="price">
			<?php if(lwp_on_sale($post)): ?>
				<p><?php echo lwp_currency_symbol($post).number_format(lwp_price($post));?></p>
				<del><?php echo lwp_currency_symbol($post).number_format(lwp_original_price($post))?></del>
				<small><?php echo lwp_discout_rate($post); ?></small>
			<?php  else: ?>
				<p><?php echo lwp_currency_symbol($post).number_format(lwp_price($post));?></p>
			<?php endif; ?>
		</div>
		<div class="lwp-buynow-btn">
			<?php 
				if($src == 'link'){
					echo lwp_buy_now($post, null);
				}elseif(empty($src)){
					echo lwp_buy_now($post, false);
				}else{
					echo lwp_buy_now($post, $src);
				}
			?>
		</div>
		<?php
		echo <<<EOS
			</div>
			$after_widget
EOS;
	}
	
	/**
	 * フォームの情報更新する
	 * @global Literally_WordPress $lwp
	 * @param array $newinstance
	 * @param array $oldinstance
	 * @return array
	 */
	public function update($newinstance, $oldinstance){
		global $lwp;
		return $newinstance;
	}
	
	
	/**
	 * フォームを出力する
	 * @global Literally_WordPress $lwp
	 * @param array $instance 
	 * @return void
	 */
	public function form($instance){
		global $lwp;
		extract(shortcode_atts(array(
			'title' => $lwp->_('Buy Now'),
			'post_id' => '',
			'src' => '',
			'timer' => '1'
		), $instance));
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php $lwp->e('Widgets Title'); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('post_id'); ?>"><?php $lwp->e('Post ID to display'); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('post_id'); ?>" name="<?php echo $this->get_field_name('post_id'); ?>" value="<?php echo $post_id; ?>" />
			<span class="description">
				<?php $lwp->e('Specify post\'s ID to display button outside singular page(ex. In category page) or leave it blank.'); ?>
			</span>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('src'); ?>"><?php $lwp->e('Original Image src'); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('src'); ?>" name="<?php echo $this->get_field_name('src'); ?>" value="<?php echo $src; ?>" />
			<span class="description">
				<?php $lwp->e('Specify your original "BuyNow" button src. If you don\'t have, leave it blank and PayPal button will be displayed. Put word \'link\', button will be displayed with normal link tag.'); ?>
			</span>
		</p>
		<p>
			<label><?php $lwp->e('Show Timer'); ?></label><br />
			<label>
				<input type="radio" name="<?php echo $this->get_field_name('timer'); ?>" value="1" <?php if($timer == 1) echo ' checked="checked"'?>/>
				<?php $lwp->e('Show'); ?>
			</label><br />
			<label>
				<input type="radio" name="<?php echo $this->get_field_name('timer'); ?>" value="0" <?php if($timer != 1) echo ' checked="checked"'?>/>
				<?php $lwp->e('Don\'t show'); ?>
			</label><br />
			<span class="description">
				<?php $lwp->e('Timer will be displayed if post is on sale.'); ?>
			</span>
		</p>
		<?php
	}
}
