import React from "react";

export default class TitleBox extends React.Component{
	render() {
		return(
			<header className="titleBox" onClick={this.props.toggleEmbed}>
				<h3><a href={this.props.linkout} className="titleLink" target="_blank">{this.props.title}</a></h3> 
				<div className="votecount">(+{this.props.score})</div>
			</header>
		)
	}
}