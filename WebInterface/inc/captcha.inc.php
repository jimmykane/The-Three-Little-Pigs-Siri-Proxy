<?php
#######################################
## © 2008 Wouter De Schuyter (Paradox)
## <info@paradox-productions.net>
## http://paradox-productions.net/
## CAPTCHA V2.0 (SPAM PROTECTION)
#######################################

session_start(); // START SESSION

// FUNCTION TO SELECT A RANDOM CHARACTER OUT OF A STRING
function random_char($string) {
    $length = strlen($string);
    $position = mt_rand(0, $length - 1);
    return $string[$position];
}

$width = 60; // IMG WIDTH (PX)
$height = 25; // IMG HEIGHT (PX)
$characters = "abcdefghijklmnopqrstuvwxyz0123456789"; // CHARACTERS FOR CAPTCHA STRING
$font = "fonts/calibri.ttf"; // FONT LOCATION
$fontS = 14; // FONT SIZE (PX)
$min = 25; // MIN NUMBER FOR THE RANDOM RGB TEXT COLOR
$max = 200; // MAX NUMBER FOR THE RANDOM RGB TEXT COLOR
$eBorder = false; // ENABLE BORDER, "true" TO ENABLE & "false" TO DISABLE
$eLines = true; // ENABLE LINES, "true" TO ENABLE & "false" TO DISABLE (RECOMMENDED TO ENABLE)
$MiLC = 200; // MIN LINE COLOR
$MaLC = 250; // MAX LINE COLOR
$maxLines = 10; // MAX LINES (RECOMMENDED BETWEEN 5 & 15)
// ADVANCED
////////////
$positionCharacterX = 3; // POSITION CHARACTER 1 ON THE X-AXIS (PX)
$characterSpace = 14; // SPACE FOR 1 CHARACTER (PX)
$positionCharactersY = 18; // SPACE ON THE Y-AXIS (PX)

// // // // // // // // // // // // //

$img = imagecreate($width, $height);
imagecolorallocate($img, 255, 255, 255); // BACKGROUND COLOR IN RGB

$randNr1 = rand($min, $max); // RANDOM NUMBER 1 BETWEEN $min & $max
$randNr2 = rand($min, $max); // RANDOM NUMBER 2 BETWEEN $min & $max
$randNr3 = rand($min, $max); // RANDOM NUMBER 3 BETWEEN $min & $max

$randomChar1 = random_char($characters); // RANDOM CHARACTER 1
$randomChar2 = random_char($characters); // RANDOM CHARACTER 2
$randomChar3 = random_char($characters); // RANDOM CHARACTER 3
$randomChar4 = random_char($characters); // RANDOM CHARACTER 4

$textcolor1 = imagecolorallocate($img, $randNr1, $randNr2, $randNr3); // TEXT COLOR 1
$textcolor2 = imagecolorallocate($img, $randNr2, $randNr3, $randNr1); // TEXT COLOR 2
$textcolor3 = imagecolorallocate($img, $randNr3, $randNr1, $randNr2); // TEXT COLOR 3
$textcolor4 = imagecolorallocate($img, $randNr3, $randNr2, $randNr1); // TEXT COLOR 4

if($eLines == true) {
    for($i=0; $i <= $maxLines; $i++) {
        $linesC = imagecolorallocate($img, rand($MiLC, $MaLC), rand($MiLC, $MaLC), rand($MiLC, $MaLC));
        imageline($img, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $linesC);
    }
}

if($eBorder == true) {
    $bColor = imagecolorallocate($img, 175, 175, 175); // BORDER COLOR IN RGB
    imageline($img, 0, 0, $width, 0, $bColor);
    imageline($img, 0, $height, 0, 0, $bColor);
    imageline($img, $width-1, 0, $width-1, $height, $bColor);
    imageline($img, $width-1, $height-1, 0, $height-1, $bColor);
}

imagettftext($img, $fontS, 0, $positionCharacterX + 0 * $characterSpace, $positionCharactersY, $textcolor1, $font, $randomChar1); // CHARACTER 1
imagettftext($img, $fontS, 0, $positionCharacterX + 1 * $characterSpace, $positionCharactersY, $textcolor2, $font, $randomChar2); // CHARACTER 2
imagettftext($img, $fontS, 0, $positionCharacterX + 2 * $characterSpace, $positionCharactersY, $textcolor3, $font, $randomChar3); // CHARACTER 3
imagettftext($img, $fontS, 0, $positionCharacterX + 3 * $characterSpace, $positionCharactersY, $textcolor4, $font, $randomChar4); // CHARACTER 4

$_SESSION['captcha'] = $randomChar1 . $randomChar2 . $randomChar3 . $randomChar4; // CAPTCHA STRING FOR SESSION

header("content-type: image/png"); // CONTENT TYPE

imagepng($img); // CREATE IMAGE
imagedestroy($img); // DESTROY IMAGE
?>