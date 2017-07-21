import React from "react";

export default class AuthorBubble extends React.Component{
	render() {
		return(
			<img src={this.props.authorData.logo} className="littleThingAuthor" />
		)
	}
}