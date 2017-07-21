import React from "react";

export default class SortButton extends React.Component{
	render() {
		return(
			<div id="sortButton" className="channelChangerButton">
				<div className="channelChangerLogo">
					<img src={dailiesGlobalData.thisDomain + "/wp-content/uploads/2017/04/sort.png"} />
				</div>
			</div>
		)
	}
}