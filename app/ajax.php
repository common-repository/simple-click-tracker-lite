<?php
function my_handle_attachment($file_handler,$post_id,$set_thu=false) {
    // check to make sure its a successful upload
    if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();
    
    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');
    
    $attach_id = media_handle_upload( $file_handler, $post_id );
    if ( is_numeric( $attach_id ) ) {
    //update_post_meta( $post_id, '_my_file_upload', $attach_id );
    return $attach_id;
    }
}
if(isset($_REQUEST['action'])){
    if($_REQUEST['action']=="sctimgupload"){
            if ($_FILES['img_url']['name']) { 
                $file = array( 
                    'name' => sanitize_text_field($_FILES['img_url']['name']),
                    'type' => sanitize_text_field($_FILES['img_url']['type']), 
                    'tmp_name' => sanitize_text_field($_FILES['img_url']['tmp_name']), 
                    'error' => sanitize_text_field($_FILES['img_url']['error']),
                    'size' => sanitize_text_field($_FILES['img_url']['size'])
                ); 
                $_FILES = array ("img_url" => $file);             
                    foreach ($_FILES as $file => $array) {              
                    $newupload = my_handle_attachment($file,$pid);
                        $img = wp_get_attachment_image_src($newupload,'full');
                        _e($img[0]);
                    } 
                
            }else{
                _e(0);
            }
    }
    if($_REQUEST['action']=="delete_multiple_links"){
        //$ids =  explode(",",$_REQUEST['ids']);
        $table_link  = $wpdb->prefix."sct_link";        
        $formated_links_ids = array_fill(0, count(sanitize_text_field($_POST['links'])), '%d');
    	$links = implode(", ", $formated_links_ids);
    	 $wpdb->query($wpdb->prepare('DELETE FROM '.$table_link.' WHERE link_id IN ('.$links.')', sanitize_text_field($_POST['links'])));
        
    }
}
?>