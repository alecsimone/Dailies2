import React from "react";
import SeedlingControls from './SeedlingControls.jsx';
import KeepBar from './KeepBar.jsx';
import SeedlingEmbedBox from './SeedlingEmbedBox.jsx';

export default class SeedlingContent extends React.Component{
	render() {
		if (this.props.embed === '') {
			var content = '';
		} else {
			var content = ( 
				<div className="seedlingContentComponents">
					<SeedlingControls slug={this.props.slug} embed={this.props.embed} cutSlug={this.props.cutSlug} voteSlug={this.props.voteSlug} voters={this.props.voters} vodLink={this.props.vodLink} clipTime={this.props.clipTime}/>
					<div className="seedlingContentRight">
						<KeepBar slug={this.props.slug} embed={this.props.embed} keepSlug={this.props.keepSlug} vodLink={this.props.vodLink} clipTime={this.props.clipTime} voters={this.props.voters} source={this.props.source} />
						<SeedlingEmbedBox embed={this.props.embed} />
					</div>
				</div>
			)
		}
		return(
			<div className='seedlingContent'>
				{content}
			</div>
		)
	}
}