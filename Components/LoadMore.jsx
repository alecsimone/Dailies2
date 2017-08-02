import React from "react";

export default class LoadMore extends React.Component{
	render() {
		return(
			<button id="loadMore" onClick={this.props.pushFurther}>Load More</button>
		)
	}
}