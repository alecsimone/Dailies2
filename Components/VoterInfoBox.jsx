import React from "react";
import {privateData} from '../Scripts/privateData.jsx';

export default class VoterInfoBox extends React.Component{
	render() {
		var voterData = this.props.voterData;
		var voterIDs = Object.keys(voterData);
		var voterBubbles = voterIDs.map(function(voterID) {
			var voterName = voterData[voterID]['name'];
			var voterPic = voterData[voterID]['picture'];
			return (
				<img key={voterID} className="voterBubble" src={voterPic} title={voterName} />
			)
		});
		var twitchVoters = this.props.twitchVoters;
		var twitchVoterBubbles = [];
		jQuery.each(twitchVoters, function(voter, pic) {
			if (pic === 'none') {
				pic = 'http://dailies.gg/wp-content/uploads/2017/03/default_pic.jpg'
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
			var thisBubble = <img key={voter} className="voterBubble" src={pic} title={voter} />
			twitchVoterBubbles.push(thisBubble);
		});
		return (
			<div className="VoterInfoBox">
				{voterBubbles}{twitchVoterBubbles}
			</div>
		)
	}
}