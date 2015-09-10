
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
	  
$(function(){
      $('#main_place').load('/getmainplace.php?mode=get_all');
       

	     
});

function update_PM(){
      $.ajax({
          url: '/getmainplace.php',
          data: "mode=pm_data",
          success: function(data){
	    if (data) {
		if (data.contact2js) {
		    var $input = $('#EM_email_res');
		    $input.typeahead({
			source:data.contact2js, 
			autoSelect: true
		    }); 
		    $('#EM_email_res').change(function() {
		       var current =  $('#EM_email_res').typeahead("getActive");
		       if (current) {
			 $('#EM_email_res1').val(current.id);
		       }
		    });
		}
		$('#EM_New_message').html(data.new_message);
		$('#EM_PersonalMessages').html(data.ul);
		$('ul.dropdown-menu [data-toggle=dropdown]').on('click', function(event) {
			$('.dropdown-submenu').removeClass('open');
			event.preventDefault(); 
			event.stopPropagation(); 
			// If a menu is already open we close it
			//$('ul.dropdown-menu [data-toggle=dropdown]').parent().removeClass('open');
			// opening the one you clicked on
			$(this).parent().addClass('open');

			var menu = $(this).parent().find("ul");
			var menupos = menu.offset();

			if ((menupos.left + menu.width()) + 30 > $(window).width()) {
				var newpos = - menu.width();      
			} else {
				var newpos = $(this).parent().width();
			}
			menu.css({ left:newpos });

		});
	    }
	  },
          dataType: 'json'
       });
}


function createMessage(link){
    $('#EM_email_res1').val($(link).attr('contactId'));
    $('#EM_email_res').val($(link).attr('contactName'));
    $('#ModalComposeMail').modal('show');
    return false;
}

function reply_message(link){
    $('#EM_email_res1').val($(link).attr('contactId'));
    $('#EM_email_res').val($(link).attr('contactName'));
    $('#EM_email_title').val('RE: '+$(link).attr('messageTitle'));
    $('#ModalComposeMail').modal('show');
    return false;
}

function update_notepad(){
          $.ajax({
          url: '/getmainplace.php',
          data: "mode=notepad_data",
          success: function(data){
	    if (data) {
		$('#EM_NoteBook').html(data);
		$('ul.dropdown-menu [data-toggle=dropdown]').on('click', function(event) {
			$('.dropdown-submenu').removeClass('open');
			event.preventDefault(); 
			event.stopPropagation(); 
			// If a menu is already open we close it
			//$('ul.dropdown-menu [data-toggle=dropdown]').parent().removeClass('open');
			// opening the one you clicked on
			$(this).parent().addClass('open');

			var menu = $(this).parent().find("ul");
			var menupos = menu.offset();

			if ((menupos.left + menu.width()) + 30 > $(window).width()) {
				var newpos = - menu.width();      
			} else {
				var newpos = $(this).parent().width();
			}
			menu.css({ left:newpos });

		});
	    }
	  },
          dataType: 'json'
       });
}
	  



