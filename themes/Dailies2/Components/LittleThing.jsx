import React from "react";
import Titlebox from "./Titlebox.jsx";
import VoteBox from "./VoteBox.jsx";
import StarBox from "./StarBox.jsx";
import LiveAdminControls from "./LiveAdminControls.jsx";
import AuthorBubble from "./AuthorBubble.jsx";
import LittleThingEmbedder from "./LittleThingEmbedder.jsx";

export default class LittleThing extends React.Component{
	constructor() {
		super();
		this.state = {
			isEmbedding: false,
		}
		this.toggleEmbed = this.toggleEmbed.bind(this);
		this.vote = this.vote.bind(this);
	}

	toggleEmbed(e) {
		e.preventDefault();
		var currentState = this.state;
		currentState.isEmbedding = !currentState.isEmbedding;
		this.setState(currentState);
	}

	vote() {
		let userID = this.props.userData.userID.toString(10);
		let rep = parseFloat(this.state.rep);
		var votecount = parseFloat(this.state.votecount);
		let guestlist = this.state.guestlist;
		let clientIP = this.props.userData.clientIP;
		let repTime = this.state.repTime;
		var currentState = this.state;
		if (userID !== "0") {
			var voteledger = this.state.voteledger;
			if( Object.keys(voteledger).indexOf(userID) > -1 ) {
				currentState.votecount = (votecount - voteledger[userID]).toFixed(1);
				delete currentState.voteledger[userID];
				if (jQuery.inArray(clientIP, guestlist) > -1){
					let guestIndex = jQuery.inArray(clientIP, guestlist);
					guestlist.splice(guestIndex, 1);
					if (guestlist.length === 0) {
						guestlist = '';
					}
					currentState.guestlist = guestlist;
					currentState.votecount = (currentState.votecount - .1).toFixed(1);
				}
			} else {
				var currentTime = Date.now() / 1000;
				if (currentTime > repTime) {rep = rep + .1};
				currentState.voteledger[userID] = rep;
				currentState.votecount = (votecount + rep).toFixed(1);
				currentState.repTime = {0: currentTime};
				currentState.rep = rep;
			}
		} else if (jQuery.inArray(clientIP, guestlist) > -1){
			let guestIndex = jQuery.inArray(clientIP, guestlist);
			guestlist.splice(guestIndex, 1);
			if (guestlist.length === 0) {
				guestlist = '';
			}
			currentState.guestlist = guestlist;
			currentState.votecount = (votecount - .1).toFixed(1);
		} else {
			if (guestlist === '' || guestlist == null) {
				var newGuestlist = [clientIP];
			} else {
				guestlist.push(clientIP);
				var newGuestlist = guestlist;
			}
			currentState.guestlist = newGuestlist;
			currentState.votecount = (votecount + .1).toFixed(1);
		}
		currentState.justVoted = true;
		this.setState(currentState);
		var postID = this.props.postData.id;
		jQuery('#LittleThing' + this.props.postData.id).find('.voteIcon').addClass("replaceHold");
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				id: this.props.postData.id,
				action: 'official_vote',
				vote_nonce: liveData.nonce,
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				//console.log(data);
			}
		});
	}

	componentDidUpdate() {
		jQuery('#livePostsLoop').isotope({
			itemSelector: '.LittleThing',
			masonry: {
				gutter: 18,
				horizontalOrder: true,
			},
		});
	}
	render() {
		var linkout = '';
		if (this.props.postData.voteledger === '') {
			var voteledger = [];
		} else {
			var voteledger = this.props.postData.voteledger;
		}
		if (this.state.justVoted != true) {
			this.state = {
				votecount: this.props.postData.votecount,
				voteledger,
				guestlist: this.props.postData.guestlist,
				rep: dailiesGlobalData.userData.userRep,
				repTime: dailiesGlobalData.userData.userRepTime,
				isEmbedding: this.state.isEmbedding,
			};
		} else {
			this.state.justVoted = false;
		}
		var twitchcode = this.props.postData.EmbedCodes.TwitchCode;
		var youtubecode = this.props.postData.EmbedCodes.YouTubeCode;
		var gfycode = this.props.postData.EmbedCodes.GFYtitle;
		var embedcode = this.props.postData.EmbedCodes.EmbedCode;
		if (twitchcode !== '') {
			linkout = 'https://clips.twitch.tv/' + twitchcode;
		} else if (youtubecode !== '') {
			linkout = "https://youtube.com/watch?v=" + this.props.postData.EmbedCodes.YouTubeCode;
		} else if (gfycode !== '') {
			linkout = "http://gfycat.com/" + gfycode;
		}
		if (this.state.isEmbedding === true) {
			var embedder = <LittleThingEmbedder embeds={this.props.postData.EmbedCodes} />;
		} else {
			var embedder = '';
		}
		if (dailiesGlobalData.userData.userID === 1) {
			var adminControls = <LiveAdminControls thisID={this.props.postData.id} postTrasher={this.props.postTrasher} />
		} else {
			var adminControls = '';
		}
		return(
			<article className="LittleThing" id={'LittleThing' + this.props.postData.id} >
				<div className="littleThingTop">
					<a className="littleThingSourceImgLink" href={dailiesGlobalData.thisDomain + '/source/' + this.props.postData.taxonomies.source[0].slug}><img className="sourcepic" src={this.props.postData.taxonomies.source[0].logo} /></a>
					<Titlebox title={this.props.postData.title} score={this.state.votecount} linkout={linkout} toggleEmbed={this.toggleEmbed} />
					<VoteBox thisID={this.props.postData.id} userData={this.props.userData} voteledger={this.state.voteledger} guestlist={this.state.guestlist} vote={this.vote}/>
				</div>
				{embedder}
				<div className="littleThingBottom">
					<StarBox stars={this.props.postData.taxonomies.stars} />
					{adminControls}
					<AuthorBubble authorData={this.props.postData.author} />
				</div>
			</article>
		)
	}
}