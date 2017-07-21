import React from "react";

export default class LiveAdminControls extends React.Component{
	render() {
		return(
			<div className="liveAdminControls">
				<a href={dailiesGlobalData.thisDomain + '/wp-admin/post.php?post=' + this.props.thisID + '&action=edit'} className="editLittleThingLink" target="_blank"><img src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/07/edit-this.png'} className="editThisImg" /></a>
				<img src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/04/red-x.png'} className="littleThingTrasher" />
			</div>
		)
	}
}