import React from "react";
import Seedling from './Seedling.jsx';
import SubmissionSeedling from './SubmissionSeedling.jsx';

export default class Garden extends React.Component{
	render() {
		var cutSlug = this.props.cutSlug;
		var nukeSlug = this.props.nukeSlug;
		var tagSlug = this.props.tagSlug;
		var voteSlug = this.props.voteSlug;
		var keepSlug = this.props.keepSlug;

		var clips = this.props.clips;
		var submissions = this.props.submissions;
		var cutSubmission = this.props.cutSubmission;

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
		var tags = this.props.tags;
		var tags = this.props.tags;
		var cutSlugs = this.props.cutSlugs;
		var seedlingComponents = seedlingsArray.map(function(key) {
			let seedlingData = sortedClips[key];
			let slug = seedlingData.slug;
			var thisSeedlingVoters = [];
			if (voters[slug] !== undefined) {
				thisSeedlingVoters = voters[slug];
			}
			var thisSeedlingTags = [];
			if (tags[slug] !== undefined) {
				thisSeedlingTags = tags[slug];
			}
			var thisSeedlingNuker = [];
			if (cutSlugs[slug] !== undefined) {
				thisSeedlingNuker = (cutSlugs[slug].Nuker);
			}
			return(
				<Seedling seedlingData={seedlingData} key={slug} cutSlug={cutSlug} tagSlug={tagSlug} voteSlug={voteSlug} nukeSlug={nukeSlug} keepSlug={keepSlug} voters={thisSeedlingVoters} tags={thisSeedlingTags} nuker={thisSeedlingNuker} />
			)
		});

		var submissionCounter = 0;
		jQuery(submissions).each(function() {
			seedlingComponents.unshift(
				<SubmissionSeedling key={'submisson' + submissionCounter} submissionData={submissions[submissionCounter]} cutSubmission={cutSubmission}/>
			)
			submissionCounter++;
		});
		return(
			<section id="garden">{seedlingComponents}</section>
		)
	}
}