$(document).ready(function() {
	$('.menu-item').on('click', function() {
		$(this).next('.addon-container').toggle();
	});
});