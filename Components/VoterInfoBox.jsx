import React from "react";
import {privateData} from '../Scripts/privateData.jsx';

export default class VoterInfoBox extends React.Component{
	render() {
		var voterData = this.props.voterData;
		var voterIDs = Object.keys(voterData);
		var voterBubbles = voterIDs.map(function(voterID) {
			var voterName = voterData[voterID]['name'];
			var voterPic = voterData[voterID]['picture'];
			let voterRep = voterData[voterID]['rep'];
			return (
				<img key={voterID} className="voterBubble" src={voterPic} title={`${voterName}: ${voterRep}`} onError={(e) => window.imageError(e, 'twitchVoter')} />
			)
		});
		var twitchVoters = this.props.twitchVoters;
		var twitchVoterBubbles = [];
		jQuery.each(twitchVoters, function(voter, pic) {
			if (pic === 'none' || pic === null) {
				pic = 'https://dailies.gg/wp-content/uploads/2017/03/default_pic.jpg'
				if (dailiesGlobalData.userData.userRole === 'administrator') {
					var query = 'https://api.twitch.tv/kraken/users?login=' + voter;
					jQuery.ajax({
						type: 'GET',
						url: query,
						headers: {
							'Client-ID' : privateData.twitchClientID,
							'Accept' : 'application/vnd.twitchtv.v5+json',
						},
						error: function(one, two, three) {
							console.log(one);
							console.log(two);
							console.log(three);
						},
						success: function(data) {
							var picSrc = data.users[0]['logo'];
							console.log(`voter: ${voter}`);
							console.log(`picSrc: ${picSrc}`);
							jQuery.ajax({
								type: "POST",
								url: dailiesGlobalData.ajaxurl,
								dataType: 'json',
								data: {
									twitchName: voter,
									twitchPic: picSrc,
									action: 'update_twitch_db',
								},
								error: function(one, two, three) {
									console.log(one);
									console.log(two);
									console.log(three);
								},
								success: function(data) {
									console.log(data);
								}
							});
						}
					});
				}
			}
			var thisBubble = <img key={voter} className="voterBubble" src={pic} title={`${voter}: 1`} onError={(e) => window.imageError(e, 'twitchVoter')}/>
			twitchVoterBubbles.push(thisBubble);
		});

		let guestBubble;
		let guestcount;
		try {
			guestcount = this.props.guestlist.length;
		}
		catch(error) {
			guestcount = 0;
		}
		if (guestcount > 0) {
			guestBubble = <img key={`guestBubble-${this.props.thisID}`} className="voterBubble" src='https://dailies.gg/wp-content/uploads/2017/03/default_pic.jpg' title={`Guests: ${guestcount}`} />
		}

		let twitterBubble;
		const addedCount = parseInt(this.props.addedVotes, 10);
		if (addedCount > 0) {
			twitterBubble = <img key={`twitterBubble-${this.props.thisID}`} className="voterBubble" src="https://dailies.gg/wp-content/uploads/2018/08/twitter-logo.png" title={`Twitter Votes: ${addedCount}`} />
		}
		
		return (
			<div className="VoterInfoBox">
				{voterBubbles}{twitchVoterBubbles}{guestBubble}{twitterBubble}
			</div>
		)
	}
}