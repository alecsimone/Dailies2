import React from "react";
import ReactDOM from 'react-dom';
import {turnGfycatURLIntoGfycode, turnYoutubeURLIntoYoutubeCode, turnTwitterURLIntoTweetID, turnTwitchURLIntoTwitchCode} from '../Scripts/global.js';
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
		
		if (dailiesGlobalData.userData.userID === 0) {
			redFlash(titleBox);
			redFlash(urlBox);
			jQuery('#AddProspectInstructions').text("You must be logged in to submit. Go to the homepage and log in with the gold-topped box.");
			return;
		}

		var postType = this.props.submitType;
		if (postType === 'submitimg') {
			postType = 'postButton';
		} else if (postType === 'submitPage') {
			postType = 'submitButton';
			var submitPage = true;
		}


		function redFlash(target) {
			target.addClass('redFlash');
			setTimeout(function() {target.removeClass('redFlash')
			}, 1000);
		}

		function greenFlash(target) {
			target.addClass('greenFlash');
			setTimeout(function() {target.removeClass('greenFlash')
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

		var isMultipleURLS = -1;
		var isTwitch = url.indexOf('twitch.tv/');
		if (isTwitch > -1) {
			isMultipleURLS = url.indexOf('twitch.tv/', isTwitch);
		}
		var isYouTube = url.indexOf('youtube.com/');
		if (isYouTube > -1) {
			isMultipleURLS = url.indexOf('youtube.com/', isYouTube);
		}
		var isYtbe = url.indexOf('youtu.be/');
		if (isYtbe > -1) {
			isMultipleURLS = url.indexOf('youtu.be/', isYtbe);
		}
		var isTwitter = url.indexOf('twitter.com/');
		if (isTwitter > -1) {
			isMultipleURLS = url.indexOf('twitter.com/', isTwitter);
		}
		var isGfy = url.indexOf('gfycat.com/');
		if (isGfy > -1) {
			isMultipleURLS = url.indexOf('gfycat.com/', isGfy);
		}

		// if (isMultipleURLS > -1) {
		// 	jQuery('#AddProspectInstructions').text("One submission at a time, please");
		// 	redFlash(urlBox);
		// 	return
		// }

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

		if (postType === 'submitButton') {
			var action = 'submitClip'
		} else if (postType === 'postButton') {
			var action = 'addProspect'
		}
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action,
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
				if (data === 'That clip has already been submitted') {
					redFlash(titleBox);
					redFlash(urlBox);
					jQuery('#AddProspectInstructions').text("That clip has already been submitted!");
					return;
				} else {
					greenFlash(titleBox);
					greenFlash(urlBox);
					resetAddProspectForm();
				}
			}
		});
	}

	render() {
		return(
			<section id="prospectForm">
				<img id="lightboxCloseButton" src={dailiesGlobalData.thisDomain + '/wp-content/uploads/2017/04/red-x.png'} />
				<header id="AddProspectInstructions">Currently only Twitch clips, tweets, Youtube videos, and Gfycats are supported.</header>
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


jQuery("#menu-links").on('click', '.submitButton', showAddProspectForm);
jQuery("#menu-links").on('click', '.postButton', showAddProspectForm);

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

function showAddProspectForm(e) {
	e.preventDefault();
	var addProspectForm = jQuery('#AddProspectForm');
	var lightboxOverlay = jQuery('#lightboxOverlay');
	if ( addProspectForm.length ) {
		killAddProspectForm();
	} else {
		var AddProspectBox = document.createElement("section");
		if (dailiesGlobalData.userData.userID === 0) {
			AddProspectBox.id = 'loggedOutProspectForm'
		} else {
			AddProspectBox.id = 'AddProspectForm';
		}
		AddProspectBox.className = "movedUp";
		document.body.appendChild(AddProspectBox);
		setTimeout(() =>AddProspectBox.classList.remove("movedUp"), 1);
		var lightboxOverlayElement = document.createElement("div");
		lightboxOverlayElement.id = 'lightboxOverlay';
		document.body.appendChild(lightboxOverlayElement);
		if (dailiesGlobalData.userData.userID === 0) {
			let loginWidget = `
				<div id="wp-social-login" class="">
					<style type="text/css">
					.wp-social-login-connect-with{}.wp-social-login-provider-list{}.wp-social-login-provider-list a{}.wp-social-login-provider-list img{}.wsl_connect_with_provider{}</style>
					<div class="wp-social-login-widget">
						<div class="wp-social-login-connect-with">Login now with:</div>
						<div class="wp-social-login-provider-list">
							<a rel="nofollow" href="https://dailies.gg/wp-login.php?action=wordpress_social_authenticate&amp;mode=login&amp;provider=Facebook&amp;redirect_to=https%3A%2F%2Fdailies.gg%2F" title="Connect with Facebook" class="wp-social-login-provider wp-social-login-provider-facebook" data-provider="Facebook">
								Facebook
							</a>
							<a rel="nofollow" href="https://dailies.gg/wp-login.php?action=wordpress_social_authenticate&amp;mode=login&amp;provider=Google&amp;redirect_to=https%3A%2F%2Fdailies.gg%2F" title="Connect with Google" class="wp-social-login-provider wp-social-login-provider-google" data-provider="Google">
								Google
							</a>
							<a rel="nofollow" href="https://dailies.gg/wp-login.php?action=wordpress_social_authenticate&amp;mode=login&amp;provider=Twitter&amp;redirect_to=https%3A%2F%2Fdailies.gg%2F" title="Connect with Twitter" class="wp-social-login-provider wp-social-login-provider-twitter" data-provider="Twitter">
								Twitter
							</a>
							<a rel="nofollow" href="https://dailies.gg/wp-login.php?action=wordpress_social_authenticate&amp;mode=login&amp;provider=Steam&amp;redirect_to=https%3A%2F%2Fdailies.gg%2F" title="Connect with Steam" class="wp-social-login-provider wp-social-login-provider-steam" data-provider="Steam">
								Steam
							</a>
							<a rel="nofollow" href="https://dailies.gg/wp-login.php?action=wordpress_social_authenticate&amp;mode=login&amp;provider=TwitchTV&amp;redirect_to=https%3A%2F%2Fdailies.gg%2F" title="Connect with Twitch.tv" class="wp-social-login-provider wp-social-login-provider-twitchtv" data-provider="TwitchTV">
								Twitch.tv
							</a>
						</div>
						<div class="wp-social-login-widget-clearing"></div>
					</div>
				</div>
			`
			let loggedOutProspectForm = document.getElementById('loggedOutProspectForm');
			loggedOutProspectForm.innerHTML = loginWidget;
		} else {		
			ReactDOM.render(
				<AddProspectForm submitType={e.target.className} />,
				document.getElementById('AddProspectForm')
			);
		}
	}
}

function killAddProspectForm() {
	var addProspectForm = jQuery('#AddProspectForm');
	var loggedOutProspectForm = jQuery('#loggedOutProspectForm');
	var lightboxOverlay = jQuery('#lightboxOverlay');
	addProspectForm.addClass("scaleOut");
	loggedOutProspectForm.addClass("scaleOut");
	setTimeout(() => addProspectForm.remove(), 250);
	setTimeout(() => loggedOutProspectForm.remove(), 250);
	setTimeout(() => lightboxOverlay.remove(), 250);
}

function resetAddProspectForm() {
	var titleBox = jQuery('#AddProspectTitleBox');
	titleBox.val('');
	var urlBox = jQuery('#AddProspectURLBox');
	urlBox.val('');
	jQuery('#AddProspectInstructions').text("Your clip was successfully submitted! Want to submit another? (Limit is two per person per show)");
}

window.onload = function() {
	var submitPageTesterElement = document.getElementById("AddProspectForm");
	if (submitPageTesterElement) {
		ReactDOM.render(
			<AddProspectForm submitType='submitPage' />,
			document.getElementById('AddProspectForm')
		);
	}
}
