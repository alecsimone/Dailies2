import React from "react";

export default class LiveAdminControls extends React.Component{
	constructor() {
		super();
		this.loadTheseVotes = this.loadTheseVotes.bind(this);
		this.handleNomming = this.handleNomming.bind(this);
	}

	loadTheseVotes() {
		var postID = this.props.thisID;
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				postID,
				action: 'load_votes',
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

	handleNomming() {
		var thisThing = jQuery(`#LittleThing${this.props.thisID}`);
		if (thisThing.hasClass("littleThingHighlight")) {
			thisThing.removeClass("littleThingHighlight");
		} else {
			thisThing.addClass("littleThingHighlight");
		}
	}

	render() {
		var currentCategory = this.props.postCategory;
		var authorID = parseInt(this.props.authorID, 10);

		var downButton;
		var upButton;
		if (dailiesGlobalData.userData.userRole === 'administrator') {
			if (currentCategory === 'Contenders') {
				var downFileDirectory = '/wp-content/uploads/2017/04/red-x.png';
			} else {
				var downFileDirectory = '/wp-content/uploads/2018/01/Red-Down-Arrow.png';
			}
			
			downButton = <img src={dailiesGlobalData.thisDomain + downFileDirectory} className="littleThingTrasher" onClick={(e) => this.props.postDemoter(this.props.thisID)}/>
			
			if (currentCategory !== 'Nominees') {
				upButton = <img src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2018/01/Green-Up-Arrow.png'} className="littleThingPromoter" onClick={(e) => this.props.postPromoter(this.props.thisID)}/>;
			}
		}

		var editButton;
		if (dailiesGlobalData.userData.userID === authorID || dailiesGlobalData.userData.userRole === 'administrator') {
			editButton = <a href={dailiesGlobalData.thisDomain + '/wp-admin/post.php?post=' + this.props.thisID + '&action=edit'} className="editLittleThingLink" target="_blank"><img src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/07/edit-this.png'} className="editThisImg" /></a>
		}

		var toNomButton;
		if (currentCategory === 'Contenders' && (dailiesGlobalData.userData.userID === authorID || dailiesGlobalData.userData.userRole === 'administrator') ) {
			toNomButton = <input key={'nomButton' + this.props.thisID} type="checkbox" className="nomCheckbox" onClick={this.handleNomming} />
		}

		var onTheTable;
		if (dailiesGlobalData.userData.userID === authorID || dailiesGlobalData.userData.userRole === 'administrator') {
			onTheTable = <input type="radio" name="onTheTable" onClick={this.loadTheseVotes} />
		}

		return(
			<div className="liveAdminControls">
				{downButton}
				<div className="littleThingAdminRightSide">
					{upButton}
					{editButton}
				</div>
			</div>
		)
	}
}