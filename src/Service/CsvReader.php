<?php
/**
 * CsvReader.php
 *
 * @author    Tristan Florin <tristan.florin@smile.fr>
 * @copyright 2022 Smile
 */

namespace App\Service;

use League\Csv\Reader;
use League\Csv\Writer;

class CsvReader
{
    /** @var array */
    private array $params;

    /** @var Reader */
    private Reader $reader;

    /** @var string  */
    private string $fileFolder;

    /**
     * @param array $params
     * @param string $fileFolder
     */
    public function __construct(array $params = [], string $fileFolder = '')
    {
        $this->params = $params;
        $this->fileFolder = $fileFolder;
    }

    public function getRecords(string $csv): iterable
    {
        $reader = $this->mapHeader(Reader::createFromString($csv));
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

    public function saveCsv(string $newCsvString)
    {
        $csv = Reader::createFromString($newCsvString)
            ->setHeaderOffset(0);

        $file = Writer::createFromPath($this->fileFolder . '/enriched_list.csv', 'w+');
        $file->insertOne($csv->getHeader());
        $file->insertAll($csv->getRecords());
    }
}
