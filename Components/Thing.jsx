import React from "react";
import ThingHeader from './ThingHeader.jsx';
import EmbedBox from './EmbedBox.jsx';
import Tagbox from './Tagbox.jsx';
import Votebox from './Votebox.jsx';
import AttributionBox from './AttributionBox.jsx';
import AdminControls from './AdminControls.jsx';
import VoterInfoBox from './VoterInfoBox.jsx';

export default class Thing extends React.Component {
	constructor() {
		super();
		this.vote = this.vote.bind(this);
		this.declareWinner = this.declareWinner.bind(this);
		this.addScore = this.addScore.bind(this);
	}

	vote() {
		let userID = this.props.userData.userID.toString(10);
		let hash = dailiesGlobalData.userRow.hash;
		let rep = parseFloat(this.state.rep);
		var votecount = parseFloat(this.state.votecount);
		var currentState = this.state;
		let guestlist = this.state.guestlist;
		let clientIP = this.props.userData.clientIP;
		let repTime = this.state.repTime;
		if (userID !== "0") {
			var voteledger = this.state.voteledger;
			if (voteledger === '') {
				currentState.voteledger = [];
			}
			if ( Object.keys(voteledger).indexOf(userID) > -1 ) {
				currentState.votecount = (votecount - voteledger[userID]).toFixed(0);
				delete currentState.voteledger[userID];
				if (jQuery.inArray(clientIP, guestlist) > -1){
					let guestIndex = jQuery.inArray(clientIP, guestlist);
					guestlist.splice(guestIndex, 1);
					if (guestlist.length === 0) {
						guestlist = '';
					}
					currentState.guestlist = guestlist;
					currentState.votecount = (currentState.votecount - 1).toFixed(0);
				}
			} else if (Object.keys(voteledger).indexOf(hash) > -1) {
				currentState.votecount = (votecount - voteledger[hash]).toFixed(0);
				delete currentState.voteledger[hash];
				if (jQuery.inArray(clientIP, guestlist) > -1){
					let guestIndex = jQuery.inArray(clientIP, guestlist);
					guestlist.splice(guestIndex, 1);
					if (guestlist.length === 0) {
						guestlist = '';
					}
					currentState.guestlist = guestlist;
					currentState.votecount = (currentState.votecount - 1).toFixed(0);
				}
			} else {
				var currentTime = Date.now() / 1000;
				if (currentTime > repTime && rep < 100) {rep = rep + 1};
				currentState.voteledger[userID] = rep;
				currentState.votecount = (votecount + rep).toFixed(0);
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
			currentState.votecount = (votecount - 1).toFixed(0);
		} else if (Object.values(guestlist).indexOf(clientIP) > -1) {
			let thisGuestKey = Object.keys(guestlist).find(key => guestlist[key] === clientIP);
			delete guestlist[thisGuestKey];
			currentState.guestlist = guestlist;
			currentState.votecount = (votecount - 1).toFixed(0);
		} else {
			if (guestlist === '' || guestlist == null) {
				var newGuestlist = [clientIP];
			} else {
				let guestKeys = Object.keys(guestlist);
				let lastGuestKey = guestKeys[guestKeys.length - 1];
				let nextGuestKey = parseInt(lastGuestKey, 10) + 1;
				guestlist[nextGuestKey] = clientIP;
				var newGuestlist = guestlist;
			}
			currentState.guestlist = newGuestlist;
			currentState.votecount = (votecount + 1).toFixed(0);
		}
		this.setState(currentState);
		jQuery('#thing' + this.props.thingData.id).find('.voteIcon').addClass("replaceHold");
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				id: this.props.thingData.id,
				action: 'handle_vote',
				vote_nonce: dailiesMainData.nonce,
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

	declareWinner() {
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				id: this.props.thingData.id,
				action: 'declare_winner',
				vote_nonce: dailiesMainData.nonce,
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				console.log(this.props);
			}
		});
	}

	addScore(e) {
		if (e.which === 13) {
			var target = e.target;
			var scoreToAdd = parseFloat(target.value, 10);
			if (!isNaN(scoreToAdd)) {
				var currentState = this.state;
				currentState.votecount = parseFloat(currentState.votecount) + scoreToAdd;
				this.setState(currentState);
				jQuery.ajax({
					type: "POST",
					url: dailiesGlobalData.ajaxurl,
					dataType: 'json',
					data: {
						id: this.props.thingData.id,
						action: 'add_score',
						scoreToAdd,
						vote_nonce: dailiesMainData.nonce,
					},
					error: function(one, two, three) {
						console.log(one);
						console.log(two);
						console.log(three);
					},
					success: function(data) {
						console.log(data);
						target.value = '';
					}
				});
			} else {
				e.target.value = 'NaN!';
			}
		}
	}

	render() {
		this.state = {};
		this.state = this.props.voteData;
		this.state.rep = this.props.userData.userRep;
		this.state.repTime = this.props.userData.userRepTime;
		var thingID = 'thing' + this.props.thingData.id;
		var guestlist = [];
		if (this.props.voteData.guestlist) {
			guestlist = this.props.voteData.guestlist;
		}
		var WinnerBanner;
		var isWinner = false;
		for (var i = this.props.thingData.taxonomies.tags.length - 1; i >= 0; i--) {
			if (this.props.thingData.taxonomies.tags[i].slug === 'winners') {
				var WinnerBanner = <section className="WinnerBanner"><img src="https://dailies.gg/wp-content/uploads/2017/02/Winner-banner-black.jpg" className="winnerbannerIMG"></img></section>
				var isWinner = true;
			}
		}
		var adminControls;
		if (dailiesGlobalData.userData.userID === 1) {
			var adminControls = <AdminControls thisID={this.props.thingData.id} declareWinner={this.declareWinner} isWinner={isWinner} addScore={this.addScore} />
		}
		var embedCodes = this.props.thingData.EmbedCodes;
		var embedCodeKeys = Object.keys(embedCodes);
		for (var i = 0; i < embedCodeKeys.length; i++) {
			if (embedCodes[embedCodeKeys[i]] !== '') {
				var embedCode = embedCodes[embedCodeKeys[i]];
				var embedSource = embedCodeKeys[i];
			}
		}
		return(
			<article className="thing noise" id={thingID}>
				{WinnerBanner}
				<ThingHeader score={this.props.voteData.votecount} link={this.props.thingData.link} title={this.props.thingData.title}/>
				<EmbedBox thumbs={this.props.thingData.thumbs} embedCode={embedCode} embedSource={embedSource} />
				<Tagbox thisID={this.props.thingData.id} tags={this.props.thingData.taxonomies.tags} skills={this.props.thingData.taxonomies.skills} />
				<Votebox thisID={this.props.thingData.id} userData={this.props.userData} voteledger={this.props.voteData.voteledger} guestlist={guestlist} vote={this.vote} />
				<AttributionBox thisID={this.props.thingData.id} stars={this.props.thingData.taxonomies.stars} source={this.props.thingData.taxonomies.source} />
				{adminControls}
				<VoterInfoBox thisID={this.props.thingData.id} voterData={this.props.thingData.voterData} guestlist={this.props.thingData.guestlist} twitchVoters={this.props.thingData.twitchVoters} addedVotes={this.props.thingData.addedScore} />
			</article>
		)
	}
}