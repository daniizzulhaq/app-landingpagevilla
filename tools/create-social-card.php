<?php
// Render the original PropertyME wordmark as a portable social sharing image.
$image = imagecreatetruecolor(1200, 630);
$navy = imagecolorallocate($image, 20, 38, 53); $gold = imagecolorallocate($image, 198, 167, 123); $white = imagecolorallocate($image, 247, 245, 240);
imagefill($image, 0, 0, $navy); imagesetthickness($image, 2);
imagepolygon($image, [85, 520, 85, 185, 270, 80, 455, 185, 455, 520], $gold);
imagepolygon($image, [170, 520, 170, 245, 270, 185, 370, 245, 370, 520], $gold);
imageline($image, 270, 185, 270, 520, $gold); imageline($image, 550, 320, 650, 320, $gold);
$font = PHP_OS_FAMILY === 'Windows' ? 'C:/Windows/Fonts/georgia.ttf' : '/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf';
if (file_exists($font)) {
    imagettftext($image, 60, 0, 545, 275, $white, $font, 'PropertyME.');
    imagettftext($image, 17, 0, 550, 370, $gold, $font, 'BALI REAL ESTATE & BEYOND');
    imagettftext($image, 15, 0, 550, 420, $white, $font, 'Your Bali. Your Vision. Our Expertise.');
} else { imagestring($image, 5, 550, 270, 'PropertyME | Bali Real Estate', $white); }
imagepng($image, dirname(__DIR__).'/public/images/social-card.png', 9);
echo "Original social card rendered.\n";
