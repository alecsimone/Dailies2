import React from "react";
import ReactDOM from 'react-dom';
import UserRoleDropdown from './UserRoleDropdown.jsx';

export default class UserRow extends React.Component{
	constructor() {
		super();
		this.deleteHandler = this.deleteHandler.bind(this);
		this.changeRole = this.changeRole.bind(this);
	}

	deleteHandler() {
		let deadUserObject = {
			dailiesID: this.props.dailiesID,
			twitchName: this.props.twitchName,
		};
		if (!confirm(`Are you sure you want to delete ${deadUserObject.dailiesID}/${deadUserObject.twitchName}?`)) {return;}
		let thisRowsID = "row" + this.props.counter;
		let thisRow = document.getElementById(thisRowsID);
		thisRow.style.display = 'none';
		this.props.deleteUser(deadUserObject);
	}

	changeRole(newRole) {
		let dailiesID = this.props.dailiesID;
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				dailiesID,
				newRole,
				action: 'UMselectUserRole',
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
		let lastRepTimeShaky = parseInt(this.props.lastRepTime, 10)
		if (lastRepTimeShaky <= 9999999999) {
			var lastRepTimeMilliseconds = lastRepTimeShaky * 1000
		} else {
			var lastRepTimeMilliseconds = lastRepTimeShaky
		}
		let lastRepTime = new Date(lastRepTimeMilliseconds);
		let lastRepMonth = lastRepTime.getMonth() + 1;
		let lastRepDate = lastRepTime.getDate();
		let lastRepYear = lastRepTime.getFullYear().toString().substring(2);
		const daysOfWeek = ['Sun', 'Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat'];
		let lastRepDayofWeek = daysOfWeek[lastRepTime.getDay()];
		var lastRepTimeReadable = `${lastRepMonth}/${lastRepDate}/${lastRepYear}`;

		const rawDailiesID = this.props.dailiesID;
		let dailiesID = rawDailiesID;
		if (rawDailiesID == '-1') {
			dailiesID = '--';
		} else if (rawDailiesID < 10) {
			dailiesID = "000" + rawDailiesID;
		} else if (rawDailiesID < 100) {
			dailiesID = "00" + rawDailiesID;
		} else if (rawDailiesID < 1000) {
			dailiesID = "0" + rawDailiesID;
		};

		let votes = this.props.votes;
		if (votes === '') {
			votes = '0';
		} else {
			if (jQuery.isArray(votes)) {
				votes = votes.length;
			} else if (typeof votes === "object") {
				votes = Object.keys(votes).length
			} else {
				votes = '--';
			}
		};
		
		return(
			<tr id={"row" + this.props.counter} className="UserRow">
				<td>{this.props.counter}</td>
				<td className="picColumn"><img src={this.props.picture} className="userManagementPic" title={this.props.picture} /></td>
				<td className="dailiesIDColumn">{dailiesID}</td>
				<td className="dailiesNameColumn">{this.props.dailiesDisplayName}</td>
				<td>{this.props.twitchName}</td>
				<td>{this.props.rep}</td>
				<td>{lastRepTimeReadable}</td>
				<td>{votes}</td>
				<td className="emailColumn">{this.props.email}</td>
				<td>{this.props.provider}</td>
				<td><UserRoleDropdown changeRole={this.changeRole} role={this.props.role} counter={this.props.counter} /></td>
				<td><input type="text" size="4"/></td>
				<td><input type="checkbox" /></td>
				<td><img className="trash" src="https://dailies.gg/wp-content/uploads/2018/08/trash-80.png" onClick={this.deleteHandler} /></td>
			</tr>
		)
	}
}