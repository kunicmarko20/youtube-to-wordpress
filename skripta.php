<?php
	$key = "";
	require('wp-blog-header.php');
	$poslednjiklipovi = array();


	// Call file for channels
	$lines = file("kanali.txt");

	foreach($lines as $line){

		$kanal = explode ("=", $line);
		$skrati = trim ($kanal[0]);
		$skrati1 = trim ($kanal[1]);
			// call api and get recent videos
			$get = file_get_contents("https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=5&playlistId={$skrati1}&key={$key}");
			$get1 = file_get_contents('poslednji.json');  // get last video that you inserted to WP
			$l=0;
			$result = json_decode($get,true);
			$novo = array();
			
			
			
			
			$poslednji = json_decode($get1,true);

			
			echo "kanal : <strong>".$skrati."</strong> { <br/> ";
			
			
			foreach ($result['items'] as $post){ 
			
			if($post['snippet']['resourceId']['videoId'] != $poslednji[$skrati]){
				
					
					
					echo $post['snippet']['title']."<br/>";
					// collect what you need from youtube API
					$novo[$l][0]=$post['snippet']['resourceId']['videoId'];
					$novo[$l][1]=$post['snippet']['title'];
					$novo[$l][2]=$post['snippet']['description'];
					$novo[$l][3]=$post['snippet']['thumbnails']['default']['url'];
						
				$l++;
				
				$poslednjiklipovi[$skrati]= $novo[0][0];
				
				}
				else if ($l==0){$poslednjiklipovi[$skrati]= $poslednji[$skrati];break;} 
				else break;
		
			}

		echo "}<br/><br/>";
			
		//reverse array, if we don't do this newest video will be last 
		$reversed = array_reverse($novo);
		
		for($j=0; $j<count($reversed); $j++){
				
				$ime = $reversed[$j][1];
				$opis =  $reversed[$j][2];
				$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
				// if there is link in description make it a link
				if(preg_match($reg_exUrl, $opis, $url)) {
					$opis = preg_replace($reg_exUrl, '<a href="'.$url[0].'" rel="nofollow"  style="color:blue;"target="_blank">'.$url[0].'</a>', $opis);
				} 
				
				
				// insert post to WP
				
				
				
				$my_post = array(
	  'post_title'    => $ime,
	  'post_content'  => $opis,
	  'post_status'   => 'publish',
	  'post_author'   => 1,
	  'post_category' => array(2)
	);

				$post_id = wp_insert_post( $my_post, $wp_error ); 
		
			add_post_meta($post_id, 'youtube', $reversed[$j][0], true);
			add_post_meta($post_id, 'image', $reversed[$j][3], true);
			
		
		
	}

		
	}

	// add newest video for every channel to .json

	$json = json_encode($poslednjiklipovi); 
	file_put_contents('poslednji.json', $json);









?>