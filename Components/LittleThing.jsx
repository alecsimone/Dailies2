import React from "react";
import Titlebox from "./Titlebox.jsx";
import VoteBox from "./VoteBox.jsx";
import StarBox from "./StarBox.jsx";
import LiveAdminControls from "./LiveAdminControls.jsx";
import AuthorBubble from "./AuthorBubble.jsx";
import EmbedBox from "./EmbedBox.jsx";

export default class LittleThing extends React.Component{
	constructor() {
		super();
		this.state = {
			isEmbedding: false,
		}
		this.toggleEmbed = this.toggleEmbed.bind(this);
	}

	toggleEmbed(e) {
		e.preventDefault();
		var currentState = this.state;
		currentState.isEmbedding = !currentState.isEmbedding;
		this.setState(currentState);
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
		var twitchcode = this.props.postData.EmbedCodes.TwitchCode;
		var youtubecode = this.props.postData.EmbedCodes.YouTubeCode;
		var gfycode = this.props.postData.EmbedCodes.GFYtitle;
		var embedcode = this.props.postData.EmbedCodes.EmbedCode;
		var twittercode = this.props.postData.EmbedCodes.TwitterCode;
		if (twitchcode !== '') {
			linkout = 'https://clips.twitch.tv/' + twitchcode;
		} else if (youtubecode !== '') {
			linkout = "https://youtube.com/watch?v=" + this.props.postData.EmbedCodes.YouTubeCode;
		} else if (gfycode !== '') {
			linkout = "http://gfycat.com/" + gfycode;
		} else if (twittercode !== '') {
			linkout = "https://twitter.com/statuses/" + twittercode;
		}
		var embedCodes = this.props.postData.EmbedCodes;
		var embedCodeKeys = Object.keys(embedCodes);
		for (var i = 0; i < embedCodeKeys.length; i++) {
			if (embedCodes[embedCodeKeys[i]] !== '') {
				var embedCode = embedCodes[embedCodeKeys[i]];
				var embedSource = embedCodeKeys[i];
			}
		}
		if (this.state.isEmbedding === true) {
			var embedder = <EmbedBox embedCode={embedCode} embedSource={embedSource}/>;
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
					<Titlebox title={this.props.postData.title} score={this.props.postData.votecount} linkout={linkout} toggleEmbed={this.toggleEmbed} />
					<VoteBox thisID={this.props.postData.id} userData={this.props.userData} voteledger={this.props.postData.voteledger} guestlist={this.props.postData.guestlist} vote={this.props.vote}/>
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