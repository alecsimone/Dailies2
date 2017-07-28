import React from "react";

export default class ChannelChangerButton extends React.Component{
	render() {
		if (this.props.channelData.active === true) {
			var classes = "channelChangerButton active";
		} else {
			var classes = "channelChangerButton";
		}
		return(
			<div className={classes} onClick={(e) => this.props.changeChannel(this.props.channelKey)} style={{width: this.props.size}}>
				<div className="channelChangerLogo">
					<a href={dailiesGlobalData.thisDomain + "/source/" + this.props.channelData.slug} target="_blanK"><img src={this.props.channelData.logo} /></a>
				</div>
				<div className="channelChangerDetails">
					<div className="channelDisplayName">
						{this.props.channelData.displayName}
					</div>
					<div className="channelInfo">
						{this.props.channelData.details}
					</div>
					<div className="channelTime">
						{this.props.channelData.time}
					</div>
				</div>
			</div>
		)
	}
}