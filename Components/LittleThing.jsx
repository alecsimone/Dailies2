import React from "react";
import Titlebox from "./Titlebox.jsx";
import VoteBox from "./VoteBox.jsx";
import StarBox from "./StarBox.jsx";
import LiveAdminControls from "./LiveAdminControls.jsx";
import AuthorBubble from "./AuthorBubble.jsx";
import EmbedBox from "./EmbedBox.jsx";
import VoterInfoBox from "./VoterInfoBox.jsx";

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
		if (dailiesGlobalData.userData.userRole === 'administrator' || dailiesGlobalData.userData.userRole === 'editor' || dailiesGlobalData.userData.userRole === 'author' || dailiesGlobalData.userData.userRole === 'editor' || dailiesGlobalData.userData.userRole === 'contributor' ) {
			var adminControls = <LiveAdminControls thisID={this.props.postData.id} postTrasher={this.props.postTrasher} postPromoter={this.props.postPromoter} postDemoter={this.props.postDemoter} postCategory={this.props.postData.categories} authorID={this.props.postData.author.id} />
		} else {
			var adminControls = '';
		}
		var sourceSlug = this.props.postData.taxonomies.source[0].slug;
		var sourceLogo = this.props.postData.taxonomies.source[0].logo
		var starsLogo = this.props.postData.taxonomies.stars[0].logo;
		if (sourceSlug === 'user-submits' && this.props.postData.taxonomies.stars[0].logo !== '' && this.props.postData.taxonomies.stars[0].logo !== undefined) {
			sourceSlug = this.props.postData.taxonomies.stars[0].slug;
			sourceLogo = this.props.postData.taxonomies.stars[0].logo;
		}
		var VoterInfoBoxHolder;
		if (Object.keys(this.props.postData.voterData).length > 0 || this.props.postData.twitchVoters !== '') {
			VoterInfoBoxHolder = <VoterInfoBox voterData={this.props.postData.voterData} guestlist={this.props.postData.guestlist} twitchVoters={this.props.postData.twitchVoters} />
		} else {
			VoterInfoBoxHolder = <div className="VoterInfoBox" />
		}
		var votePrompter;
		if (this.props.stage === 'Contenders') {
			votePrompter = <div className="votePrompter">!vote{this.props.counter}</div>
		}

		let votedFor;
		if (this.props.postData.voteledger.hasOwnProperty(this.props.userData.userID) || this.props.postData.guestlist.hasOwnProperty(this.props.userData.clientIP) || this.props.postData.voteledger.hasOwnProperty(this.props.userData.hash)) {
			votedFor = 'votedFor';
		}

		return(
			<article className={`LittleThing brick ${votedFor}`} id={'LittleThing' + this.props.postData.id} >
				{votePrompter}
				<div className="littleThingTop">
					<a className="littleThingSourceImgLink" href={dailiesGlobalData.thisDomain + '/source/' + sourceSlug}><img className="sourcepic" src={sourceLogo} onError={(e) => window.imageError(e)} /></a>
					<Titlebox title={this.props.postData.title} linkout={linkout} score={this.props.postData.votecount} toggleEmbed={this.toggleEmbed} />
					<VoteBox thisID={this.props.postData.id} userData={this.props.userData} voteledger={this.props.postData.voteledger} guestlist={this.props.postData.guestlist} vote={this.props.vote}/>
				</div>
				{embedder}
				<div className="littleThingBottom">
					<StarBox stars={this.props.postData.taxonomies.stars} source={this.props.postData.taxonomies.source[0].slug} />
					{adminControls}
				</div>
				{VoterInfoBoxHolder}
			</article>
		)
	}
}