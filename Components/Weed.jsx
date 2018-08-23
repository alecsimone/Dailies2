import React from "react";
import ReactDOM from 'react-dom';

export default class Weed extends React.Component{
	render() {
		return(
			<section id="Weeder">
				Yo
			</section>
		)
	}
}

ReactDOM.render(
	<Weed />,
	document.getElementById('weedApp');
);