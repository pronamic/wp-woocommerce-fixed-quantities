// Handles single product pages
jQuery(document).ready(function($){
	
	// Exit when the Fixed_Quantities object is not found
	if(typeof Fixed_Quantities == 'undefined')
		return;
	
	// Maximum number of select options
	var maximumSelectOptions = 10;
	
	// When the selected variation changes the stock selector should be updated
	$('input[name=variation_id]').change(function(){
		
		// Exit when step is either 1 or not set
		if(Fixed_Quantities.step == 1 || Fixed_Quantities.step == '')
			return;
		
		// Variation id
		var variationID = $(this).val();
		
		// Exit on empty value
		if(variationID == '')
			return;
		
		// Get stock. When stock is below the fixed quantity threshold, but backorders are allowed,
		// set stock to maximumSelectOptions times the fixed quantity to fill the quantity selector with.
		if(Fixed_Quantities.stock == '') Fixed_Quantities.stock = 0;
		var stock = parseFloat(Fixed_Quantities.stock[variationID]);
		if(Fixed_Quantities.backorders && stock < parseFloat(Fixed_Quantities.step) * maximumSelectOptions)
			stock = parseFloat(Fixed_Quantities.step) * maximumSelectOptions;
		
		// Build quantity selector
		var select = '<select name="quantity" class="fixed-quantity-select input-select select">';
		for(var i = parseFloat(Fixed_Quantities.step); i <= stock; i += parseFloat(Fixed_Quantities.step)){
			
			// Add option
			select += '<option value="' + i + '">' + i + '</option>';
			
			// Break when the maximum number of selected options is reached
			if(i >= maximumSelectOptions * parseFloat(Fixed_Quantities.step))
				break;
		}
		select += '</select>';
		
		// Replace quantity field with new quantity selector
		$('.quantity.buttons_added').html(select);
	});
});

// Handles the cart page
jQuery(document).ready(function($){
	
	// Exit when the Fixed_Quantities object is not found
	if(typeof Fixed_Quantities_Cart == 'undefined')
		return;
	
	// Maximum number of select options
	var maximumSelectOptions = 10;
	
	// Loop through cart items
	$.each(Fixed_Quantities_Cart, function(key, values){
			
		// Exit when step is either 1 or not set
		if(values.step == 1 || values.step == '')
			return;
		
		// Loop through qunatity fields to find the right one
		var $quantityField;
		$('.quantity.buttons_added').each(function(){
			$('input').each(function(){
				if($(this).attr('name') == 'cart[' + key + '][qty]')
					$quantityField = $(this);
			});
		});
		
		// Return when the quantity field is null
		if($quantityField == null || $quantityField == 'undefined')
			return;
		
		// Get stock. When stock is below the fixed quantity threshold, but backorders are allowed,
		// set stock to maximumSelectOptions times the fixed quantity to fill the quantity selector with.
		if(values.stock == '') values.stock = 0;
		var stock = parseFloat(values.stock);
		if(values.backorders && stock < parseFloat(values.step) * maximumSelectOptions)
			stock = parseFloat(values.step) * maximumSelectOptions;
		
		// If the current value is higher than the fixed quantity and stock, but backorders are disabled, set stock to current value
		if(parseFloat(values.step) <= $quantityField.val() && stock < $quantityField.val() && !values.backorders)
			stock = $quantityField.val();
		
		// Build quantity selector
		var select = '<select name="cart[' + key + '][qty]" class="fixed-quantity-select input-select select">';
		for(var i = parseFloat(values.step); i <= stock; i += parseFloat(values.step)){
			
			// Check if option needs to be selected
			var selected = '';
			if($quantityField.val() == i)
				selected = 'selected="selected"';
			
			// Add option
			select += '<option value="' + i + '"' + selected + '>' + i + '</option>';
			
			// Break when the maximum number of selected options is reached
			if(i >= maximumSelectOptions * parseFloat(values.step))
				break;
		}
		
		// If currentValue is bigger than any item in the list, show it selected at the end of the list.
		if($quantityField.val() > i)
			select += '<option value="' + $quantityField.val() + '" selected="selected">' + $quantityField.val() + '</option>';
		
		// Close list
		select += '</select>';
		
		// Replace quantity field with new quantity selector
		$quantityField.parent().html(select);
	});
});