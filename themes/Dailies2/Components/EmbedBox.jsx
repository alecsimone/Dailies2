import React from "react";

export default class EmbedBox extends React.Component {
	constructor(props) {
		super();
		this.state = {
			content: '',
		};
		if (props.thumbs !== undefined) {
			var windowWidth = jQuery(window).width();
			if (windowWidth < 400) {
				var size = 'small';
			} else if (windowWidth < 650) {
				var size = 'medium';
			} else {
				var size = 'large';
			}
			this.state.content = [<img src={props.thumbs[size][0]} key={'thumb' + props.embedCode} className="thumb" onClick={() => this.thumbReplacer()} />, <img src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2016/08/playbutton.png'} key={'playbutton' + props.embedCode} className="playbutton" />];
		} else if (props.embedCode !== undefined) {
			var embed = this.generateEmbed(props);
			this.state.content = embed;
		};
		this.generateEmbed = this.generateEmbed.bind(this);
		this.thumbReplacer = this.thumbReplacer.bind(this);
	}

	componentDidUpdate() {
		var twitterVideo = jQuery('#twitterVideo' + this.props.embedCode);
		if (twitterVideo.length && !jQuery('#twitter-widget-0').length) {
			twttr.widgets.createVideo(
  				this.props.embedCode,
  				document.getElementById('twitterVideo' + this.props.embedCode)
			);
		}
	}

	generateEmbed(props) {
		if (props.embedSource === 'TwitchCode') {
			var seedlingInfo = jQuery('.seedlingInfo');
			if (seedlingInfo.length) {
				var seedlingInfoWidth = jQuery('.seedlingInfo').width();
				let heightByWidth = seedlingInfoWidth / 16 * 9;
				let viewportHeight = jQuery(window).height();
				let baseSeedlingHeight = jQuery('.seedlingMeta').outerHeight();
				let heightByViewport = viewportHeight - baseSeedlingHeight - 144;
				if (heightByViewport < heightByWidth) {
					var height = heightByViewport + 'px';
				} else {
					var height = heightByWidth + 'px';
				}
				var width = seedlingInfoWidth + 'px';
			} else {
				var width = "640"; 
				var height = "360";
			}
			var embed = <iframe src={"https://clips.twitch.tv/embed?clip=" + props.embedCode + "&autoplay=true"} width={width} height={height} frameBorder="0" scrolling="no" allowFullScreen="true" />;
		} else if (props.embedSource === 'GFYtitle') {
			var embed = <iframe src={'https://gfycat.com/ifr/' + props.embedCode} frameBorder='0' scrolling='no' width='100%' height='100%' allowFullScreen />;
		} else if (props.embedSource === 'TwitterCode') {
			var embed = <div id={'twitterVideo' + props.embedCode}></div>;
		} else if (props.embedSource === 'YouTubeCode') {
			var embed = <iframe width="1280" height="720" src={"https://www.youtube.com/embed/" + props.embedCode + "?showinfo=0&autoplay=1"} frameBorder="0" allowFullScreen />;
		} else if (props.embedSource === 'TwitchLive') {
			var embed = 'Soon TM';
		}
		return embed;
	}

	thumbReplacer() {
		var embed = this.generateEmbed(this.props);
		this.setState({content: embed});
	}

	render() {
		return(
			<section className="contentbox" id={this.props.embedCode}>
				<div className="embed-container">
					{this.state.content}
				</div>
			</section>
		)
	}

}