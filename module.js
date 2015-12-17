/**
 * @namespace
 */
M.mod_teletask = M.mod_teletask || {};

/**
 * This function is initialized from PHP
 *
 * @param {Object} Y YUI instance
 */
M.mod_teletask.init = function(Y, course) {
    
	var uploader = new plupload.Uploader({
		runtimes : 'html5,flash,silverlight,html4',
		browse_button : 'pickfiles', // you can pass an id...
		container: document.getElementById('container'), // ... or DOM Element itself
		url : '../mod/teletask/upload.php?id='+course,
		chunk_size: '200kb',
		flash_swf_url : '../mod/teletask/upload/Moxie.swf',
		silverlight_xap_url : '../mod/teletask/upload/Moxie.xap',
		
		filters : {
			max_file_size : '100000mb',
			mime_types: [
				{title : "teleTask-Moodle", extensions : "ttpp"}
			]
		},

		init: {
			PostInit: function() {
				document.getElementById('filelist').innerHTML = '';

				document.getElementById('uploadfiles').onclick = function() {
				
					// Generate a V1 TimeUUID
					var uuid1 = UUIDjs.create(1);
				
					uploader.files[0].name = uuid1.toString() + '.ttpp';
					uploader.start();
					return false;
				};
			},

			FilesAdded: function(up, files) {
			
				var maxfiles = 1;
                if(up.files.length > maxfiles )
                {
                    up.splice(maxfiles);
                    alert('no more than '+maxfiles + ' file(s)');
                }
                if (up.files.length === maxfiles) {
					// hide?
                }
				document.getElementById('filelist').innerHTML = '';
				plupload.each(files, function(file) {
					document.getElementById('filelist').innerHTML += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
				});
			},

			UploadProgress: function(up, file) {
				document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span> ' + M.util.get_string('videoisuploading', 'teletask') + ' ' + file.percent + "%</span>";
			},
			
			UploadComplete: function (up, files) {
			
				/*****unzipping************/
				//Gather containing files
				$.ajax({
					type: 'POST',
					url: "../mod/teletask/unzip.php",
					data: { fn: files[0].name, action: 'gather' },
					async: false,
					success: function( data ) {
						var lectureInfo = $.parseJSON(data);
						//Unzip all files one by one
						document.getElementById(files[0].id).getElementsByTagName('b')[0].innerHTML = '<span> '+ M.util.get_string('videoisextracting', 'teletask') + ' </span>';
						for (var i = 0, len = lectureInfo.files.length; i < len; i++) {
							$.ajax({
								type: 'POST',
								url: "../mod/teletask/unzip.php", 
								data: { fn: files[0].name, action: 'unzip', ufn: lectureInfo.files[i] },
								async: false,
								success: function( data ) {
									document.getElementById(files[0].id).getElementsByTagName('b')[0].innerHTML = '<span> ' + M.util.get_string('videoisextracting', 'teletask') + ' ' + (i+1) + ' '+ M.util.get_string('videoisextractingof', 'teletask') + ' ' + len + '</span>';
								}
							});							
						}
						
						//Set Meta Info from XML File
						document.getElementById('id_name').value = lectureInfo.lectureName;
						document.getElementById('id_description').value = lectureInfo.lectureDescription;
						document.getElementById('id_speaker').value = lectureInfo.lectureSpeaker;
						
						document.getElementById('id_date_day').value = lectureInfo.lectureDay;
						document.getElementById('id_date_month').value = lectureInfo.lectureMonth;
						document.getElementById('id_date_year').value = lectureInfo.lectureYear;
						
						//Set video if available
						for (var i = 0, len = lectureInfo.files.length; i < len; i++) {
							if(lectureInfo.files[i] == "CameraVideo.mp4")
							{
								document.getElementById('id_video_url_speaker').value = (files[0].name).replace(/\.[^/.]+$/, "") + "/" + lectureInfo.files[i];
							}
							if(lectureInfo.files[i] == "ScreenVideo.mp4")
							{
								document.getElementById('id_video_url_desktop').value = (files[0].name).replace(/\.[^/.]+$/, "") + "/" + lectureInfo.files[i];
							}
						}
						
						//Put ScreenVideo to speaker video if no speaker video is available
						if(document.getElementById('id_video_url_desktop').value && !document.getElementById('id_video_url_speaker').value){
							document.getElementById('id_video_url_speaker').value = document.getElementById('id_video_url_desktop').value;
							document.getElementById('id_video_url_desktop').value = "";
						}
						
						//Set Section Information
						//Remove Current Section
						$( "#video_sections" ).html("");
						for (var i = 0, len = lectureInfo.sections.length; i < len; i++) {
							$( "#video_sections" ).append('<div>'+ M.util.get_string('videosection', 'teletask') + ': <input type="text" name="sections[]" value="'+ lectureInfo.sections[i].name +'"> '+ M.util.get_string('videosectiontime', 'teletask') + ': <input type="text" name="sectiontimes[]" value="'+ lectureInfo.sections[i].time +'"> <a class="remove_section" style="cursor: pointer;">'+ M.util.get_string('videosectionremove', 'teletask') + '</a></div>');
						}
						
						//Visualize Sections
						$("#id_general").removeClass("collapsed");	
						$("#id_Sections").removeClass("collapsed");					
					}
				});
				
				//Remove archive
				$.ajax({
					type: 'POST',
					url: "../mod/teletask/unzip.php", 
					data: { fn: files[0].name, action: 'remove' },
					async: false,
					success: function( data ) {
						if(data == "success")
						{
							document.getElementById(files[0].id).getElementsByTagName('b')[0].innerHTML = M.util.get_string('videoarchiveisremoving', 'teletask');
						}
					}
				});		
				
				//Get Parsed XML Data and prefill fields
				
				
				document.getElementById(files[0].id).getElementsByTagName('b')[0].innerHTML = M.util.get_string('videouploadisdone', 'teletask');
			},

			Error: function(up, err) {
				alert("Error #" + err.code + ": " + err.message);
			}
		}
	});

	uploader.init();
	
	$( "#add_section" ).click(function() {
		$( "#video_sections" ).append('<div>'+ M.util.get_string('videosection', 'teletask') + ': <input type="text" name="sections[]"> '+ M.util.get_string('videosectiontime', 'teletask') + ': <input type="text" name="sectiontimes[]"> <a class="remove_section" style="cursor: pointer;">'+ M.util.get_string('videosectionremove', 'teletask') + '</a></div>');
	});
	$('#video_sections').on('click', '.remove_section', function(e) {
		$(this).parent().remove();
	});
	
}