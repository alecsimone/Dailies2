import React from "react";

export default class LiveAdminControls extends React.Component{
	render() {
		var currentCategory = this.props.postCategory;
		var authorID = parseInt(this.props.authorID, 10);

		var downButton;
		var upButton;
		if (dailiesGlobalData.userData.userRole === 'administrator') {
			if (currentCategory === 'Prospects') {
				var downFileDirectory = '/wp-content/uploads/2017/04/red-x.png';
			} else {
				var downFileDirectory = '/wp-content/uploads/2018/01/Red-Down-Arrow.png';
			}
			
			downButton = <img src={dailiesGlobalData.thisDomain + downFileDirectory} className="littleThingTrasher" onClick={(e) => this.props.postDemoter(this.props.thisID)}/>
			
			if (currentCategory !== 'Nominees') {
				upButton = <img src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2018/01/Green-Up-Arrow.png'} className="littleThingPromoter" onClick={(e) => this.props.postPromoter(this.props.thisID)}/>;
			}
		}

		var editButton;
		if (dailiesGlobalData.userData.userID === authorID || dailiesGlobalData.userData.userRole === 'administrator') {
			editButton = <a href={dailiesGlobalData.thisDomain + '/wp-admin/post.php?post=' + this.props.thisID + '&action=edit'} className="editLittleThingLink" target="_blank"><img src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/07/edit-this.png'} className="editThisImg" /></a>
		}


		return(
			<div className="liveAdminControls">
				{downButton}
				<div className="littleThingAdminRightSide">
					{upButton}
					{editButton}
				</div>
			</div>
		)
	}
}