<?php /* @var $this LWP_Form */?>

<?php if(!$notification): ?>
	<p class="message warning error"><?php $this->e('Failed to send mail. Please note this page.'); ?></p>
<?php elseif($notification === 'sent'):?>
	<p class="message notice"><?php $this->e('You have already ordered this item. Please see the note below.'); ?></p>
<?php else: ?>
	<p class="message success"><?php $this->e("We send you a reminder mail to your registered addres."); ?></p>
<?php endif; ?>
<?php echo $lwp->notifier->get_thankyou($transaction); ?>
<p>
	<a class="button" href="<?php echo $link; ?>"><?php $this->e("Return"); ?></a>
</p>