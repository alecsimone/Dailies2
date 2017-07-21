import React from "react";
import ReactDOM from 'react-dom';
import Streamlist from './Streamlist.jsx';
import Garden from './Garden.jsx';
import GardenHeader from './GardenHeader.jsx';
import LoadMore from './LoadMore.jsx';

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
		this.state = {
			streamList: gardenData.streamList,
			clips: gardenData.clips,
			cursors,
			cutSlugs: gardenData.cutSlugs,
		};
		this.addStream = this.addStream.bind(this);
		this.setState = this.setState.bind(this);
		this.cutSlug = this.cutSlug.bind(this);
		this.voteSlug = this.voteSlug.bind(this);
		this.keepSlug = this.keepSlug.bind(this);
		this.pushStreamQueryFurther = this.pushStreamQueryFurther.bind(this);
	}

	cutSlug(slugObj, scope) {
		var currentState = this.state;
		currentState.cutSlugs[slugObj.slug] = slugObj;
		this.setState(currentState);
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
		var currentState = this.state;
		currentState.cutSlugs[slugObj.slug] = slugObj;
		this.setState(currentState);
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
				if (Number.isInteger(data)) {
					window.open(dailiesGlobalData.thisDomain + '/wp-admin/post.php?post=' + data + '&action=edit', '_blank');
				}
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
		if (streamToAdd === "Rocket League") {
			var queryURL = 'https://api.twitch.tv/kraken/clips/top?game=Rocket%20League&period=day&limit=100';
		} else {
			var queryURL = `https://api.twitch.tv/kraken/clips/top?channel=${streamToAdd}&period=day&limit=100`;
		}
		currentState.streamList[streamToAdd] = {
			cursor: "",
			viewThreshold: "0",
		}
		var ajax = jQuery.ajax({
			type: 'GET',
			url: queryURL,
			headers: {
				'Client-ID' : 'r7cqs4kgrg1sknyz32brgy9agivw9n',
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
		var currentState = this.state;
		var boundThis = this;
		jQuery.each(streams, function() {
			var streamName = this;
			var currentCursor = cursorsObject[streamName];
			if (streamName === 'Rocket League') {
				var query = `https://api.twitch.tv/kraken/clips/top?game=Rocket%20League&period=day&limit=100&cursor=${currentCursor}`
			} else {
				var query = `https://api.twitch.tv/kraken/clips/top?channel=${streamName}&period=day&limit=100&cursor=${currentCursor}`
			}
			var ajax = jQuery.ajax({
				type: 'GET',
				url: query,
				headers: {
					'Client-ID' : 'r7cqs4kgrg1sknyz32brgy9agivw9n',
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

	render() {
		var clips = this.state.clips;
		var cutSlugs = this.state.cutSlugs;

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
							}
						});
					}
				});
			}
			if (cutSlugs[slug] !== undefined) {
				if (cutSlugs[slug].cutBoolean === true || cutSlugs[slug].cutBoolean === 'true') {
					cutThisSlug = true;
				}
			}
			if (cutThisSlug !== true) {
				seedsToPlant.push(seedlingData);
			}
		});
		var clipCount = Object.keys(clips).length;
		var plantCount = seedsToPlant.length;
		var cutCount = clipCount - plantCount;

		var voters = {};
		jQuery.each(cutSlugs, function() {
			if (this.likeIDs !== undefined) {
				voters[this.slug] = this.likeIDs;
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
				<Streamlist streamList={this.state.streamList} />
				<Garden clips={seedsToPlant} voters={voters} cutSlug={this.cutSlug} voteSlug={this.voteSlug} keepSlug={this.keepSlug} />
				{loadMore}
			</section>
		)
	}
}

if (jQuery('#secretGardenApp').length) {
	var streams = Object.keys(gardenData.streamList);
	var datas = [];
	jQuery.each(streams, function() {
		var streamName = this;
		var query = `https://api.twitch.tv/kraken/clips/top?channel=${this}&period=day&limit=100`;
		var ajax = jQuery.ajax({
			type: 'GET',
			url: query,
			headers: {
				'Client-ID' : 'r7cqs4kgrg1sknyz32brgy9agivw9n',
				'Accept' : 'application/vnd.twitchtv.v5+json',
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
					allClips.push(this);
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