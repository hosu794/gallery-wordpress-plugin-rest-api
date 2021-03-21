<?php

/*
Plugin Name: Media Folder Grapql
Plugin URI:
Description: Grapql api with sorted medias by folder.
Author: Grzegorz Szczęsny
Author URI: intelligentprogrammer.com
Version: 0.1
*/



    add_action('rest_api_init', function () {
    register_rest_route( 'api/v1', 'images/(?P<image_id>\d+)',array(
                  'methods'  => 'GET',
                  'callback' => 'get_latest_posts_by_category'
        ));
  });

  function get_latest_posts_by_category($request) {

    $stuff = connectToDatabase($request['image_id']);

    $request['image_id'];

    $args = array(
      'category' => $request['image_id']
);

    $posts = get_posts($args);

    if (empty($stuff)) {

    return new WP_Error( 'empty_category', 'there is no post in this category', array('status' => 404) );

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
  $medias = connectToDatabase();

}

function connectToDatabase($folder_id) {
  $conn = new mysqli("localhost", "root", "", "wordpress");

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  // echo "Connected successfully <br />";

  $sql = "SELECT * FROM wp_realmedialibrary";

  $result = $conn->query($sql);

  if ($result->num_rows > 0) {

    // output data of each row
    while($row = $result->fetch_assoc()) {
      // echo "id:".$row["id"]. "name: ".$row["name"];
    }
  } else {
    echo "0 results";
  }

  $sql = "SELECT * FROM wp_realmedialibrary_posts WHERE fid = '$folder_id'";

  $result = $conn->query($sql);

  // echo "<br />";

  if ($result->num_rows > 0) {

        $rows = [];
        while($row = mysqli_fetch_array($result))
        {
            $rows[] = $row["attachment"];
        }

        foreach ($rows as &$value) {
          // echo $value."<br />";
             }

             $sql = 'SELECT *
          FROM `wp_posts`
         WHERE `id` IN (' . implode(',', array_map('intval', $rows)) . ')';

         $result = $conn->query($sql);

         if($result->num_rows > 0) {

          $attachments = [];
        while($row = mysqli_fetch_array($result))
        {
            $attachments[] = $row;
        }

        foreach ($attachments as &$value) {
              // echo $value["post_mime_type"]."<br />";
              // echo $value["guid"]."<br />";
        }

        return $attachments;

         } else {
          // echo "NULL";
         }


  } else {
    // echo "0 results";
  }

  $conn->close();

}

?>