<?php /* @var $this Literally_WordPress */ ?>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo admin_url('admin.php?page=lwp-refund'); ?>" class="nav-tab<?php if(!isset($_GET['status']) && !isset($_GET['option'])) echo ' nav-tab-active';?>">
		<?php $this->e(LWP_Payment_Status::REFUND_REQUESTING); ?>
		<?php if(($count = $this->refund_manager->on_queue_count())):  ?>
		<small class="tab-count"><?php echo $count; ?></small>
		<?php endif; ?>
	</a>
	<a href="<?php echo admin_url('admin.php?page=lwp-refund&status='.LWP_Payment_Status::REFUND); ?>" class="nav-tab<?php if(isset($_GET['status']) && $_GET['status'] == LWP_Payment_Status::REFUND) echo ' nav-tab-active';?>">
		<?php $this->e(LWP_Payment_Status::REFUND); ?>
	</a>
	<a href="<?php echo admin_url('admin.php?page=lwp-refund&option=message'); ?>" class="nav-tab<?php if(isset($_GET['option']) && $_GET['option'] == 'message') echo ' nav-tab-active';?>">
		<?php $this->e('Refund Messages'); ?>
	</a>
</h2>

<?php if(!isset($_GET['option']) || $_GET['option'] != 'message'):  ?>
<form method="get" action="<?php echo admin_url('admin.php'); ?>">
	<input type="hidden" name="page" value="lwp-refund" />
	<?php if(isset($_GET['status']) && $_GET['status'] == LWP_Payment_Status::REFUND): ?>
	<input type="hidden" name="status" value="<?php echo LWP_Payment_Status::REFUND ?>" />
	<?php endif; ?>
<?php
require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-refund.php";
$list_table = new LWP_List_Refund();
$list_table->prepare_items();
do_action('admin_notice');
$list_table->search_box(__('Search'), 's');
$list_table->display();
?>
</form>
<?php else: ?>

<div id="refund-place-holders">
	<input type="hidden" name="display_name" value="Test User" />
	<input type="hidden" name="site_url" value="<?php bloginfo('url'); ?>" />
	<input type="hidden" name="site_name" value="<?php bloginfo('name'); ?>" />
	<input type="hidden" name="purchase_history" value="<?php echo lwp_history_url(); ?>" />
	<input type="hidden" name="account_url" value="<?php echo lwp_refund_account_url(); ?>" />
	<input type="hidden" name="item_name" value="Test Item" />
	<input type="hidden" name="paid_price" value="$1,000" />
	<input type="hidden" name="refund_price" value="$500" />
</div>
<form method="post" action="<?php echo admin_url('admin.php?page=lwp-refund&option=message'); ?>">
	<?php wp_nonce_field('lwp_refund_message'); ?>
	
	<table class="form-table">
		<tbody>
			<tr>
				<th valign="top">
					<label for="refund_succeeded"><?php $this->e('Refund is finished'); ?></label>
				</th>
				<td>
					<textarea rows="10" style="width: 90%;" name="refund_succeeded" id="refund_succeeded"><?php echo esc_html($this->refund_manager->message['succeeded']); ?></textarea>
					<p class="description">
						<?php $this->e('This message will be sent if transaction status is changed to be refunded.'); ?>
						<a class=" button refund-message-preview" href="#"><?php _e('Preview'); ?></a><br />
						<?php $this->e('Available placeholders: '); foreach($this->refund_manager->get_place_holders('succeeded') as $placeholder): ?>
						<code>%<?php echo $placeholder; ?>%</code> 
						<?php endforeach; ?>
					</p>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="refund_accepted"><?php $this->e('Refund is accepted'); ?></label>
				</th>
				<td>
					<textarea rows="10" style="width: 90%;" name="refund_accepted" id="refund_accepted"><?php echo esc_html($this->refund_manager->message['accepted']); ?></textarea>
					<p class="description">
						<?php $this->e('This message will be sent if transaction status is changed to be refund requesting.'); ?>
						<a class=" button refund-message-preview" href="#"><?php _e('Preview'); ?></a><br />
						<?php $this->e('Available placeholders: '); foreach($this->refund_manager->get_place_holders('accepted') as $placeholder): ?>
						<code>%<?php echo $placeholder; ?>%</code> 
						<?php endforeach; ?>
					</p>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="refund_required"><?php $this->e('Refund account is required'); ?></label>
				</th>
				<td>
					<textarea rows="10" style="width: 90%;" name="refund_required" id="refund_required"><?php echo esc_html($this->refund_manager->message['required']); ?></textarea>
					<p class="description">
						<?php printf($this->_('This message will be sent if request button is clicked on <a href="%s">Refund Requesting</a>.'), admin_url('admin.php?page=lwp-refund')); ?>
						<a class=" button refund-message-preview" href="#"><?php _e('Preview'); ?></a><br />
						<?php $this->e('Available placeholders: '); foreach($this->refund_manager->get_place_holders('required') as $placeholder): ?>
						<code>%<?php echo $placeholder; ?>%</code> 
						<?php endforeach; ?>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<?php submit_button(); ?>
	</p>
</form>

<?php endif; ?>
