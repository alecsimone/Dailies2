import React from "react";
import ReactDOM from 'react-dom';
import UserRow from './UserRow.jsx';
import UserTable from './UserTable.jsx';
import UserLinker from './UserLinker.jsx';
import UserAddRep from './UserAddRep.jsx';

export default class UserManagement extends React.Component{
	constructor() {
		super();
		this.state = {
			UserDB: userManagementData
		};
		// if (this.state.UserDB === undefined || this.state.UserDB === '') {
		// 	this.createDB();
		// }
		this.deleteUser = this.deleteUser.bind(this);
		this.linkUser = this.linkUser.bind(this);
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

	linkUser(linkUserObject) {
		console.table(linkUserObject);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				linkUserObject,
				action: 'linkUser',
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

	addRepToUser(addRepObject) {
		console.table(addRepObject);
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				addRepObject,
				action: 'addRepToUser',
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

	createDB() {
		let userDB = {};
		let counter = 1;
		let alreadyProcessedDailiesIDs = [];
		jQuery.each(this.state.twitchUserDB, (user) => {
			let dailiesID = this.state.twitchUserDB[user].dailiesUserID;
			if (dailiesID !== 'none') {
				let dailiesData;
				jQuery.each(this.state.wp_users, function() {
					if (this.basic.ID == dailiesID) {
						dailiesData = this;
					}
				});

				let picture;
				if (this.state.twitchUserDB[user].twitchPic !== 'none') {
					picture = this.state.twitchUserDB[user].twitchPic;
				} else if (dailiesData.custom.custom_pic !== '') {
					picture = dailiesData.custom.custom_pic;
				} else if (dailiesData.meta.wsl_current_user_image !== undefined) {
					picture = dailiesData.meta.wsl_current_user_image[0];
				} else {
					picture = 'https://dailies.gg/wp-content/uploads/2017/03/default_pic.jpg';
				}

				let rep;
				if (this.state.twitchUserDB[user].rep >= dailiesData.custom.rep) {
					rep = this.state.twitchUserDB[user].rep;
				} else {
					rep = dailiesData.custom.rep;
				}
				if (rep > 100) {rep = 100};

				let lastRepTime;
				let dailiesLastRepTime = dailiesData.custom.lastRepTime;
				if (dailiesLastRepTime === '') {
					dailiesLastRepTime = 0
				} else {
					dailiesLastRepTime = dailiesLastRepTime * 1000;
				};
				if (this.state.twitchUserDB[user].lastRepTime >= dailiesLastRepTime) {
					lastRepTime = this.state.twitchUserDB[user].lastRepTime;
				} else {
					lastRepTime = dailiesLastRepTime;
				}

				let email = dailiesData.basic.data.user_email;
				if (email.indexOf('example.com') > -1) {email = '--'};

				let provider;
				if (dailiesData.meta.wsl_current_provider == undefined) {
					provider = "--";
				} else {
					provider = dailiesData.meta.wsl_current_provider[0];
				}

				var userRow = {
					counter,
					picture,
					dailiesID,
					dailiesDisplayName: dailiesData.basic.data.display_name,
					twitchName: user,
					rep,
					lastRepTime,
					votes: dailiesData.custom.voteHistory,
					email,
					provider,
					role: Object.keys(dailiesData.basic.caps)[0],
				};
				alreadyProcessedDailiesIDs.push(parseInt(dailiesID, 10));
			} else {
				let rep = this.state.twitchUserDB[user].rep;
				if (rep > 100) {rep = 100};
				var userRow = {
					counter,
					picture: this.state.twitchUserDB[user].twitchPic,
					dailiesID: '--',
					dailiesDisplayName: '--',
					twitchName: user,
					rep,
					lastRepTime: this.state.twitchUserDB[user].lastRepTime,
					votes: '--',
					email: '--',
					provider: 'Twitch',
					role: '--',
				}
			}
			userDB[counter] = userRow;
			counter++;
		});

		jQuery.each(this.state.wp_users, function() {
			if (alreadyProcessedDailiesIDs.indexOf(this.basic.ID) > -1) {
				return true; //return true is the jquery.each equivalent of continue
			}

			let picture;
			if (this.meta.wsl_current_user_image == undefined) {
				picture = this.custom.custom_pic;
			} else {
				picture = this.meta.wsl_current_user_image[0];
			}

			let rep = this.custom.rep;
			if (rep > 100) {rep = 100};

			let email = this.basic.data.user_email;
			if ( email.indexOf('@example.com') > -1 ) {email = '--'};

			let lastRepTime = this.custom.lastRepTime;
			if (lastRepTime === '') {
				lastRepTime = 0
			} else {
				lastRepTime = lastRepTime * 1000;
			};

			let votes = this.custom.voteHistory;

			let provider;
			if (this.meta.wsl_current_provider == undefined) {
				provider = "--";
			} else {
				provider = this.meta.wsl_current_provider[0];
			}
			var userRow = {
				counter,
				picture,
				dailiesID: this.basic.ID,
				dailiesDisplayName: this.basic.data.display_name,
				twitchName: '--',
				rep,
				email,
				lastRepTime,
				provider,
				role: Object.keys(this.basic.caps)[0],
			};
			userDB[counter] = userRow;
			counter++;
		});
		this.state.UserDB = userDB;
	}

	render() {
		return(
			<section id="UserManagementPage">
				<UserAddRep addRepToUser={this.addRepToUser} />
				<UserLinker linkUser={this.linkUser} />
				<UserTable userDB={this.state.UserDB} />
			</section>
		)
	}
}

if (dailiesGlobalData.userData.userRole === 'administrator') {
	ReactDOM.render(
		<UserManagement />,
		document.getElementById('userManagementApp')
	);
}