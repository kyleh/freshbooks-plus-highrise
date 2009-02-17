$(document).ready(function() {
						   
	// Uncheck all checkboxes at page load					   
	$("input:checkbox:checked").attr("checked", "");
	
  	// show form-note div on input focus
    $(".input").focus(function () {
         $(this).next(".form-note").css('display','block');
		 $(this).parents(".api-input").addClass("api-active");
		 $(this).addClass("input-active");
    });
	
	// hide form-note div on input blur
	$(".input").blur(function () {
         $(this).next(".form-note").css('display','none');
		 $(this).parents(".api-input").removeClass("api-active");
		 $(this).removeClass("input-active");
    });
	
	// Show tags div
	$('#showTags').click( function() {
		$('#tags').slideDown();
	});
	
	// Hide tags div and uncheck all checkboxes
	$('#hideTags_1').click( function() {
		$('#tags').hide();
		$('#showTags').show();
		$("input:checkbox:checked").attr("checked", "");
	});
	
	// Hide tags div and uncheck all checkboxes
	$('#hideTags_2').click( function() {
		$('#tags').hide();
		$('#showTags').show();
		$("input:checkbox:checked").attr("checked", "");
	});
	
});