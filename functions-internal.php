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
 * Callback function for lwp_list_cancel_condition
 * @global Literally_WordPress $lwp
 * @param string $limit
 * @param int $days_before
 * @param string $ratio 
 * @param int $total
 * @param int $counter
 * @param boolean $is_current
 */
function _lwp_show_condition($limit, $days_before, $ratio, $total, $counter, $is_current){
	global $lwp;
	$limit = strtotime($limit) - ($days_before * 60 * 60 * 24);
	$returns = '';
	$classes = array();
	if(current_time('timestamp') > $limit){
		$classes[] = "outdated";
	}
	if($is_current){
		$classes[] = 'current';
	}
	if(preg_match("/%$/", $ratio)){
		$returns = sprintf($lwp->_('Refunds %s of bought price'), $ratio);
	}elseif(preg_match("/^-[0-9]+$/", $ratio)){
		$returns = sprintf($lwp->_('Refunds bought price - %s'), number_format($ratio * -1).' '.lwp_currency_code());
	}else{
		$returns = sprintf($lwp->_('Refunds %s'), number_format($ratio).' '.lwp_currency_code());
	}
	printf('<tr class="%1$s"><th scope="row">%2$s</th><td>%3$s</td></tr>',
			implode(' ', $classes),
			sprintf($lwp->_('Until %s'), date_i18n(get_option('date_format'), $limit)),
			$returns);
}

/**
 * Disaply ticket list 
 * @global Literally_WordPress $lwp
 * @param int $parent_id
 */
function _lwp_show_ticket($parent_id){
	global $lwp;
	$total = lwp_get_ticket_stock(true);
	$stock = lwp_get_ticket_stock();
	if($stock < 5){
		$stock_class = ' few';
	}elseif($stock < 10){
		$stock_class = ' some';
	}else{
		$stock_class = '';
	}
	?>
		<dt class="lwp-ticket-title lwp-button" id="lwp-ticket-title-<?php the_ID(); ?>">
		
			<?php if(current_time('timestamp') >= lwp_selling_limit('U', $parent_id)): ?>
				<span class="lwp-ticket-soldout"><?php $lwp->e('Sold Out'); ?></span>
			<?php elseif($stock <= 0): ?>
				<?php if(function_exists('lwp_has_cancel_list') && lwp_has_cancel_list($parent_id)): ?>
					<?php if(lwp_is_user_waiting()): ?>
						<span class="lwp-ticket-soldout"><?php $lwp->e('You are waiting'); ?></span>
					<?php else: ?>
						<a class="button" href="<?php echo lwp_cancel_list_url(); ?>" onclick="if(!confirm('<?php echo esc_attr(sprintf($lwp->_('Are you sure to register on waiting list for %2$s of %1$s?'), get_the_title($parent_id), get_the_title())); ?>')) return false;" rel="noindex,nofollow"><?php $lwp->e('Wait for Cancellation') ?></a>
					<?php endif; ?>
				<?php else: ?>
					<span class="lwp-ticket-soldout"><?php $lwp->e('Sold Out'); ?></span>
				<?php endif; ?>
			<?php else: ?>
				<a class="button" href="<?php echo esc_url(lwp_buy_url());?>" rel="noindex,nofollow">
					<?php $lwp->e('Buy Now'); ?>
				</a>
			<?php endif; ?>
					
			<?php the_title(); ?>
			<span class="lwp-ticket-stock<?php echo $stock_class ?>">
				<?php printf($lwp->_('(%s tickets)'), number_format($total)); ?>
			</span> - 
			<strong class="lwp-ticket-price"><?php lwp_the_price();?></strong>
			<?php if(lwp_on_sale()): ?>
				<del class="lwp-ticket-discount"><?php echo lwp_currency_symbol().number_format(lwp_original_price()); ?></del><br />
				<small class="lwp-ticket-sale"><?php printf($lwp->_('Discount price untill <strong>%s</strong>'), lwp_campaign_end()); ?></small>
			<?php endif; ?>
			<?php if(lwp_is_owner()): ?>
				<br />
				<small class="lwp-ticket-owner"><?php printf($lwp->_('You have this ticket. See <a href="%1$s">Purchase History</a> or <a href="%2$s">Ticket List</a>.'), lwp_history_url(), lwp_ticket_url($parent_id)); ?></small>
			<?php endif; ?>
				
		</dt>
		<dd class="lwp-ticket-content" id="lwp-ticket-content-<?php the_ID(); ?>">
			<?php the_content(); ?>
		</dd>
	<?php
}

/**
 * Show user list
 * @global Literally_WordPress $lwp
 * @param WP_User $user
 * @param int $post_id 
 */
function _lwp_list_participant($user, $post_id){
	global $lwp;
	?>
		<li class="lwp-participant participant-<?php echo $user->ID?>">
			<?php echo get_avatar($user->user_email, 24);?>
			<?php echo $user->display_name; ?>さん
		</li>
	<?php
}