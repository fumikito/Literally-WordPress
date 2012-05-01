<?php /* @var $this Literally_WordPress */ ?>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo admin_url('users.php?page=lwp-personal-reward'); ?>" class="nav-tab<?php if(!isset($_GET['tab'])) echo ' nav-tab-active';?>">
		<?php $this->e('Your Reward Summery'); ?>
	</a>
	<?php if($this->reward->promotable): ?>
	<a href="<?php echo admin_url('users.php?page=lwp-personal-reward&tab=link'); ?>" class="nav-tab<?php if(isset($_GET['tab']) && $_GET['tab'] == 'link') echo ' nav-tab-active';?>">
		<?php $this->e('Get promotion link'); ?>
	</a>
	<?php endif; ?>
	<a href="<?php echo admin_url('users.php?page=lwp-personal-reward&tab=history'); ?>" class="nav-tab<?php if(isset($_GET['tab']) && $_GET['tab'] == 'history') echo ' nav-tab-active';?>">
		<?php $this->e('Reward History'); ?>
	</a>
	<a href="<?php echo admin_url('users.php?page=lwp-personal-reward&tab=request'); ?>" class="nav-tab<?php if(isset($_GET['tab']) && $_GET['tab'] == 'request') echo ' nav-tab-active';?>">
		<?php $this->e('Payment Request'); ?>
	</a>
</h2>

<?php do_action('admin_notice'); ?>

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
			<form method="post" action="<?php echo admin_url('admin.php?page=lwppersonal-reward&tab=request'); ?>">
				<?php
					require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-reward-request.php";
					$list_table = new LWP_List_Reward_Request(get_current_user_id());
					$list_table->prepare_items();
					$list_table->display();
				?>
			</form>
			<div class="description">
				<p>
					<strong>Note:</strong><br />
					<?php $this->e('You can edit the campain detail by clicking item link.<br />But You can\'t change the price of temporary active campain.<br /><strong>In that case, you have to stop campaign and recreate another campaing.</strong>'); ?>
				</p>
			</div>
			<!-- .description ends -->
		</div>
		<!-- .col-wrap ends -->
	</div>
	<!-- #col-right ends -->
	
	<div id="col-left">
		<div class="col-wrap">
			<div class="form-wrap">
				<h3><?php $this->e('Make Payment Request'); ?></h3>
				<form method="post">
					<?php wp_nonce_field("lwp_reward_request_".get_current_user_id()); ?>
										
					<div class="form-field">
						<label for="book_id"><?php $this->e("Campaign Item");?></label>
						<select id="book_id" name="book_id">
							<option disabled="disabled" selected="selected"><?php $this->e("Select from here.");?></option>
							<?php foreach(get_posts(array("post_type" => $this->option['payable_post_types'], 'posts_per_page' => -1, 'post_status' => array('publish', 'future', 'draft'))) as $p): ?>
							<?php if(lwp_original_price($p) > 0): ?>
							<option value="<?php echo $p->ID; ?>"><?php echo $p->post_title; ?></option>
							<?php endif; ?>
							<?php endforeach; ?>
						</select>
						<p>
							<?php $this->e("Item for which campaign will be adopted.");?>
						</p>
					</div>
					<!-- .form-field ends -->
					
					<div class="form-field">
						<label for="price"><?php $this->e("Campaign Price"); ?></label>
						<input type="text" id="price" name="price" />
						<p>
							<?php $this->e('Price for campaign.'); ?>
						</p>
					</div>
					<!-- .form-field ends -->
					
					<div class="form-field">
						<label for="start"><?php $this->e('Start Date');?></label>
						<input type="text" id="start_date" name="start" class="date-picker" />
						<p>
							<?php printf($this->_('Format must be %s.'), '<span class="cursive">YYYY-mm-dd HH:MM:SS</span>'); ?>
						</p>
					</div>
					<!-- .form-field ends -->
					
					<div class="form-field">
						<label for="end"><?php $this->e('End Date');?></label>
						<input type="text" id="end_date" name="end" class="date-picker" />
						<p>
							<?php printf($this->_('Format must be %s.'), '<span class="cursive">YYYY-mm-dd HH:MM:SS</span>'); ?>
						</p>
					</div>
					<!-- .form-field ends -->
					
					<p class="submit">
						<input type="submit" value="<?php $this->e('Add new campaing');?>" id="submit" name="submit" class="button">
					</p>
					<!-- .submit ends -->
				</form>
			</div>
			<!-- .form-wrap ends -->
		</div>
		<!-- .col-wrap ends -->
	</div>
	<!-- #col-left ends -->
</div>
<!-- #col-container -->

<?php endif; ?>