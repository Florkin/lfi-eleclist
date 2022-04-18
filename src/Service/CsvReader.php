<?php
/**
 * CsvReader.php
 *
 * @author    Tristan Florin <tristan.florin@smile.fr>
 * @copyright 2022 Smile
 */

namespace App\Service;

use League\Csv\Reader;

class CsvReader
{
    public function getFile(string $path)
    {
        $file = Reader::createFromPath('%kernel.root_dir%/../' . $path, "r");
        $file
            ->setDelimiter(";")
            ->setHeaderOffset(0);

        return $file;
    }
}
