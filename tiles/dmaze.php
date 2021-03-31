<?php

if(!defined('COLOR_DEPTH')) {
define("COLOR_DEPTH", "2"); // Monochrome    (Single Plane)
}

$tile_width  = 12;
$tile_height = 16;

$pat_size    = 24;

$order = "planar";

$plane1="AAAAIIIIQQQQ
         AAAAIIIIQQQQ
         BBBBJJJJRRRR
         BBBBJJJJRRRR
         CCCCKKKKSSSS
         CCCCKKKKSSSS
         DDDDLLLLTTTT
         DDDDLLLLTTTT
         EEEEMMMMUUUU
         EEEEMMMMUUUU
         FFFFNNNNVVVV
         FFFFNNNNVVVV
         GGGGOOOOWWWW
         GGGGOOOOWWWW
         HHHHPPPPXXXX
         HHHHPPPPXXXX";

$color0 = array(0x00, 0x00, 0x00);

$color1 = array(0x63, 0xcf, 0x63);

$color2 = array(0x39, 0x33, 0xff);

$color3 = array(0xdc, 0xff, 0xff);

$color4 = array(0x33, 0x00, 0x86);

$color5 = array(0xbf, 0x73, 0x00);

$color6 = array(0x00, 0xcf, 0xff);

$color7 = array(0xef, 0xeb, 0xb4);

$color8 = array(0x93, 0x00, 0x00);

$color9 = array(0x51, 0xff, 0x00);

$colorA = array(0xff, 0xac, 0x00);

$colorB = array(0xbc, 0x11, 0xa4);

$colorC = array(0x00, 0x00, 0x00);

$colorD = array(0x59, 0x8c, 0xf2);

$colorE = array(0xb6, 0x00, 0x9f);

$colorF = array(0x83, 0xdc, 0x00);

?>
