<?php /* @var $this Literally_WordPress */ ?>
<h2><?php $this->e("General Setting"); ?></h2>
<form method="post">
	<?php wp_nonce_field("lwp_update_option"); ?>
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
				<th valign="top">
					<label for="account"><?php $this->e('Bank Account'); ?></label>
				</th>
				<td>
					<textarea cols="40" rows="5" name="account" id="account"><?php $this->h($this->option['account'])?></textarea>
					<p class="description">
						<?php $this->e('This account will be shown on thank you screen and thank you mail. Please enter actual bank account which user can transfer their deposit to.'); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="thankyou"><?php $this->e('Thank you Message'); ?></label>
				</th>
				<td>
					<textarea cols="40" rows="5" name="thankyou" id="thankyou"><?php $this->h($this->option['thankyou'])?></textarea>
					<p class="description">
						<?php $this->e('This message will be shown on thank you screen and thank you mail. You can use place holders below.'); ?><br />
						<strong>%account%, %user_display%, %price%, %item%</strong>
					</p>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="transfer_footer"><?php $this->e('Mail Footer'); ?></label>
				</th>
				<td>
					<textarea cols="40" rows="5" name="transfer_footer" id="transfer_footer"><?php $this->h($this->option['transfer_footer'])?></textarea>
					<p class="description">
						<?php $this->e('This footer is displayed on notify e-mail footer. You can use place holders below.'); ?><br />
						<strong>%site_name%, %site_description%, %url%</strong>
					</p>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="notification"><?php $this->e('Notification Mail Body'); ?></label>
				</th>
				<td>
					<textarea cols="40" rows="5" name="notification" id="notification"><?php $this->h($this->option['notification'])?></textarea>
					<p class="description">
						<?php $this->e('This message will be displayed on reminder for transfer. You can use place holders below.'); ?><br />
						<strong>%item%, %price%, %user_display%, %account%</strong>
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
					<?php foreach(get_post_types('', 'object') as $post_type): if(false === array_search($post_type->name, array('revision', 'nav_menu_item', 'page', 'attachment'))): ?>
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
