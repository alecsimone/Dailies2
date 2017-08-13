import React from "react";
import StarBox from './StarBox.jsx';

class Source extends React.Component {
	render() {
		var source = this.props.source;
		let sourceKeys = Object.keys(source);

		var sourceElements = sourceKeys.map(function(key) {
			let thisID = "Source" + key;
			let sourceLink = dailiesGlobalData.thisDomain + "/source/" + source[key]['slug'];
			return(
				<p key={thisID} className="attribution source">
					<a className="starSourceImgLink" href={sourceLink}><img className="sourcepic" src={source[key]['logo']} onError={(e) => window.imageError(e, 'source')}></img></a><a className="starSourceLink" href={sourceLink}>{source[key]['name']}</a> 
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
				<StarBox stars={this.props.stars} />
			</section>
		)
	}
}