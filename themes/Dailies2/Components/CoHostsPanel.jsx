import React from "react";
import CoHostsButton from './CoHostButton.jsx';

export default class CoHostsPanel extends React.Component{
	render() {
		var cohosts = this.props.cohosts;
		var cohostsArray = Object.keys(cohosts);
		var cohostComponents = cohostsArray.map(function(cohost) {
			return <CoHostsButton key={cohost} cohostData={cohosts[cohost]} />
		});
		return(
			<section id="coHostsPanel">
				<div id="coHostsPanelTitle">TONIGHT'S COHOSTS</div>
				{cohostComponents}
			</section>
		)
	}
}