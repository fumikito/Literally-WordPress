<?php
/**
 * Internal functions
 * @package literally_wordpress
 * @since 0.8.10
 */

/**
 * 電子書籍のカスタムフィールドを返す
 * 
 * @internal
 * @param string $key
 * @param int|object (optional) ループ内で使用した場合は現在のポスト
 * @param boolean $single (optional) 配列で取得する場合はfalse
 * @return string
 */
function _lwp_post_meta($key, $post = null, $single = true)
{
	if($post){
		if(is_numeric($post)){
			$post_id = $post;
		}else{
			$post_id = $post->ID;
		}
	}elseif(is_null($post)){
		global $post;
		$post_id = $post->ID;
	}
	if(!is_numeric($post_id))
		return null;
	else
		return get_post_meta($post_id, $key, $single);
}

/**
 * 
 * @internal
 * @global Literally_WordPress $lwp
 * @param type $total
 * @param type $current 
 */
function _lwp_show_indicator($total = null, $current = null){
	global $lwp;
	$total = (int) $total;
	$current = (int) $current;
	if($total == 0 || $current == 0){
		return;
	}
	$ratio = $total > 0 ? round($current / $total * 100) : 0;
	$text = sprintf($lwp->_('<strong>%1$d</strong> of %2$d Steps'), $current, $total);
	$markup = <<<EOS
<div class="lwp-indicator">
	<div class="lwp-indicator-progress-bg">
		<div class="lwp-indicator-progress" style="width:{$ratio}%"></div>
	</div>
	<span class="lwp-indicator-text">{$text}</span>
</div>
EOS;
	echo apply_filters('lwp_show_indicator', $markup, $total, $current);
}

/**
 * Disaply ticket list 
 * @global Literally_WordPress $lwp
 * @param int $parent_id
 */
function _lwp_show_ticket($parent_id){
	global $lwp;
	?>
		<li class="ticket-<?php the_ID(); ?>">
			<div class="ticket-title">
				<strong><?php the_title(); ?></strong>
				&nbsp; <?php lwp_the_price();?>
			</div>
			<div class="ticket-content">
				<?php the_content(); ?>
				<p class="lwp-buy-ticket-buy">
					<?php echo lwp_buy_now();?>
				</p>
			</div>
		</li>
	<?php
}