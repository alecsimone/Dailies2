Scouting is the first round of the Dailies process. https://Dailies.gg/Scout pulls in all potentially relevant clips and lets people vote on them. The clips that get a high enough score will be used in the next round, the rest wither and die.

In the past, Scout has been called "Weed" and "1R", so those names may pop up from time to time as synonyms.

## Pulling Fresh Clips ##
"All potentially relevant clips" is a fun phrase. It includes:
-Any clip with at least three views from an event in schedule
-The top 100 most viewed Rocket League clips
-Twitter mentions of @Rocket_Dailies with media attached, links to supported sites, or replies to tweets that meet either of those conditions
-Clips submitted through the form on https://Dailies.gg.

## Query Schedule ##
-Clips submitted through the form are immediately added to the database and do not need to be "pulled" at any point
-Every 30 minutes a cron job does the whole set of queries for both Twitch and Twitter
-Twitch can be done by the client, but Twitter does not accept cross-origin headers so it must be done server-side. This significantly delays the initial page load time while showing nothing, and the data changes slowly, so it is not done to-order. Twitch is queried on any page load, unless it's already been queried in the past minute

So when the page loads, it does the following:
-generateWeedData() collects all the existing clips still in contention and localizes it to the page as weedData
-If new Twitch clips have not been pulled in the last minute, the client queries for them, filters them, adds their information to weedData locally, and sends them off to the server for storage.

## generateWeedData ##
The generateWeedData() function is called by clientInformation for the Scout page, which then localizes the returned data to the page so it can be accessed client-side. It provides the following data:
-streamList: The list of Twitch channels to query clips for
-lastClipUpdateTime: The last time we queried for new Twitch clips
-cutoffTimestamp: The oldest a clip can be for our database to allow it in
-clips: The existing clip database filtered through getCleanPulledClipsDB()
-seenSlugs: The list of slugs this person has already voted on

__We are now moving to work being done client side, currently in the theme, not the plugin__

## Filtering ##
Once we have all the clips, we have to filter out the bad ones
-Remove anything that has already been nuked
-Remove anything with a score below -50
-Remove anything that is in this person's seenSlugs
-Remove anything that is a similar moment to this person's seenSlugs


## Sorting ##
Once all the clips have been retrieved, they get sorted
-First, just to be safe, we kill anything that has been nuked
-Any clips without vodlinks are pushed to the back
-Any clips with 0 views are pulled to the front
-Tweets and user submits are pulled right behind them
-Clips are then sorted by 5 progressive dimensions (only ties on a lower number move on to be sorted by the later number):
1. vodID
2. vodTime
3. Score
4. Views
5. Age

The sorted clips are then put into clipsArray, and the app is rendered for the first slug in the array.

## Progress Bars ##
At the end of the constructor method, we count all the clips currently in weedData. This is then set in state as the totalClips number. It is decreased every time a clip is nuked.

Two bars cross the top of the page tracking progress to that number. 
-One tracks the community's collective progress by counting the number of clips that have been nuked or have votes on them 
-The other tracks your personal progress on judging all of them by counting clipsArray. 

## Voting ##
Most people have two options for voting:
-Yea will add their rep to the clip's score
-Nay subtracts 20% of their rep from the clip's score

Editors also have a third option
-Nuke the clip and remove it from consideration entirely. This also reduces state.totalClips by the number of nuked clips

After voting, we check the rest of the remaining twitch clips to see if their vodlink comes from a similar time, and we nuke the ones that do.

All of this will be handled by the VotingMachine component. After voting, that clip will be removed from clipsArray and the next one will be rendered.

## Components ##
The Scout page contains the following components found elsewhere throughout the site:
-ClipPlayer takes in a slug and a type and embeds it
-VotingMachine takes in a slug and shows the vote buttons for it along with its current score and voters in each direction
-CommentBox takes in a slug and displays the comments on it, along with an input to add a new comment.
-MetaBox that contains components with additional information about the slug


## You Won ##
When there are no clips to display, we show a fun little You Won! page. 