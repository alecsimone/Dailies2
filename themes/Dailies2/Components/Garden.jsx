import React from "react";
import Seedling from './Seedling.jsx';

export default class Garden extends React.Component{
	render() {
		var clips = this.props.clips;
		function clipsByViews(a,b) {
			return b.views - a.views
		}
		var sortedClips = clips.sort(clipsByViews);
		var seedlingsArray = Object.keys(sortedClips);
		var tickUpCut = this.props.tickUpCut;
		var cutSlug = this.props.cutSlug;
		var voteSlug = this.props.voteSlug;
		var keepSlug = this.props.keepSlug;
		var cutSlugs = this.props.cutSlugs;
		var seedlingComponents = seedlingsArray.map(function(key) {
			let seedlingData = clips[key];
			let slug = seedlingData.slug;
			var voters = [];
			if (cutSlugs[slug] !== undefined) {
				if (cutSlugs[slug].cutBoolean === true || cutSlugs[slug].cutBoolean === 'true') {
					return '';
				} else if (cutSlugs[slug].likeIDs !== undefined) {
					voters = cutSlugs[slug].likeIDs;
				}
			}
			return(
				<Seedling seedlingData={seedlingData} key={slug} cutSlug={cutSlug} voteSlug={voteSlug} keepSlug={keepSlug} voters={voters} />
			)
		});
		return(
			<section id="garden">{seedlingComponents}</section>
		)
	}
}