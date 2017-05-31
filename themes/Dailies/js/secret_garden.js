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
			tickUpCutCounter();
			seedling.remove();
			var allSeeds = jQuery('.seedling');
			jQuery.each(allSeeds, function() {
				var thisVODLink = jQuery(this).find('.seedling-views a');
				var thisVODBase = thisVODLink.attr("data-vodbase");
				var thisVODTimestamp = thisVODLink.attr("data-vodtimestamp");
				if (VODBase === thisVODBase && thisVODTimestamp + 10 >= VODTimestamp && thisVODTimestamp - 10 <= VODTimestamp ) {
					jQuery(this).remove();
					tickUpCutCounter();
				}
			});
		}
	});
};

function growSeed(slug, title, source, time, seedling, VODBase, VODTimestamp, voters) {
	jQuery.ajax({
		type: "POST",
		url: data_for_secret_garden.ajaxurl,
		dataType:'json',
		data: {
			growSlug: slug,
			growSource: source,
			growTitle: title,
			growVoters: voters,
			action: 'secret_garden_grow',
		},
		success: function(data) {
			if ( Number.isInteger(data) ) {
				tickUpCutCounter();
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
				tickUpCutCounter();
				cutSlug(slug, time, seedling, VODBase, VODTimestamp, user);
			};
		}
	});
};