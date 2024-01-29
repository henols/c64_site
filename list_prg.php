<?php
// Default directory path
$rootDirectory = "."; // Replace with your root directory path
$currentDirectory = isset($_GET['dir']) ? $_GET['dir'] : $rootDirectory;

// Function to check if directory contains .prg files
function hasPrgFiles($dir) {
    $hasPrg = false;
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false && !$hasPrg) {
            if (pathinfo($file, PATHINFO_EXTENSION) == "prg") {
                $hasPrg = true;
            }
        }
        closedir($dh);
    }
    return $hasPrg;
}

// Function to list directories that contain .prg files and .prg files themselves
function listDir($dir, $root) {
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            // Link to parent directory if not in root
            if ($dir != $root) {
                $parentDir = dirname($dir);
                echo "<a href='?dir=" . urlencode($parentDir) . "'>Back to previous directory</a><br><br>";
            }

            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != "..") {
                    if (is_dir("$dir/$file") && hasPrgFiles("$dir/$file")) {
                        echo "<a href='?dir=" . urlencode("$dir/$file") . "'>$file</a><br>";
                    } elseif (pathinfo($file, PATHINFO_EXTENSION) == "prg") {
                        echo "<a href='$dir/$file'>$file</a><br>";
                    }
                }
            }
            closedir($dh);
        }
    }
}

// List directories containing .prg files and .prg files in the current directory
listDir($currentDirectory, $rootDirectory);
?>
