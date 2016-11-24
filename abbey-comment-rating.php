<?php

class Abbey_Comment_Rating{
	private $user_id, $comment_ID; 

	private $comment_key = array(); 
	private $comment_count_key = array(); 

	public function __construct(){
		add_filter( "abbey_comment_action_links", array( $this, "comment_action_links" ), 10, 2 );

		add_action ( "wp_ajax_nopriv_abbey_rate_comment", array ( $this, "rate_comment" ) );
		add_action ( "wp_ajax_abbey_rate_comment", array ( $this, "rate_comment" ) );

		$this->comment_key = apply_filters( "abbey_comment_rating_keys", array( 
			"upvote" => "abbey_comment_rating_upvote", 
			"downvote" => "abbey_comment_rating_downvote"
	 		)
		);
		$this->comment_count_key = apply_filters( "abbey_comment_rating_count_keys", array(
			"upvote" =>  "abbey_comment_rating_upvote_count", 
			"downvote" => "abbey_comment_rating_downvote_count"
			)
		);
	}

	function comment_action_links( $links, $comment ){
		$links[ "upvote" ] = sprintf( '<a href="#" class="comment-rating" title="%1$s" data-comment="%2$s" data-value="%3$s" data-rate="upvote">
									<span id="upvote-count" class="comment-rate-count">%3$s</span><span class="fa fa-thumbs-o-up"></span></a>', 
								__( "Like comment", "abbey-ajax-comment" ), 
								$comment->comment_ID, 
								$this->display_comment_rating( $comment->comment_ID, "upvote" ) 
								); 
		$links[ "downvote" ] = sprintf( '<a href="#" class="comment-rating" title="%1$s" data-comment="%2$s" data-value="%3$s" data-rate="downvote">
									<span id="downvote-count" class="comment-rate-count">%3$s</span><span class="fa fa-thumbs-o-down"></span></a>', 
								__( "Dislike comment", "abbey-ajax-comment" ), 
								$comment->comment_ID, 
								$this->display_comment_rating( $comment->comment_ID, "downvote" ) 
								); 
		return $links;
	}
	function rate_comment(){
		$this->comment_ID = $_POST["commentID"];
		$rating_value = intval( $_POST["ratingValue"] );
		$rating_type = ( in_array( $_POST["ratingType"], ["upvote", "downvote"] ) ) ? $_POST["ratingType"] : "";
		$username = $user_status = $user = "";
		
		$this->user_id = ( is_user_logged_in() ) ? wp_get_current_user()->ID : preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] );
		
		
		$rating = $this->add_comment_rating( $rating_type );

		$this->add_rating_count( $rating_type, $rating );

		print_r( get_comment_meta( $this->comment_ID, $this->comment_count_key[$rating_type], true ) );

		wp_die();
	}

	function display_comment_rating( $id, $key ){
		$count = 0;
		$ratings = get_comment_meta( $id, $this->comment_key[$key], true );
		if( !empty( $ratings ) ){
			$count = array_sum( array_map( "count", $ratings ) );
		}
		return $count;
	}

	function clear_comment_rating( $key ){
		delete_comment_meta( $this->comment_ID, $key );
	}

	function add_comment_rating ( $key ){

		$rating = get_comment_meta( $this->comment_ID, $this->comment_key[$key], true);

		$comment_rating = array( "rating_by" => $this->user_id ); 
		
		if( empty($rating) || count( $rating ) < 1 ){
			add_comment_meta( $this->comment_ID, $this->comment_key[$key], array( $comment_rating ), true );
		} elseif ( count( $rating ) > 0 ) {
			$rating[] = $comment_rating;
			update_comment_meta( $this->comment_ID, $this->comment_key[$key], $rating );
		}

		return $this->display_comment_rating( $this->comment_ID, $key );
	}

	function add_rating_count( $key, $count ){
		update_comment_meta( $this->comment_ID, $this->comment_count_key[$key], $count );
	}

}

new Abbey_Comment_Rating();
