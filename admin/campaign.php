<?php /* @var $this Literally_WordPress */ ?>
<h2><?php $this->e('Campaign'); ?></h2>

<?php
/*-------------------------------
 * 個別表示
 */
if(isset($_REQUEST["campaign"]) && $campaign = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->campaign} WHERE ID = %d", $_REQUEST["campaign"]))):
$post = wp_get_single_post($campaign->book_id);
?>
<form method="post">
	<?php wp_nonce_field("lwp_update_campaign"); ?>
	<input type="hidden" name="campaign" value="<?php echo $campaign->ID; ?>" />
	<input type="hidden" name="book_id" value="<?php echo $campaign->book_id; ?>" />
	<table class="form-table">
		<tr class="form-field">
			<th valign="top">
				<label for="book_id"><?php $this->e("Campaign Item"); ?></label>
			</th>
			<td>
				<strong><?php echo $post->post_title;?></strong>
				<p class="description">
					<?php $this->e("Item cant't be changed later."); ?>
				</p>
			</td>
		</tr>
		<tr class="form-field">
			<th valign="top">
				<label for="price"><?php $this->e('Campaign Price'); ?></label>
			</th>
			<td>
				<input type="text" name="price" id="price" value="<?php echo $campaign->price; ?>" />
				<p class="description">
					<?php printf($this->_('Original price is %s'), number_format(lwp_original_price($post->ID)));?>
				</p>
			</td>
		</tr>
		<tr class="form-field">
			<th valign="top">
				<label for="start_date"><?php $this->e('Start Date'); ?></label>
			</th>
			<td>
				<input type="text" name="start" id="start_date" value="<?php echo $campaign->start; ?>" class="date-picker" />
				<p class="description">
					<?php printf($this->_('Format must be %s.'), '<span class="cursive">YYYY-mm-dd HH:MM:SS</span>'); ?>
				</p>
			</td>
		</tr>
		<tr class="form-field">
			<th valign="top">
				<label for="end_date"><?php $this->e('End Date'); ?></label>
			</th>
			<td>
				<input type="text" name="end" id="end_date" value="<?php echo $campaign->end; ?>" class="date-picker" />
				<p class="description">
					<?php printf($this->_('Format must be %s.'), '<span class="cursive">YYYY-mm-dd HH:MM:SS</span>'); ?>
				</p>
			</td>
		</tr>
	</table>
	<?php	submit_button($this->_('Update')); ?>
</form>
<a class="button" href="<?php echo admin_url('admin.php?page=lwp-campaign'); ?>">&laquo;<?php $this->e('Return to campaign page'); ?></a>
<?php
/*-------------------------------
 * 一覧表示
 */
else: ?>
<div id="col-container">
	<div id="col-right">
		<div class="col-wrap">
			<form method="post" action="<?php echo admin_url('admin.php?page=lwp-campaign'); ?>">
				<?php
					require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-campaign.php";
					$list_table = new LWP_List_Campaigns();
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
				<h3><?php $this->e('Add new campaign'); ?></h3>
				<form method="post">
					<?php wp_nonce_field("lwp_add_campaign"); ?>
										
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

<?php
/*
 * 分岐終了
 -------------------------------*/
endif;
