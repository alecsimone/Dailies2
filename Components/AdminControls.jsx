import React from "react";

export default class AdminControls extends React.Component {
	render() {
		var declareWinnerButton;
		if (this.props.isWinner !== true) {
			declareWinnerButton = <img className="declareWinner" src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2016/12/Medal-small-100.png'} onClick={this.props.declareWinner} />
		}
		return(
			<div className="adminControls">
				<input type='text' placeholder="+Score" className="addScoreBox" onKeyDown={this.props.addScore} />
				{declareWinnerButton}
				<a href={dailiesGlobalData.thisDomain + '/wp-admin/post.php?post=' + this.props.thisID + '&action=edit'} className="editLittleThingLink" target="_blank"><img src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/07/edit-this.png'} className="editThisImg" /></a>
			</div>
		)
	}
}