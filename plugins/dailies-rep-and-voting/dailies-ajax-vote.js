/*** Vote Script ***/
function vote(ID) {
	var thisVoteButton = jQuery(`#voteIcon${ID}`);
	var thisScoreElement = jQuery(`#thingScore${ID}`);
	var thisRepElement = jQuery('.repScore');
	var thisOnboardbox = jQuery(`#thing${ID}-onboardbox`);
	thisScore = thisScoreElement.attr('data-score');
	thisScore = parseFloat(thisScore);
	thisContribution = thisScoreElement.attr('data-contribution');
	thisContribution = parseFloat(thisContribution);
	thisRep = thisRepElement.attr('data-rep');
	thisRep = parseFloat(thisRep);
	thisVoteDirection = thisVoteButton.attr('data-vote');
	if (thisVoteDirection === "up") {
		newScore = (thisScore + thisRep).toFixed(1);
		thisScoreElement.attr('data-contribution', thisRep);
		thisVoteButton.replaceWith(`<img src="${daily_vote.medal}" id="voteIcon${ID}" class="voteIcon" data-id="${ID}" data-vote="down" onclick="vote(${ID})">`).fadeIn(200);
	} else {
		newScore = (thisScore - thisContribution).toFixed(1);
		thisScoreElement.attr('data-contribution', 0);
		thisVoteButton.replaceWith(`<img src="${daily_vote.emptyVoteIcon}" id="voteIcon${ID}" class="voteIcon" data-id="${ID}" data-vote="up" onclick="vote(${ID})">`).fadeIn(200);
	}
	thisScoreElement.html( `(+${newScore})` );
	console.log(newScore);
	thisScoreElement.attr('data-score', newScore);
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
				thisRepElement.html(data.new_rep);
				thisRepElement.attr('data-rep', data.new_rep);
				window._loq = window._loq || []; // ensure queue available
				window._loq.push(["tag","Voted", true]); //Tag recordings in lucky orange
				console.log(data.log);
			};
		}
	});
}

function scoreFadeBack(ID) {
	return function() {
		var thisScoreElement = jQuery(`#thingScore${ID}`);
		
		thisScoreElement.fadeIn(200);
	};
}