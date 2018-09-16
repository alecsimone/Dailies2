import React from "react";
import ReactDOM from 'react-dom';
import ClipPlayer from './ClipPlayer.jsx';
import WeedMeta from './WeedMeta.jsx';
import WeedBallot from './WeedBallot.jsx';
import WeedComments from './WeedComments.jsx';
import {privateData} from '../Scripts/privateData.jsx';

export default class Weed extends React.Component{
	constructor() {
		super();
		console.log(Object.keys(weedData.clips).length);
		jQuery.each(weedData.clips, function(slug, slugObj) {
			if (slugObj.score === undefined) {
				slugObj.score = 0;
			}
			if (slugObj.nuked === undefined) {
				slugObj.nuked = 0;
			}
		});
		this.state = {
			clips: weedData.clips,
			seenSlugs: weedData.seenSlugs,
			comments: [],
			commentsLoading: true,
		};

		let state = this.state;
		jQuery.each(this.state.clips, function(index, val) {
			if (val.nuked == 1) {
				delete state.clips[index];
			}
		});

		let seenMoments = [];
		let boundThis = this;
		jQuery.each(this.state.seenSlugs, function(index, seenSlugObject) {
			if (seenSlugObject.vodlink === 'none') {
				return true;
			} else {
				let vodlink = seenSlugObject.vodlink;
				seenMoments.push(boundThis.turnVodlinkIntoMomentObject(vodlink));
			}
		});
		this.state.seenMoments = seenMoments;

		let clipList = Object.keys(this.state.clips);
		jQuery.each(this.state.seenSlugs, function(index, seenSlugObject) {
			if (clipList.includes(seenSlugObject.slug)) {
				// console.log(seenSlugObject.slug);
				delete state.clips[seenSlugObject.slug];
				// console.log(`Removing ${seenSlugObject.slug} because you've already seen it`);
			}
		});

		let today = new Date().getUTCDay();
		jQuery.each(this.state.clips, function(index, clipData) {
			if (clipData.vodlink !== undefined) {
				let thisMoment = boundThis.turnVodlinkIntoMomentObject(clipData.vodlink);
				if (!boundThis.checkMomentFreshness(thisMoment)) {
					delete state.clips[index];
				}
			}
			if (clipData.source === "RocketLeague" && today === 1) {
				delete state.clips[index];
			}
		})

		this.sortClips = this.sortClips.bind(this);
		this.judgeClip = this.judgeClip.bind(this);
		this.postComment = this.postComment.bind(this);
		this.yeaComment = this.yeaComment.bind(this);
	}

	turnVodlinkIntoMomentObject(vodlink) {
		let vodIDIndex = vodlink.indexOf('/videos/') + 8;
		let vodTimeIndex = vodlink.indexOf('?t=') + 3;
		let vodID = vodlink.substring(vodIDIndex, vodTimeIndex - 3);

		let timestamp = vodlink.substring(vodTimeIndex, vodlink.length);
		let timestampHourIndex = timestamp.indexOf('h');
		if (timestampHourIndex > -1) {
			var timestampHours = Number(timestamp.substring(0, timestampHourIndex));
		} else {
			var timestampHours = 0;
		}
		let timestampMinuteIndex = timestamp.indexOf('m');
		if (timestampMinuteIndex > -1) {
			var timestampMinutes = Number(timestamp.substring(timestampHourIndex + 1, timestampMinuteIndex));
		} else {
			var timestampMinutes = 0;
		}
		let timestampSecondIndex = timestamp.indexOf('s');
		if (timestampSecondIndex > -1) {
			var timestampSeconds = Number(timestamp.substring(timestampMinuteIndex + 1, timestampSecondIndex));
		} else {
			var timestampSeconds = 0;
		}
		let vodtime = timestampSeconds + 60 * timestampMinutes + 60 * 60 * timestampHours;
		return {
			vodID,
			vodtime,
		};
	}

	sortClips(clipsArray) {
		// if return is < 0, a comes first, greater than 0 b comes first
		let clipsData = this.state.clips;
		let component = this;
		clipsArray.sort(function(a, b) {
			let sortScoreA = component.getSortScore(a);
			let sortScoreB = component.getSortScore(b);
			return sortScoreB - sortScoreA;
		});
		// let scoresObject = {};
		// jQuery.each(clipsArray, function(index, val) {
		// 	scoresObject[val] = component.getSortScore(val);
		// })
		// console.table(scoresObject);
		return clipsArray;
	}

	getSortScore(slug) {
		let score = Number(this.state.clips[slug].score);
		let views = Number(this.state.clips[slug].views);
		
		let timestamp = new Date(this.state.clips[slug].age).getTime();
		let now = new Date().getTime();
		let age = now - timestamp;
		var queryHours = parseInt(weedData.queryHours, 10);


		var priorityStreamList = Object.keys(weedData.streamList);
		var upperCaseStreamList = priorityStreamList.map(function(name) {
			return name.toUpperCase();
		});
		let streamPriority = upperCaseStreamList.indexOf(this.state.clips[slug].source.toUpperCase());
		
		var upperCaseGoodStreams = weedData.goodStreams.map(function(name) {
			return name.toUpperCase();
		});
		let isGoodStream = upperCaseGoodStreams.indexOf(this.state.clips[slug].source.toUpperCase());

		let sortScore = score + (Math.log10(views) * 5) - (age / 1000 / 60 / 60 / 10);
		if (views < 3) {sortScore = sortScore - 100;}
		if (streamPriority > -1) {sortScore = sortScore + 10;}
		if (isGoodStream > -1) {sortScore = sortScore + 5;}
		if (Number(this.state.clips[slug].votecount) == 0) {sortScore = sortScore + 2000;}
		if (Number(this.state.clips[slug].votecount) == 1) {sortScore = sortScore + 1000;}
		if (Number(this.state.clips[slug].votecount) == 2) {sortScore = sortScore + 500;}
		if (Number(this.state.clips[slug].nuked) == 1) {sortScore = sortScore - 10000;}
		if (age / 1000 / 60 / 60 - 6 > queryHours) {sortScore = sortScore - 100;}

		return sortScore;
	}

	judgeClip(e) {
		let currentState = this.state;
		let boundThis = this;
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				slug: this.firstSlug,
				vodlink: currentState.clips[this.firstSlug].vodlink,
				judgment: e.currentTarget.id,
				action: 'judge_slug',
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				if (data != "Dummy just passed") {
					let slug = data.slug;
					let vodlink = data.vodlink;
					if (vodlink !== undefined) {
						currentState.seenMoments.push(boundThis.turnVodlinkIntoMomentObject(vodlink))
					}
					currentState.clips[slug].score = data.score;
					currentState.seenSlugs.push(data);
				} else {
					currentState.seenSlugs.push({slug: boundThis.firstSlug});
				}
				currentState.commentsLoading = true;
				currentState.comments = [];
				boundThis.setState(currentState);
			}
		});
	}
	removeSeenSlugs(clipList) {
		let seenSlugs = this.state.seenSlugs;
		jQuery.each(seenSlugs, function(index, seenSlugObject) {
			if (clipList.includes(seenSlugObject.slug)) {
				let seenSlugIndex = clipList.indexOf(seenSlugObject.slug);
				clipList.splice(seenSlugIndex, 1);
				// console.log(`Removing ${seenSlugObject.slug} because you've already seen it`);
			}
		});
		return clipList;
	}
	checkMomentFreshness(momentObject) {
		let vodID = momentObject.vodID;
		let vodtime = momentObject.vodtime;
		let isFresh = true;
		jQuery.each(this.state.seenMoments, function(index, moment) {
			if (vodID == moment.vodID) {
				if (vodtime + 25 >= moment.vodtime && vodtime - 25 <= moment.vodtime) {
					isFresh = false;
				}
			}
		})
		return isFresh;
	}

	postComment(commentObject) {
		let currentState = this.state;
		let randomID = Math.round(Math.random() * 100);
		let commentData = {
			comment: commentObject.comment,
			commenter: dailiesGlobalData.userData.userName,
			pic: dailiesGlobalData.userData.userPic,
			id: randomID,
			replytoid: commentObject.replytoid,
			slug: this.firstSlug,
			score: 0,
			time: Date.now(),
		}
		currentState.comments.push(commentData);
		this.setState(currentState);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				slug: this.firstSlug,
				commentObject,
				action: 'post_comment',
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

	componentDidMount() {
		this.getComments();
	}

	componentDidUpdate() {
		if (this.state.commentsLoading) {
			this.getComments();
		}
	}

	getComments() {
		let queryURL = `${dailiesGlobalData.thisDomain}/wp-json/dailies-rest/v1/clipcomments/slug=${this.firstSlug}`
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

	render() {
		let slugsArray = Object.keys(this.state.clips);
		let sortedClips = this.sortClips(slugsArray);
		let clipDataLogger = {};
		sortedClips = this.removeSeenSlugs(sortedClips);
		if (sortedClips.length === 0) {
			return(
				<section id="weeder" className="weederVictory">
					<div id="Victory">You won!</div>
				</section>
			)
		}
		this.firstSlug = sortedClips[0];
		let firstSlugData = this.state.clips[this.firstSlug];
		let firstSlugMoment = this.turnVodlinkIntoMomentObject(firstSlugData.vodlink);
		let momentIsFresh = this.checkMomentFreshness(firstSlugMoment);
		let i = 0;
		while (!momentIsFresh && i < sortedClips.length) {
			console.log(`Skipping ${this.firstSlug} because you've seen that moment already`);
			i++;
			this.firstSlug = sortedClips[i];
			firstSlugData = this.state.clips[this.firstSlug];
			if (firstSlugData !== undefined) {
				firstSlugMoment = this.turnVodlinkIntoMomentObject(firstSlugData.vodlink);
				momentIsFresh = this.checkMomentFreshness(firstSlugMoment);
			} else {
				momentIsFresh = true;
			}
		}
		if (sortedClips.length === i) {
			return(
				<section id="weeder" className="weederVictory">
					<div id="Victory">You won!</div>
				</section>
			)
		}

		let width = jQuery(window).width() - 10;
		let windowHeight = jQuery(window).height();
		let menuLinksDivHeight = jQuery("#menu-links").height();
		let menuLinksMarginBottomString = jQuery("#menu-links").css("marginBottom");
		let menuLinksMarginBottomStringLength = menuLinksMarginBottomString.length;
		let menuLinksMarginBottom = Number(menuLinksMarginBottomString.substring(0, menuLinksMarginBottomStringLength - 2));
		let menuLinksHeight = menuLinksDivHeight + menuLinksMarginBottom;
		let height = windowHeight - menuLinksHeight;
		// if (3 * width >= 4 * height) {
		// 	var orientation = "Landscape";
		// 	width = width - 250;
		// } else {
		// 	var orientation = "Portrait";
		// }
		let playerHeight = height - 140;
		let playerWidth = playerHeight * 16 / 9;
		if (playerWidth + 250 < width) {
			var orientation = "Landscape";
		} else {
			var orientation = "Portrait";
			playerWidth = width;
		}

		return(
			<section id="weeder" className={"weeder" + orientation}>
				<div id="weedPlayer">
					<ClipPlayer slug={this.firstSlug} width={playerWidth} />
					<WeedMeta title={firstSlugData.title} score={firstSlugData.score} age={firstSlugData.age} views={firstSlugData.views} clipper={firstSlugData.clipper} width={playerWidth} />
				</div>
				<WeedBallot judgeClip={this.judgeClip} orientation={orientation} height={height}/>
				<WeedComments postComment={this.postComment} commentsLoading={this.state.commentsLoading} comments={this.state.comments} yeaComment={this.yeaComment} />
			</section>
		)
	}
}

if (jQuery('#weedApp').length) {
	var streams = Object.keys(weedData.streamList);
	var queries = [];
	var queryHours = parseInt(weedData.queryHours, 10);
	var queryPeriod;
	if (queryHours > 24) {
		queryPeriod = "week";
	} else {
		queryPeriod = "day";
	}

	if (weedData.needsFreshQuery == 'true' || weedData.clips.length === 0) {
		if (weedData.clips === null) {weedData.clips={};}	
		jQuery.each(streams, function() {
			if (this === "Rocket_Dailies") {return true;}
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
					let clipStorage = {};
					jQuery.each(data.clips, function(index, clipData) {
						if (clipData.game !== "Rocket League") {
							return true;
						}
						let clipTime = Date.parse(clipData.created_at);
						let currentTime = + new Date();
						let timeSince = currentTime - clipTime;
						var timeAgo = Math.floor(timeSince / 1000 / 60 / 60);
						if (timeAgo >= queryHours) {
							return true;
						}
						if (clipData.vod) {
							var vodlink = clipData.vod.url;
						} else {
							var vodlink = 'none';
						}
						let thisClipObject = {
							slug: clipData.slug,
							title: clipData.title,
							views: clipData.views,
							age: clipData.created_at,
							source: clipData.broadcaster.display_name,
							vodlink: vodlink,
							clipper: clipData.curator.display_name,
							score: 0,
							votecount: 0,
						}
						if (weedData.clips !== null && weedData.clips[clipData.slug] !== undefined) {
							if (weedData.clips[clipData.slug].nuked == 1) {
								return true;
							}
							thisClipObject.score = weedData.clips[clipData.slug].score;
						}
						clipStorage[clipData.slug] = thisClipObject;
					});
					if (Object.keys(clipStorage).length === 0) {
						// console.log("no clips here");
						return true;
					}
					storePulledClips(clipStorage);
					jQuery.each(clipStorage, function(index, val) {
						weedData.clips[index] = val;
					});
				}
			});
			queries.push(ajax); 
		});

		var query = `https://api.twitch.tv/kraken/clips/top?game=Rocket%20League&period=${queryPeriod}&limit=100`;
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
				let clipStorage = {};
				jQuery.each(data.clips, function(index, clipData) {
					if (clipData.game !== "Rocket League") {
						return true;
					}
					let clipTime = Date.parse(clipData.created_at);
					let currentTime = + new Date();
					let timeSince = currentTime - clipTime;
					var timeAgo = Math.floor(timeSince / 1000 / 60 / 60);
					if (timeAgo >= queryHours) {
						return true;
					}
					if (clipData.vod) {
						var vodlink = clipData.vod.url;
					} else {
						var vodlink = 'none';
					}
					let thisClipObject = {
						slug: clipData.slug,
						title: clipData.title,
						views: clipData.views,
						age: clipData.created_at,
						source: clipData.broadcaster.display_name,
						vodlink: vodlink,
						clipper: clipData.curator.display_name,
						votecount: 0,
					}
					if (weedData.clips !== null && weedData.clips[clipData.slug] !== undefined) {
						if (weedData.clips[clipData.slug].nuked == 1) {
							return true;
						}
						thisClipObject.score = weedData.clips[clipData.slug].score;
					}
					clipStorage[clipData.slug] = thisClipObject;
				});
				if (Object.keys(clipStorage).length === 0) {
					// console.log("no clips here");
					return true;
				}
				storePulledClips(clipStorage);
				jQuery.each(clipStorage, function(index, val) {
					weedData.clips[index] = val;
				});
			}
		});
		queries.push(ajax);

		jQuery.when.apply(jQuery, queries).then(function() {
			// var allClips = [];
			// jQuery.each(queries, function() {
			// 	var clipData = JSON.parse(this.responseText);
			// 	jQuery.each(clipData.clips, function() {
			// 		if (this.game === "Rocket League") {
			// 			let clipTime = Date.parse(this.created_at);
			// 			let currentTime = + new Date();
			// 			let timeSince = currentTime - clipTime;
			// 			var timeAgo = Math.floor(timeSince / 1000 / 60 / 60);
			// 			if (timeAgo <= queryHours) {
			// 				allClips.push(this);
			// 			}
			// 		}
			// 	});
			// });
			ReactDOM.render(
				<Weed />,
				document.getElementById('weedApp')
			);
		});
	} else {
		console.log("We've got clips, no need to query");
		ReactDOM.render(
			<Weed />,
			document.getElementById('weedApp')
		);
	}

}

function storePulledClips(clips) {
	jQuery.ajax({
		type: "POST",
		url: dailiesGlobalData.ajaxurl,
		dataType: 'json',
		data: {
			clips,
			action: 'store_pulled_clips',
		},
		error: function(one, two, three) {
			console.log(one);
			console.log(two);
			console.log(three);
		},
		success: function(data) {
			// console.log(data);
		}
	});
}