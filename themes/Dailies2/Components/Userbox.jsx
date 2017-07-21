import React from "react";
import ReactDOM from 'react-dom';

export default class Userbox extends React.Component {
	render() {
			if (this.props.userData.id != "0") {
				let thisDomain = dailiesGlobalData.thisDomain;
				var userboxElements = (
					<div id="userbox-links">
						<p className="userbox"><a href={thisDomain + "/your-votes"}>Your Votes</a></p>
						<p className="userbox"><a href={thisDomain + "/secret-garden"}>Secret Garden</a></p>
						<p className="userbox"><a href={dailiesGlobalData.logoutURL}>Logout</a></p>
					</div>
				)
			} else {
				var userboxElements = (
					<div id="userbox-links">
						<p className="userbox">Your votes count as much as your Rep. New members get 1</p>
						<p className="userbox">Vote daily and your Rep will grow</p>
					</div>
				);
				jQuery('#wp-social-login').appendTo('#userbox-links');
			}
			if (this.props.userData.userRep !== '') {
				var rep = this.props.userData.userRep;
			} else {
				var rep = 0.1;
			}

		return(
			<div id="userbox">
				<header id="repHeader"> Your Rep: {rep}</header>
				{userboxElements}
			</div>
		)
	}
}