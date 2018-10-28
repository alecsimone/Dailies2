import React from "react";
import ReactDOM from 'react-dom';

export default class ContentBox extends React.Component {
	constructor(props) {
		super();
		this.thumbReplacer = this.thumbReplacer.bind(this);
		var windowWidth = jQuery(window).width();
		if (windowWidth < 400) {
			var thumbSrc = props.thumbs.small[0];
		} else if (windowWidth < 650) {
			var thumbSrc = props.thumbs.medium[0];
		} else {
			var thumbSrc = props.thumbs.large[0];
		}
		this.state = {
			content: <img src={thumbSrc} className="thumb" onClick={this.thumbReplacer}></img>,
			playButton: <img src="https://dailies.gg/wp-content/uploads/2016/08/playbutton.png" className="playbutton"></img>
		};
	}

	thumbReplacer() {
		if (this.props.embeds.TwitchCode !== '') {
			let clipSrc = "https://clips.twitch.tv/embed?clip=" + this.props.embeds.TwitchCode + "&autoplay=true"
			var replaceCode = <div className="embed-container"><iframe src={clipSrc} width="640" height="360" frameBorder="0" scrolling="no" allowFullScreen="true"></iframe></div>;
		} else if (this.props.embeds.GFYtitle !== '') {
			let clipSrc = "https://gfycat.com/ifr/" + this.props.embed.GFYtitle;
			var replaceCode = <div class='embed-container'><iframe src={clipSrc} frameBorder='0' scrolling='no' width='100%' height='100%' style='position:absolute;top:0;left:0;' allowFullScreen="true"></iframe></div>; 
		} else if (this.props.embeds.YouTubeCode !== '') {
			let clipSrc = "https://www.youtube.com/embed/" + this.props.embeds.YouTubeCode + "?showinfo=0&autoplay=1";
			var replaceCode = <div class='embed-container'><iframe width='1280' height='720' src={clipSrc} frameBorder='0' allowFullScreen="true"></iframe></div>;
		} else if (this.props.embeds.EmbedCode !== '') {
			var replaceCode = this.props.embeds.EmbedCode;
		} else if (this.props.embeds.TwitterCode !== '') {
			var replaceCode = <div id={this.props.embeds.TwitterCode}></div>;
		} else {
			var replaceCode = null;
			return
		}
		this.setState({
			content: replaceCode,
			playButton: '',
		});
	}

	componentDidUpdate() {
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
		var contentboxID = 'thing' + this.props.thisID + '-contentbox';
		return(
			<section className="contentbox" id={contentboxID}>
				{this.state.content}
				{this.state.playButton}
			</section>
		)
	}
}