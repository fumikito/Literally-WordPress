<p class="message success">
	<?php if(!$deregister): ?>
	<?php printf($this->_("You are now on waiting list for <strong>%s</strong>."), $ticket); ?><br />
	<?php printf($this->_("You will be redirected to %s."), $ticket);?><br /><br />
	<?php echo esc_html($message); ?>
	<?php else: ?>
	<?php echo $message; ?>
	<?php endif; ?>
</p>
<p class="indicator">
	<?php printf($this->_('%s seconds left'), '<span id="lwp-redirect-indicator">5</span>'); ?><br />
</p>
<p>
	<a class="button" href="<?php echo $link;?>" id="lwp-auto-redirect"><?php $this->e("return now");?></a>
</p>