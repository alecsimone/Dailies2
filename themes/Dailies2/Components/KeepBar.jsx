import React from "react";

export default class KeepBar extends React.Component{
	constructor() {
		super();
		this.keepHandler = this.keepHandler.bind(this);
	}
	keepHandler(e) {
		if (jQuery(e.target).hasClass("keepbarInput")) {
			if (e.which === 13) {
				var name = e.target.value;
			} else {
				return;
			}
		} else {
			var name = jQuery(e.target).parent().find('.keepbarInput').val();
		}
		var thingData = {
			name,
			source: this.props.source,
		}
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
		};
		let slugObj = {
			slug: this.props.slug,
			createdAt: Date.parse(this.props.clipTime),
			cutBoolean: true,
			VODBase,
			VODTime,
			likeIDs: this.props.voters,
		};
		this.props.keepSlug(slugObj, thingData);
	}

	render() {
		return(
			<div className='keepbar'>
				<input type="text" className="keepbarInput" name="keepbar" placeholder="Who and Why?" onKeyDown={this.keepHandler} /><button className="keepbutton" onClick={this.keepHandler} >nom</button>
			</div>
		)
	}
}