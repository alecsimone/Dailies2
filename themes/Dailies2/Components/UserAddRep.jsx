import React from "react";
import ReactDOM from 'react-dom';

export default class UserAddRep extends React.Component{
	constructor() {
		super();
		this.addRepHandler = this.addRepHandler.bind(this);
	}

	addRepHandler(e) {
		e.preventDefault();
		let repDataRaw = jQuery(e.target).serializeArray();
		let repData = {
			user: repDataRaw[0].value,
			repToAdd: repDataRaw[1].value,
		}
		if (isNaN(repData.repToAdd)) {
			console.log("You didn't provide a number for the rep to be added");
			return;
		}
		this.props.addRepToUser(repData);
		e.target.reset();
	}

	render() {
		return(
			<form id="addRep" onSubmit={this.addRepHandler} >
				<div id="userInputWrapper" className="userInputWrapper">
					<label htmlFor="linkerUserInput">User</label>
					<input type="text" id="linkerUserInput" name="linkerUserInput" size="8" />
				</div>
				<div id="repInputWrapper" className="userInputWrapper">
					<label htmlFor="linkerRepInput">Rep</label>
					<input type="text" id="linkerRepInput" name="linkerRepInput" size="8" />
				</div>
				<button type="submit" id="addRepSubmitButton">Add Rep</button>
			</form>
		)
	}
}