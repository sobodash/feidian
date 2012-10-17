<?php

//-----------------------------------------------------------------------------
// Langrisser Millennium 28x28 (Dreamcast)
// Written by Derrick Sobodash
//
// Byte Count: 7
// Dimensions: 28x26 font
// Structure :
//     Plane1: AAAABBBBCCCCDDDDEEEEFFFFGGGG
//             (etc...)
//     Plane2: AAAABBBBCCCCDDDDEEEEFFFFGGGG
//             (etc...)
//-----------------------------------------------------------------------------

// Define what tile definition format this is
//define("COLOR_DEPTH", "2"); // Monochrome    (Single Plane)
  define("COLOR_DEPTH", "4"); // Four Color    (Two Plane)
//define("COLOR_DEPTH", "8"); // Eight Color   (Three Plane)
//define("COLOR_DEPTH","16"); // Sixteen Color (Four Plane)

// Tiles width and height in pixels (bits)
$tile_width  = 28;
$tile_height = 26;

// How many bytes are in the pattern?
$pat_size    = 7;

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

$plane1 = "AAAABBBBCCCCDDDDEEEEFFFFGGGG";
           
$plane2 = "AAAABBBBCCCCDDDDEEEEFFFFGGGG";

// Pallette: 2bpp supports four color graphics. Please define the four colors
//           to use here. Hex numbers can be done by prefixing with 0x[hex]
//           The format is R, G, B

// Color 0 [binary 0 0]
$color0 = array(0x00, 0x00, 0x00);

// Color 1 [binary 0 1]
$color1 = array(0x80, 0x80, 0x80);

// Color 2 [binary 1 0]
$color2 = array(0x40, 0x40, 0x40);

// Color 3 [binary 1 1]
$color3 = array(0xfb, 0xfb, 0xfb);

?>
