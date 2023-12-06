jQuery(document).ready(function($) {
	const skuBtn = $('#return-sku-btn');
	skuBtn.on('click', (e) => {
		e.preventDefault();
		$('#modalWrapper').show();
	})
	$('#closeModalBtn').on('click', (e) => {
		e.preventDefault();
		$('#modalWrapper').hide();
	})
	$(document).on('opened', '#dokan-add-product-popup', function (e) {
		$('label[for="_regular_price"]').text('Precio a Rintin (Aquí ingresas el precio con el que le vendes tu producto a Rintin)')
		let addButton = $('#add-input-dokan-btn')
		let sizesContainer = $('#dokan-add-product-sizes-flex-container')
		let sizeWarn = $ ('#sizes-warning')
		let sizeWarnColor = $('#sizes-warning-color')
		let addButtonColor = $('#add-input-dokan-btn-colors')
		let colorContainer = $('#dokan-add-product-colors-flex-container')
		let type = $('#_product_type')
		let dynamicInputsClass = $('.dynamic-inputs-class')
		let propsUnited = $('.props_united')
		let strInputs = $('.dynamic-input-str');
		let intInputs = $('.dynamic-input-int');
		let strInputsColor = $('.dynamic-input-str-colors');
		let intInputsColor = $('.dynamic-input-int-colors');
		let sizesUnited = $('#sizes_united');
		let colorsUnited = $('#_colors_united')
		if(type.val() !== 'Paquete'){
			dynamicInputsClass.hide()
			propsUnited.show()
		} else {
			propsUnited.hide()
		}
		type.on('change', () => {
			if(type.val() !== 'Paquete'){
				dynamicInputsClass.hide()
				propsUnited.show()
			} else {
				dynamicInputsClass.show()
				propsUnited.hide()
			}

		})
		addButton.on('click', (event)=>{
			event.preventDefault()
			sizesContainer.append(
				`<div id="dokan-add-product-sizes-flex-container" class="dokan-add-product-flex-container">    <div class="dokan-form-group">     <label for="_talla">Talla</label>     <input type="text" class="dokan-form-control dynamic-input-str" name="_talla" placeholder="S, M, L">    </div>    <div class="dokan-form-group">     <label for="_units">Unidades</label>     <input type="number" class="dokan-form-control dynamic-input-int" name="_units" placeholder="Cantidad">    </div>  </div>`
			)
			strInputs = $('.dynamic-input-str');
			intInputs = $('.dynamic-input-int');
			sizeWarn.show()
		})	
		addButtonColor.on('click', (event)=>{
			event.preventDefault()
			colorContainer.append(
				`<div id="dokan-add-product-sizes-flex-container" class="dokan-add-product-flex-container">    <div class="dokan-form-group">     <label for="_color">Color</label>     <input type="text" class="dokan-form-control dynamic-input-str-colors" name="_talla" placeholder="Blanco, negro, azul">    </div>    <div class="dokan-form-group">     <label for="_units">Unidades</label>     <input type="number" class="dokan-form-control dynamic-input-int-colors" name="_colors" placeholder="Cantidad">    </div>  </div>`
			)
			strInputsColor = $('.dynamic-input-str-colors');
			intInputsColor = $('.dynamic-input-int-colors');
		})	

		sizesContainer.on('input', strInputs, function() {
			var finalSize = ''
			var valueForAllSizes = true;
			for(var j = 0; j < strInputs.length; j++){
				if (strInputs[j].value === "" || intInputs[j].value.toString() === "") {

					valueForAllSizes = false;
				}
			}
			if (valueForAllSizes){
				sizeWarn.hide()
				for(var j = 0; j < strInputs.length; j++){
					finalSize = finalSize + strInputs[j].value + ' ( ' + intInputs[j].value.toString() + ' ) ' + ' | '
				}
				sizesUnited.val(finalSize)
			}
			else{
				sizeWarn.show()
			}
		});
		colorContainer.on('input', strInputsColor, function() {
			var finalSize = ''
			var valueForAllColors = true;
			for(var j = 0; j < strInputsColor.length; j++){
				if (strInputsColor[j].value === "" || intInputsColor[j].value.toString() === "") {
					valueForAllColors = false;
				}
			}
			if (valueForAllColors){
				sizeWarnColor.hide()
				for(var j = 0; j < strInputsColor.length; j++){
					finalSize = finalSize + strInputsColor[j].value + ' ( ' + intInputsColor[j].value.toString() + ' ) ' + ' | '
				}
				colorsUnited.val(finalSize)
			}
			else{
				sizeWarnColor.show()
			}
		});
	});
	var currentUrl = $(location).attr('href');
	const regex_checkout = /https:\/\/(.)*(rintin\.mx\/finalizar-compra\/)$/;
	const regex_data_register = /https:\/\/(.)*(rintin\.mx\/registro-datos\/)$/;
	const regex_data_dokan_add_product = /https:\/\/(.)*(rintin\.mx\/panel-de-ventas\/products\/)$/;
	if(regex_data_dokan_add_product.test(currentUrl)){
		const bulkActions = $('.bulk-product-status[value="delete"]');
		const deleteAtag = $('.delete');
		deleteAtag.remove()
		bulkActions.remove()	
	}
	if(regex_checkout.test(currentUrl)){
		$.ajax({
			url: my_var.ajaxurl,
			type: 'post',
			data: {
				postal_code : $('#billing_postcode').val(),
				_wpnonce : my_var.nonce,
				action : 'postal_code_ajax'
			},
			success: function(result){
				$(document.body).trigger('update_checkout');
			}
		})
		let map;

		function getUserLocation() {
			if (navigator.geolocation) {
				// The Geolocation API is supported by the browser
				navigator.geolocation.getCurrentPosition(showPosition, showError);
			}
			else {
				alert("La geolocalización no esta habilitada para tu navegador.");
			}
		}

		function showPosition(position) {
			var latitude = position.coords.latitude;
			var longitude = position.coords.longitude;
			$('#billing_lat').val(latitude);
			$('#billing_lng').val(longitude);
			map.setCenter({lng: parseFloat(longitude), lat: parseFloat(latitude)})
		}

		function showError(error) {
			return;
		}
		async function initMap() {
			const { Map } = await google.maps.importLibrary("maps");
			const myLatLng = { lat: -34.397, lng: 150.644 };

			map = new Map(document.getElementById("map"), {
				center: { lat: 20, lng: -50 },
				zoom: 16,
			});
			if($('#billing_lng').val() !== '' && $('#billing_lat').val() !== ''){
				map.setCenter({lng: parseFloat($('#billing_lng').val()), lat: parseFloat($('#billing_lat').val())})
			}
			const marker = new google.maps.Marker({
				position: myLatLng,
				map,
				title: "Hello World!",
			});
			if($('#billing_lat').val() === '' || $('#billing_lng').val() === ''){
				getUserLocation();
			}
		}


		const getGeocodeJson = async (latitude, longitude, placeid = '') => {
			let apiUrl
			if(placeid !== ''){
				apiUrl = `https://maps.googleapis.com/maps/api/geocode/json?place_id=${placeid}&key=AIzaSyC9GnYe0meFUxQBaMcUFgqaBQgYogsFkyM`
			} else {
				apiUrl = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=AIzaSyC9GnYe0meFUxQBaMcUFgqaBQgYogsFkyM`
			}
			fetch(apiUrl).then(response => response.json())
				.then(data => {
				if (data.status === 'OK') {
					// The API call was successful, and the results are in data.results array
					const locationData = data.results[0]; // Assuming you want the first result
					const addressComponents = locationData.address_components;
					const postCode = addressComponents.filter(component => component.types.includes('postal_code'))[0].long_name
					const state = addressComponents.filter(component => component.types.includes('administrative_area_level_1'))[0].long_name
					const city = addressComponents.filter(component => component.types.includes('locality'))[0].long_name
					const nbhd = addressComponents.filter(component => component.types.includes('sublocality'))[0] ? addressComponents.filter(component => component.types.includes('sublocality'))[0].long_name : addressComponents.filter(component => component.types.includes('neighborhood'))[0] ? addressComponents.filter(component => component.types.includes('neighborhood'))[0].long_name : ''
					const streetName = addressComponents.filter(component => component.types.includes('route'))[0] ? addressComponents.filter(component => component.types.includes('route'))[0].long_name : addressComponents.filter(component => component.types.includes('establishment'))[0] ? addressComponents.filter(component => component.types.includes('establishment'))[0].long_name : ''
					const streetNumber = addressComponents.filter(component => component.types.includes('street_number'))[0] ? addressComponents.filter(component => component.types.includes('street_number'))[0].long_name : 'SN'
					$('#billing_address_1').val(locationData.formatted_address);
					$('#billing_ext_num').val(streetNumber);
					$('#billing_postcode').val(postCode);
					$('#billing_lat').val(latitude);
					$('#billing_lng').val(longitude);
					$('#billing_state').val(() => {
						const optionValueMap = {
							'Ciudad de México': 'DF',
							'Jalisco': 'JA',
							'Nuevo León': 'NL',
							'Aguascalientes': 'AG',
							'Baja California': 'BC',
							'Baja California Sur': 'BS',
							'Campeche': 'CM',
							'Chiapas': 'CS',
							'Chihuahua': 'CH',
							'Coahuila': 'CO',
							'Colima': 'CL',
							'Durango': 'DG',
							'Guanajuato': 'GT',
							'Guerrero': 'GR',
							'Hidalgo': 'HG',
							'Estado de México': 'MX',
							'Michoacán': 'MI',
							'Morelos': 'MO',
							'Nayarit': 'NA',
							'Oaxaca': 'OA',
							'Puebla': 'PU',
							'Querétaro': 'QT',
							'Quintana Roo': 'QR',
							'San Luis Potosí': 'SL',
							'Sinaloa': 'SI',
							'Sonora': 'SO',
							'Tabasco': 'TB',
							'Tamaulipas': 'TM',
							'Tlaxcala': 'TL',
							'Veracruz': 'VE',
							'Yucatán': 'YU',
							'Zacatecas': 'ZA',
						};
						return optionValueMap[state] || null;
					})
					$('#select2-billing_state-container').prop('title', state);
					$('#select2-billing_state-container').text(state);
					$('#billing_city').val(nbhd)
					$('#billing_ciudad').val(city)
					$.ajax({
						url: my_var.ajaxurl,
						type: 'post',
						data: {
							postal_code : $('#billing_postcode').val(),
							_wpnonce : my_var.nonce,
							action : 'postal_code_ajax'
						},
						success: function(result){
							$(document.body).trigger('update_checkout');
						}
					})
				} else {
					console.error('Error:', data.status);
				}
			})
		}

		initMap().then(
			() => {
				// Add an event listener to get the updated center coordinates on dragend
				map.addListener('dragend', () => {
					const center = map.getCenter();
					const latitude = center.lat();
					const longitude = center.lng();

					getGeocodeJson(latitude, longitude)
				});
			}
		);

		var timer;

		$('#billing_address_1').on('input', () => {
			clearTimeout(timer);
			timer = setTimeout(() => {
				$('#icon-container-loader').show()
				$.ajax({
					url: my_var.ajaxurl,
					type: 'post',
					data: {
						billing_address : $('#billing_address_1').val(),
						_wpnonce : my_var.nonce,
						action : 'autocomplete_ajax'
					},
					success: function( result ) {
						const array_result = JSON.parse(result);
						var address = ''
						for(value of array_result){
							address = address + '<li>' + value + '</li>'
						}
						$('#icon-container-loader').hide()
						$("#billing_address_1_field").append(`<ul class="location_list_container" id="location_list_container_id" style="position: absolute; z-index: 4;">
${address}
</ul>`)
						$('.location_list_container').on("click","li", function () {
							const clickedBtnID = $(this).text();
							$('#billing_address_1').val(clickedBtnID)
							$('.location_list_container').remove()
							$.ajax({
								url: my_var.ajaxurl,
								type: 'post',
								data: {
									billing_address : $('#billing_address_1').val(),
									_wpnonce : my_var.nonce,
									action : 'geocode_ajax'
								},
								success: function( result ) {
									const array_result = JSON.parse(result);
									map.setCenter({lng: parseFloat(array_result.geometry.location.lng), lat: parseFloat(array_result.geometry.location.lat)})
									getGeocodeJson(0, 0, array_result.place_id.toString());
								},
							})
						});
					},
				})
			}, 1000)
		})
		$(document).click(function(event) { 
			var $target = $(event.target);
			if(!$target.closest('#location_list_container_id').length && 
			   $('#location_list_container_id').is(":visible")) {
				$('#location_list_container_id').hide();
			}        
		});
		$.ajax({
			url: my_var.ajaxurl,
			type: 'post',
			data: {
				_wpnonce : my_var.nonce,
				action : 'billing_state_ajax'
			},
			success: function(result){
				$('#billing_state').val(result);
				$('#select2-billing_state-container').prop('title', () => {
					const optionValueMap = {
						'DF': 'Ciudad de México',
						'JA': 'Jalisco',
						'NL': 'Nuevo León',
						'AG': 'Aguascalientes',
						'BC': 'Baja California',
						'BS': 'Baja California Sur',
						'CM': 'Campeche',
						'CS': 'Chiapas',
						'CH': 'Chihuahua',
						'CO': 'Coahuila',
						'CL': 'Colima',
						'DG': 'Durango',
						'GT': 'Guanajuato',
						'GR': 'Guerrero',
						'HG': 'Hidalgo',
						'MX': 'Estado de México',
						'MI': 'Michoacán',
						'MO': 'Morelos',
						'NA': 'Nayarit',
						'OA': 'Oaxaca',
						'PU': 'Puebla',
						'QT': 'Querétaro',
						'QR': 'Quintana Roo',
						'SL': 'San Luis Potosí',
						'SI': 'Sinaloa',
						'SO': 'Sonora',
						'TB': 'Tabasco',
						'TM': 'Tamaulipas',
						'TL': 'Tlaxcala',
						'VE': 'Veracruz',
						'YU': 'Yucatán',
						'ZA': 'Zacatecas'
					};
					return optionValueMap[result] || null;
				});
				$('#select2-billing_state-container').text(() => {
					const optionValueMap = {
						'DF': 'Ciudad de México',
						'JA': 'Jalisco',
						'NL': 'Nuevo León',
						'AG': 'Aguascalientes',
						'BC': 'Baja California',
						'BS': 'Baja California Sur',
						'CM': 'Campeche',
						'CS': 'Chiapas',
						'CH': 'Chihuahua',
						'CO': 'Coahuila',
						'CL': 'Colima',
						'DG': 'Durango',
						'GT': 'Guanajuato',
						'GR': 'Guerrero',
						'HG': 'Hidalgo',
						'MX': 'Estado de México',
						'MI': 'Michoacán',
						'MO': 'Morelos',
						'NA': 'Nayarit',
						'OA': 'Oaxaca',
						'PU': 'Puebla',
						'QT': 'Querétaro',
						'QR': 'Quintana Roo',
						'SL': 'San Luis Potosí',
						'SI': 'Sinaloa',
						'SO': 'Sonora',
						'TB': 'Tabasco',
						'TM': 'Tamaulipas',
						'TL': 'Tlaxcala',
						'VE': 'Veracruz',
						'YU': 'Yucatán',
						'ZA': 'Zacatecas'
					};
					return optionValueMap[result] || null;
				});
			}
		})
		$('#billing_postcode').on('input', function(){
			$.ajax({
				url: my_var.ajaxurl,
				type: 'post',
				data: {
					postal_code : $('#billing_postcode').val(),
					_wpnonce : my_var.nonce,
					action : 'postal_code_ajax'
				},
				success: function(result){
					$(document.body).trigger('update_checkout');
				}
			})
		});
	}



});

