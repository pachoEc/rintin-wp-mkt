<?php
/**
 * Whatsapp Notification Triggers */

// Sends message whenever the order is confirmed
add_action('woocommerce_thankyou', 'order_created_message_handler');

function order_created_message_handler($order_id) {
    $order = wc_get_order($order_id);

	$order_total = $order->get_total();
	$customer_id = $order->get_user_id();
	$customer_name = $order->get_billing_first_name();
	$customer_phone_number = '521' . substr($order->get_billing_phone(), -10);

	$prices = get_order_prices($order_id);
	$product_string = get_products_of_suborder($order_id);

	$orders = wc_get_orders(array(
		'customer_id' => $customer_id,
	));

	if (!empty($orders)) {
		$params = [
				['name' => 'name', 'value' => strval($customer_name)], 
				['name' => 'orden_padre', 'value' => strval($order_id)], 
				['name' => 'monto_orden', 'value' => strval($order_total)],
				['name' => 'subtotal_orden', 'value' => strval($prices['subtotal'])],
				['name' => 'costo_envio', 'value' => strval($prices['shipping'])],
				['name' => 'productos_ingresados_orden', 'value' => $product_string],
			];
			if(get_post_meta($order_id, 'welcome_message_sent', true) !== '1'){
				send_post_request_to_api('01_pedido_ingresado_v10', $params, $customer_phone_number);
			}
	} else {
		$params = [
				['name' => 'name', 'value' => strval($customer_name)], 
				['name' => 'orden_padre', 'value' => strval($order_id)], 
				['name' => 'monto_orden', 'value' => strval($order_total)],
				['name' => 'subtotal_orden', 'value' => strval($prices['subtotal'])],
				['name' => 'costo_envio', 'value' => strval($prices['shipping'])],
				['name' => 'productos_ingresados_orden', 'value' => $product_string],
			];
		if(get_post_meta($order_id, 'welcome_message_sent', true) !== '1'){
			send_post_request_to_api('01_pedido_ingresado_nuevo_v3', $params, $customer_phone_number);
		}
	}
	update_post_meta($order_id, 'welcome_message_sent', '1');
}


add_action('woocommerce_order_status_changed', 'order_status_change_notification_handler');
function order_status_change_notification_handler($order_id) {
	$order = wc_get_order( $order_id );

	$order_status  = $order->get_status();
	$order_parent_id = $order->get_parent_id();
	$has_childs = get_post_meta($order_id, 'has_sub_order', true);
	$order_total = $order->get_total();
    $order_state = get_post_meta($order_id, '_billing_state', true);
    $prices = get_order_prices($order_id);
	$is_cod = $order->get_payment_method() === 'cheque' ? true : false;
    $bodega = get_post_meta($order_id, '_bodega_devolucion', true);

    $customer_phone_number = '521' . substr($order->get_billing_phone(), -10);
	$customer_name = $order->get_billing_first_name();
	
	$logis_op = get_post_meta($order_id, '_logis_op', true);
	$numero_guia = get_post_meta($order_id, '_numero_guia', true);

	error_log('Entro a la funcion order_status_change_notification_handler con el numero de orden: ' . strval($order_id) . ' y cambio al estado ' . $order_status);
	
    // Parent Order Status
    // No parent and has childs
	if($order_parent_id === 0 && $has_childs === '1'){
		if($order_status === 'pendiente-pago-1'){
			wc_reduce_stock_levels( $order_id );
			$params = [
				['name' => 'name', 'value' => strval($customer_name)], 
				['name' => 'orden_padre', 'value' => strval($order_id)], 
				['name' => 'subtotal_orden', 'value' => strval($prices['subtotal'])],
				['name' => 'costo_envio', 'value' => strval($prices['shipping'])],
				['name' => 'monto_orden', 'value' => strval($prices['total'])],
			];
			send_post_request_to_api('01_pedido_ingresado_no_pagado_v4', $params, $customer_phone_number);
		}
		elseif($order_status === 'on-hold'){
				$to_contra_entrega = can_be_sent_to_seller($order_id);
				set_transient(strval($order_id), $to_contra_entrega, MINUTE_IN_SECONDS);
				if($to_contra_entrega){
					$order->update_status( 'wc-contra-entrega' );
				} else {
					$order->update_status( 'wc-valida_cod_client' );
				}
		}
		elseif($order_status === 'processing'){
			$order->update_status( 'wc-completed' );
			// Send message to mel
		}
		elseif($order_status === 'completed'){
			$params = [
				['name' => 'name', 'value' => strval($customer_name)], 
				['name' => 'orden_padre', 'value' => strval($order_id)], 
				['name' => 'monto_orden', 'value' => strval($order_total)],
				['name' => 'subtotal_orden', 'value' => strval($prices['subtotal'])],
				['name' => 'costo_envio', 'value' => strval($prices['shipping'])],
			];
			send_post_request_to_api('02_pagorecibido_v4', $params, $customer_phone_number);
		}
	}

    // No parent nor childs
	elseif($order_parent_id === 0 && $has_childs !== '1'){
		if($order_status === 'pendiente-pago-1'){
			wc_reduce_stock_levels( $order_id );
			$prices = get_order_prices($order_id);
			$params = [
				['name' => 'name', 'value' => strval($customer_name)], 
				['name' => 'orden_padre', 'value' => strval($order_id)], 
				['name' => 'subtotal_orden', 'value' => strval($prices['subtotal'])],
				['name' => 'costo_envio', 'value' => strval($prices['shipping'])],
				['name' => 'monto_orden', 'value' => strval($prices['total'])],
			];
			send_post_request_to_api('01_pedido_ingresado_no_pagado_v4', $params, $customer_phone_number);
		}
		elseif($order_status === 'contra-entrega'){
			$order->update_status( 'wc-generar_pedido' );
		}
		elseif($order_status === 'generar_pedido'){
				$current_product_number = 1;
				foreach($order->get_items() as $item_id => $item){
					$post = get_post( $item->get_product_id());
					$product_name = get_the_title($item->get_product_id());
					$parent_id = $post->post_author;
					$phone_number = '';
					$seller_bodega = ['3587', '998', '1352', '2636', '3759', '2751', '2166', '1663'];
					if($parent_id === '3465'){
						$phone_number = '5215545447672';
					}
					else if(in_array($parent_id, $seller_bodega)){
						$phone_number = '5215523202137';
					}
					if($order->get_user_id() === 1340){
						$phone_number = '5218123640742';
					}
					$sku = get_post_meta($item->get_product_id(), '_sku', true);
					$post_url = get_permalink($item->get_product_id());
					$seller_id = wp_get_post_parent_id($item_id);
					$vendor = dokan()->vendor->get($post->post_author)->get_shop_name();
					$quantity = $item->get_quantity();
					$params = [
						['name' => 'pedido_de', 'value' => strval($current_product_number)],
						['name' => 'total_pedidos', 'value' => strval(count($order->get_items()))],
						['name' => 'seller_orden', 'value' => strval($vendor)],
						['name' => 'numero_pedido_seller', 'value' => strval($order_id)],
						['name' => 'nombre_producto', 'value' => strval($product_name)],
						['name' => 'cantidad_producto', 'value' => strval($quantity)],
						['name' => 'sku_producto', 'value' => strval($sku)],
						['name' => 'imagen_producto', 'value' => strval($post_url)],

					];
					error_log($order_id);
					error_log(get_post_meta($order_id, 'seller_message_sent', true));
					if(get_post_meta($order_id, 'seller_message_sent', true) !== '1'){
						send_post_request_to_api('envio_sellers_v8', $params, $phone_number);
					}
					if(count($order->get_items()) === $current_product_number){
						update_post_meta($order_id, 'seller_message_sent', '1');
					}
					$current_product_number += 1;
					if($parent_id === '3465' || in_array($parent_id, $seller_bodega)){
						$order->update_status( 'wc-recolectar-2' );					
					}
				}
			
		}
		elseif($order_status === 'completed'){
			$params = [
				['name' => 'name', 'value' => strval($customer_name)], 
				['name' => 'orden_padre', 'value' => strval($order_id)], 
				['name' => 'monto_orden', 'value' => strval($order_total)],
				['name' => 'subtotal_orden', 'value' => strval($prices['subtotal'])],
				['name' => 'costo_envio', 'value' => strval($prices['shipping'])],
			];
			send_post_request_to_api('02_pagorecibido_v4', $params, $customer_phone_number);
			$order->update_status( 'wc-generar_pedido' );
		}
		elseif($order_status === 'on-hold'){
				$to_contra_entrega = can_be_sent_to_seller($order_id);
				if($to_contra_entrega){
					$order->update_status( 'wc-generar_pedido' );
				} else {
					$order->update_status( 'wc-valida_cod_client' );
				}
		}
		elseif($order_status === 'parcel'){
			if($logis_op === 'ogramak'){
				$params = [
					['name' => 'name', 'value' => $customer_name]
				];
				send_post_request_to_api('03_pedido_en_camino_v3_ogramak_v3', $params, $customer_phone_number);
			}
			elseif($logis_op === '99 minutos'){
				$params = [
					['name' => 'name', 'value' => $customer_name],
					['name' => 'liga_proveedor_logistico', 'value' => 'https://tracking.99minutos.com/search/' . $numero_guia],
					['name' => 'numero_guia', 'value' => $numero_guia]
				];
				send_post_request_to_api('03_pedido_en_camino_v4_estafetay99_', $params, $customer_phone_number);
			}
			elseif($logis_op === 'estafeta'){
				$params = [
					['name' => 'name', 'value' => $customer_name],
					['name' => 'liga_proveedor_logistico', 'value' => 'https://www.estafeta.com/Herramientas/Rastreo'],
					['name' => 'numero_guia', 'value' => $numero_guia]
				];
				send_post_request_to_api('03_pedido_en_camino_v4_estafetay99_', $params, $customer_phone_number);
			}
		}
		elseif($order_status === 'processing'){
		 	$order->update_status( 'wc-generar_pedido' );
		}
		elseif($order_status === 'cod-recepcion-2'){
			if($order_state === 'OA'){
				$params = [
					['name' => 'name', 'value' => $customer_name], 
					['name' => 'punto_de_recojo', 'value' => 'Oaxaca'],
					['name' => 'direccion_punto_de_recojo', 'value' => 'https://goo.gl/maps/2dUmZwuJD3suHnoP7'],
					['name' => 'orden_padre', 'value' => $order_id],
					['name' => 'subtotal_orden', 'value' => strval($prices['subtotal'])],
					['name' => 'costo_envio', 'value' => strval($prices['shipping'])],
					['name' => 'monto_orden', 'value' => strval($prices['total'])],
				];
				send_post_request_to_api('05_pedido_en_punto_recojo_no_pagado_v5', $params, $customer_phone_number);
			}
			elseif($order_state === 'DF'){
				$params = [
					['name' => 'name', 'value' => $customer_name], 
					['name' => 'punto_de_recojo', 'value' => 'CDMX'],
					['name' => 'direccion_punto_de_recojo', 'value' => 'https://goo.gl/maps/eVRfQowXgyPs6RjcA'],
					['name' => 'orden_padre', 'value' => $order_id],
					['name' => 'subtotal_orden', 'value' => strval($prices['subtotal'])],
					['name' => 'costo_envio', 'value' => strval($prices['shipping'])],
					['name' => 'monto_orden', 'value' => strval($prices['total'])],
				];
				send_post_request_to_api('05_pedido_en_punto_recojo_no_pagado_v5', $params, $customer_phone_number);
			}
		}
		elseif($order_status === 'recepcion-2'){
			if($order_state === 'OA'){
				$params = [
					['name' => 'name', 'value' => $customer_name], 
					['name' => 'punto_de_recojo', 'value' => 'Oaxaca'],
					['name' => 'direccion_punto_de_recojo', 'value' => 'https://goo.gl/maps/2dUmZwuJD3suHnoP7'],
					['name' => 'orden_padre', 'value' => $order_id],
				];
				send_post_request_to_api('05_pedido_en_punto_recojo_v3', $params, $customer_phone_number);
			}
			elseif($order_state === 'DF'){
				$params = [
					['name' => 'name', 'value' => $customer_name], 
					['name' => 'punto_de_recojo', 'value' => 'CDMX'],
					['name' => 'direccion_punto_de_recojo', 'value' => 'https://goo.gl/maps/eVRfQowXgyPs6RjcA'],
					['name' => 'orden_padre', 'value' => $order_id],
				];
				send_post_request_to_api('05_pedido_en_punto_recojo_v3', $params, $customer_phone_number);
			}
		}
		elseif($order_status === 'pickup-4'){
			if($order_state === 'OA'){
				$params = [
					['name' => 'name', 'value' => $customer_name], 
					['name' => 'punto_de_recojo', 'value' => 'Oaxaca'],
					['name' => 'direccion_punto_de_recojo', 'value' => 'https://goo.gl/maps/2dUmZwuJD3suHnoP7'],
					['name' => 'orden_padre', 'value' => $order_id],
					['name' => 'subtotal_orden', 'value' => strval($prices['subtotal'])],
					['name' => 'costo_envio', 'value' => strval($prices['shipping'])],
					['name' => 'monto_orden', 'value' => strval($prices['total'])],
					['name' => 'pedido_pagado', 'value' => $is_cod ? 'Recuerda que deberás pagarlo directamente en nuestro punto de recojo.' : '.'],
				];
				send_post_request_to_api('06_pedido_pickup_v3', $params, $customer_phone_number);
			}
			elseif($order_state === 'DF'){
				$params = [
					['name' => 'name', 'value' => $customer_name], 
					['name' => 'punto_de_recojo', 'value' => 'CDMX'],
					['name' => 'direccion_punto_de_recojo', 'value' => 'https://goo.gl/maps/eVRfQowXgyPs6RjcA'],
					['name' => 'orden_padre', 'value' => $order_id],
					['name' => 'subtotal_orden', 'value' => strval($prices['subtotal'])],
					['name' => 'costo_envio', 'value' => strval($prices['shipping'])],
					['name' => 'monto_orden', 'value' => strval($prices['total'])],
					['name' => 'pedido_pagado', 'value' => $is_cod ? 'Recuerda que deberás pagarlo directamente en nuestro punto de recojo.' : '.'],
				];
				send_post_request_to_api('06_pedido_pickup_v3', $params, $customer_phone_number);
			}
		}
		elseif($order_status === 'hunter-4'){
			if($order_state === 'OA'){
				$params = [
					['name' => 'name', 'value' => $customer_name], 
					['name' => 'orden_padre', 'value' => $order_id],
					['name' => 'bodega_interna', 'value' => 'Oaxaca'],
					['name' => 'subtotal_orden', 'value' => strval($prices['subtotal'])],
					['name' => 'costo_envio', 'value' => strval($prices['shipping'])],
					['name' => 'monto_orden', 'value' => strval($prices['total'])],
					['name' => 'pedido_pagado', 'value' => $is_cod ? 'Recuerda que deberás pagarlo directamente en nuestro punto de recojo.' : '.'],
					['name' => 'direccion_punto_de_recojo', 'value' => '+5219511390807'],
				];
				send_post_request_to_api('06_pedido_con_hunter_v4', $params, $customer_phone_number);
			}
			elseif($order_state === 'VE'){
				$params = [
					['name' => 'name', 'value' => $customer_name], 
					['name' => 'orden_padre', 'value' => $order_id],
					['name' => 'bodega_interna', 'value' => 'Veracruz'],
					['name' => 'subtotal_orden', 'value' => strval($prices['subtotal'])],
					['name' => 'costo_envio', 'value' => strval($prices['shipping'])],
					['name' => 'monto_orden', 'value' => strval($prices['total'])],
					['name' => 'pedido_pagado', 'value' => $is_cod ? 'Recuerda que deberás pagarlo directamente en nuestro punto de recojo.' : '.'],
					['name' => 'direccion_punto_de_recojo', 'value' => '+5212283136791'],
				];
				send_post_request_to_api('06_pedido_con_hunter_v4', $params, $customer_phone_number);
			}
		}
		elseif($order_status === 'cancelled'){ 
            error_log('Entro a estado cancelado y es una orden sin hijos');
			$products_string = get_products_of_suborder($order_id);
			$params = [
				['name' => 'name', 'value' => $customer_name],
				['name' => 'orden_padre', 'value' => $order_id],
				['name' => 'productos_eliminados_orden', 'value' => $products_string]
			];
			send_post_request_to_api('08_pedido_cancelado_v3', $params, $customer_phone_number);
		}
		elseif($order_status === 'devuelto' && ($bodega === '2166' || $bodega === '2705')){
			error_log('entro al estado de devuelto'); //Orden sin hijos
			$message = $bodega === '2166' ? 'Orden devuelta a Bodega CDMX con los siguientes productos: ' : 'Orden devuelta a Bodega Oaxaca con los siguientes productos: ';
			foreach ( $order->get_items() as $item_id => $item ) {
				$product_id = $item->get_product_id();
				$variation_id = $item->get_variation_id();
    			$quantity = $item->get_quantity();
				$product = wc_get_product($product_id);
				if($variation_id > 0){
					$post_type = get_post_type($variation_id);
				} else {
					$post_type = get_post_type($product_id);
				}
    			if($product && $post_type === 'product'){
					$message .= add_product_return($product, $product_id, $quantity, $bodega, $message, $order_id);
				}
				elseif($product && $post_type === 'product_variation'){
					$message = create_variable_product($product, $product_id, $variation_id, $quantity, $bodega, $message, $order_id);
				}
				
				
			}
			$order->add_order_note( $message );
		}
	}
	elseif($order_parent_id !== 0){
		if($order_status === 'processing'){
		 	$order->update_status( 'wc-generar_pedido' );
		}
		elseif($order_status === 'completed'){
			$order->update_status( 'wc-generar_pedido' );
		}
		elseif($order_status === 'generar_pedido'){
				$current_product_number = 1;
				foreach($order->get_items() as $item_id => $item){
					$post = get_post( $item->get_product_id());
					$product_name = get_the_title($item->get_product_id());
					$parent_id = $post->post_author;
					$phone_number = '';
					$seller_bodega = ['3587', '998', '1352', '2636', '3759', '2751', '2166', '1663'];
					if($parent_id === '3465'){
						$phone_number = '5215545447672';
					}
					else if(in_array($parent_id, $seller_bodega)){
						$phone_number = '5215523202137';
					}
					if($order->get_user_id() === 1340){
						$phone_number = '5218123640742';
					}
					$sku = get_post_meta($item->get_product_id(), '_sku', true);
					$post_url = get_permalink($item->get_product_id());
					$seller_id = wp_get_post_parent_id($item_id);
					$vendor = dokan()->vendor->get($post->post_author)->get_shop_name();
					$quantity = $item->get_quantity();
					$params = [
						['name' => 'pedido_de', 'value' => strval($current_product_number)],
						['name' => 'total_pedidos', 'value' => strval(count($order->get_items()))],
						['name' => 'seller_orden', 'value' => strval($vendor)],
						['name' => 'numero_pedido_seller', 'value' => strval($order_id)],
						['name' => 'nombre_producto', 'value' => strval($product_name)],
						['name' => 'cantidad_producto', 'value' => strval($quantity)],
						['name' => 'sku_producto', 'value' => strval($sku)],
						['name' => 'imagen_producto', 'value' => strval($post_url)],

					];
					error_log($order_id);
					error_log(true);
					if(get_post_meta($order_id, 'seller_message_sent', true) !== '1'){
						send_post_request_to_api('envio_sellers_v8', $params, $phone_number);
					}
					
					if(count($order->get_items()) === $current_product_number){
						update_post_meta($order_id, 'seller_message_sent', '1');
					}
					$current_product_number += 1;
					if($parent_id === '3465' || in_array($parent_id, $seller_bodega)){
						$order->update_status( 'wc-recolectar-2' );					
					}
					
				}
		}
		elseif($order_status === 'on-hold'){
			$send_to_seller = get_transient(strval($order_parent_id));
			if($send_to_seller){
				$order->update_status( 'wc-generar_pedido' );
			} else {
				$order->update_status( 'wc-valida_cod_client' );
			}
		}
		elseif($order_status === 'contra-entrega'){
			$order->update_status( 'wc-generar_pedido' );
		}
		elseif($order_status === 'cod-recepcion-2'){
			$brother_values = check_brother_statuses($order_id, $order_parent_id, $order_status);
			if( $brother_values['boolean'] ){
				if($order_state === 'OA'){
					$params = [
						['name' => 'name', 'value' => $customer_name], 
						['name' => 'punto_de_recojo', 'value' => 'Oaxaca'],
						['name' => 'direccion_punto_de_recojo', 'value' => 'https://goo.gl/maps/2dUmZwuJD3suHnoP7'],
						['name' => 'orden_padre', 'value' => $order_parent_id],
						['name' => 'subtotal_orden', 'value' => strval($brother_values['subtotal'])],
						['name' => 'costo_envio', 'value' => strval($brother_values['shipping'])],
						['name' => 'monto_orden', 'value' => strval($brother_values['total'])],
					];
					send_post_request_to_api('05_pedido_en_punto_recojo_no_pagado_v5', $params, $customer_phone_number);
				}
				elseif($order_state === 'DF'){
					$params = [
						['name' => 'name', 'value' => $customer_name], 
						['name' => 'punto_de_recojo', 'value' => 'CDMX'],
						['name' => 'direccion_punto_de_recojo', 'value' => 'https://goo.gl/maps/eVRfQowXgyPs6RjcA'],
						['name' => 'orden_padre', 'value' => $order_parent_id],
						['name' => 'subtotal_orden', 'value' => strval($brother_values['subtotal'])],
						['name' => 'costo_envio', 'value' => strval($brother_values['shipping'])],
						['name' => 'monto_orden', 'value' => strval($brother_values['total'])],
					];
					send_post_request_to_api('05_pedido_en_punto_recojo_no_pagado_v5', $params, $customer_phone_number);
				}
			}
		}
		elseif($order_status === 'recepcion-2'){
			if( check_brother_statuses($order_id, $order_parent_id, $order_status)['boolean']){
				if($order_state === 'OA'){
					$params = [
						['name' => 'name', 'value' => $customer_name], 
						['name' => 'punto_de_recojo', 'value' => 'Oaxaca'],
						['name' => 'direccion_punto_de_recojo', 'value' => 'https://goo.gl/maps/2dUmZwuJD3suHnoP7'],
						['name' => 'orden_padre', 'value' => $order_parent_id],
					];
					send_post_request_to_api('05_pedido_en_punto_recojo_v3', $params, $customer_phone_number);
				}
				elseif($order_state === 'DF'){
					$params = [
						['name' => 'name', 'value' => $customer_name], 
						['name' => 'punto_de_recojo', 'value' => 'CDMX'],
						['name' => 'direccion_punto_de_recojo', 'value' => 'https://goo.gl/maps/eVRfQowXgyPs6RjcA'],
						['name' => 'orden_padre', 'value' => $order_parent_id],
					];
					send_post_request_to_api('05_pedido_en_punto_recojo_v3', $params, $customer_phone_number);
				}
			}
		}
		elseif($order_status === 'delivered'){
			if(check_brother_statuses($order_id, $order_parent_id, $order_status)['boolean']){
				$params = [
					['name' => 'name', 'value' => $customer_name], 
					['name' => 'orden_padre', 'value' => $order_parent_id]
				];
				send_post_request_to_api('07_pedido_entregado', $params, $customer_phone_number);
			}
		}
		elseif($order_status === 'pickup-4'){
			$brother_values = check_brother_statuses($order_id, $order_parent_id, $order_status);
			if($brother_values['boolean']){
				if($order_state === 'OA'){
					$params = [
						['name' => 'name', 'value' => $customer_name], 
						['name' => 'punto_de_recojo', 'value' => 'Oaxaca'],
						['name' => 'direccion_punto_de_recojo', 'value' => 'https://goo.gl/maps/2dUmZwuJD3suHnoP7'],
						['name' => 'orden_padre', 'value' => $order_parent_id],
						['name' => 'subtotal_orden', 'value' => strval($brother_values['subtotal'])],
						['name' => 'costo_envio', 'value' => strval($brother_values['shipping'])],
						['name' => 'monto_orden', 'value' => strval($brother_values['total'])],
						['name' => 'pedido_pagado', 'value' => $is_cod ? 'Recuerda que deberás pagarlo directamente en nuestro punto de recojo.' : '.'],
					];
					send_post_request_to_api('06_pedido_pickup_v3', $params, $customer_phone_number);
				}
				elseif($order_state === 'DF'){
					$params = [
						['name' => 'name', 'value' => $customer_name], 
						['name' => 'punto_de_recojo', 'value' => 'CDMX'],
						['name' => 'direccion_punto_de_recojo', 'value' => 'https://goo.gl/maps/eVRfQowXgyPs6RjcA'],
						['name' => 'orden_padre', 'value' => $order_parent_id],
						['name' => 'subtotal_orden', 'value' => strval($brother_values['subtotal'])],
						['name' => 'costo_envio', 'value' => strval($brother_values['shipping'])],
						['name' => 'monto_orden', 'value' => strval($brother_values['total'])],
						['name' => 'pedido_pagado', 'value' => $is_cod ? 'Recuerda que deberás pagarlo directamente en nuestro punto de recojo.' : '.'],
					];
					send_post_request_to_api('06_pedido_pickup_v3', $params, $customer_phone_number);
				}
			}
		}
		elseif($order_status === 'hunter-4'){
			$brother_values = check_brother_statuses($order_id, $order_parent_id, $order_status);
			if($brother_values['boolean']){
				if($order_state === 'OA'){
					$params = [
						['name' => 'name', 'value' => $customer_name], 
						['name' => 'orden_padre', 'value' => $order_parent_id],
						['name' => 'bodega_interna', 'value' => 'Oaxaca'],
						['name' => 'subtotal_orden', 'value' => strval($brother_values['subtotal'])],
						['name' => 'costo_envio', 'value' => strval($brother_values['shipping'])],
						['name' => 'monto_orden', 'value' => strval($brother_values['total'])],
						['name' => 'pedido_pagado', 'value' => $is_cod ? 'Recuerda que deberás pagarlo directamente en nuestro punto de recojo.' : '.'],
						['name' => 'direccion_punto_de_recojo', 'value' => '+5219511390807'],
					];
					send_post_request_to_api('06_pedido_con_hunter_v4', $params, $customer_phone_number);
				}
				elseif($order_state === 'VE'){
					$params = [
						['name' => 'name', 'value' => $customer_name], 
						['name' => 'orden_padre', 'value' => $order_parent_id],
						['name' => 'bodega_interna', 'value' => 'Veracruz'],
						['name' => 'subtotal_orden', 'value' => strval($brother_values['subtotal'])],
						['name' => 'costo_envio', 'value' => strval($brother_values['shipping'])],
						['name' => 'monto_orden', 'value' => strval($brother_values['total'])],
						['name' => 'pedido_pagado', 'value' => $is_cod ? 'Recuerda que deberás pagarlo directamente en nuestro punto de recojo.' : '.'],
						['name' => 'direccion_punto_de_recojo', 'value' => '+5212283136791'],
					];
					send_post_request_to_api('06_pedido_con_hunter_v4', $params, $customer_phone_number);
				}
			}
		}
		elseif($order_status === 'parcel'){
			if(is_on_its_way($order_id, $order_parent_id, $numero_guia, $order_status)){
				if($logis_op === 'ogramak'){
					$params = [
						['name' => 'name', 'value' => $customer_name]
					];
					send_post_request_to_api('03_pedido_en_camino_v3_ogramak_v3', $params, $customer_phone_number);
				}
				elseif($logis_op === '99 minutos'){
					$params = [
						['name' => 'name', 'value' => $customer_name],
						['name' => 'liga_proveedor_logistico', 'value' => 'https://tracking.99minutos.com/'],
						['name' => 'numero_guia', 'value' => $numero_guia]
					];
					send_post_request_to_api('03_pedido_en_camino_v4_estafetay99_', $params, $customer_phone_number);
				}
				elseif($logis_op === 'estafeta'){
					$params = [
						['name' => 'name', 'value' => $customer_name],
						['name' => 'liga_proveedor_logistico', 'value' => 'https://www.estafeta.com/Herramientas/Rastreo'],
						['name' => 'numero_guia', 'value' => $numero_guia]
					];
					send_post_request_to_api('03_pedido_en_camino_v4_estafetay99_', $params, $customer_phone_number);
				}
			}
		}
		elseif($order_status === 'cancelled'){
            error_log('Entro a estado cancelado y es una orden hijo');
			$products_string = get_products_of_suborder($order_id);
			$params = [
				['name' => 'name', 'value' => $customer_name],
				['name' => 'orden_padre', 'value' => $order_parent_id],
				['name' => 'productos_eliminados_orden', 'value' => $products_string]
			];
			send_post_request_to_api('08_pedido_cancelado_v3', $params, $customer_phone_number);
		}
		elseif($order_status === 'devuelto' && ($bodega === '2166' || $bodega === '2705')){
			$message = $bodega === '2166' ? 'Orden devuelta a Bodega CDMX con los siguientes productos: ' : 'Orden devuelta a Bodega Oaxaca con los siguientes productos: ';
			
			foreach ( $order->get_items() as $item_id => $item ) {
				$product_id = $item->get_product_id();
				$variation_id = $item->get_variation_id();
    			$quantity = $item->get_quantity();
				$product = wc_get_product($product_id);
				if(isset($variation_id) && $variation_id !== 0){
					$post_type = get_post_type($variation_id);
				} else {
					$post_type = get_post_type($product_id);
				}
    			if($product && $post_type === 'product'){
					$message .= add_product_return($product, $product_id, $quantity, $bodega, $message, $order_id);
				}
				elseif($product && $post_type === 'product_variation'){
					$message = create_variable_product($product, $product_id, $variation_id, $quantity, $bodega, $message, $order_id);
				}
			}
			$order->add_order_note( $message );
		}
	}
}

function get_seller_skus($seller_id) {
    $skus = array();

    // Get all products of the seller
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'author'         => $seller_id,
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product = wc_get_product(get_the_ID());

            // Get SKU of the product
            if ($product && $product->get_sku()) {
                $skus[$product->get_sku()] = $product->get_id();
            }
        }
        wp_reset_postdata();
    }

    return $skus;
}

// Returns an array of de order final priced divided in items
function get_order_prices($order_id){
	$order = wc_get_order( $order_id );

	$shipping_price = $order->get_shipping_total();
	$order_total = $order->get_total();
	$order_subtotal = $order_total - $shipping_price;

	return array('shipping' => $shipping_price, 'total' => $order_total, 'subtotal' => $order_subtotal);
}

// Returns a string formated for the Whatsapp Notification Message containing all the products of a sub-order with its prices
function get_products_of_suborder($order_id){
	$order = wc_get_order( $order_id );
	$products_string = '';
	foreach ( $order->get_items() as $item_id => $item ) {
	  	$product_name = $item->get_name();
		$product = $item->get_product();
		$product_quantity =	$item->get_quantity();
		$price = $product->get_price();
		$sale_price = $product->get_sale_price();
	  	$products_string .= $product_name . ' x ' . strval($product_quantity) . ' - $' . (is_numeric($sale_price) ? strval($sale_price) : strval($price)) .  ', ';
	}
	return $products_string;
}

// Returns a boolean whenever an order can be sent to a seller or needs revision
function can_be_sent_to_seller($order_id){
    $order = wc_get_order($order_id);

	$has_childs = get_post_meta($order_id, 'has_sub_order', true);

	$customer_id = $order->get_customer_id();
	$orders = wc_get_orders(array(
		'customer_id' => $customer_id,
		'orderby' => 'date',
		'order' => 'DESC',
		'limit' => 25,
		'return' => 'ids',
	));
	$parent_orders = wc_get_orders(array(
		'customer_id' => $customer_id,
		'orderby' => 'date',
		'order' => 'DESC',
		'limit' => 2,
		'return' => 'ids',
		'parent' => 0
	));
	$order_key = array_search($order_id, $orders);
	$order_count = count($parent_orders);
	$child_order_ids = array();
	if($order_count > 1){
		$post_parent_id = wp_get_post_parent_id($orders[$order_key + 1]);
		if ($post_parent_id !== 0){
			$child_orders = get_children(array(
				'post_parent' => $post_parent_id,
				'post_type'   => 'shop_order'
			));
			foreach($child_orders as $child){
				if($child->post_status === 'wc-devuelto'){
					return false;
				}
			}
		} 
        else {
			if(get_post_status($orders[$order_key + 1]) === 'wc-devuelto'){
				return false;
			}
		}

		foreach($orders as $order){
			$has_childs = get_post_meta($order, 'has_sub_order', true);
			$post_parent_id = wp_get_post_parent_id($order_id);
			$child_order_ids = wc_get_orders(array(
				'return' => 'ids',
				'parent' => $order_id
			));
			if($has_childs !== '1' && $order !== $order_id && !in_array($order, $child_order_ids) && 
			   !(get_post_status($order) === 'wc-caducado' || get_post_status($order) === 'wc-devuelto' || get_post_status($order) === 'wc-delivered' ||
				get_post_status($order) === 'wc-cancelled' || get_post_status($order) === 'wc-refunded' || get_post_status($order) === 'wc-contra-cargo' ||
				get_post_status($order) === 'wc-failed' || get_post_status($order) === 'wc-devolucion_proces' || get_post_status($order) === 'auto-draft')){
				return false;
			}
		}

	}
	else {
		if($has_childs === '1' ){
			$child_orders = get_children(array(
				'post_parent' => $order_id,
				'post_type'   => 'shop_order'
			));
			$parent_order = wc_get_order($order_id);
			if(count( $parent_order->get_coupon_codes() ) === 0){
				return false;
			}
			foreach($child_orders as $child){
				$child_order = wc_get_order($child->ID);
				if(count( $child_order->get_coupon_codes() ) === 0){
					return false;
				}
			}
		}
		else {
			if(count( $order->get_coupon_codes() ) === 0){
				return false;
			}
		}
	}
	return true;
}

// Returns an array with a boolean whenever all the brother orders have been changed to the same order status
// Array also contains the relevant order pricing information formated for the Whatsapp Notification
function check_brother_statuses($order_id, $order_parent_id, $order_status){
    $order = wc_get_order( $order_parent_id );

	$child_orders = get_children(array(
		'post_parent' => $order_parent_id,
		'post_type'   => 'shop_order',
		'exclude'     => $order_id,
	));
	$result = true;
	$shipping_price = $order->get_shipping_total();
	$current_order = wc_get_order( $order_id );
	$subtotal = intval($current_order->get_total());

	foreach ($child_orders as $child){
		$child_id = $child->ID;
		$child_order = wc_get_order( $child_id );
		$total = intval($child_order->get_total());
		$subtotal += $total;
		$child_status = $child->post_status;
		$child_status = str_replace('wc-', '', $child_status); //The order_status sent into the function doesn't have the wc prefix
		if ($child_status === $order_status || $child_status === 'cancelled'){
			$result = $result && true;
		}
		else{
			$result = $result && false;
		}
	}
	$final_array = array('boolean' => $result, 'shipping' => $shipping_price, 'total' => $subtotal + $shipping_price , 'subtotal' => $subtotal);
	return $final_array;
}

// Check if order is on the way 
// If a message is already sent, then return false
function is_on_its_way($order_id, $order_parent_id, $numero_guia, $order_status){
	$child_orders = get_children(array(
		'post_parent' => $order_parent_id,
		'post_type'   => 'shop_order',
		'exclude'     => $order_id,
	));
	foreach ($child_orders as $child){
		$child_id = $child->ID;
		$child_status = $child->post_status;
		$child_status = str_replace('wc-', '', $child_status);
		$child_guia = get_post_meta($child_id, '_numero_guia', true);
		if ($child_guia === $numero_guia && $numero_guia !== ''){
			if ($child_status === $order_status){
				return false;
			}
		}
		elseif($numero_guia === ''){
			return false;
		}
	}
	return true;
}

// Send message request to api
function send_post_request_to_api($template, $parameters, $customer_phone_number) {
	$jsonString = json_encode($parameters);

    $api_url = 'https://live-server-11723.wati.io/api/v1/sendTemplateMessage?whatsappNumber=' . $customer_phone_number;

    $body = '{"parameters":' . $jsonString . ',"broadcast_name":"' . $template . '","template_name":' . '"'.$template.'"' . '}';

    $args = array(
        'body' => $body,
        'timeout' => 60,
        'headers' => array(
            'content-type' => 'text/json',
			'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiI3NGNmZjI1OS00Nzk4LTRhNTUtYjNkOS0wYjhiODEwNTgzMmIiLCJ1bmlxdWVfbmFtZSI6ImFkbWluQHJpbnRpbi5jbyIsIm5hbWVpZCI6ImFkbWluQHJpbnRpbi5jbyIsImVtYWlsIjoiYWRtaW5AcmludGluLmNvIiwiYXV0aF90aW1lIjoiMDcvMjUvMjAyMyAxNjo1ODowMyIsImRiX25hbWUiOiIxMTcyMyIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6IkFETUlOSVNUUkFUT1IiLCJleHAiOjI1MzQwMjMwMDgwMCwiaXNzIjoiQ2xhcmVfQUkiLCJhdWQiOiJDbGFyZV9BSSJ9.gFGAM_Dc5UfwDrtlruZ649XI2KlGPtE65fGM7nepO7o'
            
        ),
    );
    $response = wp_remote_post($api_url, $args);
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
		error_log('Error message: ');
		error_log($error_message);
    } else {
        $response_body = wp_remote_retrieve_body($response);
		error_log('Whatsapp notification response:');
		error_log($response_body);
    }
}
?>