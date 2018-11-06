import React from "react";

export default class TitleBox extends React.Component{
	render() {
		var fixedTitle = this.props.title;
		if (fixedTitle.indexOf('&') > -1) {
			fixedTitle = window.htmlEntityFix(fixedTitle);
		}
		if (fixedTitle.length >= 80) {
			fixedTitle = fixedTitle.substring(0,79).trim() + '...';
		};
		return(
			<header className="titleBox" onClick={this.props.toggleEmbed}>
				<h3><a href={this.props.linkout} className="titleLink" target="_blank">{fixedTitle}</a></h3> 
				<div className="votecount">(+{this.props.score})</div>
			</header>
		)
	}
}