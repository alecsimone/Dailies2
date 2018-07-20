<?php /* Template Name: votenumber */ 
get_header(); ?>
<div id="votenumber"></div>

<?php get_footer(); ?>

<style>
	#menu-links {
		display: none;
	}
	body::before {
		background: none;
	}
	body {
		color: black;
	}
	div#votenumber {
		width: 160px;
		margin: auto;
	    color: hsla(42, 79%, 64%, 1);
	    text-shadow: 0 0 3px black;
	    font-weight: bold;
	    font-size: 48px;
	    background: -webkit-linear-gradient(top, rgba(0,0,0,0) 6%,rgba(0,0,0,.5) 100%);
	    border-radius: 3px;
	}
</style>


<script>
	window.setInterval(updateVotenumber, 500);
	function updateVotenumber() {
		jQuery.ajax({
			type: "POST",
			url: dailiesGlobalData.ajaxurl,
			dataType: 'json',
			data: {
				action: 'return_vote_number',
			},
			error: function(one, two, three) {
				console.log(one);
				console.log(two);
				console.log(three);
			},
			success: function(data) {
				console.log(data);
				var voteText;
				if (data === 'false' || data === '') {
					voteText = '';
				} else {
					voteText = `!vote${data}`
				}
				jQuery("#votenumber").text(voteText);
			}
		});
	}
</script>