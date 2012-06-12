<?php /* @var $this Literally_WordPress */?>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo admin_url('admin.php?page=lwp-event'); ?>" class="nav-tab<?php if(!isset($_GET['event_id'])) echo ' nav-tab-active'; ?>">
		<?php $this->e('Event Management'); ?>
	</a>
	<?php if(isset($_GET['event_id'])): ?>
		<a href="<?php echo admin_url('admin.php?page=lwp-event&event_id='.intval($_GET['event_id'])); ?>" class="nav-tab<?php if(isset($_GET['event_id'])) echo ' nav-tab-active';?>">
			<?php printf($this->_('%s Detail'), get_the_title(intval($_GET['event_id']))); ?>
		</a>
	<?php endif; ?>
</h2>

<?php
if(isset($_GET['event_id'])): $event = wp_get_single_post($_GET['event_id']);
	require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-ticket.php";
	$list_table = new LWP_List_Ticket();
?>
<iframe src="" name="lwp-csv-output" height="0" width="100%"></iframe>
<form id="lwp-csv-output-form" method="get" action="<?php echo admin_url('admin-ajax.php'); ?>" target="lwp-csv-output">
	<input type="hidden" name="action" value="lwp_event_csv_output" />
	<?php wp_nonce_field('lwp_event_csv_output');?>
	<input type="hidden" name="event_id" value="<?php echo esc_attr($_GET['event_id']); ?>" />
	<input type="hidden" name="status" value="" />
	<input type="hidden" name="ticket" value="" />
	<p class="description">
		<?php printf( $this->_('You can get participants list of <strong>%1$s</strong> in status below.'), $event->post_title); ?>
		<input type="submit" class="button-primary" value="<?php $this->e('Get CSV'); ?>" /><br />
	</p>
</form>
<form method="get" action="<?php echo admin_url('admin.php'); ?>">
	<input type="hidden" name="page" value="lwp-event" />
	<input type="hidden" name="event_id" value="<?php echo intval($_GET['event_id']); ?>" />
<?php
$list_table->prepare_items();
do_action('admin_notice');
$list_table->search_box(__('Search'), 's');
$list_table->display();

?>
</form>


<?php else: ?>
<form method="get" action="<?php echo admin_url('admin.php'); ?>">
	<input type="hidden" name="page" value="lwp-event" />
<?php
require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-event.php";
$list_table = new LWP_List_Event();
$list_table->prepare_items();
do_action('admin_notice');
$list_table->search_box(__('Search'), 's');
$list_table->display();

?>
</form>
<?php endif; ?>