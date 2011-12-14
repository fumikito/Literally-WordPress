<?php /* @var $this Literally_WordPress */ ?>
<h2><?php $this->e('Purchase History'); ?></h2>
<form method="get">
	<input type="hidden" name="page" value="lwp-history" />
<?php
	require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-history.php";
	$table = new LWP_List_History();
	$table->prepare_items();
	do_action("admin_notice");
	$table->search_box(__('Search'), 'q');
	$table->display();
	
?>
</form>
<h3><?php $this->e('Contact'); ?></h3>
<p>
	<?php printf($this->_('If purchased items were not displayed, please contact to Site administrator &lt;<a href="mailto:%1$s">%1$s</a>&gt;. We will reply ASAP.'), get_option('admin_email')); ?>
</p>