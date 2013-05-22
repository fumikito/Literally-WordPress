<?php
/**
 * 
 * Abstract class for form
 * 
 * This class has form create methods.
 * 
 * @since 0.9.3.1
 * 
 */
abstract class LWP_Form_Template extends LWP_Cart{
	
	
	/**
	 * Localize script
	 * 
	 * @var array 
	 */
	protected $_LWP = array();
	
	
	
	/**
	 * Register template_redirect hook on construction
	 * 
	 * @param array $option
	 */
	public function __construct($option = array()) {
		parent::__construct($option);
		//Highjack frontpage request if lwp is set
		if(!is_admin()){
			add_action("template_redirect", array($this, 'avoid_caonnical_redirect'), 1);
			add_action("template_redirect", array($this, "manage_actions"));
		}
	}
	
	
	/**
	 * Remove canonical redirect
	 * @global Literally_WordPress $lwp
	 */
	public function avoid_caonnical_redirect(){
		global $lwp;
		$action = $lwp->rewrite->get_current_action();
		if(!empty($action)){
			remove_action('template_redirect', 'redirect_canonical');
		}
	}
	
	
	
	/**
	 * Manage form action to lwp endpoint
	 * 
	 * @return void
	 */
	public function manage_actions(){
		global $lwp;
		//If action is set, call each method
		if($lwp->rewrite->get_current_action()){
			//Avoid WP redirect
			$action = 'handle_'.$this->make_hungalian($lwp->rewrite->get_current_action());
			if(method_exists($this, $action)){
				$sandbox = (isset($_REQUEST['sandbox']) && $_REQUEST['sandbox']);
				if($sandbox && !current_user_can('edit_theme_options')){
					$this->kill('Sorry, but you have no permission.', 403);
				}
				if(!apply_filters('lwp_before_display_form', true, $lwp->rewrite->get_current_action())){
					$this->kill($this->_('You cannot access here.'), 403, true);
				}
				$this->{$action}($sandbox);
			}else{
				$this->kill($this->_('Sorry, but You might make unexpected action.'), 400);
			}
		}
	}
	
	
	
	/**
	 * Stop processing transaction of not logged in user. 
	 * 
	 * @param boolean $kill if set to false, user will be auth_redirec-ed.
	 */
	protected function kill_anonymous_user($kill = true){
		if(!is_user_logged_in()){
			if($kill){
				$this->kill($this->_('You must be logged in to process transaction.'), 403);
			}else{
				auth_redirect();
			}
		}
	}
	
	
	/**
	 * Returns if payment slection can be skipped
	 * 
	 * @deprecated since version 0.9.3.1
	 */
	private function can_skip_payment_selection(){
	  return false;
	}
	
	
	/**
	 * Returns is public page is SSL
	 * @return boolean 
	 */
	protected function is_publicly_ssl(){
		return ((false !== strpos(get_option('home_url'), 'https')) || (false !== strpos(get_option('site_url'), 'https')));
	}
  
	
	
	/**
	 * Make url to http protocol
	 * @param string $url
	 * @return string 
	 */
	protected function strip_ssl($url){
		return str_replace('https://', 'http://', $url);
	}
	
	
	/**
	 * Change method name to hungalian 
	 * 
	 * @param string $method
	 * @return string 
	 */
	protected function make_hungalian($method){
		return str_replace("-", "_", strtolower(trim($method)));
	}
	
	
	
	
	/**
	 * Returns handle name
	 */
	public function endpoints(){
		$methods = array();
		foreach(get_class_methods($this) as $method){
			if(0 === strpos($method, 'handle_')){
				$handle = str_replace('_', '-', str_replace('handle_', '', $method));
				if(false === array_search($handle, array('gmo-payment', 'sb-payment', 'ticket-awaiting-deregister'))){
					$methods[] = $handle;
				}
			}
		}
		return $methods;
	}
	
	
	
	
	/**
	 * Returns default template name for action
	 * @param string $action
	 * @return string 
	 */
	public function get_default_form_slug($action = ''){
		switch($action){
			case 'subscription':
			case 'success':
			case 'cancel':
			case 'payment':
			case 'payment-info':
			case 'refund-account':
			case 'sb-payment':
				return $action;
				break;
			case 'pricelist':
				return 'subscription';
				break;
			case 'buy':
				return 'selection';
				break;
			case 'confirm':
				return 'return';
				break;
			case 'ticket-cancel':
				return 'cancel-ticket';
				break;
			case 'ticket-cancel-complete':
				return 'cancel-ticket-success';
				break;
			case 'ticket-list':
				return 'event-tickets';
				break;
			case 'ticket-consume':
				return 'event-tickets-consume';
				break;
			case 'ticket-owner':
				return 'event-user';
				break;
			case 'ticket-contact':
				return 'event-contact';
				break;
			case 'ticket-awaiting':
				return 'event-tickets-awaiting';
				break;
			default:
				return '';
				break;
		}
	}
	
	
	/**
	 * Returns title of form template
	 * 
	 * @param string $template_slug
	 * @return string 
	 */
	public function get_form_title($template_slug = ''){
		switch($template_slug){
			case 'selection':
				$meta_title = $this->_('Select Payment');
				break;
			case 'payment':
				$meta_title = $this->_('Payment Information');
				break;
			case 'payment-info':
				$meta_title = $this->_('Payment Information Detail');
				break;
			case 'refund-account':
				$meta_title = $this->_('Refund Account');
				break;
			case 'transfer':
				$meta_title = $this->_('Transfer Accepted');
				break;
			case 'return':
				$meta_title = $this->_('Payment Confirmation');
				break;
			case 'success':
				$meta_title = $this->_('Transaction Completed');
				break;
			case 'cancel':
				$meta_title = $this->_('Transaciton Canceled');
				break;
			case 'cancel-ticket':
				$meta_title = $this->_('Ticket Cancel');
				break;
			case 'cancel-ticket-success':
				$meta_title = $this->_('Ticket Canceled');
				break;
			case 'event-tickets':
				$meta_title = $this->_('Ticket List');
				break;
			case 'event-tickets-consume':
				$meta_title = $this->_('Ticket Status');
				break;
			case 'event-tickets-awaiting':
				$meta_title = $this->_('Waiting for ticket cancellation');
				break;
			case 'event-user':
				$meta_title = $this->_('Find User');
				break;
			case 'event-contact':
				$meta_title = $this->_('Contact to participants');
				break;
			case 'subscription':
				$meta_title = $this->_('Subscrition Plans');
				break;
			case 'sb-check':
				$meta_title = $this->_('Softbank Payment Service Notification Check');
				break;
			default:
				$meta_title = '';
				break;
		}
		return $meta_title;
	}
	
	
	
	/**
	 * Returns form description
	 * 
	 * @param string $action
	 * @return string 
	 */
	public function get_form_description($action = ''){
		switch($action){
			case 'pricelist':
				return $this->_('Displays subscription plans.');
				break;
			case 'subscription':
				return $this->_('Displays subscription plans.').' '.$this->_('User can select it and go to payment selection page.');
				break;
			case 'success':
				return $this->_('Display thank you message when transaction finished.').' '.$this->_('User will be soon redirected to original event page in 5 seconds.');
				break;
			case 'cancel':
				return $this->_('Display message when user cancels transaction.');
				break;
			case 'buy':
				return $this->_('Displays payment methods. You can skip this form if paypal is the only method available.').
					'<small>（<a href="'.admin_url('admin.php?page=lwp-setting').'">'.$this->_("More &gt;").')</a></small>';
				break;
			case 'payment':
				return $this->_('Display form to fulfill payment information like Web CVS, CreditCard and so on.');
				break;
			case 'payment-info':
				return $this->_('Display currently quueued transaction. Especially for Web CVS or PayEasy.');
				break;
			case 'refund-account':
				return $this->_('Display form of refund account which is required to complete refund process.');
				break;
			case 'confirm':
				return $this->_('Displays form to confirm transaction when user retruns from paypal web site.');
				break;
			case 'ticket-cancel':
				return $this->_('Show list of tickets which user have bought.').' '.$this->_('User can select ticket to cancel.').' '.$this->_('If user has no tickets, wp_die will be executed.');
				break;
			case 'ticket-cancel-complete':
				return $this->_('Displays message to tell user cancel is completed.').' '.$this->_('User will be soon redirected to original event page in 5 seconds.');
				break;
			case 'ticket-list':
				return $this->_('Show list of tickets which user have bought.').' '.$this->_('If user has no tickets, wp_die will be executed.');
				break;
			case 'ticket-consume':
				return $this->_('Displays list of tikcets owned by specified user. You can consume ticket with pulldown menu.');
				break;
			case 'ticket-owner':
				return $this->_('Search tikcet owner from code which have been generared by this plugin. This code is related to particular event.');
				break;
			case 'ticket-contact':
				return $this->_('Show mail form to send emails to event participants.');
				break;
			case 'ticket-awaiting':
				return $this->_('Displayed when user choose to wait for cancellation.');
				break;
			case 'sb-payment':
				return $this->_('Check endpoint availability. Use only on sandbox.');
				break;
			default:
				return '';
				break;
		}
	}
	
	/**
	 * フォームを出力する
	 * @since 0.9.1
	 * @global Literally_WordPress $lwp
	 * @param string $slug
	 * @param array $args
	 */
	protected function show_form($slug, $args = array(), $die = true){
		$args['meta_title'] = $this->get_form_title($slug).' : '.get_bloginfo('name');
		$args = apply_filters('lwp_form_args', $args, $slug);
		extract($args);
		$slug = basename($slug);
		$filename = "{$slug}.php";
		$parent_directory = $this->dir.DIRECTORY_SEPARATOR."form-template".DIRECTORY_SEPARATOR;
		add_action('wp_enqueue_scripts', array($this, 'enqueue_form_scripts'));
		add_action('wp_head', array($this, 'form_wp_head'));
		// Check file existence
		$header = $parent_directory."header.php";
		$body = $parent_directory.$filename;
		$footer = $parent_directory."footer.php";;
		if(!file_exists($header) || !file_exists($body) || !file_exists($footer)){
			$this->kill(sprintf($this->_('Cannot load template. Please contact to the administrator of %s'), get_bloginfo('name')), 500);
		}
		include $header;
		do_action('lwp_before_form', $slug, $args);
		include $body;
		do_action('lwp_after_form', $slug, $args);
		include $footer;
		if($die){
			exit;
		}
	}
	
	
	
	/**
	 * Avoid Form to be crowled.
	 */
	public function form_wp_head(){
		echo '<meta name="robots" content="noindex,nofollow" />';
	}
  
	
	/**
	 * Add label to script global
	 * 
	 * @param string $key
	 * @param string $value
	 */
	protected function add_script_label($key, $value){
		$this->_LWP[$key] = $value;
	}
	
	
	/**
	 * Add label in 1 action
	 * @param array $args
	 */
	protected function add_script_labels($args){
		foreach((array)$args as $key => $value){
			$this->add_script_label($key, $value);
		}
	}
	
	
	/**
	 * Do enqueue scripts 
	 * 
	 */
	public function enqueue_form_scripts(){
		global $lwp;
		//Load CSS, JS
		//Screen CSS
		$css = apply_filters('lwp_css', (file_exists(get_stylesheet_directory().DIRECTORY_SEPARATOR."lwp-form.css")) ? get_stylesheet_directory_uri()."/lwp-form.css" : $lwp->url."assets/compass/stylesheets/lwp-form.css", 'form');
		if($css){
			wp_enqueue_style("lwp-form", $css, array(), $lwp->version, 'screen');
		}
		//Print CSS
		$print_css = apply_filters('lwp_css', (file_exists(get_stylesheet_directory().DIRECTORY_SEPARATOR.'lwp-print.css')) ? get_stylesheet_directory_uri()."/lwp-print.css" : $lwp->url."assets/compass/stylesheets/lwp-print.css", 'print');
		if($print_css){
			wp_enqueue_style("lwp-form-print", $print_css, array(), $lwp->version, 'print');
		}
		//JS for form helper
		wp_enqueue_script("lwp-form-helper", $this->url."assets/js/form-helper.js", array("jquery-form", 'jquery-effects-highlight'), $lwp->version, true);
		//Add Common lables
		$this->_LWP['labelProcessing'] = $this->_('Processing&hellip;');
		$this->_LWP['labelRecalculating'] = $this->_('You changed item quantity. Please click recalculate and confirm your order.');
		wp_localize_script('lwp-form-helper', 'LWP', $this->_LWP);
		//Do action hook for other plugins
		do_action('lwp_form_enqueue_scripts');
	}
}