<?php
/**
 * TinyMCEの国際化
 * @package Literally WordPress
 * @global $lwp
 */

global $lwp;

$strings = 'tinyMCE.addI18n({' . $mce_locale . ':{
	lwpShortCode:{
		title:"' . esc_js( $lwp->_('Control by Capability') ) . '",
		owner:"' . esc_js( $lwp->_('Purchaser Only') ) . '",
		subscriber:"' . esc_js( $lwp->_('Subscriber Only') ) . '",
		nonOwner:"' . esc_js( $lwp->_('Non Purchaesr Only') ) . '",
		nonSubscriber:"' . esc_js( $lwp->_('Non Subscriber Only') ) . '",
		buyNow:"'.esc_js($lwp->_('Add Buy Now')).'",
		deault: "'.esc_js($lwp->_('Default Button')).'",
		noimage: "'.esc_js($lwp->_('Inline link tag')).'",
		image: "'.esc_js($lwp->_('Original Image')).'",
		src_message: "'.esc_js($lwp->_('PUT_IMAGE_SRC_HERE')).'"
	}
}});
';
