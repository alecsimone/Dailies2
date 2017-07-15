import React from "react";

export default class ArchiveHeader extends React.Component{
	render() {
		if (this.props.headerData.logo_url !== '') {
			var logosrc = this.props.headerData.logo_url;
		} else {
			var logosrc = dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/07/rl-logo-med.png';
		}
		var headerLinks = this.props.headerData;
		var headerLinkKeys = Object.keys(headerLinks);
		var headerLinkElements = headerLinkKeys.map(function(key) {
			if (typeof headerLinks[key] === "string" && key != 'logo_url' && headerLinks[key] != '') {
				return(
					<a href={headerLinks[key]} className="archive-data-link" key={key} target="_blank"><img src={dailiesGlobalData.thisDomain + "/wp-content/uploads/socialLogos/" + key + ".png"}></img></a>
				)
			} else {
				return ''
			}
		})
		return(
			<header id="archive-header">
				<div id="archive-left">
					<div id="archive-logo">
						<img src={logosrc} className="archive-logo-img"></img>
					</div>
				</div><div id="archive-right">
					<div id="archive-title">
						<h2>{this.props.headerData.thisTerm.name}</h2>
						<div id="archive-data">
							{headerLinkElements}
						</div>
					</div>
				</div>
			</header>
		)
	}
}