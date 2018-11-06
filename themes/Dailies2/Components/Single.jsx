import React from "react";
import ReactDOM from 'react-dom';
import Thing from './Thing.jsx';

export default class Single extends React.Component{
	constructor() {
		super();
		this.state = {
			user: dailiesGlobalData.userData,
			postData: dailiesMainData.singleData.postData,
			voteData: dailiesMainData.singleData.voteData,
		};
	}
	render() {
		var parsedThingData = this.state.postData;
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