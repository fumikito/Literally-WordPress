<?php

class LWP_Context{
	
	/**
	 * Buying digital goods
	 */
	const DIGITAL = 'digital';
	
	/**
	 * Buying subscription
	 */
	const SUBSCRIPTION = 'subscription';
	
	/**
	 * Buying event
	 */
	const EVENT = 'event';
	
	/**
	 * Detect payment context by posts array
	 * 
	 * @global Literally_WordPress $lwp
	 * @param array $posts post objects array
	 * @return boolean
	 */
	public static function get($posts){
		global $lwp;
		$contexts = array();
		foreach($posts as $post){
			if($post->post_type == $lwp->subscription->post_type){
				$contexts[] = 'subscription';
			}elseif($post->post_type == $lwp->event->post_type){
				$contexts[] = 'event';
			}elseif(false !== array_search ($post->post_type, $lwp->post->post_types)){
				$contexts[] = 'digital';
			}
		}
		$singular_contexts = array();
		foreach ($contexts as $context) {
			if(false === array_search($context, $singular_contexts)){
				$singular_contexts[] = $context;
			}
		}
		if(count($singular_contexts) == 1){
			return (string)$singular_contexts[0];
		}else{
			return false;
		}
	}
}