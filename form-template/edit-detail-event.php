<?php /* @var $this LWP_Event */ ?>
<?php wp_nonce_field('lwp_event_detail', '_lwpeventnonce'); ?>

<h4><?php $this->e('Ticket Sale Setting');?></h4>
<table class="form-table" id="lwp-event-setting">
	<tbody>
		<tr>
			<th valign="top">
				<label for="event_selling_limit"><?php $this->e('Limit to sell');  ?></label>
			</th>
			<td>
				<input placeholder="ex. <?php echo date_i18n('Y-m-d'); ?>" type="text" id="event_selling_limit" name="event_selling_limit" class="date-picker" value="" />
				<span class="description">YYYY-MM-DD</span>
			</td>
		</tr>
		<tr>
			<th valign="top"><label><?php $this->e('Limit to cancel'); ?></label></th>
			<td>
				<ul id="cancel-date-list">
					<li class="no-cancelable-date"><?php $this->e('You do not specify cancel limit, so user cannot cancel.'); ?></li>
				</ul>
				<p>
					<?php
						printf(
							$this->_('User can cancel ticket %1$s days before selling limit. Refund is %2$s%%.'),
							'<input type="text" name="cancel_limit" class="small-text" value="" />',
							'<input type="text" name="cancel_ratio" class="small-text" value="" />'
						);
					?>
					<a id="lwp-cancel-add" class="button"><?php $this->e('Add'); ?></a>
				</p>
				<p class="description"><?php $this->e('If you specify cancelable date, user can cancel transaction after payment.'); ?></p>
			</td>
		</tr>
	</tbody>
</table>

<h4><?php $this->e('Ticket List');?></h4>
<table class="form-table">
	<thead>
		<tr>
			<th scope="col"><?php $this->e('Name'); ?></th>
			<th scope="col"><?php $this->e('Description'); ?></th>
			<th scope="col"><?php $this->e('Price'); ?></th>
			<th scope="col"><?php $this->e('Stock'); ?></th>
			<th scope="col"><?php $this->e('Limit'); ?></th>
			<th scope="col">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th scope="row">
				<input type="hidden" name="ticket[]" value="" />
				男２名
			</th>
			<td>このちけっとは...</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</tbody>
</table>

<h4><?php $this->e('Ticket form'); ?></h4>
<input type="hidden" name="ticket_id" value="0" />
<table class="form-table" id="lwp-event-edit-form">
	<tbody>
		<tr>
			<th valign="top"><label for="ticket_post_title"><?php $this->e('Ticket Name'); ?></label></th>
			<td>
				<input type="text" name="ticket_post_title" id="ticket_post_title" class="regular-text" value="" />
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="ticket_post_content"><?php $this->e('Ticket Description'); ?></label></th>
			<td>
				<textarea rows="5" type="text" name="ticket_post_content" id="ticket_post_content"></textarea>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="ticket_price"><?php $this->e('Ticket Price'); ?></label></th>
			<td>
				<input type="text" name="ticket_price" id="ticket_price" value="" /><?php echo lwp_currency_code();?>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="ticket_stock"><?php $this->e('Ticket Stock'); ?></label></th>
			<td>
				<input type="text" name="ticket_stock" id="ticket_stock" value="" />
			</td>
		</tr>
	</tbody>
</table>
<p class="submit">
	<a id="ticket_submit" class="button-primary" href="<?php echo admin_url('admin-ajax.php'); ?>"><?php $this->e('Submit'); ?></a>
	<a id="ticket_cancel" class="button" href="#"><?php $this->e('Cacnel'); ?></a>
</p>