<?php

//-----------------------------------------------------------------------------
// Generic NES Tile Definition
// Written by Derrick Sobodash
//
// Byte Count: 16
// Dimensions: 8x8 font
// Structure :
//     Plane1: AAAAAAAA   Plane2: IIIIIIII
//             BBBBBBBB           JJJJJJJJ
//             CCCCCCCC           KKKKKKKK
//             DDDDDDDD           LLLLLLLL
//             EEEEEEEE           MMMMMMMM
//             FFFFFFFF           NNNNNNNN
//             GGGGGGGG           OOOOOOOO
//             HHHHHHHH           PPPPPPPP
//-----------------------------------------------------------------------------

// Define what tile definition format this is
define("COLOR_DEPTH", "4"); // Four Color    (Two Plane)

// Tiles width and height in pixels (bits)
$tile_width  = 8;
$tile_height = 8;

// How many bytes are in the pattern?
$pat_size    = 16;

$plane1 = "AAAAAAAA
           BBBBBBBB
           CCCCCCCC
           DDDDDDDD
           EEEEEEEE
           FFFFFFFF
           GGGGGGGG
           HHHHHHHH";
           
$plane2 = "IIIIIIII
           JJJJJJJJ
           KKKKKKKK
           LLLLLLLL
           MMMMMMMM
           NNNNNNNN
           OOOOOOOO
           PPPPPPPP";

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

?>
