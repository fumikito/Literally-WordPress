<?php /* @var $this LWP_Form */ ?>

<noscript>
<p class="message error">
	ちょコムでの決済を行うためには、<strong>Javascriptを有効</strong>にする必要があります。
	ご利用のブラウザの設定をご確認の上、このページを再度表示してください。
</p>
</noscript>

<?php $this->the_price_list($products, false); ?>

<p>
	お支払い方法：<strong><?php $this->e($method); ?></strong>
</p>

<p class="message info">
	<span><?php echo $message; ?></span>
	<span style="display:none">ちょコムへ移動する準備をしています...</span>
	<span style="display:none">準備が完了しました！</span>
	<span style="display:none"></span>
</p>


<form id="chocom-transaction" method="post" action="<?php echo $action; ?>">
	<input type="hidden" name="action" value="chocom_generate_transaction" />
	<?php wp_nonce_field('chocom_generate_transaction_'.get_current_user_id()); ?>
	<input type="hidden" name="lwp-method" value="<?php echo $method; ?>" />
	<?php foreach($products as $product): ?>
		<input type="hidden" name="product[]" value="<?php echo $product->ID; ?>" />
		<input type="hidden" name="quantity[<?php echo esc_attr($product->ID); ?>]" value="<?php echo esc_attr($this->get_current_quantity($product)); ?>" />
	<?php endforeach; ?>
</form>

<p class="submit">
	<a class="button-primary" href="#" id="chocom-redirector">ちょコムへ移動する</a>
</p>
<p>
	<a class="button" href="<?php echo $link; ?>"><?php $this->e("Return"); ?></a>
</p>