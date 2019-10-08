<?php
namespace Eusonlito\LaravelProcessor\Library;

use Request;

trait FilesTrait
{
    public static function saveFormFiles($form, &$data, $folder)
    {
        foreach ($form as $name => $input) {
            if ($input->attr('type') !== 'file') {
                continue;
            }

            if ($file = request()->file($name)) {
                $data[$name] = self::saveFile($file, $folder.'/'.$name);
            } elseif (array_key_exists($name, $data)) {
                unset($data[$name]);
            }
        }

        return $data;
    }

    public static function saveFormFilesName($name, $folder)
    {
        $files = array_filter(request()->file($name));

        if (empty($files)) {
            return [];
        }

        $data = [];

        foreach ($files as $file) {
            $data[] = self::saveFile($file, $folder);
        }

        return array_values(array_filter($data));
    }

    public static function securePath($path)
    {
        return preg_replace('/(\\|\/\.{2,}\/)/', '', $path);
    }

    public static function saveFile($file, $folder, $name = null)
    {
        $folder = strtolower(self::securePath($folder));
        $storage = self::getStoragePath($folder);

        if (!is_dir($storage)) {
            mkdir($storage, 0755, true);
        }

        if (is_object($file)) {
            $name = $file->getClientOriginalName();
        } elseif (empty($name)) {
            $name = basename($file);
        }

        $name = preg_replace('/[^\w\.]/', '-', strtolower($name));
        $name = preg_replace('/\-+/', '-', $name);
        $name = substr(uniqid(), 2, 8).'-'.$name;

        copy($file, $storage.'/'.$name);

        return $folder.'/'.$name;
    }

    public static function deleteOldFiles($form, $model)
    {
        foreach ($form as $name => $input) {
            if ($input->attr('type') !== 'file') {
                continue;
            }

            $value = $input->val();

            if (is_array($value) && !empty($value['name']) && $model->$name) {
                self::deleteFile($model->$name);
            }
        }
    }

    protected static function deleteFile($file)
    {
        $file = self::getStoragePath(self::securePath($file));

        if (is_file($file)) {
            return unlink($file);
        }

        return true;
    }

    protected static function getRelativeStoragePath($file)
    {
        return 'storage/resources/'.$file;
    }

    protected static function getStoragePath($file)
    {
        return public_path(self::getRelativeStoragePath($file));
    }
}
