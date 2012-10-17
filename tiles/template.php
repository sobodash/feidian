<?php

//-----------------------------------------------------------------------------
// TILE DEFINITION TEMPLATE
//-----------------------------------------------------------------------------


//-----------------------------------------------------------------------------
// TITLE OF DEFINITION
// Written by YOUR NAME
//
// Byte Count: # OF BYTES IN THE PATTERN
// Dimensions: WIDTHxHEIGHT
// Structure :
//     Plane1: YOUR PATTERN HERE
//     Plane2: YOUR PATTERN HERE (if two planes)
//     Plane3: YOUR PATTERN HERE (if three planes)
//     Plane4: YOUR PATTERN HERE (if four planes)
//-----------------------------------------------------------------------------

if(!defined('COLOR_DEPTH')) {
// UNCOMMENT WHICHEVER FITS YOUR TILE TYPE
//define("COLOR_DEPTH", "2"); // Monochrome    (Single Plane)
//define("COLOR_DEPTH", "4"); // Four Color    (Two Plane)
//define("COLOR_DEPTH", "8"); // Eight Color   (Three Plane)
//define("COLOR_DEPTH","16"); // Sixteen Color (Four Plane)
}

// SPECIFY THE WIDTH AND HEIGHT OF YOUR TILE IN PIXELS
$tile_width  = WIDTH;
$tile_height = HEIGHT;

// HOW MANY BYTES ARE IN YOUR COMPLETE PATTERN? (includes all planes)
$pat_size    = TOTAL BYTES;

// UNCOMMENT WHICH BYTE ORDERING YOU WANT (hint: most tiles are planar)
//$order = "planar";
//$order = "linear";

// You only need to define the pattern up till where it repeats. You may use
// whitespaces to help you align everything (they will be stripped when your
// string is used). 64 bytes is the limit.
//
// The hirearchy of letters is [A-Z] [a-z] [2-9] [!?@*]
//
// Basically, each unique letter is a byte, and the repeats of that letter are
// bits within that byte.

$plane1 = "PLANE 1 PATTERN GOES HERE";

// Uncomment this for a two-plane (2bpp) definition
//$plane2 = "PLANE 2 PATTERN GOES HERE";

// Uncomment this for a three-plane (3bpp) definition
//$plane3 = "PLANE 3 PATTERN GOES HERE";

// Uncomment this for a four-plane (4bpp) definition
//$plane4 = "PLANE 4 PATTERN GOES HERE";

// Pallette: 2bpp supports four color graphics. Please define the four colors
//           to use here. Hex numbers can be done by prefixing with 0x[hex]
//           The format is R, G, B

// Color 0 [binary 0 0]
$color0 = array(0x00, 0x00, 0x00);

// Color 1 [binary 0 1]
$color1 = array(0x63, 0xcf, 0x63);

// Color 2 [binary 1 0]
$color2 = array(0x39, 0x33, 0xff);

// Color 3 [binary 1 1]
$color3 = array(0xdc, 0xff, 0xff);

// Color 4 [binary 0 0]
$color4 = array(0x33, 0x00, 0x86);

// Color 5 [binary 0 1]
$color5 = array(0xbf, 0x73, 0x00);

// Color 6 [binary 1 0]
$color6 = array(0x00, 0xcf, 0xff);

// Color 7 [binary 1 1]
$color7 = array(0xef, 0xeb, 0xb4);

// Color 8 [binary 0 0]
$color8 = array(0x93, 0x00, 0x00);

// Color 9 [binary 0 1]
$color9 = array(0x51, 0xff, 0x00);

// Color 10 [binary 1 0]
$colorA = array(0xff, 0xac, 0x00);

// Color 11 [binary 1 1]
$colorB = array(0xbc, 0x11, 0xa4);

// Color 12 [binary 0 0]
$colorC = array(0x00, 0x00, 0x00);

// Color 13 [binary 0 1]
$colorD = array(0x59, 0x8c, 0xf2);

// Color 14 [binary 1 0]
$colorE = array(0xb6, 0x00, 0x9f);

// Color 15 [binary 1 1]
$colorF = array(0x83, 0xdc, 0x00);

?>
