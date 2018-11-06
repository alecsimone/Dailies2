import React from "react";
import ReactDOM from 'react-dom';
import HomeTop from './HomeTop.jsx';
import ChannelChanger from './ChannelChanger.jsx';
import StageFilter from './StageFilter.jsx';
import CoHostsPanel from './CoHostsPanel.jsx';
import LivePostsLoop from './LivePostsLoop.jsx';
import SubmissionsOpenToggle from './SubmissionsOpenToggle.jsx';

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
			stage: 'All'
		}
		this.changeChannel = this.changeChannel.bind(this);
		this.updatePostData = this.updatePostData.bind(this);
		this.sortLive = this.sortLive.bind(this);
		this.postTrasher = this.postTrasher.bind(this);
		this.postPromoter = this.postPromoter.bind(this);
		this.postDemoter = this.postDemoter.bind(this);
		this.littleThingVote = this.littleThingVote.bind(this);
		this.stageChange = this.stageChange.bind(this);
		this.resetLive = this.resetLive.bind(this);
	}

	changeChannel(key) {
		let currentState = this.state;
		var activeCount = 0;
		jQuery.each(currentState.channels, function(channel, attributes) {
				if (attributes.active === true) {
					activeCount++;
				}
		});
		if (window.ctrlIsPressed === false) {
			jQuery.each(currentState.channels, function(channel, attributes) {
				if (key != channel) {
					attributes.active = false;
				}
			});
		}
		if (currentState.channels[key].active === false || activeCount > 1) {
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

	stageChange(e) {
		var currentState = this.state;
		currentState.stage = e.target.id;
		this.setState(currentState);
	}

	littleThingVote(id) {
		let userID = this.state.userData.userID.toString(10);
		let userHash = this.state.userData.hash;
		let rep = parseFloat(this.state.userData.userRep);
		var votecount = parseFloat(this.state.postData[id].votecount);
		let guestlist = this.state.postData[id].guestlist;
		let clientIP = this.state.userData.clientIP;
		let repTime = this.state.userData.userRepTime;
		var currentState = this.state;
		if (userID !== "0") {
			var voteledger = this.state.postData[id].voteledger;
			if( Object.keys(voteledger).indexOf(userID) > -1 ) {
				currentState.postData[id].votecount = (votecount - voteledger[userID]).toFixed(0);
				var currentScoreLastDigit = currentState.postData[id].votecount.substring(currentState.postData[id].votecount.length - 1);
				if (currentScoreLastDigit === '0') {
					currentState.postData[id].votecount = currentState.postData[id].votecount.substring(0, currentState.postData[id].votecount.length - 2);
				};
				delete currentState.postData[id].voteledger[userID];
				delete currentState.postData[id].voterData[userID];
				if (jQuery.inArray(clientIP, guestlist) > -1) {
					let guestIndex = jQuery.inArray(clientIP, guestlist);
					guestlist.splice(guestIndex, 1);
					if (guestlist.length === 0) {
						guestlist = '';
					}
					currentState.postData[id].guestlist = guestlist;
					currentState.postData[id].votecount = (currentState.postData[id].votecount - 1).toFixed(0);
				}
			} else if (Object.keys(voteledger).indexOf(userHash) > -1) {
				currentState.postData[id].votecount = (votecount - voteledger[userHash]).toFixed(0);
				delete currentState.postData[id].voteledger[userHash];
				delete currentState.postData[id].voterData[userHash];
				if (jQuery.inArray(clientIP, guestlist) > -1){
					let guestIndex = jQuery.inArray(clientIP, guestlist);
					guestlist.splice(guestIndex, 1);
					if (guestlist.length === 0) {
						guestlist = '';
					}
					currentState.postData[id].guestlist = guestlist;
					currentState.postData[id].votecount = (currentState.votecount - 1).toFixed(0);
				}
			} else {
				var currentTime = Date.now() / 1000;
				if (currentTime > repTime + 24 * 60 * 60 && rep < 100) {rep = rep + 1};
				currentState.postData[id].voteledger[userID] = rep;
				currentState.postData[id].voterData[userID] = {
					name: dailiesGlobalData.userData.userName,
					picture: dailiesGlobalData.userData.userPic,
				}
				currentState.postData[id].votecount = (votecount + rep).toFixed(1);
				var currentScoreLastDigit = currentState.postData[id].votecount.substring(currentState.postData[id].votecount.length - 1);
				if (currentScoreLastDigit === '0') {
					currentState.postData[id].votecount = currentState.postData[id].votecount.substring(0, currentState.postData[id].votecount.length - 2);
				};
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
			currentState.postData[id].votecount = (votecount - 1).toFixed(1);
			var currentScoreLastDigit = currentState.postData[id].votecount.substring(currentState.postData[id].votecount.length - 1);
			if (currentScoreLastDigit === '0') {
				currentState.postData[id].votecount = currentState.postData[id].votecount.substring(0, currentState.postData[id].votecount.length - 2);
			};
		} else {
			if (guestlist === '' || guestlist == null) {
				var newGuestlist = [clientIP];
			} else {
				guestlist.push(clientIP);
				var newGuestlist = guestlist;
			}
			currentState.postData[id].guestlist = newGuestlist;
			currentState.postData[id].votecount = (votecount + 1).toFixed(1);
			var currentScoreLastDigit = currentState.postData[id].votecount.substring(currentState.postData[id].votecount.length - 1);
			if (currentScoreLastDigit === '0') {
				currentState.postData[id].votecount = currentState.postData[id].votecount.substring(0, currentState.postData[id].votecount.length - 2);
			};
		}
		this.setState(currentState);
		let thisLittleThing = jQuery(`#LittleThing${id}`);
		if (thisLittleThing.hasClass('votedFor')) {
			thisLittleThing.removeClass('votedFor');
		} else {
			thisLittleThing.addClass('votedFor');
		}
		jQuery('#LittleThing' + id).find('.voteIcon').addClass("replaceHold");
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				id: id,
				action: 'handle_vote',
				vote_nonce: liveData.nonce,
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

	updatePostData() {
		let endOfRestUsableTimestamp = liveData.wordpressUsableTime.indexOf('+');
		if (endOfRestUsableTimestamp === -1) {
			var restUsableTimestamp = liveData.wordpressUsableTime;
		} else {
			var restUsableTimestamp = liveData.wordpressUsableTime.substring(0, endOfRestUsableTimestamp);
		}
		let liveDataQuery = dailiesGlobalData.thisDomain + '/wp-json/wp/v2/posts?categories_exclude=4&per_page=50&after=' + restUsableTimestamp;
		var currentState = this.state;
		var cutPosts = this.state.cutPostIDs;
		var boundThis = this;
		jQuery.get({
			url: liveDataQuery,
			dataType: 'json',
			success: function(data) {
				var newPostData = {};
				for (var i = 0; i < data.length; i++) {
					let postDataObject = data[i].postDataObj;
					//Before we go any further, we need to check if the post has been cut client-side since the server update
					//So we're going to grab the state array holding the IDs of cut posts
					//And check if this post's ID is in it
					let currentID = postDataObject.id;
					if (cutPosts.indexOf(currentID) > -1) {
						//If it is, return, else keep going
						continue;
					} else {
						//See who the server thinks has voted on the post
						let serverVoteLedger = postDataObject.voteledger;
						let serverGuestList = postDataObject.guestlist;
						if (serverGuestList === null) {serverGuestList = ''};
						//See who the client thinks has voted on the post. Basically we're checking to see if the user has voted since the server updated
						let localDataObject = currentState.postData[currentID];
						if (localDataObject === undefined) {
							newPostData[currentID] = postDataObject;
							continue;
						}
						let localVoteLedger = localDataObject.voteledger;
						let localGuestList = localDataObject.guestlist;
						if (localGuestList === null) {localGuestList = ''};
						let userID = dailiesGlobalData.userData.userID;
						let clientIP = dailiesGlobalData.userData.clientIP;
						let hash = dailiesGlobalData.userData.hash;
						//Next we need a bunch of conditionals
						//If the user has voted on both the server and the client, just pass the object through normally
						if (dailiesGlobalData.userData.userID !== 0) { 
							if ( ( (localVoteLedger.hasOwnProperty(userID) || localVoteLedger.hasOwnProperty(hash)) && (serverVoteLedger.hasOwnProperty(userID) || serverVoteLedger.hasOwnProperty(hash)) ) || (localVoteLedger.length === 0 && serverVoteLedger.length === 0) || (Object.keys(localVoteLedger).length === 0 && Object.keys(serverVoteLedger).length === 0) ) {
								newPostData[currentID] = postDataObject;
							}
							//If the user has voted on the client but not the server, add the user's rep to the score and pass
							if ( (localVoteLedger.hasOwnProperty(userID) || localVoteLedger.hasOwnProperty(hash)) && ( (!serverVoteLedger.hasOwnProperty(userID) && !serverVoteLedger.hasOwnProperty(hash)) || serverVoteLedger.length === 0 || Object.keys(serverVoteLedger).length === 0) ) {
								newPostData[currentID] = postDataObject;
								newPostData[currentID].votecount = parseFloat(newPostData[currentID].votecount) + parseFloat(currentState.userData.userRep);
								newPostData[currentID].voteledger[userID] = currentState.userData.userRep;
							}
							//If the user has voted on the server but not the client, subtract the user's rep from the score and pass
							if ( ( (!localVoteLedger.hasOwnProperty(userID) && !localVoteLedger.hasOwnProperty(hash)) || localVoteLedger.length === 0 || Object.keys(localVoteLedger).length === 0) && (serverVoteLedger.hasOwnProperty(userID) || serverVoteLedger.hasOwnProperty(hash)) ) {
								newPostData[currentID] = postDataObject;
								newPostData[currentID].votecount = parseFloat(newPostData[currentID].votecount) - parseFloat(currentState.userData.userRep);
								delete newPostData[currentID].voteledger[userID]; 
							}
							if ( ( (!localVoteLedger.hasOwnProperty(userID) && !localVoteLedger.hasOwnProperty(hash)) && (localVoteLedger.length !== 0 || Object.keys(localVoteLedger).length !== 0)) && (!serverVoteLedger.hasOwnProperty(userID) && !serverVoteLedger.hasOwnProperty(hash)) ) {
								newPostData[currentID] = postDataObject;
							}
						} else {
							//Same trio of conditionals, but for IP addresses and the guestlist
							if ( (localGuestList.indexOf(clientIP) > -1 && serverGuestList.indexOf(clientIP) > -1) || (localGuestList === '' && serverGuestList === '') ) {
								newPostData[currentID] = postDataObject;
							}
							if ( localGuestList.indexOf(clientIP) > -1 && (serverGuestList.indexOf(clientIP) === -1 || serverGuestList === '') ) {
								newPostData[currentID] = postDataObject;
								newPostData[currentID].votecount = parseFloat(newPostData[currentID].votecount) + 1;
							}
							if ( (localGuestList.indexOf(clientIP) === -1 || localGuestList === '') && serverGuestList.indexOf(clientIP) > -1) {
								newPostData[currentID] = postDataObject;
								newPostData[currentID].votecount = parseFloat(newPostData[currentID].votecount) - 1;
							}
						}
					}
				}
				currentState.postData = newPostData;
				boundThis.setState(currentState);
			},
		});
	}

	postTrasher(id) {
		var currentState = this.state;
		delete currentState.postData[id];
		currentState.cutPostIDs.push(id);
		this.setState(currentState);
		window.playAppropriateKillSound();
	}

	postDemoter(id) {
		var currentState = this.state;
		var currentCategory = currentState.postData[id]['categories'];
		if (currentCategory === 'Prospects') {
			this.postTrasher(id);
		}
		if (currentCategory === 'Contenders') {
			// currentState.postData[id]['categories'] = 'Prospects';
			this.postTrasher(id);
		}		
		if (currentCategory === 'Nominees') {
			currentState.postData[id]['categories'] = 'Contenders';
		}
		this.setState(currentState);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				id: id,
				action: 'post_demoter',
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

	postPromoter(id) {
		var currentState = this.state;
		var currentCategory = currentState.postData[id]['categories'];
		if (currentCategory === 'Prospects') {
			currentState.postData[id]['categories'] = 'Contenders'
		}
		if (currentCategory === 'Contenders') {
			currentState.postData[id]['categories'] = 'Nominees'
		}		
		if (currentCategory === 'Nominees') {
			console.log("you can't promote that any further");
		}
		this.setState(currentState);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				id: id,
				action: 'post_promoter',
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
		window.playAppropriatePromoSound();
	}

	resetLive() {
		var date = Date.now();
		if (confirm('Are you sure you want to reset the posts?')) {
			console.log("OK, we'll reset them.");
			var currentState = this.state;
			currentState.postData = {};
			this.setState(currentState);
			jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				timestamp: date,
				action: 'reset_live',
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				var dateObj = new Date(date - 21600000);
				var almostUsableDate = dateObj.toISOString();
				var endOfUsableDate = almostUsableDate.indexOf('.');
				var usableDate = almostUsableDate.substring(0, endOfUsableDate);
				liveData.wordpressUsableTime = usableDate;
			}
		});
		} else {
			console.log("OK cool, we'll just keep these then.");
		}
	}

	componentDidMount() {
		var refreshRate = 3000;
		if (dailiesGlobalData.userData.userRole === 'administrator') {
			refreshRate = 1000;
		}
		window.setInterval(this.updatePostData, refreshRate);
	}

	render() {
		var activeFilter = [];
		var postData = jQuery.extend({}, this.state.postData);
		var unfilteredPostCount = Object.keys(postData).length;
		//First we're gonna go through each channel and see if any are active
		jQuery.each(this.state.channels, function() {
			if (this.active === true) {
				activeFilter.push(this.slug);
			}
		})
		//If there are any channels chosen, we're gonna go through each post and remove anything that's not from that channel
		if (activeFilter.length > 0) {
			jQuery.each(postData, function() {
				if (activeFilter.indexOf(this.taxonomies.source[0].slug) === -1) {
					delete postData[this.id];
				}
			}) 
		}

		var prospectPostData = {};
		var contenderPostData = {};
		var finalistPostData = {};
		var nomineePostData = {};

		jQuery.each(postData, function(id) {
			if (this.categories === 'Prospects') {
				prospectPostData[id] = this;
			} else if (this.categories === 'Contenders') {
				contenderPostData[id] = this;
			} else if (this.categories === 'Finalists') {
				finalistPostData[id] = this;
			} else if (this.categories === 'Nominees') {
				nomineePostData[id] = this;
			}
		});

		var stages = ['Nominees', 'Finalists', 'Contenders', 'Prospects'];
		var userData = this.state.userData;
		var sort = this.state.sort
		var postTrasher = this.postTrasher;
		var postPromoter = this.postPromoter;
		var postDemoter = this.postDemoter;
		var littleThingVote = this.littleThingVote;
		var highlightPost = this.highlightPost;
		var stageLoops = stages.map(function(stageName) {
			if (stageName === 'Prospects') {
				var stagePostData = prospectPostData;
			} else if (stageName === 'Contenders') {
				var stagePostData = contenderPostData;
			} else if (stageName === 'Finalists') {
				var stagePostData = finalistPostData;
			} else if (stageName === 'Nominees') {
				var stagePostData = nomineePostData;
			}
			var thisStagePostCount = Object.keys(stagePostData).length;
			if (thisStagePostCount === 0) {
				return
			}
			return (
				<LivePostsLoop key={stageName} stage={stageName} userData={userData} postData={stagePostData} sort={sort} postTrasher={postTrasher} postPromoter={postPromoter} postDemoter={postDemoter} vote={littleThingVote} unfilteredPostCount={unfilteredPostCount} highlightPost={highlightPost} />
			)
		});
		if (Object.keys(postData).length === 0) {
			var stageLoops = <div className="thatsAll">No contenders yet for today. Want to <a href="https://dailies.gg/submit/">submit</a> one?</div>
		}
		
		var CoHosts;
		if (this.state.cohosts.length !== 0) {
			var CoHosts = <CoHostsPanel cohosts={this.state.cohosts} />
		}

		if (dailiesGlobalData.userData.userID === 1) {
			var resetLiveButton = <img className="resetLive" onClick={this.resetLive} src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/12/reset-icon.png'} />
		} else {
			var resetLiveButton = '';
		}

		return(
			<section id="Live">
				<HomeTop user={this.state.userData} />
				{CoHosts}
				<section id="LivePostsLoops">
					{stageLoops}
				</section>
				{resetLiveButton}
			</section>
		)
	}
}

//				<ChannelChanger channels={this.state.channels} changeChannel={this.changeChannel} sortLive={this.sortLive} sort={this.state.sort} />


ReactDOM.render(
	<Live />,
	document.getElementById('liveApp')
);