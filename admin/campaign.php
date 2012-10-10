<?php /* @var $this Literally_WordPress */ ?>
<h2><?php $this->e('Campaign'); ?></h2>

<?php
/*-------------------------------
 * 個別表示
 */
if(isset($_REQUEST["campaign"]) && $campaign = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->campaign} WHERE ID = %d", $_REQUEST["campaign"]))):
$post = wp_get_single_post($campaign->book_id);
if($campaign->type == LWP_Campaign_Type::SINGULAR){
	$post_ids = array($campaign->book_id);
}elseif($campaign->type == LWP_Campaign_Type::SET){
	$post_ids = $this->campaign_manager->get_campaign_posts($campaign->ID);
}
?>
<form method="post">
	<?php wp_nonce_field("lwp_add_campaign"); ?>
	<input type="hidden" name="campaign" value="<?php echo $campaign->ID; ?>" />
	<table class="form-table">
		<tr class="form-field">
			<th valign="top">
				<label for="post-picker"><?php $this->e("Campaign Item"); ?></label>
			</th>
			<td>
				<input type="hidden" name="book_id" id="book_id" value="<?php echo implode(',', $post_ids); ?>" />
				<div id="post-container" class="tagchecklist button-container">
					<?php foreach($post_ids as $post_id):  ?>
					<span><a id="menu_item<?php echo $post_id; ?>">X</a>
						<?php switch(get_post_type($post_id)){
							case $this->subscription->post_type:
								echo $this->_('Subscription')." ".get_the_title($post_id);
								break;
							case $this->event->post_type:
								echo get_the_title($this->event->get_event_from_ticket_id($post_id))." ".get_the_title($post_id);
								break;
							default: 
								echo get_the_title($post_id); 
								break;
						}
						printf('(%s)', number_format_i18n(lwp_original_price($post_id)));
						?>
					</span>
					<?php endforeach; ?>
				</div>
				<input type="text" id="post-picker" value="" placeholder="<?php $this->e('Input here and search items'); ?>" />
				<ul id="campaign-post-list" class="ui-autocomplete ui-menu ui-widget ui-widget-content ui-corner-all">
				</ul>
				<p class="description">
					<?php $this->e("If you change campaign item after publishing, transaction statistic data may be somehow confused."); ?>
				</p>
			</td>
		</tr>
		<tr class="form-field lwp-form-field">
			<th valign="top">
				<label for="price"><?php $this->e('Campaign Price'); ?></label>
			</th>
			<td>
				<input type="text" name="price" id="price" value="<?php echo $campaign->price; ?>" />
				<select name="calcuration" id="calcuration">
					<?php foreach(LWP_Campaign_Calculation::get_all() as $type): ?>
					<option value="<?php echo $type; ?>"<?php if($type == $campaign->calculation) echo ' selected="selected"'; ?>>
						<?php switch($type){
							case LWP_Campaign_Calculation::SPECIAL_PRICE:
								printf($this->_('%s as sale price'), lwp_currency_code());
								break;
							case LWP_Campaign_Calculation::DISCOUNT:
								printf($this->_('%s as discount'), lwp_currency_code());
								break;
							case LWP_Campaign_Calculation::PERCENT:
								printf($this->_('%s of original price'), '%');
								break;
						} ?>
					</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr class="form-field">
			<th valign="top">
				<label for="coupon"><?php $this->e("Coupon"); ?></label>
			</th>
			<td>
				<input type="text" id="coupon" name="coupon" placeholder="ex.WFEJ-O8S8-19JI-LLSE" value="<?php echo esc_attr($campaign->coupon); ?>" />
				<p class="description">
					<?php $this->e('If you enter coupon code, the sale price will be adopted only for user with coupon.'); ?>
				</p>
			</td>
		</tr>
		<tr class="form-field">
			<th valign="top">
				<label for="payment_method"><?php $this->e("Payment method"); ?></label>
			</th>
			<td>
				<select id="payment_method" name="payment_method">
					<option value="<?php if(false === array_search($campaign->method, LWP_Payment_Methods::get_all_methods())) echo ' selected="selected"'; ?>><?php $this->e('Any'); ?></option>
					<?php
						$methods = array(LWP_Payment_Methods::PAYPAL);
						if($this->notifier->is_enabled()){
							$methods[] = LWP_Payment_Methods::TRANSFER;
						}
						foreach($methods as $method): ?>
						<option value="<?php echo esc_attr($method);?>"<?php if($method == $campaign->method) echo ' selected="selected"'; ?>><?php $this->e($method); ?></option>
					<?php endforeach; ?>
				</select>
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
					<?php $this->e('You can edit the campain detail by clicking item link.<br />But <strong>please be careful to change the itmes or price of temporary active campain</strong>.'); ?>
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
					<div class="form-field lwp-form-field">
						<label for="post-picker"><?php $this->e("Campaign Item");?></label>
						<input type="hidden" name="book_id" id="book_id" value="" />
						<div id="post-container" class="tagchecklist button-container">
						</div>
						<input type="text" id="post-picker" value="" placeholder="<?php $this->e('Input here and search items'); ?>" />
						<ul id="campaign-post-list" class="ui-autocomplete ui-menu ui-widget ui-widget-content ui-corner-all">
						</ul>
						<p>
							<?php $this->e("Item for which campaign will be adopted. Please search with text input. You can choose multiple items.");?>
						</p>
					</div>
					<!-- .form-field ends -->
					
					<div class="form-field lwp-form-field">
						<label for="price"><?php $this->e("Campaign Price"); ?></label>
						<input type="text" id="price" name="price" placeholder="ex. 100" />
						<select name="calcuration" id="calcuration">
							<?php foreach(LWP_Campaign_Calculation::get_all() as $type): ?>
							<option value="<?php echo $type; ?>"<?php if($type == LWP_Campaign_Calculation::SPECIAL_PRICE) echo ' selected="selected"'; ?>>
								<?php switch($type){
									case LWP_Campaign_Calculation::SPECIAL_PRICE:
										printf($this->_('%s as sale price'), lwp_currency_code());
										break;
									case LWP_Campaign_Calculation::DISCOUNT:
										printf($this->_('%s as discount'), lwp_currency_code());
										break;
									case LWP_Campaign_Calculation::PERCENT:
										printf($this->_('%s of original price'), '%');
										break;
								} ?>
							</option>
							<?php endforeach; ?>
						</select>
						<p>
							<?php $this->e('Price for campaign which will be applied to all items.'); ?>
						</p>
					</div>
					<!-- .form-field ends -->
					
					
					<div class="form-field lwp-form-field">
						<label for="coupon"><?php $this->e("Coupon"); ?></label>
						<input type="text" id="coupon" name="coupon" placeholder="ex.WFEJ-O8S8-19JI-LLSE" />
						<p>
							<?php $this->e('If you enter coupon code, the sale price will be adopted only for user with coupon.'); ?>
						</p>
					</div>
					<!-- .form-field ends -->
					
					<div class="form-field lwp-form-field">
						<label for="payment_method"><?php $this->e("Payment method"); ?></label>
						<select id="payment_method" name="payment_method">
							<option value="" selected="selected"><?php $this->e('Any'); ?></option>
							<?php
								$methods = array(LWP_Payment_Methods::PAYPAL);
								if($this->notifier->is_enabled()){
									$methods[] = LWP_Payment_Methods::TRANSFER;
								}
								foreach($methods as $method): ?>
								<option value="<?php echo esc_attr($method); ?>"><?php $this->e($method); ?></option>
							<?php endforeach; ?>
						</select>
						<p>
							<?php $this->e('Campaign is adopted only with this payment method.'); ?>
						</p>
					</div>
					<!-- .form-field ends -->
					
					<div class="form-field lwp-form-field">
						<label for="start"><?php $this->e('Start Date');?></label>
						<input type="text" id="start_date" name="start" class="date-picker" placeholder="ex. <?php echo date('Y-m-d H:i:s'); ?>" />
						<p>
							<?php printf($this->_('Format must be %s.'), '<span class="cursive">YYYY-mm-dd HH:MM:SS</span>'); ?>
						</p>
					</div>
					<!-- .form-field ends -->
					
					<div class="form-field lwp-form-field">
						<label for="end"><?php $this->e('End Date');?></label>
						<input type="text" id="end_date" name="end" class="date-picker" placeholder="ex. <?php echo date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 7); ?>"  />
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
