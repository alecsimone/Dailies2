import React from "react";

export default class WeedBallot extends React.Component{
	render() {
		if (this.props.orientation === "Landscape") {
			var style = {
				maxHeight: this.props.height,
			};
			var strongButtonStyle = {
				height: this.props.height / 4 - 10,
				width: this.props.height / 4 - 10
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
		}
		return(
			<div id="weedBallot" className={'ballot' + this.props.orientation} style={style} >
				<button id="strongNo" className="ballotButton" style={strongButtonStyle} onClick={this.props.judgeClip} ><img id={"strongNoImg"} src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/04/red-x.png'} style={strongImgStyle} /></button>
				<button id="weakNo" className="ballotButton weakButton" style={weakButtonStyle} onClick={this.props.judgeClip}><img id={"weakNoImg"} src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2018/01/Red-Down-Arrow.png'} style={weakImgStyle} /></button>
				<button id="pass" className="ballotButton weakButton" style={weakButtonStyle} onClick={this.props.judgeClip}><img id={"passImg"} src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2018/08/tilde-50.png'} style={weakImgStyle} /></button>
				<button id="weakYes" className="ballotButton weakButton" style={weakButtonStyle} onClick={this.props.judgeClip}><img id={"weakYesImg"} src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2018/01/Green-Up-Arrow.png'} style={weakImgStyle} /></button>
				<button id="strongYes" className="ballotButton" style={strongButtonStyle} onClick={this.props.judgeClip}><img id={"strongYesImg"} src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/12/green-plus.png'} style={strongImgStyle} /></button>
			</div>
		);
	}
}