<h2>電子書籍設定</h2>
<form method="post">
	<?php wp_nonce_field("lwp_update_option"); ?>
	<table class="form-table">
		<tbody>
			<tr>
				<th valign="top">
					<label for="marchant_id">Paypal マーチャントID</label>
				</th>
				<td>
					<input id="marchant_id" name="marchant_id" class="regular-text" type="text" value="<?php $this->h($this->option["marchant_id"]); ?>" />
					<p class="description">
						Paypalから発行されているセキュアなIDです。<small>（<a href="<?php echo $this->help("account"); ?>">もっと詳しく</a>）</small>
					</p>
					<?php if(!preg_match("/^[a-zA-Z0-9]+$/", $this->option["marchant_id"])): ?>
					<p class="error">
					もしかしたらこのIDはまちがっているかもしれません。<a href="<?php echo $this->help("account"); ?>">ヘルプ</a>を確認して、セキュアIDを取得してください。
					</p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="token">Paypal PDTトークン</label>
				</th>
				<td>
					<input id="token" name="token" class="regular-text" type="text" value="<?php $this->h($this->option["token"]); ?>" />
					<p class="description">
						Paypalから発行されるトークンです。購入履歴の同期に必要です。<small>（<a href="<?php echo $this->help("account"); ?>">もっと詳しく</a>）</small>
					</p>
					<?php if(!preg_match("/^[a-zA-Z0-9]+$/", $this->option["token"])): ?>
					<p class="error">
					もしかしたらこのトークンはまちがっているかもしれません。<a href="<?php echo $this->help("account"); ?>">ヘルプ</a>を確認して、PDTトークンを取得してください。
					</p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="dir">ファイル保存用ディレクトリ</label>
				</th>
				<td>
					<input id="dir" name="dir" class="regular-text" type="text" value="<?php $this->h($this->option["dir"]); ?>" />
					<p class="description">
						電子書籍のファイルを保存するディレクトリです。書き込み可能であり、外部からアクセスできない必要があります。<small>（<a href="<?php echo $this->help("dir"); ?>">もっと詳しく</a>）</small>
					</p>
					<?php if(!is_writable($this->option["dir"])): ?>
					<p class="error">
					ディレクトリに書き込みできません。このままではファイルをアップロードできません。<a href="<?php echo $this->help("dir"); ?>">ヘルプ</a>を確認してください。
					</p>
					<?php endif; ?>
					<?php if(!empty($this->message["access"])): ?>
					<p class="error">
					ファイルにアクセスできてしまうようです。<a href="<?php echo $this->help("dir"); ?>">ヘルプ</a>を確認し、ファイルを保護してください。
					</p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="product_slug">取引識別スラッグ</label>
				</th>
				<td>
					<input id="product_slug" name="product_slug" type="text" value="<?php $this->h($this->option['slug']); ?>" />
					<p class="description">
					Paypalの管理画面に表示される商品識別IDです。一つのPaypalアカウントで複数の取引を利用している場合に有用です。
					<small>（<a href="<?php echo $this->help("account"); ?>">もっと詳しく</a>）</small><br />
					<strong>10文字程度の半角英数字</strong>で設定してください。末尾にハイフンとIDがつきます。<small>例：example-100</small>
					</p>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="mypage">マイページ</label>
				</th>
				<td>
					<select id="mypage" name="mypage">
						<?php foreach(get_pages() as $p): ?>
						<option value="<?php echo $p->ID;?>"<?php if($p->ID == $this->option['mypage']) echo ' selected="selected"';?>><?php echo $p->post_title; ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">
					ログイン後に顧客が移動できるページです。このページを設定することで、「本棚」を作ることができます。
					<small>（<a href="<?php echo $this->help("customize"); ?>">もっと詳しく</a>）</small>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="button-primary" value="設定更新" />
	</p>
</form>
<h3>その他注意点</h3>

<h4>パーマリンクについて</h4>
<p>
パーマリンクを有効化している場合、電子書籍のページが<em>&quot;404 Not Found&quot;</em>となり、表示されません。<br />
<a href="<?php admin_url(); ?>options-permalink.php">パーマリンク設定</a>から一度「変更を保存」してください。設定内容を変更する必要はありません。<br />
これでリライトルールが初期化され、<em>&quot;<?php bloginfo("url"); ?>/ebook/example-book&quot;</em>というURLの電子書籍ページが表示されるようになります。
</p>

<h4>カスタマイズ</h4>
<p>
Literally WordPressはテンプレートタグを利用することでカスタマイズすることができます。
テンプレートタグおよび関数については<a href="<?php echo $this->help("customize"); ?>">ヘルプ</a>をご覧下さい。
</p>

<h3>寄付する</h3>
<p>
	このプラグインは<a href="http://hametuha.com" target="_blank">破滅派</a>の<a href="http://takahashifumiki.com" target="_blank">高橋文樹</a>が作成しました。
	寄付をしてくれるともっと開発をがんばれるかもしれません。
</p>

<!-- //Paypal -->
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
	<input type="hidden" name="cmd" value="_s-xclick" />
	<input type="hidden" name="hosted_button_id" value="46TV2VBBZDSV4" />
	<table>
		<tr>
			<td><input type="hidden" name="on0" value="寄付金額" />寄付金額</td>
		</tr>
		<tr>
			<td>
				<select name="os0">
					<option value="気持ちだけ">気持ちだけ ¥100</option>
					<option value="期待を込めて">期待を込めて ¥1,000</option>
					<option value="大盤振る舞い">大盤振る舞い ¥3,000</option>
					<option value="焼肉でも食え">焼肉でも食え ¥5,000</option>
					<option value="わしが育てた">わしが育てた ¥10,000</option>
				</select>
			</td>
		</tr>
	</table>
	<input type="hidden" name="currency_code" value="JPY" />
	<input type="hidden" name="ctb" value="高橋文樹.comへ戻る" />
	<input type="image" src="https://www.paypal.com/ja_JP/JP/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal- オンラインで安全・簡単にお支払い" />
	<img alt="" border="0" src="https://www.paypal.com/ja_JP/i/scr/pixel.gif" width="1" height="1" />
</form>

<!-- Paypal //-->