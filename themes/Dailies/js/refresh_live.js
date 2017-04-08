function refreshLive() {
	var oldPostsDataContainer = jQuery("#live-posts-data");
	var oldPostsDataRaw = oldPostsDataContainer.text();
	var oldPostsData = JSON.parse(oldPostsDataRaw);
	jQuery.ajax({
		type: "POST",
		url: data_for_refresh_live.ajaxurl,
		dataType:'json',
		data: {
			oldData: oldPostsData,
			action: 'refresh_live',
		},
		success: function(data) {
			console.log('refreshed!');
			var fresh = data.fresh;
			var stale = data.stale;
			if (fresh != null) {	
				var freshCount = fresh.length;
				var i = 0;
				while (i < freshCount) {
					var freshPosts = jQuery(fresh[i]);
					console.log(fresh[i]);
					grid.prepend(freshPosts).isotope('prepended', freshPosts);
					i++
				}
			}
			if (stale != null) {
				var staleCount = stale.length;
				var i = 0;
				while (i < staleCount) {
					var stalePostID = stale[i];
					var staleThingID = "#little-thing-" + stalePostID;
					var staleThing = jQuery(staleThingID);
					grid.isotope('remove', staleThing).isotope('layout');
					i++
				}
			}
			oldPostsDataContainer.html(data.newData);
			var postsAndScores = JSON.parse(data.newData);
			jQuery.each(postsAndScores, function(id, score) {
				var scoreDivID = `#thingScore${id}`;
				var scoreDiv = jQuery(scoreDivID);
				var newScore = `(+${score})`
				scoreDiv.html(newScore);
			});

		}
	});
}

function postTrasher(ID) {
	var removeTargetID = "#little-thing-" + ID;
	var removeTarget = jQuery(removeTargetID);
	grid.isotope('remove', removeTarget).isotope('layout');
	jQuery.ajax({
		type: "POST",
		url: data_for_refresh_live.ajaxurl,
		dataType:'json',
		data: {
			trash: ID,
			action: 'trash_post',
		},
		success: function(data) {
			refreshLive();
		}
	});
}