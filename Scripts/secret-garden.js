import SecretGarden from '../Components/SecretGarden.jsx';

window.vodLinkTimeParser = function(vodLink) {
	var timestampIndex = vodLink.lastIndexOf('t=');
	var timestamp = vodLink.substring(timestampIndex + 2);
	var hourMark = timestamp.lastIndexOf('h');
	if (hourMark > -1) {
		var hourCount = timestamp.substring(0, hourMark);
	} else {
		var hourCount = 0;
	}
	var minuteMark = timestamp.lastIndexOf('m');
	if (minuteMark > -1) {
		var minuteCount = timestamp.substring(hourMark + 1, minuteMark);
	} else {
		var minuteCount = 0;
	}
	var secondMark = timestamp.lastIndexOf('s');
	if (secondMark > -1) {
		var secondCount = timestamp.substring(minuteMark + 1, secondMark);
	} else {
		var secondCount = 0;
	}
	var VODTime = 3600 * hourCount + 60 * minuteCount + 1 * secondCount;
	return VODTime;
}

jQuery("body").on('mouseenter', '.streamListItem', function() {
	if (jQuery(this).hasClass('deactivated')) {
		jQuery(this).css('opacity', 1);
	} else {
		jQuery(this).css('opacity', .1);
	}
});
jQuery("body").on('mouseout', '.streamListItem', function() {
	if (jQuery(this).hasClass('deactivated')) {
		jQuery(this).css('opacity', '');
	} else {
		jQuery(this).css('opacity', '');
	}
});