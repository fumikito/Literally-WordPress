<?php /* @var $this Literally_WordPress */?>
<?php if(!$notification_status): ?>
<p class="message warning error"><?php $this->e('Failed to send mail. Please note this page.'); ?></p>
<?php endif; ?>
<?php echo $this->notifier->get_thankyou($transaction); ?>
<p>
	<a class="button" href="<?php echo get_permalink($post_id); ?>"><?php $this->e("Return"); ?></a>
</p>