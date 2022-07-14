<?php

class fastmediagallery_class {
    function __construct() {
        // We safely integrate with VC with this hook
        add_action( 'init', array( $this, 'integrate_fastmediagallery_with_vc' ) );
 
        // Use this when creating a shortcode addon
        add_shortcode( 'vc_fastmediagallery', array( $this, 'fastmediagallery_function_output' ) );

        // Register CSS and JS
        add_action( 'wp_enqueue_scripts', array( $this, 'fastmediagallery_load_assets' ) );

    }
 
    public function integrate_fastmediagallery_with_vc() {
        if ( ! defined( 'WPB_VC_VERSION' ) ) {
            return;
        }
		
		include('animate-list.php');
		
        vc_map( array(
            "name" => __("Fast Media Gallery", 'fastmediagallery'),
            "description" => __("Create your media gallery", 'fastmediagallery'),
            "base" => "vc_fastmediagallery",
            "class" => "",
            "icon" => plugins_url('fastmediagallery/assets/img/fastmediagallery.png'),
            "category" => __('Fast Media Gallery', 'js_composer'),
            "params" => array(		
						
						  array(
							  "type" => "textfield",
							  "class" => "",
							  "heading" => __("Media Gallery Name",'fastmediagallery'),
							  "param_name" => "name",
							  "admin_label" => true,
						  ),
						  
						  array(
							  "type" => "dropdown",
							  "class" => "",
							  "heading" => __("Show Name", 'fastmediagallery'),
							  "param_name" => "name_show",
							  "value" => array(	
												'Hidden' => 'false',
												'Show' 	 => 'true',									
							   )
						  ),
						  	
						  array(
							  "type" => "dropdown",
							  "class" => "",
							  "heading" => __("Columns", 'fastmediagallery'),
							  "param_name" => "columns",
							  "value" => array(
												'1' => '1',
												'2' => '2',
												'3' => '3',
												'4' => '4',
												'5' => '5',
												'6' => '6',
												'7' => '7',
												'8' => '8',
												'9' => '9'
							   )
						  ),
						  
						  array(
							  "type" => "dropdown",
							  "class" => "",
							  "heading" => __("Masonry / Grid", 'fastmediagallery'),
							  "param_name" => "layout",
							  "value" => array(
												'Grid' => 'fmg-grid',
												'Masonry' => 'fmg-masonry'
							   )
						  ),	
						  	
						  array(
							  "type" => "dropdown",
							  "class" => "",
							  "heading" => __("Responsive / Fluid", 'fastmediagallery'),
							  "param_name" => "responsive_type",
							  "value" => array(
												'Responsive' 	=> 'fg_responsive',
												'Fluid' 		=> 'fg_fluid'
							   )
						  ),
						  
						  array(
							  "type" => "dropdown",
							  "class" => "",
							  "heading" => __("Over Image", 'fastmediagallery'),
							  "param_name" => "over_image",
							  "value" => array(
												'On' => 'fg_over_image_on',
												'Off' => 'fg_over_image_off'
							   )
						  ),	
						  		  			  
						  array(
							  "type" => "dropdown",
							  "class" => "",
							  "heading" => __("Show Caption Image", 'fastmediagallery'),
							  "param_name" => "caption",
							  "value" => array(
												'Off' => 'off',
												'On' => 'on'
							   )
						  ),		
						  					  
						  array(
							  "type" => "dropdown",
							  "class" => "",
							  "heading" => __("Lazy Load", 'fastmediagallery'),
							  "param_name" => "lazyload",
							  "value" => array(
												'Off' => 'off',
												'On'  => 'on'
							   )
						  ),
						  
						  array(
							  "type" => "dropdown",
							  "class" => "",
							  "heading" => __("Lazy Load Animation (Fade in Effect)", 'fastmediagallery'),
							  "param_name" => "lazyload_effect",
							  "value" => array(
												'Yes' => 'yes',
												'No'  => 'no'
							   ),
							  'dependency' => array(
									'element' => 'lazyload',
									'value' => array( 'on' )
							  ),				   
						  ),
						  
						  array(
							  "type" => "dropdown",
							  "class" => "",
							  "heading" => __("Pagination", 'fastmediagallery'),
							  "param_name" => "pagination_active",
							  "value" => array(
												'Off' => 'off',
												'On'  => 'on'
							   )
						  ),
						  
						  array(
							  "type" => "textfield",
							  "class" => "",
							  "heading" => __("Number of images for each page",'fastmediagallery'),
							  "param_name" => "pagination_number",
							  "value" => "10",
							  "description" => __("For example: 15 (Default value is 10)",'fastmediagallery'),
							  'dependency' => array(
									'element' => 'pagination_active',
									'value' => array( 'on' )
							  ),			  
						  ),		  			  							  
						  
						  	
							
						  /* MEDIA GALLERY ELEMENTS */ 	
										  			  
						  array(
						  	  'type' => 'param_group',
						      'heading' => __('Add Element'),
						      'param_name' => 'add_element',
						      'group' => 'Elements',	
						      'description' => __('Add and sort multiple elements'),
						      'params' => array(	
						
									array(
									  "type" => "textfield",
									  "class" => "",
									  "heading" => __('Label','fastmediagallery'),
									  "param_name" => "element_label_name",
									  "description" => __('Element Label Name','fastmediagallery'),
									  "admin_label" => true,			  
									  "group" => "element"				  			  				  
									),								
						
									array(
									  "type" => "dropdown",
									  "class" => "",
									  "heading" => __("Type", 'fastmediagallery'),
									  "param_name" => "element_type",
									  "value" => array(
									  					__('Image','fastmediagallery') 	=> 'image',
														__('Video','fastmediagallery')  => 'video',														
														__('Audio','fastmediagallery') 	=> 'audio',
														__('Iframe','fastmediagallery') => 'iframe',
														__('Custom URL (element without lightbox)','fastmediagallery') => 'custom_url',
									   ),			   			   
									),
									
									/* VIDEO TYPE */

									array(
									  "type" => "dropdown",
									  "class" => "",
									  "heading" => __("Video URL Type", 'fastmediagallery'),
									  "param_name" => "element_url_video_type",
									  "value" => array(
									  					__('Local','fastmediagallery') 		  	   => 'local',
														__('Youtube or Vimeo','fastmediagallery')  => 'youtube_vimeo'
									   ),	
									   "dependency" => array(
												'element' => 'element_type',
												'value' => array( 'video' )
									   ),				  
									   "group" => "element"									   		   			   
									 ),
											
									array(
									  "type" => "textfield",
									  "class" => "",
									  "heading" => __("Video URL",'fastmediagallery'),
									  "param_name" => "element_type_local_video",
									  "description" => __('ex http://yoursite.com/video.mp4','fastmediagallery'),
									  "dependency" => array(
												'element' => 'element_type',
												'value' => array( 'video' )
										),
									  "value"	=> ' ',					  
									  "group" => "element"				  			  				  
									),							

									array(
									  "type" => "textfield",
									  "class" => "",
									  "heading" => __("Youtube or Vimeo Video URL",'fastmediagallery'),
									  "param_name" => "element_type_youtube_vimeo_video",
									  "description" => __('ex https://www.youtube.com/watch?v=asQx7laC9Ww','fastmediagallery'),
									  "dependency" => array(
												'element' => 'element_type',
												'value' => array( 'video' )
										),
									  "value"	=> ' ',					  
									  "group" => "element"				  			  				  
									),	

									/* AUDIO TYPE */

									array(
									  "type" => "dropdown",
									  "class" => "",
									  "heading" => __("Audio URL Type", 'fastmediagallery'),
									  "param_name" => "element_url_audio_type",
									  "value" => array(
									  					__('Local','fastmediagallery') 		  => 'local',
														__('Sound Cloud','fastmediagallery')  => 'soundcloud'
									   ),	
									   "dependency" => array(
												'element' => 'element_type',
												'value' => array( 'audio' )
									   ),				  
									   "group" => "element"									   		   			   
									 ),
								
									array(
									  "type" => "textfield",
									  "class" => "",
									  "heading" => __("Audio URL Local",'fastmediagallery'),
									  "param_name" => "element_type_local_audio",
									  "description" => __('ex http://yoursite.com/sound.mp3','fastmediagallery'),
									  "dependency" => array(
												'element' => 'element_type',
												'value' => array( 'audio' )
										),
									  "value"	=> ' ',					  
									  "group" => "element"				  			  				  
									),						

									array(
									  "type" => "textfield",
									  "class" => "",
									  "heading" => __("Audio URL Sound Cloud",'fastmediagallery'),
									  "param_name" => "element_type_soundcloud_audio",
									  "description" => __('ex https://soundcloud.com/travisscott-2/wonderful-ftthe-weeknd','fastmediagallery'),
									  "dependency" => array(
												'element' => 'element_type',
												'value' => array( 'audio' )
										),
									  "value"	=> ' ',					  
									  "group" => "element"				  			  				  
									),

									/* IFRAME TYPE */

									array(
									  "type" => "textfield",
									  "class" => "",
									  "heading" => __("Iframe",'fastmediagallery'),
									  "param_name" => "element_type_iframe",
									  "description" => __('ex https://www.google.com','fastmediagallery'),
									  "dependency" => array(
												'element' => 'element_type',
												'value' => array( 'iframe' )
										),
									  "value"	=> ' ',					  
									  "group" => "element"				  			  				  
									),


									array(
									  "type" => "dropdown",
									  "class" => "",
									  "heading" => __("Image Poster Type", 'fastmediagallery'),
									  "param_name" => "element_poster_image_type",
									  "value" => array(
									  					__('Default','fastmediagallery') => 'default',
														__('Custom','fastmediagallery')  => 'custom'
									   ),	
									   "dependency" => array(
												'element' => 'element_type',
												'value' => array( 'video','audio','iframe' )
									   ),				  
									   "group" => "element"									   		   			   
									 ),

									 array(
										  "type" => "attach_image",
										  "class" => "",
										  "heading" => __("Image Poster Custom", 'fastmediagallery'),
										  "param_name" => "element_image_poster",
										  "description" => __('If you get image from library (not uploaded) we reccomended you to regenerate your thumbs','fastmediagallery'),
										  "dependency" => array(
												'element' => 'element_type',
												'value' => array( 'video','audio','iframe' )
										  ),				  
										  "group" => "element"								  
									 ),
								

		
									/* IMAGE TYPE */
		
									 array(
										  "type" => "attach_image",
										  "class" => "",
										  "heading" => __("Upload Your Image", 'fastmediagallery'),
										  "param_name" => "element_type_image",
										  "description" => __('If you get image from library (not uploaded) we reccomended you to regenerate your thumbs','fastmediagallery'),
											"dependency" => array(
												'element' => 'element_type',
												'value' => array( 'image' )
											),				  
										"group" => "element"								  
									 ),	

									 array(
										  "type" => "attach_image",
										  "class" => "",
										  "heading" => __("Upload Your Image", 'fastmediagallery'),
										  "param_name" => "element_type_image_custom_url",
										  "description" => __('If you get image from library (not uploaded) we reccomended you to regenerate your thumbs','fastmediagallery'),
											"dependency" => array(
												'element' => 'element_type',
												'value' => array( 'custom_url' )
											),				  
										"group" => "element"								  
									 ),	

									array(
									  "type" => "textfield",
									  "class" => "",
									  "heading" => __("Custom URL",'fastmediagallery'),
									  "param_name" => "element_custom_url",
									  "description" => __('ex http://yoursite.com/','fastmediagallery'),
									  "dependency" => array(
												'element' => 'element_type',
												'value' => array( 'custom_url' )
										),
									  "value"	=> ' ',					  
									  "group" => "element"				  			  				  
									), 

									array(
									  "type" => "dropdown",
									  "class" => "",
									  "heading" => __("Custom URL Target", 'fastmediagallery'),
									  "param_name" => "element_custom_url_target",
									  "value" => array(
									  					__('Blank (new window)','fastmediagallery') 	=> '_blank',
														__('Self (Same window)','fastmediagallery')  	=> '_self',
									   ),
									  "dependency" => array(
												'element' => 'element_type',
												'value' => array( 'custom_url' )
										),										   			   			   
									),		


									array(
									  "type" => "dropdown",
									  "class" => "",
									  "heading" => __("Caption", 'fastmediagallery'),
									  "param_name" => "element_caption_active",
									  "value" => array(
									  					__('Off','fastmediagallery') 	=> 'off',
														__('On','fastmediagallery')  	=> 'on',
									   ),										   			   			   
									),	

									array(
									  "type" => "textfield",
									  "class" => "",
									  "heading" => __("Caption Text",'fastmediagallery'),
									  "param_name" => "element_caption",
									  "dependency" => array(
												'element' => 'element_caption_active',
												'value' => array( 'on' )
										),
									  "value"	=> ' ',					  
									  "group" => "element"				  			  				  
									), 
														
								  ),	   
							),	/* #MEDIA GALLERY ELEMENTS */

							  /* STYLE */ 
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Style", 'fastmediagallery'),
								  "param_name" => "style",
								  "value" => array(
													'Style 1' => 'fg_style1',
													'Style 2' => 'fg_style2',
													'Style 3' => 'fg_style3',
													'Style 4' => 'fg_style4',
													'Style 5' => 'fg_style5',
													'Style 6' => 'fg_style6',
													'Style 7' => 'fg_style7',
													'Style 8' => 'fg_style8',
													'Style 9' => 'fg_style9',
													'Style 10' => 'fg_style10',
													'Style 11' => 'fg_style11',
													'Style 12' => 'fg_style12',
													'No Style' => 'fg_no_style',																		
								   ),
								  "group" => "Style",
							  ),			  			  	  			  	
							  array(
								  "type" => "colorpicker",
								  "class" => "",
								  "heading" => __("Main Color", 'fastmediagallery'),
								  "param_name" => "main_color",
								  "value" => '#FC615D',
								  "group" => "Style",
							  ),
							  array(
								  "type" => "textfield",
								  "class" => "",
								  "heading" => __("Opacity",'fastmediagallery'),
								  "param_name" => "main_color_opacity",
								  "value" => "1",
								  "description" => __("Main Color Opacity (0.1 to 1)",'fastmediagallery'),
								  "group" => "Style",
							  ),			  
							  array(
								  "type" => "colorpicker",
								  "class" => "",
								  "heading" => __("Secondary Color", 'fastmediagallery'),
								  "param_name" => "secondary_color",
								  "value" => '#FFFFFF',
								  "group" => "Style",
							  ),	
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Custom spacing between the photos", 'fastmediagallery'),
								  "param_name" => "spacing_active",
								  "value" => array(
													'Off' => 'off',
													'On' => 'on'																	
								   ),
								   "group" => "Style",			   				   
							  ),
							  array(
								  "type" => "textfield",
								  "class" => "",
								  "heading" => __("spacing between the photos",'fastmediagallery'),
								  "param_name" => "spacing",
								  "value" => "20",
								  "description" => __("Enter pixel value (examples: 20)",'fastmediagallery'),
								  'dependency' => array(
										'element' => 'spacing_active',
										'value' => array( 'on' )
								  ),
								  "group" => "Style",
							  ),			  			  		  
							  array(
								  "type" => "textfield",
								  "class" => "",
								  "heading" => __("Gallery Name Font Size",'fastmediagallery'),
								  "param_name" => "gallery_name_font_size",
								  "value" => "20",
								  "description" => __("Enter pixel value (examples: 20)",'fastmediagallery'),
								  'dependency' => array(
										'element' => 'name_show',
										'value' => array( 'true' )
								  ),
								  "group" => "Style",
							  ),
							  array(
								  "type" => "colorpicker",
								  "class" => "",
								  "heading" => __("Gallery Name Font Color", 'fastmediagallery'),
								  "param_name" => "gallery_name_font_color",
								  "value" => '#FC615D',
								  'dependency' => array(
										'element' => 'name_show',
										'value' => array( 'true' )
								  ),
								  "group" => "Style",
							  ),
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Gallery Name Text Align", 'fastmediagallery'),
								  "param_name" => "gallery_name_text_align",
								  "value" => array(
													'Center' => 'center',
													'Left' => 'left',
													'Right' => 'right'																	
								   ),
								  'dependency' => array(
										'element' => 'name_show',
										'value' => array( 'true' )
								  ),
								  "group" => "Style",
							  ),
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Pagination Style", 'fastmediagallery'),
								  "param_name" => "pagination_style",
								  "value" => array(
													'Style 1'  => 'fg_pagination_style1',
													'Style 2'  => 'fg_pagination_style2'
								   ),
								  'dependency' => array(
										'element' => 'pagination_active',
										'value' => array( 'on' )
								  ),	
								  "group" => "Style",			   
							  ),				
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Icon Image Lightbox Width", 'fastmediagallery'),
								  "param_name" => "image_width",
								  "value" => array(
													'Small (Default)' => 'small',
													'Medium' => 'medium',
													'Large' => 'large'																	
								   ),
								  "group" => "Style",
							  ),		
							  			
							/* OPTIONS */
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Image Thumbnail Size", 'fastmediagallery'),
								  "param_name" => "image_thumb_size",
								  "value" => array(
													'Default (1000px cropped)' 	=> 'fmg-default-thumb',
													'Thumbnail' 				=> 'thumbnail',
													'Medium' 					=> 'medium',
													'Large' 					=> 'large',
													'Full' 						=> 'full',
								  ),
								  'dependency' => array(
										'element' => 'layout',
										'value' => array( 'fmg-grid' )
								  ),								  				   
								  "group" => "Options",
							  	),
								
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Masonry Thumbnails Size", 'fastmediagallery'),
								  "param_name" => "thumbs_masonry",
								  "value" => array(
													'Default (800px)' => 'fmg-default-thumb-masonry',
													'Thumbnail' => 'thumbnail',
													'Medium' => 'medium',
													'Large' => 'large',
													'Full' => 'full'
								   ),
								  'dependency' => array(
										'element' => 'layout',
										'value' => array( 'fmg-masonry' )
								  ),
								  "group" => "Options",
							  )	,		
			  													
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Image Lightbox Size", 'fastmediagallery'),
								  "param_name" => "image_lightbox_size",
								  "value" => array(
													'Default (1200px cropped)' 	=> 'fmg-default-lightbox',
													'Thumbnail' 				=> 'thumbnail',
													'Medium' 					=> 'medium',
													'Large' 					=> 'large',
													'Full' 						=> 'full',
								  ),				   
								  "group" => "Options",
							  	),							

							/* LIGHT GALLERY */

							  array(
								  "type" => "textfield",
								  "class" => "",
								  "heading" => __("Speed",'fastmediagallery'),
								  "param_name" => "lg_speed",
								  "value" => "2000",
								  "description" => __("time in ms (1000 = 1sec). Example: 2000",'fastmediagallery'),
								  "group" => "Light Gallery",
							  ),			  			  			  
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Thumbnail", 'fastmediagallery'),
								  "param_name" => "lg_thumbnail",
								  "value" => array(
													'On' => 'true',
													'Off' => 'false'																		
								   ),
								  "group" => "Light Gallery",				   
							  ),
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Controls", 'fastmediagallery'),
								  "param_name" => "lg_controls",
								  "value" => array(
													'On' => 'true',
													'Off' => 'false'																		
								   ),
								  "group" => "Light Gallery",				   
							  ),		  


							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Youtube Modest branding", 'fastmediagallery'),
								  "param_name" => "lg_yt_modestbranding",
								  "value" => array(
													'On' => '1',
													'Off' => '0'																		
								   ),
								  "group" => "Light Gallery",				   
							  ),
							  
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Youtube Show Info", 'fastmediagallery'),
								  "param_name" => "lg_yt_showinfo",
								  "value" => array(
													'On' => '1',
													'Off' => '0'																		
								   ),
								  "group" => "Light Gallery",				   
							  ),
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Youtube Rel", 'fastmediagallery'),
								  "param_name" => "lg_yt_rel",
								  "value" => array(
													'On' => '1',
													'Off' => '0'																		
								   ),
								  "group" => "Light Gallery",				   
							  ),
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Youtube Controls", 'fastmediagallery'),
								  "param_name" => "lg_yt_controls",
								  "value" => array(
													'On' => '1',
													'Off' => '0'																		
								   ),
								  "group" => "Light Gallery",				   
							  ),							  

							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Vimeo Byline", 'fastmediagallery'),
								  "description" => __("Show the user’s byline on the video",'fastmediagallery'),
								  "param_name" => "lg_vi_byline",
								  "value" => array(
													'On' => '1',
													'Off' => '0'																		
								   ),
								  "group" => "Light Gallery",				   
							  ),
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Vimeo Portrait", 'fastmediagallery'),
								  "description" => __("Show the user’s portrait on the video",'fastmediagallery'),
								  "param_name" => "lg_vi_portrait",
								  "value" => array(
													'On' => '1',
													'Off' => '0'																		
								   ),
								  "group" => "Light Gallery",				   
							  ),
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Vimeo Title", 'fastmediagallery'),
								  "description" => __("Show the title on the video",'fastmediagallery'),
								  "param_name" => "lg_vi_title",
								  "value" => array(
													'On' => '1',
													'Off' => '0'																		
								   ),
								  "group" => "Light Gallery",				   
							  ),
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Vimeo Badge", 'fastmediagallery'),
								  "description" => __("Enables or disables the badge on the video",'fastmediagallery'),
								  "param_name" => "lg_vi_badge",
								  "value" => array(
													'On' => '1',
													'Off' => '0'																		
								   ),
								  "group" => "Light Gallery",				   
							  ),
							  							  							  							
							/* MOBILE COLUMNS */
							
							array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Active Custom Responsive", 'fastmediagallery'),
								  "param_name" => "active_custom_responsive",
								  "value" => array(
													'Default Value' 			=> 'fg_responsive',
													'Active Custom Responsive' 	=> 'active_custom_responsive',
								  ),
								  'dependency' => array(
										'element' => 'responsive_type',
										'value' => array( 'fg_responsive' )
								  ),				   
								  "group" => "Responsive",
							  ),			  
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Smartphone Portrait", 'fastmediagallery'),
								  "param_name" => "fg_smartphone_p_columns",
								  "value" => array(
													'1' => '1',
													'2' => '2',
													'3' => '3',
													'4' => '4',
													'5' => '5',
													'6' => '6',
													'7' => '7',
													'8' => '8',
													'9' => '9'
								  ),
								  'dependency' => array(
										'element' => 'active_custom_responsive',
										'value' => array( 'active_custom_responsive' )
								  ),				   
								  "group" => "Responsive",
							  	),
							  	array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Smartphone Landscape", 'fastmediagallery'),
								  "param_name" => "fg_smartphone_l_columns",
								  "value" => array(
													'1' => '1',
													'2' => '2',
													'3' => '3',
													'4' => '4',
													'5' => '5',
													'6' => '6',
													'7' => '7',
													'8' => '8',
													'9' => '9'
								  	),
								  	'dependency' => array(
										'element' => 'active_custom_responsive',
										'value' => array( 'active_custom_responsive' )
								  	),				   
								  	"group" => "Responsive",
							  		),
							  	array(
								   "type" => "dropdown",
								   "class" => "",
								   "heading" => __("Tablet Portrait", 'fastmediagallery'),
								   "param_name" => "fg_tablet_p_columns",
								   "value" => array(
													'1' => '1',
													'2' => '2',
													'3' => '3',
													'4' => '4',
													'5' => '5',
													'6' => '6',
													'7' => '7',
													'8' => '8',
													'9' => '9'
								   ),
								   'dependency' => array(
										'element' => 'active_custom_responsive',
										'value' => array( 'active_custom_responsive' )
								   ),				   
								   "group" => "Responsive",
							  	),			  	
							  	array(
								   "type" => "dropdown",
								   "class" => "",
								   "heading" => __("Tablet Landscape", 'fastmediagallery'),
								   "param_name" => "fg_tablet_l_columns",
								   "value" => array(
													'1' => '1',
													'2' => '2',
													'3' => '3',
													'4' => '4',
													'5' => '5',
													'6' => '6',
													'7' => '7',
													'8' => '8',
													'9' => '9'
								   ),
								   'dependency' => array(
										'element' => 'active_custom_responsive',
										'value' => array( 'active_custom_responsive' )
								   ),				   
								   "group" => "Responsive",
							  	),
							  	array(
								   "type" => "dropdown",
								   "class" => "",
								   "heading" => __("Desktop (min value 640px - max value 1024px)", 'fastmediagallery'),
								   "param_name" => "fg_desktop_medium_columns",
								   "value" => array(
													'1' => '1',
													'2' => '2',
													'3' => '3',
													'4' => '4',
													'5' => '5',
													'6' => '6',
													'7' => '7',
													'8' => '8',
													'9' => '9'
								   ),
								   'dependency' => array(
										'element' => 'active_custom_responsive',
										'value' => array( 'active_custom_responsive' )
								   ),				   
								   "group" => "Responsive",
							  	),
							  	array(
								   "type" => "dropdown",
								   "class" => "",
								   "heading" => __("Desktop (max 639px)", 'fastmediagallery'),
								   "param_name" => "fg_desktop_small_columns",
								   "value" => array(
													'1' => '1',
													'2' => '2',
													'3' => '3',
													'4' => '4',
													'5' => '5',
													'6' => '6',
													'7' => '7',
													'8' => '8',
													'9' => '9'
								   ),
								   'dependency' => array(
										'element' => 'active_custom_responsive',
										'value' => array( 'active_custom_responsive' )
								   ),				   
								   "group" => "Responsive",
							  	),						
						
							  /* ANIMATION */
																																						  
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Animate",'fastmediagallery'),
								  "param_name" => "fg_animate",
								  "value" => array(
													__('Off','vcmegapack') 	  => 'off',
													__('On','vcmegapack') => 'on'
													
								   ),
								   "group" => "Animation"
							  ),			  
							  array(
								  "type" => "dropdown",
								  "class" => "",
								  "heading" => __("Animate Effects",'fastmediagallery'),
								  "param_name" => "fg_animate_effect",
								  "value" => $animate_effect,
								  'dependency' => array(
										'element' => 'fg_animate',
										'value' => array( 'on' )
								  ),
								  "group" => "Animation"				   
							  ),				  
							  array(
								  "type" => "textfield",
								  "class" => "",
								  "heading" => __("Animate Delay (ms)",'fastmediagallery'),
								  "param_name" => "fg_delay",
								  "description" => "ex 1000",					  
								  'dependency' => array(
										'element' => 'fg_animate',
										'value' => array( 'on' )
								  ),
								  "value" => "0",
								  "group" => "Animation"					  
							  ),			    										  		  			  
			)  
		)		  			  			  			  			  			  
	  );
    }	
	
    public function fastmediagallery_load_assets() {

		// Load WP jQuery if not included
		wp_enqueue_script('jquery');
		
		// Load main js script
		wp_register_script('fastmediagallery-frontend-script', VC_FMG_URL . 'assets/js/frontend.js', array('jquery'), '', true);
		wp_register_style( 'fastmediagallery-vc-main-style',  VC_FMG_URL . 'assets/css/style.css' );
		wp_enqueue_style('fastmediagallery-vc-main-style');
		wp_register_style( 'fonts-vc',  VC_FMG_URL . 'assets/css/fonts.css' ); 
		wp_register_style( 'fastmediagallery-custom-responsive-vc',  VC_FMG_URL . 'assets/css/custom_responsive.css' );		

		// LIGHT GALLERY
		wp_register_style( 'fastmediagallery-lightgallery',  VC_FMG_URL . 'assets/css/lightGallery.css' );
		wp_register_script( 'fastmediagallery-lightgallery-js',  VC_FMG_URL . 'assets/js/lightGallery.min.js' , array('jquery'), '', true);	
		wp_register_script( 'fastmediagallery-lightgallery-js-vimeo', VC_FMG_URL . 'assets/js/froogaloop2.min.js');
		
		// ANIMATE
		wp_register_style( 'fastmediagallery-animations',  VC_FMG_URL . 'assets/css/animations.min.css' );
		wp_register_script( 'fastmediagallery-appear-js',  VC_FMG_URL . 'assets/js/appear.min.js' , array('jquery'), '', true);				
		wp_register_script( 'fastmediagallery-animate-js',  VC_FMG_URL . 'assets/js/animations.min.js' , array('jquery'), '', true);		

		// LAZY LOAD EFFECT
		wp_register_script( 'fastmediagallery-lazyload-js',  VC_FMG_URL . 'assets/js/jquery.lazyload.js' , array('jquery'), '', true);				
		wp_register_script( 'fastmediagallery-imagesLoaded-js',  VC_FMG_URL . 'assets/js/imagesLoaded.js' , array('jquery'), '', true);	
							
    }	
}
// Finally initialize code
new fastmediagallery_class();

?>