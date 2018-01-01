import React from "react";

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
		return (
			<div className="VoterInfoBox">
				{voterBubbles}
			</div>
		)
	}
}