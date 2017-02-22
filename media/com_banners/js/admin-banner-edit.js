jQuery(document).ready(function ($){
	$("#jform_type").on("change", function (a, params) {

		var v = typeof(params) !== "object" ? $("#jform_type").val() : params.selected;

		var img_url = $("#image, #url");
		var custom  = $("#custom");

		switch (v) {
			case "0":
				// Image
				img_url.show();
				custom.hide();
				break;
			case "1":
				// Custom
				img_url.hide();
				custom.show();
				break;
		}
	}).trigger("change");
});
