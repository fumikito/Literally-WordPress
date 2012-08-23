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