<?php /* @var $this Literally_WordPress */ ?>
<h2><?php $this->e("File Download Logs"); ?></h2>

<form method="get" action="<?php echo admin_url('admin.php'); ?>">
	<input type="hidden" name="page" value="lwp-download-logs" />
<?php
require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-download-logs.php";
$list_table = new LWP_List_Download_Logs();
$list_table->prepare_items();
$list_table->search_box(__('Search'), 's');
$list_table->display();
?>
</form>