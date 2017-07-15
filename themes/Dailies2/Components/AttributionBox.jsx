import React from "react";

class Stars extends React.Component {
	render() {
		var stars = this.props.stars;
		let starKeys = Object.keys(stars);

		var starElements = starKeys.map(function(key) {
			let thisID = "Star" + key;
			let starLink = dailiesGlobalData.thisDomain + "/stars/" + stars[key]['slug'];
			return(
				<p key={thisID} className="attribution stars">
					<a className="starSourceImgLink" href={starLink}><img className="starpic" src={stars[key]['logo']}></img></a><a className="starSourceLink" href={starLink}>{stars[key]['name']}</a> 
				</p>
			)
		});
		if (starKeys.length > 1) {
			var inline = "";
		} else {
			var inline = " inline";
		}
		let classes =  "stars" + inline;

		return(
			<div className={classes}>{starElements}</div>
		)
	}
}

class Source extends React.Component {
	render() {
		var source = this.props.source;
		let sourceKeys = Object.keys(source);

		var sourceElements = sourceKeys.map(function(key) {
			let thisID = "Source" + key;
			let sourceLink = dailiesGlobalData.thisDomain + "/source/" + source[key]['slug'];
			return(
				<p key={thisID} className="attribution source">
					<a className="starSourceImgLink" href={sourceLink}><img className="sourcepic" src={source[key]['logo']}></img></a><a className="starSourceLink" href={sourceLink}>{source[key]['name']}</a> 
				</p>
			)
		})

		return(
			<div className="source">{sourceElements}</div>
		)
	}
}

export default class AttributionBox extends React.Component {
	render() {
		var attributionBoxID = "thing" + this.props.thisID + "-attributionBox";
		return(
			<section className="attributionBox" id={attributionBoxID}>
				<Source source={this.props.source} />
				<Stars stars={this.props.stars} />
			</section>
		)
	}
}