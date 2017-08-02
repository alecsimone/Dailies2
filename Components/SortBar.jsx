import React from "react";

export default class SortBar extends React.Component{
	render() {
		if (this.props.tax === "post_tag") {
			var tax = "tag";
		} else {
			var tax = this.props.tax;
		}
		var classBase = "orderLink ";
		var newestClasses = classBase + "newest";
		var oldestClasses = classBase + "oldest";
		var topClasses = classBase + "top";
		if (this.props.orderby === "date") {
			if (this.props.order === "ASC") {
				oldestClasses = oldestClasses + " currentSort";
			} else {
				newestClasses = newestClasses + " currentSort";
			}
		} else if (this.props.orderby === 'meta_value_num' && this.props.order === 'DESC') {
				topClasses = topClasses + " currentSort";
		} else {
			newestClasses = newestClasses + " currentSort";
		}
		return(
			<nav id="sortbar">
				Sort by: <a href={dailiesGlobalData.thisDomain + "/" + tax + "/" + this.props.slug + "/?orderby=date&order=desc"} className={newestClasses} >Newest</a>
				<a href={dailiesGlobalData.thisDomain + "/" + tax + "/" + this.props.slug + "/?orderby=date&order=asc"} className={oldestClasses} >Oldest</a>
				<a href={dailiesGlobalData.thisDomain + "/" + tax + "/" + this.props.slug + "/?orderby=meta_value_num&order=desc"} className={topClasses} >Top</a>
			</nav>
		)
	}
}