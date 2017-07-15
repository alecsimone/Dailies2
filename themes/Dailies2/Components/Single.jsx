import React from "react";
import ReactDOM from 'react-dom';
import Thing from './Thing.jsx';

export default class Single extends React.Component{
	constructor() {
		super();
		this.state = {
			user: {
				id: jQuery('#dataDrop').attr("data-user-id"),
				clientIP: jQuery('#dataDrop').attr("data-client-ip"),
				rep: jQuery('#dataDrop').attr("data-rep"),
				repTime: jQuery('#dataDrop').attr("data-rep-time"),
			},
			postData: JSON.parse(jQuery('#dataDrop').attr("data-postdata")),
			voteData: JSON.parse(jQuery('#dataDrop').attr("data-votedata")),
		};
	}
	render() {
		var parsedThingData = JSON.parse(this.state.postData);
		var voteData = this.state.voteData[parsedThingData.id];
		return(
			<Thing thingData={parsedThingData} userData={this.state.user} voteData={voteData} key={parsedThingData.id}/>
		)
	}
}

if (jQuery('#singleApp').length) {
	ReactDOM.render(
		<Single />,
		document.getElementById('singleApp')
	);
}