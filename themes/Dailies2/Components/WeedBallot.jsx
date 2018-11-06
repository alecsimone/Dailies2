import React from "react";

export default class WeedBallot extends React.Component{
	render() {
		if (this.props.orientation === "Landscape") {
			var style = {
				maxHeight: this.props.height,
				flexBasis: this.props.height / 4 - 10
			};
			var strongButtonStyle = {
				height: this.props.height / 4 - 10 <= 200 ? this.props.height / 4 - 10 : 200,
				width: this.props.height / 4 - 10 <= 200 ? this.props.height / 4 - 10 : 200,
			}
			var weakButtonStyle = {
				height: this.props.height / 8 - 5,
				width: this.props.height / 8 - 5
			}
			var strongImgStyle ={
				height: this.props.height / 12 - 10,
				width: this.props.height / 12 - 10
			}
			var weakImgStyle ={
				height: this.props.height / 24 - 10,
				width: this.props.height / 24 - 10
			}
		} else {
			var style = {};
			let width = jQuery(window).width() - 10;
			let playerHeight = width * 9 / 16;
			let ballotHeight = this.props.height - playerHeight -  140;
			var strongButtonStyle = {
				height: ballotHeight - 24 > 150 ? 150 : ballotHeight - 24,
				width: ballotHeight - 24 > 150 ? 150 : ballotHeight - 24,
			}
		}

		let nukeButton;
		if (dailiesGlobalData.userData.userRole === "author" || dailiesGlobalData.userData.userRole === "editor" || dailiesGlobalData.userData.userRole === "administrator") {
			nukeButton = <button id="nukeButton" onClick={this.props.nukeHandler}>Nuke</button>;
		}

		return(
			<div id="weedBallot" className={'ballot' + this.props.orientation} style={style} >
				<button id="strongNo" className="ballotButton" onClick={this.props.judgeClip} ><img id={"strongNoImg"} src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2018/07/votenay.png'} /></button>
				{nukeButton}
				<button id="strongYes" className="ballotButton" onClick={this.props.judgeClip}><img id={"strongYesImg"} src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2018/07/voteyea.png'} /></button>
			</div>
		);
	}
}

				// <button id="weakNo" className="ballotButton weakButton" style={weakButtonStyle} onClick={this.props.judgeClip}><img id={"weakNoImg"} src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2018/01/Red-Down-Arrow.png'} style={weakImgStyle} /></button>
				// <button id="pass" className="ballotButton weakButton" style={weakButtonStyle} onClick={this.props.judgeClip}><img id={"passImg"} src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2018/08/tilde-50.png'} style={weakImgStyle} /></button>
				// <button id="weakYes" className="ballotButton weakButton" style={weakButtonStyle} onClick={this.props.judgeClip}><img id={"weakYesImg"} src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2018/01/Green-Up-Arrow.png'} style={weakImgStyle} /></button>
