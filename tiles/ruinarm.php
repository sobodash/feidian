<?php

//-----------------------------------------------------------------------------
// Ruin Arm (SNES) for FEIDIAN
// Written by Derrick Sobodash
//
// Byte Count: 32
// Dimensions: 16x16 font
// Structure:  AAAAAAAAIIIIIIII
//             BBBBBBBBJJJJJJJJ
//             CCCCCCCCKKKKKKKK
//             DDDDDDDDLLLLLLLL
//             EEEEEEEEMMMMMMMM
//             FFFFFFFFNNNNNNNN
//             GGGGGGGGOOOOOOOO
//             HHHHHHHHPPPPPPPP
//             ...(etc)
//-----------------------------------------------------------------------------

// Define what tile definition format this is
  define("COLOR_DEPTH", "2"); // Monochrome    (Single Plane)
//define("COLOR_DEPTH", "4"); // Four Color    (Two Plane)
//define("COLOR_DEPTH", "8"); // Eight Color   (Three Plane)
//define("COLOR_DEPTH","16"); // Sixteen Color (Four Plane)

// Tiles width and height in pixels (bits)
$tile_width  = 16;
$tile_height = 8;

// You only need to define the pattern up till where it repeats. You may use
// whitespaces to help you align everything (they will be stripped when your
// string is used). 64 bytes is the limit.
//
// The hirearchy of letters is [A-Z] [a-z] [2-9] [!?@*]
//
// Basically, each unique letter is a byte, and the repeats of that letter are
// bits within that byte.

$plane1  = "AAAAAAAAIIIIIIII
            BBBBBBBBJJJJJJJJ
            CCCCCCCCKKKKKKKK
            DDDDDDDDLLLLLLLL
            EEEEEEEEMMMMMMMM
            FFFFFFFFNNNNNNNN
            GGGGGGGGOOOOOOOO
            HHHHHHHHPPPPPPPP";
                
// How many bytes are in the above pattern?
$pat_size    = 16;

?>
