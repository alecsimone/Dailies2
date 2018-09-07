import React from "react";
import SeedlingMeta from './SeedlingMeta.jsx';
import SeedlingContent from './SeedlingContent.jsx';

export default class Seedling extends React.Component{
	constructor() {
		super();
		this.state = {
			embed: '',
		};
		this.toggleClipEmbed = this.toggleClipEmbed.bind(this);
	}

	toggleClipEmbed(e) {
		e.preventDefault();
		var slug = this.props.seedlingData.slug
		var currentState = this.state;
		if (this.state.embed === '') {
			currentState.embed = slug;
		} else {
			currentState.embed = '';
		}
		this.setState(currentState);
	}
	render() {
		var slug = this.props.seedlingData.slug;
		if (this.props.seedlingData.vod == null) {
			var vodLink = "null"
		} else {
			var vodLink = this.props.seedlingData.vod.url;
		}
		return(
			<div className={'seedling ' + this.props.seedlingData.broadcaster.name} id={slug}>
				<SeedlingMeta title={this.props.seedlingData.title} permalink={this.props.seedlingData.url} embedder={this.toggleClipEmbed} viewCount={this.props.seedlingData.views} clipper={this.props.seedlingData.curator.display_name} clipTime={this.props.seedlingData.created_at} vodLink={vodLink} broadcaster={this.props.seedlingData.broadcaster} score={this.props.score} voters={this.props.voters} tags={this.props.tags} nuker={this.props.nuker} />
				<SeedlingContent slug={slug} vodLink={vodLink} clipTime={this.props.seedlingData.created_at} embed={this.state.embed} cutSlug={this.props.cutSlug} nukeSlug={this.props.nukeSlug} tagSlug={this.props.tagSlug} voteSlug={this.props.voteSlug} keepSlug={this.props.keepSlug} voters={this.props.voters} source={this.props.seedlingData.broadcaster} />
			</div>
		)
	}
}