import React from "react";

export default class SubmissionsOpenToggle extends React.Component{
	constructor() {
		super();	
		this.lockSubmissions = this.lockSubmissions.bind(this);
	}

	lockSubmissions() {
		if (dailiesGlobalData.userData.userRole !== 'administrator') {
			return;
		}
		if (dailiesGlobalData.submissionsOpen === 'true') {
			if (confirm('Do you want to lock submissions?')) {
				var intendedToggle = 'false';
			}
		} else {
			if (confirm('Do you want to open submissions?')) {
				var intendedToggle = 'true';
			}
		}

		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'toggleSubmissions',
				intendedToggle,
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				console.log(data);
			},
		});
	}	

	render() {
		if (dailiesGlobalData.submissionsOpen === 'true') {
			var submissionsStatus = 'open';
		} else {
			var submissionsStatus = 'locked';
		}
		return(
			<p id="submissionsToggle" onClick={this.lockSubmissions}>Submissions are <span className="strong">{submissionsStatus}</span></p>
		);
	}

}