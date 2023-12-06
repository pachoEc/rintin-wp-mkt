<?php
function create_variable_product($product, $product_id, $variation_id, $quantity, $bodega, $message, $order_id) {
    $price = get_post_meta($variation_id, '_regular_price', true);
	$sale_price = $product->get_sale_price();
	$material = get_post_meta($product_id, '_product_material', true);
	$colors = get_post_meta($product_id, '_colors', true);
	$origin = get_post_meta($product_id, '_origin', true);
	$units = get_post_meta($product_id, '_units_per_pack', true);
	$last_sku = get_post_meta($product_id, '_sku', true);
	$var_last_sku = get_post_meta($variation_id, '_sku', true);
	$cost_of_goods = get_post_meta($product_id, '_cost_of_goods', true);
	global $wpdb;
	$sku = ($bodega === '2166' ? 'RYM' : 'OAX') . $last_sku;
	$var_sku = ($bodega === '2166' ? 'RYM' : 'OAX') . $var_last_sku;
	$sku_list = get_seller_skus(intval($bodega));
	$query = "
		SELECT id
		FROM $wpdb->posts
		LEFT JOIN $wpdb->postmeta 
		ON ( ID = post_id )
		WHERE post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
		AND meta_key = '_sku' AND meta_value = '$var_sku'
	";
	$results = $wpdb->get_col($query);
	$currentDate = new DateTime();
	$datetimeForSQL = $currentDate->format('Y-m-d H:i:s');
	$targetTimezone = new DateTimeZone('America/Chicago'); // GMT-6 or CST
	$datetimeForSQLMX = clone $currentDate; 
	$datetimeForSQLMX->setTimezone($targetTimezone);
	$datetimeForSQLMXFormated = $datetimeForSQLMX->format('Y-m-d H:i:s');
	$table_name = 'stock_log';
	if(count($results) > 0){
		$current_stock = get_post_meta($results[0], '_stock', true);
		update_post_meta($results[0], '_stock' ,strval($current_stock + $quantity));
		$data = array(
			'product_id'  => intval($results[0]),
			'previous_stock' => intval($current_stock),
			'new_stock' => intval($quantity + $current_stock),
			'reason' => 'Incremento de stock para bodega por devolucion',
			'modification_date' => $datetimeForSQL,
			'modification_date_mx' => $datetimeForSQLMXFormated,
			'order_id' => $order_id,
		);
		$wpdb->insert($table_name, $data);
		$message .= get_the_title($results[0]) . ' (' . strval($quantity) . '), ';
	} else{
		if(array_key_exists($sku, $sku_list)){
			$message .= "El producto padre de la variación " . strval($var_last_sku) . "ya existe. Es necesario agregar la variación y su stock correspondiente de manera manual. ";
		} else {
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
				'post_author' => intval($bodega), // Set the post author ID
				'post_type' => 'product',
				'post_title' => get_the_title($product_id), // Replace with the desired product title
				'post_status' => 'publish',
				'post_excerpt' => get_the_excerpt($product_id),
			);
			
			$query = "
					SELECT DISTINCT meta_key
					FROM $wpdb->postmeta
					WHERE meta_key LIKE '%attribute%' and post_id = $variation_id
				";
			$results = $wpdb->get_col($query);
			$var_attribute = get_post_meta($variation_id, $results[0], true);
			$new_variable_product = new WC_Product_Variable();
			$new_variable_product->set_name(get_the_title($product_id));
			$new_variable_product->set_status('publish');

			// Set product attributes
			$attributes = array();

			// add the format attribute
			$attribute = new WC_Product_Attribute();
			$attribute->set_id( wc_attribute_taxonomy_id_by_name(str_replace("attribute_","",$results[0])));
			$attribute->set_name( str_replace("attribute_","",$results[0]));
			// see if current format attribute exists and set as var
			$term = get_term_by('name', $var_attribute, str_replace("attribute_","",$results[0]));
			if(!$term){
				// create new pa_format term 
				wp_insert_term(
					$var_attribute, // the term 
					str_replace("attribute_","",$results[0]) // the taxonomy
				);
				$term = get_term_by('name', $var_attribute, str_replace("attribute_","",$results[0]));
			}
			$attribute->set_options( array( $term->term_id ) );
			$attribute->set_position( 0 );
			$attribute->set_visible( true );
			$attribute->set_variation( true );
			$attribute->is_taxonomy( true );
			$attributes[] = $attribute;
			$new_variable_product->set_attributes( $attributes );
			$new_product_id = $new_variable_product->save();

			$variation = new WC_Product_Variation();
			$variation->set_regular_price($price);
			$variation->set_sku( $var_sku );
			$variation->set_manage_stock( true ); 
			$variation->set_stock_quantity( intval($quantity) );
			$variation->set_parent_id($new_product_id);
			$variation->set_attributes(array(
				str_replace("attribute_","",$results[0]) => $term->slug
			));
			$variation->save();
			$new_variable_product->save();
			$arg = array(
				'ID' => $new_product_id,
				'post_author' => intval($bodega),
				'post_excerpt' => get_the_excerpt($product_id),
			);
			wp_update_post( $arg );
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
			update_post_meta($new_product_id, '_cost_of_goods', $cost_of_goods);
			update_post_meta( $new_product_id, '_sku', $sku );
			wp_set_object_terms($new_product_id, $category_ids, 'product_cat');
			wp_set_object_terms($new_product_id, $tag_ids, 'product_tag');
			update_post_meta($new_product_id, '_stock' ,strval($quantity));
			set_post_thumbnail($new_product_id, $image_id);
			$data = array(
				'product_id'  => $new_product_id,
				'previous_stock' => 0,
				'new_stock' => intval($quantity),
				'reason' => 'Creacion de variación para bodega por devolucion',
				'modification_date' => $datetimeForSQL,
				'modification_date_mx' => $datetimeForSQLMXFormated,
				'order_id' => $order_id,
			);
			$wpdb->insert($table_name, $data);
			$message .= get_the_title($variation_id) . ' (' . strval($quantity) . '), ';
		}
		}
    return $message;
}

function add_product_return($product, $product_id, $quantity, $bodega, $message, $order_id){
	global $wpdb;
	$price = get_post_meta($product_id, '_regular_price', true);
	$sale_price = get_post_meta($product_id, '_sale_price', true);
	$material = get_post_meta($product_id, '_product_material', true);
	$colors = get_post_meta($product_id, '_colors', true);
	$origin = get_post_meta($product_id, '_origin', true);
	$units = get_post_meta($product_id, '_units_per_pack', true);
	$last_sku = get_post_meta($product_id, '_sku', true);
	$cost_of_goods = get_post_meta($product_id, '_cost_of_goods', true);
	$sku = ($bodega === '2166' ? 'RYM' : 'OAX') . $last_sku;
	$sku_list = get_seller_skus(intval($bodega));
	error_log('Entro a la funcion add_product_return');
	$query = "
		SELECT id
		FROM $wpdb->posts
		LEFT JOIN $wpdb->postmeta 
		ON ( ID = post_id )
		WHERE post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
		AND meta_key = '_sku' AND meta_value = '$sku'
	";
	$results = $wpdb->get_col($query);
	$currentDate = new DateTime();
	$datetimeForSQL = $currentDate->format('Y-m-d H:i:s');
	$targetTimezone = new DateTimeZone('America/Chicago'); // GMT-6 or CST
	$datetimeForSQLMX = clone $currentDate; 
	$datetimeForSQLMX->setTimezone($targetTimezone);
	$datetimeForSQLMXFormated = $datetimeForSQLMX->format('Y-m-d H:i:s');
	$table_name = 'stock_log';
	if(array_key_exists($sku, $sku_list)){
		$stock = intval(get_post_meta($sku_list[$sku], '_stock' ,true));
		$newstock = $stock + intval($quantity);
		update_post_meta($sku_list[$sku], '_stock', $newstock);
		$message .= get_the_title($product_id) . ' (' . strval($quantity) . '), ';
		$data = array(
			'product_id'  => intval($results[0]),
			'previous_stock' => intval($stock),
			'new_stock' => intval($newstock),
			'reason' => 'Incremento de stock para bodega por devolucion',
			'modification_date' => $datetimeForSQL,
			'modification_date_mx' => $datetimeForSQLMXFormated,
			'order_id' => $order_id,
		);
		$wpdb->insert($table_name, $data);
		return $message;
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
			'post_author' => intval($bodega), // Set the post author ID
			'post_type' => 'product',
			'post_title' => get_the_title($product_id), // Replace with the desired product title
			'post_status' => 'publish',
			'post_excerpt' => get_the_excerpt($product_id),
		);
		$new_product_id = wp_insert_post($product_data);
		$new_product = wc_get_product($product_id);
		update_post_meta($new_product_id, '_regular_price' ,$price);
		if($sale_price && isset($sale_price) && $sale_price !== '0'){
			update_post_meta($new_product_id, '_price' ,$sale_price);
		} else {
			update_post_meta($new_product_id, '_price' ,$price);
		}
		
		update_post_meta($new_product_id, '_sale_price' ,$sale_price);
		update_post_meta($new_product_id, 'Description' ,$description);
		update_post_meta($new_product_id, '_manage_stock' ,'yes');
		update_post_meta($new_product_id, '_product_material', $material);
		update_post_meta($new_product_id, '_colors', $colors);
		update_post_meta($new_product_id, '_origin', $origin);
		update_post_meta($new_product_id, '_units_per_pack', $units);
		update_post_meta($new_product_id, '_observations', $observations);
		update_post_meta($new_product_id, '_cost_of_goods', $cost_of_goods);
		update_post_meta( $new_product_id, '_sku', $sku );
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
		return $message;
	}
}
?>