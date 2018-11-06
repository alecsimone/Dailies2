<?php /* Template Name: chipstack */ 
$livePostObject = get_page_by_path('live');
$liveID = $livePostObject->ID;
$currentVotersList = get_post_meta($liveID, 'currentVoters', true);
$yeaVotersCount = count($currentVotersList['yea']);
$twitchUserDB = getTwitchUserDB(); 
$ajaxURL = admin_url( 'admin-ajax.php' ); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

<div id="chipstack">
	<?php for ($i=0; $i < $yeaVotersCount; $i++) { ?>
		<?php $voter = $currentVotersList['yea'][$i]; 
		$voterRep = $twitchUserDB[$voter]['rep'];
		if ($voterRep > 0) {
			$repClass = "hasRep";
		} else {
			$repClass = "noRep";
		}; ?>
		<div class="chip <?php echo $repClass; ?>"></div>
	<?php }; ?>
</div>

<style>
	.chip {
		height: 9px;
		width: 40px;
		/*background: #2f830b;*/
		/*background: #29720a;*/
		background: #121212;
		border-bottom: 1px solid gold;
		border-radius: 4px;
		animation: chipdrop .3s cubic-bezier(.25,0,1,.75);
	}
	.chip.noRep {
		background: #2f830b;
		border-bottom: 1px solid white;
	}
	@keyframes chipdrop {
		0% {
			transform: translateY(50px);
		}
		100% {
			transform: translateY(0);
		}
	}
</style>

<script>
	window.setInterval(updateVoteCount, 1000);
	function updateVoteCount() {
		jQuery.ajax({
		type: "POST",
		url: '<?php echo $ajaxURL; ?>',
		dataType: 'json',
		data: {
			action: 'get_chat_votes',
		},
		error: function(one, two, three) {
			console.log(one);
			console.log(two);
			console.log(three);
		},
		success: function(data) {
			var votecount = data.yea.length;
			var chips = $('.chip');
			var chipcount = chips.length;
			if (chipcount === votecount) {
				return;
			} else if (chipcount < votecount) {
				var chipsToIncrease = votecount - chipcount;
				for (var i = chipsToIncrease; i > 0; i--) {
					$('#chipstack').append("<div class='chip'></div>");
				}
			} else if (chipcount > votecount) {
				var chipsToDecrease = chipcount - votecount;
				for (var i = chipsToDecrease; i > 0; i--) {
					$('.chip')[0].remove();
				}
			}
		}
	});
	}
</script>