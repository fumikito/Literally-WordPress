<?php
	/**
	* @var  Literally_WordPress $this
	*/
	$this;
	
	$file = false;
	$uploading = false;
	$uploaded = false;
	$updating = false;
	$updated = false;
	$deleted = false;
	$error = false;
	$message = array();
	//POSTが設定されていたら、アップロードアクション
	if(
		isset($_POST["_wpnonce"]) && wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_upload")
		&& isset($_REQUEST["post_id"]) && $_GET["tab"] == "ebook"
	){
		//更新状態を変更
		$uploaded = true;
		//タイトルチェック
		if(empty($_REQUEST["title"]))
			$message[] = "タイトルが空です。";
		//公開状態のチェック
		if(!is_numeric($_REQUEST["public"]))
			$message[] = "公開状態を選択してください。";
		//ファイルのチェック
		if(empty($_FILES["file"]))
			$message[] = "ファイルが指定されていません。";
		//ファイルのエラーチェック
		elseif($this->file_has_error($_FILES["file"]))
			$message[] = $this->file_has_error($_FILES["file"]);
		//エラー状態のチェック
		if(empty($message)){
			$this->upload_file($_REQUEST["post_id"], $_REQUEST["title"], $_FILES["file"]["name"], $_FILES["file"]["tmp_name"], $_REQUEST["desc"], $_REQUEST["public"], $_REQUEST["free"]);
		}else
			$error = true;
	}
	//更新用アクション
	elseif(
		isset($_POST["_wpnonce"]) && wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_update")
		&& isset($_REQUEST["post_id"]) && $_GET["tab"] == "ebook" && isset($_REQUEST["file_id"])
	){
		$updating = true;
	}
	//更新完了アクション
	elseif(
		isset($_POST["_wpnonce"]) && wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_updated")
		&& isset($_REQUEST["post_id"]) && $_GET["tab"] == "ebook" && isset($_REQUEST["file_id"])
	){
		$updated = true;
		if($this->update_file($_REQUEST["file_id"], $_REQUEST["title"], $_REQUEST["desc"], $_REQUEST["public"], $_REQUEST["free"]))
			$message[] = "ファイルの更新に成功しました";
		else
			$message[] = "ファイルの更新に失敗しました";
	}
	//削除完了アクション
	elseif(
		isset($_POST["_wpnonce"]) && wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_deleted")
		&& isset($_REQUEST["post_id"]) && $_GET["tab"] == "ebook" && isset($_REQUEST["file_id"])
	){
		$deleted = true;
		if($this->delete_file($_REQUEST["file_id"]))
			$message[] = "削除しました。";
		else
			$message[] = "削除に失敗しました";
	}else
		$uploading = true;
	
	if($updating || $updated)
		$file = $this->get_files($_REQUEST["post_id"], $_REQUEST["file_id"]);
	$files = $this->get_files($_GET["post_id"]);
?><form method="post" class="media-upload-form type-form validate" enctype="multipart/form-data">
	<?php
		if($updating || $updated){
			wp_nonce_field("lwp_updated");
			echo '<input type="hidden" name="file_id" value="'.$_REQUEST["file_id"].'" />';
		}
		else
			wp_nonce_field("lwp_upload");
			
	?>
	<h3 class="media-title">電子書籍ファイルの管理</h3>
	<div class="media-items">
		<h4 class="media-sub-title">
		<?php if($updating || $updated): ?>
			ファイルの情報を更新してください
		<?php else: ?>
			ファイルをアップロードしてください
		<?php endif; ?>
		</h4>
		<?php if(!empty($message)): ?>
		<p class="error">
			<?php foreach($message as $m): ?>
			<?php echo $m; ?><br />
			<?php endforeach; ?>
		</p>
		<?php endif; ?>
		<table class="describe">
			<tbody>
				<tr>
					<th scope="row" valign="top" class="label">
						<label for="title">名称</label>
					</th>
					<td class="field">
						<input id="title" name="title" type="text" value="<?php if($updating || $updated) $this->h($file->name); ?>"/>
						<p class="help">このファイルの名前です。例：iBooks用ePub【第二版】</p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top" class="label">
						<label>公開状態</label>
					</th>
					<td class="field">
						<label><input name="public" type="radio" value="1"<?php if(!($updating || $updated) || $file->public == 1) echo ' checked="checked"'; ?> />公開</label>
						<label><input name="public" type="radio" value="0"<?php if(($updating || $updated) && $file->public == 0) echo ' checked="checked"'; ?> />非公開</label>
						<p class="help">このファイルの公開状態です。</p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top" class="label">
						<label>立ち読み</label>
					</th>
					<td class="field">
						<label><input name="free" type="radio" value="0"<?php if(!($updating || $updated) || $file->free == 0) echo ' checked="checked"'; ?> />できない</label>
						<label><input name="free" type="radio" value="1"<?php if(($updating || $updated) && $file->free == 1) echo ' checked="checked"'; ?> />できる</label>
						<p class="help">
							「立ち読みできる」とは<strong>購入せずにすべてをダウンロードできる状態</strong>を意味します。<br />
							「部分的な立ち読み」を提供したい場合は<strong>立ち読み用のファイル</strong>を用意した上で「立ち読みできる」を指定してください。
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top" class="label">
						<label for="desc">説明</label>
					</th>
					<td class="field">
						<textarea id="desc" name="desc"><?php if($updating || $updated) $this->h($file->desc); ?></textarea>
						<p class="help">※必要なら記入してください。</p>
					</td>
				</tr>
				<?php if(!($updating || $updated)): ?>
				<tr>
					<th scope="row" valign="top" class="label">
						<label for="file">ファイル</label>
					</th>
					<td class="field">
						<input id="file" name="file" type="file" />
					</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="submit" value="<?php echo ($updating || $updated) ? '更新' : 'アップロード'; ?>" />
			<?php if(($updating || $updated)): ?>
			<a class="button" href="">新規登録へ</a>
			<?php endif; ?>
		</p>
	</div>
</form>

<?php if(!empty($files)): ?>
<div id="media-upload">
	<div id="media-items" style="margin:1em">
		<h4 class="media-sub-title">登録済み電子書籍ファイル</h4>
		<?php foreach($files as $f): ?>
		<div class="media-item">
			<img class="pinkynail" src="<?php echo $this->url; ?>/assets/icons/<?php echo end(explode(".", $f->file)); ?>.png" />
			<form method="post" class="describe-toggle-on">
				<?php wp_nonce_field("lwp_deleted"); ?>
				<input type="hidden" value="<?php echo $f->ID; ?>" name="file_id" />
				<input type="submit" class="button" value="削除" onclick="if(!confirm('削除してよろしいですか？')) return false;" />
			</form>
			<form method="post" class="describe-toggle-on">
				<?php wp_nonce_field("lwp_update"); ?>
				<input type="hidden" value="<?php echo $f->ID; ?>" name="file_id" />
				<input type="submit" class="button" value="更新" />
			</form>
			<div class="filename">
				<span class="title"><?php $this->h($f->name); ?></span>
			</div>
			<br style="clear:both" />
		</div>
		<!-- .media-items -->
		
		<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>