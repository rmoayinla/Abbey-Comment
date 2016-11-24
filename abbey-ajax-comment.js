// jquery-javascript //
(function($) {
	$( document ).ready( function() {
		
		$( "#commentform #submit" ).click(function(event){
			event.preventDefault();
			var formValues,resultDiv,button,ajaxUrl,spinner; 

			$("#verification").val( "true" );

			formValues = new FormData(document.getElementById("commentform") );
			formValues.append( "action", "abbey_ajax_comment" );
			resultDiv = $(".comment-list");
			button = $( this );
			ajaxUrl = abbeyAjaxComment.ajax_url;
			spinner = abbeyAjaxComment.spinner_url;	

			$.ajax({
				url: ajaxUrl,
				data: formValues,
				processData: false, 
				contentType: false, 
				type: "POST",
				success: function( data ){
					jqueryData = $($.parseHTML( data ));
					if( jqueryData.hasClass("alert") ){
						$( "#commentform" ).prepend( data );
						if(jqueryData.hasClass("alert-success")){
							$("#commentform").each(function(){
								this.reset();
							});
						}
					} else{
						resultDiv.append(data);
					}
				},
				error: function ( xhr, status, message){
					alert( status + ": "+message );
				}, 
				beforeSend: function( xhr ){
					$("#commentform .alert").remove();
					button.text( "Processing . . ." ).append( "<span><img src='"+spinner+"' /></span>" ).addClass("active");
				}, 
				complete: function (  xhr ){
					button.text( "Post Comment" ).removeClass("active");
					$("#verification").val( "false" );
				}

			});

		}); // click event //
	$(".comment-rating").click(function(event){
		event.preventDefault();
		var $this, commentID, ratingValue, ratingType, rateData, ajaxUrl; 
		$this = $(this);
		ajaxUrl = abbeyAjaxComment.ajax_url;
		rateData = {
			commentID: $this.data("comment"), 
			ratingValue: parseInt($this.data("value")),
			ratingType: $this.data("rate"), 
			action: "abbey_rate_comment"
		};
		$.ajax({
				url: ajaxUrl,
				data: rateData,
				type: "POST",
				success: function( data ){
					$this.find(".comment-rate-count").html(data);
				},
				error: function ( xhr, status, message){
					alert( status + ": "+message );
				}, 
				beforeSend: function( xhr ){
					$this.prepend( "<span class='fa fa-spinner fa-spin fa-fw'></span>" );
				}, 
				complete: function (  xhr ){
					$this.find(".fa-spinner").remove();
				}

			});


	}); //.comment-rating click//

	}); //document.ready //

})( jQuery ); 