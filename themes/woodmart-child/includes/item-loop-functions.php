<?php
/** Item loop Hooks and Functions */

// Hide Oaxaca seller products if user is not from Oaxaca
add_action( 'pre_get_posts', 'exclude_products_by_seller' );
function exclude_products_by_seller( $query ) {
    if ( ! is_admin() && $query->is_main_query() ) {
		$user_id = get_current_user_id();
		$post_code = get_user_meta($user_id, 'billing_postcode', true);
		$array_cp = ['68070', '68200', '71330', '71294', '68288', '71588', '71355', '70484', '71256', '71550', '70442', '71516', '68026', '70450', '71586', '71313', '68290', '71334', '68235', '71580', '70426', '71235', '71408', '68207', '68125', '71220', '68237', '68254', '68204', '71268', '68043', '71207', '68067', '68140', '70400', '71249', '71210', '71508', '70496', '70411', '71526', '71569', '68133', '71202', '70423', '71333', '71224', '71205', '68230', '70445', '68293', '71283', '68090', '68218', '68287', '68259', '70480', '70497', '68120', '71574', '71242', '71400', '71233', '68149', '71363', '71505', '71520', '71203', '71575', '70461', '70437', '68146', '71354', '68213', '70474', '68250', '70434', '68214', '68269', '71232', '71529', '71552', '71578', '68277', '68210', '71316', '71343', '71545', '68013', '68216', '68143', '68157', '71352', '68130', '71266', '71267', '70410', '71507', '71240', '68273', '68276', '68016', '71246', '68266', '71248', '68028', '68233', '70456', '70412', '71273', '71323', '71548', '71568', '71292', '71340', '68068', '71502', '70436', '68126', '71317', '70435', '71218', '71404', '68268', '71228', '71406', '68075', '71215', '71409', '68208', '68261', '71253', '68060', '71295', '68025', '70430', '71260', '68267', '68284', '70440', '68024', '68220', '71204', '68023', '71290', '68104', '70404', '70406', '71525', '68222', '71284', '71557', '68217', '71227', '71270', '70453', '70495', '68264', '68278', '68205', '68224', '71315', '68103', '70420', '71226', '71244', '71320', '71338', '71403', '68257', '68280', '71350', '70498', '68020', '68045', '71359', '68248', '68000', '68144', '70477', '68156', '68010', '68299', '70482', '71597', '71214', '71300', '68227', '68034', '68134', '71500', '71514', '68240', '71405', '68110', '71528', '68256', '71576', '70439', '71577', '68228', '70403', '71314', '71543', '70460', '68258', '68236', '68033', '71517', '71200', '70467', '68027', '71515', '68260', '68285', '68275', '68128', '70478', '71247', '71230', '68100', '71223', '71567', '71324', '71523', '71243', '71310', '71554', '71255', '68080', '71590', '71318', '68018', '70428', '71506', '70464', '71518', '71297', '68155', '68234', '68040', '68030', '71534', '68232', '71280', '71250', '68219', '70408', '68274', '71336', '68148', '68159', '68083', '70417', '68247', '71254', '71560', '70405', '68115', '71265', '68127', '71565', '68050', '71213', '68039', '68153', '71512', '68150', '71357', '68154', '71360', '71245', '71337', '70458', '71530', '71504', '71553', '68044', '68270', '68226', '68262', '70479', '71217', '71573', '71231', '71286', '71595', '71364', '70407', '68263', '68283', '68244', '71510', '68297', '71537', '71274', '71562', '71566', '71570', '68238', '70424', '71222', '70805'];
		if(in_array($post_code, $array_cp)){
			return;
		} else {
			$excluded_seller_id = 2705;
			$excluded_seller_ids = array( $excluded_seller_id );
			$query->set( 'author__not_in', $excluded_seller_ids );
		}
    }
}

// Display product attributes in loop
add_action('woocommerce_shop_loop_item_title', 'changeProductInfoInLoop', 10 );
function changeProductInfoInLoop() {
	$product = wc_get_product();
	$product_name = get_the_title();
	$product_id = $product->get_id();
	$post = get_post( $product->get_id());
	$author = $post->post_author;
	$user = get_user_by( 'ID', $author );
	$vendor = dokan()->vendor->get($author);
	$display_name = $user->display_name;
	$brand = get_post_meta($product->get_id(), '_brand', true);
	
	$publish_date = get_post_field('post_date', $product_id, 'raw');


	$ten_days_ago = date('Y-m-d H:i:s', strtotime('-10 days'));

	$is_new = false;
	

	if ($publish_date >= $ten_days_ago && $publish_date <= current_time('mysql')) {
		$is_new = true;
	}
	$product_tags = get_the_terms($product->get_id(), 'product_tag');
	$product_cats = get_the_terms($product->get_id(), 'product_cat');
	if ($product_tags && is_array($product_tags)) {
		$tag_names = array();

		foreach ($product_tags as $tag) {
			$tag_names[] = $tag->name;
		}
	}
	$special_label = '';
	if ($product_cats && is_array($product_cats)) {
		$cat_names = array();

		foreach ($product_cats as $cat) {
			$cat_names[] = $cat->name;
			if (preg_match("/^\d+(?: Pares| Piezas| Conjuntos| pares| piezas| conjuntos)$/", $cat->name)){
				$special_label = $cat->name;
			}
		}

	}
	
	
    echo '<h3 class="woocommerce-loop-product_title">';
	if ($is_new){
		echo '<span style="padding-left: 10px; padding-right: 10px; border-radius: 10px; background-color: green; color: white; font-size: 12px; margin: 0; margin-right: 5px;">Nuevo</span>';
	}
	echo '<a href="'.get_the_permalink().'" style="display:block; font-size: 18px;">';
	echo $product_name . '</a>';
	echo '<a href="'.get_the_permalink().'" style="display:block; font-size: 16px; font-weight: 400; color: #a5a5a5;">' . (!empty($brand) ? $brand : $vendor->get_shop_name()) . '</a>';
	echo '</h3>';
	echo '<div>';
	if(($tag_names !== null && in_array('Paquete', $tag_names)) || ($cat_names !== null && in_array('Paquete', $cat_names))){
		echo '<span style="font-size: 12px; background-color: #e0e0e0; padding-left: 10px; padding-right: 10px; border-radius: 10px; margin-right: 5px; width: fit-content; display: inline-block; color: black;">Paquete</span>';
	}
	if(($tag_names !== null && in_array('Paquete', $tag_names)) || ($cat_names !== null && in_array('Paquete', $cat_names))){
		echo '<span style="font-size: 12px; background-color: #e0e0e0; padding-left: 10px; padding-right: 10px; border-radius: 10px; margin-right: 5px; display: inline-block;">Unidad</span>';
	}
	if(strlen($special_label) > 0){
		echo '<span style="font-size: 12px; background-color: #e0e0e0; padding-left: 10px; padding-right: 10px; border-radius: 10px; margin-right: 5px; width: fit-content; display: inline-block; color: black;">' . $special_label . '</span>';
	}
	if($author === '2166' || $author === '2705' || $author === '1663' || $author === '2636' || $author === '3731' || $author === '2751' || $author === '3465'){
		echo '<span style="font-size: 12px; background-color: #86379F; padding-left: 10px; padding-right: 10px; border-radius: 10px; margin-right: 5px; display: inline-block; width: fit-content; color: white;">Envío Inmediato </span>';
	}
	echo '</div>';
	
	
}

// Display product price in loop
add_filter('woocommerce_get_price_suffix', 'customer_price_suffix', 10, 2);
function customer_price_suffix($suffix, $product){
	$product_id = $product->get_id();
	$regular_price = get_post_meta($product_id, '_regular_price', true);
	$discount_price = $product->get_sale_price();
	$pack_content = get_post_meta($product_id, '_units_per_pack', true);
	$matches = array();
	preg_match("/\d+/", $pack_content, $matches);
	if (count($matches) > 0){
		$units_int = $matches[0];
	}
	else {
		$units_int = 0;
	}
	
	if (is_numeric($units_int) && is_numeric($discount_price) && $units_int != 0){
		$unit_price_disc = number_format($discount_price / $units_int, 2);
		return '<span style="display:block;">Precio Unitario: $'. $unit_price_disc . '</span>';
	}
	elseif (is_numeric($units_int) && is_numeric($regular_price) && $units_int != 0){
		$unit_price = number_format($regular_price / $units_int, 2);
		return '<span style="display:block;">Precio Unitario: $'. $unit_price . '</span>';
	}
}

add_shortcode( 'vendor_min_amount', 'dokan_shop_min_shortcode' );
function dokan_shop_min_shortcode() {
	$seller	= get_post_field( 'post_author' );
	$author	= get_user_by( 'id', $seller );
	$store_info_in_cart = dokan_get_store_info($seller);
	$dokan_info = dokan_get_store_info($seller);
	if (array_key_exists('zone', $dokan_info)) {
		$zone = $dokan_info['zone'] ?  $dokan_info['zone'] : '';
	} else{
		$zone = '';
	}
	
	if ( !empty( $store_info_in_cart['minimum_order'] ) and $store_info_in_cart['minimum_order'] > 0 ) { ?>
	<span class="details">
		<?php printf( '<p style="color: black;">Precio mínimo de pedido para este vendedor: $%s</p>', $store_info_in_cart['minimum_order'] ); ?>
	</span>
	<?php 
    }
	elseif ( !empty( $store_info_in_cart['minimum_products'] ) and  $zone != 'centro' ) { ?>
	<span class="details">
		<?php printf( '<p style="color: black;">La cantidad mínima de productos para este vendedor: %s productos</p>',  $store_info_in_cart['minimum_products'] ); ?>
	</span>
	<?php 
    }
}

// Product metadata shortcode
add_shortcode( 'product_data', 'product_data_shortcode' );
function product_data_shortcode(){
	$post = get_the_ID();
	$price = get_post_meta($post, '_regular_price', true);
	$material = get_post_meta($post, '_product_material', true);
	$colors = get_post_meta($post, '_colors', true);
	$sizes = get_post_meta($post, '_product_sizes', true);
	$pack_content = get_post_meta($post, '_units_per_pack', true);
	$origin = get_post_meta($post, '_origin', true);
	$observations = get_post_meta($post, '_observations', true);
	$sku = get_post_meta($post, '_sku', true);
	$discount_price = get_post_meta($post, '_sale_price', true);
	$brand = get_post_meta($post, '_brand', true);
	$matches = array();
	preg_match("/\d+/", $pack_content, $matches);
	if (count($matches) > 0){
		$units_int = $matches[0];
	}
	else {
		$units_int = 0;
	}
	printf('<h2 class="elementor-heading-title elementor-size-medium">' . get_the_title($post) . '</h2>');
	echo '<br>';
	if (!empty($brand)){
		printf( '<p><span style="color: black; font-weight: bold;">Marca: </span>%s</p>',  $brand );
	}
	if( !empty($sku) ) {
		printf( '<p><span style="color: black; font-weight: bold;">SKU: </span>%s</p>',  $sku );
	}
	if( !empty($material) ) {
		printf( '<p><span style="color: black; font-weight: bold;">Material: </span>%s</p>',  $material );
	}
	if( !empty($sizes) ) {
		printf( '<p><span style="color: black; font-weight: bold;">Tallas: </span>%s</p>',  $sizes );
	}
	if( !empty($pack_content)  ) {
		printf( '<p><span style="color: black; font-weight: bold;">Unidades por paquete: </span>%s</p>',  $pack_content );
	}
	if( !empty($origin) ) {
		printf( '<p><span style="color: black; font-weight: bold;">Origen: </span>%s</p>',  $origin );
	}
	if( !empty($colors) ) {
		printf( '<p><span style="color: black; font-weight: bold;">Colores: </span>%s</p>',  $colors );
	}
	if (is_numeric($units_int) && is_numeric($discount_price) && $units_int != 0){
		$unit_price_disc = number_format($discount_price / $units_int, 2);
		printf( '<p><span style="color: black; font-weight: bold;">Precio unitario: </span>$%s</p>',  $unit_price_disc );
	}
	elseif (is_numeric($units_int) && is_numeric($price) && $units_int != 0){
		$unit_price = number_format($price / $units_int, 2);
		printf( '<p><span style="color: black; font-weight: bold;">Precio unitario: </span>$%s</p>',  $unit_price );
	}
	if( !empty($observations) ) {
		printf( '<p><span style="color: black; font-weight: bold;">Observaciones del producto: </span>%s</p>',  $observations );
	}
}
?>