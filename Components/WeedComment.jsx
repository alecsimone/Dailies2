import React from "react";

export default class WeedComment extends React.Component{
	constructor() {
		super();
		this.yeaHandler = this.yeaHandler.bind(this);
		this.delHandler = this.delHandler.bind(this);
	}

	yeaHandler() {
		this.props.yeaComment(this.props.commentID);
	}

	delHandler() {
		this.props.delComment(this.props.commentID);
	}

	render() {
		let rawTime = Number(this.props.commentTime);
		if (rawTime < 10000000000) {rawTime = rawTime * 1000;}
		let commentTime = new Date(rawTime);
		let currentTime = + new Date();
		let timeSince = currentTime - commentTime;
		if (timeSince < 3600000) {
			var timeAgo = Math.floor(timeSince / 1000 / 60);
			var timeAgoUnit = 'm';
		} else {
			var timeAgo = Math.floor(timeSince / 1000 / 60 / 60);
			var timeAgoUnit = 'h';
		}

		let rawComment = this.props.comment;
		String.prototype.stripSlashes = function() {
		    return this.replace(/\\(.)/mg, "$1");
		}
		let comment = rawComment.stripSlashes();

		if (this.props.commenter === dailiesGlobalData.userData.userName) {
			var yeaButton = '';
		} else {
			var yeaButton = <img className="yeaButton" src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2018/07/voteyea.png'} onClick={this.yeaHandler} />;
		}

		if (this.props.commenter === dailiesGlobalData.userData.userName || dailiesGlobalData.userData.userRole === "author" || dailiesGlobalData.userData.userRole === "editor" || dailiesGlobalData.userData.userRole === "admi`nistrator") {
			var delButton = <img className="delButton" src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/04/red-x.png'} onClick={this.delHandler} />;
		} else {
			var delButton = '';
		}

		return(
			<div className="weedComment">
				<div className="commentLeft">
					<div className="commentPic"><img className="commenterPic" src={this.props.pic} /></div>
					<div className="comment">
						<div className="commentContent"><span className="commenter">{this.props.commenter}</span> {comment}</div>
						<div className="commentMeta"><span className="commentTime">{timeAgo}{timeAgoUnit}</span> <span className="commentScore">+{this.props.score}</span></div>
					</div>
				</div>
				<div className="commentRight">
					{delButton}
					{yeaButton}
				</div>
			</div>
		)
	}
}