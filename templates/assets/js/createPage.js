$(document).ready(function() {

	var app = {
		totalOrders: [],
		totalPrice: 0.00
	};

	$('.menu-item').on('click', function() {
		$(this).next('.addon-container').toggle();
	});

	$('.add-to-order-btn').on('click', function() {
		var selectedMenuItemId = $(this).attr('data-itemid'),
			selectedMenuDiv = $('#menu-item-' + selectedMenuItemId),
			menuItemName = selectedMenuDiv.find('.desc').text(),
			menuItemPrice = selectedMenuDiv.find('.price').text();

		var newOrder = {
			menuItemName: menuItemName,
			menuItemId: selectedMenuItemId,
			menuItemPrice: menuItemPrice,
			addons: {}
		};

		var selectedRadioBtns = selectedMenuDiv.next().find('input[type=radio]:checked, input[type=checkbox]:checked');
		$.each(selectedRadioBtns, function(i, radioBtn) {
			var nameAttr = $(radioBtn).attr('name'),
				nameAttrArray = nameAttr.split('-'),
				categoryName = nameAttrArray[2];

			newOrder.addons[categoryName] || (newOrder.addons[categoryName] = []);

			var newAddon = {
				name: $(radioBtn).parent().text(),
				id: $(radioBtn).val(),
				categoryName: categoryName
			};

			newOrder.addons[categoryName].push(newAddon);
		});

		console.group('New Order');
		console.log(newOrder);
		console.groupEnd();

		app.totalOrders.push(newOrder);


		var idCSV = '';
		$.each(newOrder.addons, function(i, addonAry) {
			$.each(addonAry, function(j, addon) {
			 	idCSV += ',' + addon.id;
			});
		});

		var getPriceForOrder = $.ajax({
			type: 'GET',
			url: '/getprice',
			data: {
				menuItemId: newOrder.menuItemId,
				addonIds: idCSV
			}
		});

		$.when(getPriceForOrder).then(function(data) {
			console.log('done');
			console.log(parseFloat(data));
			var currentOrderPrice = parseFloat(data);
			var cartEl = $('.cart-items');
			var appendHtml = '';
			cartEl.append('<div class="title">' +
						  	'<span class="desc"><h3>' + newOrder.menuItemName + '</h3></span>' +
						  	'<span class="price"><h3>$' + currentOrderPrice + '</h3></span>' +
						  '</div>');
			$.each(newOrder.addons, function(categoryName, addonAry) {
				appendHtml += '<div class="addons-desc">' + categoryName + ':';
				$.each(addonAry, function(i, addon) {
					appendHtml += ' ' + addon.name;
					if (i !== addonAry.length - 1)
						appendHtml += ', ';
				});
				appendHtml += '</div>';
			});

			cartEl.append(appendHtml);

			app.totalPrice += currentOrderPrice;
			$('.total-price').text('Total: $' + app.totalPrice);

			window.scrollTo(0,document.body.scrollHeight);

			$.each(selectedRadioBtns, function(i, radio) {
				$(radio).prop('checked', false);
			});

			$('#menu-item-' + selectedMenuItemId).next().hide();

		});
	});

});