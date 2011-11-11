<?php
/* @var $this Literally_WordPress */
/* @var $wpdb wpdb */
?>
<h2><?php $this->e("Devices"); ?></h2>

<?php if(isset($_GET['device']) && ($device = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->devices} WHERE ID = %d", $_GET['device'])))): ?>

<form method="post" action="<?php echo admin_url('admin.php?page=lwp-devices&device='.$device->ID);?>">
	<?php wp_nonce_field('edit_device')?>
	<input type="hidden" name="device_id" value="<?php echo esc_attr($device->ID); ?>" />
	<table class="form-table">
		<tbody>
			<tr>
				<th><label for="device_name"><?php $this->e('Device Name'); ?></label></th>
				<td>
					<input type="text" id="device_name" name="device_name" value="<?php echo esc_attr($device->name); ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="device_slug"><?php $this->e('Slug'); ?></label></th>
				<td>
					<input type="text" id="device_slug" name="device_slug" value="<?php echo esc_attr($device->slug); ?>" />
				</td>
			</tr>
			<tr>
				<th><?php $this->e('Assigned'); ?></th>
				<td><?php echo $wpdb->get_var($wpdb->prepare("SELECT COUNT(file_id) FROM {$this->file_relationships} WHERE device_id = %d", $device->ID));?></td>
			</tr>
		</tbody>
	</table>
	<?php	submit_button($this->_('Update')); ?>
</form>
<p>
	<a class="button" href="<?php echo admin_url('admin.php?page=lwp-devices'); ?>"><?php $this->e('Return to list'); ?></a>
</p>
<?php else: ?>

<div id="col-container">
	<div id="col-right">
		<div class="col-wrap">
			<form method="get" action="<?php echo admin_url('admin.php'); ?>">
				<input type="hidden" name="page" value="lwp-devices" />
				<?php wp_nonce_field("lwp_delete_devices"); ?>
				<?php
					require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-devices.php";
					$list_table = new LWP_List_Devices();
					$list_table->prepare_items();
					$list_table->display();
				?>
			</form>
			<div class="description">
				<p>
					<strong>Note:</strong><br />
					<?php $this->e("You can delete device, but you still have to delete files on each post-editting page");?>
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
				<h3><?php $this->e("Add new device"); ?></h3>
				<form method="post">
					<?php wp_nonce_field("lwp_add_device"); ?>
										
					<div class="form-field">
						<label for="device_name"><?php $this->e("Device Name"); ?></label>
						<input type="text" id="device_name" name="device_name" />
						<p>
							<?php $this->e("Name of device to display."); ?>
						</p>
					</div>
					<!-- .form-field ends -->
					
					<div class="form-field">
						<label for="device_slug"><?php $this->e("Slug"); ?></label>
						<input type="text" id="device_slug" name="device_slug" style="ime-mode:disabled;" />
						<p>
							<?php $this->e("Slug is alphabetical short name for device. It will help for displaying icon.<br />ex. &lt;img src=\"DEVICE-SLUG.png\" /&gt;"); ?>
						</p>
					</div>
					<!-- .form-field ends -->
					
					<p class="submit">
						<input type="submit" value="<?php $this->e("Add new device"); ?>" id="submit" name="submit" class="button">
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
<?php endif;