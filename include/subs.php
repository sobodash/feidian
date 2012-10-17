<?php
/*
    FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter
    Copyright (C) 2003, 2004 Derrick Sobodash
    Version: 0.89
    Web    : https://github.com/sobodash/feidian
    E-mail : derrick@sobodash.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program (license.txt); if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
//-----------------------------------------------------------------------------
// FEIDIAN Subs Module
//-----------------------------------------------------------------------------
// This is just frequently used chunks of code for file/io, etc, that I would
// rather not have to write 90x.
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// binaryread - reads a file to a bit string
//-----------------------------------------------------------------------------
function binaryread($filename, $length, $offset, $invert) {
  $fd = fopen($filename, "rb");
  fseek($fd, $offset, SEEK_SET);
  $fddump = fread($fd, $length);
  fclose($fd); $binarydump = "";
  // Transform our dump to a long binary string, 8-bits per byte, and pad
  // left with zeros when needed
  for($i=0; $i<strlen($fddump); $i++) {
    $binarydump .= str_pad(decbin(hexdec(bin2hex(substr($fddump, $i, 1)))), 8, "0", STR_PAD_LEFT);
  }
  if ($invert==1) {
    $invertmap = "";
    for ($lala=0; $lala<strlen($binarydump); $lala++) {
      if ($binarydump[$lala]==0) $invertmap.=1;
      else if ($binarydump[$lala]==1) $invertmap.=0;
      else die("What'chu talkin' bout Willis?\n");
    }
    $binarydump = $invertmap;
  }
  return($binarydump);
}

//-----------------------------------------------------------------------------
// hexread - reads a file to a hex string
//-----------------------------------------------------------------------------
function hexread($filename, $length, $offset) {
  $fd = fopen($filename, "rb");
  fseek($fd, $offset, SEEK_SET);
  $fddump = fread($fd, $length);
  fclose($fd); $hexdump = "";
  // Transform our dump to a long binary string, 8-bits per byte, and pad
  // left with zeros when needed
  for($i=0; $i<strlen($fddump); $i++) {
    $hexdump .= str_pad(bin2hex(substr($fddump, $i, 1)), 2, "0", STR_PAD_LEFT);
  }
  return($hexdump);
}

//-----------------------------------------------------------------------------
// fileread - reads a file
//-----------------------------------------------------------------------------
function fileread($filename, $length, $offset) {
  $fd = fopen($filename, "rb");
  fseek($fd, $offset, SEEK_SET);
  $fddump = fread($fd, $length);
  fclose($fd);
  return($fddump);
}

//-----------------------------------------------------------------------------
// injectfile - inserts data into another file
//-----------------------------------------------------------------------------
function injectfile($out_file, $seekstart, $bitplane) {
  $top=""; $bottom="";
  if (file_exists($out_file)){
    $fd = fopen($out_file, "rb");
    $top = fread($fd, $seekstart);  
    fseek($fd, $seekstart + strlen($bitplane), SEEK_SET);
    $bottom = fread($fd, filesize($out_file)-($seekstart + strlen($bitplane)));
    fclose($fd);
  }  
  $fo = fopen($out_file, "wb");
  fputs($fo, $top . $bitplane . $bottom);
  fclose($fo);
}

//-----------------------------------------------------------------------------
// bitmapheader_1bpp - writes a valid monochrome bitmap header
//-----------------------------------------------------------------------------
function bitmapheader_1bpp($length, $width, $height) {
  // Base of the bitmap header
  $header = "BM" . pack("V*", $length+62) . pack("V*", 0) . pack("V*", 62);
  
  $info_header = pack("V*", 40) . pack("V*", $width) . pack("V*", $height) . 
                 pack("v*", 1) . pack("v*", 1) . pack("V*", 0) . pack("V*", $length) . 
                 pack("V*", 0) . pack("V*", 0) . pack("V*", 0) . pack("V*", 0) . pack("V*", 0);
                 
  // As this is a monochrome image, no palette is needed. We write the
  // first palette entry anyway just to appease the Bitmap gods
  $rgbquad = chr(0xff) . chr(0xff) . chr(0xff) . chr(0);
  
  return($header . $info_header . $rgbquad);
}

//-----------------------------------------------------------------------------
// writexpm - Makes an XPM image file
//-----------------------------------------------------------------------------
function writexpm($bitmap, $width, $height, $out_file, $prefix, $color0, $color1, $color2, $color3, $color4, $color5, $color6, $color7, $color8, $color9, $colorA, $colorB, $colorC, $colorD, $colorE, $colorF) {
  $output = "/* XPM */\n";
  $output.= "static char * xpm2_xpm[] = {\r\n";
  $output.= "/* Generated by FEIDIAN */\r\n";
  $output.= "\"$width $height 16 1\",\r\n";
  $output.= "\"0 	c $color0\",\r\n";
  $output.= "\"1 	c $color1\",\r\n";
  $output.= "\"2 	c $color2\",\r\n";
  $output.= "\"3 	c $color3\",\r\n";
  $output.= "\"4 	c $color4\",\r\n";
  $output.= "\"5 	c $color5\",\r\n";
  $output.= "\"6 	c $color6\",\r\n";
  $output.= "\"7 	c $color7\",\r\n";
  $output.= "\"8 	c $color8\",\r\n";
  $output.= "\"9 	c $color9\",\r\n";
  $output.= "\"a 	c $colorA\",\r\n";
  $output.= "\"b 	c $colorB\",\r\n";
  $output.= "\"c 	c $colorC\",\r\n";
  $output.= "\"d 	c $colorD\",\r\n";
  $output.= "\"e 	c $colorE\",\r\n";
  $output.= "\"f 	c $colorF\"";
  for($i=0; $i<$height; $i++)
    $output.= ",\r\n\"" . strrev(substr($bitmap, $i*$width, $width)) . "\"";
  $output.= "};\r\n";
  $fo = fopen($out_file . "_$prefix.xpm", "wb");
  fputs($fo, $output);
  fclose($fo);
  print $out_file . "_$prefix.xpm was written!\n\n";
}

//-----------------------------------------------------------------------------
// writexpmpal - Makes an XPM image file with a pregenerated palette
//-----------------------------------------------------------------------------
function writexpmpal($bitmap, $width, $height, $out_file, $prefix, $palette) {
  $output = "/* XPM */\n";
  $output.= "static char * xpm2_xpm[] = {\r\n";
  $output.= "/* Generated by FEIDIAN */\r\n";
  $output.= "\"$width $height 16 1\",\r\n";
  $output.= rtrim($palette);
  for($i=0; $i<$height; $i++)
    $output.= ",\r\n\"" . strrev(substr($bitmap, $i*$width, $width)) . "\"";
  $output.= "};\r\n";
  $fo = fopen($out_file . "_$prefix.xpm", "wb");
  fputs($fo, $output);
  fclose($fo);
  print $out_file . "_$prefix.xpm was written!\n\n";
}

//-----------------------------------------------------------------------------
// xpm2bitstring - Reads an XPM to a 16 color running string
//-----------------------------------------------------------------------------
function xpm2bitstring($in_file, $invert) {
  // A word of warning. This function only supports XPMs up to 131072 pixels
  // wide. If you make a wider XPM than that, you suck.
  
  // Here comes your TV car chase.
  
  $fd = fopen($in_file, "rb");
  // Test to see if it's a valid XPM
  if(trim(fgets($fd, 0x20000)) != "/* XPM */")
    die(print "ERROR: The supplied image does not appear to be an XPM\n");

  // Next we need to get the info for the graphic, but ignore any stupid
  // comments people may have added ot the file, since XPM is a gay format and
  // lets people add comments to GRAPHICS.
  $diebit=0;
  while($diebit==0) {
    $buffer = trim(fgets($fd, 0x20000));
    if((substr($buffer, 0, 2) != "/*")&&(substr($buffer, 0, 6)!="static")) {
      list($width, $height, $color_depth, $byte_count) = split(" ", substr($buffer, 1, (strlen($buffer)-2)));
      $diebit=1;
    }
  }
  
  // It's the TV-car-chase-theme, HAH!
  if($color_depth > 16) die(print "ERROR: This XPM contains more than 16 colors!\n");
  
  // Now we need to read in the color table. Actually, the colors are totally
  // irrelevant. We just need to know what the bits they use to represent each
  // color are, and the order of the color table.
  $diebit=0; $color_table=array(); $color_number=0;
  while($color_number < $color_depth) {
    $buffer = trim(fgets($fd, 0x20000));
    if(substr($buffer, 0, 2) != "/*") {
      $color_table[$color_number] = substr($buffer, 1, $byte_count);
      $color_number++;
    }
  }
  $letter_people = array("0", "1", "2", "3", "4", "5", "6", "7",
                         "8", "9", "a", "b", "c", "d", "e", "f");
  
  // Well you're a TV car chase!
  
  // ON WITH THE FUCKING SHOW!
  $lines_processed=0; $output="";
  while($lines_processed < $height) {
    $buffer = trim(fgets($fd, 0x20000));
    if(substr($buffer, 0, 2) != "/*") {
      $buffer = strrev(substr($buffer, 1, (strlen($buffer)-3)));
      for($i=0; $i<$width; $i++){
        $output .= $letter_people[array_search(substr($buffer, $i*$byte_count, $byte_count), $color_table)];
      }
      $lines_processed++;
    }
  }
  fclose($fd);
  
  if ($invert==1) {
    $invertmap = "";
    for ($lala=0; $lala<strlen($output); $lala++) {
      if ($output[$lala]==0) $invertmap.=1;
      else if ($output[$lala]==1) $invertmap.=0;
      else die("What'chu talkin' bout Willis?\n");
    }
    $output = $invertmap;
  }
  
  return($output);
}

//-----------------------------------------------------------------------------
// getxpminfo - Gets the info of an XPM file
//-----------------------------------------------------------------------------
function getxpminfo($in_file) {
  // A word of warning. This function only supports XPMs up to 131072 pixels
  // wide. If you make a wider XPM than that, you suck.
  
  // Here comes your TV car chase.
  
  $fd = fopen($in_file, "rb");
  // Test to see if it's a valid XPM
  if(trim(fgets($fd, 0x20000)) != "/* XPM */")
    die(print "ERROR: The supplied image does not appear to be an XPM\n");

  // Next we need to get the info for the graphic, but ignore any stupid
  // comments people may have added ot the file, since XPM is a gay format and
  // lets people add comments to GRAPHICS.
  $diebit=0;
  while($diebit==0) {
    $buffer = trim(fgets($fd, 0x20000));
    if((substr($buffer, 0, 2) != "/*")&&(substr($buffer, 0, 6)!="static")) {
      list($width, $height, $color_depth, $byte_count) = split(" ", substr($buffer, 1, (strlen($buffer)-2)));
      $diebit=1;
    }
  }
  
  // It's the TV-car-chase-theme, HAH!
  if($color_depth > 16) die(print "ERROR: This XPM contains more than 16 colors!\n");
  
  // Now we need to read in the color table. Actually, the colors are totally
  // irrelevant. We just need to know what the bits they use to represent each
  // color are, and the order of the color table.
  $diebit=0; $palette=""; $color_number=0;
  while($color_number < $color_depth) {
    $buffer = trim(fgets($fd, 0x20000));
    if(substr($buffer, 0, 2) != "/*") {
      $palette.= "\"" . dechex($color_number) . " " . substr($buffer, 1+$byte_count, strlen($buffer)-1-$byte_count) . "\n";
      $color_number++;
    }
  }
  
  return(array($width, $height, $color_depth, $palette));
}

//-----------------------------------------------------------------------------
// make_pal - creates a palette string
//-----------------------------------------------------------------------------
function make_pal($color0, $color1, $color2, $color3, $color4, $color5, $color6, $color7, $color8, $color9, $colorA, $colorB, $colorC, $colorD, $colorE, $colorF) {
  $rgbquad = chr($color0[2]) . chr($color0[1]) . chr($color0[0]) . chr(0) .
             chr($color1[2]) . chr($color1[1]) . chr($color1[0]) . chr(0) .
             chr($color2[2]) . chr($color2[1]) . chr($color2[0]) . chr(0) .
             chr($color3[2]) . chr($color3[1]) . chr($color3[0]) . chr(0) .
             chr($color4[2]) . chr($color4[1]) . chr($color4[0]) . chr(0) .
             chr($color5[2]) . chr($color5[1]) . chr($color5[0]) . chr(0) .
             chr($color6[2]) . chr($color6[1]) . chr($color6[0]) . chr(0) .
             chr($color7[2]) . chr($color7[1]) . chr($color7[0]) . chr(0) .
             chr($color8[2]) . chr($color8[1]) . chr($color8[0]) . chr(0) .
             chr($color9[2]) . chr($color9[1]) . chr($color9[0]) . chr(0) .
             chr($colorA[2]) . chr($colorA[1]) . chr($colorA[0]) . chr(0) .
             chr($colorB[2]) . chr($colorB[1]) . chr($colorB[0]) . chr(0) .
             chr($colorC[2]) . chr($colorC[1]) . chr($colorC[0]) . chr(0) .
             chr($colorD[2]) . chr($colorD[1]) . chr($colorD[0]) . chr(0) .
             chr($colorE[2]) . chr($colorE[1]) . chr($colorE[0]) . chr(0) .
             chr($colorF[2]) . chr($colorF[1]) . chr($colorF[0]) . chr(0);
  return($rgbquad);
}

//-----------------------------------------------------------------------------
// bitmapheader_xbpp - writes a valid x color bitmap header
//-----------------------------------------------------------------------------
function bitmapheader_xbpp($length, $width, $height, $palette) {
  // Base of the bitmap header
  $header = "BM" . pack("V*", $length+0x76) . pack("V*", 0) . pack("V*", 0x76);
  
  $info_header = pack("V*", 0x28) . pack("V*", $width) . pack("V*", $height) .
                 pack("v*", 1) . pack("v*", 4) . pack("V*", 0) . pack("V*", $length) .
                 pack("V*", 0) . pack("V*", 0) . pack("V*", 16) . pack("V*", 0);

  return($header . $info_header . $palette);
}

//-----------------------------------------------------------------------------
// getbmpscale - gets the dimensions of a bmp for scaling
//-----------------------------------------------------------------------------
function getbmpinfo($in_file) {
  $fd = fopen($in_file, "rb");
  fseek($fd, 0x12, SEEK_SET);
  $img_width  = hexdec(bin2hex(strrev(fread($fd, 4))));
  fseek($fd, 0x16, SEEK_SET);
  $img_height = hexdec(bin2hex(strrev(fread($fd, 4))));
  fseek($fd, 0x1c, SEEK_SET);
  $bpp = hexdec(bin2hex(strrev(fread($fd, 2))));
  fclose($fd);
  return(array($img_width, $img_height, $bpp));
}

//-----------------------------------------------------------------------------
// makevwftile - autosizes a fixed tile for VWF
//-----------------------------------------------------------------------------
function makevwftile($tile, $width, $spacing){
  $wrapped = wordwrap($tile, $width, "\n", 1);
  $tilelines = split("\n", $wrapped);
  while($trimmed!=1) {
    for($i=0; $i<count($tilelines); $i++){
      if(substr($tilelines[$i], strlen($tilelines[$i])-1, 1)=="1")
        $trimmed=1;
    }
    if($trimmed!=1)
      for($i=0; $i<count($tilelines); $i++)
        $tilelines[$i] = substr($tilelines[$i], 0, strlen($tilelines[$i])-1);
    if(strlen($tilelines[0])==0){
      for($i=0; $i<count($tilelines); $i++)
        for($k=0; $k<$spacing*3; $k++)
          $tilelines[$i].="0";
      $trimmed=1;
    }
  }
  for($i=0; $i<count($tilelines); $i++)
    for($k=0; $k<$spacing; $k++)
      $tilelines[$i].="0";
  $output="";
  for($i=0; $i<count($tilelines); $i++)
    $output .= $tilelines[$i];
  return(array($output, strlen($tilelines[0])));
}

//-----------------------------------------------------------------------------
// merge_two_planes - combine 2 layers to one four-color string
//-----------------------------------------------------------------------------
function merge_two_planes($plane1, $plane2) {
  $output = "";
  for($i=0; $i<strlen($plane1); $i++)
    $output.=bindec($plane2[$i] . $plane1[$i]);
  return($output);
}

//-----------------------------------------------------------------------------
// merge_three_planes - combine 3 layers to one eight-color string
//-----------------------------------------------------------------------------
function merge_three_planes($plane1, $plane2, $plane3) {
  $output = "";
  for($i=0; $i<strlen($plane1); $i++)
    $output.=bindec($plane3[$i] . $plane2[$i] . $plane1[$i]);
  return($output);
}

//-----------------------------------------------------------------------------
// merge_four_planes - combine 4 layers to one sixteen-color string
//-----------------------------------------------------------------------------
function merge_four_planes($plane1, $plane2, $plane3, $plane4) {
  $output = "";
  for($i=0; $i<strlen($plane1); $i++)
    $output.=dechex(bindec($plane4[$i] . $plane3[$i] . $plane2[$i] . $plane1[$i]));
  return($output);
}

//-----------------------------------------------------------------------------
// demux_two_planes - split a color pixel to two planes
//-----------------------------------------------------------------------------
function demux_two_planes($bitbit) {
  $output=str_pad(decbin(hexdec($bitbit)), 2, "0", STR_PAD_LEFT);
  return(array(substr($output, 1, 1), substr($output, 0, 1)));
}

//-----------------------------------------------------------------------------
// demux_three_planes - split a color pixel to three planes
//-----------------------------------------------------------------------------
function demux_three_planes($bitbit) {
  $output=str_pad(decbin(hexdec($bitbit)), 3, "0", STR_PAD_LEFT);
  return(array(substr($output, 2, 1), substr($output, 1, 1), substr($output, 0, 1)));
}

//-----------------------------------------------------------------------------
// demux_four_planes - split a color pixel to four planes
//-----------------------------------------------------------------------------
function demux_four_planes($bitbit) {
  $output=str_pad(decbin(hexdec($bitbit)), 4, "0", STR_PAD_LEFT);
  return(array(substr($output, 3, 1), substr($output, 2, 1), substr($output, 1, 1), substr($output, 0, 1)));
}

?>
