import React from "react";

export default class WeedMeta extends React.Component{
	render() {
		let clipTime = new Date(this.props.age);
		let currentTime = + new Date();
		let timeSince = currentTime - clipTime;
		if (timeSince < 3600000) {
			var timeAgo = Math.floor(timeSince / 1000 / 60);
			var timeAgoUnit = 'minutes';
			if (timeAgo === 1) {var timeAgoUnit = 'minute'};
		} else {
			var timeAgo = Math.floor(timeSince / 1000 / 60 / 60);
			var timeAgoUnit = 'hours';
			if (timeAgo === 1) {var timeAgoUnit = 'hour'};
		}

		let score;
		if (this.props.score < 0) {
			score = "" + this.props.score;
		} else if (this.props.score >= 0) {
			score = "+" + this.props.score;
		}

		let titleStyle = {
			maxWidth: this.props.width,
		};
		return(
			<div id="weedMeta">
				<div id="weedTitle" style={titleStyle} >({score}) {this.props.title}</div>
				<div id="weedInfo">{this.props.views} views. Clipped by {this.props.clipper} about {timeAgo} {timeAgoUnit} ago.</div>
			</div>
		);
	}
}