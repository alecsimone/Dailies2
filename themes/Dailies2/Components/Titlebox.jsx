import React from "react";

export default class TitleBox extends React.Component{
	render() {
		return(
			<div className="titleBox" onClick={this.props.toggleEmbed}><h3><a href={this.props.linkout} className="littleThingTitleLink" target="_blank">{this.props.title}</a></h3> <div className="littleThingVotecount">(+{this.props.score})</div></div>
		)
	}
}