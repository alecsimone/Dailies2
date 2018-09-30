import React from "react";
import ReactDOM from 'react-dom';
import Leader from './Leader.jsx';
import TopFive from './TopFive.jsx';
import Pleb from './Pleb.jsx';

export default class Hopefuls extends React.Component{
	constructor() {
		super();
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
		this.state = {
			clips: hopefulsData
		}

		this.keepSlug = this.keepSlug.bind(this);
		this.cutSlug = this.cutSlug.bind(this);
	}

	keepSlug(slugObj, thingData) {
		console.log(slugObj);
		var currentState = this.state;
		currentState.clips.shift();
		this.setState(currentState);
		window.playAppropriatePromoSound();
		var page = this;
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'plant_seed',
				slugObj,
				thingData,
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				console.log(data);
				if (Number.isInteger(data)) {
					//window.open(dailiesGlobalData.thisDomain + '/wp-admin/post.php?post=' + data + '&action=edit', '_blank');
					jQuery.ajax({
						type: "POST",
						url: dailiesGlobalData.ajaxurl,
						dataType: 'json',
						data: {
							action: 'addSourceToPost',
							channelURL: slugObj.channelURL,
							channelPic: slugObj.channelPic,
							postID: data,
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
			}
		});
	}

	cutSlug(slugObj, scope) {
		var currentState = this.state;
		currentState.clips.shift();
		this.setState(currentState);
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
		let leader = this.state.clips[0];
		let topfive = [];
		for (var i = 1; i < 5 && i < this.state.clips.length; i++) {
			topfive.push(this.state.clips[i]);
		}
		let topfivecomponents = topfive.map(function(clipdata) {
			return <TopFive key={clipdata.id} clipdata={clipdata} />;
		});
		let plebs = [];
		for (var i = 5; i < this.state.clips.length; i++) {
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