import React from "react";
import WeedComments from './WeedComments.jsx';
import VoterInfoBox from './VoterInfoBox.jsx';
import VotingMachine from './Things/VotingMachine.jsx';
import SlugTitle from './Things/SlugTitle.jsx';


export default class TopFive extends React.Component{
	constructor() {
		super();
		this.state = {
			comments: [],
			commentsLoading: true,
			voters: [],
			votersLoading: true,
		}

		this.postComment = this.postComment.bind(this);
		this.yeaComment = this.yeaComment.bind(this);
		this.delComment = this.delComment.bind(this);
	}

	componentDidMount() {
		this.getComments();
		this.getVoters();
	}

	componentDidUpdate() {
		if (this.state.commentsLoading) {
			this.getComments();
		}
		if (this.state.votersLoading) {
			this.getVoters();
		}
	}

	getComments() {
		let queryURL = `${dailiesGlobalData.thisDomain}/wp-json/dailies-rest/v1/clipcomments/slug=${this.props.clipdata.slug}`
		let currentState = this.state;
		let boundThis = this;
		jQuery.get({
			url: queryURL,
			dataType: 'json',
			success: function(data) {
				currentState.comments = data;
				currentState.commentsLoading = false;
				boundThis.setState(currentState);
			}
		});
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

	postComment(commentObject) {
		let currentState = this.state;
		currentState.commentsLoading = true;
		this.setState(currentState);
		// let randomID = Math.round(Math.random() * 100);
		let boundThis = this;
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				slug: this.props.clipdata.slug,
				commentObject,
				action: 'post_comment',
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				// jQuery.each(currentState.comments, function(index,commentData) {
				// 	if (commentData.id == randomID) {
				// 		currentState.comments[index].id = data;
				// 	}
				// });
				// this.setState(currentState);
				let commentData = {
					comment: commentObject.comment,
					commenter: dailiesGlobalData.userData.userName,
					pic: dailiesGlobalData.userData.userPic,
					id: data,
					replytoid: commentObject.replytoid,
					slug: boundThis.props.clipdata.slug,
					score: 0,
					time: Date.now(),
				}
				currentState.comments.push(commentData);
				currentState.commentsLoading = false;
				boundThis.setState(currentState);
			}
		});
	}

	yeaComment(commentID) {
		let currentState = this.state;
		jQuery.each(currentState.comments, function(index, data) {
			if (data.id == commentID) {
				currentState.comments[index].score = Number(data.score) + 1;
			}
		})
		this.setState(currentState);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				commentID,
				action: 'yea_comment',
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

	delComment(commentID) {
		let currentState = this.state;
		jQuery.each(currentState.comments, function(index, commentData) {
			if (commentData === undefined) {return true;}
			if (commentID == commentData.id) {
				delete currentState.comments[index];
			}
		});
		this.setState(currentState);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				commentID,
				action: 'del_comment',
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

	render() {
		let thumbSrc = this.props.clipdata.thumb;
		if (thumbSrc === 'none') {
			thumbSrc = `${dailiesGlobalData.thisDomain}/wp-content/uploads/2018/09/default-clip-thumb.jpg`;
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
			// voters = <VoterInfoBox key={`voterInfoBox-${this.props.clipdata.slug}`} thisID={this.props.clipdata.slug} voterData={this.state.voters} twitchVoters={[]} guestlist={[]} addedVotes="0" />
			voters = <VotingMachine key={`votingMachine-${this.props.clipdata.slug}`} slug={this.props.clipdata.slug} voterData={this.state.voters} />		
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
			<div className="TopFive">
				<img src={thumbSrc} className="topfivethumb" />
				<div className="hopefuls-meta">
					<div className="hopefuls-title"><SlugTitle slug={this.props.clipdata.slug} type={this.props.clipdata.type} title={this.props.clipdata.title} /></div>
					{voters}
					<div className="hopefuls-data">{this.props.clipdata.views} views. {this.props.clipdata.source === "User Submit" ? "Submitted by" : "Clipped by"} {this.props.clipdata.clipper} about {timeAgo} {timeAgoUnit} ago. {vodlink}</div>
					<WeedComments key={this.props.clipdata.slug} slug={this.props.clipdata.slug} postComment={this.postComment} commentsLoading={this.state.commentsLoading} comments={this.state.comments} yeaComment={this.yeaComment} delComment={this.delComment} />
				</div>
			</div>
		)
	}
}