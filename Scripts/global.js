import AddProspectForm from '../Components/AddProspectForm.jsx';
import {privateData} from '../Scripts/privateData.jsx';

jQuery(document).mouseup(function (e) {
	var searchBox = jQuery('#searchbox');
	if ( jQuery('#searchToggle').is(e.target) ) {
		toggleSearch();
	} else if ( !searchBox.is(e.target) && searchBox.has(e.target).length === 0 ) {
		searchBox.css("maxWidth", "0");
	}
});

function toggleSearch() {
	var searchBox = jQuery("#searchbox");
	if (searchBox.width() > 0) {
		searchBox.css("maxWidth", "0");
	} else {
		searchBox.css("maxWidth", "300px");
	}
}

jQuery("body").on('mouseenter', '.hoverReplacer', function() {
	replaceImage(jQuery(this));
});
jQuery("body").on('mouseleave', '.hoverReplacer', function() {
	if (!jQuery(this).hasClass('replaceHold')) {
		replaceImage(jQuery(this));
	} else {
		jQuery(this).removeClass('replaceHold');
		jQuery(this).css('opacity', '1');
	}
});

function replaceImage(thisIMG) {
	var thisOldSrc = thisIMG.attr("src");
	var thisNewSrc = thisIMG.attr("data-replace-src");
	thisIMG.attr("src", thisNewSrc);
	thisIMG.attr("data-replace-src", thisOldSrc);
	var thisOpacity = thisIMG.css('opacity');
	if (thisIMG.css('opacity') === '0.5') {
		thisIMG.css('opacity', '1');
	} else {
		thisIMG.css('opacity', '.5');
	}
}

window.imageError = function imageError(e, type) {
	if (type === 'source') {
		e.target.src=dailiesGlobalData.thisDomain + "/wp-content/uploads/2017/07/rl-logo-med.png";
	} else if (type === 'twitchVoter') {
		e.persist();
		var voterFull = e.target.title;
		if (voterFull.indexOf(':')) {
			var voter = voterFull.substring(0, voterFull.indexOf(':'));
		} else {
			var voter = voterFull;
		}
		e.target.src=dailiesGlobalData.thisDomain + "/wp-content/uploads/2017/03/default_pic.jpg";
		var query = 'https://api.twitch.tv/kraken/users?login=' + voter;
		jQuery.ajax({
			type: 'GET',
			url: query,
			headers: {
				'Client-ID' : privateData.twitchClientID,
				'Accept' : 'application/vnd.twitchtv.v5+json',
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				var picSrc = data.users[0]['logo'];
				e.target.src = picSrc;
				jQuery.ajax({
					type: "POST",
					url: dailiesGlobalData.ajaxurl,
					dataType: 'json',
					data: {
						twitchName: voter,
						twitchPic: picSrc,
						action: 'update_twitch_db',
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
	} else {
		e.target.src=dailiesGlobalData.thisDomain + "/wp-content/uploads/2017/03/default_pic.jpg";
	}
};

window.htmlEntityFix = function(textToFix) {
	let txt = document.createElement("textarea");
	txt.innerHTML = textToFix;
	return txt.value;
};
window.ctrlIsPressed = false;
jQuery(document).keydown(function(e) {
	if (e.which=="17") {
		window.ctrlIsPressed = true;
	}
});
jQuery(document).keyup(function(e) {
	if (e.which=="17") {
		window.ctrlIsPressed = false;
	}
});