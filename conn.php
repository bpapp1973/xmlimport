<?php
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    require_once( ABSPATH . 'wp-includes/taxonomy.php' );
    require_once( ABSPATH . 'wp-includes/post.php' );
    require_once( ABSPATH . 'wp-includes/category.php' );

function conn() {
    $servername = "localhost";
    //$username = "intermot";
    //$password = "Dkw1Zj849c";
    //$schema   = "intermot_wp2";
    //$username = "bpapp";
    //$password = "Pa55wd123!";
    //$schema   = "wordpress";
    $username = "cimkerak";
    $password = "1Ktn87pu7W";
    $schema   = "cimkerak_wp5";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$schema", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //echo "Connected successfully"; 
        }
    catch(PDOException $e)
        {
        echo "Connection failed: " . $e->getMessage();
        }
    return $conn;
}

function getXMLlist() {
    $conn=conn();
	$result = null;
	$i=0;
    $sql = 'SELECT source_id, name, url, last_import FROM wp_xmlimport_sources ORDER BY source_id';
    foreach ($conn->query($sql) as $row) {
        $result[$i]["source_id"] = $row['source_id'];
        $result[$i]["name"] = $row['name'];
        $result[$i]["url"] = $row['url'];
        $result[$i]["last_import"] = $row['last_import'];
        $i++;
    }
    $conn=null;
    return $result;
}

function touchSource($name) {
    $conn=conn();
    $sql='UPDATE wp_xmlimport_sources SET last_import=NOW() WHERE name="'.$name.'"';
    if ($conn->query($sql) == TRUE) {
        echo "Sikeres import " . $name;
    } else {
        echo "Hiba conn.touchSource: " . $sql . "<br>" . $conn->error;
    }

    $conn=null;
}

function insertCategories($categoryString, $post_id) {
    //echo 'insertCategories '.$categoryString.' start<br/>';
    $categories=explode(" / ", $categoryString);
    $post_categories=array();
    $parent=0;
    $i=0;
    foreach ($categories as $category) {
        $cat=get_term_by( 'name', $category, 'product_cat' );
        $id=$cat->term_id;
        
        if ($id==false) {
            $catarr = array(
                'cat_name' => $category,
                'category_description' => $category,
                'category_nicename' => $category,
                'category_parent' => $parent,
                'taxonomy' => 'product_cat' );
            $term=wp_insert_term($category,'product_cat', ['parent' => $parent , 'description'=> $category, 'slug' => createSlug($category) ]);
            $id=$term['term_id'];
        }
        array_push($post_categories, $id);
        $parent=$id;
    }
    $product = wc_get_product($post_id);
    $product->set_category_ids($post_categories);
    $product->save();
    //echo 'insertCategories '.$categoryString.' end<br/>';
}

function createSlug($str, $delimiter = '-'){
    $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
    return $slug;
} 

function getMapping($source) {
    $conn=conn();
    $result = null;
    $i=0;

    $sql='SELECT * FROM wp_xmlimport_mapping WHERE source="'.$source.'"';
    foreach ($conn->query($sql) as $row) {
        $result[$i]["xml_tag"] = $row['xml_tag'];
        $result[$i]["xml_type"] = $row['xml_type'];
        $result[$i]["db_table"] = $row['db_table'];
        $result[$i]["db_type"] = $row['db_type'];
        $i++;
    }
    $conn=null;
    return $result;
}

function mergeProduct($product) {
    //echo 'mergeProduct '.$product["post_title"].' start<br/>';
    try {
        $output=get_page_by_title( $product["post_title"], ARRAY_A, 'product' );
        //echo $product["post_title"]."<br/>";
        //var_dump($output);
        $product["ID"]=$output["ID"];
        //echo "ID: ".$output["ID"]."<br/>";
        $post_id = wp_insert_post($product,true);
        wp_set_object_terms( $post_id, 'simple', 'product_type' );
        update_post_meta( $post_id, '_price', $product['_price'] );
        update_post_meta( $post_id, '_sku', $product['_sku'] );

        //echo 'mergeProduct '.$product["post_title"].' end OK<br/>';
        return $post_id;
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    //echo 'mergeProduct '.$product["post_title"].' end error<br/>';
    return null;
}

function uploadAttachment($parent_post_id, $url) {
    //echo 'uploadAttachment '.$parent_post_id.' start<br/>';
    $media = get_attached_media( 'image', $parent_post_id);

    if (sizeof($media)==0) {
        return uploadFile($parent_post_id, $url);
    } else {
        foreach ($media as $value) {
            if ($value->post_content!=$url) {
                return uploadFile($parent_post_id, $url);
            }
        }
    }
    //echo 'uploadAttachment '.$parent_post_id.' end<br/>';
}

function uploadFile($parent_post_id, $url) {
    //echo 'uploadFile '.$parent_post_id.' start<br/>';
    $file = array();
    $file['name'] = basename($url);
    $file['tmp_name'] = download_url($url);
    if (is_wp_error($file['tmp_name'])) {
        @unlink($file['tmp_name']);
        //echo 'ERROR in uploadFile '.$parent_post_id.'<br/>';
        return new WP_Error('uploadAttachment', 'Could not download image from remote source');
    }
            
    $attachmentId = media_handle_sideload($file, $parent_post_id, null, array("post_content"=>$url));
            
    $attach_data = wp_generate_attachment_metadata( $attachmentId,  get_attached_file($attachmentId));
    wp_update_attachment_metadata( $attachmentId,  $attach_data );

    set_post_thumbnail( $parent_post_id, $attachmentId );
    //echo 'uploadFile '.$parent_post_id.' end<br/>';
    return $attachmentId;
}
?>
