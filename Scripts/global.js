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
	}
});

function replaceImage(thisIMG) {
	var thisOldSrc = thisIMG.attr("src");
	var thisNewSrc = thisIMG.attr("data-replace-src");
	thisIMG.attr("src", thisNewSrc);
	thisIMG.attr("data-replace-src", thisOldSrc);
}

window.imageError = function imageError(e, type) {
	if (type === 'source') {
		e.target.src=dailiesGlobalData.thisDomain + "/wp-content/uploads/2017/07/rl-logo-med.png";
	} else {
		e.target.src=dailiesGlobalData.thisDomain + "/wp-content/uploads/2017/03/default_pic.jpg";
	}
};