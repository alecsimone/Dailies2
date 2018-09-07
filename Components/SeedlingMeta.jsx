import React from "react";

export default class SeedlingMeta extends React.Component{
	render() {
		if (this.props.vodLink !== 'null') {
			var vodlink = <a href={this.props.vodLink} target="_blank" className="VODLink">VOD Link</a>
		} else {
			var vodlink = '';
		}
		let clipTime = Date.parse(this.props.clipTime);
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
		var nukerInfo = '';
		if (this.props.nuker !== undefined && this.props.nuker.length !== 0) {
			nukerInfo = 'Nuked by ' + this.props.nuker + '.';
		}
		var score = '';
		var voteCount = this.props.voters.length;
		let totalScore = voteCount + Number(this.props.score);
		var scoreText;
		if (totalScore > 0) {
			scoreText = '(+' + totalScore + ') ';
		} else if (totalScore < 0) {
			scoreText = '(' + totalScore + ') ';
		}
		var tags;
		var tagArray = this.props.tags;
		if (tagArray.length>0) {
			tags = tagArray.map(function(tag) {
				return <span className="tag" key={tag}>{tag}</span>;
			});
		}
		return(
			<div className='seedlingMeta'>
				<div className='seedlingLogo'><a href={this.props.broadcaster.channel_url}><img src={this.props.broadcaster.logo} /></a></div>
				<div className='seedlingInfo'>
					<div className='seedlingTitle'><a href={this.props.permalink} target="_blank" onClick={this.props.embedder}><span className="score">{scoreText}</span>{this.props.title}</a>{tags}</div>
					<div className='seedlingDetails'>{this.props.viewCount} views. Clipped by {this.props.clipper} about {timeAgo} {timeAgoUnit} ago. {nukerInfo} {vodlink}</div>
				</div>
			</div>
		)
	}
}