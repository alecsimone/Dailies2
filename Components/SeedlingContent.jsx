import React from "react";
import SeedlingControls from './SeedlingControls.jsx';
import KeepBar from './KeepBar.jsx';
import EmbedBox from './EmbedBox.jsx';

export default class SeedlingContent extends React.Component{
	render() {
		if (this.props.embed === '') {
			var content = '';
		} else {
			var keepBar;
			if (dailiesGlobalData.userData.userRole === 'administrator' || dailiesGlobalData.userData.userRole === 'editor' || dailiesGlobalData.userData.userRole === 'author' || dailiesGlobalData.userData.userRole === 'editor' || dailiesGlobalData.userData.userRole === 'contributor' ) {
				keepBar = <KeepBar slug={this.props.slug} embed={this.props.embed} keepSlug={this.props.keepSlug} vodLink={this.props.vodLink} clipTime={this.props.clipTime} voters={this.props.voters} source={this.props.source.channel_url} sourcePic={this.props.source.logo} />;
			}
			var content = ( 
				<div className="seedlingContentComponents">
					<SeedlingControls slug={this.props.slug} embed={this.props.embed} cutSlug={this.props.cutSlug} nukeSlug={this.props.nukeSlug} tagSlug={this.props.tagSlug} voteSlug={this.props.voteSlug} voters={this.props.voters} vodLink={this.props.vodLink} clipTime={this.props.clipTime}/>
					<div className="seedlingContentRight">
						{keepBar}
						<EmbedBox embedCode={this.props.embed} embedSource='TwitchCode' />
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