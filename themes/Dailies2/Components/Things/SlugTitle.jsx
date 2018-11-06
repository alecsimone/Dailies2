import React from "react";

const SlugTitle = (titleData) => {
	let titleLink;
	if (titleData.type === "twitch") {
		titleLink = `https://clips.twitch.tv/${titleData.slug}`;
	} else if (titleData.type === "twitter") {
		titleLink = `https://twitter.com/statuses/${titleData.slug}`;
	} else if (titleData.type === "gfycat") {
		titleLink = `https://gfycat.com/${titleData.slug}`;
	} else if (titleData.type === "youtube" || titleData.type === "ytbe") {
		titleLink = `https://www.youtube.com/watch?v=${titleData.slug}`;
	}
	return <a href={titleLink} target="_blank">{titleData.title}</a>;
};

export default SlugTitle;