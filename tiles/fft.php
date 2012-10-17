<?php

//-----------------------------------------------------------------------------
// Final Fantasy Tactics
// Written by MaDMaLKav
//
// Byte Count: # OF BYTES IN THE PATTERN
// Dimensions: WIDTHxHEIGHT
// Structure :
//     Plane1: YOUR PATTERN HERE
//     Plane2: YOUR PATTERN HERE (if two planes)
//     Plane3: YOUR PATTERN HERE (if three planes)
//     Plane4: YOUR PATTERN HERE (if four planes)
//-----------------------------------------------------------------------------

// UNCOMMENT WHICHEVER FITS YOUR TILE TYPE
//define("COLOR_DEPTH", "2"); // Monochrome    (Single Plane)
define("COLOR_DEPTH", "4"); // Four Color    (Two Plane)
//define("COLOR_DEPTH", "8"); // Eight Color   (Three Plane)
//define("COLOR_DEPTH","16"); // Sixteen Color (Four Plane)

// SPECIFY THE WIDTH AND HEIGHT OF YOUR TILE IN PIXELS
$tile_width  = 10;
$tile_height = 14;

// HOW MANY BYTES ARE IN YOUR COMPLETE PATTERN? (includes all planes)
$pat_size    = 5;

// UNCOMMENT WHICH BYTE ORDERING YOU WANT (hint: most tiles are planar)
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

$plane1 = "AAAABBBBCC
           CCDDDDEEEE";
 
$plane2 = "AAAABBBBCC
           CCDDDDEEEE";
  
// Color 0 [binary 0 0]
$color0 = array(0x00, 0x00, 0x00);

// Color 1 [binary 0 1]
$color1 = array(0x70, 0x70, 0x70);

// Color 2 [binary 1 0]
$color2 = array(0xfc, 0xfc, 0xfc);

// Color 3 [binary 1 1]
$color3 = array(0x50, 0x50, 0x50);

?>
