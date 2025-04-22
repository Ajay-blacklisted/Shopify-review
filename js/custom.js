console.log('ssdf');
(function(){
	var loadScript = function(url, callback){
		var script = document.createElement("script");
		script.type = "text/javascript";
		if (script.readyState){ 
			script.onreadystatechange = function(){
				if (script.readyState == "loaded" || script.readyState == "complete"){
					script.onreadystatechange = null;
					callback();
				}
			};
		} else {
			script.onload = function(){
				callback();
			};
		}
		script.src = url;
		document.getElementsByTagName("head")[0].appendChild(script);
	};
	var myAppJavaScript = function($){
		$(document).ready(function() {
			var baseUrl='https://blacklistedagency.com/projects/Shopify-review/';
			var pagetype = window.ShopifyAnalytics.meta['page']['pageType'];
			var mainurl = window.location.href;
			var shop = Shopify.shop
			if((mainurl.includes('products'))){  
				var productid = window.ShopifyAnalytics.meta['product']['id'];

				$.ajax({
					type: "POST",
					url: baseUrl+'review.php',
					data: {'storename':shop,'productid':productid},
					success: function(response){ 	
					if(response != 1 ){
						$('.product').append(response);   
					}
						else{

					}					
				}
			});
			};

			$(document).on("click",".reviewButton",function() {
			    alert('ssss');
				$('.smartreview').show();
			});

			$(".dropdown-toggle").click(function(){
              $(".dropdown-menu").toggle();
            });
            
            $(document).on("click","#review1",function() {
			    alert('dd');
				$("#review1").addClass("smart-star").removeClass("smart-star-0");
				$("#review2").addClass("smart-star-0").removeClass("smart-star");
				$("#review3").addClass("smart-star-0").removeClass("smart-star");
				$("#review4").addClass("smart-star-0").removeClass("smart-star");
				$("#review5").addClass("smart-star-0").removeClass("smart-star");
				$("#rating-value").val('1');
			});

			$(document).on("click","#review2",function() {
				$("#review1").addClass("smart-star").removeClass("smart-star-0");
				$("#review2").addClass("smart-star").removeClass("smart-star-0");
				$("#review3").addClass("smart-star-0").removeClass("smart-star");
				$("#review4").addClass("smart-star-0").removeClass("smart-star");
				$("#review5").addClass("smart-star-0").removeClass("smart-star");
				$("#rating-value").val('2');
			});

			$(document).on("click","#review3",function() {
				$("#review1").addClass("smart-star").removeClass("smart-star-0");
				$("#review2").addClass("smart-star").removeClass("smart-star-0");
				$("#review3").addClass("smart-star").removeClass("smart-star-0");				
				$("#review4").addClass("smart-star-0").removeClass("smart-star");
				$("#review5").addClass("smart-star-0").removeClass("smart-star");
				$("#rating-value").val('3');
			});

			$(document).on("click","#review4",function() {				
				$("#review1").addClass("smart-star").removeClass("smart-star-0");
				$("#review2").addClass("smart-star").removeClass("smart-star-0");
				$("#review3").addClass("smart-star").removeClass("smart-star-0");
				$("#review4").addClass("smart-star").removeClass("smart-star-0");				
				$("#review5").addClass("smart-star-0").removeClass("smart-star");
				$("#rating-value").val('4');
			});

			$(document).on("click","#review5",function() {
				$("#review1").addClass("smart-star").removeClass("smart-star-0");
				$("#review2").addClass("smart-star").removeClass("smart-star-0");
				$("#review3").addClass("smart-star").removeClass("smart-star-0");
				$("#review4").addClass("smart-star").removeClass("smart-star-0");
				$("#review5").addClass("smart-star").removeClass("smart-star-0");	
				$("#rating-value").val('5');			
			});	
			$("#reviewForm").submit(function (event) {
				event.preventDefault();
				$.ajax({
					type: "POST",
					url: baseUrl+"save_review.php",
					data:  new FormData(this),
					contentType: false,
					cache: false,
					processData:false,
				}).done(function (data) {
					$('.success-message').show();
				});
			});
		});
	};
	if ((typeof jQuery === 'undefined') || (parseInt(jQuery.fn.jquery) === 1 && parseFloat(jQuery.fn.jquery.replace(/^1\./,"")) < 9.1)) {
		loadScript('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js', function(){
			jQuery191 = jQuery.noConflict(true);
			myAppJavaScript(jQuery191);
		});
	} else {
		myAppJavaScript(jQuery);
	}
})();