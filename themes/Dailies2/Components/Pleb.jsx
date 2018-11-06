import React from "react";
import VoterInfoBox from './VoterInfoBox.jsx';


export default class Pleb extends React.Component{
	constructor() {
		super();
		this.state = {
			votersLoading: true,
			voters: [],
		}
	}

	componentDidMount() {
		this.getVoters();
	}

	componentDidUpdate() {
		if (this.state.votersLoading) {
			this.getVoters();
		}
	}

	getVoters() {
		let queryURL = `${dailiesGlobalData.thisDomain}/wp-json/dailies-rest/v1/clipvoters/slug=${this.props.clipdata.slug}`
		let currentState = this.state;
		let boundThis = this;
		jQuery.get({
			url: queryURL,
			dataType: 'json',
			success: function(data) {
				currentState.voters = data;
				currentState.votersLoading = false;
				boundThis.setState(currentState);
			}
		});
	}

	render() {
		let sourcePic = this.props.clipdata.sourcepic;
		if (sourcePic === "unknown") {
			sourcePic = `${dailiesGlobalData.thisDomain}/wp-content/uploads/2017/07/rl-logo-med.png`;
		}

		let clipTime = new Date(this.props.clipdata.age);
		let currentTime = + new Date();
		let timeSince = currentTime - clipTime;
		if (timeSince < 3600000) {
			var timeAgo = Math.floor(timeSince / 1000 / 60);
			var timeAgoUnit = 'minutes';
			if (timeAgo === 1) {var timeAgoUnit = 'minute'};
		} else {
			var timeAgo = Math.floor(timeSince / 1000 / 60 / 60);
			var timeAgoUnit = 'hours';
			if (timeAgo === 1) {var timeAgoUnit = 'hour'};
		}

		let vodlink;
		if (this.props.clipdata.vodlink !== "none") {
			vodlink = <a href={this.props.clipdata.vodlink} className="vodlink" target="_blank">VOD Link</a>;
		}

		let voters;
		if (this.state.votersLoading) {
			voters = "Voters Loading..."
		} else {	
			voters = <VoterInfoBox key={`voterInfoBox-${this.props.clipdata.slug}`} thisID={this.props.clipdata.slug} voterData={this.state.voters} twitchVoters={[]} guestlist={[]} addedVotes="0" />
		}

		let rawTitle = this.props.clipdata.title;
		String.prototype.stripSlashes = function() {
		    return this.replace(/\\(.)/mg, "$1");
		}
		let title = rawTitle.stripSlashes();

		let link;
		if (this.props.clipdata.type === "twitch") {
			link = `https://clips.twitch.tv/${this.props.clipdata.slug}`;
		} else if (this.props.clipdata.type === "youtube" || this.props.clipdata.type === "ytbe") {
			link = `https://www.youtube.com/watch?v=${this.props.clipdata.slug}`;
		} else if (this.props.clipdata.type === "gfycat") {
			link = `https://gfycat.com/${this.props.clipdata.slug}`;
		} else if (this.props.clipdata.type === "twitter") {
			link = `https://twitter.com/statuses/${this.props.clipdata.slug}`;
		}

		return(
			<div className="Pleb">
				<img className="plebPic" src={sourcePic} />
				<div className="hopefuls-meta">
					<div className="hopefuls-title"><span className="hopefuls-score">(+{this.props.clipdata.score})</span> <a href={link} target="_blank">{title}</a></div>
					<div className="hopefuls-data">{this.props.clipdata.views} views. Clipped by {this.props.clipdata.clipper} about {timeAgo} {timeAgoUnit} ago. {vodlink}</div>
					{voters}
				</div>
			</div>
		)
	}
}