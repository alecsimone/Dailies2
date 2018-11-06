import React from "react";
import ReactDOM from 'react-dom';
import Leader from './Leader.jsx';
import TopFive from './TopFive.jsx';
import Pleb from './Pleb.jsx';
import {privateData} from '../Scripts/privateData.jsx';
import {playAppropriatePromoSound, playAppropriateKillSound} from '../Scripts/sounds.js';

export default class Hopefuls extends React.Component{
	constructor() {
		super();
		// this.state = {
		// 	clips: hopefulsData
		// }
		this.state = {
			hasData: false,
			locallyCutSlugs: [],
		}

		this.keepSlug = this.keepSlug.bind(this);
		this.cutSlug = this.cutSlug.bind(this);
	}

	keepSlug(newThingName, slug) {
		let clips = this.state.clips;
		let locallyCutSlugs = this.state.locallyCutSlugs;
		clips.shift();
		locallyCutSlugs.push(slug);
		this.setState({
			clips,
			locallyCutSlugs,
		});
		playAppropriatePromoSound();
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'keepSlug',
				newThingName,
				slug,
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: (data) => {
				console.log(data);
			},
		});
	}

	// keepSlug(slugObj, thingData) {
	// 	console.log(slugObj);
	// 	var currentState = this.state;
	// 	currentState.clips.shift();
	// 	this.setState(currentState);
	// 	window.playAppropriatePromoSound();
	// 	var page = this;
	// 	jQuery.ajax({
	// 		type: "POST",
	// 		url: dailiesGlobalData.ajaxurl,
	// 		dataType: 'json',
	// 		data: {
	// 			action: 'keepSlug',
	// 			slugObj,
	// 			thingData,
	// 		},
	// 		error: function(one, two, three) {
	// 			console.log(one);
	// 			console.log(two);
	// 			console.log(three);
	// 		},
	// 		success: function(data) {
	// 			console.log(data);
	// 			if (Number.isInteger(data)) {
	// 				//window.open(dailiesGlobalData.thisDomain + '/wp-admin/post.php?post=' + data + '&action=edit', '_blank');
	// 				jQuery.ajax({
	// 					type: "POST",
	// 					url: dailiesGlobalData.ajaxurl,
	// 					dataType: 'json',
	// 					data: {
	// 						action: 'addSourceToPost',
	// 						channelURL: slugObj.channelURL,
	// 						channelPic: slugObj.channelPic,
	// 						postID: data,
	// 					},
	// 					error: function(one, two, three) {
	// 						console.log(one);
	// 						console.log(two);
	// 						console.log(three);
	// 					},
	// 					success: function(data) {
	// 						console.log(data);
	// 					}
	// 				});
	// 			}
	// 		}
	// 	});
	// }

	componentDidMount() {
		this.updateHopefuls();
		window.setInterval(() => this.updateHopefuls(), 3000);
	}

	updateHopefuls() {
		jQuery.get({
			url: `${dailiesGlobalData.thisDomain}/wp-json/dailies-rest/v1/hopefuls`,
			dataType: 'json',
			success: (data) => {
				let locallyCutSlugs = this.state.locallyCutSlugs;
				data.forEach((hopeful, index) => {
					if (locallyCutSlugs.indexOf(hopeful.slug) > -1) {
						data.splice(index, 1);
					}
				});
				this.sortHopefuls(data);
				this.setState({
					clips: data,
					hasData: true,
				});
			}
		});
	}

	sortHopefuls(hopefulsData) {
		hopefulsData.sort(function(a,b) {
			let scoreA = Number(a.score);
			let scoreB = Number(b.score);
			if (scoreA === scoreB) {
				let timeA = new Date(a.age).getTime();
				let timeB = new Date(b.age).getTime();
				return timeB - timeA;
			}
			return scoreB - scoreA;
		});
		return hopefulsData;
	}

	cutSlug(slugObj, scope) {
		var clips = this.state.clips;
		clips.shift();
		let locallyCutSlugs = this.state.locallyCutSlugs;
		locallyCutSlugs.push(slugObj.slug);
		this.setState({
			clips,
			locallyCutSlugs,
		});
		window.playAppropriateKillSound();
		console.log(slugObj.slug);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'hopefuls_cutter',
				slug: slugObj.slug,
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				console.log(data);
			}
		});
	}

	render() {
		if (!this.state.hasData) {
			return(
				<section id="hopefuls" className="noHope">
					<div>
						<div>Getting Hopefuls...</div>
						<div className="lds-ring"><div></div><div></div><div></div><div></div></div>
					</div>
				</section>
			); 
		}
		if (this.state.clips.length === 0) {
			return(
				<section id="hopefuls" className="noHope">
					<div>There are no hopefuls yet! Maybe go do some <a href={`${dailiesGlobalData.thisDomain}/1r`}>scouting</a> and find us some?</div>
				</section>
			);
		}
		let leader = this.state.clips[0];
		let topfive = [];
		for (var i = 1; i < 7 && i < this.state.clips.length; i++) {
			topfive.push(this.state.clips[i]);
		}
		let topfivecomponents = topfive.map(function(clipdata) {
			return <TopFive key={clipdata.id} clipdata={clipdata} />;
		});
		let plebs = [];
		for (var i = 7; i < this.state.clips.length; i++) {
			plebs.push(this.state.clips[i]);
		}
		let plebcomponents = plebs.map(function(clipdata) {
			return <Pleb key={clipdata.id} clipdata={clipdata} />;
		});
		return(
			<section id="hopefuls">
				<div id="leader">
					<Leader key={leader.id} clipdata={leader} keepSlug={this.keepSlug} cutSlug={this.cutSlug}/>
				</div>
				<div id="topfive">
					{topfivecomponents}
				</div>
				<div id="plebs">
					{plebcomponents}
				</div>
			</section>
		)
	}
}

ReactDOM.render(
	<Hopefuls />,
	document.getElementById('hopefulsApp')
);