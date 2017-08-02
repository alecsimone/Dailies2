import React from "react";

export default class SortButton extends React.Component{
	render() {
		if (this.props.sort === true) {
			var classes = "channelChangerButton active";
		} else {
			var classes= "channelChangerButton";
		}
		return(
			<div id="sortButton" className={classes} onClick={this.props.sortLive} style={{width: this.props.size + 6, height: this.props.size + 6}}>
				<div className="channelChangerLogo">
					<img src={dailiesGlobalData.thisDomain + "/wp-content/uploads/2017/04/sort.png"} />
				</div>
			</div>
		)
	}
}