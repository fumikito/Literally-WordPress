<h3><?php printf($this->_('About %s'), $this->_("Subscription")); ?></h3>
<p class="description">
	<?php $this->e('You can provide member only contents by subscription plan. You can choose any price, any period.'); ?>
</p>
<h3><?php $this->e('Setting'); ?></h3>
<table class="form-table">
	<tbody>
		<tr>
			<th valign="top">
				<label><?php $this->e('Accept Subscription'); ?></label>
			</th>
			<td>
				<label><input type="radio" name="subscription" value="0" <?php if(!$this->option['subscription']) echo 'checked="checked"'; ?> /><?php $this->e('Disallow'); ?></label><br />
				<label><input type="radio" name="subscription" value="1" <?php if($this->option['subscription']) echo 'checked="checked"'; ?> /><?php $this->e('Allow'); ?></label>
			</td>
		</tr>
		<tr>
			<th valign="top"><?php $this->e('Registered Plans'); ?></th>
			<td>
				<?php printf(
					$this->_('You have %d subscription plans.'),
					$wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = %s", $this->subscription->post_type))
							);
				?>
				<?php printf($this->_('You can set it up <a href="%s">here</a>.'), admin_url('edit.php?post_type=lwp-subscription'));  ?>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label><?php $this->e('Subscription Post Type'); ?></label>
			</th>
			<td>
				<?php foreach(get_post_types('', 'object') as $post_type): if(false === array_search($post_type->name, array('revision', 'nav_menu_item', 'page', 'attachment', 'lwp_notification'))): ?>
					<label>
						<input type="checkbox" name="subscription_post_types[]" value="<?php echo $post_type->name; ?>" <?php if(false !== array_search($post_type->name, $this->option['subscription_post_types'])) echo 'checked="checked" '; ?>/>
						<?php echo $post_type->labels->name; ?>
					</label>&nbsp;
				<?php endif; endforeach; ?>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label><?php $this->e('Subscription Format'); ?></label>
			</th>
			<td>
				<label><input type="radio" name="subscription_format" value="more" <?php if($this->option['subscription_format'] == 'more') echo 'checked="checked"'; ?> /><?php $this->e("More tag"); ?></label><br />
				<label><input type="radio" name="subscription_format" value="nextpage" <?php if($this->option['subscription_format'] == 'nextpage') echo 'checked="checked"'; ?> /><?php $this->e("Nextpage Tag"); ?></label><br />
				<label><input type="radio" name="subscription_format" value="all" <?php if($this->option['subscription_format'] == 'all') echo 'checked="checked"'; ?> /><?php $this->e("All"); ?></label>
				<p class="description">
					<?php printf($this->_("If you choose More Tag or Nextpage Tag, non subscriber can see the content before it and the invitation message after it. If you choose all, non subscriber saw only the invitation message. You can customize the invitation message at <a href=\"%s\">Subscription</a>."), admin_url('admin.php')); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>
