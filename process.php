<?php
require($_SERVER["DOCUMENT_ROOT"].'/wp-blog-header.php');
require($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');
require_once( ABSPATH . 'wp-includes/post.php' );

include dirname(__FILE__)."/conn.php";
if(isset($_POST['submit']))
{
   main();
} 

function main() {
	try {
		echo "main<br/>";
		$mapping=getMapping($_POST["name"]);
		if ($_POST["name"]=="mobilnet") {
			$sxe=simplexml_load_file(dirname(__FILE__)."/sources/products.xml", 'SimpleXMLIterator');
			echo "mobilnet<br/>";
			printXML($sxe);
			return;
		} else {
		    $sxe = simplexml_load_file($_POST["url"], 'SimpleXMLIterator');
		    echo "dit</br>";
		}
	    $i=0;
	    for ($sxe->rewind(); $sxe->valid(); $sxe->next()) {
	        if ($sxe->hasChildren()) {
	            foreach ($sxe->getChildren() as $element=>$value) {

	            	if (strtolower($value->getName())=='product') {
	            		//var_dump(xml2array($value));	
	            		//return;
	            		$product = array(
							'post_content' => $value->full_description->__toString(),
							'post_title'   => $value->name->__toString(),
							'post_excerpt' => $value->short_description->__toString(),
							'post_status'  => 'publish',
							'post_name'    => createSlug($value->name->__toString()),
							'post_type'    => 'product',
							'_price'       => $value->offered_selling_price->__toString(),
							'_sku'         => $value->ean->__toString()
						);
						
						$post_id = mergeProduct($product);
	            		//echo $value->name->__toString()."</br>";
						$product_image_urls=$value->product_image_urls;
						$list_id=array();
						for( $product_image_urls->rewind(); $product_image_urls->valid(); $product_image_urls->next() ) {
						    foreach($product_image_urls->getChildren() as $name => $url) {
					        $attachment_id=uploadAttachment($post_id, $url);
					        array_push($list_id, $attachment_id);
						    }
						}
						update_post_meta($post_id,'_product_image_gallery',implode(',',$list_id));
						if ($value->category!='') {
							insertCategories($value->category, $post_id);
						}
						echo $post_id." - ".$value->name->__toString()."<br/>";
						$i++;
						//break;
	            	}
	            }
	        }
	    }
	    touchSource($_POST["name"]);
	}
	catch(Exception $e) {
	    echo $e->getMessage();
	}
}

function parse_category($categoryString) {
	$categories=explode(" / ", $categoryString);

	$parent=0;
	foreach ($categories as $category) {
		$id=checkCategory($category,$parent);
		if (is_null($id)) {
			insertCategory($category,$parent);
			# code...
		} else {
			print($id.'-');
			$parent=$id;
		}
		# code...
	}

}

function createOrUpdateProduct($product) {
	echo '<br/>-'.'createOrUpdateProduct '.$product["post_title"];
	$post_id = wp_insert_post($product,true);
	return $post_id;
}

function xml2array($xml)
{
    $arr = array();
 
    foreach ($xml->children() as $r)
    {
        $t = array();
        if(count($r->children()) == 0)
        {
            $arr[$r->getName()] = strval($r);
        }
        else
        {
            $arr[$r->getName()][] = xml2array($r);
        }
    }
    return $arr;
}

function printXML($sxe) {
	for ($sxe->rewind(); $sxe->valid(); $sxe->next()) {
		if ($sxe->hasChildren()) {
			echo $sxe->key()."<br/>";
	        foreach ($sxe->getChildren() as $element=>$value) {
	    	  	echo $value->getName().'-'.$value."<br/>";
	        }
	    }

    }
}

?>