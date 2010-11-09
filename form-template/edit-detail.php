<div class="mcm_metabox">
<?php wp_nonce_field("lwp_price", "_lwpnonce", false); ?>
<table>
	<tbody>
		<tr>
			<th>
				<label>定価</label>
			</th>
			<td>
				<input type="text" name="lwp_price" value="<?php echo get_post_meta($_GET["post"], "lwp_price", true); ?>" />円
			</td>
		</tr>
	</tbody>
</table>
<?php if(empty($files)): ?>
<p class="error">
ファイルをアップロードしましたか？<br />
もしまだなら、ファイルアップローダーから電子書籍用ファイルを追加しましょう。
</p>
<?php endif; ?>
</div>