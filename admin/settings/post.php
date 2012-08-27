<!-- Sell post -->
<h3><?php printf($this->_('About %s'), $this->_('Post sell'));?></h3>
<p class="description">
	<?php $this->e('You can sell post content itself or attached downloadble content. Typically you can sell your ebook, music video, how-tos and so on.'); ?>
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
	</tbody>
</table>

<h3>
	<?php $this->e('iOS non-consumable Product'); ?>
	<small class="experimental"><?php $this->e('EXPERIMENTAL'); ?></small>
</h3>
<p class="description">
	<?php $this->e('You can manage iOS non-consumable product with WordPress.'); ?>
</p>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->e('Enable iOS product');  ?></th>
			<td>
				<label><input type="radio" name="ios" value="1" <?php if($this->option['ios']) echo 'checked="checked"'; ?> /><?php $this->e('Enabled'); ?></label><br />
				<label><input type="radio" name="ios" value="0" <?php if(!$this->option['ios']) echo 'checked="checked"'; ?> /><?php $this->e('Disabled'); ?></label>
			</td>
		</tr>
		<tr>
			<th><?php $this->e('Post Type public');  ?></th>
			<td>
				<label><input type="radio" name="ios_public" value="1" <?php if($this->option['ios_public']) echo 'checked="checked"'; ?> /><?php $this->e('Public'); ?></label><br />
				<label><input type="radio" name="ios_public" value="0" <?php if(!$this->option['ios_public']) echo 'checked="checked"'; ?> /><?php $this->e('Hidden'); ?></label>
				<p class="description">
					<?php $this->e('If you choose public, this post type is publicly displayed.'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th><?php $this->e('Availability');  ?></th>
			<td>
				<label><input type="radio" name="ios_available" value="1" <?php if($this->option['ios_available']) echo 'checked="checked"'; ?> /><?php $this->e('Available from Web site'); ?></label><br />
				<label><input type="radio" name="ios_available" value="0" <?php if(!$this->option['ios_available']) echo 'checked="checked"'; ?> /><?php $this->e('Only available with iOS'); ?></label>
				<p class="description">
					<?php $this->e('If you enable this, iOS post type is can be bought from Web site.'); ?>
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
	</tbody>
</table>