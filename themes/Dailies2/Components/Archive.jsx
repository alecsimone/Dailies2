import React from "react";
import ReactDOM from 'react-dom';
import ArchiveHeader from './ArchiveHeader.jsx';
import Userbox from './Userbox.jsx';
import SortBar from './SortBar.jsx';
import Thing from './Thing.jsx';

class Archive extends React.Component{
	constructor() {
		super();
		this.state = {
			headerData: JSON.parse(jQuery('#dataDrop').attr("data-archive-header")),
			user: {
				id: jQuery('#dataDrop').attr("data-user-id"),
				clientIP: jQuery('#dataDrop').attr("data-client-ip"),
				rep: jQuery('#dataDrop').attr("data-rep"),
				repTime: jQuery('#dataDrop').attr("data-rep-time"),
			}
		};
		this.state.postData = JSON.parse(jQuery('#dataDrop').attr("data-initial-postdata"));
		this.state.voteData = JSON.parse(jQuery('#dataDrop').attr("data-initial-votedata"));
		this.state.order = jQuery('#dataDrop').attr("data-order");
		this.state.orderby = jQuery('#dataDrop').attr("data-orderby");
	}
	render() {
		var voteDataObj = this.state.voteData;
		var userData = this.state.user;
		var things = this.state.postData;
		var thingsArray = Object.keys(things);
		var thingComponents = thingsArray.map(function(key) {
			var parsedThingData = JSON.parse(things[key]);
			var voteData = voteDataObj[parsedThingData['id']];
			return(
				<Thing thingData={parsedThingData} userData={userData} voteData={voteData} key={parsedThingData.id} />
			)
		});

		return(
			<div>
				<section id="archivetop">
					<ArchiveHeader headerData={this.state.headerData} />
					<Userbox userData={this.state.user}/>
				</section>
				<SortBar orderby={this.state.orderby} order={this.state.order} tax={this.state.headerData.thisTerm.taxonomy} slug={this.state.headerData.thisTerm.slug} />
				{thingComponents}
			</div>
		)
	}
}

if (jQuery('#archiveApp').length) {
	ReactDOM.render(
		<Archive />,
		document.getElementById('archiveApp')
	);
}