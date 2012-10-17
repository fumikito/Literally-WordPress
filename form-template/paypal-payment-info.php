<?php /* @var $this LWP_Form */?>
<p class="message notice">
	<?php printf($this->_('%1$s transaction status for <strong><a href="%3$s">%2$s</a> x %4$d</strong>. Print or bookmark this page and keep it accessible.'), $method_name, $item_name, $link, $quantity); ?>
</p>

<table class="form-table lwp-ticket-table" id="lwp-ticket-table-list">
	<thead>
		<tr>
			<?php foreach($headers as $header): ?>
				<th scope="col"><?php echo esc_html($header); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach($rows as $row): ?>
		<tr>
			<?php 
				$counter = 0;
				foreach($row as $cell){
					$tag = $counter > 0 ? 'td' : 'th'; 
					printf('<%1$s>%2$s</%1$s>', $tag, $cell);
					$counter++; 
				}
			?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<p class="submit">
	<a id="lwp-submit" class="button-primary" href="#" onclick="window.print(); return false;"><?php $this->e("Print"); ?></a>
</p>
<p>
	<a class="button" href="<?php echo $back; ?>"><?php printf($this->_("Return to %s"), $this->_('Purchase History')); ?></a>
</p>