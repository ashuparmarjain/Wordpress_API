<?php 


/*
Plugin Name: Wordpress_API
Plugin URI: http://dummyurl.com
Description: An example to create REST API.
Author: Ashutosh Parmar Jain
Version: 1
Author URI: www.ashuparmarjain.com
*/


/*


To Build Custom End Point, we need to follow three steps
1. Register a Route 
2. Write a Response Functionality
3. Add the custom route to the in-built wordpress rest api 

 
*/



// THE RESPONSE
function recent_posts_response($data) {
   
    $posts_per_page = 10;
   
    /***
    	if page attribut is not set
    		first 10 post will be returned
    	else
    		if page attribute is set lets say page=2 
    		than we need to show recent 10 post from the 11thpost i.e 11,12,13,14,15,16,17,18,19,20
    */ 
    if(isset($data['page'])){
        $offset = $posts_per_page * (intval($data['page'])-1);
    }  else {
        $offset = 0;
    }

    /***
	WP QUERY to get recent post 
    */
		$args = array(
		'post_type'	=> 'post',
		'posts_per_page' => 10,
		'offset'	=>  $offset,
		);			





	$query = new WP_Query($args);


	$posts = array();// we shall store all the post data in this array we would want to return when the API is called 


	//THE LOOP
	if($query->have_posts()){
		while($query->have_posts()){
			$query->the_post();

			$post = array();//An array to store the post data for an individual post


			// add data to post array			
			$post['id'] 				= get_the_ID(); 
			$post['title'] 				= get_the_title();
			$post['thumbnail_url']		= get_the_post_thumbnail_url();
			$post['link']				= get_permalink();



            // get first category name and add it to the array
			$post_category=get_the_terms( $post['id']); 
			$post_category=array_pop($post_category);
			$post['category'] = $post_category->name;	

			$posts[] = $post; // adding the single post data to posts array which will hold all the post data
		}
	}else{

		//no post 
	}
	if(empty($posts)){
		return null;
	}

	return $posts;
}

// REGISTER THE ROUTE 
function recent_post_route() {
    register_rest_route( 'my-api/v1', '/posts/', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'recent_posts_response',
        'args'	   => array(
        	// mechanism to pass page attricute along with url 
    		'page' => array(
    			'type' 		=>  'integer',
    			'default'   =>   1, // if page value is not passed then by default the page value be 1
    			'validate_callback' =>  function($v){ is_numeric($v);}	//to check if the page value passed is a number		
    		)
        )
    ) );
}
 
//ADD ACTION
add_action( 'rest_api_init', 'recent_post_route' ); //Register our custom end point api to inbuilt rest api.

