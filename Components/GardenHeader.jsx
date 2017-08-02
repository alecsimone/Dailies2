import React from "react";
import ClipCounter from './ClipCounter.jsx';
import AddStreamBox from './AddStreamBox.jsx';
import AddRLButton from './AddRLButton.jsx';

export default class GardenHeader extends React.Component{
	render() {
		return(
			<section id="gardenHeader">
				<ClipCounter clipCount={this.props.clipCount} cutCount={this.props.cutCount} />
				<AddStreamBox addStream={this.props.addStream} />
				<AddRLButton addStream={this.props.addStream} />
			</section>
		)
	}
}