import AddProspectForm from '../Components/AddProspectForm.jsx';

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