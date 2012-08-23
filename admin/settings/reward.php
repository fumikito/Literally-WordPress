<h3>
	<?php printf($this->_('About %s'), $this->_('Reward')); ?>
	<small class="experimental"><?php $this->e('EXPERIMENTAL'); ?></small>
</h3>
<p class="description">
	<?php $this->e('You can allow your users to promote your product and reward for them.'); ?>
	<?php $this->e('This is so called affiliate system, but very experimental. Please be careful to your regal responsibility to your partners.'); ?>
</p>

<h3><?php $this->e('Setting'); ?></h3>
<table class="form-table">
	<tbody>
		<tr>
			<th valign="top"><label><?php $this->e('Reward for promoter'); ?></label></th>
			<td>
				<label><input type="radio" name="reward_promoter" value="0" <?php if(!$this->option['reward_promoter']) echo 'checked="checked"'; ?> /><?php $this->e('Disable'); ?></label><br />
				<label><input type="radio" name="reward_promoter" value="1" <?php if($this->option['reward_promoter']) echo 'checked="checked"'; ?> /><?php $this->e('Enable'); ?></label>
				<p class="description">
					<?php $this->e('If you wish to reward for your user who promote your product, enable this. Your users can promote your product and can manage there sales statistic.'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="reward_promotion_margin"><?php $this->e('Reward margin for promotion'); ?></label></th>
			<td>
				<input type="text" class="small-text" name="reward_promotion_margin" id="reward_promotion_margin" value="<?php echo esc_attr($this->option['reward_promotion_margin']); ?>" />%
				<p class="description">
					<?php $this->e('Specify back margin in percentage. This is global setting for promotion and you can override on each product individually.'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="reward_promotion_max"><?php $this->e("Maximum promotion margin"); ?></label></th>
			<td>
				<input type="text" class="small-text" name="reward_promotion_max" id="reward_promotion_max" value="<?php echo esc_attr($this->option['reward_promotion_max']); ?>" />%
				<p class="description">
					<?php $this->e("You can override margin per posts and users. In some case, margin exceeds 100%. You can set limit to avoid paying unexpected amount of reward."); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><label><?php $this->e('Reward for author'); ?></label></th>
			<td>
				<label><input type="radio" name="reward_author" value="0" <?php if(!$this->option['reward_author']) echo 'checked="checked"'; ?> /><?php $this->e('Disable'); ?></label><br />
				<label><input type="radio" name="reward_author" value="1" <?php if($this->option['reward_author']) echo 'checked="checked"'; ?> /><?php $this->e('Enable'); ?></label>
				<p class="description">
					<?php $this->e('If you wish to reward for your authors, enable this. Your authors can see how much they sold their contents on sales report dashboard.'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="reward_author_margin"><?php $this->e('Reward margin for author'); ?></label></th>
			<td>
				<input type="text" class="small-text" name="reward_author_margin" id="reward_author_margin" value="<?php echo esc_attr($this->option['reward_author_margin']); ?>" />%
				<p class="description">
					<?php $this->e('Specify back margin in percentage. This is global setting for author and you can override on each author individually.'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="reward_author_max"><?php $this->e("Maximum author margin"); ?></label></th>
			<td>
				<input type="text" class="small-text" name="reward_author_max" id="reward_author_max" value="<?php echo esc_attr($this->option['reward_author_max']); ?>" />%
				<p class="description">
					<?php $this->e("You can override margin per authors. In some case, margin exceeds 100%. You can set limit to avoid paying unexpected amount of reward."); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="reward_minimum"><?php $this->e("Reward minimum amount"); ?></label></th>
			<td>
				<input type="text" class="small-text" name="reward_minimum" id="reward_minimum" value="<?php echo esc_attr($this->option['reward_minimum']); ?>" /><?php echo lwp_currency_code();?>
				<p class="description">
					<?php $this->e("Your partners can request payment for reward if reward amount is above this value."); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><label><?php $this->e("Reward Request"); ?></label></th>
			<td>
				<?php
					$select = '<select name="reward_pay_after_month">';
					foreach(range(0,2) as $i){
						$select .= '<option value="'.$i.'"';
						if($this->option['reward_pay_after_month'] == $i){
							$select .= ' selected="selected"';
						}
						$select .= '>'.($i ? sprintf($this->_('%d month after'), $i) : $this->_('That month'));
						$select .= '</option>';
					}
					$select .= '</select>';
					printf(
						$this->_('If reward amount exceeds %1$d (%2$s), user can request payment once until %3$s on every month. Reward will be paid at %4$s on %5$s'),
						$this->option['reward_minimum'], lwp_currency_code(), 
						'<input type="text" class="small-text" name="reward_request_limit" value="'.$this->option['reward_request_limit'].'" />',
						'<input type="text" class="small-text" name="reward_pay_at" value="'.$this->option['reward_pay_at'].'" />',
						$select
					);
				?>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="reward_notice"><?php $this->e("Reward Notice"); ?></label></th>
			<td>
				<textarea cols="80" rows="5" name="reward_notice" id="reward_notice"><?php echo esc_html($this->reward->get_raw_notice());  ?></textarea>
				<p class="description">
					<?php $this->e('This will be displayed on profile page. You can use placeholders: '); ?>
					<?php foreach($this->reward->get_notice_placeholders() as $placeholder => $desc): ?>
						<code><?php echo $placeholder;?></code>
						<small><?php echo esc_html($desc); ?></small> | 
					<?php endforeach; ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="reward_contact"><?php $this->e("Contact information for reward"); ?></label></th>
			<td>
				<textarea cols="80" rows="5" name="reward_contact" id="reward_contact" placeholder="<?php $this->e('i.e. To get reward payed, you have to enter bank account. All information for transfer is required.'); ?>"><?php echo esc_html($this->reward->get_contact_description());  ?></textarea>
				<p class="description">
					<?php $this->e('If you need some additional information on payment besides WordPress Default(i.e. Bank account required), enter description. If it is not blank, This description will be displayed on payment request page.'); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>