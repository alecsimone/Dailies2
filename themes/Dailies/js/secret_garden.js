function cutSlug(slug, time, seedling, VODBase, VODTimestamp, scope) {
	jQuery.ajax({
		type: "POST",
		url: data_for_secret_garden.ajaxurl,
		dataType:'json',
		data: {
			cutSlug: slug,
			cutSlugsTime: time,
			cutSlugsVODBase: VODBase,
			cutSlugsVODTimestamp: VODTimestamp,
			cutSlugScope: scope,
			action: 'secret_garden_cut',
		},
		success: function(data) {
			console.log(data);
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
				cutSlug(slug, time, seedling, VODBase, VODTimestamp, 'everyone');
				window.open(`http://dailies.gg/wp-admin/post.php?post=${data}&action=edit`, '_blank');
			}
		}
	});
};

function voteSlug(slug, time, seedling, VODBase, VODTimestamp, user) {
	jQuery.ajax({
		type: "POST",
		url: data_for_secret_garden.ajaxurl,
		dataType:'json',
		data: {
			voteSlug: slug,
			voteSlugsTime: time,
			voteSlugsVODBase: VODBase,
			voteSlugsVODTimestamp: VODTimestamp,
			voteSlugScope: user,
			action: 'secret_garden_vote',
		},
		success: function(data) {
			if (data == true) {
				console.log("You voted!");
				cutSlug(slug, time, seedling, VODBase, VODTimestamp, user);
			};
		}
	});
};