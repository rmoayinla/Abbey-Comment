<?php

class Abbey_Sort_Comments{

	private $sort_keys = array();

	private $sort_menus = array();

	public function __construct(){
		add_action ( "abbey_before_comments_display", array( $this, "display_menu" ) );
		

		$this->sort_keys = apply_filters( "abbey_sort_comments_keys", array(
			"orderby" => "abbey_c_orderby", 
			"order" => "abbey_c_order", 
			"meta_query" => "abbey_c_meta_query",
			"meta_key" =>  "abbey_c_meta_key"
			 ) 
		);

		add_filter( "query_vars", array( $this, "comment_sort_vars" ) );

		add_action('pre_get_comments', array( $this, 'sort_comments' ) ); 

		$this->create_sort_menus();
	}

	function display_menu(){	?>
		<ul class="nav nav-pills">
			<?php 
				if( count( $this->sort_menus ) > 0 ) {
					$html = "";
					foreach( $this->sort_menus as $sort_by => $menu ){
						$html .= sprintf('<li><a href="%1$s" title="%2$s"> <span class="fa fa-fw %4$s"> </span> %3$s </a></li>', 
											$this->query_vars_url( $menu["url"] ), 
											sprintf( __( "Sort comments by %s", "abbey-ajax-comment" ), $sort_by ), 
											esc_html( ucwords( $sort_by ) ), 
											esc_attr( $menu["icon"] )
										);
					}
				}
				echo $html;		
			?>
		</ul>			<?php 
	}

	function comment_sort_vars( $vars ){
		if ( count( $this->sort_keys ) > 0 )
			foreach ( $this->sort_keys as $key ){
				$vars[] = $key;
			}

		return $vars;
	}

	function query_vars_url ( $key = array() ){
		$defaults = array(
			$this->sort_keys["orderby"] => "", 
			$this->sort_keys["meta_query"] => "", 
			$this->sort_keys["meta_key"] => "",
			$this->sort_keys["order"] => "",
			);

		 
		$key = array_merge( $defaults, $key );

		if( empty( get_query_var( $this->sort_keys["order"] ) ) ){
			$key[ $this->sort_keys["order"] ] = ( get_option( "comment_order" ) === "asc" ) ? "DESC" : "ASC";
		}
		else {
			$key[ $this->sort_keys["order"] ] = ( get_query_var( $this->sort_keys["order"] ) === "ASC" ) ? "DESC" : "ASC";
		}

		$key = array_filter( $key, function ( $value ){ return !empty( $value ); } );

		return esc_url( add_query_arg( $key, get_the_permalink()."#comments" ) );
	}

	function create_sort_menus(){
		$order_value = "";
		$this->sort_menus[ "date" ] = array( "icon" => "fa-clock-o", "url" => array( ) ); 
		$this->sort_menus[ "upvote" ] = array( "icon" => "fa-thumbs-o-up", 
			"url" => [ "abbey_c_meta_key" => "abbey_comment_rating_upvote_count", "abbey_c_orderby" => "meta_value" ] 
			);
	}

	function sort_comments( $query ){
		$args = array();

		if( count( $this->sort_keys ) > 0 ){
			foreach( $this->sort_keys as $key => $sort ){
				if( !empty( get_query_var( $sort ) ) )
					$args[ $key ] = get_query_var( $sort );
			}
		}
		/*
		$args['meta_key'] = "abbey_comment_rating_upvote_count"; 
    	$args['meta_query'] = array(
        "key" => "abbey_comment_rating_upvote_count", 
        "value" => "1", 
        "compare" => ">=", 
        "type" => "numeric"
    	);
    	$args['orderby'] = 'meta_value'; 
    	$args['order'] = "DESC";
    	*/
    	$query->query_vars = wp_parse_args( $args, $query->query_vars );
	}


	
	
}




new Abbey_Sort_Comments();