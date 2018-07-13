import React from "react";
import ReactDOM from 'react-dom';
import VoterInfoBox from "./VoterInfoBox.jsx";

export default class ContenderVoteboard extends React.Component{
	constructor() {
		super();
		var postData = contenderVoteboardData.postData;
		this.state = {};
		this.updateVoteCount = this.updateVoteCount.bind(this);
	}

	updateVoteCount() {
		var boundThis = this;
		var currentState = this.state;
		let endOfRestUsableTimestamp = contenderVoteboardData.resetTime.indexOf('+');
		if (endOfRestUsableTimestamp === -1) {
			var restUsableTimestamp = contenderVoteboardData.resetTime;
		} else {
			var restUsableTimestamp = contenderVoteboardData.resetTime.substring(0, endOfRestUsableTimestamp);
		}
		let liveDataQuery = dailiesGlobalData.thisDomain + '/wp-json/wp/v2/posts?categories_exclude=4&per_page=50&after=' + restUsableTimestamp;
		jQuery.get({
			url: liveDataQuery,
			dataType: 'json',
			success: function(data) {
				currentState.postData = data;
				boundThis.setState(currentState);
			}
		});
	}

	componentDidMount() {
		this.updateVoteCount();
		window.setInterval(this.updateVoteCount, 1000);
	}

	render() {
		var allPosts = this.state.postData;
		if (allPosts === undefined) {
			allPosts = []; //postData is undefined until updateVoteCount runs for the first time, so we need this
		}
		function postsByID(a,b) {
			return a.id - b.id; //they need to correspond to the !voteX numbering, which is ordered by postID
		}
		var sortedPosts = allPosts.sort(postsByID);
		var contenders = [];
		jQuery.each(sortedPosts, function(index, data) {
			if (data.categories[0] === 1125) {
				contenders.push(sortedPosts[index]['postDataObj']); //We only want contenders, obviously
			}
		});
		var voterBoxes = contenders.map(function(contender, index) {
			return(
				<div key={index} className="voterBoxContainer">
					<VoterInfoBox key={'contender' + index} voterData={contender.voterData} guestlist={contender.guestlist} twitchVoters={contender.twitchVoters} />
					<div key={"voterBoxHeader" + index} className="voterBoxHeader">{index + 1}</div>
				</div>
			)
		})
		return(
			<section id="ContenderVoteboard">
				{voterBoxes}
			</section>
		)
	}
}

ReactDOM.render(
	<ContenderVoteboard />,
	document.getElementById('contenderVoteboardApp')
);