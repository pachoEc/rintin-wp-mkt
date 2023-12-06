<?php
/**
 * Redirect functions */ 
add_filter( 'woocommerce_registration_redirect', 'my_registration_redirect' );
function my_registration_redirect( $redirect_to ) {
    $redirect_to = home_url();
    return $redirect_to;
}

add_action( 'woocommerce_login_redirect', 'custom_login_redirect' );
function custom_login_redirect() {
	$redirect_to = home_url();
    return $redirect_to;
}

add_filter( 'woocommerce_get_checkout_url', 'my_custom_checkout_url' );
function my_custom_checkout_url( $url ) {
	if(!is_user_logged_in()){
		$url = 'https://rintin.mx/registro';
	}
	else{
		$url = 'https://rintin.mx/finalizar-compra';
	}
    return $url;
}
?>