import React from "react";

export default class VotingMachine extends React.Component{
	render() {
		// console.log(this.props.voterData);
		
		let voters = Object.keys(this.props.voterData);

		let score = 0;
		let yeaVotersList = [];
		let nayVotersList = [];
		voters.forEach( (key) => {
			let thisScore = Number(this.props.voterData[key].weight);
			score = score + thisScore;
			if (thisScore > 0) {
				yeaVotersList.push(this.props.voterData[key]);
			} else {
				nayVotersList.push(this.props.voterData[key]);
			}
		});

		console.log(yeaVotersList, nayVotersList);

		let yeaVoters = Object.keys(yeaVotersList).map((key) => {
			var voterName = yeaVotersList[key]['name'];
			var voterPic = yeaVotersList[key]['picture'];
			let voterRep = yeaVotersList[key]['weight'];
			return (
				<img key={key} className="voterBubble" src={voterPic} title={`${voterName}: ${voterRep}`} onError={(e) => window.imageError(e, 'twitchVoter')} />
			)
		});

		let nayVoters = Object.keys(nayVotersList).map((key) => {
			var voterName = nayVotersList[key]['name'];
			var voterPic = nayVotersList[key]['picture'];
			let voterRep = nayVotersList[key]['weight'];
			return (
				<img key={key} className="voterBubble" src={voterPic} title={`${voterName}: ${voterRep}`} onError={(e) => window.imageError(e, 'twitchVoter')} />
			)
		});

		return (
			<div className="votingMachine">
				{nayVoters}
				<img src={`${dailiesGlobalData.thisDomain}/wp-content/uploads/2018/07/votenay.png`} className={`nayButton voteButton`} />
				<p className="score">{score > 0 ? `+${score}` : `${score}`}</p>
				<img src={`${dailiesGlobalData.thisDomain}/wp-content/uploads/2018/07/voteyea.png`} className={`yeaButton voteButton`} />
				{yeaVoters}
			</div>
		);
	}
};