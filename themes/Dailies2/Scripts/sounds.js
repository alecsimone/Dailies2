var killSounds = {
	files: {
		BasicKill: .4,
		BasicStab: .4,
		RifleBlast: .4,
		QuickAutofire: .4,
		MachineGun: .4,
		FiveShots: .4,
		PacmanDeath: .4,	
	},
	sounds: [],
}
processSoundsObject(killSounds);

var specialKillSounds = {
	files: {
		FiveAngryShots: .4,
		Explosion: .4,
		StabbingSpree: .4,
		Shotgunned: .4,
		Drowning: .4,
		Nani: .8,
	},
	sounds: [],
}
processSoundsObject(specialKillSounds);

var promoSounds = {
	files: {
		ZeldaItem: .4,
		ZeldaSmallItem: .4,
		Reward1: .4,
		SingleYay: .4,
		MarioMushroom: .4,
		WowLevelUp: .4,
		SonicRing: .4,
		MarioCoin: .4,
		ZeldaPuzzle: .4,
	},
	sounds: [],
}
processSoundsObject(promoSounds);

var specialPromoSounds = {
	files: {
		Yay: .4,
		SkyrimLevelUp: .4,
		ZeldaHeartContainer: .4,
		MarioFanfare: .4,
		FinalFantasyVictory: .4,
		SmashVictory: .4,
	},
	sounds: [],
}
processSoundsObject(specialPromoSounds);

function processSoundsObject(soundsObject) {
	jQuery.each(soundsObject.files, function(file, volume) {
		soundsObject['sounds'][file] = new Audio(`${dailiesGlobalData.thisDomain}/wp-content/uploads/sounds/${file}.mp3`);
		soundsObject['sounds'][file]['volume'] = volume;
	});
}

function playRandomSound(soundsArray) {
	let soundKeys = Object.keys(soundsArray);
	let soundKeysLength = soundKeys.length;
	let soundKeyIndex = Math.floor(Math.random() * soundKeysLength);
	let soundKey = soundKeys[soundKeyIndex]; 
	soundsArray[soundKey].play();
}

window.playAppropriateKillSound = function() {
	jQuery.ajax({
		type: "POST",
		url: dailiesGlobalData.ajaxurl,
		dataType: 'json',
		data: {
			action: 'get_chat_votes',
		},
		error: function(one, two, three) {
			return false;
		},
		success: function(data) {
			if (data.nay.length > 8) {
				playRandomSound(specialKillSounds.sounds)
			} else {
				playRandomSound(killSounds.sounds)
			}
		}
	});
}

window.playAppropriatePromoSound = function() {
	jQuery.ajax({
		type: "POST",
		url: dailiesGlobalData.ajaxurl,
		dataType: 'json',
		data: {
			action: 'get_chat_votes',
		},
		error: function(one, two, three) {
			return false;
		},
		success: function(data) {
			if (data.yea.length > 5) {
				playRandomSound(specialPromoSounds.sounds)
			} else {
				playRandomSound(promoSounds.sounds)
			}
		}
	});
}

export {playAppropriatePromoSound, playAppropriateKillSound};