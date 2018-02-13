import React from "react";
import LittleThing from './LittleThing.jsx';

export default class LivePostsLoop extends React.Component{
	render() {
		var userData = this.props.userData;
		var postDatas = this.props.postData;
		var postIDs = Object.keys(postDatas).reverse();
		var postTrasher = this.props.postTrasher;
		var postPromoter = this.props.postPromoter;
		var postDemoter = this.props.postDemoter;
		var vote = this.props.vote;
		if (this.props.sort === true) {
			function littleThingSort(a,b) {
				let parsedA = postDatas[a];
				let parsedB = postDatas[b];
				let scoreA = parseFloat(parsedA.votecount, 10);
				let scoreB = parseFloat(parsedB.votecount, 10);
				return scoreB - scoreA;
			}
			postIDs = postIDs.sort(littleThingSort);
		}
		var littleThingComponents = postIDs.map(function(postID) {
			let postData = postDatas[postID];
			return (
				<LittleThing key={postID} userData={userData} postData={postData} postTrasher={postTrasher} postPromoter={postPromoter} postDemoter={postDemoter} vote={vote} />
			)
		});
		if (postIDs.length === 0) {
			if (this.props.unfilteredPostCount === 0) {
				littleThingComponents = <div className="thatsAll">No contenders yet for today. Want to <a href="mailto:submit@therocketdailies.com?subject=Rocket%20Dailies%20Submission">suggest</a> one?</div>
			} else {
				littleThingComponents = <div className="thatsAll">You filtered out all the contenders</div>
			}
		}
		return(
			<section id="livePostsLoop">
				<h4 className="LivePostsLoopHeader brick">{this.props.stage.toUpperCase()}</h4>
				<div className="littleThingsContainer">
					{littleThingComponents}
				</div>
			</section>
		)
	}
}