import React from "react";
import SortButton from './SortButton.jsx';
import ChannelChangerButton from './ChannelChangerButton.jsx';

export default class ChannelChanger extends React.Component{
	render() {
		var channelData = this.props.channels;
		var channels = Object.keys(channelData);
		var changeChannel = this.props.changeChannel;
		var channelComponents = channels.map(function(key) {
			let thisID = key + "channelChangerButton";
			return(
				<ChannelChangerButton key={thisID} channelKey={key} channelData={channelData[key]} changeChannel={changeChannel} />
			)
		});
		return(
			<section id="channelChanger">
				<SortButton sortLive={this.props.sortLive} sort={this.props.sort} />
				{channelComponents}
			</section>
		)
	}
}