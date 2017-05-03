function cutSlug(slug, time, seedling, VODBase, VODTimestamp) {
	jQuery.ajax({
		type: "POST",
		url: data_for_secret_garden.ajaxurl,
		dataType:'json',
		data: {
			cutSlug: slug,
			cutSlugsTime: time,
			cutSlugsVODBase: VODBase,
			cutSlugsVODTimestamp: VODTimestamp,
			action: 'secret_garden_cut',
		},
		success: function(data) {
			seedling.remove();
			var allSeeds = jQuery('.seedling');
			jQuery.each(allSeeds, function() {
				var thisVODLink = jQuery(this).find('.seedling-views a');
				var thisVODBase = thisVODLink.attr("data-vodbase");
				var thisVODTimestamp = thisVODLink.attr("data-vodtimestamp");
				if (VODBase == thisVODBase) {
					var timeDifference = VODTimestamp - thisVODTimestamp;
					if ( -15 <= timeDifference && timeDifference <= 15) {
						jQuery(this).remove();
						var cutCounterSpan = jQuery('.cutCounter');
						oldCutCount = parseInt(cutCounterSpan.text());
						newCutCounter = oldCutCount + 1;
						cutCounterSpan.text(newCutCounter);
					}
				}
			});
		}
	});
};

function growSeed(slug, title, source, time, seedling, VODBase, VODTimestamp) {
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
				cutSlug(slug, time, seedling, VODBase, VODTimestamp);
				window.open(`http://dailies.gg/wp-admin/post.php?post=${data}&action=edit`, '_blank');
			}
		}
	});
};