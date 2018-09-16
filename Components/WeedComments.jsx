import React from "react";
import WeedComment from './WeedComment.jsx';

export default class WeedComments extends React.Component{
	constructor() {
		super();
		this.postCommentHandler = this.postCommentHandler.bind(this);
	}

	componentDidMount() {
		let postCommentHandler = this.postCommentHandler;
		jQuery("#weedCommentBox").keypress(function(e) {
			if(e.which == 13 && !e.shiftKey) {
				postCommentHandler(e);
			}
		});
	}

	postCommentHandler(e) {
		e.preventDefault();
		let comment = e.target.value;
		e.target.value = '';
		let commentObject = {
			comment,
			replytoid: null,
		}
		this.props.postComment(commentObject);
	}

	render() {
		let commentsLoader;
		let comments;
		let boundThis = this;
		if (this.props.commentsLoading == true) {
			commentsLoader = <div className="lds-ring"><div></div><div></div><div></div><div></div></div>;
		} else {
			commentsLoader = "";
			if (this.props.comments.length == 0) {
				comments = <div className="weedComment">No Comments yet</div>;
			} else {
				comments = this.props.comments.map( function(commentData, index) {
					return <WeedComment key={commentData.id} commentID={commentData.id} commenter={commentData.commenter} pic={commentData.pic} commentTime={commentData.time} comment={commentData.comment} score={commentData.score} yeaComment={boundThis.props.yeaComment} />;
				});
			}
		}
		return(
			<div id="weedComments">
				<div id="commentsLoader">{commentsLoader}</div>
				{comments}
				<textarea id="weedCommentBox" name="weedCommentBox" placeholder="Add Comment" minLength="1" maxLength="2200" spellCheck="true" rows="1" onSubmit={this.commentHandler}/>
			</div>
		)
	}

}