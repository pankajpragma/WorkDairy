jQuery(function($) {	
	 $(".Collapsable").click(function () {
        $(this).parent().children().toggle();
        $(this).toggle();
    });
	$(".topopup1").click(function() {
			loading(); // loading
			setTimeout(function(){ // then show popup
				loadPopup('toPopup1'); // function show popup
			}, 100); 
		return false;
	});
	$(".topopup2").click(function() {
		url = "plugin.php?page=WorkDairy/view_today_updates&ajax=1";
		$.when(send_data(url, '')).done(function(a1){	
			$('#toPopup3 #popup_content').html(a1); 
			loadPopup('toPopup3');
		});			 
		return false;
	});

	$(".link_view").click(function() {
			url = "plugin.php?page=WorkDairy/view_bug&ajax=1&id="+$(this).attr('data-value');
			$.when(send_data(url, '')).done(function(a1){	
				$('#toPopup3 #popup_content').html(a1); 
				loadPopup('toPopup3');
			});			 
		return false;
	});

	$(".link_operation").click(function() {
		loading(); // loading
		$('#status_title').html('Add Note');
		if($(this).attr('data-value2') == '20')
		{
			$('#status_title').html('Mark Feedback');
		}			
		else if($(this).attr('data-value2') == '90')
		{
			$('#status_title').html('Mark Closed');
		}			
		else if($(this).attr('data-value2') == '50')
		{			
			$('#status_title').html('Assigned');
			$('#assigned_me1').show();
			$('#assigned_me2').show();
			if($(this).attr('data-value3') != 'undefined')
			{
				$('#new_handler_id').val($(this).attr('data-value3'));
			}
				
		}		
		loadPopup('toPopup4');
		
		$('#note').focus();
		$('#toPopup4 #bug_id').val($(this).attr('data-value1'));
		$('#toPopup4 #status').val($(this).attr('data-value2'));
	 
		return false;
	});
	
	$(".timer_operation").click(function() {
		loading(); // loading
		bug_id = $(this).attr('data-value1');
		url = "plugin.php?page=WorkDairy/bug_timer&ajax=1";
		data = "&bug_id="+bug_id+"&operation="+$(this).attr('data-value2');
		$.when(send_data(url, data)).done(function(a1){	
			var status = $.trim(a1);			 
			if(status == "1")
			{ 
				window.location.href = 'plugin.php?page=WorkDairy/my_dairy';
			}
			else
			{
				alert(a1.replace(/<\/?[^>]+>/gi, ''));
			}
		});	

		return false;
	});

	
	$(".active_tab").click(function() {
		var sel_tab = $(this).attr('data-value')
		$(".active_tab").removeClass(' selected_tab ');
		 $(this).addClass(' selected_tab ');
		if(sel_tab == 'n')
		{
			$(".current_week").hide();
			$(".next_week").show();
		}
		else
		{
			$(".current_week").show();
			$(".next_week").hide();
		}
		return false;
	});
		
	$("#btn_submit_operation").click(function() {		
		var note = $.trim($('#note').val());	
		bug_id = $.trim($('#toPopup4 #bug_id').val());
		if(bug_id == '')
			return false;
		if($('#toPopup4 #status').val() == '')
			return false;
		
		if(note == '')
		{
			$('#note').focus();
			return false;
		} 
		
		data  = $("#toPopup4 :input").serialize();
		url = "plugin.php?page=WorkDairy/bug_operation&ajax=1";
		$.when(send_data(url, data)).done(function(a1){	
			var status = $.trim(a1);			 
			if(status == "1")
			{
				if($('#toPopup4 #status').val() == '90')
				{
					$('.li_bug_'+bug_id).remove('');
					$('#note').val('');
					$('#toPopup4 #bug_id').val('');
					$('#toPopup4 #status').val('');
					disablePopup();
					closeloading();
					return;
				}				
				window.location.href = 'plugin.php?page=WorkDairy/my_dairy';
			}
			else if(status == "2")
			{
				$('#note').val('');
				$('#toPopup4 #bug_id').val('');
				$('#toPopup4 #status').val('');
				disablePopup();
				closeloading();
			}
			else
			{
				alert(a1.replace(/<\/?[^>]+>/gi, ''));
			}
		});		 
		return false;
	});
 
	
	$("#btn_submit_quick_task").click(function() {		
		var project_id = $.trim($('[name="project_id"]').val());	
		if(project_id == '')
		{
			$('[name="project_id"]').focus();
			return false;
		}
		var category_id = $.trim($('[name="category_id"]').val());	
		if(category_id == '')
		{
			$('[name="category_id"]').focus();
			return false;
		}
		var priority = $.trim($('[name="priority"]').val());	
		if(priority == '')
		{
			$('[name="priority"]').focus();
			return false;
		}
		var due_date = $.trim($('[name="due_date"]').val());	
		if(due_date == '')
		{
			$('[name="due_date"]').focus();
			return false;
		}
		var handler_id = $.trim($('[name="handler_id"]').val());	
		if(handler_id == '')
		{
			$('[name="handler_id"]').focus();
			return false;
		}
		var summary = $.trim($('[name="summary"]').val());	
		if(summary == '')
		{
			$('[name="summary"]').focus();
			return false;
		}
		var description = $.trim($('[name="description"]').val());	
		if(description == '')
		{
			$('[name="description"]').focus();
			return false;
		}
		  
		data  = $("#toPopup1 :input").serialize();
		url = "plugin.php?page=WorkDairy/new_bug_report&ajax=1";
		$.when(send_data(url, data)).done(function(a1){	
			popup_content = $.trim(a1);
			if(popup_content != '')
			{
				alert(popup_content.replace(/<\/?[^>]+>/gi, ''));
				
			}				
			else
			{
				alert('Issue added successfully!');
				disablePopup();
				closeloading();
				window.location.href = 'plugin.php?page=WorkDairy/my_dairy';
			}			 
		});		 
		return false;
	});
	
	
	/* event for close the popup */
	$("div.close").hover(
		function() {
			$('span.ecs_tooltip').show();
		},
		function () {
			$('span.ecs_tooltip').hide();
		}
	);

	$("div.close").click(function() {
		disablePopup();  // function close pop up
	});

	$(this).keyup(function(event) {
		if (event.which == 27) { // 27 is 'Ecs' in the keyboard
			disablePopup();  // function close pop up
		}
	});

        $("div#backgroundPopup").click(function() {
		disablePopup();  // function close pop up
	});

	$('a.livebox').click(function() {
		alert('Hello World!');
		return false;
	});

	 /************** start: functions. **************/
	function loading() {
		$("div.loader").show();
	}
	function closeloading() {
		$("div.loader").fadeOut('normal');
	}

	var popupStatus = 0; // set value

	function loadPopup(id) {
		if(popupStatus == 0) { // if value is 0, show popup
			closeloading(); // fadeout loading
			$("#"+id).fadeIn(0500); // fadein popup div
			$("#backgroundPopup").css("opacity", "0.7"); // css opacity, supports IE7, IE8
			$("#backgroundPopup").fadeIn(0001);
			popupStatus = 1; // and set value to 1			
			var scrollTop = parseInt($(window).scrollTop()+40);
			$("#"+id).css("top", scrollTop);
		}
	}

	function disablePopup() {
		if(popupStatus == 1) { // if value is 1, close popup
			$(".toPopup").fadeOut("normal");
			$("#backgroundPopup").fadeOut("normal");
			popupStatus = 0;  // and set value to 0
		}
	}
	
	function send_data(url, data)
	{
		return $.ajax({
			type: 'POST',
			url: url,
			data: data,
			beforeSend:function(){
				loading(); 		 		
			},
			success:function(data){
				closeloading();
			},
			error:function(){
				closeloading();
				alert('Oops! Try that again in a few moments.');
			}
		});	
	}
	/************** end: functions. **************/
}); // jQuery End
 

function get_elapsed_time_string(total_seconds) {
  function pretty_time_string(num) {
    return ( num < 10 ? "0" : "" ) + num;
  }

  var hours = Math.floor(total_seconds / 3600);
  total_seconds = total_seconds % 3600;

  var minutes = Math.floor(total_seconds / 60);
  total_seconds = total_seconds % 60;

  var seconds = Math.floor(total_seconds);

  // Pad the minutes and seconds with leading zeros, if required
  hours = pretty_time_string(hours);
  minutes = pretty_time_string(minutes);
  seconds = pretty_time_string(seconds);

  // Compose the string for display
  var currentTimeString = hours + ":" + minutes + ":" + seconds;

  return "("+currentTimeString+")";
}