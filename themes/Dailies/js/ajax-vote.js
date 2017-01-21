/*** Vote Script ***/
function vote(ID) {
	var thisVoteButtonID = 'voteIcon' + ID;
	var thisVoteButton = jQuery('#' + thisVoteButtonID);
	var thisScore = jQuery('#thingScore' + ID);
	var thisRep = jQuery('.repScore');
	thisVoteButton.fadeOut(200);
	thisScore.fadeOut(200);
	jQuery.ajax({
		type: "POST",
		url: daily_vote.ajaxurl,
		dataType:'json',
		data: {
			id: ID,
			action: 'daily_vote',
			vote_nonce: daily_vote.nonce
		},
		success: function(data) {
			if (data) {
				if (data.voted) {
					thisVoteButton.replaceWith('<img src="http://therocketdailies.com/wp-content/uploads/2016/12/Medal-small-100.png" id="voteIcon' + ID + '" class="voteIcon" data-id="' + ID + '" onclick="vote(' + ID + ')">').fadeIn(200);
				} else {
					thisVoteButton.replaceWith('<img src="http://therocketdailies.com/wp-content/uploads/2016/12/Vote-Icon-100.png" id="voteIcon' + ID + '" class="voteIcon" data-id="' + ID + '" onclick="vote(' + ID + ')">').fadeIn(200);
				}
					thisScore.html('+' + data.new_score);
					thisScore.fadeIn(200);
					thisRep.html(data.new_rep);
					window._loq = window._loq || []; // ensure queue available
					window._loq.push(["tag","Voted", true]);
					console.log(data.log);
			};
		}
	});
}