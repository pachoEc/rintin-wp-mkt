<?php
error_reporting( E_ALL & ~E_NOTICE );
 /**
  * Script Enquers
  */

require_once __DIR__ . '/vendor/autoload.php';
add_action( 'wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010 );
function woodmart_child_enqueue_styles() {
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), '1.0.18' );
}



add_action('admin_enqueue_scripts', 'enqueue_custom_admin_style');
function enqueue_custom_admin_style() {
    $handle = 'admin-style';
    $src = get_stylesheet_directory_uri() . '/admin-style.css';
    $deps = array();
    $ver = time();

    wp_enqueue_style($handle, $src, $deps, $ver);
}

add_action('wp_enqueue_scripts', 'enqueue_ajax_js_script');
function enqueue_ajax_js_script($hook) {
	$version = time();
	wp_enqueue_script( 'ajax_postal_code',  get_stylesheet_directory_uri() . '/js/custom.js', array( 'jquery' ), $version, true );
	$rest_nonce = wp_create_nonce( 'wp_rest' );
	wp_localize_script( 'ajax_postal_code', 'my_var', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce' => $rest_nonce,
	));
}

add_action('admin_enqueue_scripts', 'enqueue_custom_admin_script');
function enqueue_custom_admin_script() {
	$version = time();
    
    wp_enqueue_script('admin_custom', get_stylesheet_directory_uri() . '/js/admin-custom.js', array('jquery'), $version, true);
	$rest_nonce = wp_create_nonce( 'wp_rest' );
	wp_localize_script( 'admin_custom', 'my_var', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce' => $rest_nonce,
	));
}


/**
 * Header buffer init
 */

add_action('init', 'app_output_buffer');
function app_output_buffer() {
    ob_start();
} 

include('includes/cron-jobs.php');
include('includes/notification-triggers.php');
include('includes/return-product-functions.php');
include('includes/admin-functions.php');
include('includes/redirection-functions.php');
include('includes/ajax-handlers.php');
include('includes/checkout-functions.php');
include('includes/item-loop-functions.php');
include('includes/dokan-functions.php');


/** Footer Scripts */

add_action( 'wp_footer','custom_checkout_jqscript');
function custom_checkout_jqscript(){
    if(is_checkout() && ! is_wc_endpoint_url()):
    ?>
    <script type="text/javascript">
    jQuery( function($){
        $('form.checkout').on('change', 'input[name="payment_method"]', function(){
            $(document.body).trigger('update_checkout');
        });
    });
    </script>
    <?php
    endif;
}


/** Shortcodes */

add_shortcode( 'my_login', 'my_login_shortcode' );
function my_login_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'redirect' => '',
    ), $atts );

	if( is_user_logged_in() ){
		wp_redirect("https://rintin.mx/");
		exit;
	}
	
    $output = '';

    if ( isset( $_POST['user_submit'] ) ) {
        // Log the user in
        $creds = array(
            'user_login' => sanitize_text_field( $_POST['user_login'] ),
            'user_password' => $_POST['user_pass'],
            'remember' => true,
        );
        $user_login = wp_signon( $creds );

        if ( is_wp_error( $user_login ) ) {
            $output .= '<p>' . $user_login->get_error_message() . '</p>';
        } else {
            $output .= '<p>Login successful! You are now logged in as ' . $user_login->display_name . '</p>';

            // Redirect the user if a redirect URL was specified
            if ( ! empty( $atts['redirect'] ) ) {
                wp_redirect( esc_url_raw( $atts['redirect'] ) );
                exit;
            }
        }
    }

    // Display the login form
    $output .= '<div>';
	$output .= '<h2 class="wd-login-title">Acceder</h2>';
    $output .= '<form method="post" class="woocommerce-form woocommerce-form-register register">';
	$output .= '<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">';
    $output .= '<label for="user_login">Nombre de Usuario o Correo Electrónico</label>';
    $output .= '<input type="text" name="user_login" id="user_login">';
	$output .= '</p>';
	$output .= '<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">';
    $output .= '<label for="user_pass">Contraseña</label>';
    $output .= '<input type="password" name="user_pass" id="user_pass">';
	$output .= '</p>';
    $output .= '<input type="submit" class="woocommerce-Button button wp-element-button" name="user_submit" value="Iniciar Sesión">';
    $output .= '</form>';
	$output .= '</div>';
    return $output;
}

add_shortcode( 'my_register', 'my_register_shortcode');
function my_register_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'redirect' => '',
    ), $atts );

	if( is_user_logged_in() ){
		wp_redirect("https://rintin.mx/");
		exit;
	}
	
    $output = '';

    if ( isset( $_POST['user_submit'] ) ) {
        // Create a new user
        $email = $_POST['user_email'];
		if ( preg_match( '/^([a-zA-Z0-9._%+-]+)@/', $email, $matches ) ) {
			$first_part = $matches[1];
		}
        $user_id = wp_insert_user( array(
            'user_login' => sanitize_user( $first_part ),
            'user_email' => sanitize_email( $email ),
            'user_pass' => sanitize_text_field( $_POST['user_pass'] ),
            'role' => 'customer',
        ) );

        // Check if the user was created successfully
        if ( is_wp_error( $user_id ) ) {
            $output .= '<p>' . $user_id->get_error_message() . '</p>';
        } else {
            // Log the user in
            $user = get_user_by( 'id', $user_id );
            $creds = array(
                'user_login' => $user->user_login,
                'user_password' => $_POST['user_pass'],
                'remember' => true,
            );
            $user_login = wp_signon( $creds );

            $output .= '<p>Registration successful! You are now logged in as ' . $user->display_name . '</p>';

            // Redirect the user if a redirect URL was specified
            if ( ! empty( $atts['redirect'] ) ) {
                wp_redirect( esc_url_raw( $atts['redirect'] ) );
                exit;
            }
        }
    }

    // Display the registration form
    $output .= '<div>';
	$output .= '<h2 class="wd-login-title">Registro</h2>';
    $output .= '<form method="post" class="woocommerce-form woocommerce-form-register register">';
	$output .= '<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">';
    $output .= '<label for="user_email">Dirección de correo electrónico</label>';
    $output .= '<input type="email" name="user_email" id="user_email">';
	$output .= '</p>';
	$output .= '<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">';
    $output .= '<label for="user_pass">Contraseña</label>';
    $output .= '<input type="password" name="user_pass" id="user_pass">';
	$output .= '</p>';
	$output .= '<p class="form-row">';
	$output .= '<div class="woocommerce-privacy-policy-text"><p>Tus datos personales se utilizarán para procesar tu pedido, mejorar tu experiencia en esta web, gestionar el acceso a tu cuenta y otros propósitos descritos en nuestra <a href="https://rintin.mx/politica-privacidad/" class="woocommerce-privacy-policy-link" target="_blank">política de privacidad</a>.</p>
</div>';
    $output .= '<input type="submit" class="woocommerce-Button button wp-element-button" name="user_submit" value="Registrarse">';
	$output .= '</p>';
    $output .= '</form>';
	$output .= '<div class="woocommerce-privacy-policy-text"><p>¿Ya estás registrado? <a href="https://rintin.mx/iniciar-sesion/" class="woocommerce-privacy-policy-link" style="text-decoration: underline;">Inicia Sesión Aquí</a>.</p>
</div>';
	$output .= '</div>';

    return $output;
}

/** Header Scripts */
function my_custom_head_code() {
    ?>
<!-- Google Tag Manager -->

<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':

new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],

j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=

'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);

})(window,document,'script','dataLayer','GTM-M3ND4S6');</script>

<!-- End Google Tag Manager -->




<!-- Google Tag Manager (noscript) -->

<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M3ND4S6"

height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

<!-- End Google Tag Manager (noscript) -->
    <?php
}
add_action( 'wp_head', 'my_custom_head_code' );
function maps_js_mount(){
	?>

		<script>
  (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
    key: "AIzaSyC9GnYe0meFUxQBaMcUFgqaBQgYogsFkyM",
    v: "weekly",
  });
</script>

    <?php
}

add_action( 'wp_head', 'maps_js_mount' );