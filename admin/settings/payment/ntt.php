<?php /* @var $this Literally_WordPress */ ?>
<p class="description">
	<?php echo $this->ntt->get_desc('general'); ?>
</p>
<hr />
<table class="form-table">
	<tbody>
		<tr>
			<th valign="top"><?php $this->e('Status'); ?></th>
			<td>
				<?php if($this->ntt->is_enabled()): ?>
					<p class="valid">
						<?php printf($this->_('This payment method is enabled as %s.'),
								($this->ntt->is_sandbox ? $this->_('Sandbox') : $this->_('Productional environment'))); ?>
					</p>
				<?php else: ?>
					<p class="invalid"><?php $this->e('This payment method is invalid.'); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="ntt_sandbox"><?php $this->e('Sandbox'); ?></label>
			</th>
			<td>
				<label>
					<input type="checkbox" name="ntt_sandbox" id="ntt_sandbox" value="1"<?php if($this->ntt->is_sandbox) echo ' checked="checked"';?> />
					<?php $this->e("This is a develop enviorment and needs pseudo transaction.")?>
				</label>
				<p class="info"><?php $this->e('This payment method is very experimenal. <strong>Test on Sandbox is strongly recommended</strong>. If you find some bug please contact to plugin author at <a href="http://lwper.info">LWPper.info</a>'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top"><?php $this->e('Stealth mode'); ?></th>
			<td>
				<label>
					<input type="checkbox" name="ntt_stealth" value="1"<?php if($this->ntt->is_stealth) echo ' checked="checked"'; ?> />
					<?php $this->e('Enable stealth mode'); ?>
				</label>
				<p class="description"><?php $this->e('If stealth mode is on, only administrator can see this payment option on transaction form. This is useful for productional test.'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top">ちょこむeマネー決済</th>
			<td>
				<label>
					<input type="checkbox" name="ntt_emoney" value="1"<?php if($this->ntt->is_emoney_enabled()) echo ' checked="checked"'; ?> />
					<?php $this->e('Contaracted and available'); ?>
				</label>
				<p class="description"><?php echo $this->ntt->get_desc('emoney'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="ntt_shop_id"><?php printf($this->_('Shop ID for %s'), $this->_(LWP_Payment_Methods::NTT_EMONEY)); ?></label>
				<small class="required"><?php $this->e('Required'); ?></small>
			</th>
			<td>
				<input type="text" name="ntt_shop_id" id="ntt_shop_id" class="regular-text" value="<?php echo esc_attr($this->ntt->shop_id); ?>" />
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="ntt_access_key"><?php printf($this->_('Access Key for %s'), $this->_(LWP_Payment_Methods::NTT_EMONEY)); ?></label>
				<small class="required"><?php $this->e('Required'); ?></small>
			</th>
			<td>
				<input type="text" name="ntt_access_key" id="ntt_access_key" class="regular-text" value="<?php echo esc_attr($this->ntt->access_key); ?>" />
				<p class="info"><?php echo $this->ntt->get_desc('contract'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label><?php $this->e('Credit Card'); ?></label>
			</th>
			<td>
				<label>
					<input type="checkbox" name="ntt_creditcard" value="1"<?php if($this->ntt->is_cc_enabled()) echo ' checked="checked"'; ?> />
					<?php $this->e('Contaracted and available'); ?>
				</label>
				<p class="description"><?php echo $this->ntt->get_desc('credit'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="ntt_shop_id_cc"><?php printf($this->_('Shop ID for %s'), $this->_(LWP_Payment_Methods::NTT_CC)); ?></label>
				<small class="required"><?php $this->e('Required'); ?></small>
			</th>
			<td>
				<input type="text" name="ntt_shop_id_cc" id="ntt_shop_id_cc" class="regular-text" value="<?php echo esc_attr($this->ntt->shop_id_cc); ?>" />
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="ntt_access_key_cc"><?php printf($this->_('Access Key for %s'), $this->_(LWP_Payment_Methods::NTT_CC)); ?></label>
				<small class="required"><?php $this->e('Required'); ?></small>
			</th>
			<td>
				<input type="text" name="ntt_access_key_cc" id="ntt_access_key_cc" class="regular-text" value="<?php echo esc_attr($this->ntt->access_key_cc); ?>" />
				<p class="info"><?php echo $this->ntt->get_desc('contract'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label><?php $this->e('Web CVS'); ?></label>
			</th>
			<td>
				<label>
					<input type="checkbox" name="ntt_webcvs" value="1"<?php if($this->ntt->is_cvs_enabled()) echo ' checked="checked"'; ?> />
					<?php $this->e('Contaracted and available'); ?>
				</label>
				<p class="description"><?php echo $this->ntt->get_desc('cvs'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="ntt_cvs_date">コンビニ最大支払日数</label>
			</th>
			<td>
				<label>申込から <input type="text" name="ntt_cvs_date" id="ntt_cvs_date" class="short-text" value="<?php echo esc_attr($this->ntt->cvs_limit); ?>" /> 日後</label>
				<?php if($this->ntt->cvs_limit < 1): ?>
				<p class="invalid">
					コンビニ最大支払日数は最低1日からです。
				</p>
				<?php endif; ?>
				<p class="description">
					この値はちょコムで設定した値と同じにしてください。これを超えるとエラーになります。
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="ntt_shop_id_cvs"><?php printf($this->_('Shop ID for %s'), $this->_(LWP_Payment_Methods::NTT_CVS)); ?></label>
				<small class="required"><?php $this->e('Required'); ?></small>
			</th>
			<td>
				<input type="text" name="ntt_shop_id_cvs" id="ntt_shop_id_cvs" class="regular-text" value="<?php echo esc_attr($this->ntt->shop_id_cvs); ?>" />
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="ntt_access_key_cvs"><?php printf($this->_('Access Key for %s'), $this->_(LWP_Payment_Methods::NTT_CVS)); ?></label>
				<small class="required"><?php $this->e('Required'); ?></small>
			</th>
			<td>
				<input type="text" name="ntt_access_key_cvs" id="ntt_access_key_cvs" class="regular-text" value="<?php echo esc_attr($this->ntt->access_key_cvs); ?>" />
				<p class="info"><?php echo $this->ntt->get_desc('contract'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="ntt_comdisp">同意選択画面番号</label>
			</th>
			<td>
				<input type="text" name="ntt_comdisp" id="ntt_comdisp" class="short-text" value="<?php echo esc_attr($this->ntt->comdisp); ?>" />
				<p class="description">
					ちょコムサイトに移動したときにユーザーに表示される画面をカスタマイズします。NTTスマートトレード担当者から聞いた<code>comDisp</code>の番号を入力してください。
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><?php $this->e('Notification URL'); ?></th>
			<td>
				<label>
					<input type="text" readonly="readonly" onclick="this.select(0, this.value.length);" value="<?php echo esc_attr(lwp_endpoint('chocom-emoney')); ?>" class="regular-text" />
					ちょコムeマネー通知
				</label><br />
				<label>
					<input type="text" readonly="readonly" onclick="this.select(0, this.value.length);" value="<?php echo esc_attr(lwp_endpoint('chocom-cc')); ?>" class="regular-text" />
					ちょコムクレジット通知
				</label><br />
				<label>
					<input type="text" readonly="readonly" onclick="this.select(0, this.value.length);" value="<?php echo esc_attr(lwp_endpoint('chocom-cvs')); ?>" class="regular-text" />
					ちょコムコンビニ受付通知
				</label><br />
				<label>
					<input type="text" readonly="readonly" onclick="this.select(0, this.value.length);" value="<?php echo esc_attr(lwp_endpoint('chocom-cvs-complete')); ?>" class="regular-text" />
					ちょコムコンビニ決済完了通知
				</label>
				<p class="description">
					<?php $this->e('NTT SmartTrade requires contact endpoint on your server. Please notify this URL to your service manager.'); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>