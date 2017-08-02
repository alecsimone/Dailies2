import React from "react";

export default class LittleThingEmbedder extends React.Component{
	componentDidMount() {
		var twitterVideo = jQuery('#' + this.props.embeds.TwitterCode);
		if (twitterVideo.length) {
			console.log(this.props.embeds.TwitterCode);
			twttr.widgets.createVideo(
  				this.props.embeds.TwitterCode,
  				document.getElementById(this.props.embeds.TwitterCode)
			);
		}
	}
	render() {
		if (this.props.embeds.TwitchCode !== '') {
			var embed = <iframe src={"https://clips.twitch.tv/embed?clip=" + this.props.embeds.TwitchCode + "&autoplay=true"} width="640" height="360" frameBorder="0" scrolling="no" allowFullScreen="true"></iframe>
		} else if (this.props.embeds.GFYtitle !== '') {
			var embed = <iframe src={'https://gfycat.com/ifr/' + this.props.embeds.GFYtitle} frameBorder='0' scrolling='no' width='100%' height='100%' allowFullScreen></iframe>
		} else if (this.props.embeds.YouTubeCode !== '') {
			var embed = <iframe width="1280" height="720" src={"https://www.youtube.com/embed/" + this.props.embeds.YouTubeCode + "?showinfo=0&autoplay=1"} frameBorder="0" allowFullScreen></iframe>;
		} else if (this.props.embeds.TwitterCode !== '') {
			var embed = <div id={this.props.embeds.TwitterCode}></div>
		}
		return(
			<div className="embed-container">{embed}</div>
		)
	}
}