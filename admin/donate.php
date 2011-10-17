<?php /* @var $this Literally_WordPress */ ?>
<hr />
<h3><?php $this->e("Donate"); ?></h3>
<p>
	<?php printf($this->_('This plugin was made by <a href="http://takahashifumiki.com" target="_blank">%1$s</a> of <a href="http://hametuha.co.jp" target="_blank">%2$s</a>. If you like this plugin, please vote <a href="http://wordpress.org/extend/plugins/literally-wordpress/" target="_blank">Here</a> or donate.'), $this->_("Takahashi Fumiki"), $this->_("Hametuha inc."));?><br />
	<?php printf($this->_('If you have request or find bugs, please contact me at <a href="%1$s" target="_blank">plugin support page</a> or my <a href="%2$s" target="_blank">twitter account</a>.'), 'http://tech.hametuha.org/projects/lwp', 'https://twitter.com/#!/takahashifumiki');?><br />
	&copy; 2011 <a href="http://takahashifumiki.com"><?php $this->e("Takahashi Fumiki"); ?></a>
</p>

<!-- //Paypal -->
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
	<input type="hidden" name="cmd" value="_s-xclick" />
	<input type="hidden" name="hosted_button_id" value="HGH7CAEFE3F42" />
	<table>
		<tr>
			<td><input type="hidden" name="on0" value="寄付総額" /><?php $this->e("Donation Ammount"); ?></td>
		</tr>
		<tr>
			<td>
				<select name="os0">
					<option value="ビールでも飲め"><?php $this->e("Drink beer."); ?> &yen;100</option>
					<option value="ランチでも食べろ"><?php $this->e("Take a lunch."); ?> &yen;1,000</option>
					<option value="居酒屋にでも行け"><?php $this->e("Go to some nice bar."); ?> &yen;3,000</option>
					<option value="パーティに行って来い"><?php $this->e("Have a good party."); ?> &yen;5,000</option>
					<option value="わしが育てた"><?php $this->e("I am your father."); ?> &yen;10,000</option>
				</select>
			</td>
		</tr>
	</table>
	<input type="hidden" name="currency_code" value="JPY" />
	<input type="hidden" name="ctb" value="<?php $this->e("Return to takahashifumiki.com")?>" />
	<input type="image" src="https://www.paypalobjects.com/ja_JP/JP/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal- オンラインで安全・簡単にお支払い" />
	<img alt="" border="0" src="https://www.paypalobjects.com/ja_JP/i/scr/pixel.gif" width="1" height="1" />
</form>

<p class="warning">
	<?php $this->e('"Buy Now" button used beacause Japanese goverment dosen\'t allow paypal to intermediate donation.'); ?>
</p>

<!-- Paypal //-->
