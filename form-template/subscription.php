<?php
/* @var $lwp Literally_WordPres */ /* @var $this LWP_Form */ /* @var $subscriptions WP_Query */
global $lwp;
?>
<?php if(!$transaction): ?>
	<p class="message notice"><?php printf($this->_("%s's subscription plans are below."), get_bloginfo('name')); ?></p>

	<?php if(lwp_is_subscriber()):  ?>
		<p class="message success">
			<?php printf($this->_('You have subscription plan \'%s\'.'), $owned_subscription->post_title); ?>
			<?php if($owned_subscription->expires == '0000-00-00 00:00:00'): ?>
				<?php $this->e('Your subscription is unlimited.'); ?>
			<?php else: ?>
				<?php printf(
							$this->_('You got it at <strong>%s</strong> and it will be expired at <strong>%s</strong>.'),
							mysql2date(get_option("date_format"), $owned_subscription->updated),
							mysql2date(get_option("date_format"), $owned_subscription->expires)
				); ?>
			<?php endif; ?>
		</p>
	<?php else: ?>
		<p class="message warning"><?php $this->e("You have no subscription plan."); ?></p>
	<?php endif; ?>
<?php else: ?>
<p class="message notice">
	<?php printf($this->_("Please select %s's subscription plan from the list below."), get_bloginfo('name')); ?>
</p>
<form method="get" action="<?php echo lwp_endpoint(); ?>">
	<input type="hidden" name="lwp" value="buy" />
<?php endif; ?>
	<table class="form-table">
		<tbody>
			<?php $counter = 0; if($subscriptions->have_posts()) while($subscriptions->have_posts()):  $subscriptions->the_post();  $counter++; ?>
			<tr>
				<th>
					<?php if($transaction): ?>
						<input type="radio" name="lwp-id" id="lwp-id-<?php the_ID(); ?>" value="<?php the_ID(); ?>" <?php if($counter == 1) echo 'checked="checked" '; ?>/>
					<?php else: ?>
						<?php if(!is_user_logged_in()): ?>
							<?php echo $counter; ?>
						<?php else: ?>
							<?php echo ($lwp->subscription->is_subscriber() == get_the_ID()) ? $on_icon : $off_icon ; ?>
						<?php endif; ?>
					<?php endif; ?>
				</th>
				<td>
					<label for="lwp-id-<?php the_ID(); ?>">
						<?php the_title(); ?>
					</label>
				</td>
				<td><?php echo get_post_meta(get_the_ID(), '_lwp_expires', true).' '; $this->e('Days');  ?></td>
				<td><?php echo lwp_the_price()." (".lwp_currency_code().")"; ?></td>
				<td><?php the_content(); ?></td>	
			</tr>
			<?php endwhile;wp_reset_query(); ?>
		</tbody>
	</table>
<?php if($transaction): ?>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php $this->e('Next &raquo;'); ?>" />
	</p>
</form>
<p>
	<a class="button" href="#" onclick="window.history.back(); return false;"><?php $this->e("Cancel"); ?></a>
</p>
<?php else: ?>

	<p>
	<?php if(isset($_GET['popup']) && $_GET['popup']): ?>
		<a class="button" href="<?php bloginfo('url'); ?>" onclick="window.close(); return false;"><?php $this->e("Close"); ?></a>
	<?php elseif(preg_match("/lwp/", $_SERVER["HTTP_REFERER"])): ?>
		<a class="button" href="<?php echo esc_attr($archive); ?>"><?php $this->e("Return"); ?></a></p>
	<?php elseif(isset($_GET['back']) && $_GET['back']): ?>
		<a class="button" href="<?php echo esc_attr($url); ?>"><?php $this->e("Return"); ?></a></p>
	<?php else: ?>
		<a class="button" href="<?php bloginfo('url'); ?>" onclick="window.history.back(); return false;"><?php $this->e("Return"); ?></a>
	<?php endif; ?>
	</p>
	
<?php endif; ?>