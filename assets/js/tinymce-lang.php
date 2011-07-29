<?php
/**
 * TinyMCEの国際化
 * @package Literally WordPress
 * @global $lwp
 */

global $lwp;

$strings = 'tinyMCE.addI18n({' . $mce_locale . ':{
	lwpShortCode:{
		title:"' . esc_js( $lwp->_('Capability') ) . '",
		owner:"' . esc_js( $lwp->_('Owner') ) . '",
		subscriber:"' . esc_js( $lwp->_('Subscriber') ) . '",
		nonOwner:"' . esc_js( $lwp->_('Non Owner') ) . '",
		nonSubscriber:"' . esc_js( $lwp->_('Non Subscriber') ) . '"
	}
}});
';
