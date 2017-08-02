import React from "react";

export default class AddStreamBox extends React.Component{
	render() {
		return(
			<input id="addStreamBox" type="text" name="addStreamInput" placeholder="Add Stream?" onKeyDown={this.props.addStream} />
		)
	}
}