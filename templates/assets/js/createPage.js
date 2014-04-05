$(document).ready(function() {

	var app = {};

	$('.menu-item').on('click', function() {
		$(this).next('.addon-container').toggle();
	});

	$('.add-to-order-btn').on('click', function() {
		var selectedMenuItemId = $(this).attr('data-itemid'),
			selectedMenuDiv = $('#menu-item-' + selectedMenuItemId),
			categoryCountForMenu = selectedMenuDiv.next().find('.addon').length;

		app.newOrder = {
			menuItemId: selectedMenuItemId,
			addons: []
		};

		var selectedRadioBtns = selectedMenuDiv.next().find('input[type=radio]:checked, input[type=checkbox]:checked');
		$.each(selectedRadioBtns, function(i, radioBtn) {
			var nameAttr = $(radioBtn).attr('name'),
				nameAttrArray = nameAttr.split('-'),
				categoryName = nameAttrArray[2];


			var newAddon = {
				name: $(radioBtn).parent().text(),
				id: $(radioBtn).val(),
				categoryName: categoryName
			};

			app.newOrder.addons.push(newAddon);
		});

		console.group('New Order');
		console.log(app.newOrder);
		console.groupEnd();




	});

});