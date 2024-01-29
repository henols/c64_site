<?php
// Default directory path
$rootDirectory = "."; // Replace with your root directory path
$currentDirectory = isset($_GET['dir']) ? $_GET['dir'] : $rootDirectory;

// Configurable file types
$fileTypes = ["prg", "d64"]; // Add more file types as needed

// Function to check if directory contains files of specified types
function hasSpecifiedFiles($dir, $types) {
    $hasFile = false;
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false && !$hasFile) {
            if (in_array(pathinfo($file, PATHINFO_EXTENSION), $types)) {
                $hasFile = true;
            }
        }
        closedir($dh);
    }
    return $hasFile;
}

// Function to list directories and files
function listDir($dir, $root, $types) {
    $directories = [];
    $files = [];

    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            // Link to parent directory if not in root
            if ($dir != $root) {
                $parentDir = dirname($dir);
                echo "<li><a href='?dir=" . urlencode($parentDir) . "'>Back to previous directory</a></li><br>";
            }

            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != "..") {
                    if (is_dir("$dir/$file") && hasSpecifiedFiles("$dir/$file", $types)) {
                        $directories[] = $file;
                    } elseif (in_array(pathinfo($file, PATHINFO_EXTENSION), $types)) {
                        $files[] = $file;
                    }
                }
            }
            closedir($dh);
        }
    }

    // Sort and display directories and files
    sort($directories);
    foreach ($directories as $directory) {
        echo "<li><a href='?dir=" . urlencode("$dir/$directory") . "'>$directory</a></li>";
    }

    sort($files);
    foreach ($files as $file) {
        echo "<li><a href='$dir/$file'>$file</a></li>";
    }
}

// List directories containing specified file types and files in the current directory
listDir($currentDirectory, $rootDirectory, $fileTypes);
?>
