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
			var VODTime = window.vodLinkTimeParser(vodLink);
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
		var nuker;
		if (dailiesGlobalData.userData.userRole === "administrator" || dailiesGlobalData.userData.userRole === "editor" || dailiesGlobalData.userData.userRole === "author") {
			nuker = <button className="nukeButton" style={{display: "block"}} onClick={this.cutHandler}>Nuke It</button>;
		}
		return(
			<div className='seedlingControls'>
				<div className="seedlingControlsTop">
					<img className="seedVoter seedControlImg hoverReplacer" src={seedVoterSrc} data-replace-src={seedReplaceSrc} onClick={this.voteHandler} />
					<img className="seedCutter seedControlImg" src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/04/red-x.png'} onClick={this.cutHandler} />
					{nuker}
				</div>
			</div>
		)
	}
}