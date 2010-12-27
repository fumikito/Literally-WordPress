<h2>対応端末</h2>

<?php
	//端末を取得
	$devices = $wpdb->get_results("SELECT * FROM {$this->devices}");
?>
<script type="text/javascript">
//<![CDATA[
	function lwp_confirm_delete(event)
	{
		var select = jQuery(event.target).prev().attr("value");
		if(select != "delete" || !confirm("削除してもよろしいですか？")){
			event.preventDefault();
		}
	}
//]]>
</script>
<div id="col-container">
	<div id="col-right">
		<div class="col-wrap">
			<form method="post" action="<?php echo admin_url(); ?>edit.php?post_type=ebook&amp;page=lwp-devices">
				<?php wp_nonce_field("lwp_delete_devices"); ?>
				<div class="tablenav">
					<div class="alignleft actions">
						<select name="action">
							<option selected="selected" value="">一括操作</option>
							<option value="delete">削除 </option>
						</select>
						<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="適用" onclick="lwp_confirm_delete(event);"/>
						<br class="clear" />
					</div>
				</div>
				<!-- .tablenav -->
				<table class="widefat tag fixed" cellspacing="0">
					<thead>
						<tr>
							<th class="manage-column check-column">
								<input type="checkbox" />
							</th>
							<th class="manage-column">端末名</th>
							<th class="manage-column">スラッグ</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th class="manage-column check-column">
								<input type="checkbox" />
							</th>
							<th class="manage-column">端末名</th>
							<th class="manage-column">スラッグ</th>
						</tr>
					</tfoot>
					<tbody>
						<?php if($devices): $counter = 0; foreach($devices as $d): $counter++; ?>
						<tr<?php if($counter % 2 == 1) echo ' class="alternate"'; ?>>
							<th class="check-column">
								<input type="checkbox" value="<?php echo $d->ID; ?>" name="devices[]" />
							</th>
							<td><?php $this->h($d->name); ?></td>
							<td><?php $this->h($d->slug); ?></td>
						</tr>
						<?php endforeach; else: ?>
						<tr>
							<td colspan="3">登録されている端末はありません。</td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
				<div class="tablenav">
					<div class="alignleft actions">
						<select name="action">
							<option selected="selected" value="">一括操作</option>
							<option value="delete">削除 </option>
						</select>
						<input type="submit" class="button-secondary action" id="doaction2" name="doaction2" value="適用" onclick="lwp_confirm_delete(this);" />
						<br class="clear" />
					</div>
				</div>
				<!-- .tablenav -->
			</form>
			<div class="description">
				<p>
					<strong>Note:</strong><br />
					端末は削除できますが、ファイルの削除自体は手動で行ってください。
				</p>
			</div>
			<!-- .description ends -->
		</div>
		<!-- .col-wrap ends -->
	</div>
	<!-- #col-right ends -->
	
	<div id="col-left">
		<div class="col-wrap">
			<div class="form-wrap">
				<h3>新しい端末を追加</h3>
				<form method="post">
					<?php wp_nonce_field("lwp_add_device"); ?>
										
					<div class="form-field">
						<label for="device_name">端末名</label>
						<input type="text" id="device_name" name="device_name" />
						<p>
							端末の名前です。
						</p>
					</div>
					<!-- .form-field ends -->
					
					<div class="form-field">
						<label for="device_slug">端末のスラッグ</label>
						<input type="text" id="device_slug" name="device_slug" style="ime-mode:disabled;" />
						<p>
							端末のスラッグです。アイコンを表示するときなどに役立ちます。
						</p>
					</div>
					<!-- .form-field ends -->
					
					<p class="submit">
						<input type="submit" value="新しい端末を追加" id="submit" name="submit" class="button">
					</p>
					<!-- .submit ends -->
				</form>
			</div>
			<!-- .form-wrap ends -->
		</div>
		<!-- .col-wrap ends -->
	</div>
	<!-- #col-left ends -->
</div>
<!-- #col-container -->
