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
    /** @var array */
    private array $params;

    /** @var Reader */
    private Reader $reader;

    /**
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function getRecords(string $path): iterable
    {
        $reader = $this->mapHeader(Reader::createFromPath('%kernel.root_dir%/../' . $path, "r"));
        $reader
            ->setDelimiter(',')
            ->setHeaderOffset(0);

        $this->reader = $reader;

        return $this->reader->getRecords();
    }

    private function mapHeader(Reader $file)
    {
        $csvStr = $file->toString();
        $headers = $this->params['column_headers'];

        $mapped = str_replace($headers, array_keys($headers), $csvStr);

        return Reader::createFromString($mapped);
    }

    public function getReader()
    {
        return $this->reader;
    }
}
