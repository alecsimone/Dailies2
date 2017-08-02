import React from "react";

export default class Streamlist extends React.Component{
	render() {
		var streamListArray = Object.keys(this.props.streamList);
		var streamListElements = streamListArray.map(function(key) {
			return <a className="streamListItem" key={key}>{key}</a>
		})
		return(
			<nav id="streamList">{streamListElements}</nav>
		)
	}
}