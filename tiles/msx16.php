<?php

//-----------------------------------------------------------------------------
// Generic MSX 16x16 2bpp Tile Definition
// Written by Derrick Sobodash
//
// Byte Count: 64
// Dimensions: 16x16 4 colors
// Structure :
//     Plane1: AAAAAAAAQQQQQQQQ  Plane2: ggggggggwwwwwwww
//             BBBBBBBBRRRRRRRR          hhhhhhhhxxxxxxxx
//             CCCCCCCCSSSSSSSS          iiiiiiiiyyyyyyyy
//             DDDDDDDDTTTTTTTT          jjjjjjjjzzzzzzzz
//             EEEEEEEEUUUUUUUU          kkkkkkkk22222222
//             FFFFFFFFVVVVVVVV          llllllll33333333
//             GGGGGGGGWWWWWWWW          mmmmmmmm44444444
//             HHHHHHHHXXXXXXXX          nnnnnnnn55555555
//             IIIIIIIIYYYYYYYY          oooooooo66666666
//             JJJJJJJJZZZZZZZZ          pppppppp77777777
//             KKKKKKKKaaaaaaaa          qqqqqqqq88888888
//             LLLLLLLLbbbbbbbb          rrrrrrrr99999999
//             MMMMMMMMcccccccc          ssssssss!!!!!!!!
//             NNNNNNNNdddddddd          tttttttt????????
//             OOOOOOOOeeeeeeee          uuuuuuuu@@@@@@@@
//             PPPPPPPPffffffff          vvvvvvvv********
//-----------------------------------------------------------------------------

// Define what tile definition format this is
//define("COLOR_DEPTH", "2"); // Monochrome    (Single Plane)
  define("COLOR_DEPTH", "4"); // Four Color    (Two Plane)
//define("COLOR_DEPTH", "8"); // Eight Color   (Three Plane)
//define("COLOR_DEPTH","16"); // Sixteen Color (Four Plane)

// Interleave pixels
$interleave  = 0;

// Tiles width and height in pixels (bits)
$tile_width  = 16;
$tile_height = 16;

// How many bytes are in the pattern?
$pat_size    = 64;

// You only need to define the pattern up till where it repeats. You may use
// whitespaces to help you align everything (they will be stripped when your
// string is used). 64 bytes is the limit.
//
// The hirearchy of letters is [A-Z] [a-z] [2-9] [!?@*]
//
// Basically, each unique letter is a byte, and the repeats of that letter are
// bits within that byte.

$plane1 = "AAAAAAAAQQQQQQQQ
           BBBBBBBBRRRRRRRR
           CCCCCCCCSSSSSSSS
           DDDDDDDDTTTTTTTT
           EEEEEEEEUUUUUUUU
           FFFFFFFFVVVVVVVV
           GGGGGGGGWWWWWWWW
           HHHHHHHHXXXXXXXX
           IIIIIIIIYYYYYYYY
           JJJJJJJJZZZZZZZZ
           KKKKKKKKaaaaaaaa
           LLLLLLLLbbbbbbbb
           MMMMMMMMcccccccc
           NNNNNNNNdddddddd
           OOOOOOOOeeeeeeee
           PPPPPPPPffffffff";
           
$plane2 = "ggggggggwwwwwwww
           hhhhhhhhxxxxxxxx
           iiiiiiiiyyyyyyyy
           jjjjjjjjzzzzzzzz
           kkkkkkkk22222222
           llllllll33333333
           mmmmmmmm44444444
           nnnnnnnn55555555
           oooooooo66666666
           pppppppp77777777
           qqqqqqqq88888888
           rrrrrrrr99999999
           ssssssss!!!!!!!!
           tttttttt????????
           uuuuuuuu@@@@@@@@
           vvvvvvvv********";

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
