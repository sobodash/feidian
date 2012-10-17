<?php

//-----------------------------------------------------------------------------
// Final Fantasy VII (NES) for FEIDIAN
// Written by Derrick Sobodash
//
// Byte Count: 32
// Dimensions: 16x16 font dumped in pairs
// Structure:  AAAAAAAAQQQQQQQQIIIIIIIIYYYYYYYY
//             BBBBBBBBRRRRRRRRJJJJJJJJZZZZZZZZ
//             CCCCCCCCSSSSSSSSKKKKKKKKaaaaaaaa
//             DDDDDDDDTTTTTTTTLLLLLLLLbbbbbbbb
//             EEEEEEEEUUUUUUUUMMMMMMMMcccccccc
//             FFFFFFFFVVVVVVVVNNNNNNNNdddddddd
//             GGGGGGGGWWWWWWWWOOOOOOOOeeeeeeee
//             HHHHHHHHXXXXXXXXPPPPPPPPffffffff
//-----------------------------------------------------------------------------

// Define what tile definition format this is
define("COLOR_DEPTH", "2"); // Monochrome    (Single Plane)

// Tiles width and height in pixels (bits)
$tile_width  = 32;
$tile_height = 16;

$plane1  = "AAAAAAAAQQQQQQQQIIIIIIIIYYYYYYYY
            BBBBBBBBRRRRRRRRJJJJJJJJZZZZZZZZ
            CCCCCCCCSSSSSSSSKKKKKKKKaaaaaaaa
            DDDDDDDDTTTTTTTTLLLLLLLLbbbbbbbb
            EEEEEEEEUUUUUUUUMMMMMMMMcccccccc
            FFFFFFFFVVVVVVVVNNNNNNNNdddddddd
            GGGGGGGGWWWWWWWWOOOOOOOOeeeeeeee
            HHHHHHHHXXXXXXXXPPPPPPPPffffffff";
                
// How many bytes are in the above pattern?
$pat_size    = 32;

?>
