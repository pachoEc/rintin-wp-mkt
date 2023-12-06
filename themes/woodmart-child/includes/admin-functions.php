<?php
/**
 * Admin panel hooks & functions */ 

// Write in stock_log everytime a direct modification of a product stock is made
add_action( 'pre_post_update', 'stock_modification_from_product_handler');
function stock_modification_from_product_handler($post_id){
	$post = get_post($post_id);
	if ($post->post_type === 'product') {
		$new_stock_value = intval($_POST['_stock']);
		$old_stock_value = intval(get_post_meta($post_id, '_stock', true));
		if ($_POST['_stock'] !== null && get_post_meta($post_id, '_stock', true) !== null && $old_stock_value !== $new_stock_value) {
			global $wpdb;
			$currentDate = new DateTime();
			$datetimeForSQL = $currentDate->format('Y-m-d H:i:s');
			$targetTimezone = new DateTimeZone('America/Chicago'); // GMT-6 or CST
			$datetimeForSQLMX = clone $currentDate; 
			$datetimeForSQLMX->setTimezone($targetTimezone);
			$datetimeForSQLMXFormated = $datetimeForSQLMX->format('Y-m-d H:i:s');
			$table_name = 'stock_log';
			$data = array(
				'product_id'  => $post_id,
				'previous_stock' => intval($old_stock_value),
				'new_stock' => intval($new_stock_value),
				'reason' => 'Modificacion directa de stock',
				'modification_date' => $datetimeForSQL,
				'modification_date_mx' => $datetimeForSQLMXFormated,
			);
			$wpdb->insert($table_name, $data);
		}
	}
}


// Get all cart products with its respective quantity and save it to a transient, used for looking up the stock changes whenever a product quantity is modified inside the cart of an order
add_action( 'load-post.php', 'order_cart_transient_setter' );
function order_cart_transient_setter() {
    $post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;
    $post = get_post( $post_id );
	$order_array = array();
	$orders_cart_optn = get_transient( 'orders_cart' );
	
	if ( isset($post) && $post->post_type === 'shop_order' ){
		$order = wc_get_order( $post_id );
		$order_items = $order->get_items();
		$total_qty = 0;
		foreach ( $order_items as $item_id => $item ) {
			$product_id = $item->get_product_id();
			$quantity = $item->get_quantity();
			$order_array[$product_id] = $quantity;
			$total_qty += $quantity;
		}
		$array_to_optn = array($post_id => $order_array);
		if(!isset($orders_cart_optn)){
			set_transient( 'orders_cart', $array_to_optn, HOUR_IN_SECONDS );
		} else {
			$orders_cart_optn[$post_id] = $order_array;
			set_transient( 'orders_cart', $orders_cart_optn, HOUR_IN_SECONDS );
		}
	}
}

// Write in stock_log everytime the stock of a product is modified inside and order and update the cart transient
add_action( 'woocommerce_saved_order_items', 'cart_stock_modification_handler', 10, 1 );
function cart_stock_modification_handler( $order_id ) {
    $order = wc_get_order( $order_id );
	$orders_cart_optn = get_transient( 'orders_cart' );
	$order_array = array();
    if ( $order ) {
        $order_items = $order->get_items();
		$total_qty = 0;
        foreach ( $order_items as $item_id => $item ) {
            $product_id = $item->get_product_id();
			$product = wc_get_product( $product_id );
			$product_name = $product->get_name();
            $quantity = $item->get_quantity();
			$order_array[$product_id] = $quantity;
			if($orders_cart_optn[$order_id][$product_id] !== null && ($orders_cart_optn[$order_id][$product_id] !== $quantity)){
				error_log('entro');
				$product_stock = intval(get_post_meta($product_id, '_stock', true));
				$difference = $orders_cart_optn[$order_id][$product_id] - $quantity;
				update_post_meta($product_id, '_stock', strval($product_stock + $difference));
				global $wpdb;
				$currentDate = new DateTime();
				$datetimeForSQL = $currentDate->format('Y-m-d H:i:s');
				$targetTimezone = new DateTimeZone('America/Chicago'); // GMT-6 or CST
				$datetimeForSQLMX = clone $currentDate; 
				$datetimeForSQLMX->setTimezone($targetTimezone);
				$datetimeForSQLMXFormated = $datetimeForSQLMX->format('Y-m-d H:i:s');
				$table_name = 'stock_log';
				$data = array(
					'product_id'  => $product_id,
					'previous_stock' => $product_stock,
					'new_stock' => intval($product_stock + $difference),
					'reason' => 'Stock modificada desde el carrito de la orden',
					'modification_date' => $datetimeForSQL,
					'order_id' => intval($order_id),
					'modification_date_mx' => $datetimeForSQLMXFormated,
				);
				$wpdb->insert($table_name, $data);
				$message = "Stock modificada para el producto $product_name " . strval($product_stock) . "->" . strval($product_stock + $difference);
	$order->add_order_note( $message );
			}
        }
		$orders_cart_optn[$order_id] = $order_array;
		set_transient( 'orders_cart', $orders_cart_optn, HOUR_IN_SECONDS );
    }
}

// Write in stock_log whenever a product is deleted from the cart
add_action( 'woocommerce_before_delete_order_item', 'product_deletion_from_order_handler', 10, 2 );
function product_deletion_from_order_handler( $item_id ) {
    $order = wc_get_order( wc_get_order_id_by_order_item_id( $item_id ) );
	if ( $order ) {
        $order_item = $order->get_item( $item_id );
        if ( $order_item ) {
            $product_id = $order_item->get_product_id();
            $quantity = $order_item->get_quantity();
			$product_stock = intval(get_post_meta($product_id, '_stock', true));
			global $wpdb;
			$currentDate = new DateTime();
			$datetimeForSQL = $currentDate->format('Y-m-d H:i:s');
			$targetTimezone = new DateTimeZone('America/Chicago'); // GMT-6 or CST
			$datetimeForSQLMX = clone $currentDate; 
			$datetimeForSQLMX->setTimezone($targetTimezone);
			$datetimeForSQLMXFormated = $datetimeForSQLMX->format('Y-m-d H:i:s');
			$table_name = 'stock_log';
			$data = array(
				'product_id'  => $product_id,
				'previous_stock' => intval($product_stock - $quantity),
				'new_stock' => intval($product_stock),
				'reason' => 'Producto eliminado de la orden',
				'modification_date' => $datetimeForSQL,
				'order_id' => intval($order->get_id()),
				'modification_date_mx' => $datetimeForSQLMXFormated,
			);
			$wpdb->insert($table_name, $data);
        }
    }
}

// Modifies the stock of a product whenever is added directly to an order
// Updates the cart transient since is updated
add_action( 'woocommerce_ajax_add_order_item_meta', 'wp_kama_woocommerce_ajax_add_order_item_meta_action', 10, 3 );

function wp_kama_woocommerce_ajax_add_order_item_meta_action( $item_id, $item, $order ){
	$orders_cart_optn = get_transient( 'orders_cart' );
	$order_array = array();
	
	$product_id = $item->get_product_id();
	$product = wc_get_product( $product_id );
	$product_name = $product->get_name();
	$product_stock = intval(get_post_meta($product_id, '_stock', true));
	$quantity = $item->get_quantity();
	$order = wc_get_order( wc_get_order_id_by_order_item_id( $item_id ) );
	update_post_meta($product_id, '_stock', strval($product_stock - $quantity));
	
	$order_array[$product_id] = $quantity;
	$orders_cart_optn[$order->get_id()] = $order_array;
	set_transient( 'orders_cart', $orders_cart_optn, HOUR_IN_SECONDS );
	
	global $wpdb;
	$currentDate = new DateTime();
	$datetimeForSQL = $currentDate->format('Y-m-d H:i:s');
	$targetTimezone = new DateTimeZone('America/Chicago'); // GMT-6 or CST
	$datetimeForSQLMX = clone $currentDate; 
	$datetimeForSQLMX->setTimezone($targetTimezone);
	$datetimeForSQLMXFormated = $datetimeForSQLMX->format('Y-m-d H:i:s');
	$table_name = 'stock_log';
	$message = "Stock modificada para el producto $product_name ($product_stock) ->" . strval($product_stock - $quantity);
	$order->add_order_note( $message );
	$data = array(
		'product_id'  => $product_id,
		'previous_stock' => intval($product_stock),
		'new_stock' => intval($product_stock - $quantity),
		'reason' => 'Producto agregado a la orden',
		'modification_date' => $datetimeForSQL,
		'order_id' => intval($order->get_id()),
		'modification_date_mx' => $datetimeForSQLMXFormated,
	);
	$wpdb->insert($table_name, $data);
}

// Added order metadata fields for internal operation purposes
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'add_custom_order_field' );
function add_custom_order_field( $order ) {
	// Get the order ID
    $order_id = $order->get_id();

    // Get the order object
    $order = wc_get_order($order_id);

    // Get all items in the cart
    $items = $order->get_items();
	$list_string = '';
    // Output the cart items
    if ($items) {
        $list_string.= '<h3>Productos del Carrito:</h3>';
        $list_string.= '<ul>';
        foreach ($items as $item_id => $item) {
            $product = $item->get_product();
            $list_string.= '<li>SKU: ' . $product->get_sku() . ' (' . $product->get_name() .  ') Cantidad: ' . $item->get_quantity() . '</li>';
        }
        $list_string.= '</ul>';
    }
    $modified_date = current_time('mysql', 1); // Get the current date and time in MySQL format
    $modified_date = date('Y-m-d H:i:s', strtotime('-15 days', strtotime($modified_date))); // Subtract 15 days

    global $wpdb;
	$results = $wpdb->get_results(
		"SELECT id FROM {$wpdb->prefix}posts where post_parent = 0 and post_type = 'shop_order' and post_date > '$modified_date'"
	);
	$options = '';
	foreach($results as $result) {
		$values = $result->id;
		$options .= '<option value="' . $values .  '">' . $values . '</option>';
	}
    echo '<div id="custom_data_column">';
    woocommerce_wp_text_input( array(
        'id'          => '_numero_guia',
        'label'       => 'Número de Guia Cliente',
        'value'       => get_post_meta( $order->get_id(), '_numero_guia', true ),
        'wrapper_class' => 'form-field-wide',
    ) );
	woocommerce_wp_select( array(
        'id'          => '_logis_op',
        'label'       => 'Operador Logístico Cliente',
        'value'       => get_post_meta( $order->get_id(), '_logis_op', true ),
        'options'     => array(
			'none'		=> 'Elije un operador',
            'estafeta' => 'Estafeta',
            '99 minutos' => '99 Minutos',
            'ogramak' => 'Ogramak',
			'redpack' => 'Redpack',
			'fedex' => 'FEDEX',
        ),
        'wrapper_class' => 'form-field-wide',
    ) );
	woocommerce_wp_text_input( array(
        'id'          => '_numero_guia_interno',
        'label'       => 'Número de Guia Interno',
        'value'       => get_post_meta( $order->get_id(), '_numero_guia_interno', true ),
        'wrapper_class' => 'form-field-wide',
    ) );
	woocommerce_wp_select( array(
        'id'          => '_logis_op_interno',
        'label'       => 'Operador Logístico Interno',
        'value'       => get_post_meta( $order->get_id(), '_logis_op_interno', true ),
        'options'     => array(
			'none'		=> 'Elije un operador',
            'estafeta' => 'Estafeta',
            '99 minutos' => '99 Minutos',
            'ogramak' => 'Ogramak',
			'fedex' => 'FEDEX',
			'redpack' => 'Redpack',
			'chavobus' => 'Chavobus',
			'ogramak_camion' => 'Ogramak Camion',
        ),
        'wrapper_class' => 'form-field-wide',
    ) );
	woocommerce_wp_select( array(
        'id'          => '_metodo_pago_interno',
        'label'       => 'Metodo de Pago Interno',
        'value'       => get_post_meta( $order->get_id(), '_metodo_pago_interno', true ),
        'options'     => array(
			'none'		=> 'Elije un metodo de pago',
            'tarjeta' => 'Tarjeta de Credito o Debito',
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia Bancaria / SPEI',
        ),
        'wrapper_class' => 'form-field-wide',
    ) );
	woocommerce_wp_select( array(
        'id'          => '_bodega_devolucion',
        'label'       => 'Bodega para devolución',
        'value'       => get_post_meta( $order->get_id(), '_bodega_devolucion', true ),
        'options'     => array(
			'none'		=> 'Elije una bodega',
            '2166' => 'Ciudad de México',
            '2705' => 'Oaxaca',
        ),
        'wrapper_class' => 'form-field-wide',
    ) );
	woocommerce_wp_select( array(
        'id'          => '_tipo_devolucion',
        'label'       => 'Tipo de devolución',
        'value'       => get_post_meta( $order->get_id(), '_tipo_devolucion', true ),
        'options'     => array(
			'none'		=> 'Elije un tipo',
            'demora' => 'Demora',
            'product_distinto' => 'Producto distinto',
			'product_fallado' => 'Producto fallado',
			'falta_respuesta' => 'Falta de respuesta',
			'falta_dinero' => 'Falta de dinero',
			'cliente_no_le_gusto_pedido' => 'Cliente no le gusto el pedido',
        ),
        'wrapper_class' => 'form-field-wide',
    ) );
	woocommerce_wp_select( array(
        'id'          => '_lugar_compra',
        'label'       => 'Lugar de Compra',
        'value'       => get_post_meta( $order->get_id(), '_lugar_compra', true ),
        'options'     => array(
			'none'		=> 'Elije un lugar',
            'showroom' => 'Showroom Rintin',
            'cdmx_bodega_rintin' => 'Bodega Rintin CDMX',
			'oax_bodega_rintin' => 'Pick Up Oaxaca',
        ),
        'wrapper_class' => 'form-field-wide',
    ) );
    echo '<p class="form-field-wide form-field"><label for="_order_parent">Orden Padre</label>
    <input type="text" list="_orden_padre" name="_orden_padre" value="' . strval(wp_get_post_parent_id($order->get_id())) . '" placeholder="Número de orden padre">
    <datalist id="_orden_padre" class="select2-selection">';
    echo $options;
    echo '</datalist></p>';
	echo '<p class="form-field-wide form-field"><a class="button button-secondary" id="return-sku-btn">Agregar SKUs para devolución.</a></p>';
	echo ' <div id="modalWrapper" style="display:none;">
    <div id="modalContent">
      <h2>Agrega los SKUs que van a ser regresados</h2> ' . $list_string .
	  
	  '<div id="skus-container"></div>
	  <button id="add-sku">Agregar otro SKU</button>
	 	<input id="skus-united" name="skus-united" style="display:none;"/>
      <button id="closeModalBtn">Confirmar</button>
    </div>
  </div>
';
    echo '</div>';
}

// Update order metadata fields in database
// In case the add sku for return, the product is added to its respective warehouse stock
add_action( 'woocommerce_process_shop_order_meta', 'save_custom_order_field', 10, 2 );
function save_custom_order_field( $order_id ) {
    if ( isset( $_POST['_tipo_devolucion'] ) ) {
        $custom_field_value = sanitize_text_field( $_POST['_tipo_devolucion'] );
        update_post_meta( $order_id, '_tipo_devolucion', $custom_field_value );
    }
	if ( isset( $_POST['_orden_padre'] ) ) {
		$post_data = array(
			'ID' => $order_id,
			'post_parent' => intval($_POST['_orden_padre']),
		);
		wp_update_post($post_data);
	}
	
	if ( isset( $_POST['_numero_guia'] ) ) {
        $custom_field_value = sanitize_text_field( $_POST['_numero_guia'] );
        update_post_meta( $order_id, '_numero_guia', $custom_field_value );
    }
	if ( isset( $_POST['_lugar_compra'] ) ) {
        $custom_field_value = sanitize_text_field( $_POST['_lugar_compra'] );
        update_post_meta( $order_id, '_lugar_compra', $custom_field_value );
    }
	if ( isset( $_POST['_logis_op'] ) ) {
        $custom_field_value = sanitize_text_field( $_POST['_logis_op'] );
        update_post_meta( $order_id, '_logis_op', $custom_field_value );
    }
	if ( isset( $_POST['_numero_guia_interno'] ) ) {
        $custom_field_value = sanitize_text_field( $_POST['_numero_guia_interno'] );
        update_post_meta( $order_id, '_numero_guia_interno', $custom_field_value );
    }
	if ( isset( $_POST['_logis_op_interno'] ) ) {
        $custom_field_value = sanitize_text_field( $_POST['_logis_op_interno'] );
        update_post_meta( $order_id, '_logis_op_interno', $custom_field_value );
    }
	if ( isset( $_POST['_metodo_pago_interno'] ) ) {
        $custom_field_value = sanitize_text_field( $_POST['_metodo_pago_interno'] );
        update_post_meta( $order_id, '_metodo_pago_interno', $custom_field_value );
    }
	if ( isset( $_POST['_bodega_devolucion'] ) ) {
        $custom_field_value = sanitize_text_field( $_POST['_bodega_devolucion'] );
        update_post_meta( $order_id, '_bodega_devolucion', $custom_field_value );
    }
	if ( isset( $_POST['skus-united'] ) ) {
		global $wpdb;
		$currentDate = new DateTime();
		$datetimeForSQL = $currentDate->format('Y-m-d H:i:s');
		$targetTimezone = new DateTimeZone('America/Chicago'); // GMT-6 or CST
		$datetimeForSQLMX = clone $currentDate; 
		$datetimeForSQLMX->setTimezone($targetTimezone);
		$datetimeForSQLMXFormated = $datetimeForSQLMX->format('Y-m-d H:i:s');
		$table_name = 'stock_log';
		$skus_array = json_decode(stripslashes( $_POST['skus-united'] ), true);
		$bodega_int = intval($_POST['_bodega_devolucion']);
		$message = $_POST['_bodega_devolucion'] === '2166' ? 'Orden devuelta a Bodega CDMX con los siguientes productos: ' : 'Orden devuelta a Bodega Oaxaca con los siguientes productos: ';
		
		foreach ( $skus_array as $sku => $quantity ) {
			$product_id = wc_get_product_id_by_sku( $sku );
			$product = wc_get_product( $product_id );
			if($product && !$product->is_type('variable')){
				$price = get_post_meta($product_id, '_regular_price', true); 
				$sale_price = $product->get_sale_price();
				$material = get_post_meta($product_id, '_product_material', true);
				$colors = get_post_meta($product_id, '_colors', true);
				$origin = get_post_meta($product_id, '_origin', true);
				$units = get_post_meta($product_id, '_units_per_pack', true);
				$last_sku = get_post_meta($product_id, '_sku', true);
				$cost_of_goods = get_post_meta($product_id, '_cost_of_goods', true);
				$new_sku = ($_POST['_bodega_devolucion'] === '2166' ? 'RYM' : 'OAX') . $last_sku;
				$sku_list = get_seller_skus($bodega_int);
				$query = "
					SELECT id
					FROM $wpdb->posts
					LEFT JOIN $wpdb->postmeta 
					ON ( ID = post_id )
					WHERE post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
					AND meta_key = '_sku' AND meta_value = '$new_sku'
				";
				$results = $wpdb->get_col($query);
				if(array_key_exists($new_sku, $sku_list)){
						$stock = intval(get_post_meta($sku_list[$new_sku], '_stock' ,true));
						$newstock = $stock + intval($quantity);
						update_post_meta($sku_list[$new_sku], '_stock', $newstock);
						$message .= get_the_title($product_id) . ' (' . strval($quantity) . '), ';
						$data = array(
							'product_id'  => intval($results[0]),
							'previous_stock' => $stock,
							'new_stock' => $newstock,
							'reason' => 'Incremento de stock para bodega por devolucion',
							'modification_date' => $datetimeForSQL,
							'modification_date_mx' => $datetimeForSQLMXFormated,
							'order_id' => $order_id,
						);
						$wpdb->insert($table_name, $data);
				} else{
					$description = $product->get_description();
					$observations = get_post_meta($product_id, '_observations', true);
					$taxonomy = 'product_cat';
					$product_categories = wp_get_post_terms($product_id, $taxonomy);
					$category_ids = array();
					foreach ($product_categories as $category) {
						$category_id = $category->term_id;
						$category_ids[] = intval($category_id);
					}
					$taxonomy = 'product_tag';
					$product_tags = wp_get_post_terms($product_id, $taxonomy);
					$tag_ids = array();
					foreach ($product_tags as $tag) {
						$tag_id = $tag->term_id;
						$tag_ids[] = intval($tag_id);
					}
					$image_url = get_the_post_thumbnail_url($product_id, 'full');
					$image_id = attachment_url_to_postid($image_url);
					$product_data = array(
						'post_author' => $bodega_int, // Set the post author ID
						'post_type' => 'product',
						'post_title' => get_the_title($product_id), // Replace with the desired product title
						'post_status' => 'publish',
						'post_excerpt' => get_the_excerpt($product_id),
					);
					$new_product_id = wp_insert_post($product_data);
					$new_product = wc_get_product($product_id);
					update_post_meta($new_product_id, '_regular_price' ,$price);
					update_post_meta($new_product_id, '_price' ,$price);
					update_post_meta($new_product_id, '_sale_price' ,strval($sale_price));
					update_post_meta($new_product_id, 'Description' ,$description);
					update_post_meta($new_product_id, '_manage_stock' ,'yes');
					update_post_meta($new_product_id, '_product_material', $material);
					update_post_meta($new_product_id, '_colors', $colors);
					update_post_meta($new_product_id, '_origin', $origin);
					update_post_meta($new_product_id, '_units_per_pack', $units);
					update_post_meta($new_product_id, '_observations', $observations);
					update_post_meta( $new_product_id, '_sku', $new_sku );
					update_post_meta($new_product_id, '_cost_of_goods', $cost_of_goods);
					wp_set_object_terms($new_product_id, $category_ids, 'product_cat');
					wp_set_object_terms($new_product_id, $tag_ids, 'product_tag');
					update_post_meta($new_product_id, '_stock' ,strval($quantity));
					set_post_thumbnail($new_product_id, $image_id);
					$message .= get_the_title($product_id) . ' (' . strval($quantity) . '), ';
					$data = array(
						'product_id'  => intval($new_product_id),
						'previous_stock' => 0,
						'new_stock' => intval($quantity),
						'reason' => 'Creacion de producto para bodega por devolucion',
						'modification_date' => $datetimeForSQL,
						'modification_date_mx' => $datetimeForSQLMXFormated,
						'order_id' => $order_id,
					);
					$wpdb->insert($table_name, $data);
			}
		}
		$order = new WC_Order( $order_id );
		$order->add_order_note( $message );
        $custom_field_value = sanitize_text_field( $_POST['skus-united'] );
        update_post_meta( $order_id, '_skus_devueltos', $custom_field_value );
    }
	}
}

// Product metadata fields
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');
function woocommerce_product_custom_fields()
{
	global $woocommerce, $post;
	echo '<div class="product_custom_field">';
	// Custom Product Text Field
	woocommerce_wp_text_input(
		array(
			'id' => '_product_material',
			'placeholder' => 'Material del Producto',
			'label' => __('Material del Producto', 'woocommerce'),
			'desc_tip' => 'true'
		)
	);
		
	//Custom Product Number Field
	woocommerce_wp_text_input(
		array(
			'id' => '_colors',
			'placeholder' => 'Color(es)',
			'label' => __('Color(es)', 'woocommerce'),
			'desc_tip' => 'true'
		)
	);
	//Custom Product Number Field
	woocommerce_wp_text_input(
		array(
			'id' => '_origin',
			'placeholder' => 'Origen del Producto',
			'label' => __('Origen del Producto', 'woocommerce'),
			'desc_tip' => 'true'
		)
	);

	// Custom Product Text Field
	woocommerce_wp_text_input(
		array(
			'id' => '_product_sizes',
			'placeholder' => 'Tallas del producto',
			'label' => __('Tallas Del Producto', 'woocommerce'),
			'desc_tip' => 'true'
		)
	);
	// Custom Product Text Field
	woocommerce_wp_text_input(
		array(
			'id' => '_units_per_pack',
			'placeholder' => 'Unidades por Paquete',
			'label' => __('Unidades por Paquete', 'woocommerce'),
			'desc_tip' => 'true'
		)
	);
	woocommerce_wp_textarea_input( array(
        'id'          => '_observations',
        'label'       => __( 'Observaciones', 'woocommerce' ),
        'desc_tip'    => true,
        'description' => __( 'Observaciones', 'woocommerce' ),
    ));
	echo '</div>';
}

//Update product metadata fields in database
add_action('woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save');
function woocommerce_product_custom_fields_save($post_id)
{

	$woocommerce_product_observations = $_POST['_observations'];
	if (!empty($woocommerce_product_observations))
		update_post_meta($post_id, '_observations', esc_attr($woocommerce_product_observations));
	
	$woocommerce_product_material_field = $_POST['_product_material'];
	if (!empty($woocommerce_product_material_field))
		update_post_meta($post_id, '_product_material', esc_attr($woocommerce_product_material_field));

	$woocommerce_product_colors = $_POST['_colors'];
	if (!empty($woocommerce_product_colors))
		update_post_meta($post_id, '_colors', esc_attr($woocommerce_product_colors));

	$woocommerce_product_sizes_field = $_POST['_product_sizes'];
	if (!empty($woocommerce_product_sizes_field))
		update_post_meta($post_id, '_product_sizes', esc_attr($woocommerce_product_sizes_field));
	
	$woocommerce_pack_content_field = $_POST['_units_per_pack'];
	if (!empty($woocommerce_pack_content_field))
		update_post_meta($post_id, '_units_per_pack', esc_attr($woocommerce_pack_content_field));
	
	$woocommerce_product_origin = $_POST['_origin'];
	if (!empty($woocommerce_product_origin))
		update_post_meta($post_id, '_origin', esc_attr($woocommerce_product_origin));
}

?>