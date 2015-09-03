$(document).ready(function(){
	$("#analyze").submit(function() {
		$.ajax({
			type: "POST",
			url: "",
			data: $("#analyze").serialize(),
			beforeSend: function(XMLHttpRequest){
				$("#loading").addClass("loading-visible");
				$('#generated_content').empty();	
			}, 
			success: function(data){
				$("#loading").removeClass("loading-visible");
				$('#generated_content').append(data);
	
			}
		});

		return false;
	});
	
}); 