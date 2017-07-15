import React from "react";

export default class AddRLButton extends React.Component{
	render() {
		return(
			<button id="addRL" onClick={this.props.addStream}>Add RL</button>
		)
	}
}