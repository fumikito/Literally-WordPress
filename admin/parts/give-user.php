<?php /* @var $this Literally_WordPress */ ?>
<h3><?php $this->e('Give Downloadable contents.'); ?></h3>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><label for="ebook_id"><?php $this->e('Contents to Give'); ?></label></th>
			<td>
				<select name="ebook_id" id="ebook_id">
					<option selected="selected" value=""><?php $this->e('Please Select');  ?></option>
				<?php foreach($ebooks as $e): ?>
					<option value="<?php echo $e->ID; ?>">
						<?php echo get_post_type_object($e->post_type)->labels->name.': '.$e->post_title.' ('.number_format_i18n($e->price).' '.lwp_currency_code().')'; ?>
					</option>
				<?php endforeach;?>
				</select>
			</td>
		</tr>
	</tbody>
</table>