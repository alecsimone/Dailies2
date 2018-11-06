import React from "react";
import ReactDOM from 'react-dom';
import Streamlist from './Streamlist.jsx';
import Garden from './Garden.jsx';
import GardenHeader from './GardenHeader.jsx';
import GardenStatus from './GardenStatus.jsx';
import LoadMore from './LoadMore.jsx';
import {privateData} from '../Scripts/privateData.jsx';

/*
Welcome to the Secret Garden, the most complicated thing I've ever built.

In functions.php, a condition checks to see if we're on the Secret Garden page. If we are, it registers and enqueues the secret-garden bundle. This is made from the /Entries/secret-garden-entry.js file, which includes only /Scripts/secret-garden.js, which includes this file, as well as some functions used on the page.

React is not initiated on this page until we successfully query Twitch for clips. 
We take a list of events happening today (pulled from the schedule in functions.php and added to gardenData, a variable which is localized to this page when the scripts are enqueued), query each associated twitch channel for clips, put them all into a big list, and when all the queries are finished, we initiate React.

*/

export default class SecretGarden extends React.Component{
	constructor() {
		super();
		var cutSlugsArray = gardenData.cutSlugs;
		var cutSlugsObj = {};
		var cursors = {};
		var streams = Object.keys(gardenData.streamList);
		jQuery.each(streams, function() {
			if (gardenData.streamList[this].cursor !== '') {
				cursors[this] = gardenData.streamList[this].cursor;
			}
		});
		var queryHours = parseInt(gardenData.queryHours, 10);
		var queryPeriod;
		if (queryHours > 24) {
			queryPeriod = "week";
		} else {
			queryPeriod = "day";
		}
		this.state = {
			streamList: gardenData.streamList,
			clips: gardenData.clips,
			cursors,
			submissions: gardenData.submissionSeedlings,
			cutSlugs: gardenData.cutSlugs,
			streamFilter: ['Cuts'],
			statusMessage: 'Welcome to the Secret Garden',
			queryHours,
			queryPeriod,
		};
		this.addStream = this.addStream.bind(this);
		this.setState = this.setState.bind(this);
		this.cutSlug = this.cutSlug.bind(this);
		this.tagSlug = this.tagSlug.bind(this);
		this.voteSlug = this.voteSlug.bind(this);
		this.keepSlug = this.keepSlug.bind(this);
		this.cutSubmission = this.cutSubmission.bind(this);
		this.pushStreamQueryFurther = this.pushStreamQueryFurther.bind(this);
		this.filterStreams = this.filterStreams.bind(this);
		this.nukeSlug = this.nukeSlug.bind(this);
		this.updateSlugList = this.updateSlugList.bind(this);
	}

	cutSlug(slugObj, scope) {
		var currentState = this.state;
		currentState.cutSlugs[slugObj.slug] = slugObj;
		let clipLink = <a href={'http://clips.twitch.tv/' + slugObj.slug} target="_blank">{slugObj.slug}</a>
		let clipPretext = <p>You just cut </p>;
		currentState.statusMessage = <h4>{clipPretext} {clipLink}</h4>;
		console.log("You just cut http://clips.twitch.tv/" + slugObj.slug);
		this.setState(currentState);
		window.playAppropriateKillSound();
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'cut_slug',
				slugObj,
				scope,
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

	nukeSlug(slugObj) {
		console.log("we nuking " + slugObj.slug);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'nuke_slug',
				slugObj,
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				var nukeButton = jQuery("#nuker");
				nukeButton.fadeOut();
			}
		});
	}

	tagSlug(tagObj) {
		var currentState = this.state;
		var cutSlugs = currentState.cutSlugs;
		var slugToTag = tagObj.slugToTag;
		if (cutSlugs[slugToTag] !== undefined) {
			if (!cutSlugs[slugToTag].hasOwnProperty('tags')) {
				cutSlugs[slugToTag]['tags'] = [];
			}
			jQuery.each(tagObj.tags, function(index, tag) {
				cutSlugs[slugToTag]['tags'].push(tag);
			});
		} else {
			cutSlugs[slugToTag] = {
				VODBase: tagObj.VODBase,
				VODTime: tagObj.VODTime,
				createdAt: tagObj.createdAt,
				tags: tagObj.tags,
				likeIDs: '',
				slug: slugToTag,
				cutBoolean: false,
			}
		}
		this.setState(currentState);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'tag_slug',
				tagObj,
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

	voteSlug(slugObj) {
		jQuery('#' + slugObj.slug).find('.seedVoter').addClass('replaceHold');
		var currentState = this.state;
		if (currentState.cutSlugs[slugObj.slug] === undefined) {
			currentState.cutSlugs[slugObj.slug] = slugObj;
			this.setState(currentState);
		} else {
			var voteCheckIndex = currentState.cutSlugs[slugObj.slug].likeIDs.indexOf(dailiesGlobalData.userData.userID)
			if ( voteCheckIndex === -1) {
				currentState.cutSlugs[slugObj.slug].likeIDs.push(dailiesGlobalData.userData.userID);
				this.setState(currentState);
			} else {
				currentState.cutSlugs[slugObj.slug].likeIDs.splice(voteCheckIndex, 1);
				this.setState(currentState);
			}
		}
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'vote_slug',
				slugObj,
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

	keepSlug(slugObj, thingData) {
		console.log(slugObj);
		var currentState = this.state;
		currentState.cutSlugs[slugObj.slug] = slugObj;
		this.setState(currentState);
		window.playAppropriatePromoSound();
		var page = this;
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'plant_seed',
				slugObj,
				thingData,
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				console.log(data);
				if (Number.isInteger(data)) {
					//window.open(dailiesGlobalData.thisDomain + '/wp-admin/post.php?post=' + data + '&action=edit', '_blank');
					currentState.statusMessage = <h4>You have entered <a href={'https://clips.twitch.tv/' + slugObj.slug} target="_blank">{slugObj.slug}</a> into contention for tonight! See it at <a href="https://dailies.gg/live" target="_blank">Dailies.gg/Live</a></h4>;
					page.setState(currentState);
					jQuery.ajax({
						type: "POST",
						url: dailiesGlobalData.ajaxurl,
						dataType: 'json',
						data: {
							action: 'addSourceToPost',
							channelURL: slugObj.channelURL,
							channelPic: slugObj.channelPic,
							postID: data,
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
			}
		});
	}

	cutSubmission(metaInput) {
		var currentState = this.state;
		var indexToCut
		var cutClipURL
		jQuery.each(currentState.submissions, function(index, el) {
			if (el['meta_input'] === metaInput) {
				indexToCut = index;
				cutClipURL = el.clipURL
			}
		});
		currentState.submissions[indexToCut]['cut'] = 'cut';
		var statusUpdateIntro = 'You just cut ';
		var statusUpdateContent = <a href={cutClipURL} target="_blank">This Clip</a>
		currentState.statusMessage = <h4>{statusUpdateIntro} {statusUpdateContent}</h4>;
		console.log("You just cut " + cutClipURL);
		this.setState(currentState);
		window.playAppropriateKillSound();
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'cutSubmission',
				metaInput,
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

	addStream(e) {
		if (e.target.id === "addStreamBox" && e.which === 13) {
			var streamToAdd = e.target.value;
			var target = e.target;
		} else if (e.target.id === "addRL") {
			var streamToAdd = "Rocket League";
		} else {
			return
		}
		var currentState = this.state;
		var queryPeriod = currentState.queryPeriod;
		if (streamToAdd === "Rocket League") {
			var queryURL = `https://api.twitch.tv/kraken/clips/top?game=Rocket%20League&period=${queryPeriod}&limit=100`;
		} else {
			var queryURL = `https://api.twitch.tv/kraken/clips/top?channel=${streamToAdd}&period=${queryPeriod}&limit=100`;
		}
		currentState.streamList[streamToAdd] = {
			cursor: "",
			viewThreshold: "0",
		}
		var ajax = jQuery.ajax({
			type: 'GET',
			url: queryURL,
			headers: {
				'Client-ID' : privateData.twitchClientID,
				'Accept' : 'application/vnd.twitchtv.v5+json',
			},
			error: function(data) {
				console.log(data);
				if(target) {target.value = '';}
			},
			success: function(data) {
				if(target) {target.value = '';}
				let cursor = data._cursor;
				currentState.streamList[streamToAdd].cursor = cursor;
				currentState.cursors[streamToAdd] = cursor;
				jQuery.each(data.clips, function() {
					var unique = true;
					var addingSlug = this.slug;
					jQuery.each(currentState.clips, function() {
						var existingSlug = this.slug;
						if (addingSlug === existingSlug) {
							unique = false;
						}
					})
					if (unique) {
						currentState.clips.push(this);
					}
				});
				this.setState(currentState);
			}.bind(this),
		});
	}

	pushStreamQueryFurther() {
		var cursorsObject = this.state.cursors;
		var streams = Object.keys(cursorsObject);
		var datas = [];
		var queryPeriod = this.state.queryPeriod;
		var currentState = this.state;
		var boundThis = this;
		jQuery.each(streams, function() {
			var streamName = this;
			var currentCursor = cursorsObject[streamName];
			if (streamName === 'Rocket League') {
				var query = `https://api.twitch.tv/kraken/clips/top?game=Rocket%20League&period=${queryPeriod}&limit=100&cursor=${currentCursor}`
			} else {
				var query = `https://api.twitch.tv/kraken/clips/top?channel=${streamName}&period=${queryPeriod}&limit=100&cursor=${currentCursor}`
			}
			var ajax = jQuery.ajax({
				type: 'GET',
				url: query,
				headers: {
					'Client-ID' : privateData.twitchClientID,
					'Accept' : 'application/vnd.twitchtv.v5+json',
				},
				success: function(data) {
					let cursor = data._cursor;
					currentState.streamList[streamName].cursor = cursor;
					currentState.cursors[streamName] = cursor;
				}
			});
			datas.push(ajax);
		});

		jQuery.when.apply(jQuery, datas).then(function() {
			jQuery.each(datas, function() {
				var clipData = JSON.parse(this.responseText);
				jQuery.each(clipData.clips, function() {
					if (this.game === "Rocket League") {
						currentState.clips.push(this);
					}
				});
			});
			boundThis.setState(currentState);
		});
	}

	filterStreams(streamName) {
		var currentFilter = this.state.streamFilter;
		if (window.ctrlIsPressed === false) {
			if (currentFilter.indexOf(streamName) > -1) {
				currentFilter.splice(currentFilter.indexOf(streamName), 1)
			} else {
				currentFilter.push(streamName);
			}
		} else {
			var originalFilterLength = currentFilter.length;
			currentFilter = []
			var streamListLength = 0;
			jQuery.each(this.state.streamList, function(index) {
				streamListLength++;
				if (index !== streamName) {
					currentFilter.push(index);
				}
			})
			 if (originalFilterLength === streamListLength - 1) {
			 	currentFilter = [];
			 };
		}
		this.setState({streamFilter: currentFilter});
	}

	updateSlugList() {
		var currentState = this.state;
		var boundThis = this;
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'generateCutSlugsHandler',
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				currentState.cutSlugs = data;
				boundThis.setState(currentState);
			}
		});
	}

	componentDidMount() {
		this.updateSlugList();
		window.setInterval(this.updateSlugList, 60000);
	}

	render() {
		var clips = this.state.clips;
		var cutSlugs = this.state.cutSlugs;
		var filteredStreams = this.state.streamFilter;
		var streamList = this.state.streamList;
		var queryPeriod = this.state.queryPeriod;
		var queryHours = this.state.queryHours;

		var cutMoments = {};
		jQuery.each(cutSlugs, function() {
			if (this.cutBoolean === true || this.cutBoolean === "true") {
				if (cutMoments[this.VODBase] === undefined) {
					cutMoments[this.VODBase] = [parseInt(this.VODTime, 10)];
				} else {
					cutMoments[this.VODBase].push(parseInt(this.VODTime, 10));
				}
			}
		});

		var seedsToPlant = [];
		var alreadyQueuedSlugs = [];
		jQuery.each(clips, function() {
			var seedlingData = this;
			var slug = seedlingData.slug;
			var cutThisSlug = false;
			if (alreadyQueuedSlugs.indexOf(slug) > -1) {
				cutThisSlug = true;
			} else {
				alreadyQueuedSlugs.push(slug);
			}
			if (seedlingData.vod !== null) {
				var seedlingVODBase = seedlingData.vod.id;
				var seedlingVODLink = seedlingData.vod.url;
				var seedlingVODTime = window.vodLinkTimeParser(seedlingVODLink);
				var cutMomentsVODBases = Object.keys(cutMoments);
				jQuery.each(cutMomentsVODBases, function() {
					if (parseInt(this, 10) === parseInt(seedlingVODBase, 10)) {
						jQuery.each(cutMoments[this], function() {
							if (this + 15 >= seedlingVODTime && this - 15 <= seedlingVODTime) {
								cutThisSlug = true;
								if (filteredStreams.indexOf('Cuts') === -1) {
									cutThisSlug = false;
								} 
							}
						});
					}
				});
			}
			if (cutSlugs[slug] !== undefined) {
				if (cutSlugs[slug].cutBoolean === true || cutSlugs[slug].cutBoolean === 'true') {
					cutThisSlug = true;
					if (filteredStreams.indexOf('Cuts') === -1) {
						cutThisSlug = false;
					} 
				}
			}
			var channelURL = seedlingData.broadcaster.channel_url;
			var channelNameStartPosition = channelURL.lastIndexOf('/') + 1;
			var channelName = channelURL.substring(channelNameStartPosition);
			var lowerCaseStreamFilter = filteredStreams.map(function(streamName) {
				return streamName.toLowerCase();
			});
			if (lowerCaseStreamFilter.indexOf(channelName) > -1) {
				cutThisSlug = true;
			}
			if (filteredStreams.indexOf('Rocket League') > -1) {
				var streamsArray = [];
				jQuery.each(streamList, function(index) {
					if (index !== 'Rocket League') {
						streamsArray.push(index.toLowerCase())
					}
				});
				if (streamsArray.indexOf(channelName) === -1) {
					cutThisSlug = true;
				}
			}
			var clipTime = Date.parse(seedlingData.created_at);
			let currentTime = + new Date();
			let timeSince = currentTime - clipTime;
			var timeAgo = Math.floor(timeSince / 1000 / 60 / 60);
			if (timeAgo >= queryHours) {
				cutThisSlug = true;
			}
			if (gardenData.pulledClips[seedlingData.slug] !== undefined) {
				if (Number(gardenData.pulledClips[seedlingData.slug].score) < 0) {
					cutThisSlug = true;
				}
			}
			if (cutThisSlug !== true) {
				seedsToPlant.push(seedlingData);
				alreadyQueuedSlugs.push(slug);
			}
		});

		var submitsToPlant = [];
		var submits = this.state.submissions;
		jQuery.each(submits, function() {
			if (this.cut === 'cut') {
				return
			}
			if (filteredStreams.indexOf('User_Submits') > -1) {
				return
			}
			var clipData = this;
			var fullURL = clipData.clipURL;
			if (fullURL === false || fullURL === undefined) {
				fullURL = '';
			}
			if (fullURL.indexOf('clips.twitch.tv') > -1) {
				var slugStartPosition = fullURL.indexOf('.tv/') + 4;
				var slugEndPosition = fullURL.indexOf('?');
				if (slugEndPosition > -1) {
					var slugLength = slugEndPosition - slugStartPosition;
					var slug = fulllURL.substring(slugStartPosition, slugEndPosition);
				} else {
					var slug = fullURL.substring(slugStartPosition);
				}
				if (alreadyQueuedSlugs.indexOf(slug) === -1) {
					submitsToPlant.push(clipData);
				}
			} else {
				submitsToPlant.push(clipData);
			}
		});

		var clipCount = Object.keys(clips).length;
		var plantCount = seedsToPlant.length;
		var cutCount = clipCount - plantCount;

		var voters = {};
		var tags = {};
		jQuery.each(cutSlugs, function() {
			if (this.likeIDs !== undefined && this.likeIDs !== null) {
				voters[this.slug] = this.likeIDs;
			} else {
				voters[this.slug] = [];
			}
			if (this.tags !== undefined) {
				tags[this.slug] = this.tags;
			}
		});

		var streamsWithMoreToGive = Object.keys(this.state.cursors);
		var loadMore = '';
		if (streamsWithMoreToGive.length > 0) {
			loadMore = <LoadMore cursors={this.state.cursors} pushFurther={this.pushStreamQueryFurther} />
		}
		
		return(
			<section id="secretGarden">
				<GardenHeader clipCount={clipCount} cutCount={cutCount} addStream={this.addStream} />
				<Streamlist streamList={this.state.streamList} filterStreams={this.filterStreams} streamFilter={this.state.streamFilter} />
				<GardenStatus message={this.state.statusMessage} />
				<Garden clips={seedsToPlant} cutSlugs={this.state.cutSlugs} submissions={submitsToPlant} voters={voters} tags={tags} cutSlug={this.cutSlug} nukeSlug={this.nukeSlug} tagSlug={this.tagSlug} voteSlug={this.voteSlug} keepSlug={this.keepSlug} cutSubmission={this.cutSubmission} streamFilter={this.state.streamFilter} />
				{loadMore}
			</section>
		)
	}
}

if (jQuery('#secretGardenApp').length) {
	var streams = Object.keys(gardenData.streamList);
	var datas = [];
	var queryHours = parseInt(gardenData.queryHours, 10);
	var queryPeriod;
	if (queryHours > 24) {
		queryPeriod = "week";
	} else {
		queryPeriod = "day";
	}
	jQuery.each(streams, function() {
		var streamName = this;
		var query = `https://api.twitch.tv/kraken/clips/top?channel=${this}&period=${queryPeriod}&limit=100`;
		var ajax = jQuery.ajax({
			type: 'GET',
			url: query,
			headers: {
				'Client-ID' : privateData.twitchClientID,
				'Accept' : 'application/vnd.twitchtv.v5+json',
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				let cursor = data._cursor;
				gardenData.streamList[streamName].cursor = cursor;
			}
		});
		datas.push(ajax); 
	});

	jQuery.when.apply(jQuery, datas).then(function() {
		var allClips = [];
		jQuery.each(datas, function() {
			var clipData = JSON.parse(this.responseText);
			jQuery.each(clipData.clips, function() {
				if (this.game === "Rocket League") {
					let clipTime = Date.parse(this.created_at);
					let currentTime = + new Date();
					let timeSince = currentTime - clipTime;
					var timeAgo = Math.floor(timeSince / 1000 / 60 / 60);
					if (timeAgo <= queryHours) {
						allClips.push(this);
					}
				}
			});
		});
		gardenData.clips = allClips;
		ReactDOM.render(
			<SecretGarden />,
			document.getElementById('secretGardenApp')
		);
	});
	/*jQuery.when.apply(jQuery, datas).then(function() {
		var allClips = [];
		jQuery.each(datas, function() {
			var clipData = JSON.parse(this.responseText);
			jQuery.each(clipData.clips, function() {
				if (this.game === "Rocket League") {
					allClips.push(this);
				}
			});
		});
		gardenData.clips = allClips;
		ReactDOM.render(
			<SecretGarden />,
			document.getElementById('secretGardenApp')
		);
	});*/
}