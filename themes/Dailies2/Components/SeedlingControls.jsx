import React from "react";

export default class SeedlingControls extends React.Component{
	constructor() {
		super();
		this.cutHandler = this.cutHandler.bind(this);
		this.voteHandler = this.voteHandler.bind(this);
	}

	cutHandler(e) {
		let vodLink = this.props.vodLink;
		if (vodLink === "null") {
			var VODBase = "null";
			var VODTime = "null";
		} else {
			var timestampIndex = vodLink.lastIndexOf('t=');
			var VODBase = vodLink.substring(29, timestampIndex - 1);
			var timestamp = vodLink.substring(timestampIndex + 2);
			var hourMark = timestamp.lastIndexOf('h');
			if (hourMark > -1) {
				var hourCount = timestamp.substring(0, hourMark);
			} else {
				var hourCount = 0;
			}
			var minuteMark = timestamp.lastIndexOf('m');
			if (minuteMark > -1) {
				var minuteCount = timestamp.substring(hourMark + 1, minuteMark);
			} else {
				var minuteCount = 0;
			}
			var secondMark = timestamp.lastIndexOf('s');
			if (secondMark > -1) {
				var secondCount = timestamp.substring(minuteMark + 1, secondMark);
			} else {
				var secondCount = 0;
			}
			var VODTime = 3600 * hourCount + 60 * minuteCount + 1 * secondCount;
		}
		let slugObj = {
			slug: this.props.slug,
			createdAt: Date.parse(this.props.clipTime),
			cutBoolean: true,
			VODBase,
			VODTime,
		}
		if (jQuery(e.target).hasClass('seedCutter')) {
			var scope = dailiesGlobalData.userData.userID;
		} else if (jQuery(e.target).hasClass('nukeButton')) {
			var scope = "all"
		}
		this.props.cutSlug(slugObj, scope);
	}

	voteHandler() {
		let slugObj = {
			slug: this.props.slug,
			createdAt: Date.parse(this.props.clipTime),
			cutBoolean: false,
			likeIDs: [dailiesGlobalData.userData.userID],
		};
		this.props.voteSlug(slugObj);
	}

	render() {
		let userIDString = dailiesGlobalData.userData.userID.toString(10);
		if (this.props.voters.indexOf(userIDString) > -1 || this.props.voters.indexOf(dailiesGlobalData.userData.userID) > -1) {
			var seedVoterSrc = dailiesGlobalData.thisDomain + '/wp-content/uploads/2016/12/Medal-small-100.png';
			var seedReplaceSrc = dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/07/Vote-Icon-light-line-100.png';
		} else {
			var seedVoterSrc = dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/07/Vote-Icon-light-line-100.png';
			var seedReplaceSrc = dailiesGlobalData.thisDomain + '/wp-content/uploads/2016/12/Medal-small-100.png';
		}
		return(
			<div className='seedlingControls'>
				<div className="seedlingControlsTop">
					<img className="seedVoter seedControlImg hoverReplacer" src={seedVoterSrc} data-replace-src={seedReplaceSrc} onClick={this.voteHandler} />
					<img className="seedCutter seedControlImg" src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/04/red-x.png'} onClick={this.cutHandler} />
				</div>
				<button className="nukeButton" onClick={this.cutHandler}>Nuke</button>
			</div>
		)
	}
}