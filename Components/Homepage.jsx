import React from "react";
import ReactDOM from 'react-dom';
import HomeTop from './HomeTop.jsx';
import Thing from './Thing.jsx';
import DayContainer from './DayContainer.jsx';

class Homepage extends React.Component {
	constructor() {
		super();
		this.state = {
			winner: dailiesMainData.firstWinner.postData,
			dayContainers: {
				0: dailiesMainData.dayOne,
			},
		}
		this.state.user = dailiesGlobalData.userData;
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
			let dayContainerCount = Object.keys(this.state.dayContainers).length;
			let lastDayContainer = this.state.dayContainers[dayContainerCount - 1];
			var currentDay = lastDayContainer['date']['day'];
			if (currentDay < 10 && currentDay.charAt(0) !== '0') {
				currentDay = '0' + currentDay;
			}
			var currentMonth = lastDayContainer['date']['month'];
			if (currentMonth < 10 && currentMonth.charAt(0) !== '0') {
				currentMonth = '0' + currentMonth;
			}
			var currentYear = lastDayContainer['date']['year'];
			var currentDayObject = new Date(currentYear, currentMonth-1, currentDay);

			this.stepBackDayAndQuery(currentDayObject, currentYear, currentMonth, currentDay);
		}
	}

	stepBackDayAndQuery(currentDayObject, currentYear, currentMonth, currentDay) {
		var newDayObject = currentDayObject - 1000 * 60 * 60 * 24;
		newDayObject = new Date(newDayObject);
		let newYear = newDayObject.getFullYear().toString();
		let newMonth = newDayObject.getMonth() + 1;
		if (newMonth < 10) {
			newMonth = '0' + newMonth;
		} else {
			newMonth = newMonth.toString();
		}
		let newDay = newDayObject.getDate();
		if (newDay < 10) {
			newDay = '0' + newDay;
		} else {
			newDay = newDay.toString();
		}
		let currentFormattedDate = currentYear + '-' + currentMonth + '-' + currentDay;
		let nextFormattedDate = newYear + '-' + newMonth + '-' + newDay;
		let nextDateQuery = dailiesGlobalData.thisDomain + '/wp-json/wp/v2/posts?after=' + nextFormattedDate + 'T00:00:00&before=' + currentFormattedDate + 'T00:00:00&categories=4';
		jQuery.get({
			url: nextDateQuery,
			dataType: 'json',
			success: function(data) {
				if (data.length === 0) {
					this.stepBackDayAndQuery(newDayObject, newYear, newMonth, newDay);
				} else {
					let newPostDatas = [];
					let newVoteDatas = {};
					jQuery.each(data, function(index, allData) {
						newPostDatas.push(allData.postDataObj);;
						newVoteDatas[allData.id] = {
							votecount: allData.votecount[0],
							voteledger: allData.voteledger[0],
							guestlist: allData.guestlist[0],
						};
					});
					let newDate = {
						day: newDay,
						month: newMonth,
						year: newYear,
					};
					let oldDayContainers = this.state.dayContainers;
					let dayContainerCounter = Object.keys(oldDayContainers).length;
					oldDayContainers[dayContainerCounter] = {
						date: newDate,
						postDatas: newPostDatas,
						voteDatas: JSON.stringify(newVoteDatas),
					}
					this.setState({
						dayContainers: oldDayContainers,
						loadingMore: false,
					});
				}
			}.bind(this)
		});
	}

	render() {
		var userData = this.state.user;
		var winnerVoteData = dailiesMainData.firstWinner.voteData;
		var dayContainers = this.state.dayContainers;
		var dayContainersArray = Object.keys(dayContainers);
		var dayContainerComponents = dayContainersArray.map(function(key) {
			if (dayContainers[key]['postDatas'].length > 0) {
				if (dayContainers[key]['date']['month'] < 10) {
					var monthString = '0' + dayContainers[key]['date']['month'].toString();
				} else {
					var monthString = dayContainers[key]['date']['month'].toString();
				}
				if (dayContainers[key]['date']['day'] < 10) {
					var dayString = '0' + dayContainers[key]['date']['day'].toString();
				} else {
					var dayString = dayContainers[key]['date']['day'].toString();
				}
				let dateKey = dayContainers[key]['date']['year'].toString() + monthString + dayString;
				return(
					<DayContainer dayData={dayContainers[key]} userData={userData} key={dateKey} />
				)
			}
		})

		return(
			<div id="appContainer">
				<HomeTop user={this.state.user} />
				<section id="homePagePosts">
					<Thing thingData={this.state.winner} userData={this.state.user} voteData={winnerVoteData} />
					{dayContainerComponents}
				</section>
			</div>
		)
	}
}

if (jQuery('#homepageApp').length) {
	ReactDOM.render(
		<Homepage />,
		document.getElementById('homepageApp')
	);
}