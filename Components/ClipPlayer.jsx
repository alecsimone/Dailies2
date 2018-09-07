import React from "react";

export default class ClipPlayer extends React.Component{
	render() {
		let seedlingInfoWidth = this.props.width;
		if (seedlingInfoWidth > 1920) {seedlingInfoWidth = 1920};
		let embedHeight = seedlingInfoWidth * 9 / 16;
		let iframeSrc = 'https://clips.twitch.tv/embed?clip=' + this.props.slug;
		return(
			<iframe src={iframeSrc} width={seedlingInfoWidth} height={embedHeight} frameBorder='0' scrolling='no' allowFullScreen='true'></iframe>
		);
	}
}