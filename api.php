<?php
// Disable caching
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header("Expires: 0");
header("Access-Control-Allow-Origin: *");

// Process request and output data
$localurl = "images/*.{gif,jpg,png,webp}";
$img_array = glob($localurl, GLOB_BRACE);

// Check if any images were found
if (!$img_array) {
    header("HTTP/1.0 404 Not Found");
    echo "No images found.";
    exit();
}

// Randomly select an image
$img = array_rand($img_array);
$imgurl = $img_array[$img];

// Add cache control query parameter
$imgurl = $imgurl . '?t=' . time();

// Determine if HTTPS is used
$https = isset($_GET["https"]) ? filter_var($_GET["https"], FILTER_VALIDATE_BOOLEAN) : true;
$protocol = $https ? 'https://' : 'http://';
$imgurl = $protocol . $_SERVER['SERVER_NAME'] . '/' . ltrim($imgurl, '/');

// Check if direct image return is requested
if (isset($_GET["return"]) && $_GET["return"] === "img") {
    $imageInfo = getimagesize($img_array[$img]);
    if ($imageInfo === false) {
        header("HTTP/1.0 500 Internal Server Error");
        echo "Failed to get image information.";
        exit();
    }
    $imgType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));

    header("Content-Type: " . $imageInfo['mime']);
    readfile($img_array[$img]);
    exit();
}

// Handle JSON output request
if (isset($_GET["type"]) && $_GET["type"] === "json") {
    $rTotal = $gTotal = $bTotal = $total = 0;

    $imageInfo = getimagesize($img_array[$img]);
    if ($imageInfo === false) {
        header("HTTP/1.0 500 Internal Server Error");
        echo "Failed to get image information.";
        exit();
    }
    $imgType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
    $imageFun = 'imagecreatefrom' . ($imgType == 'jpg' ? 'jpeg' : $imgType);
    if (!function_exists($imageFun)) {
        header("HTTP/1.0 500 Internal Server Error");
        echo "Unsupported image type.";
        exit();
    }
    $i = $imageFun($img_array[$img]);

    if ($i === false) {
        header("HTTP/1.0 500 Internal Server Error");
        echo "Failed to create image resource.";
        exit();
    }

    for ($x = 0; $x < imagesx($i); $x++) {
        for ($y = 0; $y < imagesy($i); $y++) {
            $rgb = imagecolorat($i, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $rTotal += $r;
            $gTotal += $g;
            $bTotal += $b;
            $total++;
        }
    }

    $rAverage = round($rTotal / $total);
    $gAverage = round($gTotal / $total);
    $bAverage = round($bTotal / $total);

    $arr = array('ImgUrl' => $imgurl, 'Color' => "$rAverage,$gAverage,$bAverage");
    echo json_encode($arr);
    exit();
}

// Default behavior: Redirect to the image URL
header("Location: $imgurl");
exit();
?>
