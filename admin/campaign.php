<?php /* @var $this Literally_WordPress */ ?>
<h2>電子書籍キャンペーン</h2>

<?php
/*-------------------------------
 * 個別表示
 */
if(isset($_REQUEST["campaign"]) && $campaign = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->campaign} WHERE ID = %d", $_REQUEST["campaign"]))):
$post = wp_get_single_post($campaign->book_id);
?>
<form method="post">
	<?php wp_nonce_field("lwp_update_campaign"); ?>
	<input type="hidden" name="campaign" value="<?php echo $campaign->ID; ?>" />
	<input type="hidden" name="book_id" value="<?php echo $campaign->book_id; ?>" />
	<table class="form-table">
		<tr class="form-field">
			<th valign="top">
				<label for="book_id">対象電子書籍</label>
			</th>
			<td>
				<strong><?php echo $post->post_title;?></strong>
				<p class="description">
					対象電子書籍は変更できません。
				</p>
			</td>
		</tr>
		<tr class="form-field">
			<th valign="top">
				<label for="price">価格</label>
			</th>
			<td>
				<input type="text" name="price" id="price" value="<?php echo $campaign->price; ?>" />
				<p class="description">
					定価は<?php echo money_format('%7n', get_post_meta($post->ID, "lwp_price", true));?>です。
				</p>
			</td>
		</tr>
		<tr class="form-field">
			<th valign="top">
				<label for="start">開始</label>
			</th>
			<td>
				<input type="text" name="start" id="start" value="<?php echo $campaign->start; ?>" class="date-picker" />
				<p class="description">
					キャンペーンの開始日時です。<span class="cursive">YYYY-mm-dd HH:MM:SS</span>の形式です。
				</p>
			</td>
		</tr>
		<tr class="form-field">
			<th valign="top">
				<label for="end">終了</label>
			</th>
			<td>
				<input type="text" name="end" id="end" value="<?php echo $campaign->end; ?>" class="date-picker" />
				<p class="description">
					キャンペーンの終了日時です。<span class="cursive">YYYY-mm-dd HH:MM:SS</span>の形式です。
				</p>
			</td>
		</tr>
	</table>
	<p class="submit">
		<input type="submit" value="更新" name="submit" class="primary-button" />
	</p>
</form>
<a href="<?php echo admin_url(); ?>edit.php?post_type=ebook&page=lwp-campaign">&laquo;キャンペーン一覧へ戻る</a>
<?php
/*-------------------------------
 * 一覧表示
 */
else:
	//オフセットの有無を確認
	$count = $wpdb->get_row("SELECT count(*) FROM {$this->campaign}")->{"count(*)"};
	$pages = floor($count / 10);
	$pages += ($count % 10 == 0) ? 0 : 1;
	if(isset($_REQUEST["offset"]) && $_REQUEST["offset"] > 1 && $_REQUEST["offset"] <= $pages ){
		$offset = (($_REQUEST["offset"] - 1) * 10).", ";
		$curpage = $_REQUEST["offset"];
	}else{
		$offset = "";
		$curpage = 1;
	}
	$sql = "SELECT * FROM {$this->campaign} ORDER BY `start` DESC LIMIT {$offset}10";
	$campaigns = $wpdb->get_results($wpdb->prepare($sql));
	$pagenator = "<small>全{$count}件:";
	for($i = 1; $i <= $pages; $i++){
		if($i == $curpage){
			$pagenator .= " {$i}";
		}else{
			$pagenator .= ' <a href="'.admin_url()."edit.php?post_type=ebook&page=lwp-campaign&amp;offset=".$i."\">{$i}</a>";
		}
	}
	$pagenator .= "</small>";
?>
<div id="col-container">
	<div id="col-right">
		<div class="col-wrap">
			<form method="post" action="<?php echo admin_url(); ?>edit.php?post_type=ebook&amp;page=lwp-campaign">
				<?php wp_nonce_field("lwp_delete_campaign"); ?>
				<div class="tablenav">
					<div class="alignleft actions">
						<select name="action">
							<option selected="selected" value="">一括操作</option>
							<option value="delete">削除 </option>
						</select>
						<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="適用" />
						<?php echo $pagenator; ?>
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
							<th class="manage-column">対象電子書籍</th>
							<th class="manage-column">期間</th>
							<th class="manage-column">金額</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th class="manage-column check-column">
								<input type="checkbox" />
							</th>
							<th class="manage-column">対象電子書籍</th>
							<th class="manage-column">期間</th>
							<th class="manage-column">金額</th>
						</tr>
					</tfoot>
					<tbody>
						<?php if($campaigns): $counter = 0; foreach($campaigns as $c): $counter++; $p = wp_get_single_post($c->book_id); ?>
						<tr<?php if($counter % 2 == 1) echo ' class="alternate"'; ?>>
							<th class="check-column">
								<input type="checkbox" value="<?php echo $c->ID; ?>" name="campaigns[]" />
							</th>
							<td>
								<p>
									<strong>
										<a href="<?php echo admin_url(); ?>edit.php?post_type=ebook&amp;page=lwp-campaign&amp;campaign=<?php echo $c->ID; ?>">
											<?php echo $p->post_title; ?>
											<small>[<?php echo mysql2date("Y/m/d", $p->post_date); ?>]</small>
										</a>
									</strong>
								</p>
							</td>
							<td>
								<p>
									<?php echo mysql2date("Y/m/d", $c->start)." ~ ".mysql2date("Y/m/d", $c->end); ?>
								</p>
							</td>
							<td>
								<?php echo money_format('%7n', $c->price); ?>
								<small>[<?php echo 100 - round($c->price / get_post_meta($p->ID, "lwp_price", true) * 100)?>%オフ]</small>
							</td>
						</tr>
						<?php endforeach; else: ?>
						<tr>
							<td colspan="4">キャンペーンはまだありません。</td>
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
						<input type="submit" class="button-secondary action" id="doaction2" name="doaction2" value="適用" />
						<?php echo $pagenator; ?>
						<br class="clear" />
					</div>
				</div>
				<!-- .tablenav -->
			</form>
			<div class="description">
				<p>
					<strong>Note:</strong><br />
					対象電子書籍のリンクをクリックすると、キャンペーン内容は変更できます。<br />
					ただし、キャンペーン開催中に価格を変更することはできません。<br />
					<strong>その場合はキャンペーンの終了日を変更し、キャンペーンを終了して下さい。</strong>
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
				<h3>新しいキャンペーンを開始</h3>
				<form method="post">
					<?php wp_nonce_field("lwp_add_campaign"); ?>
										
					<div class="form-field">
						<label for="book_id">対象書籍</label>
						<select id="book_id" name="book_id">
							<option disabled="disabled" selected="selected">選択してください</option>
							<?php foreach(get_posts("post_type=ebook") as $p): ?>
							<option value="<?php echo $p->ID; ?>"><?php echo $p->post_title; ?></option>
							<?php endforeach; ?>
						</select>
						<p>
							キャンペーンの対象となる電子書籍です。
						</p>
					</div>
					<!-- .form-field ends -->
					
					<div class="form-field">
						<label for="price">価格</label>
						<input type="text" id="price" name="price" />
						<p>
							キャンペーン価格です。
						</p>
					</div>
					<!-- .form-field ends -->
					
					<div class="form-field">
						<label for="start">開始</label>
						<input type="text" id="start" name="start" class="date-picker" />
						<p>
							キャンペーンの開始日時です。<span class="cursive">YYYY-mm-dd HH:MM:SS</span>の形式です。
						</p>
					</div>
					<!-- .form-field ends -->
					
					<div class="form-field">
						<label for="end">終了</label>
						<input type="text" id="end" name="end" class="date-picker" />
						<p>
							キャンペーンの終了日時です。<span class="cursive">YYYY-mm-dd HH:MM:SS</span>の形式です。
						</p>
					</div>
					<!-- .form-field ends -->
					
					<p class="submit">
						<input type="submit" value="新規キャンペーンを開始" id="submit" name="submit" class="button">
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

<?php
/*
 * 分岐終了
 -------------------------------*/
endif;
