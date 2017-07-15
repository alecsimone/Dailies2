import React from "react";
import ReactDOM from 'react-dom';

export default class ThingHeader extends React.Component {
	render() {
		var thingID = 'thing' + this.props.thisID + '-titlebox';
		var votecountID = 'thing' + this.props.thisID + '-votecount';
		return(
			<header className="titlebox" id={thingID}>
				<div id={votecountID} className="votecount">(+{this.props.score})</div> <h3><a href={this.props.link}>{this.props.title}</a></h3>
			</header>
		)
	}
}