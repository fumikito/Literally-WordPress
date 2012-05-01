<?php /* @var $this Literally_WordPress */ ?>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo admin_url('users.php?page=lwp-personal-reward'); ?>" class="nav-tab<?php if(!isset($_GET['tab'])) echo ' nav-tab-active';?>">
		<?php $this->e('Your Reward Summery'); ?>
	</a>
	<?php if($this->reward->promotable): ?>
	<a href="<?php echo admin_url('users.php?page=lwp-personal-reward&tab=link'); ?>" class="nav-tab<?php if(isset($_GET['tab']) && $_GET['tab'] == 'link') echo ' nav-tab-active';?>">
		<?php $this->e('Get link'); ?>
	</a>
	<?php endif; ?>
	<a href="<?php echo admin_url('users.php?page=lwp-personal-reward&tab=history'); ?>" class="nav-tab<?php if(isset($_GET['tab']) && $_GET['tab'] == 'history') echo ' nav-tab-active';?>">
		<?php $this->e('History'); ?>
	</a>
	<a href="<?php echo admin_url('users.php?page=lwp-personal-reward&tab=request'); ?>" class="nav-tab<?php if(isset($_GET['tab']) && $_GET['tab'] == 'request') echo ' nav-tab-active';?>">
		<?php $this->e('Request'); ?>
	</a>
</h2>

<?php if(!isset($_GET['tab'])): ?>
ダッシュボード

<?php elseif($_GET['tab'] == 'link' && $this->reward->promotable): ?>
<p class="description">
	<?php $this->e('You can get promotion link below.'); ?>
</p>


<?php elseif($_GET['tab'] == 'history'): ?>
<p class="description">
	<?php $this->e('Your reward histroy is below.'); ?>
</p>

<form method="get">
	<input type="hidden" name="page" value="lwp-personal-reward" /> 
	<input type="hidden" name="tab" value="history" />
	<?php
		require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-reward-history.php";
		$table = new LWP_List_Reward_History(get_current_user_id());
		$table->prepare_items();
		$table->display();
	?>
</form>






<?php elseif($_GET['tab'] == 'request'): ?>
<p class="description">
	<?php $this->e('You can request payment for your contribution.'); ?>
</p>

<div id="col-container">
	<div id="col-right">
		<div class="col-wrap">
			<form method="get" action="<?php echo admin_url('admin.php?page=lwppersonal-reward&tab=request'); ?>">
				<?php
					require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-reward-request.php";
					$list_table = new LWP_List_Reward_Request(get_current_user_id());
					$list_table->prepare_items();
					$list_table->display();
				?>
			</form>
			<!-- .description ends -->
		</div>
		<!-- .col-wrap ends -->
	</div>
	<!-- #col-right ends -->
	
	<div id="col-left">
		<div class="col-wrap">
			<div class="form-wrap">
				<h3><?php $this->e('Make Payment Request'); ?></h3>
				<div class="form-field">
					<label><?php $this->e("Current Status");?></label>
					<table class="form-table">
						<tbody>
							<tr>
								<th valign="top"><?php $this->e('Status'); ?></th>
								<td>
									<?php if($this->reward->is_user_requesting(get_current_user_id())): ?>
										<?php printf($this->_('Requesting: paid by <strong>%s</strong>'), $this->reward->next_pay_day()); ?>
									<?php elseif(($rest = $this->reward->required_payment_for_user(get_current_user_id())) > 0): ?>
										<?php printf($this->_('Rest %1$d (%2$s) required.'), $rest, lwp_currency_code());  ?>
									<?php else: ?>
										<?php printf($this->_('You can request: %1$d (%2$s)'), $this->reward->user_rest_amount(get_current_user_id()), lwp_currency_code()); ?>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th valign="top"><?php $this->e('Unpaid Reward'); ?></th>
								<td><?php echo number_format($this->reward->user_rest_amount(get_current_user_id())); ?> (<?php echo lwp_currency_code();?>)</td>
							</tr>
							<tr>
								<th valign="top"><?php $this->e('Paid Reward'); ?></th>
								<td><?php printf($this->_('%1$d (%2$s)'), $this->reward->user_reward_amount(get_current_user_id()), lwp_currency_code()); ?></td>
							</tr>
						</tbody>
					</table>
				</div>
				<!-- .form-field ends -->
				<?php if($this->reward->user_rest_amount(get_current_user_id()) > 0 && !$this->reward->is_user_requesting(get_current_user_id())): ?>
				<form method="post" action="<?php echo admin_url('users.php?page=lwp-personal-reward&tab=request'); ?>">
					<?php wp_nonce_field("lwp_reward_request_".get_current_user_id()); ?>
					<div class="form-field">
						<label><?php $this->e("Request");?></label>
						<?php echo wpautop($this->reward->get_notice()); ?>
					</div>
					<!-- .form-field ends -->
					
					<p class="submit">
						<input type="submit" value="<?php $this->e('Request');?>" id="submit" name="submit" class="button">
					</p>
					<!-- .submit ends -->
				</form>
				<?php endif; ?>
			</div>
			<!-- .form-wrap ends -->
		</div>
		<!-- .col-wrap ends -->
	</div>
	<!-- #col-left ends -->
</div>
<!-- #col-container -->

<?php endif; ?>