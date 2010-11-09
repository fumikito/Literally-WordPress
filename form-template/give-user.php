<h3>電子書籍のプレゼント</h3>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><label for="ebook_id">電子書籍名</label></th>
			<td>
				<select name="ebook_id" id="ebook_id">
					<option selected="selected" value="">選択してください</option>
				<?php foreach($ebooks as $e): ?>
					<option value="<?php echo $e->ID; ?>"><?php echo $e->post_title; ?></option>
				<?php endforeach;?>
				</select>
			</td>
		</tr>
	</tbody>
</table>