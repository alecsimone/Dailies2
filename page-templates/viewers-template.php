<?php /* Template Name: viewers */ ?>
<title>Viewers</title>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

<div id="viewerApp">
	<div id="viewerCount">
		<h2 id="viewerCountText">Viewers: <span id="viewerCounter"></span></h2>
	</div>
	<div id="mods">
		<h2 id="modHeader">Mods</h2>
	</div>
	<div id="viewers">
		<h2 id="viewerHeader">Viewers</h2>
	</div>
	<input id="channelInput" type="text" placeholder="Rocket_Dailies"></input>
</div>

<style>
	body {
		background: #262626;
	}

	#viewerApp {
		font-family: sans-serif;
		width: 250px;
		margin: auto;
		color: hsla(0, 0%, 100%, .75);
	}

	#viewerCount {
		color: hsla(30, 75%, 60%, .9);
	}

	#modHeader, #viewerHeader {
		color: hsla(210, 75%, 60%, .9);
	}

	h3 {
		margin: 40px 0 0 0;
	}

	.chatter {
		font-size: 18px;
		line-height: 1.3em;
		margin-left: 8px;
	}

	#channelInput {
		width: 100%;
		margin-top: 60px;
		padding: 6px;
		font-size: 24px;
		border-radius: 3px;
		border: 1px solid black;
	}

</style>

<script>
	window.channel = 'rocket_dailies';
	window.viewerData = false;

	function updateViewcount() {
		console.log("updating...");
		var url = `https://tmi.twitch.tv/group/user/${window.channel}/chatters`
		console.log(url);
		jQuery.ajax({
			url: url,
			dataType: 'jsonp',
			success: function(data) {
				var viewerData = data.data;
				console.log(viewerData);

				document.title = `Viewers: ${viewerData.chatter_count} [${window.channel}]`;
				jQuery('#viewerCounter').text(viewerData.chatter_count);

				var mods = viewerData.chatters.moderators;
				jQuery('#mods').html(`<h3 id="modHeader">Mods: ${mods.length}</h3>`);
				jQuery.each(mods, function(index, mod) {
					jQuery('#mods').append(`<div id=${mod} class='moderator chatter'>${mod}</div>`);
					if (window.viewerData && window.viewerData.chatters.moderators.indexOf(mod) === -1) {
						jQuery(`#${mod}`).css("color", "hsla(0, 0%, 100%, 1);");
					}
				});

				var viewers = viewerData.chatters.viewers;
				jQuery('#viewers').html(`<h3 id="viewerHeader">Viewers: ${viewers.length}</h3>`);
				jQuery.each(viewers, function(index, viewer) {
					jQuery('#viewers').append(`<div id=${viewer} class='viewer chatter'>${viewer}</div>`);
					if (window.viewerData && window.viewerData.chatters.viewers.indexOf(viewer) === -1) {
						jQuery(`#${viewer}`).css("color", "hsla(0, 0%, 100%, 1);");
					}
				});

				window.viewerData = viewerData;
			}
		});
	}
	updateViewcount();
	window.setInterval(updateViewcount, 60000);

	$("#channelInput").keydown(function(e) {
		if (e.which === 13) {
			var newChannel = $('#channelInput').val().toLowerCase();
			window.channel = newChannel;
			updateViewcount();
			$('#channelInput').val('');
		}
	})
</script>