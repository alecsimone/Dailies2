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
		var hoursAgo = Math.floor(timeSince / 1000 / 60 / 60);
		var score = '';
		var voteCount = this.props.voters.length;
		if (voteCount > 0) {
			score = '(+' + voteCount + ') ';
		}
		return(
			<div className='seedlingMeta'>
				<div className='seedlingLogo'><a href={this.props.broadcaster.channel_url}><img src={this.props.broadcaster.logo} /></a></div>
				<div className='seedlingInfo'>
					<div className='seedlingTitle'><a href={this.props.permalink} target="_blank" onClick={this.props.embedder}><span className="score">{score}</span>{this.props.title}</a></div>
					<div className='seedlingDetails'>{this.props.viewCount} views. Clipped by {this.props.clipper} about {hoursAgo} hours ago. {vodlink}</div>
				</div>
			</div>
		)
	}
}