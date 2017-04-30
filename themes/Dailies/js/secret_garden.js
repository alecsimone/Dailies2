function cutSlug(slug, time, seedling) {
	jQuery.ajax({
		type: "POST",
		url: data_for_secret_garden.ajaxurl,
		dataType:'json',
		data: {
			cutSlug: slug,
			cutSlugsTime: time,
			action: 'secret_garden_cut',
		},
		success: function(data) {
			seedling.remove();
		}
	});
};

function growSeed(slug, title, source, time, seedling) {
	jQuery.ajax({
		type: "POST",
		url: data_for_secret_garden.ajaxurl,
		dataType:'json',
		data: {
			growSlug: slug,
			growSource: source,
			growTitle: title,
			action: 'secret_garden_grow',
		},
		success: function(data) {
			if ( Number.isInteger(data) ) {
				cutSlug(slug, time, seedling);
				window.open(`http://dailies.gg/wp-admin/post.php?post=${data}&action=edit`, '_blank');
			}
		}
	});
};