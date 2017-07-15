// jquery-javascript //
(function($) {
	$( document ).ready( function() {
		
		$(document).on( "submit", "#commentform", function(event){
			
			/**
			 * prevent the default form submission behaviour 
			 * as we are submitting the form by AJAX, it is important to prevent the default event 
			 */
			event.preventDefault();

			//declare variables //
			var formValues,resultDiv,button,ajaxUrl,spinner, _this; 

			/**
			 * a simple hidden input field just to prevent the default form submission 
			 * the hidden field is set to false, we set to true with Javascript 
			 * additional checking and logic can be inserted here 
			 */
			$("#verification").val( "true" );

			/**
			 * Instantiate the FormData javascript object 
			 * this object enables sending form values and data through AJAX 
			 * a form ID is passed, and data can be appended and edited
			 * this formValues will be passed to the data object for ajax request 
			 */
			formValues = new FormData(document.getElementById("commentform") );
			
			//add the action key to the form Values //
			formValues.append( "action", "abbey_ajax_comment" );

			//set where the data received from the AJAx request will be inserted 
			resultDiv = $(".comment-list");

			//clone the (this) object, this represent the form //
			_this = $( this );

			// get the submit button //
			button = _this.find("input[type='submit']");

			// the wordpress Ajax url i.e. wp-admin/admin-ajax.php , this is copied from the AjaxComment object passed from php //
			ajaxUrl = abbeyAjaxComment.ajax_url;

			// the wordpress native spinner icon, this is copied from the AjaxComment object 
			spinner = abbeyAjaxComment.spinner_url;	

			/**
			 * Start the Ajax request, the ajax request is handled by JQuery Ajax API 
			 * the ajaxurl, formValues are passed to the API, the ajax is sent through a POST request 
			 * the success, error, complete and beforeSend methods handles the different stages of the AJAx request
			 */
			$.ajax({
				url: ajaxUrl, //url where the Ajax request will be handled i.e. wp-admin/admin-ajax.php
				data: formValues, //data that will be sent to the url i.e the comment form values 
				processData: false, // dont process this data, dont convert the values 
				contentType: false, //dont set the contenttype //
				type: "POST", //send as a POST request //
				success: function( data ){ //on a successful request  //
					/**
					 * copy the data received from the successful AJAX request and convert to Jquery HTML object
					 * this is to enable us check perform some check on the data received 
					 */
					jqueryData = $($.parseHTML( data ));

					// if the returned data as an alert class, meaning there is an error or warning //
					if( jqueryData.hasClass("alert") ){
						
						//insert the data before the comment form 
						_this.prepend( data );

						//but if its a success alert //
						if(jqueryData.hasClass("alert-success")){
							// reset the form, that is clear all the entered values //
							_this.each(function(){ this.reset(); });
						}

					} 
					// there is no alert class in the data received //
					else{
						// insert the data at the end of our comments list //
						resultDiv.append(data);
					}
				}, //end success method //
				error: function ( xhr, status, message){ // on error //
					alert( status + ": "+message );//show the error and status in an alert  //
				}, 
				beforeSend: function( xhr ){ //before sending the Ajax request //
					
					$("#commentform .alert").remove(); //remove any alert div //

					//change the submit button text and show a spinner image //
					button.text( "Processing . . ." ).append( "<span><img src='"+spinner+"' /></span>" ).addClass("active");
				}, 
				complete: function (  xhr ){ //when the Ajax request is complete //
					
					//change the submit button text back to what it was before and remove the .active class //
					button.text( "Post Comment" ).removeClass("active");

					//change the verification hidden input value to false //
					$("#verification").val( "false" );
				}

			});

		}); // click event //

		$(document).on( "click", ".comment-rating" , function(event){
			
			/**
			 * Prevent the default behaviour of the link 
			 */
			event.preventDefault();

			//declare variables //
			var $this, commentID, ratingValue, ratingType, rateData, ajaxUrl; 

			//copy the jQuery $this object to a variable //
			$this = $(this);

			//copy the ajax url from abbeyAjaxComment //
			ajaxUrl = abbeyAjaxComment.ajax_url;

			/**
			 * Create the datas that will be sent via Ajax 
			 * this include the commentID, action, ratingValue and anything we want to send to the Server via Ajax
			 */
			rateData = {
				commentID: $this.data("comment"), //get the comment ID from data attribute //
				ratingValue: parseInt($this.data("value")), //get and convert the rating value from data attribute //
				ratingType: $this.data("rate"), //is it an upvote or downvote //
				action: "abbey_rate_comment" //the ajax action //
			};

			/**
			 * The Ajax request is handled by jQuery Ajax API 
			 * the url we want to send the request to, data we want to send and actions when the request is successful or failed are handled here
			 */
			$.ajax({
					url: ajaxUrl, //url to send request to //
					data: rateData, // the datas to send //
					type: "POST", // send as a POST request //

					success: function( data ){ //on success //
						//insert the received data in the '.comment-rate-count' element //
						$this.find(".comment-rate-count").html(data);
					},

					error: function ( xhr, status, message){ // on error //
						alert( status + ": "+message ); // show Javascript alert with the status and message //
					}, 

					beforeSend: function( xhr ){ //before sending the request //
						//show a loading icon, uses font-awesome css to create a loading icon //
						$this.prepend( "<span class='fa fa-spinner fa-spin fa-fw'></span>" );
					}, 
					complete: function (  xhr ){ //when request is completed //
						//remove the loading icon //
						$this.find(".fa-spinner").remove();
					}

			}); //end $.ajax //


		}); //.comment-rating click//

	}); //document.ready //

})( jQuery ); 