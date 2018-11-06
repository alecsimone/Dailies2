import React from "react";
import ReactDOM from 'react-dom';
import {CSSTransition, TransitionGroup} from 'react-transition-group';
import ClipPlayer from './ClipPlayer.jsx';
import WeedMeta from './WeedMeta.jsx';
import WeedBallot from './WeedBallot.jsx';
import WeedComments from './WeedComments.jsx';
import {privateData} from '../Scripts/privateData.jsx';

export default class Weed extends React.Component{
	constructor() {
		super();
		console.groupCollapsed("constructor");
		jQuery.each(weedData.clips, function(slug, slugObj) {
			if (slugObj.score === undefined) {
				slugObj.score = 0;
			}
			if (slugObj.nuked === undefined) {
				slugObj.nuked = 0;
			}
			if (slugObj.nuked == 1) {
				delete weedData.clips[slug];
			}
			if (Number(slugObj.score) < -50) {
				delete weedData.clips[slug];
			}
		});

		var seenMoments = [];
		jQuery.each(weedData.seenSlugs, (index, seenSlugObject) => {
			if (seenSlugObject.vodlink === 'none') {
				return true;
			} else {
				let vodlink = seenSlugObject.vodlink;
				seenMoments.push(this.turnVodlinkIntoMomentObject(vodlink));
			}
		});

		let youJudged = 0;
		jQuery.each(weedData.seenSlugs, function(index, seenSlugObject) {
			if (Object.keys(weedData.clips).includes(seenSlugObject.slug)) {
				delete weedData.clips[seenSlugObject.slug];
				youJudged++;
			}
		});


		this.state = {
			clips: weedData.clips,
			seenSlugs: weedData.seenSlugs,
			seenMoments,
			comments: [],
			commentsLoading: true,
			newClip: true,
			lastVoteDirection: null,
			totalClips: Object.keys(weedData.clips).length + youJudged,
			youJudged,
		};


		jQuery.each(this.state.clips, (index, clipData) => {
			if (clipData.vodlink !== undefined) {
				let thisMoment = this.turnVodlinkIntoMomentObject(clipData.vodlink);
				if (!this.checkMomentFreshness(thisMoment)) {
					delete this.state.clips[index];
					this.state.totalClips--;
				}
			}
		});

		if (this.state.seenSlugs.length === undefined) {
			console.log(this.state.seenSlugs.length);
			console.log("Changing seenSlugs from an object to an array");
			let seenSlugsArray = [];
			let seenSlugsObject = this.state.seenSlugs;
			let seenKeys = Object.keys(this.state.seenSlugs);
			console.log(seenKeys.length);
			seenKeys.forEach(function(key) {
				seenSlugsArray.push(seenSlugsObject[key]);
			});
			this.state.seenSlugs = seenSlugsArray;
		}

		this.sortClips = this.sortClips.bind(this);
		this.judgeClip = this.judgeClip.bind(this);
		this.nukeButtonHandler = this.nukeButtonHandler.bind(this);
		this.postComment = this.postComment.bind(this);
		this.yeaComment = this.yeaComment.bind(this);
		this.delComment = this.delComment.bind(this);
		this.sortByVOD = this.sortByVOD.bind(this);
		
		this.state.clipsArray = this.sortClips(Object.keys(weedData.clips));
		
		console.groupEnd("constructor");
	}

	turnVodlinkIntoMomentObject(vodlink) {
		if (vodlink === "none") {
			return false;
		}
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
			if (timestampHourIndex > -1) {
				var timestampMinutes = Number(timestamp.substring(timestampHourIndex + 1, timestampMinuteIndex));
			} else {
				var timestampMinutes = Number(timestamp.substring(0, timestampMinuteIndex));
			}
		} else {
			var timestampMinutes = 0;
		}
		let timestampSecondIndex = timestamp.indexOf('s');
		if (timestampSecondIndex > -1) {
			if (timestampMinuteIndex > -1) {
				var timestampSeconds = Number(timestamp.substring(timestampMinuteIndex + 1, timestampSecondIndex));
			} else if (timestampHourIndex > -1) {
				var timestampSeconds = Number(timestamp.substring(timestampHourIndex + 1, timestampSecondIndex));
			}
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
		clipsArray.forEach( (slug, index) => {
			if (clipsData[slug].nuked == 1) {
				console.log(`${slug} has been nuked, but is still showing up`);
				clipsArray.splice(index, 1);
			}
		});
		clipsArray.sort(this.sortByVOD);

		// clipsArray.sort(function(a, b) {
		// 	component.sortByVOD(a);
		// 	let sortScoreA = component.getSortScore(a);
		// 	let sortScoreB = component.getSortScore(b);
		// 	return sortScoreB - sortScoreA;
		// });

		return clipsArray;
	}

	sortByVOD(a, b) {
		let dataA = {
			score: Number(this.state.clips[a].score),
			views: Number(this.state.clips[a].views),
			timestamp: new Date(this.state.clips[a].age).getTime(),
			vodlink: this.turnVodlinkIntoMomentObject(this.state.clips[a].vodlink),
			votecount: Number(this.state.clips[a].votecount),
		};
		if (dataA.vodlink.vodID) {
			dataA.vodlink.vodID = Number(dataA.vodlink.vodID);
		} else {
			return 1;
		}

		let dataB = {
			score: Number(this.state.clips[b].score),
			views: Number(this.state.clips[b].views),
			timestamp: new Date(this.state.clips[b].age).getTime(),
			vodlink: this.turnVodlinkIntoMomentObject(this.state.clips[b].vodlink),
			votecount: Number(this.state.clips[b].votecount),
		};
		if (dataB.vodlink.vodID) {
			dataB.vodlink.vodID = Number(dataB.vodlink.vodID);
		} else {
			return -1;
		}

		if (dataA.votecount !== dataB.votecount) {
			if (dataA.votecount === 0) {
				return -1;
			}
			if (dataB.votecount === 0) {
				return 1;
			}
		}

		if (dataA.vodlink.vodID !== dataB.vodlink.vodID) {
			return dataA.vodlink.vodID - dataB.vodlink.vodID;
		} else {
			if (dataA.vodlink.vodtime !== dataB.vodlink.vodtime) {
				return dataA.vodlink.vodtime - dataB.vodlink.vodtime;
			} else {
				if (dataA.vodlink.score !== dataB.vodlink.score) {
					return dataB.vodlink.score - dataA.vodlink.score;
				} else {
					if (dataA.vodlink.views !== dataB.vodlink.views) {
						return dataB.vodlink.views - dataA.vodlink.views;
					} else {
						if (dataA.vodlink.timestamp !== dataB.vodlink.timestamp) {
							return dataB.vodlink.timestamp - dataA.vodlink.timestamp;
						}
					}
				}
			}
		}
		return 0;

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
		if (this.state.clips[slug].source) {
			var streamPriority = upperCaseStreamList.indexOf(this.state.clips[slug].source.toUpperCase());
			var upperCaseGoodStreams = weedData.goodStreams.map(function(name) {
				return name.toUpperCase();
			});
			var isGoodStream = upperCaseGoodStreams.indexOf(this.state.clips[slug].source.toUpperCase());
		} else {
			var streamPriority = -1;
			var isGoodStream = -1;
		}
		

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
		let lastVoteDirection;
		e.currentTarget.id == "strongYes" ? lastVoteDirection = "up" : lastVoteDirection = "down";
		console.log(`You ${lastVoteDirection}voted ${this.firstSlug}`);
		this.setState({
			newClip: false,
			lastVoteDirection,
		});
		let currentState = this.state;
		let boundThis = this;
		let slug = this.firstSlug;
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				slug,
				vodlink: currentState.clips[this.firstSlug].vodlink,
				judgment: e.currentTarget.id,
				action: 'judge_slug',
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: (data) => {
				console.log(data);
				if (typeof data === "string" && data.startsWith("Unknown Clip")) {
					console.log(`${slug} was not found in the clip database`);
					currentState.seenSlugs.push({slug});
					delete currentState.clips[slug];
				} else if (data != "Dummy just passed") {
					let vodlink = data.vodlink;
					if (vodlink !== undefined) {
						currentState.seenMoments.push(boundThis.turnVodlinkIntoMomentObject(vodlink))
					}
					currentState.clips[data.slug].score = data.score;
					currentState.clips[data.slug].votecount = data.votecount;
					currentState.seenSlugs.push(data);
					currentState.clipsArray.shift();
					currentState.youJudged++;
					console.log(`increasing youjudged because you judged ${data.slug}`);
					let dupeSlugs = boundThis.findAllDupes(data.slug);
					if (dupeSlugs) {
						dupeSlugs.forEach(function(slugToNuke) {
							console.log(`Nuking ${slugToNuke} because it's the same moment as the clip you just judged`);
							boundThis.nukeSlug(slugToNuke);
						});
					} 
				} else {
					currentState.seenSlugs.push({slug: boundThis.firstSlug});
					delete currentState.clips[slug];
				}
				currentState.commentsLoading = true;
				currentState.comments = [];
				currentState.newClip = true;
				currentState.lastVoteDirection = lastVoteDirection;
				boundThis.setState(currentState);
			}
		});
	}

	findAllDupes(slug) {
		let slugData = this.state.clips[slug];
		if (slugData === undefined || slugData.vodlink === 'none') {
			return [];
		}
		let vodlink = slugData.vodlink;
		let vodMoment = this.turnVodlinkIntoMomentObject(vodlink);
		let dupeSlugs = [];
		jQuery.each(this.state.clips, (index, clipData) => {
			if (clipData.slug === slug) {return true;}
			let currentVodMoment = this.turnVodlinkIntoMomentObject(clipData.vodlink);
			if (!currentVodMoment) {return true;}
			if (vodMoment.vodID != currentVodMoment.vodID) {return true;}
			if (vodMoment.vodtime + 25 >= currentVodMoment.vodtime && vodMoment.vodtime - 25 <= currentVodMoment.vodtime) {
				dupeSlugs.push(clipData.slug);
			}
		});
		return dupeSlugs;
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
	nukeButtonHandler() {
		this.setState({
			newClip: false,
			lastVoteDirection: "down",
		});
		console.log(`You hit the nuke button on ${this.firstSlug}`);
		let dupeSlugs = this.findAllDupes(this.firstSlug);
		let boundThis = this;
		this.nukeSlug(this.firstSlug);
		dupeSlugs.forEach(function(slugToNuke) {
			console.log(`Nuking ${slugToNuke} because it's the same moment as the clip you just judged`);
			boundThis.nukeSlug(slugToNuke);
		});
	}

	nukeSlug(slug) {
		if (!weedData.clips[slug]) {
			return;
		}
		// weedData.clips[slug].nuked = 1;
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				slug,
				action: 'nuke_slug',
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: (data) => {
				console.log(`you've nuked ${data}! And we'll be increasing youjudged because of it`);
				let clips = this.state.clips;
				clips[data].nuked = 1;
				let clipsArray = this.state.clipsArray;
				clipsArray.splice(0,1);
				this.setState({
					clips,
					clipsArray,
					newClip: true,
					totalClips: this.state.totalClips - 1,
				});
			},
		});
	}

	postComment(commentObject) {
		let currentState = this.state;
		currentState.commentsLoading = true;
		this.setState(currentState);
		// let randomID = Math.round(Math.random() * 100);
		let boundThis = this;
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
				// jQuery.each(currentState.comments, function(index,commentData) {
				// 	if (commentData.id == randomID) {
				// 		currentState.comments[index].id = data;
				// 	}
				// });
				// this.setState(currentState);
				let commentData = {
					comment: commentObject.comment,
					commenter: dailiesGlobalData.userData.userName,
					pic: dailiesGlobalData.userData.userPic,
					id: data,
					replytoid: commentObject.replytoid,
					slug: this.firstSlug,
					score: 0,
					time: Date.now(),
				}
				currentState.comments.push(commentData);
				currentState.commentsLoading = false;
				boundThis.setState(currentState);
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

	delComment(commentID) {
		let currentState = this.state;
		jQuery.each(currentState.comments, function(index, commentData) {
			if (commentData === undefined) {return true;}
			if (commentID == commentData.id) {
				delete currentState.comments[index];
			}
		});
		this.setState(currentState);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				commentID,
				action: 'del_comment',
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
				boundThis.setState({
					comments: data,
					commentsLoading: false,
				});
			}
		});
	}

	render() {
		console.groupCollapsed("render");
		console.log(`Rendering ${this.state.clipsArray[0]}`);
		// let slugsArray = Object.keys(this.state.clips);
		// let sortedClips = this.sortClips(slugsArray);
		// sortedClips = this.removeSeenSlugs(sortedClips);
		if (this.state.clipsArray.length === 0) {
			return(
				<section id="weeder" className="weederVictory">
					<div id="Victory">You won!</div>
				</section>
			)
		}
		this.firstSlug = this.state.clipsArray[0];
		let firstSlugData = this.state.clips[this.firstSlug];
		let firstSlugMoment = this.turnVodlinkIntoMomentObject(firstSlugData.vodlink);
		let momentIsFresh = this.checkMomentFreshness(firstSlugMoment);
		let i = 0;
		// while (!momentIsFresh && i < this.state.clipsArray.length) {
		// 	// this.nukeSlug(this.firstSlug);
		// 	console.log(`Skipping ${this.firstSlug} because you've seen that moment already`);
		// 	i++;
		// 	this.firstSlug = this.state.clipsArray[i];
		// 	firstSlugData = this.state.clips[this.firstSlug];
		// 	if (firstSlugData !== undefined) {
		// 		firstSlugMoment = this.turnVodlinkIntoMomentObject(firstSlugData.vodlink);
		// 		momentIsFresh = this.checkMomentFreshness(firstSlugMoment);
		// 	} else {
		// 		momentIsFresh = true;
		// 	}
		// }
		if (!momentIsFresh) {
			let clipsArray = this.state.clipsArray;
			clipsArray.splice(0, 1);
			this.setState({clipsArray});
		}
		if (this.state.clipsArray.length === i) {
			return(
				<section id="weeder" className="weederVictory">
					<div id="Victory">You won!</div>
				</section>
			)
		}

		let unjudgedClipCounter = 0;
		let yourUndjudgedClips = 0;
		jQuery.each(this.state.clips, function(index, clipData) {
			if (clipData.votecount == 0 && clipData.nuked == 0) {unjudgedClipCounter++;}
		});

		let width = jQuery(window).width() - 10;
		let windowHeight = jQuery(window).height();
		let menuLinksDivHeight = jQuery("#menu-links").height();
		let menuLinksMarginBottomString = jQuery("#menu-links").css("marginBottom");
		let menuLinksMarginBottomStringLength = menuLinksMarginBottomString.length;
		let menuLinksMarginBottom = Number(menuLinksMarginBottomString.substring(0, menuLinksMarginBottomStringLength - 2));
		let menuLinksHeight = menuLinksDivHeight + menuLinksMarginBottom;
		let height = windowHeight - menuLinksHeight;
		let playerHeight = height - 150;
		let playerWidth = playerHeight * 16 / 9;
		if (playerWidth + 250 < width) {
			var orientation = "Landscape";
		} else {
			var orientation = "Portrait";
			playerWidth = width;
		}
		var playerStyle = {
			maxWidth: playerWidth,
		}
		let usWidth = 100 * (this.state.totalClips - Number(unjudgedClipCounter)) / this.state.totalClips;
		var usStyle = {
			width: usWidth + '%',
		}
		let youWidth = 100 * (this.state.youJudged) / this.state.totalClips;
		var youStyle = {
			width: youWidth + '%',
		}
		console.groupEnd("render");
		return(
			<section id="weeder" className={"weeder" + orientation}>
				<CSSTransition
					in={true}
					timeout={500}
					appear={true}
					classNames={{
						appear: "slide-down",
						appearActive: "slide-down-active"
					}}
				>
					<div id="clipsLeftCounter">
						<div className="progressBackground">
							<div className="progressText">Us: {this.state.totalClips - Number(unjudgedClipCounter)} / {this.state.totalClips} Clips</div>
							<CSSTransition
								in={true}
								timeout={750}
								appear={true}
								classNames={{
									appear: "barGrow-appear",
									appearActive: "barGrow-appear-active"
								}}
							>
								<div id="usProgress" className="progressBar" style={usStyle} ></div>
							</CSSTransition>
						</div>
						<div className="progressBackground">
							<div className="progressText">You: {this.state.youJudged} / {this.state.totalClips} Clips</div>
							<CSSTransition
								in={true}
								timeout={750}
								appear={true}
								classNames={{
									appear: "barGrow-appear",
									appearActive: "barGrow-appear-active"
								}}
							>
								<div id="youProgress" className="progressBar" style={youStyle} ></div>
							</CSSTransition>
						</div>
					</div>
				</CSSTransition>
				<CSSTransition
					in={this.state.newClip}
					timeout={500}
					appear={true}
					classNames={{
						appear: "scale-up",
						appearActive: "scale-up-active",
						enter: `slide-right`,
						enterActive: `slide-right-active`,
						exit: `slide-out-${this.state.lastVoteDirection}`,
						exitActive: `slide-out-${this.state.lastVoteDirection}-active`,
						exitDone: `slide-out-${this.state.lastVoteDirection}-active`,
					}}
				>
					<section id="wholeClipBallot">
						<div id="weedPlayer" style={playerStyle} >
							<ClipPlayer slug={this.firstSlug} width={playerWidth} type={firstSlugData.type} newClip={this.state.newClip} />
							<WeedMeta slug={this.firstSlug} type={firstSlugData.type} title={firstSlugData.title} score={firstSlugData.score} age={firstSlugData.age} views={firstSlugData.views} clipper={firstSlugData.clipper} source={firstSlugData.source} vodlink={firstSlugData.vodlink} width={playerWidth} />
						</div>
						<WeedBallot judgeClip={this.judgeClip} nukeHandler={this.nukeButtonHandler} orientation={orientation} height={height}/>
						<WeedComments key={this.firstSlug} slug={this.firstSlug} postComment={this.postComment} commentsLoading={this.state.commentsLoading} comments={this.state.comments} yeaComment={this.yeaComment} delComment={this.delComment} />
					</section>
				</CSSTransition>
			</section>
		)
	}
}

if (jQuery('#weedApp').length) {
	var streams = Object.keys(weedData.streamList);
	var queries = [];
	var lastQueryTime = parseInt(weedData.lastUpdate, 10);
	var currentTime = new Date() / 1000;
	var secondsAgo = currentTime - lastQueryTime;
	var hoursAgo = secondsAgo / 60 / 60;
	var queryPeriod;
	if (hoursAgo > 24) {
		queryPeriod = "week";
	} else {
		queryPeriod = "day";
	}

	console.log(secondsAgo);
	if (secondsAgo > 60 || weedData.clips.length === 0) {
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
					parseClipData(data);
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
				parseClipData(data);
			},
		});
		queries.push(ajax);

		jQuery.when.apply(jQuery, queries).then(function() {
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

function parseClipData(data) {
	let clipStorage = {};
	jQuery.each(data.clips, function(index, clipData) {
		if (clipData.game !== "Rocket League") {
			console.log(`Rejecting ${clipData.slug} because it's not Rocket League`);
			return true;
		}
		if (Number(clipData.views) < 3) {
			console.log(`Rejecting ${clipData.slug} because it has too few views`);
			return true;
		}
		let clipTime = Date.parse(clipData.created_at) / 1000;
		if (clipTime <= Number(weedData.cutoffTimestamp)) {
			console.log(`Rejecting ${clipData.slug} because it's too old`);
			return true;
		}
		if (clipData.vod) {
			var vodlink = clipData.vod.url;
		} else {
			var vodlink = 'none';
		}
		let thumb;
		if (clipData.thumbnails) {
			thumb = clipData.thumbnails.medium;
		} else {
			thumb = null;
		}
		let thisClipObject = {
			slug: clipData.slug,
			title: clipData.title,
			views: clipData.views,
			age: clipData.created_at,
			source: clipData.broadcaster.display_name,
			sourcepic: clipData.broadcaster.logo,
			vodlink,
			clipper: clipData.curator.display_name,
			score: 0,
			votecount: 0,
			thumb,
			type: "twitch",
		}
		if (weedData.clips !== null && weedData.clips[clipData.slug] !== undefined) {
			if (weedData.clips[clipData.slug].nuked == 1) {
				console.log(`Rejecting ${clipData.slug} because it's been nuked`);
				return true;
			}
			thisClipObject.score = weedData.clips[clipData.slug].score;
			thisClipObject.votecount = weedData.clips[clipData.slug].votecount;
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

function storePulledClips(clips) {
	// console.log(clips);
	if (Object.keys(clips).length === 0) {
		return;
	}
	if (Object.keys(clips).length > 50) {
		var clipsA = {};
		var clipsB = {};
		for (var i = 49; i >= 0; i--) {
			let key = Object.keys(clips)[i];
			clipsA[key] = clips[key];
		}
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				clips: clipsA,
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
		for (var i = Object.keys(clips).length - 1; i >= 50; i--) {
			let key = Object.keys(clips)[i];
			clipsB[key] = clips[key];
		}
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				clips: clipsB,
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
		return;
	}
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