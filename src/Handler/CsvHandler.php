<?php

/**
 * CsvHandler
 *
 * @author    Tristan Florin <tristan.florin@smile.fr>
 * @copyright 2022 Smile
 */

namespace App\Handler;

use ArrayIterator;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\Writer;
use Symfony\Component\Filesystem\Filesystem;

class CsvHandler
{
    /** @var array */
    private array $params;

    /** @var Reader */
    private Reader $reader;

    /** @var array */
    private array $csvPaths;

    /** @var Filesystem */
    private Filesystem $filesystem;

    public function __construct(
        Filesystem $filesystem,
        array $params = [],
        array $csvPaths = []
    ) {
        $this->params = $params;
        $this->csvPaths = $csvPaths;
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

        $this->filesystem->mkdir($this->csvPaths['temp_file_folder']);
        $path = $this->csvPaths['temp_file_folder'] . '/enriched_list.csv';

        $file = Writer::createFromPath($path, 'w+');
        $file->insertOne($csv->getHeader());
        $file->insertAll($csv->getRecords());

        return $path;
    }

    public function archive(string $filePath)
    {
        $this->filesystem->mkdir($this->csvPaths['archives']);
        $this->filesystem->rename(
            $filePath,
            $this->csvPaths['archives'] . '/' . $this->generateDatedFilename('imported'),
            'w+'
        );
    }

    public function archiveFailedFromArray(array $records)
    {
        $this->filesystem->mkdir($this->csvPaths['archives_fails']);
        $writer = Writer::createFromPath(
            $this->csvPaths['archives_fails'] . '/' . $this->generateDatedFilename('fail'),
            'w+'
        );

        $writer->insertOne(array_keys($records[0]));
        $writer->insertAll(new ArrayIterator($records));
    }

    private function generateDatedFilename(string $prefix)
    {
        $datetime = new \DateTime('now');
        return $prefix . '_' . $datetime->format('d.m.Y_h:m:s') . '.csv';
    }
}
