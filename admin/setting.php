<?php /* @var $this Literally_WordPress */ ?>
<h2 class="nav-tab-wrapper">
	<a class="nav-tab nav-tab-active" href="#tab-1"><?php $this->e("LWP Required Setting"); ?></a>
	<a class="nav-tab" href="#tab-2"><?php $this->e('Transfer Setting'); ?></a>
	<a class="nav-tab" href="#tab-3"><?php $this->e("Subscription"); ?></a>
	<a class="nav-tab" href="#tab-4"><?php $this->e('Reward Setting'); ?></a>	
</h2>

<form id="lwp-setting-form" method="post" action="<?php echo admin_url('admin.php?page=lwp-setting'); ?>">
	<?php wp_nonce_field("lwp_update_option"); ?>
	
	<div id="tab-1">
		<h3><?php $this->e('PayPal Settings');?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th valign="top">
						<label><?php $this->e("Use Sandbox"); ?></label>
					</th>
					<td>
						<label>
							<input type="checkbox" name="sandbox" id="sandbox" value="1"<?php if($this->option['sandbox']) echo ' checked="checked"';?> />
							<?php $this->e("This is a develop enviorment.")?>
						</label>
						<p class="description">
							<?php $this->e("Sandbox means develop enviorment. You can test your settings by checking above."); ?><small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label for="country_code"><?php $this->e("Country Code");?></label>
					</th>
					<td>
						<select name="country_code" id="country_code">
							<?php foreach(PayPal_Statics::country_codes() as $code => $country): ?>
							<option value="<?php echo $code; ?>"<?php if($this->option['country_code'] == $code) echo ' selected="selected"';?>><?php echo $country; ?></option>
							<?php endforeach;?>
						</select>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label for="currency_code"><?php $this->e("Currency Code");?></label>
					</th>
					<td>
						<select name="currency_code" id="currency_code">
							<?php foreach (PayPal_Statics::currency_codes() as $code => $desc): ?>
							<option value="<?php echo $code; ?>"<?php if($this->option['currency_code'] == $code) echo ' selected="selected"';?>><?php echo PayPal_Statics::currency_entity($code); ?> (<?php echo $desc; ?>)</option>
							<?php endforeach;?>
						</select>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label for="user_name"><?php $this->e("PayPal API User Name");?></label>
					</th>
					<td>
						<input id="user_name" name="user_name" class="regular-text" type="text" value="<?php $this->h($this->option["user_name"]); ?>" />
						<p class="description">
							<?php $this->e("PayPal API User Name issued by PayPal."); ?><small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label for="marchand_pass"><?php $this->e("PayPal Password");?></label>
					</th>
					<td>
						<input id="marchand_pass" name="marchand_pass" class="regular-text" type="password" value="<?php $this->h($this->option["password"]); ?>" />
						<p class="description">
							<?php $this->e("API password issued by PayPal"); ?><small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label for="signature"><?php $this->e("PayPal Signature");?></label>
					</th>
					<td>
						<input id="signature" name="signature" class="regular-text" type="text" value="<?php $this->h($this->option["signature"]); ?>" />
						<p class="description">
							<?php $this->e("API signature issued by PayPal"); ?><small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label for="token"><?php $this->e('PayPal PDT Token'); ?></label>
					</th>
					<td>
						<input id="token" name="token" class="regular-text" type="text" value="<?php $this->h($this->option["token"]); ?>" />
						<p class="description">
							<?php $this->e("Token issued by PayPal. Required for transaction.")?><small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small>
						</p>
						<?php if(!preg_match("/^[a-zA-Z0-9]+$/", $this->option["token"])): ?>
						<p class="error">
							<?php printf($this->_('This Token might be incorrect. See %s and get correct PDT Token.'), $this->help('account', $this->_('Help'))); ?>
						</p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label for="product_slug"><?php $this->e('Product slug'); ?></label>
					</th>
					<td>
						<input id="product_slug" name="product_slug" type="text" value="<?php $this->h($this->option['slug']); ?>" />
						<p class="description">
							<?php $this->e('Slug for product ID displayed on PayPal Account Panel. It is usefull if you have multiple business on singular account.'); ?>
							<small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small><br />
							<?php $this->e('Set <strong>about 10 alphanumeric letters</strong>. Hypen and product ID follow this slug. <small>ex: example-100</small>'); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<h3><?php $this->e('WordPress Setting'); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th valign="top">
						<label for="dir"><?php $this->e('Directory for File protection'); ?></label>
					</th>
					<td>
						<input id="dir" name="dir" class="regular-text" type="text" value="<?php $this->h($this->option["dir"]); ?>" />
						<p class="description">
							<?php $this->e('Directory to save files. This should be writable and innaccessible via HTTP.'); ?>
							<small>（<?php echo $this->help("dir", $this->_("More &gt;"))?>）</small>
						</p>
						<?php if(!is_writable($this->option["dir"])): ?>
						<p class="error">
							<?php printf($this->_('Directory isn\'t writable. You can\'t upload files. See %s and set appropriate permission.'), $this->help("dir", $this->_("Help")));?>
						</p>
						<?php endif; ?>
						<?php if(!empty($this->message["access"])): ?>
						<p class="error">
							<?php printf($this->_('Directory is accessible via HTTP. See %s and prepend others from direct access.'), $this->help('dir', $this->_('Help'))); ?>
						</p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label for="mypage"><?php $this->e('Purchase History Page'); ?></label>
					</th>
					<td>
						<select id="mypage" name="mypage">
							<option value="0"><?php echo '-------'; ?></option>
							<?php foreach(get_pages() as $p): ?>
							<option value="<?php echo $p->ID;?>"<?php if($p->ID == $this->option['mypage']) echo ' selected="selected"';?>><?php echo $p->post_title; ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php $this->e('You can specify a public page as Purchase History page. If you leave it blank, Purchase History Page will be displayed on admin panel.');  ?>
							<small>（<?php echo $this->help("customize", $this->_("More &gt;"))?>）</small>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label><?php $this->e("Custom Post Type"); ?></label>
					</th>
					<td>
						<label>
							<input type="text" name="custom_post_type_name" value="<?php if(isset($this->option['custom_post_type']['name'])) echo $this->option['custom_post_type']['name']; ?>" />
							<?php $this->e('Custom post name');  ?>
							<small class="description"><?php $this->e('If not needed, leave it blank. ex: eBooks'); ?></small>
						</label><br />
						<label>
							<input type="text" name="custom_post_type_singular" value="<?php if(isset($this->option['custom_post_type']['singular'])) echo $this->option['custom_post_type']['singular']; ?>" />
							<?php $this->e('Custom post name in Singular');  ?>
							<small class="description"><?php $this->e('If you don\'t specified, this same as above field. ex: eBook'); ?></small>
						</label><br />
						<label>
							<input type="text" name="custom_post_type_slug" value="<?php if(isset($this->option['custom_post_type']['slug'])) echo $this->option['custom_post_type']['slug']; ?>" />
							<?php $this->e('Custom post slug');  ?>
							<small class="description"><?php $this->e('Must be alphabetical and lowercase. Used for permalink. ex: ebook'); ?></small>
						</label>
						<p class="description">
							<?php $this->e('If you need detailed settings, please leave it blank and make custom post by yourself. 3rd party plugins will help you.'); ?>
							<small>（<?php echo $this->help("customize", $this->_("More &gt;"))?>）</small>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label><?php $this->e("Payable Post Types"); ?></label>
					</th>
					<td>
						<?php foreach(get_post_types('', 'object') as $post_type): if(false === array_search($post_type->name, array('revision', 'nav_menu_item', 'page', 'attachment', 'lwp_notification'))): ?>
						<label>
							<input type="checkbox" name="payable_post_types[]" value="<?php echo $post_type->name; ?>" <?php if(false !== array_search($post_type->name, $this->option['payable_post_types'])) echo 'checked="checked" '; ?>/>
							<?php echo $post_type->labels->name; ?>
						</label>&nbsp;
						<?php endif; endforeach; ?>
						<p class="description">
							<?php $this->e('You can manually make any post type payable. See detail at how to customize.'); ?>
							<small>（<?php echo $this->help("customize", $this->_("More &gt;"))?>）</small>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label><?php $this->e("Automatic output"); ?></label>
					</th>
					<td>
						<label>
							<input type="radio" name="show_form" value="1" <?php if($this->option['show_form']) echo 'checked="checked" ';?>/>
							<?php $this->e("Show complete form"); ?>
						</label>
						<label>
							<input type="radio" name="show_form" value="0" <?php if(!$this->option['show_form']) echo 'checked="checked" ';?>/>
							<?php $this->e("Manually display form"); ?>
						</label>
						<p class="description">
							<?php $this->e('If you choice automatic display, form will be displayed at bottom of the post content. You can manually put the form parts with short codes or template tags.');?>
							<small>（<?php echo $this->help("customize", $this->_("More &gt;"))?>）</small>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label><?php $this->e("Assets Loading"); ?></label>
					</th>
					<td>
						<label>
							<input type="radio" name="load_assets" value="2" <?php if($this->option['load_assets'] == 2) echo 'checked="checked" ';?>/>
							<?php $this->e("Load plugin default CSS and JS"); ?>
						</label>
						<label>
							<input type="radio" name="load_assets" value="1" <?php if($this->option['load_assets'] == 1) echo 'checked="checked" ';?>/>
							<?php $this->e("Load only JS"); ?>
						</label>
						<label>
							<input type="radio" name="load_assets" value="0" <?php if($this->option['load_assets'] == 0) echo 'checked="checked" ';?>/>
							<?php $this->e("Load no Assets"); ?>
						</label>
						<p class="description">
							<?php $this->e('This plugin load CSS and JS to erabolate &quot;Buy now&quot; button. JS is used for displaying count down timer for campaign.');?>
							<small>（<?php echo $this->help("customize", $this->_("More &gt;"))?>）</small>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
	</div> <!-- // #tab1 -->
	
	<div id="tab-2">
		<h3><?php $this->e('Transfer Setting'); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th valign="top">
						<label><?php $this->e('Accept Transfer'); ?></label>
					</th>
					<td>
						<label><input type="radio" name="transfer" value="0" <?php if(!$this->option['transfer']) echo 'checked="checked"'; ?> /><?php $this->e('Disallow'); ?></label><br />
						<label><input type="radio" name="transfer" value="1" <?php if($this->option['transfer']) echo 'checked="checked"'; ?> /><?php $this->e('Allow'); ?></label>
						<p class="description">
							<?php $this->e('If you accept transfer, users can pay with bank account or something that is not digital transaction.'); ?>
							<?php $this->e('This helps users, but transactional process has a little bit more complex, because you have to check actual bank account to know whether bank deposit transfer has been made.'); ?>	
							<small>（<?php echo $this->help("transfer", $this->_("More &gt;"))?>）</small>
						</p>
					</td>
				</tr>
				<tr>
					<th><label><?php $this->e('Notification Frequency'); ?></label></th>
					<td>
						<label>
							<?php printf(
									$this->_('Send reminder on every %s days'),
									'<input class="short" type="text" name="notification_frequency" id="notification_frequency" value="'.intval($this->option['notification_frequency']).'" />'
							);?>
						</label><br />
						<label>
							<?php printf(
									$this->_('Transaction expires by %s days'),
									'<input class="short" type="text" name="notification_limit" id="notification_limit" value="'.intval($this->option['notification_limit']).'" />'
							);?>
						</label>
						<p class="description">
							<?php $this->e('If you don\'t want to send reminder, set notification frequency to 0. Transfer transaction will be expired after notification limit days will have been past.'); ?><br />
						</p>
					</td>
				</tr>
			</tbody>
		</table>
	</div><!-- // #tab2 -->
	
	<div id="tab-3">
		<h3><?php $this->e("Subscription"); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th valign="top">
						<label><?php $this->e('Accept Subscription'); ?></label>
					</th>
					<td>
						<label><input type="radio" name="subscription" value="0" <?php if(!$this->option['subscription']) echo 'checked="checked"'; ?> /><?php $this->e('Disallow'); ?></label><br />
						<label><input type="radio" name="subscription" value="1" <?php if($this->option['subscription']) echo 'checked="checked"'; ?> /><?php $this->e('Allow'); ?></label>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label><?php $this->e('Subscription Post Type'); ?></label>
					</th>
					<td>
						<?php foreach(get_post_types('', 'object') as $post_type): if(false === array_search($post_type->name, array('revision', 'nav_menu_item', 'page', 'attachment', 'lwp_notification'))): ?>
							<label>
								<input type="checkbox" name="subscription_post_types[]" value="<?php echo $post_type->name; ?>" <?php if(false !== array_search($post_type->name, $this->option['subscription_post_types'])) echo 'checked="checked" '; ?>/>
								<?php echo $post_type->labels->name; ?>
							</label>&nbsp;
						<?php endif; endforeach; ?>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label><?php $this->e('Subscription Format'); ?></label>
					</th>
					<td>
						<label><input type="radio" name="subscription_format" value="more" <?php if($this->option['subscription_format'] == 'more') echo 'checked="checked"'; ?> /><?php $this->e("More tag"); ?></label><br />
						<label><input type="radio" name="subscription_format" value="nextpage" <?php if($this->option['subscription_format'] == 'nextpage') echo 'checked="checked"'; ?> /><?php $this->e("Nextpage Tag"); ?></label><br />
						<label><input type="radio" name="subscription_format" value="all" <?php if($this->option['subscription_format'] == 'all') echo 'checked="checked"'; ?> /><?php $this->e("All"); ?></label>
						<p class="description">
							<?php printf($this->_("If you choose More Tag or Nextpage Tag, non subscriber can see the content before it and the invitation message after it. If you choose all, non subscriber saw only the invitation message. You can customize the invitation message at <a href=\"%s\">Subscription</a>."), admin_url('admin.php')); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
	</div><!-- #tab3 -->

	<div id="tab-4">
		<h3><?php $this->e('Reward Setting'); ?><small class="experimental"><?php $this->e('EXPERIMENTAL'); ?></small></h3>
		<p class="description">
			<?php $this->e('You can allow your users to promote your product and reward for them.'); ?>
		</p>
		<table class="form-table">
			<tbody>
				<tr>
					<th valign="top"><label><?php $this->e('Reward for promoter'); ?></label></th>
					<td>
						<label><input type="radio" name="reward_promoter" value="0" <?php if(!$this->option['reward_promoter']) echo 'checked="checked"'; ?> /><?php $this->e('Disable'); ?></label><br />
						<label><input type="radio" name="reward_promoter" value="1" <?php if($this->option['reward_promoter']) echo 'checked="checked"'; ?> /><?php $this->e('Enable'); ?></label>
						<p class="description">
							<?php $this->e('If you wish to reward for your user who promote your product, enable this. Your users can promote your product and can manage there sales statistic.'); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top"><label for="reward_promotion_margin"><?php $this->e('Reward margin for promotion'); ?></label></th>
					<td>
						<input type="text" class="small-text" name="reward_promotion_margin" id="reward_promotion_margin" value="<?php echo esc_attr($this->option['reward_promotion_margin']); ?>" />%
						<p class="description">
							<?php $this->e('Specify back margin in percentage. This is global setting for promotion and you can override on each product individually.'); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top"><label for="reward_promotion_max"><?php $this->e("Maximum promotion margin"); ?></label></th>
					<td>
						<input type="text" class="small-text" name="reward_promotion_max" id="reward_promotion_max" value="<?php echo esc_attr($this->option['reward_promotion_max']); ?>" />%
						<p class="description">
							<?php $this->e("You can override margin per posts and users. In some case, margin exceeds 100%. You can set limit to avoid paying unexpected amount of reward."); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top"><label><?php $this->e('Reward for author'); ?></label></th>
					<td>
						<label><input type="radio" name="reward_author" value="0" <?php if(!$this->option['reward_author']) echo 'checked="checked"'; ?> /><?php $this->e('Disable'); ?></label><br />
						<label><input type="radio" name="reward_author" value="1" <?php if($this->option['reward_author']) echo 'checked="checked"'; ?> /><?php $this->e('Enable'); ?></label>
						<p class="description">
							<?php $this->e('If you wish to reward for your authors, enable this. Your authors can see how much they sold their contents on sales report dashboard.'); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top"><label for="reward_author_margin"><?php $this->e('Reward margin for author'); ?></label></th>
					<td>
						<input type="text" class="small-text" name="reward_author_margin" id="reward_author_margin" value="<?php echo esc_attr($this->option['reward_author_margin']); ?>" />%
						<p class="description">
							<?php $this->e('Specify back margin in percentage. This is global setting for author and you can override on each author individually.'); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top"><label for="reward_author_max"><?php $this->e("Maximum author margin"); ?></label></th>
					<td>
						<input type="text" class="small-text" name="reward_author_max" id="reward_author_max" value="<?php echo esc_attr($this->option['reward_author_max']); ?>" />%
						<p class="description">
							<?php $this->e("You can override margin per authors. In some case, margin exceeds 100%. You can set limit to avoid paying unexpected amount of reward."); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top"><label for="reward_minimum"><?php $this->e("Reward minimum amount"); ?></label></th>
					<td>
						<input type="text" class="small-text" name="reward_minimum" id="reward_minimum" value="<?php echo esc_attr($this->option['reward_minimum']); ?>" /><?php echo lwp_currency_code();?>
						<p class="description">
							<?php $this->e("Your partners can request payment for reward if reward amount is above this value."); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top"><label><?php $this->e("Reward Request"); ?></label></th>
					<td>
						<?php
							$select = '<select name="reward_pay_after_month">';
							foreach(range(0,2) as $i){
								$select .= '<option value="'.$i.'"';
								if($this->option['reward_pay_after_month'] == $i){
									$select .= ' selected="selected"';
								}
								$select .= '>'.($i ? sprintf($this->_('%d month after'), $i) : $this->_('That month'));
								$select .= '</option>';
							}
							$select .= '</select>';
							printf(
								$this->_('If reward amount exceeds %1$d (%2$s), user can request payment once until %3$s on every month. Reward will be paid at %4$s on %5$s'),
								$this->option['reward_minimum'], lwp_currency_code(), 
								'<input type="text" class="small-text" name="reward_request_limit" value="'.$this->option['reward_request_limit'].'" />',
								'<input type="text" class="small-text" name="reward_pay_at" value="'.$this->option['reward_pay_at'].'" />',
								$select
							);
						?>
					</td>
				</tr>
				<tr>
					<th valign="top"><label for="reward_notice"><?php $this->e("Reward Notice"); ?></label></th>
					<td>
						<textarea cols="80" rows="5" name="reward_notice" id="reward_notice"><?php echo esc_html($this->reward->get_raw_notice());  ?></textarea>
						<p class="description">
							<?php $this->e('This will be displayed on profile page. You can use placeholders: '); ?>
							<?php foreach($this->reward->get_notice_placeholders() as $placeholder => $desc): ?>
								<code><?php echo $placeholder;?></code>
								<small><?php echo esc_html($desc); ?></small> | 
							<?php endforeach; ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
	</div><!-- #tab4 -->
	
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php $this->e("Update"); ?>" />
	</p>
</form>
<h3><?php $this->e("Notes"); ?></h3>

<h4><?php $this->e("Permalink"); ?></h4>
<p>
	<?php printf($this->_('If permalink is enabled, custom post type page will return %s and won\'t be displayed.'), "<em>&quot;404 Not Found&quot;</em>")?><br />
	<?php printf($this->_('Visit <a href="%1$s">%2$s</a> and push &quot;%3$s&quot;. It flushes rewrite rule and you can get the proper display.'), admin_url('options-permalink.php'),__('Permalinks'), __( 'Save Changes' )); ?><br />
</p>

<h4><?php $this->e("Customize"); ?></h4>
<p>
	<?php $this->e("If you are a experienced developper, you can customize this plugin with template tags.");?><br />
	<?php printf($this->_('See detail at %s.'), $this->help("customize", $this->_("Help")));?><br />
	<?php printf($this->_('<a target="_blank" href="%s">Template Tags</a> are also available.'), $this->url."docs/");?>
</p>
