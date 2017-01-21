/*** toggleSearch function to do... I don't know, maybe let's have it toggle the search box? Or is that too on the nose? ***/
function toggleSearch() {
	var searchBox = jQuery('#searchbox');
	var searchWidth = searchBox.width();
	if (searchWidth > 0) {
		searchBox.css("max-width", "0px");
	} else {
		searchBox.css("max-width", "400px");
	}
};

/*** Click outside detector
jQuery(document).mouseup(function (e)
{
	console.log('you clicking sunuvabitch');
    var searchBox = jQuery("#searchbox");

    if ( 
	!searchBox.is(e.target) // if the target of the click isn't the container...
        && searchBox.has(e.target).length === 0 // ... nor a descendant of the container
	&& searchBox.width() > 0
	)
    {
        searchBox.css("max-width", "0px");
    }
}); ***/

/*** navBurger: Shows and hides the extra buttons in the nav menu for mobile users ***/
function navBurger() {
	var hideyDivs = jQuery('.mobileHide');
	var hideyWidth = hideyDivs.width();
	if (hideyWidth > 0 ) {
		hideyDivs.css("max-width", "0px");
	} else {
		hideyDivs.css("max-width", "400px");
	};
}