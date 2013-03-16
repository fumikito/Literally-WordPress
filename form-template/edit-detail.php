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
				<a title="<?php $this->e('File Manager'); ?>" class="thickbox button" href="<?php echo admin_url('media-upload.php?post_id='.$post->ID.'&tab=ebook&TB_iframe=1'); ?>">
					<?php $this->e('Manage File'); ?>
				</a>
			</td>
		</tr>
		<tr>
			<th><label for="lwp_price"><?php $this->e("Price");?></label></th>
			<td><input type="text" name="lwp_price" id="lwp_price" value="<?php echo lwp_original_price(); ?>" /> (<?php echo lwp_currency_code(); ?>)</td>
		</tr>
		<tr>
			<th><?php $this->e('Download Limit'); ?></th>
			<td>
				<label><input type="text" class="short" name="lwp_donwload_limit_days" id="lwp_donwload_limit_days" value="<?php echo $this->get_download_limit($post->ID); ?>" /> <?php $this->e('Days'); ?></label>
				<a class="thickbox" href="#TB_inline?width=auto&amp;height=auto&amp;inlineId=lwp-faq-about-limit&amp;modal=true" title="<?php $this->e('About Download Limit'); ?>">[?]</a>
				<br />
				<div id="lwp-faq-about-limit" class="lwp-hidden">
					<div class="lwp-faq-inner">
						<p><?php $this->e('You can set download limit to digital contents in 2 ways.'); ?></p>
						<p><strong><?php $this->e('1. Limit by days'); ?></strong></p>
						<p><?php $this->e('For example, If you set 10 days as download limit, a user will not be able to donwload 10 days after transaction. &quot;0&quot; means no limit. This option is valid for all files associated to this post.'); ?></p>
						<p><strong><?php $this->e('2. Limit by times'); ?></strong></p>
						<p><?php $this->e('For example, If you set 10 times as donwload limit, a user can donwload only 10 times. &quot;0&quot; means no limit. This option must be set to each files on the file manager.'); ?></p>
						<p class="info"><?php $this->e('You can set both limit, but simple usage is recommended. Too many limitation will confuse your users.'); ?></p>
						<p class="lwp-faq-close">
							<a class="button" href="#" onclick="if(tb_remove) tb_remove(); return false;"><?php $this->e('Close'); ?></a>
						</p>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<th><?php $this->e('Campaign'); ?></th>
			<td>
				<?php if(lwp_on_sale($post)): $campaign = $lwp->campaign_manager->get_campaign($post->ID, date('Y-m-d H:i:s')); ?>
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