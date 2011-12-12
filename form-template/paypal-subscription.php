<?php if(!$transaction): ?>
<p class="message notice">
	<?php printf($this->_("%s's subscription plans are below."), get_bloginfo('name')); ?>
</p>
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
						<?php echo $counter; ?>
					<?php endif; ?>
				</th>
				<td>
					<label for="lwp-id-<?php echo $s->ID; ?>">
						<?php echo $s->post_title; ?>
					</label>
				</td>
				<td><?php echo $s->expires.' '; $this->e('Days');  ?></td>
				<td><?php echo number_format($s->price)." (".$this->option['currency_code'].")"; ?></td>
				<td><?php echo apply_filters('the_content', $s->post_content); ?></td>	
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
<?php elseif(preg_match("/lwp/", $_SERVER["HTTP_REFERER"])): ?>
<p>
	<a class="button" href="<?php echo $archive; ?>"><?php $this->e("Return"); ?></a>
</p>
<?php else: ?>
<p>
	<a class="button" href="#" onclick="window.history.back(); return false;"><?php $this->e("Return"); ?></a>
</p>
<?php endif; ?>