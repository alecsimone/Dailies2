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
			userData: dailiesGlobalData.userData,
			channels: liveData.channels,
			cohosts: liveData.cohosts,
			postData: liveData.postData,
			sort: false,
		}
		this.changeChannel = this.changeChannel.bind(this);
		this.updatePostData = this.updatePostData.bind(this);
		this.sortLive = this.sortLive.bind(this);
		this.postTrasher = this.postTrasher.bind(this);
	}

	changeChannel(key) {
		let currentState = this.state;
		if (currentState.channels[key].active === false) {
			currentState.channels[key].active = true;
		} else {
			currentState.channels[key].active = false;
		}
		this.setState(currentState);
	}

	sortLive() {
		if (this.state.sort === true) {
			this.setState({sort: false});
		} else {
			this.setState({sort: true});
		}
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
		let liveDataQuery = dailiesGlobalData.thisDomain + '/wp-json/wp/v2/posts?categories_exclude=4&per_page=50&after=' + tenDaysAgoFormatted;
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

	postTrasher(id) {
		var currentState = this.state;
		delete currentState.postData[id];
		this.setState(currentState);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				id: id,
				action: 'post_trasher',
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				//console.log(data);
			}
		});
	}

	componentDidMount() {
		window.setInterval(this.updatePostData, 3000);
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
		var activeFilter = [];
		var postData = jQuery.extend({}, this.state.postData);
		jQuery.each(this.state.channels, function() {
			if (this.active === true) {
				activeFilter.push(this.slug);
			}
		})
		if (activeFilter.length > 0) {
			jQuery.each(postData, function() {
				if (activeFilter.indexOf(JSON.parse(this).taxonomies.source[0].slug) === -1) {
					delete postData[JSON.parse(this).id];
				}
			}) 
		}
		var CoHosts;
		if (this.state.cohosts.length !== 0) {
			var CoHosts = <CoHostsPanel cohosts={this.state.cohosts} />
		}
		return(
			<section id="Live">
				<HomeTop user={this.state.userData} />
				<ChannelChanger channels={this.state.channels} changeChannel={this.changeChannel} sortLive={this.sortLive} sort={this.state.sort} />
				{CoHosts}
				<LivePostsLoop userData={this.state.userData} postData={postData} sort={this.state.sort} postTrasher={this.postTrasher} />
			</section>
		)
	}
}

ReactDOM.render(
	<Live />,
	document.getElementById('liveApp')
);