<?php /* @var $this Literally_WordPress */ ?>
<h2><?php $this->e("Devices"); ?></h2>

<?php
	//端末を取得
	$devices = $wpdb->get_results("SELECT * FROM {$this->devices}");
?>
<script type="text/javascript">
//<![CDATA[
	function lwp_confirm_delete(event)
	{
		var select = jQuery(event.target).prev().attr("value");
		if(select != "delete" || !confirm("<?php $this->e("You really delete this device?"); ?>")){
			event.preventDefault();
		}
	}
//]]>
</script>
<div id="col-container">
	<div id="col-right">
		<div class="col-wrap">
			<form method="post" action="<?php echo admin_url(); ?>edit.php?post_type=ebook&amp;page=lwp-devices">
				<?php wp_nonce_field("lwp_delete_devices"); ?>
				<div class="tablenav">
					<div class="alignleft actions">
						<select name="action">
							<option selected="selected" value=""><?php $this->e("Action"); ?></option>
							<option value="delete"><?php $this->e("Delete"); ?></option>
						</select>
						<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="<?php $this->e("Apply"); ?>" onclick="lwp_confirm_delete(event);"/>
						<br class="clear" />
					</div>
				</div>
				<!-- .tablenav -->
				<table class="widefat tag fixed" cellspacing="0">
					<thead>
						<tr>
							<th class="manage-column check-column">
								<input type="checkbox" />
							</th>
							<th class="manage-column"><?php $this->e("Device Name"); ?></th>
							<th class="manage-column"><?php $this->e("Slug"); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th class="manage-column check-column">
								<input type="checkbox" />
							</th>
							<th class="manage-column"><?php $this->e("Device Name"); ?></th>
							<th class="manage-column"><?php $this->e("Slug"); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php if($devices): $counter = 0; foreach($devices as $d): $counter++; ?>
						<tr<?php if($counter % 2 == 1) echo ' class="alternate"'; ?>>
							<th class="check-column">
								<input type="checkbox" value="<?php echo $d->ID; ?>" name="devices[]" />
							</th>
							<td><?php $this->h($d->name); ?></td>
							<td><?php $this->h($d->slug); ?></td>
						</tr>
						<?php endforeach; else: ?>
						<tr>
							<td colspan="3"><?php $this->e("No device was registered."); ?></td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
				<div class="tablenav">
					<div class="alignleft actions">
						<select name="action">
							<option selected="selected" value=""><?php $this->e("Action"); ?></option>
							<option value="delete"><?php $this->e("Delete"); ?></option>
						</select>
						<input type="submit" class="button-secondary action" id="doaction2" name="doaction2" value="<?php $this->e("Apply"); ?>" onclick="lwp_confirm_delete(this);" />
						<br class="clear" />
					</div>
				</div>
				<!-- .tablenav -->
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
