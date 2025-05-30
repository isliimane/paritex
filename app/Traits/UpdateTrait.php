<?php

namespace App\Traits;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

trait UpdateTrait
{

    private $files = [];

    public function delete_directory($dirname,$is_remove = true): bool
    {
        $dir_handle = '';
        if (is_dir($dirname))
            $dir_handle = opendir($dirname);
        if (!$dir_handle)
            return false;
        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != ".." && $file != ".gitignore") {
                if (!is_dir($dirname . "/" . $file))
                    unlink($dirname . "/" . $file);
                else
                    $this->delete_directory($dirname . '/' . $file);
            }
        }
        closedir($dir_handle);
        if ($is_remove) {
            rmdir($dirname);
        }
        return true;
    }

}
