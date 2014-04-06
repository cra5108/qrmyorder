$(document).ready(function() {

	var app = {
		totalOrders: [],
		totalPrice: 0.00,
		orderCSVs: []
	};

	$('.cancel-order').on('click', function() {
		location.reload();
	});

	$('.submit-order').on('click', function() {
		console.log(app.totalOrders);
		var ordersCSVs = totalOrdersToCSV();
		var submitReq = $.ajax({
			type: 'POST',
			url: '/submitorder',
			data: {
				csv: ordersCSVs
			}
		});

		$.when(submitReq).then(function(data) {
			window.location = data;
		});
	});

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

			var currentOrderPrice = parseFloat(data);
			var cartEl = $('.cart-items');
			var appendHtml = '';
			var cartItemEl = $('<div/>', {
				'id' : 'cart-item-' + (app.totalOrders.length - 1),
				'class': 'cart-item'
			});

			cartItemEl.append('<div class="title">' +
						  	'<a class="remove-order btn btn-danger btn-xs" data-index="' + (app.totalOrders.length - 1) + '"><i class="glyphicon glyphicon-remove"></i></a>  <span class="desc"><h3>' + newOrder.menuItemName + '</h3></span>' +
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
			cartItemEl.append(appendHtml);
			cartEl.append(cartItemEl);

			app.totalPrice += currentOrderPrice;
			$('.total-price').text('Total: $' + app.totalPrice.toFixed(2));

			window.scrollTo(0,document.body.scrollHeight);

			$.each(selectedRadioBtns, function(i, radio) {
				$(radio).prop('checked', false);
			});

			$('#menu-item-' + selectedMenuItemId).next().hide();

			$('.remove-order').unbind('click');
			$('.remove-order').on('click', function() {
				var removeIndex = $(this).attr('data-index');
				delete app.totalOrders[removeIndex];

				var subtractAmount = parseFloat($(this).parent().find('.price').text().replace('$', ''));

				app.totalPrice -= subtractAmount;
				$('.total-price').text('Total: $' + app.totalPrice.toFixed(2));

				$('#cart-item-' + removeIndex).remove();
			});
		});
	});


	function totalOrdersToCSV() {
		var csvArray = [];

		$.each(app.totalOrders, function(ordersIndex, order) {
			var orderCSV = order.menuItemId;
			$.each(order.addons, function(addonArrayIndex, addonArray) {
				$.each(addonArray, function(addonIndex, addon) {
					orderCSV += ',' + addon.id;
				});
			});

			csvArray.push(orderCSV);
		});

		return csvArray;
	}
});