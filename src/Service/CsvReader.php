<?php
/**
 * CsvReader.php
 *
 * @author    Tristan Florin <tristan.florin@smile.fr>
 * @copyright 2022 Smile
 */

namespace App\Service;

use League\Csv\CharsetConverter;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\Writer;
use Symfony\Component\Filesystem\Filesystem;

class CsvReader
{
    /** @var array */
    private array $params;

    /** @var Reader */
    private Reader $reader;

    /** @var string */
    private string $fileFolderPath;

    /** @var Filesystem */
    private Filesystem $filesystem;

    /**
     * @param Filesystem $filesystem
     * @param array $params
     * @param string $fileFolderPath
     */
    public function __construct(
        Filesystem $filesystem,
        array $params = [],
        string $fileFolderPath = ''
    ) {
        $this->params = $params;
        $this->fileFolderPath = $fileFolderPath;
        $this->filesystem = $filesystem;
    }

    public function mapHeader(string $delimiter = ',', int $offset = 0)
    {
        if (!isset($this->reader)) {
            throw new Exception('Reader is not created, please use CsvReader::createReader()');
        }

        $csvStr = $this->reader->toString();
        $headers = $this->params['column_headers'];

        $mapped = str_replace($headers, array_keys($headers), $csvStr);

        $this->reader = Reader::createFromString($mapped)
            ->setHeaderOffset($offset)
            ->setDelimiter($delimiter);
    }

    public function getReader(): Reader
    {
        return $this->reader;
    }

    public function createReader(string $filePath = '', string $delimiter = ',', int $offset = 0): Reader
    {
        $reader = Reader::createFromPath($filePath)
            ->setDelimiter($delimiter)
            ->setHeaderOffset($offset);

        $this->reader = $reader;
        return $this->reader;
    }

    public function saveCsv(string $newCsvString, string $delimiter = ',', int $offset = 0): string
    {
        $csv = Reader::createFromString($newCsvString)
            ->setHeaderOffset($offset)
            ->setDelimiter($delimiter);

        $this->filesystem->mkdir($this->fileFolderPath);
        $path = $this->fileFolderPath . '/enriched_list.csv';

        $file = Writer::createFromPath($path, 'w+');
        $file->insertOne($csv->getHeader());
        $file->insertAll($csv->getRecords());

        return $path;
    }

    public function delete(string $newFilePath)
    {
        $this->filesystem->remove($newFilePath);
    }
}
