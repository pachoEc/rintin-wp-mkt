jQuery(document).ready(function($) {
	const skuBtn = $('#return-sku-btn');
	const skusContainer = $('#skus-container');
	var strInputs;
	var intInputs;
	const skusUnited = $('#skus-united');
	skusContainer.append(`<div id="dokan-add-product-sizes-flex-container" class="dokan-add-product-flex-container">    <div class="dokan-form-group">     <label for="_sku">SKU</label>     <input type="text" class="dokan-form-control dynamic-input-str" name="_sku" placeholder="EJEMPLO123">    </div>    <div class="dokan-form-group">     <label for="_units">Unidades</label>     <input type="number" class="dokan-form-control dynamic-input-int" name="_units" placeholder="5">    </div>  </div>`)
	strInputs = $('.dynamic-input-str');
		intInputs = $('.dynamic-input-int');
	skuBtn.on('click', (e) => {
		e.preventDefault();
		$('#modalWrapper').show();
	})
	$('#closeModalBtn').on('click', (e) => {
		e.preventDefault();
		$('#modalWrapper').hide();
	})
	$('#add-sku').on('click', (e) => {
		e.preventDefault();
		skusContainer.append(`<div id="dokan-add-product-sizes-flex-container" class="dokan-add-product-flex-container">    <div class="dokan-form-group">     <label for="_sku">SKU</label>     <input type="text" class="dokan-form-control dynamic-input-str" name="_sku">    </div>    <div class="dokan-form-group">     <label for="_units">Unidades</label>     <input type="number" class="dokan-form-control dynamic-input-int" name="_units">    </div>  </div>`)
		strInputs = $('.dynamic-input-str');
		intInputs = $('.dynamic-input-int');
	})
	skusContainer.on('input', strInputs, function() {
			var finalSKU = {}
			var valueForAllSizes = true;
			for(var j = 0; j < strInputs.length; j++){
				if (strInputs[j].value === "" || intInputs[j].value.toString() === "") {
					valueForAllSizes = false;
				}
			}
			if (valueForAllSizes){
				for(var j = 0; j < strInputs.length; j++){
					finalSKU[strInputs[j].value.replace(/\s/g,'')] = intInputs[j].value.toString().replace(/\s/g,'');
				}
				
				skusUnited.val(JSON.stringify(finalSKU))
				console.log(skusUnited.val())
			}
			else{
				
			}
		});
})