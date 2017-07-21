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
		return(
			<article className="LittleThing">
				<div className="littleThingTop">
					<a className="littleThingSourceImgLink" href={dailiesGlobalData.thisDomain + '/source/' + this.props.postData.taxonomies.source[0].slug}><img className="sourcepic" src={this.props.postData.taxonomies.source[0].logo} /></a>
					<Titlebox title={this.props.postData.title} score={this.props.postData.votecount} linkout={linkout} toggleEmbed={this.toggleEmbed} />
					<VoteBox thisID="{this.props.thingData.id}" user={this.props.user} voteledger="{this.props.voteData.voteledger}" guestlist="{guestlist}" vote="{this.vote}"/>
				</div>
				{embedder}
				<div className="littleThingBottom">
					<StarBox stars={this.props.postData.taxonomies.stars} />
					<LiveAdminControls thisID={this.props.postData.id} />
					<AuthorBubble authorData={this.props.postData.author} />
				</div>
			</article>
		)
	}
}