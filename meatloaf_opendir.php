<?php
//
// Meatloaf - A Commodore 64/128 multi-device emulator
// https://github.com/idolpx/meatloaf
// Copyright(C) 2022 James Johnston
//
// Meatloaf Server Script-----------------------------------------
// Create a directory listing as a Commodore Basic Program
// Responds with binary PRG file ready to load and list
// ---------------------------------------------------------------
//
// Meatloaf is free software : you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Meatloaf is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Meatloaf. If not, see <http://www.gnu.org/licenses/>.
//

//
// https://gist.github.com/idolpx/ab8874f8396b6fa0d89cc9bab1e4dee2
//

// This header will be displayed at the top of directory listings
// It should be no longer than 16 characters
$header = substr("MEATLOAF ARCHIVE", 0, 16);

/////////////////////////////////////////////////////////////////

$basic_start = 0x0401;
$next_entry = $basic_start;

$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = $_SERVER['SERVER_NAME'];

$dir = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$dir = urldecode($dir);


function get_type($name)
{
    global $root;

    if(is_dir($root.$name))
    {
        $ext = "DIR";
    }
    else
    {
        $ext = pathinfo($root.$name, PATHINFO_EXTENSION);
        if (strlen($ext) < 3)
            $ext = "PRG";
    }
    return strtoupper($ext);
}

function sendLine($blocks, $line)
{
    global $next_entry;

    $line .= "\x00";
    //$next_entry = $next_entry + 4 + strlen($line);
    //echo pack('v', $next_entry);
	echo pack('v', 0x0101);
    echo pack('v', $blocks);
    echo strtoupper("$line");
}

function sendListing($dir, $exp)
{
    global $url, $root, $basic_start, $header;

    // Send basic load address
    echo pack('v', $basic_start);

    // Send List HEADER
    sendLine(0, "\x12\"$header\" 08 2A");

    //echo "[$dir]"; exit();
	$directory = preg_filter($exp, '$0', scandir($root.$dir));

    // Send Extra INFO
    sendLine(0, sprintf("\"%-19s\" NFO", "[URL]"));
    sendLine(0, sprintf("\"%-19s\" NFO", $url));
    if (strlen($dir) > 1)
    {
        sendLine(0, sprintf("\"%-19s\" NFO", "[PATH]"));
        sendLine(0, sprintf("\"%-19s\" NFO", $dir));
    }
    sendLine(0, "\"-------------------\" NFO");

    // Send file entries
    foreach($directory as $key => $file) {
        $stat = stat("$root$dir/$file");
        $type = get_type("$dir/$file");
        $blocks = 0;
        $block_spc = 3;
        if ( $type != "DIR" ) {
            $blocks = ceil($stat['size']/256);
            if ($blocks > 9) $block_spc--;
            if ($blocks > 99) $block_spc--;
        }
        $line = sprintf("%s%-18s %s", str_repeat(" ", $block_spc), "\"".$file."\"", $type);
        sendLine( $blocks, $line );
    }

    sendLine( 65535, "BLOCKS FREE" );

    // Send 0000 to end basic program
    echo "\x00\x00";
}

//Set Content Type
header('Content-Type: application/octet-stream');

//Use Content-Disposition: attachment to specify the filename
header('Content-Disposition: attachment; filename="index.prg"');
header('Meatloaf-Debug: '.$dir);

sendListing($dir, '/(?!^\..*?$|^.*?.html|^.*?.php|^api$|^web.config$)^.*?$/i');

?>
