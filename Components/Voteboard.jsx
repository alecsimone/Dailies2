import React from "react";
import ReactDOM from 'react-dom';

export default class Voteboard extends React.Component{
	constructor() {
		super();
		this.state = {
			yeaVoters: voteboardData.currentVotersList.yea,
			nayVoters: voteboardData.currentVotersList.nay,
		};
		this.updateVoteCount = this.updateVoteCount.bind(this);
	}

	updateVoteCount() {
		var boundThis = this;
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'get_chat_votes',
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				var currentState = boundThis.state;
				currentState.yeaVoters = data.yea;
				currentState.nayVoters = data.nay;
				boundThis.setState(currentState);
			}
		});
	}

	componentDidMount() {
		window.setInterval(this.updateVoteCount, 1000);
	}

	render() {
		var yeaVoters = this.state.yeaVoters.map(function(key) {
			return(
				<div className="yeaVoterName" key={key}>{key}</div>
			)
		});
		var nayVoters = this.state.nayVoters.map(function(key) {
			return(
				<div className="yeaVoterName" key={key}>{key}</div>
			)
		});
		var yeaVotes = this.state.yeaVoters.length;
		var nayVotes = this.state.nayVoters.length;
		var yeaPercent = 100 * yeaVotes / (yeaVotes + nayVotes);
			if (isNaN(yeaPercent)) {
				yeaPercent = 0;
			}
		var nayPercent = 100 * nayVotes / (yeaVotes + nayVotes);
			if (isNaN(nayPercent)) {
				nayPercent = 0;
			}
		return(
			<section id="Voteboard">
				<div id="voteboardTop">
					<div id="yeaCount" className="votetotal">
						<div className="percentage">{Math.round(yeaPercent)}%</div>
						<img src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2018/07/voteyea.png'} className="votebadge" id="voteYeaBadge" />
						{yeaVotes}
					</div>
					<div id="nayCount" className="votetotal">
						{nayVotes}
						<img src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2018/07/votenay.png'} className="votebadge" id="voteYeaBadge" />
						<div className="percentage">{Math.round(nayPercent)}%</div>
					</div>
				</div>
				<div id="voteboardBot">
					<div id="yeaList" className="voterlist">
						{yeaVoters}
					</div>
					<div id="nayList" className="voterlist">
						{nayVoters}
					</div>
				</div>
			</section>
		)
	}
}

ReactDOM.render(
	<Voteboard />,
	document.getElementById('voteboardApp')
);