<?php /* @var $this Literally_WordPress */ ?>
<!-- Sell post -->
<h3><?php printf($this->_('About %s'), $this->_('Digital Contents'));?></h3>
<p class="description">
	<?php $this->e('You can sell post content itself or attached downloadble content like mp3, ePub, zip and so on. Typically you can sell your ebook, music video, how-tos and so on.'); ?>
</p>

<h3><?php $this->e('Setting'); ?></h3>
<table class="form-table">
	<tbody>
		<tr>
			<th valign="top">
				<label for="dir"><?php $this->e('Directory for File protection'); ?></label>
			</th>
			<td>
				<input id="dir" name="dir" class="regular-text" type="text" value="<?php echo esc_attr($this->option["dir"]); ?>" />
				<p class="description">
					<?php $this->e('Directory to save files. This should be writable and innaccessible via HTTP.'); ?>
					<small>（<?php echo $this->help("dir", $this->_("More &gt;"))?>）</small>
				</p>
				<?php if(($dir_check = $this->post->directory_safety_warning())): ?>
					<p class="invalid">
						<?php echo esc_html($dir_check);?>
					</p>
				<?php endif; ?>
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
				<?php foreach(get_post_types('', 'object') as $post_type): if(false === array_search($post_type->name, array('revision', 'nav_menu_item', 'page', 'attachment', 'lwp_notification', $this->ios->post_type))): ?>
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
			<th valign="top"><?php $this->e('User XML-RPC API'); ?></th>
			<td>
				<label>
					<input type="checkbox" name="use_xmlrpc_api" value="1"<?php if($this->post->xmlrpc) echo ' checked="checked"'; ?> />
					<?php $this->e('Enable XML-RPC API endpoint'); ?>
				</label>
				<p class="description"><?php $this->e('XML-RPC provides interface to other softwares. This is for experienced developer.'); ?></p>
				<?php if($this->post->xmlrpc): ?>
					<?php if(!get_option('enable_xmlrpc')): ?>
						<p class="invalid"><?php printf($this->_('WordPress XML-RPC endpoint is not working. Please enable it <a href="%s">here</a>.'), admin_url('options-writing.php')); ?></p>
					<?php else: ?>
						<p class="valid"><?php $this->e('XML-RPC API is working in order.'); ?></p>
					<?php endif; ?>
				<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>

<h3>
	<?php $this->e('Cooperating with Smartphone Application'); ?>
	<small class="experimental"><?php $this->e('EXPERIMENTAL'); ?></small>
</h3>
<p class="description">
	<?php $this->e('Smartphone applications have so-called <strong>In-App Purchase</strong>. LWP can be a content delivery server for them with XML-RPC protocol. Now iOS and android(Google Play) are supoorted.'); ?>
	<?php printf($this->_('Once you enable it, new post type will be created <a href="%1$s">here</a> and you can access <a href="%2$s">API Manual</a>.'), admin_url('edit.php?post_type='.$this->ios->post_type), admin_url('edit.php?post_type='.$this->ios->post_type.'&page=lwp-ios-api'));?>
	<br />
	<?php $this->e('<strong>Note: </strong> This feature is only for experienced developers.');?>
</p>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->e('Enable iOS In App Purchase');  ?></th>
			<td>
				<label><input type="radio" name="ios" value="1" <?php if($this->option['ios']) echo 'checked="checked"'; ?> /><?php $this->e('Enabled'); ?></label><br />
				<label><input type="radio" name="ios" value="0" <?php if(!$this->option['ios']) echo 'checked="checked"'; ?> /><?php $this->e('Disabled'); ?></label>
			</td>
		</tr>
		<tr>
			<th><?php $this->e('Enable android In-app Billing');  ?></th>
			<td>
				<label><input type="radio" name="android" value="1" <?php if($this->option['android']) echo 'checked="checked"'; ?> /><?php $this->e('Enabled'); ?></label><br />
				<label><input type="radio" name="android" value="0" <?php if(!$this->option['android']) echo 'checked="checked"'; ?> /><?php $this->e('Disabled'); ?></label><br />
				<label for="android_public_key"><?php $this->e('Android Public Key'); ?></label>
				<textarea name="android_public_key" id="android_public_key" style="width:90%" rows="6"><?php echo esc_html($this->option['android_pub_key']); ?></textarea>
				<p class="description">
					<?php printf($this->_('To verify receipt sent from Android application, WordPress needs Public key. You can get it at <a href="%s">Google Play Developer Console</a>. '), 'https://play.google.com/apps/publish/Home'); ?><br />
					<?php $this->e('<strong>Note: </strong>PHP\'s OpenSSL can\'t treat Google Play\'s RSA public key as-is. It should be converted like below and paste the result above.'); ?><br />
				</p>
				<pre class="bash">
<span># 0. <?php $this->e('Copy and paste public key and save it as pubkey.txt.'); ?> </span>
<span># 1. <?php $this->e('Decode public key with Base64 and save it.'); ?> </span>
base64 -D -i pubkey.txt -o pubkey.der
<span># 2. <?php $this->e('Convert DER pubkey to PEM format.'); ?> </span>
openssl rsa -inform DER -outform PEM -pubin -in pubkey.der -out pubkey.pem
<span># 3. <?php $this->e('Encode public key and save it.'); ?> </span>
base64 -i pubkey.pem -o pubkey_to_paste.txt </pre>
			</td>
		</tr>
		<tr>
			<th><?php $this->e('Post Type public');  ?></th>
			<td>
				<label><input type="radio" name="ios_public" value="1" <?php if($this->option['ios_public']) echo 'checked="checked"'; ?> /><?php $this->e('Public'); ?></label><br />
				<label><input type="radio" name="ios_public" value="0" <?php if(!$this->option['ios_public']) echo 'checked="checked"'; ?> /><?php $this->e('Hidden'); ?></label>
				<p class="description">
					<?php $this->e('If you choose public, this post type is publicly displayed and can be accessed on Web site.'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th><?php $this->e('Availability');  ?></th>
			<td>
				<label><input type="radio" name="ios_available" value="1" <?php if($this->option['ios_available']) echo 'checked="checked"'; ?> /><?php $this->e('Available from Web site'); ?></label><br />
				<label><input type="radio" name="ios_available" value="0" <?php if(!$this->option['ios_available']) echo 'checked="checked"'; ?> /><?php $this->e('Only available with Smartphone'); ?></label>
				<p class="description">
					<?php $this->e('If you enable this, post type for smartphone can be bought from Web site.'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th><?php $this->e('Force SSL on XML-RPC'); ?></th>
			<td>
				<label><input type="radio" name="ios_force_ssl" value="2" <?php if($this->option['ios_force_ssl'] == 2) echo 'checked="checked"'; ?> /><?php $this->e('Force'); ?></label><br />
				<label><input type="radio" name="ios_force_ssl" value="1" <?php if($this->option['ios_force_ssl'] == 1) echo 'checked="checked"'; ?> /><?php $this->e('Depends on WordPress setting'); ?></label><br />
				<label><input type="radio" name="ios_force_ssl" value="0" <?php if(!$this->option['ios_force_ssl']) echo 'checked="checked"'; ?> /><?php $this->e('Do nothing'); ?></label>
				<p class="description"><?php
					printf(
						$this->_('Your current WordPress setting: Admin Panel SSL = <strong>%1$s</strong> , Login SSL = <strong>%2$s</strong> '),
						(FORCE_SSL_ADMIN ? 'ON': 'OFF'),
						(FORCE_SSL_LOGIN ? 'ON': 'OFF')); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th><?php $this->e('Endpoint'); ?></th>
			<td><code><?php echo esc_html(home_url('/xmlrpc.php', ($this->ios->is_ssl_forced() ? 'https' : 'http' )) ); ?></code></td>
		</tr>
		<tr>
			<th><?php $this->e('XML-RPC availability'); ?></th>
			<td>
				<?php if(get_option('enable_xmlrpc')): ?>
					<?php $this->e('Enabled'); ?>
				<?php else: ?>
					<?php printf($this->_('XML-RPC is disabled. Please enable it <a href="%s">here</a>'), admin_url('options-writing.php')); ?>
				<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>