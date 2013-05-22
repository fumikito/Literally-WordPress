<?php /* @var $this Literally_WordPress */ ?>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo admin_url('admin.php?page=lwp-reward'); ?>" class="nav-tab<?php if(!isset($_GET['tab'])) echo ' nav-tab-active';?>">
		<?php $this->e('Reward Dashboard'); ?>
	</a>
	<a href="<?php echo admin_url('admin.php?page=lwp-reward&tab=history'); ?>" class="nav-tab<?php if(isset($_GET['tab']) && $_GET['tab'] == 'history') echo ' nav-tab-active';?>">
		<?php $this->e('History'); ?>
	</a>
	<a href="<?php echo admin_url('admin.php?page=lwp-reward&tab=request'); ?>" class="nav-tab<?php if(isset($_GET['tab']) && $_GET['tab'] == 'request') echo ' nav-tab-active';?>">
		<?php $this->e('Request'); ?>
		<?php if(($count = $this->reward->on_queue_count())):  ?>
		<small class="tab-count"><?php echo $count; ?></small>
		<?php endif; ?>
	</a>
</h2>

<?php do_action('admin_notice'); ?>

<?php if(!isset($_GET['tab'])): ?>
<?php require_once $this->dir.'/admin/parts/reward-summary.php';?>




<?php elseif($_GET['tab'] == 'history'): ?>
<p class="description">
	<?php $this->e('Users contribution list is below.'); ?>
</p>

<form method="get">
	<input type="hidden" name="page" value="lwp-reward" /> 
	<input type="hidden" name="tab" value="history" />
	<?php
		require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-reward-history.php";
		$table = new LWP_List_Reward_History();
		$table->prepare_items();
		$table->search_box(__('Search'), 'q');
		$table->display();
	?>
</form>

<?php elseif($_GET['tab'] == 'request'): ?>

<p class="description">
	<?php $this->e('User payment requests are below.'); ?>
</p>

<form method="get">
	<input type="hidden" name="page" value="lwp-reward" /> 
	<input type="hidden" name="tab" value="request" />
	<?php
		require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-reward-request.php";
		$table = new LWP_List_Reward_Request();
		$table->prepare_items();
		$table->display();
	?>
</form>


<?php endif; ?>