import React from "react";
import Userbox from './Userbox.jsx';

export default class HomeTop extends React.Component {
	render() {
		return(
			<section id="hometop">
				<div className="propaganda" id="propLeft">
					<div className="propbutton">Today's Prize: $25</div>
					<a className="propbutton img" href="https://www.patreon.com/rocket_dailies" target="_blank"><img className="patreonImg" src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/04/Patreon.png'} /></a>
					<a className="propbutton" href="https://twitch.streamlabs.com/the_rocket_dailies#/" target="_blank">Donate</a>
				</div>
				<div className="propaganda" id="propRight"><img src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/Choose-Excellence-textonly.png'} className="chooseExcellence" /></div>
				<Userbox userData={this.props.user} />
			</section>
		)
	}
}