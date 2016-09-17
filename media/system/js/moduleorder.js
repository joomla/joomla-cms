!(function() {
	jQuery(document).ready(function($) {
		var $field = $('.module-ajax-ordering'),
			$url = $field.data('url'),
			$clientId = $field.data('client-id'),
			$element = document.getElementById($field.data('element')),
			$linkedField = $field.data('linked-field') ? $field.data('linked-field') : 'jform_position',
			$linkedFieldEl = $('#' + $linkedField),
			$originalOrder = $field.data('ordering'),
			$originalPos = $linkedFieldEl.chosen().val(),
			$name = $field.data('name'),
			$attr = $field.data('client-attr') ? $field.data('client-attr') : '',
			$id = $field.attr('id') + '_1',
			$orders = new Array(),
			getNewOrder = function() {
				$.ajax({
					type: "GET",
					dataType: "json",
					url: $url,
					data: {
						"client_id": $clientId,
						"position" : $originalPos
					}
					})
					.fail(function (jqXHR, textStatus, error) {
						Joomla.renderMessages(Joomla.ajaxErrorsMessages(jqXHR, textStatus, error));

						window.scrollTo(0, 0);
					})
					.done(function (response) {
						if (response.data)
						{
							// Check if everything is OK
							if (response.data.length > 0)
							{
								var i;
								for (i = 0; i < response.data.length; ++i) {
									$orders[i] = response.data[i].split(',');
								}
								writeDynaList('name="' + $name + '" id="' + $id +'"' + $attr, $orders, $originalPos, $originalPos, $originalOrder, $element);
							}
						}

						// Render messages, if any. There are only message in case of errors.
						if (typeof response.messages == 'object' && response.messages !== null)
						{
							Joomla.renderMessages(response.messages);
							window.scrollTo(0, 0);
						}
					});
			};

		// Initialize the field on document ready
		getNewOrder();

		// Event listener for the linked field
		$linkedFieldEl.chosen().change( function() {
			$originalPos = $('#' + $linkedField).chosen().val();
			getNewOrder();
		});
	});
})();
