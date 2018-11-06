import React from "react";
import ReactDOM from 'react-dom';

export default class UserRoleDropdown extends React.Component{
	componentDidMount() {
		let dropdown = document.getElementById('userRoleDropdown' + this.props.counter);
		dropdown.value = this.props.role;
	}

	selection(e) {
		this.props.changeRole(e.target.value);
	}

	render() {
		return(
			<select id={'userRoleDropdown' + this.props.counter} className="userRoleDropdown" onChange={(e) => this.selection(e) }>
				<option value='--'>--</option>
				<option value="subscriber">Subscriber</option>
				<option value="contributor">Contributor</option>
				<option value="author" >Author</option>
				<option value="editor">Editor</option>
				<option value="administrator">Administrator</option>
			</select>
		)
	}

}