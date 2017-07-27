import React from "react";
import ReactDOM from 'react-dom';

export default class ThingHeader extends React.Component {
	render() {
		return(
			<header className="titleBox" id={'thing' + this.props.thisID + '-titlebox'}>
				<div className="votecount">(+{this.props.score})</div> <h3><a href={this.props.link}>{this.props.title}</a></h3>
			</header>
		)
	}
}