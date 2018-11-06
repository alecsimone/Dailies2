import React from "react";

export default class Streamlist extends React.Component{
	render() {
		var streamListArray = Object.keys(this.props.streamList);
		var filterStreams = this.props.filterStreams;
		var streamFilter = this.props.streamFilter;
		var streamListElements = streamListArray.map(function(key) {
			var active = '';
			if (streamFilter.indexOf(key) > -1) {
				var active = " deactivated";
			}
			return <a className={'streamListItem' + active} key={key} onClick={(e)=>filterStreams(key)}>{key}</a>
		})
		return(
			<nav id="streamList">{streamListElements}</nav>
		)
	}
}