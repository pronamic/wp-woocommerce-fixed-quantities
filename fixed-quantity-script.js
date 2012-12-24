jQuery(document).ready(function($){
	var quantity;
	var quantities;
	if(typeof fixed_quantity != 'undefined'){
		quantity = fixed_quantity.fixed_quantity;
	}
	if(typeof fixed_quantities != 'undefined'){
		quantities = fixed_quantities.fixed_quantities;
	}
	
	if(quantity){
		
		setInterval(
			function(){
				if(quantity > 1 && $('.qty').attr('value') != undefined && $('.qty').attr('value') == $('.qty').attr('data-min')){
					var max = parseFloat($('.qty').attr('data-max'));
					var min = parseFloat($('.qty').attr('data-min'));
					
					$parent = $('.qty').parent();
					
					var select = '<select name="quantity" class="fixed-quantity-select input-select select">';

					for(var i = parseFloat(quantity); i <= max; i += parseFloat(quantity)){
						select += '<option value="' + i + '">' + i + '</option>';
					}
					
					$parent.html(select);
				}
			},
			50
		);
		
		$('select').change(function(){
			if(quantity <= 1)
				return;
			
			setTimeout(function(){
				if($('.stock').html() == null)
					return;
				
				var max = parseFloat($('.stock').html().replace(/[A-Za-z$-]/g, ''));
				var min = 1;
				
				$parent = $('.fixed-quantity-select').parent();
				
				var select = '<select name="quantity" class="fixed-quantity-select input-select select">';

				for(var i = parseFloat(quantity); i <= max; i += parseFloat(quantity)){
					select += '<option value="' + i + '">' + i + '</option>';
				}
				
				$parent.html(select);
			}, 200)
		});
	} else if(quantities){
		$.each(quantities, function(key, value){
			$.fn.fixed_quantities = function(quantity){
				if(quantity <= 1)
					return;
				
				$element = $(this);
				
				var name = $element.attr('name');
				var value = parseFloat($element.attr('value'));
				var max = parseFloat($element.attr('data-max'));
				var min = parseFloat($element.attr('data-min'));
				
				$parent = $element.parent();
				
				var select = '<select name="' + name + '" class="fixed-quantity-select input-select select">';

				for(var i = parseFloat(quantity); i <= max; i += parseFloat(quantity)){
					var selected = '';
					if(value == i)
						selected = 'selected="selected"';
					
					select += '<option value="' + i + '" ' + selected + '>' + i + '</option>';
				}
				
				$parent.html(select);
				
				return;

				$element.attr({
					'data-min': quantity,
					'data-max': $('.qty').attr('data-max') - ($('.qty').attr('data-max') % quantity)
				});

				$element.next().click(function() {
				    var currentVal = parseInt($(this).prev(".qty").val());
				    if (!currentVal || currentVal=="" || currentVal == "NaN") currentVal = 0;
					    
				    $qty = $(this).prev(".qty");
					    
				    var max = parseInt($qty.attr('data-max'));
				    if (max=="" || max == "NaN") max = '';
					    
				    if (max && (max==currentVal || currentVal>max)) {
				    	$qty.val(max); 
				    } else {
				    	$qty.val(currentVal + (quantity - 1)); 
				    }
					    
				    $qty.trigger('change');
				});

				$element.prev().click(function() {
					var currentVal = parseInt($(this).next(".qty").val());
				    if (!currentVal || currentVal=="" || currentVal == "NaN") currentVal = 0;
					    
				    $qty = $(this).next(".qty");
					    
				    var min = parseInt($qty.attr('data-min'));
				    if (min=="" || min == "NaN") min = 0;
					    
				    if (min && (min==currentVal || currentVal<min)) {
				    	$qty.val(min); 
				    } else if (currentVal > 0) {
				    	$qty.val(currentVal - (quantity + 1));
				    }
					    
				    $qty.trigger('change');
				});
			}

			$('.qty').each(function(){
				if($(this).attr('name') == 'cart[' + key + '][qty]')
					$(this).fixed_quantities(value);
			});
		});
	}
});