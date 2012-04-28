<?php
	/* @var $this Literally_WordPress */
	/* @var $post object */
	/* @var $files array */
?>
<?php wp_nonce_field("lwp_price", "_lwpnonce", false); ?>
<table>
	<tbody>
		<tr>
			<th>
				<label for="lwp_price"><?php $this->e("Price");?></label>
			</th>
			<td>
				<input type="text" name="lwp_price" id="lwp_price" value="<?php echo get_post_meta($post->ID, "lwp_price", true); ?>" /> (<?php echo lwp_currency_code(); ?>)
			</td>
		</tr>
	</tbody>
</table>
<?php if(intval(get_post_meta($post->ID, "lwp_price", true) == 0)):?>
<p class="error">
	<?php $this->e("This post is free.");?>
</p>
<?php endif; ?>
<?php if(empty($files)): ?>
<p class="error">
	<?php $this->e("This Post has no files. You can add files from media uploader."); ?>
</p>
<?php endif; ?>