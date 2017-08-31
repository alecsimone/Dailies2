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
		this.state.page = 0;
		this.handleScroll = this.handleScroll.bind(this);
		window.addEventListener("scroll", this.handleScroll);
	}

	handleScroll() {
		var windowHeight = jQuery(window).height();
		var pageHeight = jQuery(document).height();
		var scrollTop = jQuery(window).scrollTop();
		if (scrollTop + 2 * windowHeight > pageHeight && !this.state.loadingMore) {
			this.setState({
				loadingMore: true,
			});
			var currentState = this.state;
			var queryURL = dailiesGlobalData.thisDomain + '/wp-json/wp/v2/posts?categories=4&' + this.state.headerData.thisTerm.taxonomy + '=' + this.state.headerData.thisTerm.term_id + '&offset=' + (this.state.page + 1) * 10 + '&filter[orderby]=' + dailiesMainData.initialArchiveData.orderby + '&order=' + dailiesMainData.initialArchiveData.order.toLowerCase();
			console.log(queryURL);
			jQuery.get({
				url: queryURL,
				dataType: 'json',
				success: function(data) {
					if (data.length > 0) {
						let newPostDatas = [];
						let newVoteDatas = {};
						jQuery.each(data, function(index, allData) {
							currentState.postData.push(allData.postDataObj[0]);
							currentState.voteData[allData.id] = {
								votecount: allData.votecount[0],
								voteledger: allData.voteledger[0],
								guestlist: allData.guestlist[0],
							};
						});
						currentState.loadingMore = false;
						currentState.page++;
						this.setState(currentState);
					} else {
						this.setState({
							tappedOut: true,
						});
					}
				}.bind(this)
			});
		}
	}

	render() {
		var voteDataObj = this.state.voteData;
		var userData = this.state.user;
		var things = this.state.postData;
		var thingsArray = Object.keys(things);
		var thingComponents = thingsArray.map(function(key) {
			var parsedThingData = things[key];
			var voteData = voteDataObj[parsedThingData['id']];
			return(
				<Thing thingData={parsedThingData} userData={userData} voteData={voteData} key={parsedThingData.id} />
			)
		});
		var thatsAll;
		if (this.state.tappedOut === true) {
			var thatsAll = <div className="thatsAll">That's all, folks!</div>;
		}

		return(
			<div>
				<section id="archivetop">
					<ArchiveHeader headerData={this.state.headerData} />
					<Userbox userData={this.state.user}/>
				</section>
				<SortBar orderby={this.state.orderby} order={this.state.order} tax={this.state.headerData.thisTerm.taxonomy} slug={this.state.headerData.thisTerm.slug} />
				{thingComponents}
				{thatsAll}
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