<?php

require_once "config/database.php";

$name_error = $email_error = $comment_error = $website_error = "";
$name = $email = $comment = $website = $fileToUpload = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    try {

        include "inc/user_input.php";
        
        $file_name      = $_FILES["fileToUpload"]["name"];
        $file_name2     = $_FILES["fileToUpload2"]["name"];
        $temp_file_name = $_FILES["fileToUpload"]["tmp_name"];
        $temp_file_name2= $_FILES["fileToUpload2"]["tmp_name"];
        $file_size      = $_FILES["fileToUpload"]["size"];
        $file_size2     = $_FILES["fileToUpload2"]["size"];
        $target_dir     = "uploads/";
        $target_file    = strtolower($target_dir . basename($file_name));
        $target_file2   = strtolower($target_dir . basename($file_name2));
        $upload_ok      = 1;
        $img_file_type  = pathinfo($target_file, PATHINFO_EXTENSION);
        $doc_file_type  = pathinfo($target_file2,PATHINFO_EXTENSION);

        //Check if image is an actual image or fake image
        $check_img = getimagesize($temp_file_name);

        if($check_img == false) {
            echo "File is not an image";
            $upload_ok = 0;
        } else {
            //echo "File is an image - " . $check_img["mime"];
            $upload_ok = 1;

            //Check if file already exists
            if(file_exists($target_file) || file_exists($target_file2)) {
                echo "File is already uploaded!";
                $upload_ok = 0;
            } else {
                //Check file size
                if($file_size > 500000) {
                    echo "Please enter a file size between 5mb";
                    $upload_ok = 0;
                } else {
                    //Allow certain file formats
                    if($img_file_type != "jpg" && $img_file_type != "png" && $img_file_type != "jpeg" && $img_file_type != "gif" && $doc_file_type !="docx" && $doc_file_type !="pdf" && $doc_file_type !="xlsx" && $doc_file_type !="csv") {
                        echo "JPG, PNG, JPEG and GIF files are allowed";
                        $upload_ok = 0;
                    } else {
                        //Check if $upload_ok is set to 0 by an error
                        if($upload_ok === 0) {
                            echo "File has not been uploaded";
                        } else {
                            if(move_uploaded_file($temp_file_name, $target_file) && move_uploaded_file($temp_file_name2, $target_file2)){

                                $db_query = "INSERT INTO employees(
                                                            name,
                                                            email,
                                                            website,
                                                            comment,
                                                            image_path,
                                                            docu_path)
                                                            VALUES(
                                                                :name,
                                                                :email,
                                                                :website,
                                                                :comment,
                                                                :image_path,
                                                                :docu_path)";
                                $statement = $connection->prepare($db_query);
                                $statement->bindParam(':name', $name, PDO::PARAM_STR);
                                $statement->bindParam(':email', $email, PDO::PARAM_STR);
                                $statement->bindParam(':website', $website, PDO::PARAM_STR);
                                $statement->bindParam(':comment', $comment, PDO::PARAM_STR);
                                $statement->bindParam(':image_path',$target_file, PDO::PARAM_STR);
                                $statement->bindParam(':docu_path',$target_file2, PDO::PARAM_STR);
                                $statement->execute();
                            }
                        }
                    }
                }
            }
        }
    } catch (PDOException $e) {
            echo $e->getMessage();
    }

    $conn = null;

}

?>
