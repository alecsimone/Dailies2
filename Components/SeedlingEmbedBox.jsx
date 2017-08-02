import React from "react";

export default class SeedlingEmbedBox extends React.Component{
	render() {
		if (this.props.embed === '') {
			var embedBox = '';
		} else {
			var seedlingInfoWidth = jQuery('.seedlingInfo').width();
			let heightByWidth = seedlingInfoWidth / 16 * 9;
			let viewportHeight = jQuery(window).height();
			let baseSeedlingHeight = jQuery('.seedlingMeta').outerHeight();
			let heightByViewport = viewportHeight - baseSeedlingHeight - 144;
			if (heightByViewport < heightByWidth) {
				var embedHeight = heightByViewport;
			} else {
				var embedHeight = heightByWidth;
			}
			var iframeSrc = 'https://clips.twitch.tv/embed?clip=' + this.props.embed + '&tt_medium=clips_api&tt_content=embed';
			var embedBox = <iframe src={iframeSrc} width={seedlingInfoWidth} height={embedHeight} frameBorder='0' scrolling='no' allowFullScreen='true'></iframe>
		}
		return(
			<div className='seedlingEmbedBox'>
				{embedBox}
			</div>
		)
	}
}