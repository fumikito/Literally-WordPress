<?php /* @var $this Literally_WordPress */ ?>
<?php if(!$transaction): ?>
	<p class="message notice"><?php printf($this->_("%s's subscription plans are below."), get_bloginfo('name')); ?></p>

	<?php if(lwp_is_subscriber()): $subscription = $this->subscription->get_subscription_owned_by(); ?>
		<p class="message success">
			<?php printf($this->_('You have subscription plan \'%s\'.'), $subscription->post_title); ?>
			<?php if($subscription->expires == '0000-00-00 00:00:00'): ?>
				<?php $this->e('Your subscription is unlimited.'); ?>
			<?php else: ?>
				<?php printf(
							$this->_('You got it at <strong>%s</strong> and it will be expired at <strong>%s</strong>.'),
							mysql2date(get_option("date_format"), $subscription->updated),
							mysql2date(get_option("date_format"), $subscription->expires)
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
			<?php $counter = 0; foreach($subscriptions as $s): $counter++; ?>
			<tr>
				<th>
					<?php if($transaction): ?>
						<input type="radio" name="lwp-id" id="lwp-id-<?php echo $s->ID; ?>" value="<?php echo $s->ID; ?>" <?php if($counter == 1) echo 'checked="checked" '; ?>/>
					<?php else: ?>
						<?php if(!is_user_logged_in()): ?>
							<?php echo $counter; ?>
						<?php else: ?>
							<?php if($this->subscription->is_subscriber() == $s->ID): ?>
								<img src="<?php echo $this->url; ?>/assets/icon-check-on.png" width="32" heigth="32" alt="ON" />
							<?php else: ?>
								<img src="<?php echo $this->url; ?>/assets/icon-check-off.png" width="32" heigth="32" alt="OFF" />
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				</th>
				<td>
					<label for="lwp-id-<?php echo $s->ID; ?>">
						<?php echo $s->post_title; ?>
					</label>
				</td>
				<td><?php echo $s->expires.' '; $this->e('Days');  ?></td>
				<td><?php echo number_format($s->price)." (".$this->option['currency_code'].")"; ?></td>
				<td><?php echo wpautop($s->post_content); ?></td>	
			</tr>
			<?php endforeach; ?>
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
		<a class="button" href="#" onclick="window.close(); return false;"><?php $this->e("Close"); ?></a>
	<?php elseif(preg_match("/lwp/", $_SERVER["HTTP_REFERER"])): ?>
		<a class="button" href="<?php echo $archive; ?>"><?php $this->e("Return"); ?></a></p>
	<?php else: ?>
		<a class="button" href="#" onclick="window.history.back(); return false;"><?php $this->e("Return"); ?></a>
	<?php endif; ?>
	</p>
	
<?php endif; ?>