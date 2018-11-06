import React from "react";
import SortButton from './SortButton.jsx';
import ChannelChangerButton from './ChannelChangerButton.jsx';

export default class ChannelChanger extends React.Component{
	render() {
		var channelData = this.props.channels;
		var channelURLs = [];
		jQuery.each(channelData, function(key) {
			if (channelURLs.indexOf(this.twitchURL) > -1) {
				delete channelData[key];
			} else {
				channelURLs.push(this.twitchURL);
			}
		});
		var channels = Object.keys(channelData);
		var changeChannel = this.props.changeChannel;
		var channelChangerWidth = jQuery('#menu-links').width() * .95;
		if (!jQuery('#channelChanger').length) { //if the contents of the page haven't loaded yet we need to pretend there's a scrollbar
			channelChangerWidth = channelChangerWidth - 17 * .95;
		}
		var channelCount = channels.length + 1;
		var totalButtonSize = channelChangerWidth / channelCount;
		var actualButtonSize = totalButtonSize - 12;
		var size = actualButtonSize;
		if (actualButtonSize < 120) {
			size = 120;
		} else if (actualButtonSize > 192) {
			size = 192;
		}
		var channelComponents = channels.map(function(key) {
			let thisID = key + "channelChangerButton";
			return(
				<ChannelChangerButton key={thisID} channelKey={key} channelData={channelData[key]} changeChannel={changeChannel} size={size}/>
			)
		});
		return(
			<section id="channelChanger">
				<SortButton sortLive={this.props.sortLive} sort={this.props.sort} size={size} />
				{channelComponents}
			</section>
		)
	}
}