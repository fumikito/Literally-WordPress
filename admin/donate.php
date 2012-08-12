<?php if(current_user_can('edit_others_posts')): ?>
<?php /* @var $this Literally_WordPress */ ?>
<div class="lwp-author-msg">
	<div class="paypal-donate"><!-- //Paypal -->
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
		<h4><?php $this->e('Other ways to reward me');  ?></h4>
		<p>
			<?php $this->e('Besides donation, you can reward me for this plugin.');  ?>
		</p>
		<ol>
			<li><?php printf($this->_('Buy my ebook on <a href="%s">my site</a>.'), 'http://takahashifumiki.com'); ?></li>
			<li><?php printf($this->_('Place an order to my company <a href="%1$s">%2$s</a>. %2$s is Web development company and very good at WordPress and PHP development.'), 'http://hametuha.co.jp',  $this->_('Hametuha inc.')); ?></li>
		</ol>

	</div><!-- Paypal //-->
	
	<h3><?php $this->e("Donation & Promotion by Plugin author"); ?></h3>
	<?php echo get_avatar('takahashi.fumiki@hametuha.co.jp', 80); ?>
	<p class="bio">
		<?php printf($this->_('I am <a href="http://takahashifumiki.com" target="_blank">%1$s</a>, the author of this plugin and a Japanese novelist.'), $this->_("Takahashi Fumiki")); ?>
		<?php $this->e('I made LWP to sell my ebooks by myself. If you create something similar(music, video, picture and etc.), this plugin may help your your creative life.'); ?>
		<?php printf($this->_('I developed many plugins. You can see the list on <a href="%s">my WordPress profile page</a>.'), 'http://profiles.wordpress.org/takahashi_fumiki');?>
	</p>
	<h4 style="clear:left;"><?php $this->e('How to feedback'); ?></h4>
	<p class="bio">
		<?php $this->e('This plugin is developed individually. If you have some opinion, please don\'t hesitate to tell me that. There are meny ways to send feedback.');  ?>
	</p>
	<dl>
		<dt><?php $this->e('Rate on WordPress.org'); ?></dt>
		<dd><?php $this->e('You can vote this plugin or create thread <a href="http://wordpress.org/extend/plugins/literally-wordpress/" target="_blank">HERE</a>.'); ?></dd>
		<dt><?php $this->e('Collaborate on Github'); ?></dt>
		<dd><?php printf($this->_('The source code is hosted on <a href="%s">GitHub</a>. You can review all of it and make pull request.'), 'https://github.com/fumikito/Literally-WordPress'); ?></dd>
		<dt><?php $this->e('Social feedback'); ?></dt>
		<dd><?php printf($this->_('Feel free to contact me with <a href="%1$s" target="_blank">Twitter</a>, <a href="%2$s" target="_blank">Facebook</a> or <a href="%3$s" target="_blank">Google+</a>.'),
				'https://twitter.com/takahashifumiki',
				'https://www.facebook.com/TakahashiFumiki.Page',
				'https://plus.google.com/u/0/108058172987021898722/about');?></dd>
		
	</dl>
	<p class="notice">
		<img src="<?php echo $this->url; ?>assets/lightbulb_on_16.png" alt="notice" width="16" height="16" />
		<?php $this->e('This message is displayed only for users who have editor capability. I don\'t care about your client because it\'s free :p');  ?>
	</p>
</div>
<?php endif; ?>