import React from "react";

export default class CoHostButton extends React.Component{
	render() {
		var cohostLinks = [];
		jQuery.each(this.props.cohostData.links, function(key) {
			if (this !== '') {
				if (key === 'twitter_url') {
					var iconsrc = '/wp-content/uploads/2017/01/Twitter-logo.png';
				} else if (key === 'twitch_url') {
					var iconsrc = '/wp-content/uploads/2017/01/Twitch-purple-logo.png';
				} else if (key === 'donate_url') {
					var iconsrc = '/wp-content/uploads/2017/03/Donate-logo.png';
				} else if (key === 'youtube_url') {
					var iconsrc = '/wp-content/uploads/2017/01/youtube-logo.png';
				}
				cohostLinks.push(<a key={key} href={this} target="_blank" className="cohostSocialLink"><img src={dailiesGlobalData.thisDomain + iconsrc} className='cohostSocialLinkImg'/></a>)
			}
		})
		return(
			<div className="cohostButton">
				<div className="cohostLogo"><img src={this.props.cohostData.logo_url} /></div>
				<div className="cohostMeta">
					<div className="cohostName">{this.props.cohostData.hostName}</div>
					<div className="cohostLinks">
						{cohostLinks}
					</div>
				</div>
			</div>
		)
	}
}