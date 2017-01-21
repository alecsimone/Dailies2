/*** Vote Script ***/
function vote(ID) {
	var thisVoteButton = jQuery(`#voteIcon${ID}`);
	var thisScore = jQuery(`#thingScore${ID}`);
	var thisRep = jQuery('.repScore');
	var thisOnboardbox = jQuery(`#thing${ID}-onboardbox`);
	thisVoteButton.fadeOut(200);
	thisScore.fadeOut(200);
	thisOnboardbox.css("maxHeight", 300);
	jQuery.ajax({
		type: "POST",
		url: daily_vote.ajaxurl,
		dataType:'json',
		data: {
			id: ID,
			action: 'daily_vote',
			vote_nonce: daily_vote.nonce
		},
		error: function(one, two, three) {
			console.log(one);
			console.log(two);
			console.log(three);
		},
		success: function(data) {
			if (data) {
				if (data.voted) {
					thisVoteButton.replaceWith(`<img src="${daily_vote.voteIcon}" id="voteIcon${ID}" class="voteIcon" data-id="${ID}" onclick="vote(${ID})">`).fadeIn(200);
				} else {
					thisVoteButton.replaceWith(`<img src="${daily_vote.emptyVoteIcon}" id="voteIcon${ID}" class="voteIcon" data-id="${ID}" onclick="vote(${ID})">`).fadeIn(200);
				}
					thisScore.html('+' + data.new_score);
					thisScore.fadeIn(200);
					thisRep.html(data.new_rep);
					window._loq = window._loq || []; // ensure queue available
					window._loq.push(["tag","Voted", true]); //Tag recordings in lucky orange
					console.log(data.log);
			};
		}
	});
}