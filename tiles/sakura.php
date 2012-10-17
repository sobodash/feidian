<?php

//-----------------------------------------------------------------------------
// Sakura Wars (Saturn) Tile Definition
// Written by Derrick Sobodash
//
// Byte Count: 8
// Dimensions: 16x16
// Structure :
//     Plane1: AABBCCDDEEFFGGHH   Plane2: AABBCCDDEEFFGGHH
//
//     Plane3: AABBCCDDEEFFGGHH   Plane4: AABBCCDDEEFFGGHH
//-----------------------------------------------------------------------------

// Define what tile definition format this is
//define("COLOR_DEPTH", "2"); // Monochrome    (Single Plane)
//define("COLOR_DEPTH", "4"); // Four Color    (Two Plane)
//define("COLOR_DEPTH", "8"); // Eight Color   (Three Plane)
  define("COLOR_DEPTH","16"); // Sixteen Color (Four Plane)

// Tiles width and height in pixels (bits)
$tile_width  = 16;
$tile_height = 16;

// How many bytes are in the pattern?
$pat_size    = 8;

// What kind of byte ordering?
//$order = "planar";
  $order = "linear";

// You only need to define the pattern up till where it repeats. You may use
// whitespaces to help you align everything (they will be stripped when your
// string is used). 64 bytes is the limit.
//
// The hirearchy of letters is [A-Z] [a-z] [2-9] [!?@*]
//
// Basically, each unique letter is a byte, and the repeats of that letter are
// bits within that byte.

$plane1 = "AABBCCDDEEFFGGHH";

$plane2 = "AABBCCDDEEFFGGHH";

$plane3 = "AABBCCDDEEFFGGHH";

$plane4 = "AABBCCDDEEFFGGHH";

// Pallette: 2bpp supports four color graphics. Please define the four colors
//           to use here. Hex numbers can be done by prefixing with 0x[hex]
//           The format is R, G, B

// Color 0 [binary 0 0]
$color0 = array(0xff, 0xf7, 0xf5);

// Color 1 [binary 0 1]
$color1 = array(0x84, 0x84, 0x84);

// Color 2 [binary 1 0]
$color2 = array(0x00, 0xa8, 0x00);

// Color 3 [binary 1 1]
$color3 = array(0x00, 0xa8, 0xa8);

// Color 4 [binary 0 0]
$color4 = array(0x21, 0x21, 0x21);

// Color 5 [binary 0 1]
$color5 = array(0xa8, 0x00, 0xa8);

// Color 6 [binary 1 0]
$color6 = array(0x63, 0x63, 0x63);

// Color 7 [binary 1 1]
$color7 = array(0xa8, 0xa8, 0xa8);

// Color 8 [binary 0 0]
$color8 = array(0x10, 0x10, 0x10);

// Color 9 [binary 0 1]
$color9 = array(0x94, 0x94, 0x94);

// Color 10 [binary 1 0]
$colorA = array(0x44, 0x44, 0x44);

// Color 11 [binary 1 1]
$colorB = array(0xd6, 0xd6, 0xd6);

// Color 12 [binary 0 0]
$colorC = array(0x31, 0x31, 0x31);

// Color 13 [binary 0 1]
$colorD = array(0xb5, 0xb5, 0xb5);

// Color 14 [binary 1 0]
$colorE = array(0x73, 0x73, 0x73);

// Color 15 [binary 1 1]
$colorF = array(0xfc, 0xfc, 0xfc);

?>
