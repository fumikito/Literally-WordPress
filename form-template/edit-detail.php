<?php
	/* @var $lwp Literally_WordPress */
	global $lwp;
	/* @var $this LWP_Post */
	/* @var $post object */
	/* @var $files array */
	$files = $this->get_files($post->ID);
	wp_nonce_field("lwp_price", "_lwpnonce", false);
?>
<?php if(empty($files)): ?>
<p class="error">
	<?php $this->e("This Post has no files. You can add files from media uploader."); ?>
</p>
<?php endif; ?>
<table class="lwp-metabox-table">
	<tbody>
		<tr>
			<th><?php $this->e('Files'); ?></th>
			<td>
				<a title="<?php $this->e('Upload File'); ?>" class="thickbox button" href="<?php echo admin_url('media-upload.php?post_id='.$post->ID.'&tab=ebook&TB_iframe=1'); ?>"><?php $this->e('Open File Manager'); ?></a>
			</td>
		</tr>
		<tr>
			<th><label for="lwp_price"><?php $this->e("Price");?></label></th>
			<td><input type="text" name="lwp_price" id="lwp_price" value="<?php echo lwp_original_price(); ?>" /> (<?php echo lwp_currency_code(); ?>)</td>
		</tr>
		<tr>
			<th><?php $this->e('Campaign'); ?></th>
			<td>
				<?php if(lwp_on_sale($post)): $campaign = $lwp->get_campaign($post->ID); ?>
					<?php printf($this->_('Till <a href="%1$s">%2$s</a>'), admin_url('admin.php?page=lwp-campaign&campaign='.$campaign->ID), lwp_campaign_end($post, false));?>
				<?php else: ?>
					<a href="<?php echo admin_url('admin.php?page=lwp-campaign'); ?>"><?php $this->e('Not on Sale'); ?></a>
				<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>
<?php if(!lwp_original_price($post)):?>
<p class="error">
	<?php $this->e("This post is free.");?>
</p>
<?php endif; ?>