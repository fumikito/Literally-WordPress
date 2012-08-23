<h3><?php printf($this->_('About %s'), $this->_('Miscellaneous'));?></h3>
<p class="description"><?php $this->e('These settigs are all optional.'); ?></p>

<h3><?php $this->e('Settings');  ?></h3>
<table class="form-table">
	<tbody>
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
		<tr>
			<th valign="top"><label><?php $this->e('Use Cache Engine'); ?></label></th>
			<td>
				<label><input type="radio" name="use_proxy" value="0" <?php if(!$this->option['use_proxy']) echo 'checked="checked"'; ?> /><?php $this->e('Disable'); ?></label><br />
				<label><input type="radio" name="use_proxy" value="1" <?php if($this->option['use_proxy']) echo 'checked="checked"'; ?> /><?php $this->e('Enable'); ?></label>
				<p class="description">
					<?php $this->e('If you use cache plugin or proxy cache(i.e. Nginx), enable this. LWP output js to help session tracking.'); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>
