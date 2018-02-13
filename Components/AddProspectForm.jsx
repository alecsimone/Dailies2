import React from "react";
import ReactDOM from 'react-dom';
import {privateData} from '../Scripts/privateData.jsx';


export default class AddProspectForm extends React.Component{
	constructor() {
		super();
		this.postClip = this.postClip.bind(this);
		this.inputListener = this.inputListener.bind(this);
	}

	inputListener(e) {
		if (e.which === 13) {
			this.postClip();
		}
	}
	postClip() {
		var titleBox = jQuery('#AddProspectTitleBox');
		var title = titleBox.val();
		var urlBox = jQuery('#AddProspectURLBox');
		var url = urlBox.val();

		function redFlash(target) {
			target.addClass('redFlash');
			setTimeout(function() {target.removeClass('redFlash')
			}, 1000);
		}

		if (title === '') {
			jQuery('#AddProspectInstructions').text("You didn't enter a title");
			redFlash(titleBox);
			return
		} else if (url === '') {
			jQuery('#AddProspectInstructions').text("You didn't enter a URL");
			redFlash(urlBox);
			return
		}

		var isTwitch = url.indexOf('twitch.tv/');
		var isYouTube = url.indexOf('youtube.com/');
		var isYtbe = url.indexOf('youtu.be/');
		var isTwitter = url.indexOf('twitter.com/');
		var isGfy = url.indexOf('gfycat.com/');

		if (isTwitch === -1 && isYouTube === -1 && isYtbe === -1 && isTwitter === -1 && isGfy === -1) {
			jQuery('#AddProspectInstructions').text("Invalid URL");
			redFlash(urlBox);
			return
		}

		if (title.length < 3) {
			jQuery('#AddProspectInstructions').text("Try harder on that title, please");
			redFlash(titleBox);
			return
		}

		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'addProspect',
				title,
				url,
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				console.log(data);
				killAddProspectForm();
				if (isTwitch > -1) {
					var postID = data;
					var clipSlugPos = url.indexOf('.tv/') + 4;
					var clipSlugEnd = url.indexOf('?');
					var urlLength = url.length;
					if (clipSlugEnd > -1) {
						var urlSubstringLength = clipSlugEnd;
					} else {
						var urlSubstringLength = urlLength;
					}
					var clipSlug = url.substring(clipSlugPos, urlSubstringLength);
					var queryURL = 'https://api.twitch.tv/kraken/clips/' + clipSlug;
					jQuery.ajax({
						type: 'GET',
						url: queryURL,
						headers: {
							'Client-ID' : privateData.twitchClientID,
							'Accept' : 'application/vnd.twitchtv.v5+json',
						},
						error: function(data) {
							console.log(data);
						},
						success: function(data) {
							var channelURL = data.broadcaster.channel_url;
							var channelPic = data.broadcaster.logo;
							jQuery.ajax({
								type: "POST",
								url: dailiesGlobalData.ajaxurl,
								dataType: 'json',
								data: {
									action: 'addSourceToPost',
									channelURL,
									channelPic,
									postID,
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
					});
				};
			}
		});
	}

	render() {
		return(
			<section id="AddProspectForm">
				<img id="lightboxCloseButton" src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/04/red-x.png'} />
				<header id="AddProspectInstructions">Give us a title and a URL and you can add a post as a prospect. Currently only Twitch clips, tweets, Youtube videos, and Gfycats are supported.</header>
				<input id="AddProspectTitleBox" className="AddProspectFormBoxes" type="text" name="AddProspectTitleInput" placeholder="Title" maxLength="80" onKeyDown={this.inputListener} />
				<input id="AddProspectURLBox" className="AddProspectFormBoxes" type="text" name="AddProspectURLInput" placeholder="URL" maxLength="140" onKeyDown={this.inputListener} />
				<div id="AddProspectActionButtons">
					<button id="AddProspectCancelButton" className="AddProspectButton">Cancel</button>
					<button id="AddProspectPostButton" className="AddProspectButton" onClick={this.postClip}>Post</button>
				</div>
			</section>
		)
	}
};


jQuery("#menu-links").on('click', '.postButton', function() {
	var addProspectForm = jQuery('#AddProspectForm');
	var lightboxOverlay = jQuery('#lightboxOverlay');
	if ( addProspectForm.length ) {
		killAddProspectForm();
	} else {
		var AddProspectBox = document.createElement("section");
		AddProspectBox.id = 'AddProspectForm';
		document.body.appendChild(AddProspectBox);
		var lightboxOverlayElement = document.createElement("div");
		lightboxOverlayElement.id = 'lightboxOverlay';
		document.body.appendChild(lightboxOverlayElement);
		ReactDOM.render(
			<AddProspectForm />,
			document.getElementById('AddProspectForm')
		);
	}
});

jQuery(document).mouseup(function (e) {
	var addProspectForm = jQuery('#AddProspectForm');
	var lightboxOverlay = jQuery('#lightboxOverlay');
	if (e.target.id === 'lightboxOverlay') {
		killAddProspectForm();
	}
});

jQuery(document).on('click', '#lightboxCloseButton', function() {
	killAddProspectForm();
});

jQuery(document).on('click', '#AddProspectCancelButton', function() {
	killAddProspectForm();
});

jQuery(document).keydown(function(e) {
	var addProspectForm = jQuery('#AddProspectForm');
	if (addProspectForm.length && e.which === 27) {
		killAddProspectForm();
	}
});

function killAddProspectForm() {
	var addProspectForm = jQuery('#AddProspectForm');
	var lightboxOverlay = jQuery('#lightboxOverlay');
	addProspectForm.remove();
	lightboxOverlay.remove();
}

