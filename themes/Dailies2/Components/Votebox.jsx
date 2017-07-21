import React from "react";

export default class Votebox extends React.Component {
	render() {
		var vote = this.props.vote;
		var userID = this.props.user.userID.toString(10);
		var rep = this.props.user.userRep;
		var IP = this.props.user.clientIP;
		var voters = Object.keys(this.props.voteledger);
		var voted = voters.includes(userID);
		var guestlist = this.props.guestlist;
		if (Array.isArray(guestlist)) {
			var guestIndex = guestlist.indexOf(IP);
			if (guestIndex > -1) {
				var guestVoted = true;
			} else {
				var guestVoted = false;
			}
		} else {
			var guestVoted = false;
		}
		if (voted || guestVoted) {
			var voteButtonSrc = dailiesGlobalData.thisDomain + "/wp-content/uploads/2016/12/Medal-small-100.png";
			var voteButtonReplaceSrc = dailiesGlobalData.thisDomain + "/wp-content/uploads/2017/07/Vote-Icon-light-line-100.png";
		} else {
			var voteButtonSrc = dailiesGlobalData.thisDomain + "/wp-content/uploads/2017/07/Vote-Icon-light-line-100.png";
			var voteButtonReplaceSrc = dailiesGlobalData.thisDomain + "/wp-content/uploads/2016/12/Medal-small-100.png";
		}
		var voteButton = <img className="voteIcon hoverReplacer" onClick={(e) => vote(this.props.thisID)} src={voteButtonSrc} data-replace-src={voteButtonReplaceSrc}></img>;
		return(
			<section className="voteBox">{voteButton}</section>
		)
	}
}