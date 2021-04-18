<?php

/*
Plugin Name: Media Folder Grapql
Plugin URI:
Description: Grapql api with sorted medias by folder. NEED real-media-library-lite plugin to work!!!
Author: Grzegorz Szczęsny
Author URI: intelligentprogrammer.com
Version: 0.1
*/

    add_action('rest_api_init', function () {
    register_rest_route( 'api/v1', 'images/(?P<image_id>\d+)',array(
                  'methods'  => 'GET',
                  'callback' => 'get_latest_image_by_folder_id',
                  'args' => array(
                    'page' => array (
                        'required' => true
                    ),

                )
        ));
  });

  add_action('rest_api_init', function () {
    register_rest_route( 'api/v1', 'folders',array(
                  'methods'  => 'GET',
                  'callback' => 'get_folder_by_id',
        ));
  });

  function get_folder_by_id($request) {

    $folder_data = retrieve_folders_from_database();

    if(empty($folder_data)) {
      return new WP_Error( 'empty_folder', 'there is no folder!', array('status' => 404) );
    }

    $response = new WP_REST_Response($folder_data);
    $response->set_status(200);

    return $response;
  }

  function get_latest_image_by_folder_id($request) {

    $current_page = $request['page'];

    $stuff = retrieve_medias_from_database($request['image_id'], $current_page);

    $args = array(
      'category' => $request['image_id']
    );

    if (empty($stuff)) {

    return new WP_Error( 'empty_category', 'there is no images in this category', array('status' => 404) );

    }

    $response = new WP_REST_Response($stuff);
    $response->set_status(200);

    return $response;
}


add_action("admin_menu", "addMenu");
function addMenu()
{
  add_menu_page("Media Folder Grapql", "Media Folder Grapql", 4, "example-options", "exampleMenu" );
}

function exampleMenu()
{
echo <<< 'EOD'
  <h2> Plugin to integrate sorting images by file option.</h2>
EOD;
  $medias = retrieve_medias_from_database();

}

function retrieve_folders_from_database() {
  $conn = new mysqli("serwer2124775.home.pl", "34194846_strona", "ZAQ12wsx@#", "34194846_strona");

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT * FROM wp_realmedialibrary";

  $result = $conn->query($sql);

  if($result->num_rows > 0) {

    $attachments = [];

    while($row = mysqli_fetch_array($result))
    {
      $attachments[] = $row;
    }

    return $attachments;

  }

  $conn->close();

}

function retrieve_medias_from_database($folder_id, $current_page) {

  $conn = new mysqli("serwer2124775.home.pl", "34194846_strona", "ZAQ12wsx@#", "34194846_strona");;

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT * FROM wp_realmedialibrary_posts WHERE fid = '$folder_id'";

  $result = $conn->query($sql);
 
  $number_of_result = $result->num_rows;

  //First page
  $first_page = 0;

  //Records per page
  $no_of_records_per_page = 10;

  //Calculate a offset of current page
  $offset = (($current_page - 1) * $no_of_records_per_page) + 10;

  //Calculate a previous page.
  $prevPage = ($current_page - 1 <= 0) ? 0 : $current_page - 1;

  $sql = "SELECT * FROM wp_realmedialibrary_posts WHERE fid = '$folder_id' LIMIT $offset, $number_of_result";
  $result = $conn->query($sql);

  $database_data = array();

  //Calculate a total pages of images
  $total_pages = ceil($number_of_result / $no_of_records_per_page);

  //Calculate a next page. 
  $nextPage = ($current_page + 1 <= $total_pages) ? $total_pages : ($current_page + 1);

  //Calculate a hasMore 
  $hasMore = (($total_pages - 1) <= $current_page) ? false : true; 

  if ($result->num_rows > 0) {

        $rows = [];
        while($row = mysqli_fetch_array($result))
        {
            $rows[] = $row["attachment"];
        }

        $implode = implode(',', array_map('intval', $rows));

        foreach ($rows as &$value) {
          // echo $value."<br />";
             }

             $sql = 'SELECT *
          FROM `wp_posts`
         WHERE `id` IN (' . $implode . ")";

         $result = $conn->query($sql);

         if($result->num_rows > 0) {

          $mediasAttachments = [];
          while($row = mysqli_fetch_array($result))
          {
            $mediasAttachments[] = $row;
          }

          $folderResponse = [];
          $folderResponse['content'] = $mediasAttachments;
          $folderResponse['pageCount'] = 10;
          $folderResponse['nextPage'] = $nextPage;
          $folderResponse['prevPage'] = $prevPage;
          $folderResponse['currentPage'] = $current_page;
          $folderResponse['totalPages'] = $total_pages;
          $folderResponse['hasMore'] = $hasMore; 

         } else {
          $folderResponse = [];
          $folderResponse['content'] = $mediasAttachments;
          $folderResponse['pageCount'] = 10;
          $folderResponse['nextPage'] = $nextPage;
          $folderResponse['prevPage'] = $prevPage;
          $folderResponse['currentPage'] = $current_page;
          $folderResponse['totalPages'] = $total_pages;
          $folderResponse['hasMore'] = $hasMore; 

         }

         return $folderResponse;

  }

  $conn->close();

}

?>
