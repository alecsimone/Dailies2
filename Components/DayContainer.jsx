import React from "react";
import Thing from './Thing.jsx';

export default class DayContainer extends React.Component {
	render() {
		var date = this.props.dayData.date;
		var monthsArray = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		if (date.day === '1' || date.day === '01' || date.day === '21' || date.day === '31') {
			var dayString = date.day + 'st';
		} else if (date.day === '2' || date.day === '02' || date.day === '22') {
			var dayString = date.day + 'nd';
		} else if (date.day === '3' || date.day === '03' || date.day === '23') {
			var dayString = date.day + 'rd'
		} else {
			var dayString = date.day + 'th';
		}
		if (dayString.charAt(0) === '0') {
			dayString = dayString.substring(1);
		}
		var userData = this.props.userData;
		var voteDataObj = JSON.parse(this.props.dayData.voteDatas);
		var things = this.props.dayData.postDatas;
		function thingsByScore(a,b) {
			//let parsedA = JSON.parse(a);
			//let parsedB = JSON.parse(b);
			//let scoreA = parseFloat(parsedA.votecount, 10);
			//let scoreB = parseFloat(parsedB.votecount, 10);
			let scoreA = parseFloat(a.votecount, 10);
			let scoreB = parseFloat(b.votecount, 10);
			return scoreB - scoreA;
		}
		var thingsSorted = things.sort(thingsByScore);
		var thingsArray = Object.keys(thingsSorted);
		var thingComponents = thingsArray.map(function(key) {
			var parsedThingData = things[key];
			var voteData = voteDataObj[parsedThingData['id']];
			return(
				<Thing thingData={parsedThingData} userData={userData} voteData={voteData} key={parsedThingData.id} />
			)
		})
		return(
			<section className="dayContainer">
				<div className="daytitle">NOMINEES FOR {monthsArray[date.month - 1].toUpperCase()} {dayString.toUpperCase()}</div>
				{thingComponents}
			</section>
		)
	}
}