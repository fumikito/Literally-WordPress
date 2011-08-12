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
				<label for="start"><?php $this->e('Start Date'); ?></label>
			</th>
			<td>
				<input type="text" name="start" id="start" value="<?php echo $campaign->start; ?>" class="date-picker" />
				<p class="description">
					<?php printf($this->_('Format must be %s.'), '<span class="cursive">YYYY-mm-dd HH:MM:SS</span>'); ?>
				</p>
			</td>
		</tr>
		<tr class="form-field">
			<th valign="top">
				<label for="end"><?php $this->e('End Date'); ?></label>
			</th>
			<td>
				<input type="text" name="end" id="end" value="<?php echo $campaign->end; ?>" class="date-picker" />
				<p class="description">
					<?php printf($this->_('Format must be %s.'), '<span class="cursive">YYYY-mm-dd HH:MM:SS</span>'); ?>
				</p>
			</td>
		</tr>
	</table>
	<p class="submit">
		<input type="submit" value="更新" name="submit" class="primary-button" />
	</p>
</form>
<a href="<?php echo admin_url('admin.php?page=lwp-campaign'); ?>">&laquo;<?php $this->e('Return to cmapign page'); ?></a>
<?php
/*-------------------------------
 * 一覧表示
 */
else:
	//オフセットの有無を確認
	$count = $wpdb->get_row("SELECT count(*) FROM {$this->campaign}")->{"count(*)"};
	$pages = floor($count / 10);
	$pages += ($count % 10 == 0) ? 0 : 1;
	if(isset($_REQUEST["offset"]) && $_REQUEST["offset"] > 1 && $_REQUEST["offset"] <= $pages ){
		$offset = (($_REQUEST["offset"] - 1) * 10).", ";
		$curpage = $_REQUEST["offset"];
	}else{
		$offset = "";
		$curpage = 1;
	}
	$sql = "SELECT * FROM {$this->campaign} ORDER BY `start` DESC LIMIT {$offset}10";
	$campaigns = $wpdb->get_results($wpdb->prepare($sql));
	$pagenator = "<small>".sprintf($this->_("Toatal %d:"), $count)." ";
	for($i = 1; $i <= $pages; $i++){
		if($i == $curpage){
			$pagenator .= " {$i}";
		}else{
			$pagenator .= ' <a href="'.admin_url("admin.php?page=lwp-campaign&offset={$i}")."\">{$i}</a>";
		}
	}
	$pagenator .= "</small>";
?>
<div id="col-container">
	<div id="col-right">
		<div class="col-wrap">
			<form method="post" action="<?php echo admin_url('admin.php?page=lwp-campaign'); ?>">
				<?php wp_nonce_field("lwp_delete_campaign"); ?>
				<div class="tablenav">
					<div class="alignleft actions">
						<select name="action">
							<option selected="selected" value=""><?php $this->e("Action"); ?></option>
							<option value="delete"><?php $this->e("Delete"); ?></option>
						</select>
						<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="<?php $this->e("Apply"); ?>" />
						<?php echo $pagenator; ?>
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
							<th class="manage-column"><?php $this->e("Item"); ?></th>
							<th class="manage-column"><?php $this->e("Period"); ?></th>
							<th class="manage-column"><?php $this->e("Price"); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th class="manage-column check-column">
								<input type="checkbox" />
							</th>
							<th class="manage-column"><?php $this->e("Item"); ?></th>
							<th class="manage-column"><?php $this->e("Period"); ?></th>
							<th class="manage-column"><?php $this->e("Price"); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php if($campaigns): $counter = 0; foreach($campaigns as $c): $counter++; $p = wp_get_single_post($c->book_id); ?>
						<tr<?php if($counter % 2 == 1) echo ' class="alternate"'; ?>>
							<th class="check-column">
								<input type="checkbox" value="<?php echo $c->ID; ?>" name="campaigns[]" />
							</th>
							<td>
								<p>
									<strong>
										<a href="<?php echo admin_url(); ?>edit.php?post_type=ebook&amp;page=lwp-campaign&amp;campaign=<?php echo $c->ID; ?>">
											<?php echo $p->post_title; ?>
											<small>[<?php echo mysql2date("Y/m/d", $p->post_date); ?>]</small>
										</a>
									</strong>
								</p>
							</td>
							<td>
								<p>
									<?php echo mysql2date("Y/m/d", $c->start)." ~ ".mysql2date("Y/m/d", $c->end); ?>
								</p>
							</td>
							<td>
								<?php echo money_format('%7n', $c->price); ?>
								<small>[<?php printf($this->_('%d%%Off'), (100 - round($c->price / get_post_meta($p->ID, "lwp_price", true) * 100)) ); ?>]</small>
							</td>
						</tr>
						<?php endforeach; else: ?>
						<tr>
							<td colspan="4"><?php $this->e("No campaign registered."); ?></td>
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
						<input type="submit" class="button-secondary action" id="doaction2" name="doaction2" value="<?php $this->e('Apply'); ?>" />
						<?php echo $pagenator; ?>
						<br class="clear" />
					</div>
				</div>
				<!-- .tablenav -->
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
							<?php foreach(get_posts(array("post_type" => $this->option['payable_post_types'], 'posts_per_page' => -1)) as $p): ?>
							<?php if(true): ?>
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
						<input type="text" id="start" name="start" class="date-picker" />
						<p>
							<?php printf($this->_('Format must be %s.'), '<span class="cursive">YYYY-mm-dd HH:MM:SS</span>'); ?>
						</p>
					</div>
					<!-- .form-field ends -->
					
					<div class="form-field">
						<label for="end"><?php $this->e('End Date');?></label>
						<input type="text" id="end" name="end" class="date-picker" />
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
