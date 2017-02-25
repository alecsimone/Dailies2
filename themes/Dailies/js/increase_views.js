function tickUpViews(postID, viewType) {
	jQuery.ajax({
		type: "POST",
		url: data_for_increasing_views.ajaxurl,
		dataType:'json',
		data: {
			id: postID,
			viewType: viewType,
			action: 'increase_views',
		},
		success: function(data) {
			console.log('success!');
		}
	});
}