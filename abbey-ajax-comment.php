<?php
/**
 * Plugin Name: Abbey Ajax Comment
 * Description: Submit comment through Ajax 
 * Author: Rabiu Mustapha
 * Version: 0.1
 * Text Domain: abbey-ajax-comment
*/
class Abbey_Ajax_Comment{

	public function __construct(){

		add_action ( "wp_enqueue_scripts", array ( $this, "enque_js" ) );

		add_filter( 'comment_form_submit_field', array( $this, 'abbey_comment_hidden_fields' ), 10, 2 );

		add_action ( "wp_ajax_nopriv_abbey_ajax_comment", array ( $this, "process_comment" ) );

		add_action ( "wp_ajax_abbey_ajax_comment", array ( $this, "process_comment" ) );
	}

	function enque_js(){
		wp_enqueue_script( "abbey-ajax-comment-script", plugin_dir_url( __FILE__ )."/abbey-ajax-comment.js", array( "jquery" ), 1.0, true );
		wp_localize_script( "abbey-ajax-comment-script", "abbeyAjaxComment", 
			array(
				"ajax_url" => admin_url( "admin-ajax.php" ), 
				"spinner_url" => admin_url( "images/spinner.gif" )
			) 
		);
	}

	function process_comment(){
		if( empty( $_POST["action"] ) || $_POST["action"] !== "abbey_ajax_comment" ){
			return;
		}
		elseif( !isset( $_POST["abbey-ajax-comment-verification"] ) || $_POST["abbey-ajax-comment-verification"] === "false" ){
			echo sprintf( '<div class="alert alert-warning">%1$s</div>', 
					__( "Sorry, you can only submit comment through Ajax", "abbey-ajax-comment" )
				);
		} else {
			$comment = wp_handle_comment_submission( wp_unslash( $_POST ) );
			if ( is_wp_error( $comment ) && !empty( intval( $comment->get_error_data() ) ) ) 
				echo sprintf( '<div class="alert alert-warning"> %1$s </div>', $comment->get_error_message() );
		
			if ( $comment instanceof WP_Comment ){
				if ( is_user_logged_in() && current_user_can("moderate_comments") ){
					wp_list_comments( array(
						'style'      => 'ol',
						'short_ping' => true,
						'avatar_size'=> 60, 
						'callback' => array( $this, "format_comment" )
						), array( $comment )
					);
				} else {
					echo sprintf( '<div class="alert alert-success"><strong>%1$s<strong> %2$s </div>', 
									esc_html__( "Success:", "abbey-ajax-comment" ), 
									esc_html__( "Your comment has been posted and is awaiting moderation. Thanks", "abbey-ajax-comment" )
								);
				}
			}
		}

		wp_die();
	}

	function format_comment( $comment, $args, $depth ){
		$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';	?>		
	<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $args['has_children'] ? 'parent media' : 'media' ); ?>
		itemscope itemtype="http://schema.org/Comment" itemprop="comment">

		<?php if ( 0 != $args['avatar_size'] ): ?>
			<div class="media-left text-center">
				<a href="<?php echo get_comment_author_url(); ?>" class="media-object" itemprop="Author">
					<?php echo get_avatar( $comment, $args['avatar_size'], "", "", array( "class" => "img-circle" ) ); ?>
				</a>
			</div><!--.media-left closes -->
		<?php endif; ?>

		<div class="media-body">
			<div class="row margin-left-sm">	
				<h4 class="media-heading" itemprop="author"><?php echo get_comment_author_link(); ?> </h4>
				<div class="comment-time">
					<time datetime="<?php comment_time( 'c' ); ?>" itemprop="datePublished">
						<?php printf( _x( '%1$s at %2$s', '1: date, 2: time' ), get_comment_date(), get_comment_time() ); ?>
					</time>
				</div><!-- .comment-time -->
		
				<div class="comment-content" itemprop="text">
					<?php comment_text(); ?>
				</div><!-- .comment-content -->
				
			</div><!--row-->
		</div><!--.media-body -->	<?php
	}	
	
	function abbey_comment_hidden_fields( $field, $args ){
		$field .= '<div class="comment-form-verification form-group">
						<input id="verification" name="abbey-ajax-comment-verification" 
						 	type="hidden" value="false"/>
					</div>';
		return $field;
	}

}

require plugin_dir_path(__FILE__)."abbey-comment-rating.php";
require plugin_dir_path(__FILE__)."abbey-sort-comments.php";



new Abbey_Ajax_Comment();