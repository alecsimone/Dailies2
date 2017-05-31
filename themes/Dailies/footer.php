<?php global $thisDomain; ?>
<script>
/*** imgResizer Function to pull in bigger featured images on bigger screens ***/
function imgResizer() {
	var windowWidth = jQuery(window).width();
	if ( windowWidth > 750) {
		jQuery(".thumb").each(function() {
			var jQuerylargeURL = jQuery(this).attr("data-large-version");
			jQuery(this).attr("src", jQuerylargeURL);
		});
	} else if (windowWidth > 450) {
		jQuery(".thumb").each(function() {
			var jQuerymediumURL = jQuery(this).attr("data-medium-version");
			jQuery(this).attr("src", jQuerymediumURL);
		});
	} else {
		console.log("we're fine with these images");
	}
};
jQuery(document).ready( imgResizer );

/*** Click outside detector ***/
jQuery(document).mouseup(function (e)
{
    var searchBox = jQuery("#searchbox");

    if ( 
	!searchBox.is(e.target) // if the target of the click isn't the container...
        && searchBox.has(e.target).length === 0 // ... nor a descendant of the container
	&& searchBox.width() > 0
	)
    {
        searchBox.css("max-width", "0px");
    }
});

/*** Vote Icon Replacer
jQuery('.contentContainer').on('hover', '.voteIcon', function() {
	console.log("you hovered!");
	thisVoteIcon = jQuery(this);
	thisIconSrc = thisVoteIcon.attr("src")
	MedalSrc = '<?php echo $thisDomain; ?>/wp-content/uploads/2016/12/Medal-small-100.png';
	VoteIconSrc = '<?php echo $thisDomain; ?>/wp-content/uploads/2017/04/Vote-Icon-line-100.png';
	if ( thisIconSrc.includes(MedalSrc) ) {
		thisVoteIcon.attr("src", VoteIconSrc);
	} else if ( thisIconSrc.includes(VoteIconSrc) ) {
		thisVoteIcon.attr("src", MedalSrc);
	};
}); ***/

/*** heightLocker Function to keep gfys from collapsing and re-expanding when the image is swapped for the video ***/
function heightLocker() {
	var gfyitemIMGs = jQuery('.gfyitem');
	gfyitemIMGs.each(function() {
		var thisHeight = jQuery(this).outerHeight();
		var thisParent = jQuery(this).parent();
		thisParent.height(thisHeight);
	});
}
jQuery(window).load( heightLocker );

/*** Resize Locker. Re-arranges everything on resize so stuff doesn't get all on top of other stuff and whatnot ***/
function resizeLocker() {
	imgResizer();
	heightLocker();
}
jQuery(window).resize( resizeLocker );
//window.addEventListener("orientationchange", resizeLocker);

/*** Youtube Replacer ***/
function youtubeReplacer(ID, UID) {
	var featIMG = document.getElementById(UID);
	var container = jQuery(featIMG).parent();
	var playButton = container.find('.playbutton');
	var replacementCodeStart = '<div class="embed-container"><iframe width="1280" height="720" src="https://www.youtube.com/embed/';
	var replacementCodeEnd = '?showinfo=0&autoplay=1" frameborder="0" allowfullscreen></iframe></div>';
	var replacementCode = replacementCodeStart + ID + replacementCodeEnd;
	jQuery(featIMG).replaceWith(replacementCode);
	jQuery(playButton).remove();
};

/*** Twitch Replacer ***/
function generateTwitchReplacementCode(ID) {
	var replacementCodeStart = "<div class=\"embed-container\"><iframe src=\"https://clips.twitch.tv/embed?clip=";
	var replacementCodeEnd = "&autoplay=true\" width=\"640\" height=\"360\" frameborder=\"0\" scrolling=\"no\" allowfullscreen=\"true\"></iframe></div>";
	var replacementCode = replacementCodeStart + ID + replacementCodeEnd;
	return replacementCode;
}
function generateGfyReplacementCode(ID) {
	var replacementCodeStart = "<div class='embed-container'><iframe src='https://gfycat.com/ifr/"
	var replacementCodeEnd = "' frameborder='0' scrolling='no' width='100%' height='100%' style='position:absolute;top:0;left:0;' allowfullscreen></iframe></div>"
	var replacementCode = replacementCodeStart + ID + replacementCodeEnd;
	return replacementCode;
}
function generateYoutubeReplacementCode(ID) {
	var replacementCodeStart = "<div class='embed-container'><iframe width='1280' height='720' src='https://www.youtube.com/embed/"
	var replacementCodeEnd = "?showinfo=0&autoplay=1' frameborder='0' allowfullscreen></iframe></div>"
	var replacementCode = replacementCodeStart + ID + replacementCodeEnd;
	return replacementCode;
}

function twitchReplacer(ID, UID) {
	var featIMG = document.getElementById(UID);
	var container = jQuery(featIMG).parent();
	var playButton = container.find('.playbutton');
	var replacementCode = generateTwitchReplacementCode(ID);
	jQuery(featIMG).replaceWith(replacementCode);
	jQuery(playButton).remove();
};

function doesTheReplacing(thisLink) {
	var thisThing = thisLink.parent().parent().parent();
	var thisContent = thisThing.find('.contentbox');
	var thisEmbedContainer = thisContent.find('.embed-container');
	if (!thisEmbedContainer.length) {
		var thisClipURL = thisLink.attr("href");
		var thisClipKeyCodeStart = thisClipURL.indexOf('.tv') + 4;
		var thisClipKeyCode = thisClipURL.substring(thisClipKeyCodeStart);
		var replacementCodeStart = "<div class=\"embed-container\"><iframe src=\"https://clips.twitch.tv/embed?clip=";
		var replacementCodeEnd = "&autoplay=true\" width=\"640\" height=\"360\" frameborder=\"0\" scrolling=\"no\" allowfullscreen=\"true\"></iframe></div>";
		var replacementCode = replacementCodeStart + thisClipKeyCode + replacementCodeEnd;
		thisContent.html(replacementCode);

		var thisThingID = thisThing.attr("id");
		var thisPostID = thisThingID.substring(5);
		tickUpViews(thisPostID, 'fullClip');
	};
	var thisEmbed = jQuery('.embed-container');
	var embedHeight = thisEmbed.outerHeight();
	var thisContentBox = thisEmbed.parent();
	thisContentBox.height(embedHeight);
}

 jQuery('.contentContainer').on('click', '.fullClipLink', function(event) {
	event.preventDefault();
});

jQuery('.contentContainer').on('click', 'p.attribution.full-clip', function() {
	var thisLink = jQuery(this).children('.fullClipLink');
	doesTheReplacing(thisLink);
});

function littleReplacer(ID, UID) {
	console.log(UID);
}



/*** Show Comment Form **/
function showCommentForm(ID, UID) {
	var thisFormID = '#comment-form-container-' + UID;
	var thisForm = jQuery(thisFormID);
	var thisFormMaxHeight = thisForm.css("maxHeight");
	if (thisFormMaxHeight == '0px') {
		thisForm.css("maxHeight", "200px");
	} else {
		thisForm.css("maxHeight", "0px");
	}
	if ( thisForm.parent().parent().hasClass("little-thing") ) {
		jQuery(".comment-form-container").one("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd", function(){grid.isotope();});
		console.log("isotope");
	}
}

/*** Infinite scroller ***/
jQuery(function() {

	var earlier = jQuery("a.earlier"); // What is a.earlier now?
	var nomore = jQuery("a.nomore"); // Is there an a.nomore?
	var earlierHREF = earlier.attr("href"); // where is a.earlier pointing?
	var earlierOffset = earlier.offset(); // How far down is a.earlier?

	if (!earlier.length) {
		return;
	}

	jQuery(window).load(function() {	
		var viewportHeight = jQuery(window).height(); // Get the height of the screen so we can know when the bottom passes our threshold
		jQuery(window).scroll(function() { //Every scroll we're checking:
			var ourScroll = jQuery(document).scrollTop(); // how far down are we?
			if ( nomore.length ) { // If there's an a.nomore:
				nomore.fadeIn() .make // sure it's showing
				jQuery('a.loading').hide(); // hide the loading image
				console.log("the no more thing fired"); // warn everyone
			};
			if ( ourScroll + (2 * viewportHeight) >= earlierOffset.top && earlier.hasClass("more") ) { // our threshold for loading more, which we only trigger if a.earlier is telling us there are more posts
				earlier.replaceWith("<a class='earlier loading'><img src='<?php echo $thisDomain; ?>/wp-content/uploads/2016/09/More.png' class='loadingIMG'></a>"); // Switch a.earlier with a loading icon
				earlier = jQuery("a.earlier");
				console.log("pulling more!");
				jQuery.get(earlierHREF,function(data) { //get the page a.earlier is pointing to and do:
	    			var posts = jQuery(data).find('.pull'); //find everything on that page tagged 'pull'
					jQuery(posts).hide() // hide them
	    				.appendTo('.contentContainer') //And stick them in .contentContainer
	    			var pulled = jQuery('.pull img.thumb');
	    			var lastPullIndex = pulled.length - 2; //the very last thing is the link to the next page, we want the one before that
	    			var lastPull = pulled[lastPullIndex];
					jQuery('.pull img').one('load', reInit); //Run the reInit function each time an image loads
				}); // Close the get function		
	
			}; // Close the threshold crossed function
		}); //Close the scroll check
	});

	/*** reInit function to run once we've loaded the next page ***/
	function reInit() {
		earlier = jQuery("a.earlier"); // What is a.earlier now?
		nomore = jQuery("a.nomore"); // Is there an a.nomore?
		earlierHREF = earlier.attr("href"); // where is a.earlier pointing?
		earlierOffset = earlier.offset(); // How far down is a.earlier?
		imgResizer()
		heightLocker();
		console.log('re-initializing!');
	    var moreGFYs = document.getElementsByClassName('gfyitem');
	    for (var i=0; i<moreGFYs.length; i++) {
	    	moreGFYs[i].addEventListener("click",gfyCollection.init,false);
	    };
		jQuery('.pull').fadeIn();
		jQuery('a.loading').remove();
	}
});

/*** Scroll to Top ***/
jQuery(window).scroll(function() {
    if (jQuery(this).scrollTop() >= 100) {        // If page is scrolled more than 50px
        jQuery('#return-to-top').fadeIn(500);    // Fade in the arrow
    } else {
        jQuery('#return-to-top').fadeOut(500);   // Else fade out the arrow
    }
});
jQuery('#return-to-top').click(function() {      // When arrow is clicked
    jQuery('body,html').animate({
        scrollTop : 0                       // Scroll to top of body
    }, 500);
});

/** Add Score Box **/
jQuery(".thing").on('keypress', '.addScore-input', function(e) {
	if(e.which === 13) {
		var thisBox = jQuery(this);
		thisPostID = thisBox.attr("data-postid");
		thisAddition = parseFloat( thisBox.val() );
		if ( !isNaN(thisAddition) ) {
			addScore(thisPostID, thisAddition, thisBox);
		} else {
			console.log("You didn't put in a number. What are you tryna do to me?");
		}
	}
});

/** Hover Replacer **/
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


/** Tagbar Scroller **/
jQuery('#morebar').click(function() {
	var taglist = jQuery('#taglist');
	var listwidth = taglist.width();
	var wholeWidth = jQuery('#taglist')[0].scrollWidth;
	taglist.animate( {scrollLeft: '+='+listwidth}, 400, 'swing' );
	var listScroll = taglist.scrollLeft();
});
jQuery('#lessbar').click(function() {
	var taglist = jQuery('#taglist');
	var listwidth = taglist.width();
	taglist.animate( {scrollLeft: '-='+listwidth}, 400, 'swing' );
	var listScroll = taglist.scrollLeft();
});
jQuery('#taglist').scroll(function() {
	var taglist = jQuery('#taglist');
	var barItems = jQuery('.barItem');
	var lessbar = jQuery('#lessbar');
	var morebar = jQuery('#morebar');
	var barWidth = 0;
	barItems.each(function(index) {
		barWidth += parseInt(jQuery(this).outerWidth(true), 10);
	});
	var listWidth = taglist.width();
	var listLeft = taglist.scrollLeft();
	var listRight = listLeft + listWidth;
	if (listLeft == 0) {
		lessbar.css("color", "transparent");
	} else if (listRight == barWidth) {
		morebar.css("color", "transparent");
	} else {
		lessbar.css("color", "rgba(255,253,249,.5)");
		morebar.css("color", "rgba(255,253,249,.5)");
	}
});

/*** Tagbar Scrolling with jQuery Kinetic ***/
jQuery(document).ready(function() {
	jQuery('#taglist').kinetic();
	var pearls = jQuery('.pearls');
	if ( pearls.length ) {
		console.log("There be pearls here");
		pearls.each( function() {
			jQuery(this).kinetic();
		});
	};
});


/*** Sticky Sidebar ***/
jQuery(window).load(function() {
	var sidebar = jQuery('#sidebar');
	var repFooter = jQuery('#repFooter');
	var sidebarFixed = false;
	if(sidebar.length) { // if a sidebar exists
		var sidebarOffset = sidebar.offset().top; // Find out how far down it starts
		var sidebarLeft = sidebar.offset().left; // Figure out how far from the left side it is
		var sidebarWidth = sidebar.css("width");
		var sidebarHeight = sidebar.outerHeight();	// How tall it is
		var sidebarExists = true; // and create this variable to let everyone know there's a sidebar
	} else {
		var sidebarOffset = 0;
		var sidebarExists = false;
	};
	jQuery(window).resize(function() {
		if(sidebar.length) { // if a sidebar exists
			var windowWidth = jQuery( window ).width();
			sidebarOffset = sidebar.offset().top; // Find out how far down it starts
			var wrapper = jQuery('.wrapper');
			var wrapperLeft = wrapper.offset().left;
			var wrapperWidth = wrapper.width();
			var wrapperRight = wrapperLeft + wrapperWidth; 
			sidebarWidth = (windowWidth - 24) * .25 - 6;
			if (sidebarWidth > 421) {
				sidebarWidth = 421;
			}
			sidebarLeft = wrapperLeft + .75 * wrapperWidth +6;
			sidebarHeight = sidebar.outerHeight();	// How tall it is
			sidebarExists = true; // and create this variable to let everyone know there's a sidebar
			if(sidebarFixed) {
				sidebar.css("width", sidebarWidth );
				sidebar.css("left", sidebarLeft);
			};
		} else {
			var sidebarOffset = 0;
			var sidebarExists = false;
		};
	});
	jQuery(window).scroll(function() {
		var sidebar = jQuery('#sidebar');
		var sidebarBottom = sidebarOffset + sidebarHeight;
		var currentPosition = jQuery(window).scrollTop();
		var windowHeight = jQuery(window).height();
		var windowWidth = jQuery( window ).width();
		var windowBottom = currentPosition + windowHeight;
		if(sidebar.length && sidebarExists == false) { //If there's a sidebar now, but there wasn't when the page loaded, we need to establish the sidebar position variables
			sidebarOffset = sidebar.offset().top;
			sidebarHeight = sidebar.outerHeight();
			sidebarLeft = sidebar.offset().left;
			sidebarWidth = sidebar.css("width");
			sidebarExists = true;
		};
		if(sidebarBottom < windowBottom - 20) {
			sidebar.css("position", "fixed");
			sidebar.css("bottom","1em");
			sidebar.css("left", sidebarLeft);
			sidebar.css("width", sidebarWidth);
			sidebarFixed = true;
		} else {
			sidebar.css("position","absolute");
			sidebar.css("bottom","initial");
			sidebar.css("left", "initial");
			sidebar.css("right", "0px");
			sidebar.css("width", "calc(25% - 6px)");
			sidebarFixed = false;
		};
	});
});

</script>
