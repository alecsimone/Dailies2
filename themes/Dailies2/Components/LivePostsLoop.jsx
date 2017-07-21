import React from "react";
import LittleThing from './LittleThing.jsx';

export default class LivePostsLoop extends React.Component{
	render() {
		var userData = this.props.user;
		var postDatas = this.props.postData;
		var postIDs = Object.keys(postDatas).reverse();
		var littleThingComponents = postIDs.map(function(postID) {
			let postData = JSON.parse(postDatas[postID]);
			return (
				<LittleThing key={postID} user={userData} postData={postData} />
			)
		});
		return(
			<section id="livePostsLoop">
				{littleThingComponents}
			</section>
		)
	}
}