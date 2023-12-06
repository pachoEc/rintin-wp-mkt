<?php
/**
 * Google Drive Integration
 *  **/

function read_google_sheet(){
	$client = new Google_Client();
    $client->setAuthConfig(__DIR__ . '/service_account_credentials.json');
    $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);

    $client->fetchAccessTokenWithAssertion();

    $access_token = $client->getAccessToken();

    $service = new Google_Service_Sheets($client);
	
	$spreadsheet_id = '1ikEcwwkcMdGR7M880GUOaTfkgdB14AtC6MTdGswnyVg';
	$range = 'OGRAMAK - CLIENTE FINAL CDMX';
	
	$response = $service->spreadsheets_values->get($spreadsheet_id, $range);
	$values = $response->getValues();
	$data_array = array();
	foreach($values as $key=>$value){
		$data_array[$value[0]] = $value[9];
	}
	return $data_array;
	
}

/**
 * 99 minutos API integration
 */
// Returns an specific order last status from 99minutos api
function get_99_data($key){
	$token_api_url = 'https://delivery.99minutos.com/api/v3/oauth/token';
	$body = array(
		'client_id' => 'b829fb20-f13a-4e10-aa16-47e0603ed0f6',
		'client_secret' => 'C7._rZnD6~suGd78UKJm3Ub_vO'
	);
	$header = array(
		'content-type' => 'application/json',
		'accept' => 'application/json'
	);
	$token_args = array(
		'headers' => $header,
		'body' => json_encode($body)
	);
	$response = wp_remote_post($token_api_url, $token_args);
	$sheets_data = read_google_sheet();
	if (is_wp_error($response)) {
		// Handle the error
		$error_message = $response->get_error_message();
		echo "Something went wrong: $error_message";
	}
	else {
		$response_code = wp_remote_retrieve_response_code($response);
		$response_body = wp_remote_retrieve_body($response);
		$json_data_token = json_decode($response_body);
		$bearer_token = $json_data_token->access_token;
		$api_url = 'https://delivery.99minutos.com/api/v3/shipments/tracking?identifier=' . $key;
		$headers = array(
			'Authorization' => 'Bearer ' . $bearer_token
		);
		$args = array(
			'headers' => $headers
		);
		$response = wp_remote_get($api_url, $args);
		if (is_wp_error($response)) {
			
			$error_message = $response->get_error_message();
		} else {
			
			$response_code = wp_remote_retrieve_response_code($response);
			$response_body = wp_remote_retrieve_body($response);
			$json_data = json_decode($response_body);
			$response_array_length = count($json_data->data->events);
			$last_update = $json_data->data->events[$response_array_length-1]->statusCode;
			return $last_update;
		}
	}
}

/** CRON jobs */

add_action('order_status_update_ogramak', 'read_google_sheet');

// Check status of order from API, change its status if needed
add_action('order_status_update_99', 'delivered_status_update_function');
function delivered_status_update_function(){
	$args = array(
        'post_type'      => 'shop_order',
        'post_status'    => array( 'wc-parcel', 'wc-paqueteria-contra' ),
        'posts_per_page' => -1,
    );
	
    $query = new WP_Query( $args );
	$sheets_data = read_google_sheet();
	
    if ( $query->have_posts() ) {
		$numero_guia_dict = array();
        while ( $query->have_posts() ) {
            $query->the_post();
            $order_id = $query->post->ID;
			$order = new WC_Order($order_id);
			$numero_guia = get_post_meta($order_id, '_numero_guia', true);
			$logis_op = get_post_meta($order_id, '_logis_op', true);
			if(array_key_exists($numero_guia, $numero_guia_dict)){
				$numero_guia_dict[$numero_guia]['suborders'][] = $order_id;
			} else{
				$numero_guia_dict[$numero_guia]['suborders'] = array($order_id);
			}
			if($logis_op !== 'none'){
				$numero_guia_dict[$numero_guia]['operator'] = $logis_op;
			}
        }
		foreach($numero_guia_dict as $key=>$value){
			if ($value['operator'] === '99 minutos'){
				$last_update = get_99_data($key);
				if ($last_update === '4002'){
					foreach($value['suborders'] as $order_id){
						$order = new WC_Order($order_id);
						$order->update_status( 'wc-delivered' );
					}
				}
			}
			else if($value['operator'] === 'ogramak'){
				if(array_key_exists(strtoupper($key), $sheets_data)){
					if($sheets_data[strtoupper($key)] === 'ENTREGADO'){
						foreach($value['suborders'] as $order_id){
							$order = new WC_Order($order_id);
							$order->update_status( 'wc-delivered' );
						}
						
					} 
					elseif($sheets_data[strtoupper($key)] === 'DEVOLUCION TERMINADA'){
						foreach($value['suborders'] as $order_id){
							$order = new WC_Order($order_id);
							
						}
					}
				}
			}
		}
        wp_reset_postdata();
    }
}

// Change orders with pending status to pendiente-pago-1 and reduce its stock
add_action('order_status_update_pending', 'pending_status_update_function');
function pending_status_update_function(){
	$args = array(
        'post_type'      => 'shop_order',
        'post_status'    => 'wc-pending',
        'posts_per_page' => -1,
    );
	
    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $order_id = $query->post->ID;
			$order = new WC_Order($order_id);
			if (!empty($order)) {
				
				$order_date = $order->get_date_created();

				$current_datetime = new DateTime();

				$interval = $current_datetime->diff( $order_date );
				
				if ( $interval->i >= 30 || $interval->h >= 1 ) {
					$order->update_status( 'wc-pendiente-pago-1' );
					wc_reduce_stock_levels( $order_id );
				}
			}
        }
        wp_reset_postdata();
    }
}

// Change pendiente-pago to cancelled if some time has passed
add_action('order_status_update', 'cancelled_order_status_update_function');
function cancelled_order_status_update_function(){
	$args = array(
        'post_type'      => 'shop_order',
        'post_status'    => 'wc-pendiente-pago-1',
        'posts_per_page' => -1,
    );

    $query = new WP_Query( $args );
    error_log('Entro al cron order_status_update');
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $order_id = $query->post->ID;
			$order = new WC_Order($order_id);
			if (!empty($order)) {
				
				$order_date = $order->get_date_created();
				
				$current_datetime = new DateTime();

				$interval = $current_datetime->diff( $order_date );
				
				if ( $interval->d >= 2 ) {
                    error_log('Se actualizo la orden: ' . strval($order_id));
					$order->update_status( 'cancelled' );
					wc_increase_stock_levels( $order_id );
				}
			}
        }
        wp_reset_postdata();
    }
}
?>
