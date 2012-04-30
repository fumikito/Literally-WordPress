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

		<?php var_dump($this->reward->get_total_reward(get_current_user_id()), $this->reward->get_requested_reward(get_current_user_id())); ?>

<?php endif; ?>