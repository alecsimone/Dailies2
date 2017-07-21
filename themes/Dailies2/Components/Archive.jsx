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
			headerData: dailiesMainData.headerData,
			user: dailiesGlobalData.userData,
		};
		this.state.postData = dailiesMainData.initialArchiveData.postData;
		this.state.voteData = dailiesMainData.initialArchiveData.voteData;
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