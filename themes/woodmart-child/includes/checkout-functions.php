<?php

/** Checkout Hooks & Functions */


add_action('woocommerce_checkout_fields', 'add_custom_checkout_fields');
function add_custom_checkout_fields( $fields ) {
	$customer_id = get_current_user_id();
	$customer_name = get_user_meta($customer_id, 'billing_first_name', true);
	?>
	<h1>
		DETALLES DE ENTREGA
	</h1>
			<div style="height: fit-content; position: relative;">
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
				<label for="billing_first_name">Nombre&nbsp;</label>
				<input type="text" name="billing_first_name" id="billing_first_name" value="<?php echo get_user_meta($customer_id, 'billing_first_name', true); ?>">
			</p>
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
				<label for="billing_last_name_field">Apellido&nbsp;</label>
				<input type="text" name="billing_last_name_field" id="billing_last_name_field" value="<?php echo get_user_meta($customer_id, 'billing_last_name', true); ?>">
			</p>
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
				<label for="billing_phone">Número de WhatsApp&nbsp;</label>
				<input type="text" name="billing_phone" id="billing_phone" value="<?php echo get_user_meta($customer_id, 'billing_phone', true); ?>">
			</p>
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
				<label for="billing_phone1">Número de Llamadas&nbsp;</label>
				<input type="text" name="billing_phone1" id="billing_phone1" value="<?php echo get_user_meta($customer_id, 'billing_phone1', true); ?>">
			</p>
				<div class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide new-address-field hide" id="billing_address_1_field" style="position: relative; margin-bottom: 20px;" data-priority="80" id='address-input-wrapper'>
					<label for="billing_address_1" class="">Ingresa la dirección de envío completa (calle y número exterior)&nbsp;<abbr class="required" title="obligatorio">*</abbr></label>
					<span class="woocommerce-input-wrapper">
						<input type="text" class="input-text " name="billing_address_1" id="billing_address_1" placeholder="Calle y no. exterior" autocomplete="off" value="<?php echo get_user_meta($customer_id, 'billing_address_1', true); ?>">
						<span id="icon-container-loader" style="display: none;">
							<i class="loader"></i>
						</span>
					</span>
				</div>
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
					<label >¿No encuentras la dirección a donde quieres que realicemos el envío? Colócala en el mapa</label>
				</p>
				<div style="width: 100%; height: 500px; position: relative;">
					<div id="map" style="height: 100%; width: 100%;">
					</div>
				<div id="center-marker"></div>
				<input type="text" class="input-to-disable" class="input-text " name="billing_lat" id="billing_lat" value="<?php echo get_user_meta($customer_id, 'latitude', true); ?>" style="display: none;" placeholder="">
				<input type="text" class="input-to-disable" class="input-text " name="billing_lng" id="billing_lng" value="<?php echo get_user_meta($customer_id, 'longitude', true); ?>" style="display: none;" placeholder="">
				</div>
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide new-address-field hide">
					<label for="billing_ext_num">Número Exterior&nbsp;<abbr class="required" title="obligatorio">*</abbr></label>
					<input type="text" name="billing_ext_num" id="billing_ext_num" class="input-to-disable" value="<?php echo get_user_meta($customer_id, 'billing_ext_num', true); ?>" >
				</p>
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide new-address-field hide">
					<label for="billing_int_num">Número Interior&nbsp;<abbr class="required" title="obligatorio">*</abbr></label>
					<input type="text" name="billing_int_num" id="billing_int_num" class="input-to-disable" value="<?php echo get_user_meta($customer_id, 'billing_int_num', true); ?>">
				</p>
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide new-address-field hide">
					<label for="billing_betw_streets">Entre Calles</label>
					<input type="text" name="billing_betw_streets" class="input-to-disable" id="billing_betw_streets" value="<?php echo get_user_meta($customer_id, 'billing_betw_streets', true); ?>">
				</p>
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide new-address-field hide">
					<label for="billing_postcode">Código Postal&nbsp;<abbr class="required" title="obligatorio">*</abbr></label>
					<input type="text" name="billing_postcode" class="input-to-disable" id="billing_postcode" value="<?php echo get_user_meta($customer_id, 'billing_postcode', true); ?>">
				</p>
				<p class="form-row form-row-wide address-field update_totals_on_change validate-required new-address-field hide" id="billing_country_field" data-priority="70">
					<label for="billing_country" class="">País&nbsp;<abbr class="required" title="obligatorio">*</abbr></label>
					<span class="woocommerce-input-wrapper"><strong>México</strong>
						<input type="hidden" name="billing_country" id="billing_country" class="input-to-disable" value="MX" autocomplete="country" class="country_to_state" readonly="readonly" required>
					</span>
				</p>
				<p class="form-row form-row-wide address-field validate-required validate-state woocommerce-validated"
    id="billing_state_field" data-priority="130"
    data-o_class="form-row form-row-wide address-field  validate-required validate-state">
    <label for="billing_state" class="">Estado&nbsp;<abbr class="required" title="obligatorio">*</abbr></label>
    <span class="woocommerce-input-wrapper">
        <select name="billing_state" id="billing_state"
            class="state_select select2-hidden-accessible"
            data-placeholder="Elige una opción…" data-input-classes="" data-label="Estado" tabindex="-1"
            aria-hidden="true" value="<?php echo get_user_meta($customer_id, 'billing_state', true); ?>" >
            <option value="">Elige una opción…</option>
            <option value="DF">Ciudad de México</option>
            <option value="JA">Jalisco</option>
            <option value="NL">Nuevo León</option>
            <option value="AG">Aguascalientes</option>
            <option value="BC">Baja California</option>
            <option value="BS">Baja California Sur</option>
            <option value="CM">Campeche</option>
            <option value="CS">Chiapas</option>
            <option value="CH">Chihuahua</option>
            <option value="CO">Coahuila</option>
            <option value="CL">Colima</option>
            <option value="DG">Durango</option>
            <option value="GT">Guanajuato</option>
            <option value="GR">Guerrero</option>
            <option value="HG">Hidalgo</option>
            <option value="MX">Estado de México</option>
            <option value="MI">Michoacán</option>
            <option value="MO">Morelos</option>
            <option value="NA">Nayarit</option>
            <option value="OA">Oaxaca</option>
            <option value="PU">Puebla</option>
            <option value="QT">Querétaro</option>
            <option value="QR">Quintana Roo</option>
            <option value="SL">San Luis Potosí</option>
            <option value="SI">Sinaloa</option>
            <option value="SO">Sonora</option>
            <option value="TB">Tabasco</option>
            <option value="TM">Tamaulipas</option>
            <option value="TL">Tlaxcala</option>
            <option value="VE">Veracruz</option>
            <option value="YU">Yucatán</option>
            <option value="ZA">Zacatecas</option>
        </select>
    </span>
</p>
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide new-address-field hide" id="billing_ciudad_field" data-priority="120">
					<label for="billing_ciudad" class="">Ciudad&nbsp;<abbr class="required" title="obligatorio">*</abbr></label>
					<span class="woocommerce-input-wrapper"><input value="<?php echo get_user_meta($customer_id, 'billing_ciudad', true); ?>" type="text" class="input-to-disable" class="input-text " required name="billing_ciudad" id="billing_ciudad" placeholder=""></span>
				</p>
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide new-address-field hide" id="billing_city_field" data-priority="110" data-o_class="form-row form-row-wide address-field  validate-required">
					<label for="billing_city" class="">Colonia&nbsp;<abbr class="required" title="obligatorio">*</abbr></label>
					<span class="woocommerce-input-wrapper"><input value="<?php echo get_user_meta($customer_id, 'billing_city', true); ?>" class="input-to-disable" type="text" class="input-text " name="billing_city" id="billing_city" placeholder="" autocomplete="address-level2"></span>
				</p>
				<p class="form-row form-row-wide  awcfe-inline-item validate-required" id="billing_tipo_negocio_entrega_field" data-priority="150">
					<label for="billing_tipo_negocio_entrega_negocio" class="">¿La entrega seria en que tipo de propidad?&nbsp;<abbr class="required" title="obligatorio">*</abbr></label>
					<select  class="" name="billing_tipo_negocio_entrega_negocio" id="billing_tipo_negocio_entrega_negocio" value="<?php echo get_user_meta($customer_id, 'billing_tipo_negocio_entrega_negocio', true); ?>">
						<option value="-1" disabled>Selecciona el tipo de propiedad</option>
						<option value="negocio">Negocio Comercial</option>
						<option value="bodega">Bodega</option>
						<option value="domicilio_casa">Domicilio o Casa</option>
						<option value="otro">Otro</option>
					</select>
				</p>
				<p class="form-row form-row-wide validate-required" id="billing_hora_preferente_entrega_field" data-priority="160">
					<label for="billing_hora_preferente_entrega" class="">¿Podrias comentarnos a que hora prefieres la entrega? * Lo tomamos en cuenta pero podria tener variaciones&nbsp;<abbr class="required" title="obligatorio">*</abbr>
					</label>
					<span class="woocommerce-input-wrapper">
						<input type="text" class="input-text " value="<?php echo get_user_meta($customer_id, 'billing_hora_preferente_entrega', true); ?>" name="billing_hora_preferente_entrega" id="billing_hora_preferente_entrega" placeholder="Ingresa todas las opciones de dias y horas de tu preferencia" value="lunes me gusta un saludo">
					</span>
				</p>
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
					<label for="billing_address_2">Observaciones y datos extra</label>
					<span class="woocommerce-input-wrapper"><textarea value="<?php echo get_user_meta($customer_id, 'billing_address_2', true); ?>" name="billing_address_2" class="input-text " id="billing_address_2" placeholder="Notas sobre tu pedido, por ejemplo, número interior, puntos de referencia, etc..." rows="2" cols="5"></textarea></span>
				</p>
				
				<div id="loader-container">
					<div id="loader"></div>
				</div>
			</div>
	<?php
    
}

add_action('woocommerce_checkout_create_order', 'save_custom_checkout_fields_to_order');
function save_custom_checkout_fields_to_order( $order ) {
	$customer_id = get_current_user_id();
	$shipping_array_to_save = array(
		'_shipping_address_1'		 => isset( $_POST['billing_address_1'] ) ? sanitize_text_field( $_POST['billing_address_1'] ) : '',
		'_shipping_city'      => isset( $_POST['billing_city'] ) ? sanitize_text_field( $_POST['billing_city'] ) : '',
		'_shipping_country'      => 'MX',
		'_shipping_ciudad'     => isset( $_POST['billing_ciudad'] ) ? sanitize_text_field( $_POST['billing_ciudad'] ) : '',
        '_shipping_state'   => isset( $_POST['billing_state'] ) ? sanitize_text_field( $_POST['billing_state'] ) : '',
        '_shipping_postcode'    => isset( $_POST['billing_postcode'] ) ? sanitize_text_field( $_POST['billing_postcode'] ) : '',
		'_shipping_ext_num'  => isset( $_POST['billing_ext_num'] ) ? sanitize_text_field( $_POST['billing_ext_num'] ) : '',
		'_shipping_int_num'  => isset($_POST['billing_int_num']) ? sanitize_text_field( $_POST['billing_int_num'] ) : '',
		'_shipping_betw_streets'  => isset($_POST['billing_betw_streets']) ? sanitize_text_field( $_POST['billing_betw_streets'] ) : '',
		'_shipping_address_2'=> isset( $_POST['billing_address_2'] ) ? sanitize_text_field( $_POST['billing_address_2'] ) : '',
    );
	$billing_array_to_save = array(
		'_billing_first_name'  => isset( $_POST['billing_first_name'] ) ? sanitize_text_field( $_POST['billing_first_name'] ) : '',
        '_billing_last_name'     => isset( $_POST['billing_last_name_field'] ) ? sanitize_text_field( $_POST['billing_last_name_field'] ) : '',
		'_billing_address_1'		 => isset( $_POST['billing_address_1'] ) ? sanitize_text_field( $_POST['billing_address_1'] ) : '',
		'_billing_city'      => isset( $_POST['billing_city'] ) ? sanitize_text_field( $_POST['billing_city'] ) : '',
		'_billing_country'      => 'MX',
		'_billing_ciudad'     => isset( $_POST['billing_ciudad'] ) ? sanitize_text_field( $_POST['billing_ciudad'] ) : '',
        '_billing_state'   => isset( $_POST['billing_state'] ) ? sanitize_text_field( $_POST['billing_state'] ) : '',
        '_billing_postcode'    => isset( $_POST['billing_postcode'] ) ? sanitize_text_field( $_POST['billing_postcode'] ) : '',
		'_billing_phone1'   => isset( $_POST['billing_phone1'] ) ? sanitize_text_field( $_POST['billing_phone1'] ) : '',
        '_billing_phone'    => isset( $_POST['billing_phone'] ) ? sanitize_text_field( $_POST['billing_phone'] ) : '',
		'_billing_ext_num'  => isset( $_POST['billing_ext_num'] ) ? sanitize_text_field( $_POST['billing_ext_num'] ) : '',
		'_billing_int_num'  => isset($_POST['billing_int_num']) ? sanitize_text_field( $_POST['billing_int_num'] ) : '',
		'_billing_betw_streets'  => isset($_POST['billing_betw_streets']) ? sanitize_text_field( $_POST['billing_betw_streets'] ) : '',
		'_billing_address_2'=> isset( $_POST['billing_address_2'] ) ? sanitize_text_field( $_POST['billing_address_2'] ) : '',
		'_billing_tipo_negocio_entrega' => isset( $_POST['billing_tipo_negocio_entrega_negocio'] ) ? sanitize_text_field( $_POST['billing_tipo_negocio_entrega_negocio'] ) : '',
		'_billing_hora_preferente_entrega' => isset( $_POST['billing_hora_preferente_entrega'] ) ? sanitize_text_field( $_POST['billing_hora_preferente_entrega'] ) : '',
		'latitude' => isset( $_POST['billing_lat'] ) ? sanitize_text_field( $_POST['billing_lat'] ) : '',
		'longitude' => isset( $_POST['billing_lng'] ) ? sanitize_text_field( $_POST['billing_lng'] ) : '',
	);
	$user_array_to_save = array(
		'billing_first_name'  => isset( $_POST['billing_first_name'] ) ? sanitize_text_field( $_POST['billing_first_name'] ) : '',
        'billing_last_name'     => isset( $_POST['billing_last_name_field'] ) ? sanitize_text_field( $_POST['billing_last_name_field'] ) : '',
		'billing_address_1'		 => isset( $_POST['billing_address_1'] ) ? sanitize_text_field( $_POST['billing_address_1'] ) : '',
		'billing_city'      => isset( $_POST['billing_city'] ) ? sanitize_text_field( $_POST['billing_city'] ) : '',
		'billing_country'      => 'MX',
		'billing_ciudad'     => isset( $_POST['billing_ciudad'] ) ? sanitize_text_field( $_POST['billing_ciudad'] ) : '',
        'billing_state'   => isset( $_POST['billing_state'] ) ? sanitize_text_field( $_POST['billing_state'] ) : '',
        'billing_postcode'    => isset( $_POST['billing_postcode'] ) ? sanitize_text_field( $_POST['billing_postcode'] ) : '',
		'billing_phone1'   => isset( $_POST['billing_phone1'] ) ? sanitize_text_field( $_POST['billing_phone1'] ) : '',
        'billing_phone'    => isset( $_POST['billing_phone'] ) ? sanitize_text_field( $_POST['billing_phone'] ) : '',
		'billing_ext_num'  => isset( $_POST['billing_ext_num'] ) ? sanitize_text_field( $_POST['billing_ext_num'] ) : '',
		'billing_int_num'  => isset($_POST['billing_int_num']) ? sanitize_text_field( $_POST['billing_int_num'] ) : '',
		'billing_betw_streets'  => isset($_POST['billing_betw_streets']) ? sanitize_text_field( $_POST['billing_betw_streets'] ) : '',
		'billing_address_2'=> isset( $_POST['billing_address_2'] ) ? sanitize_text_field( $_POST['billing_address_2'] ) : '',
		'shipping_address_1'		 => isset( $_POST['billing_address_1'] ) ? sanitize_text_field( $_POST['billing_address_1'] ) : '',
		'shipping_city'      => isset( $_POST['billing_city'] ) ? sanitize_text_field( $_POST['billing_city'] ) : '',
		'shipping_country'      => 'MX',
		'shipping_ciudad'     => isset( $_POST['billing_ciudad'] ) ? sanitize_text_field( $_POST['billing_ciudad'] ) : '',
        'shipping_state'   => isset( $_POST['billing_state'] ) ? sanitize_text_field( $_POST['billing_state'] ) : '',
        'shipping_postcode'    => isset( $_POST['billing_postcode'] ) ? sanitize_text_field( $_POST['billing_postcode'] ) : '',
		'shipping_ext_num'  => isset( $_POST['billing_ext_num'] ) ? sanitize_text_field( $_POST['billing_ext_num'] ) : '',
		'shipping_int_num'  => isset($_POST['billing_int_num']) ? sanitize_text_field( $_POST['billing_int_num'] ) : '',
		'shipping_betw_streets'  => isset($_POST['billing_betw_streets']) ? sanitize_text_field( $_POST['billing_betw_streets'] ) : '',
		'shipping_address_2'=> isset( $_POST['billing_address_2'] ) ? sanitize_text_field( $_POST['billing_address_2'] ) : '',
		'billing_tipo_negocio_entrega' => isset( $_POST['billing_tipo_negocio_entrega_negocio'] ) ? sanitize_text_field( $_POST['billing_tipo_negocio_entrega_negocio'] ) : '',
		'billing_hora_preferente_entrega' => isset( $_POST['billing_hora_preferente_entrega'] ) ? sanitize_text_field( $_POST['billing_hora_preferente_entrega'] ) : '',
		'latitude' => isset( $_POST['billing_lat'] ) ? sanitize_text_field( $_POST['billing_lat'] ) : '',
		'longitude' => isset( $_POST['billing_lng'] ) ? sanitize_text_field( $_POST['billing_lng'] ) : '',
	);
	foreach ( $shipping_array_to_save as $meta_key => $meta_value ) {
        $order->update_meta_data( $meta_key, $meta_value );
    }
	foreach ( $billing_array_to_save as $meta_key => $meta_value ) {
        $order->update_meta_data( $meta_key, $meta_value );
    }
	foreach ( $user_array_to_save as $meta_key => $meta_value ){
		update_user_meta($customer_id, $meta_key, $meta_value);
	}
    $order->save();
}

add_action( 'woocommerce_checkout_process', 'check_if_selected_checkout' );
function check_if_selected_checkout() {
	$array_cp = ['68070', '68200', '71330', '71294', '68288', '71588', '71355', '70484', '71256', '71550', '70442', '71516', '68026', '70450', '71586', '71313', '68290', '71334', '68235', '71580', '70426', '71235', '71408', '68207', '68125', '71220', '68237', '68254', '68204', '71268', '68043', '71207', '68067', '68140', '70400', '71249', '71210', '71508', '70496', '70411', '71526', '71569', '68133', '71202', '70423', '71333', '71224', '71205', '68230', '70445', '68293', '71283', '68090', '68218', '68287', '68259', '70480', '70497', '68120', '71574', '71242', '71400', '71233', '68149', '71363', '71505', '71520', '71203', '71575', '70461', '70437', '68146', '71354', '68213', '70474', '68250', '70434', '68214', '68269', '71232', '71529', '71552', '71578', '68277', '68210', '71316', '71343', '71545', '68013', '68216', '68143', '68157', '71352', '68130', '71266', '71267', '70410', '71507', '71240', '68273', '68276', '68016', '71246', '68266', '71248', '68028', '68233', '70456', '70412', '71273', '71323', '71548', '71568', '71292', '71340', '68068', '71502', '70436', '68126', '71317', '70435', '71218', '71404', '68268', '71228', '71406', '68075', '71215', '71409', '68208', '68261', '71253', '68060', '71295', '68025', '70430', '71260', '68267', '68284', '70440', '68024', '68220', '71204', '68023', '71290', '68104', '70404', '70406', '71525', '68222', '71284', '71557', '68217', '71227', '71270', '70453', '70495', '68264', '68278', '68205', '68224', '71315', '68103', '70420', '71226', '71244', '71320', '71338', '71403', '68257', '68280', '71350', '70498', '68020', '68045', '71359', '68248', '68000', '68144', '70477', '68156', '68010', '68299', '70482', '71597', '71214', '71300', '68227', '68034', '68134', '71500', '71514', '68240', '71405', '68110', '71528', '68256', '71576', '70439', '71577', '68228', '70403', '71314', '71543', '70460', '68258', '68236', '68033', '71517', '71200', '70467', '68027', '71515', '68260', '68285', '68275', '68128', '70478', '71247', '71230', '68100', '71223', '71567', '71324', '71523', '71243', '71310', '71554', '71255', '68080', '71590', '71318', '68018', '70428', '71506', '70464', '71518', '71297', '68155', '68234', '68040', '68030', '71534', '68232', '71280', '71250', '68219', '70408', '68274', '71336', '68148', '68159', '68083', '70417', '68247', '71254', '71560', '70405', '68115', '71265', '68127', '71565', '68050', '71213', '68039', '68153', '71512', '68150', '71357', '68154', '71360', '71245', '71337', '70458', '71530', '71504', '71553', '68044', '68270', '68226', '68262', '70479', '71217', '71573', '71231', '71286', '71595', '71364', '70407', '68263', '68283', '68244', '71510', '68297', '71537', '71274', '71562', '71566', '71570', '68238', '70424', '71222', '70805'];
	$items = WC()->cart->get_cart();
	$accept_checkout = true;
	foreach($items as $item => $values){
		$product_id = $values['product_id'];
        $vendor_id = get_post_field('post_author', $product_id);
		if($vendor_id === '2705'){
			$accept_checkout = false;
		}
	}
	if ( (!in_array($_POST['billing_postcode'],  $array_cp)) && !$accept_checkout ){
		wc_add_notice( 'Los productos del vendedor Rintin Oaxaca no están disponibles en tu zona.', 'error' );
	}
	 if ( empty( $_POST['billing_address_1'] ) ) {
 	wc_add_notice( 'Por favor ingresa una dirección', 'error' );
 }
 if ( empty( $_POST['billing_city'] ) ) {
 	wc_add_notice( 'Por favor ingresa tu colonia', 'error' );
 }
 if ( empty( $_POST['billing_ciudad'] ) ) {
 	wc_add_notice( 'Por favor ingresa tu ciudad', 'error' );
 }
 if ( empty( $_POST['billing_phone'] ) ) {
 	wc_add_notice( 'Por favor ingresa tu numero de WhatsApp', 'error' );
 }
 if ( empty( $_POST['billing_phone1'] ) ) {
 	wc_add_notice( 'Por favor ingresa tu numero de llamadas', 'error' );
 }
 if ( empty( $_POST['billing_first_name'] ) ) {
 	wc_add_notice( 'Por favor ingresa tu nombre', 'error' );
 }
 if ( empty( $_POST['billing_last_name_field'] ) ) {
 	wc_add_notice( 'Por favor ingresa tu apellido', 'error' );
 }
 if ( empty( $_POST['billing_tipo_negocio_entrega_negocio'] ) ) {
 	wc_add_notice( 'Por favor ingresa el tipo de propiedad para la entrega', 'error' );
 }
 if ( empty( $_POST['billing_hora_preferente_entrega'] ) ) {
 	wc_add_notice( 'Por favor ingresa la hora preferente de entrega', 'error' );
 }
 if ( empty( $_POST['billing_state'] ) ) {
 	wc_add_notice( 'Por favor ingresa tu estado', 'error' );
 }
 if ( empty( $_POST['billing_postcode'] ) ) {
 	wc_add_notice( 'Por favor ingresa tu código postal', 'error' );
 }
 if ( empty( $_POST['billing_ext_num'] ) ) {
 	wc_add_notice( 'Por favor ingresa tu numero exterior', 'error' );
 }
}

add_filter('woocommerce_available_payment_gateways', 'remove_payment_method');
function remove_payment_method($gateways) {
	$is_in_cp_array = get_transient('is_in_cp_array');
	if((WC()->session !== null) && ($is_in_cp_array || (WC()->session->get('chosen_shipping_methods') !== null && (WC()->session->get('chosen_shipping_methods')[0] === 'flat_rate:33' || WC()->session->get('chosen_shipping_methods')[0] === 'flat_rate:30')))){
		$gatewayas[] = 'WC_Gateway_Cheque';
	} else {
		unset($gateways['cheque']);
	}
    return $gateways;
}

function custom_shipping_method_cost( $cost, $shipping_rate ) {
    $is_in_oax_array = get_transient('is_in_oax');
	$is_in_99_array = get_transient('is_in_99');
    $cart_subtotal = WC()->cart->subtotal;
		if ( $cart_subtotal >= 2000) {
			if ('flat_rate:16' === $shipping_rate->id && $is_in_99_array && 'cheque' === WC()->session->get('chosen_payment_method')){
				$new_cost = 179;
				return $new_cost;
			}
			$new_cost = 0;
			return $new_cost;
		} else {
			if ('flat_rate:30' === $shipping_rate->id || 'flat_rate:33' === $shipping_rate->id){
					$new_cost = 0;
					return $new_cost;
				}
			if($is_in_oax_array){
				if ('flat_rate:16' === $shipping_rate->id){
					$new_cost = 150;
					return $new_cost;
				}
			} if($is_in_99_array) {
				if ('flat_rate:16' === $shipping_rate->id){
					$new_cost = 179;
					return $new_cost;
				}
			}
		}
    return $cost;
}
add_filter( 'woocommerce_shipping_rate_cost', 'custom_shipping_method_cost', 10, 2 );



add_action('woocommerce_cart_calculate_fees','custom_handling_fee',10,1);
function custom_handling_fee($cart){
	$is_in_oax_array = get_transient('is_in_oax');
	if(is_admin() && ! defined('DOING_AJAX'))
        return;
	if(!($cart->has_discount())){
		if($cart->subtotal >= 2000 && !$is_in_oax_array){
			return;
		} elseif('cheque' === WC()->session->get('chosen_payment_method') && WC()->session->get('chosen_shipping_methods')[0] === 'free_shipping:17' && $is_in_oax_array){
			$cart->add_fee('Cargo por envío a domicilio en Oaxaca', 50, true);
		} else {
			if('cheque' !== WC()->session->get('chosen_payment_method')){
				return;
			}
		}
	}
} 

add_action( 'woocommerce_check_cart_items', 'dokan_minimum_products_amount' );
function dokan_minimum_products_amount()
{
    $eachVendorCartTotal = array();
	$eachVendorQtyTotal = array();
    $items = WC()->cart->get_cart();
	$center_min = 0;
	$has_center = false;
	$center_shops = '';
    foreach ($items as $item => $values) {

        $product_id = $values['product_id'];
        $product_qty = $values['quantity'];
        $product_price = $values['line_total'] * $product_qty;
        $vendor_id = get_post_field('post_author', $product_id);
		$store_info_in_cart = dokan_get_store_info($vendor_id);
		$dokan_info = dokan_get_store_info($vendor_id);
		$zone = $dokan_info['zone'];
		$vendor = dokan()->vendor->get($vendor_id);
		if ($zone == 'centro'){
			$center_min = $center_min + $product_price;
			$has_center = true;
			$center_shops = $center_shops.$vendor->get_shop_name().', ';
		}
		if(!empty($store_info_in_cart['minimum_order'] ) or $zone == 'centro') {
			if (!array_key_exists($vendor_id, $eachVendorCartTotal)) {
            	$eachVendorCartTotal[$vendor_id] = $product_price;
			} else {
				$sub_total = $product_price + $eachVendorCartTotal[$vendor_id];
				$eachVendorCartTotal[$vendor_id] = $sub_total;
			}
			
		}
		if(!empty($store_info_in_cart['minimum_products']) or $zone == 'centro') {
			if (!array_key_exists($vendor_id, $eachVendorQtyTotal)) {
            	$eachVendorQtyTotal[$vendor_id] = $product_qty;
			} else {
				$sub_total = $product_qty + $eachVendorQtyTotal[$vendor_id];
				$eachVendorQtyTotal[$vendor_id] = $sub_total;
			}
		}
    }
	

	if (!empty($eachVendorQtyTotal)){
		foreach ($eachVendorQtyTotal as $vendor_id => $value){
			$errorMessageQuantity = "El total de su pedido actual para %s es %s: debe tener un pedido con un mínimo de %s para realizar su pedido para este proveedor";

            $store_info = dokan_get_store_info($vendor_id);
            $store_name = $store_info['store_name'];
			$dokan_info = dokan_get_store_info($vendor_id);
			$zone = $dokan_info['zone'];
			if(!empty($store_info['minimum_products']) and $store_info['minimum_products'] > 0) {
                $vendor_minimum = !empty($store_info['minimum_products']) ? $store_info['minimum_products'] : 0;
                if ($value < $vendor_minimum) {
                    if (is_cart()) {

                        wc_print_notice(
                            sprintf($errorMessageQuantity,
                                $store_name,
                                ($value),
                                ($vendor_minimum)
                            ), 'error'
                        );

                    } else {
                        wc_add_notice(
                            sprintf($errorMessageQuantity,
                                $store_name,
                                ($value),
                                ($vendor_minimum)
                            ), 'error'
                        );
                    }
                }
            }
		}
	}

    if (!empty($eachVendorCartTotal)) {
		
        foreach ($eachVendorCartTotal as $vendor_id => $value) {
            $errorMessage = "El total de prendas de su pedido actual para %s es de %s prendas: debe tener un pedido mínimo de %s prendas para realizar su pedido para este proveedor";
			$errorMessageQuantity = "El total de su pedido actual para %s es %s: debe tener un pedido con un mínimo de %s para realizar su pedido para este proveedor";

            $store_info = dokan_get_store_info($vendor_id);
            $store_name = $store_info['store_name'];
			$dokan_info = dokan_get_store_info($vendor_id);
			$zone = $dokan_info['zone'];
            
			if(!empty($store_info['minimum_order']) and $store_info['minimum_order'] > 0) {
				$vendor_minimum = !empty($store_info['minimum_order']) ? $store_info['minimum_order'] : 0;
                if ($value < $vendor_minimum) {
                    if (is_cart()) {

                        wc_print_notice(
                            sprintf($errorMessageQuantity,
                                $store_name,
                                wc_price($value),
                                wc_price($vendor_minimum)
                            ), 'error'
                        );

                    } else {
                        wc_add_notice(
                            sprintf($errorMessageQuantity,
                                $store_name,
                                wc_price($value),
                                wc_price($vendor_minimum)
                            ), 'error'
                        );
                    }
                }
            }
        }
		if ($has_center and $store_info['minimum_order'] !== NULL){
			$errorMessageZone = "El total de su pedido de las tiendas %s es de %s. Debe tener un mínimo de $1100 para realizar su pedido con estos proveedores";
			if ($center_min < 1100){
				if (is_cart()) {

                        wc_print_notice(
                            sprintf($errorMessageZone,
                                $center_shops,
                                wc_price($center_min),
                                wc_price(1000)
                            ), 'error'
                        );

                    } else {
                        wc_add_notice(
                            sprintf($errorMessageQuantity,
                                $center_shops,
                                wc_price($center_min),
                                wc_price(1100)
                            ), 'error'
                        );
                    }
			}
		}
    }
} 

remove_filter( 'woocommerce_cart_shipping_packages', 'dokan_custom_split_shipping_packages' );
remove_filter( 'woocommerce_shipping_package_name', 'dokan_change_shipping_pack_name');
remove_action( 'woocommerce_checkout_create_order_shipping_item', 'dokan_add_shipping_pack_meta');

add_action('woocommerce_checkout_process', 'custom_validate_billing_phone');
function custom_validate_billing_phone() {
    $is_correct = preg_match('/^[0-9]{10,12}$/', $_POST['billing_phone']);
    if ( $_POST['billing_phone'] && !$is_correct) {
        wc_add_notice( __( 'Ingresa un número de celular correcto para el campo número de WhatsApp.' ), 'error' );
    }
	$is_correct1 = preg_match('/^[0-9]{6,12}$/', $_POST['billing_phone1']);
    if ( $_POST['billing_phone1'] && !$is_correct1) {
        wc_add_notice( __( 'Ingresa un número de telefono correcto para el campo número de llamadas.' ), 'error' );
    }
}
?>