import React from "react";
import ReactDOM from 'react-dom';

export default class UserLinker extends React.Component{
	constructor() {
		super();
		this.linkerHandler = this.linkerHandler.bind(this);
	}


	linkerHandler(e) {
		e.preventDefault();
		let linkDataRaw = jQuery(e.target).serializeArray();
		let linkData = {
			dailiesID: linkDataRaw[0].value,
			twitchName: linkDataRaw[1].value,
		}
		if (isNaN(linkData.dailiesID)) {
			console.log("You didn't provide a number for the DailiesID");
			return;
		}
		if (!isNaN(linkData.twitchName)) {
			console.log("You didn't provide a string for the twitchName");
			return;
		}
		this.props.linkUser(linkData);
		e.target.reset();
	}

	render() {
		return(
			<form id="userLinker" onSubmit={this.linkerHandler} >
				<div id="dailiesIDInputWrapper" className="linkerInputWrapper">
					<label htmlFor="linkerDailiesIDInput">DailiesID</label>
					<input type="text" id="linkerDailiesIDInput" name="linkerDailiesIDInput"size="8" />
				</div>
				<div id="twitchNameInputWrapper" className="linkerInputWrapper">
					<label htmlFor="linkerTwitchNameInput">TwitchName</label>
					<input type="text" id="linkerTwitchNameInput" name="linkerTwitchNameInput" size="8" />
				</div>
				<button type="submit" id="userLinkerSubmitButton">link</button>
			</form>
		)
	}
}