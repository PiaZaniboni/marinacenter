<?php

class fastmediagallery_function extends fastmediagallery_class {
	public function fastmediagallery_function_output($atts)
	{
		static $instance = 0;
		$instance++;
		extract(
			shortcode_atts(
				array(
					'name' 						=> '',
					'name_show' 				=> '',
					'columns' 					=> '1',
					'type' 						=> 'lightgallery', // FUTURE USE
					'layout' 					=> 'fmg-grid',
					'responsive_type' 			=> 'fg_responsive',
					'over_image' 				=> 'fg_over_image_on',
					'caption' 					=> 'off',
					'lazyload' 					=> 'off',
					'lazyload_effect' 			=> 'yes',
					'pagination_active' 		=> 'off',
					'pagination_number' 		=> '10',
																				
					'add_element' 				=> '',
					
					'style'						=> 'fg_style1',
					'main_color'				=> '#FC615D',
					'main_color_opacity'		=> '1',
					'secondary_color'			=> '#FFFFFF',
					'spacing_active'			=> 'off',
					'spacing'					=> '20',
					'gallery_name_font_size'	=> '20',
					'gallery_name_font_color'	=> '#FC615D',
					'gallery_name_text_align'	=> 'center',
					'pagination_style'			=> 'fg_pagination_style1',
					'image_width'				=> 'small',
					
					'image_lightbox_size'		=> 'fmg-default-lightbox',
					'image_thumb_size'			=> 'fmg-default-thumb',
					'thumbs_masonry'			=> 'fmg-default-thumb-masonry',

					'lg_mode' 					=> 'lg-slide',
					'lg_speed' 					=> '2000',
					'lg_thumbnail' 				=> 'true',
					'lg_controls' 				=> 'true',
					'lg_yt_modestbranding'		=> '1',
					'lg_yt_showinfo'			=> '1',
					'lg_yt_rel'					=> '1',
					'lg_yt_controls'			=> '1',
					'lg_vi_byline'				=> '1',
					'lg_vi_portrait'			=> '1',
					'lg_vi_title'				=> '1',
					'lg_vi_badge'				=> '1',
										
					'active_custom_responsive'	=> 'fg_responsive',
					'fg_smartphone_p_columns' 	=> '1',
					'fg_smartphone_l_columns' 	=> '1',
					'fg_tablet_p_columns' 		=> '1',
					'fg_tablet_l_columns' 		=> '1',
					'fg_desktop_medium_columns' => '1',
					'fg_desktop_small_columns' 	=> '1',
					
					'fg_animate' 				=> 'off',
					'fg_animate_effect' 		=> 'fade-in',
					'fg_delay' 					=> '0' 	
					
					
					), 
					$atts)
		);

			/* CHECK CUSTOM RESPONSIVE */
			if($responsive_type == 'fg_responsive') {
				if($active_custom_responsive == 'fg_responsive') {
					$responsive_type = 'fg_responsive';
				} else {
					$responsive_type = 'fg_smartphone_p_col-'.$fg_smartphone_p_columns.' fg_smartphone_l_col-'.$fg_smartphone_l_columns.' fg_tablet_p_col-'.$fg_tablet_p_columns.' fg_tablet_l_col-'.$fg_tablet_l_columns.' fg_desktop_medium_col-'.$fg_desktop_medium_columns.' fg_desktop_small_col-'.$fg_desktop_small_columns.'';				
				}	
			}

			// ANIMATION
			if($fg_animate == 'on') {
				$animation_info = " animate-in' data-anim-type='".$fg_animate_effect."' data-anim-delay='".$fg_delay."";			
			} else {
				$animation_info = "";					
			}
			// #ANIMATION




			
			// LOAD ALL STYLE AND CSS		
			fmg_enqueue_css_and_javascript($type,$layout,$responsive_type,$lazyload,$active_custom_responsive,$fg_animate);			
			
			// START MEDIA GALLERY OUTPUT
			
			$selector = "gallery-{$instance}";
			
			$output = fmg_style($selector,$main_color,$main_color_opacity,$secondary_color,$spacing_active,
					  $spacing,$name_show,$gallery_name_font_size,$gallery_name_font_color,
					  $gallery_name_text_align,$pagination_active,$pagination_style,$image_width);			
			
			/*************************************************** 
			#### GALLERY NAME SHOW OPTION ######################
			#### Since: version 1.0 ############################
			***************************************************/			
						
			$gallery_name = '';
						
			if($name_show == 'true') {
				$gallery_name = '<h2 class="fg_gallery_title-'.$selector.' fg_gallery_name">'.$name.'</h2>';	
			}			

			/*************************************************** 
			#### LIGHT GALLERY OPTION ##########################
			#### Since: version 1.0 ############################
			***************************************************/			

			$output .= '<script type="text/javascript">
						jQuery(function($){
							$(\'#'.$selector.'.fastgallery\').lightGallery({
								selector: \'.fmg-lightbox\',
								speed: '.$lg_speed.',
								thumbnail: '.$lg_thumbnail.',
								controls: '.$lg_controls.',
								youtubePlayerParams: {
									modestbranding: '.$lg_yt_modestbranding.',
									showinfo: '.$lg_yt_showinfo.',
									rel: '.$lg_yt_rel.',
									controls: '.$lg_yt_controls.'
								},
								vimeoPlayerParams: {
									byline : '.$lg_vi_byline.',
									portrait : '.$lg_vi_portrait.',
									title: '.$lg_vi_title.',
									badge: '.$lg_vi_badge.',     
								}																																
							});
						});		
					</script>';	
										
			$lazyload_class = '';
			
			$output_local = '';

				// LAZY LOAD GRID
				$lazyload_class = 'fg_lazyload_off';
				if($lazyload == 'on' && $layout == 'fmg-grid') {
					
					$output .= '<script type="text/javascript">
						jQuery(function($){
							$(\'#'.$selector.' .fg-gallery-item img.fg_lazy\').lazyload(';
							if($lazyload_effect == 'yes') {	
									 	
								$output .= '{
												effect: "fadeIn",
												effectspeed: 2000
											}';
							
							}							
					$output .= ');
						});		
					</script>';				
					$lazyload_class = 'fg_lazyload';
													
				}


				if($layout == 'fmg-masonry') {
					
					$image_thumb_size = $thumbs_masonry;
					if($lazyload == 'off') { // MASONRY WHEN LAZY LOAD IS OFF
											
						$output .= '<script type="text/javascript">
						jQuery(window).load(function() {
							jQuery(document).ready(function($){
								$(\'.fastgallery.brick-masonry\').masonry({
									singleMode: true,
									itemSelector: \'.fg-gallery-item\'
								});
							});
						});
						</script>';
						
					} else { // LAZY LOAD IS ON
					
						$output .= '<script type="text/javascript">
							jQuery(document).ready(function($){
						$("#'.$selector.' .fg-gallery-item img.fg_lazy").lazyload(';
							if($lazyload_effect == 'yes') {	
										
								$output .= '{
													effect: \'fadeIn\',
													effectspeed: 2000
													}';
							
							}
							
							$output .= ');

						$(\'#'.$selector.' .fg-gallery-item img.fg_lazy\').load(function() {
							masonry_update();
						});

						function masonry_update() {     
							var $works_list = $(\'#'.$selector.'\');
							$works_list.imagesLoaded(function(){
								$works_list.masonry({
									itemSelector: \'.fg-gallery-item\',　
								});
							});
						 }    
						 
						 
						});
						</script>';
						$lazyload_class = 'fg_lazyload';
						
					} #LAZY LOAD IS ON
				}		
							
			// OUTPUT
			$output .= "".$gallery_name."<div id='$selector' class='gallery galleryid-{$instance} gallery-columns-{$columns} fastgallery brick-masonry ".$responsive_type." ".$layout." ".$style." ".$lazyload_class." ".$over_image." ".$animation_info."'>";
			
	
			
			// LOAD ELEMENT
			$elements = json_decode(urldecode($add_element));

			// PAGINATION ### Change array for pagination ###
			if($pagination_active == 'on') {
				
					$pagination = get_query_var('fmg_page');		
					
					if(!isset($pagination) || empty($pagination)) { $pagination = 1; }
					
					$images_array = array_chunk($elements,$pagination_number);
													
					$pag = $pagination - 1;
					
					$elements = $images_array[$pag];				

					$num_page_for_pagination = count($images_array); // VALUE FOR PAGINATION FUNCTION

			}
			// #PAGINATION


			
			$counter = 1;

			foreach ( $elements as $element ) {
					
					
					$element_type = $element->element_type;		
					
					$element_caption_active = $element->element_caption_active;
					$element_caption = '';
					
					if($element_caption_active == 'on') {
						$element_caption = $element->element_caption;
					}
					
					// CHECK CAPTION
					$caption_check = '';
					if($caption == 'off' || empty($element_caption)) {
							$caption_check = 'no-caption';
					}
					// END CHECK CAPTION		
										
					if($element_type == 'image') { /* ELEMENT TYPE: IMAGE */
						
						$image_lightbox = wp_get_attachment_image_src($element->element_type_image,$image_lightbox_size);
						$url_lightbox = $image_lightbox[0];
						
						$image_thumb = wp_get_attachment_image_src($element->element_type_image,$image_thumb_size);
						$url_thumb = $image_thumb[0];	
											
							if($lazyload == 'on') {
								$img_src = '<img class="fg_lazy" data-original="'.$url_thumb.'" width="'.$image_thumb[1].'" height="'.$image_thumb[2].'">';
							} else {
								$img_src = '<img src="'.$url_thumb.'">';
							}
							
							$output .= '<div class="fg-gallery-item"><div class="fastgallery-gallery-icon '.$caption_check.'">
											<div class="fg_zoom">'.$img_src.'
												<a href="'.$url_lightbox.'" class="fmg-lightbox" data-sub-html="'.$element_caption.'">
													<span class="fg-zoom-icon icon-image"></span>
													<img src="'.$url_thumb.'" style="display:none">
												</a>
											</div>';
											
											if ($caption == 'on' && !empty($element_caption)) {
											
											$output .= '<div class="fg-wp-caption-text fg-gallery-caption">
												<div class="caption-container">
													'.$element_caption.'
												</div>
											</div>';
							
											}
							
							$output .= '</div></div>';	
			
					} elseif($element_type == 'video') { /* ELEMENT TYPE: VIDEO */
					
						wp_enqueue_script( 'fastmediagallery-lightgallery-js-vimeo');
						
						$element_type_image_poster = $element->element_poster_image_type;
						
						if($element_type_image_poster == 'custom') {
						
							$poster = wp_get_attachment_image_src($element->element_image_poster,$image_lightbox_size);
						
							$poster_url = $poster[0];
						
							$poster_thumb = wp_get_attachment_image_src($element->element_image_poster,$image_thumb_size);
							
							$poster_url_thumb = $poster_thumb[0];
						
						} else {
							
							$poster_url = $poster_url_thumb = VC_FMG_URL . 'assets/img/video_poster.jpg';
						
						}
						
						$url_type = $element->element_url_video_type;
						
						if($lazyload == 'on') {
							$img_src = '<img class="fg_lazy" data-original="'.$poster_url_thumb.'" width="'.$poster_thumb[1].'" height="'.$poster_thumb[2].'">';
						} else {
							$img_src = '<img src="'.$poster_url_thumb.'">';
						}	
												
						if($url_type == 'local') { // LOCAL
							
							$url = $element->element_type_local_video;
							
							$output_local .= '<div style="display:none;" id="fmg-video'.$counter.'">
								<video class="lg-video-object lg-html5" controls preload="none">
									<source src="'.$url.'" type="video/mp4">
									 Your browser does not support HTML5 video.
								</video>
							</div>';
							
							$output .= '<div class="fastgallery-gallery-icon '.$caption_check.' fg-gallery-item">
											<div class="fg_zoom">'.$img_src.'
												<a class="fmg-lightbox" data-sub-html="'.$element_caption.'" data-poster="'.$poster_url.'" data-html="#fmg-video'.$counter.'">
													<span class="fg-zoom-icon icon-youtube"></span>
													<img src="'.$poster_url_thumb.'" style="display:none">
												</a>
											</div>';
											
											if ($caption == 'on' && !empty($element_caption)) {
											
											$output .= '<div class="fg-wp-caption-text fg-gallery-caption">
												<div class="caption-container">
													'.$element_caption.'
												</div>
											</div>';
							
											}
							
							$output .= '</div>';							

						} else { /* #LOCAL */
							
							$url = $element->element_type_youtube_vimeo_video;

							$output .= '<div class="fastgallery-gallery-icon '.$caption_check.' fg-gallery-item">
											<div class="fg_zoom">'.$img_src.'
												<a href="'.$url.'" class="fmg-lightbox" data-sub-html="'.$element_caption.'" data-poster="'.$poster_url.'">
													<span class="fg-zoom-icon icon-youtube"></span>
													<img src="'.$poster_url_thumb.'" style="display:none">
												</a>
											</div>';
											
											if ($caption == 'on' && !empty($element_caption)) {
											
											$output .= '<div class="fg-wp-caption-text fg-gallery-caption">
												<div class="caption-container">
													'.$element_caption.'
												</div>
											</div>';
							
											}
							
							$output .= '</div>';

						}
						
						
						
						
					} elseif($element_type == 'audio') { /* ELEMENT TYPE: AUDIO */

						
						$element_type_image_poster = $element->element_poster_image_type;
						
						if($element_type_image_poster == 'custom') { // CUSTOM
						
							$poster = wp_get_attachment_image_src($element->element_image_poster,$image_lightbox_size);
						
							$poster_url = $poster[0];
						
							$poster_thumb = wp_get_attachment_image_src($element->element_image_poster,$image_thumb_size);
							
							$poster_url_thumb = $poster_thumb[0];
						
						} else { // #CUSTOM
							
							$poster_url = $poster_url_thumb = VC_FMG_URL . 'assets/img/audio_poster.jpg';
						
						} // #DEFAULT
						
						if($lazyload == 'on') {
							$img_src = '<img class="fg_lazy" data-original="'.$poster_url_thumb.'" width="'.$poster_thumb[1].'" height="'.$poster_thumb[2].'">';
						} else {
							$img_src = '<img src="'.$poster_url_thumb.'">';
						}
												
						$url_type = $element->element_url_audio_type;
						
						if($url_type == 'local') { // LOCAL
							
							$url = $element->element_type_local_audio;
							
							$output_local .= '<div style="display:none;" id="fmg-audio'.$counter.'">
								<audio class="lg-video-object lg-html5" controls preload="none">
									<source src="'.$url.'" type="audio/mp3">
									 Your browser does not support HTML5 video.
								</audio>
							</div>';
							
							$output .= '<div class="fastgallery-gallery-icon '.$caption_check.' fg-gallery-item">
											<div class="fg_zoom">'.$img_src.'
												<a class="fmg-lightbox" data-sub-html="'.$element_caption.'" data-poster="'.$poster_url.'" data-html="#fmg-audio'.$counter.'">
													<span class="fg-zoom-icon icon-headphones"></span>
													<img src="'.$poster_url_thumb.'" style="display:none">
												</a> 
											</div>';
											
											if ($caption == 'on' && !empty($element_caption)) {
											
											$output .= '<div class="fg-wp-caption-text fg-gallery-caption">
												<div class="caption-container">
													'.$element_caption.'
												</div>
											</div>';
							
											}
							
							$output .= '</div>';														
								
						} else { /* #LOCAL */
							
							$url = $element->element_type_soundcloud_audio;

							$output .= '<div class="fastgallery-gallery-icon '.$caption_check.' fg-gallery-item">
											<div class="fg_zoom">'.$img_src.'
												<a class="fmg-lightbox" data-sub-html="'.$element_caption.'" data-src="https://w.soundcloud.com/player/?url='.$url.'" data-iframe="true">
													<span class="fg-zoom-icon icon-headphones"></span>
													<img src="'.$poster_url_thumb.'" style="display:none">
												</a> 
											</div>';
											
											if ($caption == 'on' && !empty($element_caption)) {
											
											$output .= '<div class="fg-wp-caption-text fg-gallery-caption">
												<div class="caption-container">
													'.$element_caption.'
												</div>
											</div>';
							
											}
							
							$output .= '</div>';							
							
						} /* #SOUNDCLOUD */
						
									
					} elseif($element_type == 'iframe') { /* ELEMENT TYPE: IFRAME */

						$url = $element->element_type_iframe;
						$element_type_image_poster = $element->element_poster_image_type;
						
						if($element_type_image_poster == 'custom') {

							$poster_thumb = wp_get_attachment_image_src($element->element_image_poster,$image_thumb_size);
							
							$poster_url_thumb = $poster_thumb[0];
						
						} else {
							
							$poster_url_thumb = VC_FMG_URL . 'assets/img/iframe_poster.jpg';
						
						}
						
						if($lazyload == 'on') {
							$img_src = '<img class="fg_lazy" data-original="'.$poster_url_thumb.'" width="'.$poster_thumb[1].'" height="'.$poster_thumb[2].'">';
						} else {
							$img_src = '<img src="'.$poster_url_thumb.'">';
						}
						
							$output .= '<div class="fastgallery-gallery-icon '.$caption_check.' fg-gallery-item">
											<div class="fg_zoom">'.$img_src.'
												<a class="fmg-lightbox" data-sub-html="'.$element_caption.'" data-src="'.$url.'" data-iframe="true">
													<span class="fg-zoom-icon icon-file"></span>
													<img src="'.$poster_url_thumb.'" style="display:none">
												</a> 
											</div>';
											
											if ($caption == 'on' && !empty($element_caption)) {
											
											$output .= '<div class="fg-wp-caption-text fg-gallery-caption">
												<div class="caption-container">
													'.$element_caption.'
												</div>
											</div>';
							
											}
							
							$output .= '</div>';				
					
					} else { /* ELEMENT TYPE: IMAGE WITH CUSTOM URL */

							$image_thumb = wp_get_attachment_image_src($element->element_type_image_custom_url,$image_thumb_size);
							$url_thumb = $image_thumb[0];
						
							$custom_url = $element->element_custom_url;
							$custom_url_target = $element->element_custom_url_target;					

							if($lazyload == 'on') {
								$img_src = '<img class="fg_lazy" data-original="'.$url_thumb.'" width="'.$image_thumb[1].'" height="'.$image_thumb[2].'">';
							} else {
								$img_src = '<img src="'.$url_thumb.'">';
							}

							$output .= '<div class="fastgallery-gallery-icon '.$caption_check.' fg-gallery-item">
											<div class="fg_zoom">'.$img_src.'
												<a href="'.$custom_url.'" target="'.$custom_url_target.'">
													<span class="fg-zoom-icon icon-image"></span>
													<img src="'.$url_thumb.'" style="display:none">
												</a>
											</div>';
											
											if ($caption == 'on' && !empty($element_caption)) {
											
											$output .= '<div class="fg-wp-caption-text fg-gallery-caption">
												<div class="caption-container">
													'.$element_caption.'
												</div>
											</div>';
							
											}
							
							$output .= '</div>';						
						
					}
					

					
			
			$counter++;
			} // END FOREACH
			
			$output .= "</div>";

			if($pagination_active == 'on') {
				
				$output .= '<div id="'.$selector.'" class="fastgallery '.$pagination_style.'">'.get_fmg_pagination($num_page_for_pagination,$pagination).'</div>';
				
			}
			
			$output = $output_local . $output;
			
			return $output;
		} 
	}
new fastmediagallery_function();




/*************************************************** 
#### Function: fmg_enqueue_css_and_javascript() ####
#### Since: version 1.0 ############################
***************************************************/			

function fmg_enqueue_css_and_javascript($type,$layout,$responsive_type,$lazyload,$active_custom_responsive,$fg_animate) {
	
		// LOAD GENERAL CSS AND JAVASCRIPT
		wp_enqueue_script('fastmediagallery-frontend-script');
		wp_enqueue_style('fonts-vc');

		// CUSTOM RESPONSIVE: LOAD CSS
		if($active_custom_responsive == 'active_custom_responsive') {	
			wp_enqueue_style( 'fastmediagallery-custom-responsive-vc' );
		}

		// MASONRY: LOAD JAVASCRIPT 
		if($layout == 'fmg-masonry') {
			wp_enqueue_script('jquery-masonry');
		}
		
		// LIGHTGALLERY: LOAD CSS AND JAVASCRIPT
		if($type == 'lightgallery') {
			wp_enqueue_style( 'fastmediagallery-lightgallery' );
			wp_enqueue_script( 'fastmediagallery-lightgallery-js');					
		}

		if($fg_animate == 'on') {
			wp_enqueue_style( 'fastmediagallery-animations' );
			wp_enqueue_script( 'fastmediagallery-appear-js');
			wp_enqueue_script( 'fastmediagallery-animate-js');				
		}

		if($lazyload == 'on') {
			wp_enqueue_script( 'fastmediagallery-lazyload-js');
			wp_enqueue_script( 'fastmediagallery-imagesLoaded-js');	
		}

}

/*************************************************** 
#### Function: fmg_thumbnails() ####################
#### Since: version 1.0 ############################
***************************************************/

function fmg_thumbnails() {

	add_image_size( 'fmg-default-thumb', 1000, 800, true );
	add_image_size( 'fmg-default-thumb-masonry', 800 );
	add_image_size( 'fmg-default-lightbox', 1200, 1000, true );

}

add_action( 'init', 'fmg_thumbnails' );


/*************************************************** 
#### Function: fastmediagallery_vc_hex2rgb() #######
#### Since: version 1.0 ############################
***************************************************/

function fastmediagallery_vc_hex2rgb($hex) {

   $hex = str_replace("#", "", $hex);

if(strlen($hex) == 3) {
		$r = hexdec(substr($hex,0,1).substr($hex,0,1));
		$g = hexdec(substr($hex,1,1).substr($hex,1,1));
		$b = hexdec(substr($hex,2,1).substr($hex,2,1));
	} else {
		$r = hexdec(substr($hex,0,2));
		$g = hexdec(substr($hex,2,2));
		$b = hexdec(substr($hex,4,2));
	}
	$rgb = array($r, $g, $b);
	return $rgb;
}

/*************************************************** 
#### Function: fmg_add_query_vars_pagination() #####
#### Since: version 1.0 ############################
***************************************************/

function fmg_add_query_vars_pagination( $vars ){
  $vars[] = "fmg_page";
  return $vars;
}
add_filter( 'query_vars', 'fmg_add_query_vars_pagination' );

/*************************************************** 
#### Function: get_fmg_pagination() ################
#### Since: version 1.0 ############################
***************************************************/

function get_fmg_pagination($num_page_for_pagination,$pagination) {
	$output = '<ul class="fg_pagination">';
	for($i=1; $i <= $num_page_for_pagination; $i++) {
		
		if($i == $pagination) {
			$output .= '<li class="fg_current">'.$i.'</li>'; // CURRENT PAGE
		} else {
			$output .= '<li><a href="'.get_post_permalink().'&fmg_page='.$i.'">'.$i.'</a></li>'; // OTHER PAGE
		}
	}
	$output .= '</ul>';
	return $output;
}





/*************************************************** 
#### Function: fmg_style() #########################
#### Since: version 1.0 ############################
***************************************************/

function fmg_style($selector,
				   $main_color,
				   $main_color_opacity,
				   $secondary_color,
				   $spacing_active,
				   $spacing,
				   $name_show,
				   $gallery_name_font_size,
				   $gallery_name_font_color,
				   $gallery_name_text_align,
				   $pagination_active,
				   $pagination_style,
				   $image_width) {
					   
				// CHECK MAIN COLOR
				$rgb_main_color = fastmediagallery_vc_hex2rgb($main_color);
				$rgba_main_color = "rgba( ".$rgb_main_color[0]." , ".$rgb_main_color[1]." , ".$rgb_main_color[2]." , ".$main_color_opacity.")";	
				$rgb_secondary_color = fastmediagallery_vc_hex2rgb($secondary_color);
				$rgba_secondary_color = "rgba( ".$rgb_secondary_color[0]." , ".$rgb_secondary_color[1]." , ".$rgb_secondary_color[2]." , 0.3)";	
				// END MAIN COLOR	
			
				$output = "
				<style type='text/css'>
					#{$selector} {
					margin: auto;
					}
					#{$selector} .fg-gallery-item {
					margin-top: 10px;
					text-align: center;
					}
					#{$selector} .fg-gallery-caption {
					margin-left: 0;
					}
					#{$selector}.fastgallery .fg-gallery-caption, 
					#{$selector}.fastgallery .fg-gallery-caption:hover {
						background-color:".$rgba_main_color.";
					}
					#{$selector}.fastgallery.gallery .fastgallery-gallery-icon .fg_zoom a, 
					#{$selector}.fastgallery.gallery .fastgallery-gallery-icon .fg_zoom a:hover {
						color:".$main_color.";
					}
					#{$selector}.fastgallery.fg_style1 .fg-gallery-caption {
						color:".$secondary_color.";	
					}
					#{$selector}.fastgallery.gallery.fg_style2 .fastgallery-gallery-icon .fg_zoom a {
						background:".$rgba_secondary_color.";
					}
					#{$selector}.fastgallery.fg_style2 .fg-gallery-caption {
						color:".$secondary_color.";	
					}			
					#{$selector}.fastgallery.gallery.fg_style3 .fg_zoom, #{$selector}.fastgallery.gallery.fg_style3 .fg_zoom:hover {
						background:".$rgba_main_color.";
					}
					#{$selector}.fastgallery.fg_style3 .fg-gallery-caption {
						color:".$secondary_color.";	
					}				
					#{$selector}.fastgallery.fg_style4 .fg-gallery-caption,			
					#{$selector}.fastgallery.gallery.fg_style4 .fastgallery-gallery-icon .fg_zoom a, 
					#{$selector}.fastgallery.gallery.fg_style4 .fastgallery-gallery-icon .fg_zoom a:hover {
						color:".$secondary_color.";
					}
					#{$selector}.fastgallery.gallery.fg_style4 .fastgallery-gallery-icon .fg_zoom a, 
					#{$selector}.fastgallery.gallery.fg_style4 .fastgallery-gallery-icon .fg_zoom a:hover	{
						background:".$rgba_main_color.";
					}			
					#{$selector}.fastgallery.gallery.fg_style5 .fastgallery-gallery-icon .fg_zoom a, 
					#{$selector}.fastgallery.gallery.fg_style5 .fastgallery-gallery-icon .fg_zoom a:hover	{
						color:".$secondary_color.";
						background-color:".$rgba_main_color.";
					}					
					#{$selector}.fastgallery.gallery.fg_style6 .fastgallery-gallery-icon .fg_zoom a,
					#{$selector}.fastgallery.gallery.fg_style6 .fastgallery-gallery-icon .fg_zoom a:hover {
						color:".$secondary_color.";
						background:".$rgba_main_color.";				
					}
				
					#{$selector}.fastgallery.fg_style6 .fg-gallery-caption {
						color:".$secondary_color.";	
					}
					#{$selector}.fastgallery.gallery.fg_style7 .fastgallery-gallery-icon .fg_zoom a,
					#{$selector}.fastgallery.gallery.fg_style7 .fastgallery-gallery-icon .fg_zoom a:hover {
						color:".$secondary_color.";
						background:".$rgba_main_color.";				
					}		
					#{$selector}.fastgallery.fg_style7 .fg-gallery-caption {
						color:".$secondary_color.";	
					}
					
					#{$selector}.fastgallery.gallery.fg_style8 .fastgallery-gallery-icon .fg_zoom a,
					#{$selector}.fastgallery.gallery.fg_style8 .fastgallery-gallery-icon .fg_zoom a:hover {
						color:".$secondary_color.";
						background:".$rgba_main_color.";				
					}
				
					#{$selector}.fastgallery.fg_style8 .fg-gallery-caption {
						color:".$secondary_color.";	
					}
					
					#{$selector}.fastgallery.gallery.fg_style9 .fastgallery-gallery-icon .fg_zoom a,
					#{$selector}.fastgallery.gallery.fg_style9 .fastgallery-gallery-icon .fg_zoom a:hover {
						color:".$secondary_color.";
						background:".$rgba_main_color.";				
					}		
					#{$selector}.fastgallery.fg_style9 .fg-gallery-caption {
						color:".$secondary_color.";	
					}
					
					#{$selector}.fastgallery.gallery.fg_style10 .fastgallery-gallery-icon .fg_zoom a,
					#{$selector}.fastgallery.gallery.fg_style10 .fastgallery-gallery-icon .fg_zoom a:hover {
						color:".$secondary_color.";
						background:".$rgba_main_color.";				
					}		
					#{$selector}.fastgallery.fg_style10 .fg-gallery-caption {
						color:".$secondary_color.";	
					}
					#{$selector}.fastgallery.fg_style11 .fg-gallery-caption {
						color:".$secondary_color.";	
					}
					#{$selector}.fastgallery.fg_style11 .fastgallery-gallery-icon .fg_zoom a, 
					#{$selector}.fastgallery.fg_style11 .fastgallery-gallery-icon .fg_zoom a:hover {
						color:".$main_color.";
						background:".$rgba_secondary_color.";
					}
					#{$selector}.fastgallery.fg_style12 .fg-gallery-caption {
						color:".$secondary_color.";	
					}
					#{$selector}.fastgallery.fg_style12 .fastgallery-gallery-icon .fg_zoom a, 
					#{$selector}.fastgallery.fg_style12 .fastgallery-gallery-icon .fg_zoom a:hover {
						color:".$main_color.";
						background:".$rgba_secondary_color.";
					}";


				if($spacing_active == 'on') {
					$output .= "
						.fastgallery.gallery {
							width:100%;
							width: -webkit-calc(100% + ".$spacing."px);
							width: calc(100% + ".$spacing."px);
							/*margin-left:".$spacing."px;*/
						}
						.fastgallery .fg-gallery-item {
							margin-right:".$spacing."px!important;
							margin-bottom:".$spacing."px!important;
						}
						.fastgallery.gallery-columns-2 .fg-gallery-item {
							max-width: 48%;
							max-width: -webkit-calc(50% - ".$spacing."px);
							max-width:         calc(50% - ".$spacing."px);
						}
						
						.fastgallery.gallery-columns-3 .fg-gallery-item {
							max-width: 32%;
							max-width: -webkit-calc(33.3% - ".$spacing."px);
							max-width:         calc(33.3% - ".$spacing."px);
						}
						
						.fastgallery.gallery-columns-4 .fg-gallery-item {
							max-width: 23%;
							max-width: -webkit-calc(25% - ".$spacing."px);
							max-width:         calc(25% - ".$spacing."px);
						}
						
						.fastgallery.gallery-columns-5 .fg-gallery-item {
							max-width: 19%;
							max-width: -webkit-calc(20% - ".$spacing."px);
							max-width:         calc(20% - ".$spacing."px);
						}
						
						.fastgallery.gallery-columns-6 .fg-gallery-item {
							max-width: 15%;
							max-width: -webkit-calc(16.7% - ".$spacing."px);
							max-width:         calc(16.7% - ".$spacing."px);
						}
						
						.fastgallery.gallery-columns-7 .fg-gallery-item {
							max-width: 13%;
							max-width: -webkit-calc(14.28% - ".$spacing."px);
							max-width:         calc(14.28% - ".$spacing."px);
						}
						
						.fastgallery.gallery-columns-8 .fg-gallery-item {
							max-width: 11%;
							max-width: -webkit-calc(12.5% - ".$spacing."px);
							max-width:         calc(12.5% - ".$spacing."px);
						}
						
						.fastgallery.gallery-columns-9 .fg-gallery-item {
							max-width: 9%;
							max-width: -webkit-calc(11.1% - ".$spacing."px);
							max-width:         calc(11.1% - ".$spacing."px);
						}
					";
				}

				if($name_show == 'true') {
					$output .= ".fg_gallery_title-{$selector}.fg_gallery_name {
							font-size:".$gallery_name_font_size."px;
							color:".$gallery_name_font_color.";
							text-align:".$gallery_name_text_align.";
					}";
				}

				if($pagination_active == 'on') {
					$output .= "
						#{$selector}.fastgallery.fg_pagination_style1 ul.fg_pagination li a {
							background:".$rgba_main_color.";
							color:".$secondary_color.";
						}
						#{$selector}.fastgallery.fg_pagination_style1 ul.fg_pagination li a:hover {
							background:".$secondary_color.";
							color:".$rgba_main_color.";
						}
						#{$selector}.fastgallery.fg_pagination_style1 ul.fg_pagination li.fg_current {
							background:".$secondary_color.";
							color:".$rgba_main_color.";
						}
						#{$selector}.fastgallery.fg_pagination_style2 ul.fg_pagination li a {
							color:".$secondary_color.";
						}
						#{$selector}.fastgallery.fg_pagination_style2 ul.fg_pagination li a:hover {
							color:".$rgba_main_color.";
						}
						#{$selector}.fastgallery.fg_pagination_style2 ul.fg_pagination li.fg_current {
							color:".$rgba_main_color.";
						}																		
					";
				}

				if($image_width == 'small') {
					$output .= "#{$selector}.fastgallery.gallery span {
										font-size:20px!important;
					}
					#{$selector}.fastgallery.gallery .fg-zoom-icon {
										margin-left:-10px!important;
										margin-top:-40px;
					}
					#{$selector}.fastgallery.gallery.fg_style2 .fg-zoom-icon,
					#{$selector}.fastgallery.gallery.fg_style5 .fg-zoom-icon  {
										margin-top:-10px;
					}		
					#{$selector}.fastgallery.gallery .no-caption .fg-zoom-icon {
										margin-top:-10px!important;
					}
					#{$selector}.fastgallery.fg_style7 .fg-gallery-caption,
					#{$selector}.fastgallery.fg_style8 .fg-gallery-caption {
										top:55%;
					}
					";
				}
				
				if($image_width == 'medium') {
					$output .= "#{$selector}.fastgallery.gallery span {
										font-size:30px!important;
					}
					#{$selector}.fastgallery.gallery .fg-zoom-icon {
										margin-left:-15px!important;
										margin-top:-40px;
					}
					#{$selector}.fastgallery.gallery.fg_style2 .fg-zoom-icon,
					#{$selector}.fastgallery.gallery.fg_style5 .fg-zoom-icon  {
										margin-top:-15px;
					}		
					#{$selector}.fastgallery.gallery .no-caption .fg-zoom-icon {
										margin-top:-15px!important;
					}
					#{$selector}.fastgallery.fg_style7 .fg-gallery-caption,
					#{$selector}.fastgallery.fg_style8 .fg-gallery-caption {
										top:55%;
					}
					";
				}
		
				if($image_width == 'large') {
					$output .= "#{$selector}.fastgallery.gallery span {
										font-size:50px!important;
					}
					#{$selector}.fastgallery .fg-zoom-icon {
										margin-left:-25px!important;
										margin-top:-50px;
					}
					#{$selector}.fastgallery.fg_style1 .fg-zoom-icon,
					#{$selector}.fastgallery.fg_style2 .fg-zoom-icon,
					#{$selector}.fastgallery.fg_style5 .fg-zoom-icon {
										margin-top:-25px;
					}		
					#{$selector}.fastgallery .no-caption .fg-zoom-icon {
										margin-top:-25px!important;
					}
					#{$selector}.fastgallery.fg_style7 .fg-gallery-caption,
					#{$selector}.fastgallery.fg_style8 .fg-gallery-caption {
										top:55%;
					}
					";
				}						
					
			$output .= '</style>';				
	return $output;					
}

?>