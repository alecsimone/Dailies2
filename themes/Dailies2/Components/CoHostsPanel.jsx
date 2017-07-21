import React from "react";
import CoHostsButton from './CoHostButton.jsx';

export default class CoHostsPanel extends React.Component{
	render() {
		return(
			<section id="coHostsPanel">
				<CoHostsButton />
			</section>
		)
	}
}