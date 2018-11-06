import React from "react";
import StageFilterButton from "./StageFilterButton.jsx";

export default class StageFilter extends React.Component{

	render() {

		var stages = ['All', 'Prospects', 'Contenders', 'Finalists', 'Nominees'];
		var activeStageFilter = this.props.activeStageFilter;
		var stageChange = this.props.stageChange;
		var stageComponents = stages.map(function(key) {
			if (key === activeStageFilter) {
				var activeStage = 'active';
			} else {
				var activeStage = 'inactive';
			}
			return (
				<StageFilterButton key={key} stage={key} activeStage={activeStage} stageChange={stageChange} />
			)
		});

		return(
			<nav id="StageFilter">
				{stageComponents}
			</nav>
		)
	}

}