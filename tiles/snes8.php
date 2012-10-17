<?php

//-----------------------------------------------------------------------------
// Generic Super Nintendo Tile Definition
// Written by Derrick Sobodash
//
// Byte Count: 32
// Dimensions: 8x8
// Structure :
//     Plane1: AAAAAAAA   Plane2: BBBBBBBB
//             CCCCCCCC           DDDDDDDD
//             EEEEEEEE           FFFFFFFF
//             GGGGGGGG           HHHHHHHH
//             IIIIIIII           JJJJJJJJ
//             KKKKKKKK           LLLLLLLL
//             MMMMMMMM           NNNNNNNN
//             OOOOOOOO           PPPPPPPP
//
//     Plane3: QQQQQQQQ   Plane4: RRRRRRRR
//             SSSSSSSS           TTTTTTTT
//             UUUUUUUU           VVVVVVVV
//             WWWWWWWW           XXXXXXXX
//             YYYYYYYY           ZZZZZZZZ
//             aaaaaaaa           bbbbbbbb
//             cccccccc           dddddddd
//             eeeeeeee           ffffffff
//-----------------------------------------------------------------------------

// Define what tile definition format this is
//define("COLOR_DEPTH", "2"); // Monochrome    (Single Plane)
//define("COLOR_DEPTH", "4"); // Four Color    (Two Plane)
//define("COLOR_DEPTH", "8"); // Eight Color   (Three Plane)
  define("COLOR_DEPTH","16"); // Sixteen Color (Four Plane)

// Tiles width and height in pixels (bits)
$tile_width  = 8;
$tile_height = 8;

// How many bytes are in the pattern?
$pat_size    = 32;

// What kind of byte ordering?
  $order = "planar";
//$order = "linear";

// You only need to define the pattern up till where it repeats. You may use
// whitespaces to help you align everything (they will be stripped when your
// string is used). 64 bytes is the limit.
//
// The hirearchy of letters is [A-Z] [a-z] [2-9] [!?@*]
//
// Basically, each unique letter is a byte, and the repeats of that letter are
// bits within that byte.

$plane1 = "AAAAAAAA
           CCCCCCCC
           EEEEEEEE
           GGGGGGGG
           IIIIIIII
           KKKKKKKK
           MMMMMMMM
           OOOOOOOO";

$plane2 = "BBBBBBBB
           DDDDDDDD
           FFFFFFFF
           HHHHHHHH
           JJJJJJJJ
           LLLLLLLL
           NNNNNNNN
           PPPPPPPP";

$plane3 = "QQQQQQQQ
           SSSSSSSS
           UUUUUUUU
           WWWWWWWW
           YYYYYYYY
           aaaaaaaa
           cccccccc
           eeeeeeee";

$plane4 = "RRRRRRRR
           TTTTTTTT
           VVVVVVVV
           XXXXXXXX
           ZZZZZZZZ
           bbbbbbbb
           dddddddd
           ffffffff";

// Pallette: 2bpp supports four color graphics. Please define the four colors
//           to use here. Hex numbers can be done by prefixing with 0x[hex]
//           The format is R, G, B

// Color 0 [binary 0 0]
$color0 = array(0x00, 0x00, 0x00);

// Color 1 [binary 0 1]
$color1 = array(0x00, 0x00, 0xa8);

// Color 2 [binary 1 0]
$color2 = array(0x00, 0xa8, 0x00);

// Color 3 [binary 1 1]
$color3 = array(0x00, 0xa8, 0xa8);

// Color 4 [binary 0 0]
$color4 = array(0xa8, 0x00, 0x00);

// Color 5 [binary 0 1]
$color5 = array(0xa8, 0x00, 0xa8);

// Color 6 [binary 1 0]
$color6 = array(0xa8, 0x84, 0x00);

// Color 7 [binary 1 1]
$color7 = array(0xa8, 0xa8, 0xa8);

// Color 8 [binary 0 0]
$color8 = array(0x54, 0x54, 0x54);

// Color 9 [binary 0 1]
$color9 = array(0x54, 0x54, 0xfc);

// Color 10 [binary 1 0]
$colorA = array(0x54, 0xfc, 0x54);

// Color 11 [binary 1 1]
$colorB = array(0x54, 0xfc, 0xfc);

// Color 12 [binary 0 0]
$colorC = array(0xfc, 0x54, 0x54);

// Color 13 [binary 0 1]
$colorD = array(0xfc, 0x54, 0xfc);

// Color 14 [binary 1 0]
$colorE = array(0xfc, 0xfc, 0x54);

// Color 15 [binary 1 1]
$colorF = array(0xfc, 0xfc, 0xfc);

?>
