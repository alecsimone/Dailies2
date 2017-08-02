import React from "react";
import ReactDOM from 'react-dom';

export default class Tagbox extends React.Component{
	render() {
		var tagboxID = "thing" + this.props.thisID + "-tagbox";
		var tags = this.props.tags;
		var tagsArray = Object.keys(tags);

		var tagElements = tagsArray.map(function(key) {
			let link = dailiesGlobalData.thisDomain + '/tag/' + tags[key]['slug'];
			let name = tags[key]['name'];
			return(
				<a href={link} key={tags[key]['slug']} className="tagLink">{name}</a>
			)
		});

		var skills = this.props.skills;
		var skillsArray = Object.keys(skills);

		var skillElements = skillsArray.map(function(key) {
			let link = dailiesGlobalData.thisDomain + '/skills/' + skills[key]['slug'];
			let name = skills[key]['name'];
			return(
				<a href={link} key={skills[key]['slug']} className="tagLink">{name}</a>
			)
		});

		return(
			<section id={tagboxID} className="tagbox">More: {tagElements}{skillElements}</section>
		)
	}
}