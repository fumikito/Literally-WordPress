<?php /* @var $this LWP_Form */?>
<?php if($error): ?>
	<p class="message warning">
		<?php $this->e('Code is wrong.'); ?>
	</p>
<?php endif; ?>
<p class="message notice">
	<?php printf($this->_('Please enter code for %1$s <strong>&quot;%2$s&quot;</strong>.'), $post_type, $title); ?>
</p>
<form action="<?php echo $action; ?>" method="post">
	<?php wp_nonce_field('lwp_ticket_owner_'.$event_id);?>
	<table class="form-table lwp-ticket-table" id="lwp-ticket-table-list">
		<tbody>
			<tr>
				<th scope="row"><?php $this->e('Code');?></th>
				<td><input type="text" class="regular-text input" name="code" value="" /></td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php $this->e('Verify'); ?>" />
	</p>
</form>
<p>
	<a class="button" href="<?php echo $link; ?>"><?php printf($this->_("Return to %s"), $title); ?></a>
</p>