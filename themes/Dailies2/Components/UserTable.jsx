import React from "react";
import ReactDOM from 'react-dom';
import UserRow from './UserRow.jsx';

export default class UserTable extends React.Component{
	constructor() {
		super();
	}

	componentDidMount() {
		jQuery('#UserManagement').tablesorter();
	}

	deleteUser(deadUserObject) {
		console.table(deadUserObject);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				deadUserObject,
				action: 'deleteUser',
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				console.log(data);
			}
		});
	}

	render() {
		var tableRows = [];
		var alreadyProcessedDailiesIDs = [];
		var rowLimit = 100;
		var counter = 1;
		var deleteUser = this.deleteUser;
		jQuery.each(this.props.userDB, function() {
			if (counter > rowLimit) {
				return true;
			}
			var userRow = <UserRow key={counter} deleteUser={deleteUser} counter={counter} picture={this.picture} dailiesID={this.dailiesID} dailiesDisplayName={this.dailiesDisplayName} twitchName={this.twitchName} rep={this.rep} lastRepTime={this.lastRepTime} votes={this.votes} email={this.email} provider={this.provider} role={this.role} hash={this.hash} />;
			tableRows.push(userRow);
			counter++;
		});
		return(
			<table id="UserManagement">
				<thead>
					<tr>
						<th>#</th>
						<th>Pic</th>
						<th className="dailiesIDColumn">dailiesID</th>
						<th className="dailiesNameColumn">dailiesName</th>
						<th>TwitchName</th>
						<th>Rep</th>
						<th>LastRepTime</th>
						<th>Votes</th>
						<th className="emailColumn">Email</th>
						<th>WSL Provider</th>
						<th>Roles</th>
						<th>StarID</th>
						<th>Special</th>
						<th>Del</th>
					</tr>
				</thead>
				<tbody>
					{tableRows}
				</tbody>
			</table>
		)
	}
}