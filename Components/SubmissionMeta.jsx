import React from "react";

export default class SubmissionMeta extends React.Component{

	render() {

		var submissionTitle = this.props.submissionData.post_title;
		var submissionURL = this.props.submissionData.clipURL;
		var submitter = this.props.submissionData.submitter;
		var submitTime = this.props.submissionData.submitTime;

		let currentTime = + new Date();
		submitTime = submitTime * 1000;
		let timeSince = currentTime - submitTime;
		console.log(timeSince);
		if (timeSince < 3600000) {
			var timeAgo = Math.floor(timeSince / 1000 / 60);
			var timeAgoUnit = 'minutes';
			if (timeAgo === 1) {var timeAgoUnit = 'minute'};
		} else {
			var timeAgo = Math.floor(timeSince / 1000 / 60 / 60);
			var timeAgoUnit = 'hours';
			if (timeAgo === 1) {var timeAgoUnit = 'hour'};
		}

		if (this.props.submissionData.hasOwnProperty('sourceLogo')) {
			var logoURL = this.props.submissionData.sourceLogo
		} else {
			var logoURL = 'https://dailies.gg/wp-content/uploads/2017/03/default_pic.jpg'
		}

		return(
			<div className='seedlingMeta'>
				<div className='seedlingLogo'><img src={logoURL} /></div>
				<div className='seedlingInfo'>
					<div className='seedlingTitle'><a href={submissionURL} target="_blank">{submissionTitle}</a></div>
					<div className='seedlingDetails'>Submitted by {submitter} about {timeAgo} {timeAgoUnit} ago.</div>
				</div>
			</div>
		)
	}

}