<p>
	<?php $this->e("Thank you for purchasing."); ?><br />
	<?php $this->e("You will be redirected to the purchased item page.");?><br />
</p>
<p class="indicator">
	<?php printf($this->_('%s seconds left'), '<span id="lwp-redirect-indicator">5</span>'); ?><br />
</p>
<p>
	<a class="button" href="<?php echo $link;?>" id="lwp-auto-redirect"><?php $this->e("return now");?></a>
</p>