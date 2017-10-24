	import React from "react";
import Seedling from './Seedling.jsx';

export default class Garden extends React.Component{
	render() {
		var cutSlug = this.props.cutSlug;
		var voteSlug = this.props.voteSlug;
		var keepSlug = this.props.keepSlug;

		var clips = this.props.clips;
		function clipsByViews(a,b) {
			if (b.views === a.views) {
				let bDate = Date.parse(b.created_at);
				let aDate = Date.parse(a.created_at);
				return bDate - aDate;
			} else {
				return b.views - a.views;
			}
		}
		var sortedClips = clips.sort(clipsByViews);

		var seedlingsArray = Object.keys(sortedClips);
		var voters = this.props.voters;
		var seedlingComponents = seedlingsArray.map(function(key) {
			let seedlingData = sortedClips[key];
			let slug = seedlingData.slug;
			var thisSeedlingVoters = [];
			if (voters[slug] !== undefined) {
				thisSeedlingVoters = voters[slug];
			}
			return(
				<Seedling seedlingData={seedlingData} key={slug} cutSlug={cutSlug} voteSlug={voteSlug} keepSlug={keepSlug} voters={thisSeedlingVoters} />
			)
		});
		return(
			<section id="garden">{seedlingComponents}</section>
		)
	}
}