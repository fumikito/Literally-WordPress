<div class="mcm_metabox">
<?php wp_nonce_field("lwp_price", "_lwpnonce", false); ?>
<table>
	<tbody>
		<tr>
			<th>
				<label for="lwp_price">定価</label>
			</th>
			<td>
				<input type="text" name="lwp_price" id="lwp_price" value="<?php echo get_post_meta($_GET["post"], "lwp_price", true); ?>" />円
			</td>
		</tr>
		<tr>
			<th>
				<label for="lwp_number">文字量</label>
			</th>
			<td>
				<input type="text" name="lwp_number" id="lwp_number" value="<?php echo get_post_meta($_GET["post"], "lwp_number", true); ?>" />
				<p class="desc">適宜決めてください。文字数か原稿用紙換算枚数が適当です。</p>
			</td>
		</tr>
		<tr>
			<th>
				<label for="lwp_isbn">ISBN</label>
			</th>
			<td>
				<input type="text" name="lwp_isbn" id="lwp_isbn" value="<?php echo get_post_meta($_GET["post"], "lwp_isbn", true); ?>" />
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