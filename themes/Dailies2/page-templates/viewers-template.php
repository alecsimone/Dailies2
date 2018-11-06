<?php /* Template Name: viewers */ 

$specialPeople = [];
$specialPeopleObjects = getSpecialPeople();
foreach ($specialPeopleObjects as $specialPersonObject) {
	$specialPeople[] = $specialPersonObject['twitchName'];
}

?>
<title>Viewers</title>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

<div id="viewerApp" data-ajaxurl="<?php echo admin_url( 'admin-ajax.php' ); ?>" data-special-people='<?php echo json_encode($specialPeople); ?>'>
	<div id="viewerCount">
		<h2 id="viewerCountText">Viewers: <span id="viewerCounter"></span></h2>
	</div>
	<div id="mods">
		<h2 id="modHeader" class="categoryHeader">Mods</h2>
		<div id="modContainer"></div>
	</div>
	<div id="viewers">
		<h2 id="viewerHeader" class="categoryHeader">Viewers</h2>
		<div id="viewerContainer"></div>
	</div>
	<div id="bots" data-botlist='<?php echo json_encode(getBotlist()); ?>'>
		<h2 id="botHeader" class="categoryHeader">Bots</h2>
		<div id="botContainer"></div>
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

	h2.categoryHeader {
		color: hsla(210, 75%, 60%, .9);
	}

	h3 {
		margin: 40px 0 0 0;
	}

	.chatter {
		font-size: 18px;
		line-height: 1.3em;
		margin-left: 8px;
		display: flex;
		height: 24px;
		align-items: center;
	}
	.special {
		color: hsla(42, 79%, 64%, 1);
	}
	.viewerButton {
		width: 24px;
		margin-left: 4px;
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

				var botlist = JSON.parse(jQuery('#bots').attr("data-botlist"));
				var bots = [];

				var specialPeopleRaw = JSON.parse(jQuery('#viewerApp').attr("data-special-people"));
				var specialPeople = specialPeopleRaw.map(function(name) {
					return name.toLowerCase();
				})

				var mods = viewerData.chatters.moderators;
				jQuery('#modContainer').empty();
				jQuery.each(mods, function(index, mod) {
					if (botlist.includes(mod)) {
						bots.push(mod);
						return true;
					}
					if (specialPeople.includes(mod.toLowerCase())) {
						var specialness = ' special'
						var starSrc = 'https://dailies.gg/wp-content/uploads/2018/09/fullStarIcon.png'
					} else {
						var specialness = '';
						var starSrc = 'https://dailies.gg/wp-content/uploads/2018/09/emptyStarIcon.png'
					}
					jQuery('#modContainer').append(`<div id=${mod} class='moderator chatter${specialness}'>${mod}<img class="specialButton viewerButton" src="${starSrc}"><img class="botButton viewerButton" src="https://dailies.gg/wp-content/uploads/2018/09/botIcon.png"></div>`);
					if (window.viewerData && window.viewerData.chatters.moderators.indexOf(mod) === -1) {
						jQuery(`#${mod}`).css("color", "hsla(0, 0%, 100%, 1);");
					}
				});

				var viewers = viewerData.chatters.viewers;
				jQuery('#viewerContainer').empty();
				jQuery.each(viewers, function(index, viewer) {
					if (botlist.includes(viewer)) {
						bots.push(viewer);
						return true;
					}
					if (specialPeople.includes(viewer.toLowerCase())) {
						var specialness = ' special'
						var starSrc = 'https://dailies.gg/wp-content/uploads/2018/09/fullStarIcon.png'
					} else {
						var specialness = '';
						var starSrc = 'https://dailies.gg/wp-content/uploads/2018/09/emptyStarIcon.png'
					}
					jQuery('#viewerContainer').append(`<div id=${viewer} class='viewer chatter${specialness}'>${viewer}<img class="specialButton viewerButton" src="${starSrc}"><img class="botButton viewerButton" src="https://dailies.gg/wp-content/uploads/2018/09/botIcon.png"></div>`);
					if (window.viewerData && window.viewerData.chatters.viewers.indexOf(viewer) === -1) {
						jQuery(`#${viewer}`).css("color", "hsla(0, 0%, 100%, 1);");
					}
				});

				jQuery('#botContainer').empty();
				jQuery.each(bots, function(index, bot) {
					if (specialPeople.includes(bot.toLowerCase())) {
						var specialness = ' special'
						var starSrc = 'https://dailies.gg/wp-content/uploads/2018/09/fullStarIcon.png'
					} else {
						var specialness = '';
						var starSrc = 'https://dailies.gg/wp-content/uploads/2018/09/emptyStarIcon.png'
					}
					jQuery('#botContainer').append(`<div id=${bot} class='viewer chatter${specialness}'>${bot}<img class="specialButton viewerButton" src="${starSrc}"><img class="botButton viewerButton" src="https://dailies.gg/wp-content/uploads/2018/09/botIcon.png"></div>`);
				});

				let viewerCount = Number(viewerData.chatter_count) - bots.length;
				document.title = `Viewers: ${viewerCount} [${window.channel}]`;
				jQuery('#viewerCounter').text(viewerCount);

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
	});
	let ajaxurl = jQuery('#viewerApp').attr("data-ajaxurl");
	$("#viewerApp").on("click", '.botButton', function(e) {
		let twitchName = $(e.target).parent().text();
		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: 'json',
			data: {
				twitchName,
				action: 'markTwitchBot',
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
	});
	$("#viewerApp").on("click", '.specialButton', function(e) {
		let twitchName = $(e.target).parent().text();
		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: 'json',
			data: {
				twitchName,
				action: 'specialButtonHandler',
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
	});
</script>