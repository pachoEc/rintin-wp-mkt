<?php
/** Dokan User Dashboard Hooks & Functions */
/**
 * Validation add for product cover image
 *
 * @param array $errors
 * @return array $errors 
 */
function dokan_can_add_product_validation_customized( $errors ) {
  $postdata       = wp_unslash( $_POST );
  $featured_image = absint( sanitize_text_field( $postdata['feat_image_id'] ) );
  $_regular_price = absint( sanitize_text_field( $postdata['_regular_price'] ) );
  if ( isset( $postdata['feat_image_id'] ) && empty( $featured_image ) && ! in_array( 'Please upload a product cover image' , $errors ) ) {
      $errors[] = 'Please upload a product cover image';
  }
  if ( isset( $postdata['_regular_price'] ) && empty( $_regular_price ) && ! in_array( 'Please insert product price' , $errors ) ) {
      $errors[] = 'Please insert product price';
  }
  return $errors;
}
add_filter( 'dokan_can_add_product', 'dokan_can_add_product_validation_customized', 35, 1 );
add_filter( 'dokan_can_edit_product', 'dokan_can_add_product_validation_customized', 35, 1 );
function dokan_new_product_popup_validation_customized( $errors, $data ) {
  if ( isset( $data['_regular_price'] ) && ! $data['_regular_price'] ) {
    return new WP_Error( 'no-price', __( 'Debes ingresar un precio para tu producto.', 'dokan-lite' ) );
  }
	if ( isset( $data['post_title'] ) && ! $data['post_title'] ) {
    return new WP_Error( 'no-title', __( 'Debes ingresar un nombre para tu producto.', 'dokan-lite' ) );
  }
  if ( isset( $data['feat_image_id'] ) && ! $data['feat_image_id'] ) {
    return new WP_Error( 'no-image', __( 'Debes subir al menos una foto para tu producto.', 'dokan-lite' ) );
  }
	if ( isset( $data['_product_material'] ) && ! $data['_product_material'] ) {
		return new WP_Error( 'no-material', __( 'Debes ingresar el material de tu producto.', 'dokan-lite' ) );
	}
	if ( isset( $data['_product_type'] ) && $data['_product_type'] === '-1' ) {
		return new WP_Error( 'no-type', __( 'Debes seleccionar el tipo de producto.', 'dokan-lite' ) );
	}
	if (  isset( $data['_product_type'] )&& $data['_product_type'] === 'Paquete' ){
		if ( isset( $data['_units_per_pack'] ) && (! $data['_units_per_pack'] || $data['_units_per_pack'] < 1) ) {
			return new WP_Error( 'no-units', __( 'Debes seleccionar las unidades que contienen tu paquete, no pueden ser menor a 1', 'dokan-lite' ) );
		}
	}
	if ( isset( $data['_product_unit'] ) && ! $data['_product_unit'] ) {
		return new WP_Error( 'no-unittype', __( 'Debes elejir el tipo de unidad de tu producto.', 'dokan-lite' ) );
	}
	if ( isset( $data['sizes_united'] ) && ! $data['sizes_united'] ) {
		return new WP_Error( 'no-size', __((isset( $data['_product_type'] ) && $data['_product_type'] === 'Paquete') ? 'Debes ingresar las tallas de tu producto.' : 'Debes ingresar la talla de tu producto.', 'dokan-lite' ) );
	}
	if ( isset( $data['_size_characteristics'] ) && $data['_size_characteristics'] === '-1' ) {
		return new WP_Error( 'no-characteristics', __( 'Debes ingresar la característica de tu tallaje.', 'dokan-lite' ) );
	}
	if ( isset( $data['_colors_united'] ) && !$data['_colors_united'] ) {
		return new WP_Error( 'no-color', __( (isset( $data['_product_type'] ) && $data['_product_type'] === 'Paquete') ? 'Debes ingresar los colores de tu producto.' : 'Debes ingresar el color de tu producto.', 'dokan-lite' ) );
	}
	if ( isset( $data['_stock'] ) && (! $data['_stock'] || $data['_stock'] < 1) ) {
		return new WP_Error( 'no-stock', __( 'Debes ingresar la cantidad de inventario de tu producto, no puede ser menor a 1.', 'dokan-lite' ) );
	}
	if ( isset( $data['_origin'] ) && $data['_origin'] === '-1' ) {
		return new WP_Error( 'no-origin', __( 'Debes seleccionar el origen de tu producto.', 'dokan-lite' ) );
	}
}
add_filter( 'dokan_new_product_popup_args', 'dokan_new_product_popup_validation_customized', 35, 2 );
add_action( 'dokan_new_product_after_product_tags','new_product_field',10 );

function new_product_field(){ 
	global $wpdb;
	$results = $wpdb->get_results(
		"SELECT distinct meta_value FROM {$wpdb->prefix}postmeta where meta_key = '_product_material'"
	);
	$options = '';
	foreach($results as $result) {
		$values = $result->meta_value;
		$options .= '<option value="' . $values .  '">' . $values . '</option>';
	}
	$current_user_id = get_current_user_id();
	$dokan_settings = dokan_get_store_info($current_user_id);
	if($dokan_settings['product_comission'] === 'si'){
		?>
	<div class="dokan-form-group">
		<label for="_foreign_sku">Comisión por producto (porcentaje)</label>
		<input type="number" class="dokan-form-control" name="_per_product_admin_commission" placeholder="<?php esc_attr_e( 'Ej: 12.3', 'dokan-lite' ); ?>">
	</div>
	<?php
	}
	?>
	
	<div class="dokan-form-group">
		<label for="_foreign_sku">Identificador del producto</label>
		<input type="text" class="dokan-form-control" name="_foreign_sku" placeholder="<?php esc_attr_e( 'Identificador', 'dokan-lite' ); ?>">
	</div>
	<div class="dokan-form-group">
		<label for="_stock">Cantidad de inventario del producto</label>
		<input type="number" class="dokan-form-control" name="_stock" placeholder="<?php esc_attr_e( '# de inventario', 'dokan-lite' ); ?>">
	</div>
	<div class="dokan-form-group">
		<label for="_product_type">Tipo de producto (Unidad / Paquete)</label>
		<select class="select2-selection" name="_product_type" id="_product_type">
			<option value="-1">Tipo de producto</option>
			<option value="Unidad">Producto Unitario</option>
			<option value="Paquete">Producto por paquete</option>
		</select>
	</div>
	<div class="dokan-form-group dynamic-inputs-class">
		<label for="_units_per_pack">Cantidad de unidades</label>
		<input type="number" class="dokan-form-control" name="_units_per_pack" placeholder="<?php esc_attr_e( 'Cantidad de unidades', 'dokan-lite' ); ?>">
	</div>
	<div class="dokan-form-group">
		<label for="_product_unit">Tipo de unidad</label>
		<select class="select2-selection" name="_product_unit">
			<option value="-1">Selecciona el tipo de unidad</option>
			<option value="par(es)">Par</option>
			<option value="pieza(s)">Pieza</option>
			<option value="set(s)">Conjunto</option>
		</select>
	</div>
	<div class="dokan-form-group dynamic-inputs-class">
		<p>Agregar tallas</p>
		<div id="dokan-add-product-sizes-flex-container" class="dynamic-input-dokan-add">
		</div>
		<button id="add-input-dokan-btn">Agregar otra talla</button>
		<p id="sizes-warning" style="display: none;">Debes llenar todos los campos.</p>
	</div>
	<div class="dokan-form-group props_united">
		<label for="sizes_united">Talla del producto</label>
		<input type="text" id="sizes_united" class="dokan-form-control" name="sizes_united" placeholder="<?php esc_attr_e( 'Talla de tu producto', 'dokan-lite' ); ?>">
	</div>
	<div class="dokan-form-group">
		<label for="_size_characteristics">Caracteristicas de la talla</label>
		<select class="select2-selection" name="_size_characteristics" id="_size_characteristics">
			<option value="-1">Caracteristicas</option>
			<option value="Talla normal">Talla normal</option>
			<option value="Talla amplia">Talla amplia</option>
			<option value="Talla reducida">Talla reducida</option>
			<option value="Talla puede variar">Talla puede variar</option>
		</select>
	</div>
	
	<div class="dokan-form-group dynamic-inputs-class">
		<p>Agregar colores</p>
		<div id="dokan-add-product-colors-flex-container" class="dynamic-input-dokan-add">
		</div>
		<button id="add-input-dokan-btn-colors">Agregar otro color</button>
		<p id="sizes-warning-color" style="display: none;">Debes llenar todos los campos.</p>
	</div>
	<div class="dokan-form-group props_united">
		<label for="_colors_united">Color del producto</label>
		<input type="text" id="_colors_united" class="dokan-form-control" name="_colors_united" placeholder="<?php esc_attr_e( 'Color de tu producto', 'dokan-lite' ); ?>">
	</div>
	<div class="dokan-form-group dynamic-inputs-class">
		
		<label for="is_variable_checkbox">
			<input type="checkbox" id="is_variable_checkbox" class="dokan-checkbox" name="is_variable_checkbox" value="true">
			Las cantidades pueden variar
		</label>
	</div>
	<div class="dokan-form-group">
		<label for="_product_material">Material del Producto</label>
		<input type="text" list="_product_material" class="dokan-form-control" name="_product_material" placeholder="Material del producto">
		<datalist id="_product_material" class="select2-selection">
			<?php
				echo $options;
			?>
		</datalist>
	</div>
	<div class="dokan-form-group">
		<label for="_origin">Origen</label>
		<select class="select2-selection" name="_origin">
			<option value="-1">Origen del producto</option>
			<option value="Fabricado en Mexico">Fabricado en México</option>
			<option value="Importado">Importado</option>
		</select>
	</div>
	<div class="dokan-form-group">
		<label for="_observations">Observaciones</label>
		<input type="text" class="dokan-form-control" name="_observations" placeholder="<?php esc_attr_e( 'Observaciones', 'dokan-lite' ); ?>">
	</div>
   <?php
}


add_action( 'dokan_new_product_added','save_add_product_meta', 10, 2 );
//add_action( 'dokan_product_updated', 'save_add_product_meta', 10, 2 );
function save_add_product_meta($product_id, $postdata){
    if ( ! dokan_is_user_seller( get_current_user_id() ) ) {
		return;
	}
	$current_user_id = get_current_user_id();
	$seller_name = get_user_meta($current_user_id, 'dokan_store_name', true);
	$firstThreeCharacters = substr($seller_name, 0, 3);
	$sku = $firstThreeCharacters . '-' . strval($current_user_id) . '-' . (!empty($postdata['_foreign_sku']) ? $postdata['_foreign_sku'] : substr(str_replace(' ', '', $postdata['post_title']), 0, 5));
	update_post_meta( $product_id, '_sku', $sku );
	update_post_meta( $product_id, 'Description',  $seller_name);
	if ( !empty ($postdata['_regular_price'])){
		$current_user_id = get_current_user_id();
		$dokan_comission = floatval(get_user_meta($current_user_id, 'dokan_admin_percentage', true) ? get_user_meta($current_user_id, 'dokan_admin_percentage', true) : 15);
		if ( !empty ($postdata['_per_product_admin_commission']) ){
			$dokan_comission = floatval($postdata['_per_product_admin_commission']);
		}
		
		$rintin_price = $postdata['_regular_price'] * (1+($dokan_comission/100));
		update_post_meta( $product_id, '_regular_price', $rintin_price );
		update_post_meta( $product_id, '_price', $rintin_price );
	}
	if ( ! empty($postdata['_per_product_admin_commission'])){
		update_post_meta( $product_id, '_per_product_admin_commission', $postdata['_per_product_admin_commission'] );
		update_post_meta( $product_id, '_per_product_admin_commission_type', 'percentage' );
	}
	if ( ! empty( $postdata['_product_material'] ) ) {
		update_post_meta( $product_id, '_product_material', $postdata['_product_material'] );
	}
	if ( ! empty( $postdata['_colors_united'] ) ) {
		update_post_meta( $product_id, '_colors', $postdata['_colors_united'] );
	}
	if ( ! empty( $postdata['_origin'] ) ) {
		update_post_meta( $product_id, '_origin', $postdata['_origin'] );
	}
	if ( ! empty( $postdata['sizes_united'] ) ) {
		update_post_meta( $product_id, '_product_sizes', $postdata['sizes_united'] );
	}
	if ( ! empty( $postdata['_units_per_pack'] ) ) {
		update_post_meta( $product_id, '_units_per_pack', ( $postdata['_units_per_pack'] . ' ' . $postdata['_product_unit'] . '.'));
	}
	if ( ! empty( $postdata['_observations'] ) ) {
		update_post_meta( $product_id, '_observations', $postdata['_observations'] );
	}
	if ( ! empty( $postdata['_stock'] ) ) {
		update_post_meta( $product_id, '_stock', $postdata['_stock'] );
		global $wpdb;
		$currentDate = new DateTime();
		$datetimeForSQL = $currentDate->format('Y-m-d H:i:s');
		$targetTimezone = new DateTimeZone('America/Chicago'); // GMT-6 or CST
		$datetimeForSQLMX = clone $currentDate; 
		$datetimeForSQLMX->setTimezone($targetTimezone);
		$datetimeForSQLMXFormated = $datetimeForSQLMX->format('Y-m-d H:i:s');
		$table_name = 'stock_log';
		$data = array(
			'product_id'  => intval($product_id),
			'previous_stock' => 0,
			'new_stock' => intval($postdata['_stock']),
			'reason' => 'Creacion de producto',
			'modification_date' => $datetimeForSQL,
			'modification_date_mx' => $datetimeForSQLMXFormated,
		);
		$wpdb->insert($table_name, $data);
	}
	if (! empty( $postdata['_origin'] ) || ! empty( $postdata['sizes_united'] ) || ! empty( $postdata['_units_per_pack'] ) || ! empty( $postdata['_product_type'] ) || !empty($postdata['_colors_united']) || !empty($postdata['_product_material']) ){
		$new_short_description = 'Origen: ' . $postdata['_origin'] . '; Tallas:' . $postdata['sizes_united'] .'; Unidades por Paquete: ' . $postdata['_units_per_pack'] . ' ' . $postdata['_product_unit'] . '; Colores: ' . $postdata['_colors_united'] . '; Material: ' . $postdata['_product_material'];
		$post_data = array(
			'ID'           => $product_id,
			'post_excerpt' => $new_short_description
		  );
		wp_update_post($post_data);
		$term_data = array(
			$postdata['_origin'], $postdata['sizes_united'], $postdata['_product_type'], $postdata['_colors_united'], $postdata['_product_material']
		);
		wp_set_post_terms($product_id, $term_data, 'product_tag', true);
	}
	
}

//add_action('dokan_product_edit_after_product_tags','show_on_edit_page',99,2);
function show_on_edit_page($post, $post_id){
	$product_material = get_post_meta( $post_id, '_product_material', true );
	$colors = get_post_meta( $post_id, '_colors', true );
	$origin = get_post_meta( $post_id, '_origin', true );
	$sizes = get_post_meta( $post_id, '_product_sizes', true );
	$units = get_post_meta( $post_id, '_units_per_pack', true );
	$observations = get_post_meta( $post_id, '_observations', true );
	?>
	<div class="dokan-form-group">
		<label for="_foreign_sku">Identificador del producto</label>
		<input type="text" class="dokan-form-control" name="_foreign_sku" placeholder="<?php esc_attr_e( 'Identificador', 'dokan-lite' ); ?>">
	</div>
	<div class="dokan-form-group">
		<label for="_stock">Cantidad de inventario del producto</label>
		<input type="number" class="dokan-form-control" name="_stock" placeholder="<?php esc_attr_e( '# de inventario', 'dokan-lite' ); ?>">
	</div>
	<div class="dokan-form-group">
		<label for="_product_type">Tipo de producto (Unidad / Paquete)</label>
		<select class="select2-selection" name="_product_type" id="_product_type">
			<option value="-1">Tipo de producto</option>
			<option value="unit">Producto Unitario</option>
			<option value="pack">Producto por paquete</option>
		</select>
	</div>
	<div class="dokan-form-group dynamic-inputs-class">
		<label for="_units_per_pack">Cantidad de unidades</label>
		<input type="number" class="dokan-form-control" name="_units_per_pack" placeholder="<?php esc_attr_e( 'Cantidad de unidades', 'dokan-lite' ); ?>">
	</div>
	<div class="dokan-form-group">
		<label for="_product_unit">Tipo de unidad</label>
		<select class="select2-selection" name="_product_unit">
			<option value="-1">Selecciona el tipo de unidad</option>
			<option value="par(es)">Par</option>
			<option value="pieza(s)">Pieza</option>
			<option value="set(s)">Conjunto</option>
		</select>
	</div>
	<div class="dokan-form-group dynamic-inputs-class">
		<p>Agregar tallas</p>
		<div id="dokan-add-product-sizes-flex-container" class="dynamic-input-dokan-add">
		</div>
		<button id="add-input-dokan-btn">Agregar otra talla</button>
		<p id="sizes-warning" style="display: none;">Debes llenar todos los campos.</p>
	</div>
	<div class="dokan-form-group props_united">
		<label for="sizes_united">Talla del producto</label>
		<input type="text" id="sizes_united" class="dokan-form-control" name="sizes_united" placeholder="<?php esc_attr_e( 'Talla de tu producto', 'dokan-lite' ); ?>">
	</div>
	<div class="dokan-form-group">
		<label for="_size_characteristics">Caracteristicas de la talla</label>
		<select class="select2-selection" name="_size_characteristics" id="_size_characteristics">
			<option value="-1">Caracteristicas</option>
			<option value="Talla normal">Talla normal</option>
			<option value="Talla amplia">Talla amplia</option>
			<option value="Talla reducida">Talla reducida</option>
			<option value="Talla puede variar">Talla puede variar</option>
		</select>
	</div>
	
	<div class="dokan-form-group dynamic-inputs-class">
		<p>Agregar colores</p>
		<div id="dokan-add-product-colors-flex-container" class="dynamic-input-dokan-add">
		</div>
		<button id="add-input-dokan-btn-colors">Agregar otro color</button>
		<p id="sizes-warning-color" style="display: none;">Debes llenar todos los campos.</p>
	</div>
	<div class="dokan-form-group props_united">
		<label for="_colors_united">Color del producto</label>
		<input type="text" id="_colors_united" class="dokan-form-control" name="_colors_united" placeholder="<?php esc_attr_e( 'Color de tu producto', 'dokan-lite' ); ?>">
	</div>
	<div class="dokan-form-group">
		<label for="_product_material">Material del Producto</label>
		<input type="text" list="_product_material" class="dokan-form-control" name="_product_material" placeholder="Material del producto">
		<datalist id="_product_material" class="select2-selection">
			<?php
				echo $options;
			?>
		</datalist>
	</div>
	<div class="dokan-form-group">
		<label for="_origin">Origen</label>
		<select class="select2-selection" name="_origin">
			<option value="-1">Origen del producto</option>
			<option value="Fabricado en Mexico">Fabricado en México</option>
			<option value="Importado">Importado</option>
		</select>
	</div>
	<div class="dokan-form-group">
		<label for="_observations">Observaciones</label>
		<input type="text" class="dokan-form-control" name="_observations" placeholder="<?php esc_attr_e( 'Observaciones', 'dokan-lite' ); ?>">
	</div>
	<?php

}

add_action( 'dokan_settings_form_bottom', 'add_custom_input_field_to_vendor_settings' );
function add_custom_input_field_to_vendor_settings() {
    $current_user_id = get_current_user_id();
    $dokan_info = dokan_get_store_info($current_user_id);
	$zone = $dokan_info['zone'];
	$product_comission = $dokan_info['product_comission'];
	$minimum_products= isset( $dokan_info['minimum_products'] ) ? $dokan_info['minimum_products'] : '';
	?>
	 <div class="gregcustom dokan-form-group">
		<label class="dokan-w3 dokan-control-label" for="setting_address">
			<?php _e( 'Minimo de productos', 'dokan' ); ?>
		</label>
		<div class="dokan-w5">
			<input type="number" class="dokan-form-control input-md valid" name="minimum_products" id="reg_minimum_products" value="<?php echo $minimum_products; ?>" />
		</div>
	</div>
	<?php
	$minimum_order= isset( $dokan_info['minimum_order'] ) ? $dokan_info['minimum_order'] : '';
?>
 <div class="gregcustom dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="setting_address">
        <?php _e( 'Valor mínimo de Orden', 'dokan' ); ?>
    </label>
    <div class="dokan-w5">
        <input type="number" class="dokan-form-control input-md valid" name="minimum_order" id="reg_minimum_order" value="<?php echo $minimum_order; ?>" />
    </div>
</div>
	<?php
	
    ?>

    <div class="dokan-form-group">
        <label for="zone-select" class="dokan-w3 dokan-control-label"><?php esc_html_e( 'Zona', 'text-domain' ); ?></label>
        <div class="dokan-w5">
			<select id="zone-select" name="zone-select">
				<option value="null" disabled>Seleccione una Opción</option>
				<option value="centro" <?php echo $zone == 'centro' ? 'selected' : ''; ?> >Centro</option>
				<option value="fuera" <?php echo $zone == 'fuera' ? 'selected' : ''; ?>>Fuera del Centro</option>
			</select>
		</div>
    </div>
 <div class="dokan-form-group">
        <label for="product_comission" class="dokan-w3 dokan-control-label"><?php esc_html_e( 'Habilitar la comisión por producto', 'text-domain' ); ?></label>
        <div class="dokan-w5">
			<select id="product_comission" name="product_comission">
				<option value="null" disabled>Seleccione una Opción</option>
				<option value="no" <?php echo $product_comission === 'no' ? 'selected' : ''; ?>>No</option>
				<option value="si" <?php echo $product_comission === 'si' ? 'selected' : ''; ?> >Si</option>
			</select>
		</div>
    </div>
    <?php
}

add_action( 'dokan_store_profile_saved', 'save_extra_fields', 15 );
function save_extra_fields( $store_id ) {
	$dokan_settings = dokan_get_store_info($store_id);
	if (isset($_POST['product_comission'])){
		$dokan_settings['product_comission'] = $_POST['product_comission'];
	}
	if ( isset( $_POST['minimum_products'] ) ) {
		$dokan_settings['minimum_products'] = $_POST['minimum_products'];

	}
	
	if ( isset( $_POST['minimum_order'] ) ) {
		$dokan_settings['minimum_order'] = $_POST['minimum_order'];

	}
    if ( isset( $_POST['zone-select'] ) ) {
        $dokan_settings['zone'] = $_POST['zone-select'];
        
    }
	update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );
}
?>