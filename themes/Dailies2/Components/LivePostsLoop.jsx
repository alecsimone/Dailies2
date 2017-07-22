import React from "react";
import LittleThing from './LittleThing.jsx';

export default class LivePostsLoop extends React.Component{
	render() {
		var userData = this.props.userData;
		var postDatas = this.props.postData;
		var postIDs = Object.keys(postDatas).reverse();
		var postTrasher = this.props.postTrasher;
		if (this.props.sort === true) {
			function littleThingSort(a,b) {
				let parsedA = JSON.parse(postDatas[a]);
				let parsedB = JSON.parse(postDatas[b]);
				let scoreA = parseFloat(parsedA.votecount, 10);
				let scoreB = parseFloat(parsedB.votecount, 10);
				return scoreB - scoreA;
			}
			postIDs = postIDs.sort(littleThingSort);
		}
		var littleThingComponents = postIDs.map(function(postID) {
			let postData = JSON.parse(postDatas[postID]);
			return (
				<LittleThing key={postID} userData={userData} postData={postData} postTrasher={postTrasher} />
			)
		});
		return(
			<section id="livePostsLoop">
				{littleThingComponents}
			</section>
		)
	}
}