import React from "react";

export default class StageFilterButton extends React.Component{
	render() {

		return (
			<h4 className={"StageFilterButton " + this.props.activeStage} id={this.props.stage} onClick={this.props.stageChange}>{this.props.stage}</h4>
		)
	}
}