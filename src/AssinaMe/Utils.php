<?php

declare(strict_types = 1);

namespace AssinaMe;

class Utils {
    public static function atomicPutContents(string $filename, $contents, int $flags = 0) {
        $tmpFilename = sprintf('%s~', $filename);
        if (file_put_contents($tmpFilename, $contents, $flags) === strlen($contents)) {
            return rename($tmpFilename, $filename);
        }

        @unlink($tmpFilename);
        return false;
    }
}
