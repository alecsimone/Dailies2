import React from "react";
import ReactDOM from 'react-dom';
import HomeTop from './HomeTop.jsx';
import ChannelChanger from './ChannelChanger.jsx';
import CoHostsPanel from './CoHostsPanel.jsx';
import LivePostsLoop from './LivePostsLoop.jsx';

export default class Live extends React.Component{
	constructor() {
		super();
		this.state = {
			userData: dailiesGlobalData.userData,
			channels: liveData.channels,
			cohosts: liveData.cohosts,
			postData: liveData.postData,
			sort: false,
			cutPostIDs: [],
		}
		this.changeChannel = this.changeChannel.bind(this);
		this.updatePostData = this.updatePostData.bind(this);
		this.sortLive = this.sortLive.bind(this);
		this.postTrasher = this.postTrasher.bind(this);
		this.littleThingVote = this.littleThingVote.bind(this);
	}

	changeChannel(key) {
		let currentState = this.state;
		if (currentState.channels[key].active === false) {
			currentState.channels[key].active = true;
		} else {
			currentState.channels[key].active = false;
		}
		this.setState(currentState);
	}

	sortLive() {
		if (this.state.sort === true) {
			this.setState({sort: false});
		} else {
			this.setState({sort: true});
		}
	}

	littleThingVote(id) {
		let userID = this.state.userData.userID.toString(10);
		let rep = parseFloat(this.state.userData.userRep);
		var votecount = parseFloat(this.state.postData[id].votecount);
		let guestlist = this.state.postData[id].guestlist;
		let clientIP = this.state.userData.clientIP;
		let repTime = this.state.userData.userRepTime;
		var currentState = this.state;
		if (userID !== "0") {
			var voteledger = this.state.postData[id].voteledger;
			if( Object.keys(voteledger).indexOf(userID) > -1 ) {
				currentState.postData[id].votecount = (votecount - voteledger[userID]).toFixed(1);
				delete currentState.postData[id].voteledger[userID];
				if (jQuery.inArray(clientIP, guestlist) > -1) {
					let guestIndex = jQuery.inArray(clientIP, guestlist);
					guestlist.splice(guestIndex, 1);
					if (guestlist.length === 0) {
						guestlist = '';
					}
					currentState.postData[id].guestlist = guestlist;
					currentState.postData[id].votecount = (currentState.postData[id].votecount - .1).toFixed(1);
				}
			} else {
				var currentTime = Date.now() / 1000;
				if (currentTime > repTime + 24 * 60 * 60) {rep = rep + .1};
				currentState.postData[id].voteledger[userID] = rep;
				currentState.postData[id].votecount = (votecount + rep).toFixed(1);
				currentState.userData.userRepTime = {0: currentTime};
				currentState.userData.userRep = rep;
			}
		} else if (jQuery.inArray(clientIP, guestlist) > -1) {
			let guestIndex = jQuery.inArray(clientIP, guestlist);
			guestlist.splice(guestIndex, 1);
			if (guestlist.length === 0) {
				guestlist = '';
			}
			currentState.postData[id].guestlist = guestlist;
			currentState.postData[id].votecount = (votecount - .1).toFixed(1);
		} else {
			if (guestlist === '' || guestlist == null) {
				var newGuestlist = [clientIP];
			} else {
				guestlist.push(clientIP);
				var newGuestlist = guestlist;
			}
			currentState.postData[id].guestlist = newGuestlist;
			currentState.postData[id].votecount = (votecount + .1).toFixed(1);
		}
		this.setState(currentState);
		jQuery('#LittleThing' + id).find('.voteIcon').addClass("replaceHold");
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				id: id,
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

	updatePostData() {
		let currentDate = new Date();
		let tenDaysAgo = currentDate - 1000 * 60 * 60 * 24 * 10;
		tenDaysAgo = new Date(tenDaysAgo);
		let tenDaysAgoYear = tenDaysAgo.getFullYear().toString();
		let tenDaysAgoMonth = tenDaysAgo.getMonth() + 1;
		if (tenDaysAgoMonth < 10) {
			tenDaysAgoMonth = '0' + tenDaysAgoMonth;
		} else {
			tenDaysAgoMonth = tenDaysAgoMonth.toString();
		}
		let tenDaysAgoDay = tenDaysAgo.getDate();
		if (tenDaysAgoDay < 10) {
			tenDaysAgoDay = '0' + tenDaysAgoDay;
		} else {
			tenDaysAgoDay = tenDaysAgoDay.toString();
		}
		let tenDaysAgoFormatted = tenDaysAgoYear + '-' + tenDaysAgoMonth + '-' + tenDaysAgoDay + 'T00:00:00';
		let liveDataQuery = dailiesGlobalData.thisDomain + '/wp-json/wp/v2/posts?categories_exclude=4&per_page=50&after=' + tenDaysAgoFormatted;
		var currentState = this.state;
		var cutPosts = this.state.cutPostIDs;
		var boundThis = this;
		jQuery.get({
			url: liveDataQuery,
			dataType: 'json',
			success: function(data) {
				var newPostData = {};
				for (var i = 0; i < data.length; i++) {
					let postDataObject = JSON.parse(data[i].postDataObj);
					//Before we go any further, we need to check if the post has been cut client-side since the server update
					//So we're going to grab the state array holding the IDs of cut posts
					//And check if this post's ID is in it
					let currentID = postDataObject.id;
					if (cutPosts.indexOf(currentID) > -1) {
						//If it is, return, else keep going
						return;
					} else {
						//See who the server thinks has voted on the post
						let serverVoteLedger = postDataObject.voteledger;
						let serverGuestList = postDataObject.guestlist;
						if (serverGuestList === null) {serverGuestList = ''};
						//See who the client thinks has voted on the post. Basically we're checking to see if the user has voted since the server updated
						let localDataObject = currentState.postData[currentID];
						let localVoteLedger = localDataObject.voteledger;
						let localGuestList = localDataObject.guestlist;
						if (localGuestList === null) {localGuestList = ''};
						let userID = dailiesGlobalData.userData.userID;
						let clientIP = dailiesGlobalData.userData.clientIP;
						//Next we need a bunch of conditionals
						//If the user has voted on both the server and the client, just pass the object through normally
						if (dailiesGlobalData.userData.userID !== 0) { 
							if ( (localVoteLedger.hasOwnProperty(userID) && serverVoteLedger.hasOwnProperty(userID)) || (localVoteLedger.length === 0 && serverVoteLedger.length === 0) || (Object.keys(localVoteLedger).length === 0 && Object.keys(serverVoteLedger).length === 0) ) {
								newPostData[currentID] = postDataObject;
							}
							//If the user has voted on the client but not the server, add the user's rep to the score and pass
							if ( localVoteLedger.hasOwnProperty(userID) && (!serverVoteLedger.hasOwnProperty(userID) || serverVoteLedger.length === 0 || Object.keys(serverVoteLedger).length === 0) ) {
								console.log("1");
								console.log("local");
								console.log(localVoteLedger);
								console.log("server");
								console.log(serverVoteLedger);
								newPostData[currentID] = postDataObject;
								newPostData[currentID].votecount = parseFloat(newPostData[currentID].votecount) + parseFloat(currentState.userData.userRep);
								newPostData[currentID].voteledger[userID] = currentState.userData.userRep;
							}
							//If the user has voted on the server but not the client, subtract the user's rep from the score and pass
							if ( (!localVoteLedger.hasOwnProperty(userID) || localVoteLedger.length === 0 || Object.keys(localVoteLedger).length === 0) && serverVoteLedger.hasOwnProperty(userID)) {
								console.log("2");
								console.log("local");
								console.log(localVoteLedger);
								console.log("server");
								console.log(serverVoteLedger);
								newPostData[currentID] = postDataObject;
								newPostData[currentID].votecount = parseFloat(newPostData[currentID].votecount) - parseFloat(currentState.userData.userRep);
								delete newPostData[currentID].voteledger[userID]; 
							}
						} else {
							//Same trio of conditionals, but for IP addresses and the guestlist
							if ( (localGuestList.indexOf(clientIP) > -1 && serverGuestList.indexOf(clientIP) > -1) || (localGuestList === '' && serverGuestList === '') ) {
								newPostData[currentID] = postDataObject;
							}
							if ( localGuestList.indexOf(clientIP) > -1 && (serverGuestList.indexOf(clientIP) === -1 || serverGuestList === '') ) {
								console.log("3");
								console.log("local");
								console.log(localVoteLedger);
								console.log("server");
								console.log(serverVoteLedger);
								newPostData[currentID] = postDataObject;
								newPostData[currentID].votecount = parseFloat(newPostData[currentID].votecount) + .1;
							}
							if ( (localGuestList.indexOf(clientIP) === -1 || localGuestList === '') && serverGuestList.indexOf(clientIP) > -1) {
								console.log("4");
								console.log("local");
								console.log(localVoteLedger);
								console.log("server");
								console.log(serverVoteLedger);
								newPostData[currentID] = postDataObject;
								newPostData[currentID].votecount = parseFloat(newPostData[currentID].votecount) - .1;
							}
						}
					}
					//let postDataObj = JSON.parse(data[i].postDataObj);
					//currentState.postData[data[i].id] = postDataObj;
				}
				currentState.postData = newPostData;
				boundThis.setState(currentState);
			},
		});
	}

	postTrasher(id) {
		var currentState = this.state;
		delete currentState.postData[id];
		this.setState(currentState);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				id: id,
				action: 'post_trasher',
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

	componentDidMount() {
		window.setInterval(this.updatePostData, 3000);
		window.grid = jQuery('#livePostsLoop').isotope({
			itemSelector: '.LittleThing',
			masonry: {
				gutter: 18,
				horizontalOrder: true,
			},
		});
	}

	componentDidUpdate() {
		window.grid.isotope('reloadItems');
		jQuery('#livePostsLoop').isotope({
			itemSelector: '.LittleThing',
			masonry: {
				gutter: 18,
				horizontalOrder: true,
			},
		});
	}

	render() {
		var activeFilter = [];
		var postData = jQuery.extend({}, this.state.postData);
		jQuery.each(this.state.channels, function() {
			if (this.active === true) {
				activeFilter.push(this.slug);
			}
		})
		if (activeFilter.length > 0) {
			jQuery.each(postData, function() {
				if (activeFilter.indexOf(JSON.parse(this).taxonomies.source[0].slug) === -1) {
					delete postData[JSON.parse(this).id];
				}
			}) 
		}
		var CoHosts;
		if (this.state.cohosts.length !== 0) {
			var CoHosts = <CoHostsPanel cohosts={this.state.cohosts} />
		}
		return(
			<section id="Live">
				<HomeTop user={this.state.userData} />
				<ChannelChanger channels={this.state.channels} changeChannel={this.changeChannel} sortLive={this.sortLive} sort={this.state.sort} />
				{CoHosts}
				<LivePostsLoop userData={this.state.userData} postData={postData} sort={this.state.sort} postTrasher={this.postTrasher} vote={this.littleThingVote} />
			</section>
		)
	}
}

ReactDOM.render(
	<Live />,
	document.getElementById('liveApp')
);