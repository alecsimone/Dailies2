import React from "react";
import Userbox from './Userbox.jsx';

export default class HomeTop extends React.Component {
	render() {
		return(
			<section id="hometop">
				<div id="propbox">
					<div className="propaganda" id="propLeft">Today's Prize: $25.00</div>
					<div className="propaganda" id="propRight">More Coming Soon...</div>
				</div>
				<Userbox userData={this.props.user} />
			</section>
		)
	}
}