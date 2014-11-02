<script type="text/javascript" src="PERCH_LOGINPATH/addons/plugins/editors/redactor/redactor/redactor.min.js"></script>
<script type="text/javascript" src="PERCH_LOGINPATH/addons/plugins/editors/redactor-limiter/redactor/redactor.limiter.js"></script>
<script type="text/javascript" src="PERCH_LOGINPATH/addons/plugins/editors/redactor-limiter/redactor/redactor.counter.js"></script>
<link rel="stylesheet" href="PERCH_LOGINPATH/addons/plugins/editors/redactor/perch/edit.css" type="text/css" />
<link rel="stylesheet" href="PERCH_LOGINPATH/addons/plugins/editors/redactor/redactor/redactor.css" type="text/css" />
<script type="text/javascript" charset="utf-8"> 
$(function() {

	$('textarea.redactor-limiter').each(function(i,o){
		var self = $(o);

		// Grab the classes on this element
		var classes = $(this).attr('class').split(" "),
  		    charNumber = 999*999; // Just in case, though you should be using this if you're not going to use the limit fucntion

  		// Loop through classes
  		$.each(classes,function(key,value){
  		  
  		  if(value.indexOf('charlimit-') >= 0)
  		    {
  		      charNumber = value.split("-")[1]; 
  		    }
  		  
  		});

		self.wrap('<div class="editor-wrap" style="float:left;"></div>');
		self.redactor({
			plugins: ['limiter','counter'],
			imageUpload: 'PERCH_LOGINPATH/addons/plugins/editors/redactor-limiter/perch/upload.php?filetype=image',
			fileUpload: 'PERCH_LOGINPATH/addons/plugins/editors/redactor-limiter/perch/upload.php',
			uploadFields: 
			{
				'width'	 : 	self.attr('data-width')||'',
				'height' : 	self.attr('data-height')||'',
				'crop'	 : 	self.attr('data-crop')||'',
				'quality': 	self.attr('data-quality')||'',
				'sharpen': 	self.attr('data-sharpen')||'',
				'density': 	self.attr('data-density')||'',
				'bucket' : 	self.attr('data-bucket')||'default'
			},
			limiter: charNumber, // number of characters
            counterCallback: function(data)
            {
            	var availableChars = charNumber - data.characters;
            	self.siblings(".redactor-toolbar").find('.charCount').text("Remaining characters : " + availableChars);
            },
            initCallback: function()
    		{
    			self.siblings('.redactor-toolbar').append('<li class="charCount" style="padding: 9px!important">Remaining characters :</li>');
    		}
		});
	});
});
</script> 
