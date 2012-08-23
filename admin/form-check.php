<?php /* @var $this Literally_WordPress */  ?>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo admin_url('themes.php?page=lwp-form-check'); ?>" class="nav-tab<?php if(!isset($_REQUEST['tab'])) echo ' nav-tab-active';?>">
		<?php $this->e('LWP Form Check'); ?>
	</a>
	<a href="<?php echo admin_url('themes.php?page=lwp-form-check&tab=documentation'); ?>" class="nav-tab<?php if(isset($_REQUEST['tab'])) echo ' nav-tab-active';?>">
		<?php $this->e('Documentation'); ?>
	</a>
</h2>

<?php if(!isset($_REQUEST['tab'])): ?>

<h3><?php printf($this->_('About %s'), $this->_('Form Check'));  ?></h3>
<p class="description">
	<?php printf($this->_('You can check all of forms which your customer will see in his transaction. These forms\' appearance can be customized in various ways(CSS, Hooks, etc). Please see the <a href="%s">documentation</a>.'), admin_url('themes.php?page=lwp-form-check&tab=documentation')); ?>
</p>

<table class="widefat">
	<?php for($i = 0; $i < 2; $i++): $thead = $i ? 'tfoot' : 'thead'; ?>
	<<?php echo $thead; ?>>
		<tr>
			<th scope="col"><?php $this->e('Form Name'); ?></th>
			<th scope="col"><?php $this->e('Use Case'); ?></th>
			<th scope="col"><?php $this->e('View'); ?></th>
		</tr>
	</<?php echo $thead; ?>>
	<?php endfor; ?>
	<tbody>
		<?php $counter = 0; foreach($this->form->endpoints() as $method): if($method == 'file') continue; $counter++; ?>
		<tr<?php if($counter % 2 == 1) echo ' class="alternate"';?>>
			<th valign="top" scope="row"><?php echo $this->form->get_form_title($this->form->get_default_form_slug($method));?></th>
			<td><p><?php echo $this->form->get_form_description($method); ?></p></td>
			<td>
				<?php if(
						(false !== array_search($method, array('pricelist', 'subscription')) && !$this->subscription->is_enabled())
							||
						(0 === strpos($method, 'ticket') && !$this->event->is_enabled())
				): ?>
				<?php $this->e('Unavailable'); ?>
				<?php else: ?>
				<a class="button" href="<?php echo lwp_endpoint($method, true);?>" target="lwp-form-check"><?php $this->e('View'); ?></a>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php else: ?>

<p><?php $this->e('This documentation tells you how to customize forms. Choose best way for your skill and knowledge.'); ?></p>

<h3><?php $this->e('Change CSS'); ?></h3>
<p><?php printf(
		$this->_('You can change default CSS to your own. In default, LWP loads original CSS(%1$s) and all other css which your theme or other plugins load. Some conflicts might occur, or you may feel like it to suit to you own theme. To do so, just make css file named <strong>lwp-form.css</strong> and put it in your theme directory(%2$s).'),
		'<small>'.$this->dir.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'lwp-form.css</small>',
		'<small>'.get_template_directory().DIRECTORY_SEPARATOR.'lwp-form.css</small>'
); ?></p>

<h3><?php $this->e('Change Logo'); ?></h3>
<p><?php printf(
	$this->_('LWP outputs string &quot;%1$s&quot; as form title. If you feel like change it to your original logo image, use filter hook.'),
	get_bloginfo('name')
); ?></p>
<pre><code>//<?php $this->e('Write the code below in your theme\'s functions.php'); ?>

function _my_lwp_title($title){
	//<?php $this->e('Change title to logo image in your theme directory.'); ?>

	return '&lt;img src="'.get_template_directory_uri().'/img/logo.png" alt="'.esc_attr($title).'" width="300" height="80" /&gt;';
}
add_filter('lwp_form_title', '_my_lwp_title');</code></pre>
<?php $this->e('Don\'t forget to change logo in PayPal\'s landing page.'); ?>
<?php endif; ?>
