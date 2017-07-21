import React from "react";
import ReactDOM from 'react-dom';
import HomeTop from './HomeTop.jsx';
import ChannelChanger from './ChannelChanger.jsx';
import CoHostsPanel from './CoHostsPanel.jsx';
import LivePostsLoop from './LivePostsLoop.jsx';

export default class Live extends React.Component{
	constructor() {
		super();
		this.state = {
			user: dailiesGlobalData.userData,
			channels: liveData.channels,
			postData: liveData.postData,
		}
		this.changeChannel = this.changeChannel.bind(this);
		this.updatePostData = this.updatePostData.bind(this);
	}

	changeChannel(key) {
		console.log(key);
		let currentState = this.state;
		if (currentState.channels[key].active === false) {
			currentState.channels[key].active = true;
		} else {
			currentState.channels[key].active = false;
		}
		this.setState(currentState);
	}

	updatePostData() {
		let currentDate = new Date();
		let tenDaysAgo = currentDate - 1000 * 60 * 60 * 24 * 10;
		tenDaysAgo = new Date(tenDaysAgo);
		let tenDaysAgoYear = tenDaysAgo.getFullYear().toString();
		let tenDaysAgoMonth = tenDaysAgo.getMonth() + 1;
		if (tenDaysAgoMonth < 10) {
			tenDaysAgoMonth = '0' + tenDaysAgoMonth;
		} else {
			tenDaysAgoMonth = tenDaysAgoMonth.toString();
		}
		let tenDaysAgoDay = tenDaysAgo.getDate();
		if (tenDaysAgoDay < 10) {
			tenDaysAgoDay = '0' + tenDaysAgoDay;
		} else {
			tenDaysAgoDay = tenDaysAgoDay.toString();
		}
		let tenDaysAgoFormatted = tenDaysAgoYear + '-' + tenDaysAgoMonth + '-' + tenDaysAgoDay + 'T00:00:00';
		let liveDataQuery = dailiesGlobalData.thisDomain + '/wp-json/wp/v2/posts?categories_exclude=4&after=' + tenDaysAgoFormatted;
		var currentState = this.state;
		var boundThis = this;
		jQuery.get({
			url: liveDataQuery,
			dataType: 'json',
			success: function(data) {
				currentState.postData = {};
				for (var i = 0; i < data.length; i++) {
					let postDataObj = data[i].postDataObj;
					currentState.postData[data[i].id] = postDataObj;
				}
				boundThis.setState(currentState);
			},
		});
	}

	componentDidMount() {
		window.setInterval(this.updatePostData, 5000);
		window.grid = jQuery('#livePostsLoop').isotope({
			itemSelector: '.LittleThing',
			masonry: {
				gutter: 18,
				horizontalOrder: true,
			},
		});
	}

	componentDidUpdate() {
		window.grid.isotope('reloadItems');
		jQuery('#livePostsLoop').isotope({
			itemSelector: '.LittleThing',
			masonry: {
				gutter: 18,
				horizontalOrder: true,
			},
		});
	}

	render() {
		return(
			<section id="Live">
				<HomeTop user={this.state.user} />
				<ChannelChanger channels={this.state.channels} changeChannel={this.changeChannel} />
				<CoHostsPanel />
				<LivePostsLoop user={this.state.user} postData={this.state.postData} />
			</section>
		)
	}
}

ReactDOM.render(
	<Live />,
	document.getElementById('liveApp')
);