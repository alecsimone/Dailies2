import React from "react";
import ReactDOM from 'react-dom';

export default class ThingHeader extends React.Component {
	render() {
		var fixedTitle = this.props.title;
		if (fixedTitle.indexOf('&') > -1) {
			fixedTitle = window.htmlEntityFix(fixedTitle);
		}
		return(
			<header className="titleBox" id={'thing' + this.props.thisID + '-titlebox'}>
				<div className="votecount">(+{this.props.score})</div> <h3><a href={this.props.link}>{fixedTitle}</a></h3>
			</header>
		)
	}
}