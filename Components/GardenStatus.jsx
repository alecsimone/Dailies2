import React from "react";

export default class GardenStatus extends React.Component{
	render() {
		return(
			<section id="gardenStatus">
			{this.props.message}
			</section>
		)
	}
}