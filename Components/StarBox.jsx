import React from "react";

export default class StarBox extends React.Component{
	render() {
		var stars = this.props.stars;
		if (stars !== undefined && this.props.stars[0].name !== undefined) {
			let starKeys = Object.keys(stars);
			var starElements = starKeys.map(function(key) {
				let thisID = "Star" + key;
				let starLink = dailiesGlobalData.thisDomain + "/stars/" + stars[key]['slug'];
				return(
					<p key={thisID} className="attribution stars">
						<a className="starSourceImgLink" href={starLink}><img className="starpic" src={stars[key]['logo']} onError={(e) => window.imageError(e)}></img></a><a className="starSourceLink" href={starLink}>{stars[key]['name']}</a> 
					</p>
				)
			});
			if (starKeys.length > 1) {
				var inline = "";
			} else {
				var inline = " inline";
			}
			var classes =  "stars" + inline;
		} else {
			var classes = "stars";
			var thisID = "emptyStar";
			starElements = <p key={thisID} className="attribution stars"><img className="starpic" src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/03/default_pic.jpg'} /></p>
		}

		return(
			<div className={classes}>{starElements}</div>
		)
	}
}