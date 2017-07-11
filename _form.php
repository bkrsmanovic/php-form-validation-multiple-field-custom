<?php
session_start();

if (empty($_POST)) {
    die;
}

if (!isset($_POST['name']) || empty($_POST['name'])) {
    echo json_encode(array(
        'message' => 'Name is a mandatory field',
        'error' => true
    ));

    die;
}

if (!isset($_POST['email']) || empty($_POST['email'])) {
    echo json_encode(array(
        'message' => 'Email is a mandatory field',
        'error' => true
    ));

    die;
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array(
        'message' => 'Please enter valid email',
        'error' => true
    ));

    die;
}

$message = implode("\n\n", array(
    "Random text mail",
    "You can append other file values",
    "Name: {$_POST['name']}",
    "Email: {$_POST['email']}"
));

$headers = "From: {$_POST['email']}";

// boundary
$seperator = md5(time());
$mime_boundary = "==Multipart_Boundary_x{$seperator}x";

$body = "";

function reArrayFiles(&$file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}
$temp_name = $_FILES['file']['tmp_name'];

if (!empty(array_filter($temp_name))) {
    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
    $body = "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
    $body .= "--{$mime_boundary}\n";

    $file_ary = reArrayFiles($_FILES['file']);
    $allowed =  array('png', 'jpg', 'pdf', 'docx', 'txt', 'doc');

    foreach ($file_ary as $file) {
        $filename = $file['name'];
        $filename = mb_strtolower($filename, 'UTF-8');
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if($file['error']) {
            echo json_encode(array(
                'message' => 'Unknown error',
                'error' => true
            ));

            die;
        }

        if(!in_array($ext, $allowed) ) {
            echo json_encode(array(
                'message' => 'Upload text, pdf or image file',
                'error' => true
            ));

            die;
        }

        $mimetype = mime_content_type($file['tmp_name']);
        if (!in_array($mimetype, array('image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')) || !in_array($file['type'], array('image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'))) {
            echo json_encode(array(
                'message' => 'Error with file. Try again',
                'error' => true
            ));

            die;
        }

        if ($file['size'] > 17000000) {
            echo json_encode(array(
                'message' => 'File is too big',
                'error' => true
            ));

            die;
        }

        $file_size = filesize($file['tmp_name']);
        $handle = fopen($file['tmp_name'], "rb");
        $content = fread($handle, $file_size);
        fclose($handle);
        $data = chunk_split(base64_encode($content));
        $name = $file['name'];
        $body .= "Content-Type: {\"application/octet-stream\"};\n" . " name=\"$name\"\n" .
		"Content-Disposition: attachment;\n" . " filename=\"$name\"\n" .
		"Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
		$body .= "--{$mime_boundary}\n";
    }
} else {
    $body .= $message;
}

if (mail('yourmail@gmail.com', "Form submited from - {$_POST['name']}", $body, $headers)) {
    mail($_POST['email'], 'Form confirmation', implode("\n", array(
        'Thank you for submiting form.',
        'Mucho arigato.',
        '',
        'Sayonara'
    )));
    
    echo json_encode(array(
        'message' => 'Form submitted!!',
        'error' => false
    ));

} else {
    echo json_encode(array(
        'message' => 'There was an error. Please try later',
        'error' => true
    ));
}
