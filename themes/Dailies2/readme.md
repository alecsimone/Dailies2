Welcome to the Dailies theme, version 2.0, aka Dailies2. It uses Wordpress as a CMS and React as its front-end framework. I've been using Wordpress for years, so that part's decently solid, but this is my first real React project, so that part is almost certainly not.

The site is made of 4 sections.

##Main
This is the bulk of the site, although one of the slimmer sections codewise. It contains the homepage, archive pages, and single pages. Posts are displayed as "Things," which currently get their data from a Wordpress custom field called "postDataObj" (but that's proving to be a less helpful shortcut than I thought, so first up on the todo list is eliminating it).

Initial pageloads get their data from a function in functions.php which passes that data to the script via wp_localize_script. When infinite scrolling, subsequent pages get their data via the Wordpress REST API.


##Secret Garden
This is probably the most complicated section (and the least used. Yaaay efficient allocation of resources!) It gets the list of the day's tournaments from Schedule.php, then queries Twitch's clips api for all the clips from those channels in the past 24 hours. 

It then turns the API response into "seedlings" (containing all the relevant data about the clip) which are then planted in the "garden." Seedlings may be cut by the user so they don't show up again (and if the user has sufficient privileges they may be "nuked" so they don't show up for anyone ever again or "grown," aka turned into posts in the site's database).

Users may also query their own streams and pull in clips from all Rocket League streams. If any query returns more than 100 results (Twitch's limit for a single query), it will also return a cursor. The stream name and that cursor are added to the LoadMore component, which, shockingly, will load the next page of clips when clicked. 


##Live
The Live page is used for the Nightly Nom Stream, although I have plans to develop it into being just an awesome way to keep up with tournaments that are currently on.

It also pulls the list of the day's streams from Schedule.php and uses that to create a Channel Changer. Soon, that channel changer will check to see if any of those streams are live, and if they are a "Live Now" button will appear below them allowing the user to embed the stream and its chat above. Currently, the Channel Changer only serves to filter the posts below.

Below the Channel Changer is a Cohosts panel, which displays info for any cohosts of the Nom Stream that night. If there are no cohosts, it does not appear.

Finally, there's the LivePostsLoop, which shows all the posts that have not been turned into Noms yet. They're displayed as LittleThings, a stripped down version of the Things used in the Main section, and they're updated every 3 seconds with a REST API call.


##Schedule
This shit's real simple. It's just a huge PHP array looped over on a PHP page.

There is also a "Rules" page, which is just straight up HTML. 



Obviously I could probably do some more explaining, but I think this'll cut it for now. Enjoy my code, let me know if you have any questions. Thanks for any help you might offer.

Cheers,
Alec