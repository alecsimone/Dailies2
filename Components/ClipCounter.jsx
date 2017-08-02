	import React from "react";

export default class ClipCounter extends React.Component{
	render() {
		return(
			<div className="clipCounter">
				<p className="clipCount">Found: {this.props.clipCount}</p>
				<p className="clipCount">Cut: {this.props.cutCount}</p>
			</div>
		)
	}
}