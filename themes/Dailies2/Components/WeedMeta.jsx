import React from "react";
import SlugTitle from "./Things/SlugTitle.jsx";

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

		let rawTitle = this.props.title;
		String.prototype.stripSlashes = function() {
		    return this.replace(/\\(.)/mg, "$1");
		}
		let title = rawTitle.stripSlashes();

		let vodlink;
		if (this.props.vodlink !== "none") {
			vodlink = <a href={this.props.vodlink} className="vodlink" target="_blank">VOD Link</a>;
		}

		return(
			<div id="weedMeta">
				<div id="weedTitle" style={titleStyle} ><SlugTitle slug={this.props.slug} type={this.props.type} title={this.props.title} /></div>
				<div id="weedInfo">{this.props.source === "User Submit" ? '' : `${this.props.views} views. `}{this.props.source === "User Submit" ? 'Submitted' : 'Clipped'} by {this.props.clipper} about {timeAgo} {timeAgoUnit} ago. {vodlink}</div>
			</div>
		);
	}
}