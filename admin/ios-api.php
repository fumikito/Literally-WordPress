<?php /* @var $this Literally_WordPress */ ?>
<h2><?php $this->e('XML-RPC API Manual'); ?></h2>

<p class="description">
<?php printf($this->_('API Version: %1$s<br />Last Updated: %2$s'), $this->ios->api_version, mysql2date(get_option('date_format').' '.get_option('time_format'), $this->ios->api_last_updated)); ?>
</p>

<h3><?php $this->e('How to use'); ?></h3>
<p><?php printf($this->_('Once you enabled iOS Product, expanded <a href="%s">XML-RPC API</a> for products is available. Your iOS application can contact with this WordPres site and download In App Purchase products.'), 'http://codex.wordpress.org/XML-RPC_Support');  ?></p>
<p class="description"><?php printf($this->_('<strong>Note:</strong> I recommend you to use %s.'), '<a href="https://github.com/eczarny/xmlrpc">eczarny\'s Cocoa XML-RPC Framework</a>'); ?></p>

<h3><?php $this->e('Methods') ?></h3>

<div class="lwp-api-list">

<h4>lwp.ios.getApiInformation</h4>
<p><?php $this->e('You can know API version information.') ?></p>
<h5><?php $this->e('Returns'); ?></h5>
<pre>
<strong>(struct)</strong>
	<strong>(string)</strong> api_version <?php $this->e('Version of XML-RPC API'); ?> 
	<strong>(string)</strong> last_updated <?php $this->e('API\'s last updated date in DATETIME format.'); ?> 
	<strong>(string)</strong> lwp_version <?php $this->e('Version of LWP'); ?> 
	<strong>(string)</strong> wp_version <?php $this->e('WordPress version'); ?> 
	<strong>(boolean)</strong> force_ssl <?php $this->e('Is SSL forced.'); ?>
</pre>

<h4>lwp.ios.methods</h4>
<p><?php $this->e('You can get list of registered methods.') ?></p>
<h5><?php $this->e('Returns'); ?></h5>
<pre>
<strong>(array)</strong> <?php $this->e('Array consists of method names.'); ?>
</pre>


<h4>lwp.ios.getUserInfo</h4>
<p><?php $this->e('You can get userdata with credentials.'); ?></p>
<h5><?php $this->e('Parameters'); ?></h5>
<pre>
<strong>(string)</strong> username
<strong>(string)</strong> password
</pre>
<h5><?php $this->e('Returns'); ?></h5>
<pre>
<strong>(struct)</strong> <?php printf($this->_('You can get same data as <code>get_userdata</code>. See detail at <a href="%s">codex</a>.'), 'http://codex.wordpress.org/Function_Reference/get_userdata'); ?>
</pre>

<h4>lwp.ios.productGroup</h4>
<p><?php $this->e('You can get product group. Product group is a custom taxonomy and it helps you to manage multiple iOS application in one WordPress site.') ?></p>
<h5><?php $this->e('Parameters'); ?></h5>
<pre>
<strong>(struct)</strong> args <?php printf($this->_('Parameter is directly pass to <code>get_terms</code>. See detail at <a href="%s">codex</a>.'), 'http://codex.wordpress.org/Function_Reference/get_terms'); ?>
</pre>
<h5><?php $this->e('Returns'); ?></h5>
<pre>
<strong>(array)</strong> <?php $this->e('Array consists	 of term object.'); ?>
</pre>


<h4>lwp.ios.productList</h4>
<p><?php $this->e('You can get product list ') ?></p>
<h5><?php $this->e('Parameters'); ?></h5>
<pre>
<strong>(struct)</strong> args
	<strong>(int)</strong> term_taxonomy_id <?php $this->e('term_taxonomy_id of term object. Default 0.'); ?> 
	<strong>(string)</strong> orderby <?php $this->e('date, title, ID, product_id. Default product_id'); ?> 
	<strong>(string)</strong> order <?php $this->e('DESC or ASC. Default ASC'); ?> 
	<strong>(string)</strong> status <?php $this->e('Post status. Default publish'); ?> 
<strong>(boolean)</strong> with_files <?php $this->e('If set to true, File\'s information will be set as array. Default true.'); ?>
</pre>
<h5><?php $this->e('Returns'); ?></h5>
<pre>
<strong>(array)</strong>
	<strong>(struct)</strong> product
		<strong>(int)</strong> post_id
		<strong>(string)</strong> post_content
		<strong>(string)</strong> post_excerpt
		<strong>(string)</strong> post_date
		<strong>(string)</strong> post_modified
		<strong>(string)</strong> product_id
		<strong>(double)</strong> price <?php $this->e('<em>This value is registered at Post edit screen. To get precise price, use StoreKit request.</em>'); ?> 
		<strong>(array)</strong> files <?php $this->e('List of files which are attached to this post.'); ?> 
			<strong>(struct)</strong> <?php $this->e('See <code>lwp.ios.fileList</code>.'); ?> 
</pre>

<h4>lwp.ios.fileList</h4>
<p><?php $this->e('File list of specified post.') ?></p>
<h5><?php $this->e('Parameters'); ?></h5>
<pre>
<strong>(int)</strong> <?php $this->e('ID of post.'); ?>
</pre>
<h5><?php $this->e('Returns'); ?></h5>
<pre>
<strong>(array)</strong> 
	<strong>(struct)</strong> 
		<strong>(int)</strong> ID <?php $this->e('File ID.'); ?> 
		<strong>(string)</strong> name <?php $this->e('File desciptive name.'); ?> 
		<strong>(string)</strong> detal <?php $this->e('File\'s description.'); ?> 
		<strong>(int)</strong> public <?php $this->e('0 is private, 1 is public.'); ?> 
		<strong>(int)</strong> free <?php $this->e('0 is not free, 1 means free for the registered users, 2 is free for everyone.'); ?> 
		<strong>(array)</strong> devices <?php printf($this->_('Devices you registered <a href="%s">here</a>.'), admin_url('admin.php?page=lwp-devices')); ?> 
			<strong>(struct)</strong> 
				<strong>(string)</strong> name
				<strong>(string)</strong> slug
</pre>



<h4>lwp.ios.getFile</h4>
<p><?php $this->e('Get File from transaction information.') ?></p>
<h5><?php $this->e('Parameters'); ?></h5>
<pre>
<strong>(string)</strong> username
<strong>(string)</strong> password
<strong>(int)</strong> file_id
</pre>
<h5><?php $this->e('Returns'); ?></h5>
<pre>
<strong>(struct)</strong>
	<strong>(string)</strong> hash <?php $this->e('md5 hash of file'); ?> 
	<strong>(int)</strong> size <?php $this->e('File size'); ?> 
	<strong>(string)</strong> mime <?php $this->e('Mime type of file'); ?> 
	<strong>(base64)</strong> data <?php $this->e('Binary data of file.'); ?> 
</pre>




<h4>lwp.ios.registerTransaction</h4>
<p><?php $this->e('Register transaction with AppStore receipt. This aciton is very important.') ?></p>
<p class="description"><?php $this->e('<strong>Note:</strong> event in case of restoration, call this method. It parse receipt and try to find existing transaction.'); ?></p>
<h5><?php $this->e('Parameters'); ?></h5>
<pre>
<strong>(string)</strong> username
<strong>(string)</strong> password
<strong>(string)</strong> receipt <?php $this->e('Base64 encoded string of receipt you get from AppStore.'); ?> 
<strong>(double)</strong> price <?php $this->e('If not set, price set at Edit screen will be used. Setting actual price on AppStore is strongly recommended.'); ?> 
<strong>(string)</strong> uuid <?php $this->e('Typically UUID. Any string can be saved.'); ?> 
</pre>
<h5><?php $this->e('Returns'); ?></h5>
<pre>
<strong>(boolean)</strong> <?php $this->e('True on success, False on failur.'); ?>
</pre>


<h4>lwp.ios.getFileWithReceipt</h4>
<p><?php $this->e('You can get file and register transaction in one action.') ?></p>
<h5><?php $this->e('Parameters'); ?></h5>
<pre>
<strong>(string)</strong> username
<strong>(string)</strong> password
<strong>(string)</strong> receipt <?php $this->e('Base64 encoded string of receipt you get from AppStore.'); ?> 
<strong>(double)</strong> price <?php $this->e('If not set, price set at Edit screen will be used. Setting actual price on AppStore is strongly recommended.'); ?> 
<strong>(string)</strong> uuid <?php $this->e('Typically UUID. Any string can be saved.'); ?> 
<strong>(int)</strong> file_id
</pre>
<h5><?php $this->e('Returns'); ?></h5>
<pre>
<strong>(struct)</strong>
	<strong>(string)</strong> hash <?php $this->e('md5 hash of file'); ?> 
	<strong>(int)</strong> size <?php $this->e('File size'); ?> 
	<strong>(string)</strong> mime <?php $this->e('Mime type of file'); ?> 
	<strong>(base64)</strong> data <?php $this->e('Binary data of file.'); ?> 
</pre>



<h4>lwp.ios.getUserTransactions</h4>
<p><?php $this->e('Get list or user\'s transaction information.') ?></p>
<h5><?php $this->e('Parameters'); ?></h5>
<pre>
<strong>(string)</strong> username
<strong>(string)</strong> password
<strong>(int)</strong> page <?php $this->e('Default 1.'); ?> 
<strong>(string)</strong> method <?php printf($this->_('Payment method. Default <code>%1$s</code>. Allowed methods: %2$s'), implode(', ', array_map(create_function('$m', ' return "<code>".$m."</code>";'), LWP_Payment_Methods::get_all_methods()))); ?>
</pre>
<h5><?php $this->e('Returns'); ?></h5>
<pre>
<strong>(struct)</strong> 
	<strong>(int)</strong> total
	<strong>(int)</strong> page
	<strong>(array)</strong> transactions
		<strong>(struct)</strong> 
			<strong>(string)</strong> transaction_id
			<strong>(int)</strong> post_id
			<strong>(double)</strong> price
			<strong>(string)</strong> status
			<strong>(string)</strong> registered
			<strong>(string)</strong> updated
			<strong>(int)</strong> num
</pre>


</div><!-- //.lwp-api-list -->

