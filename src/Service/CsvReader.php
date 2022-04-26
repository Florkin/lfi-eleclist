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
use Symfony\Component\Filesystem\Filesystem;

class CsvReader
{
    /** @var array */
    private array $params;

    /** @var Reader */
    private Reader $reader;

    /** @var string */
    private string $fileFolderPath;

    /** @var Filesystem  */
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

    public function getRecords(string $filePath): iterable
    {
        $reader = $this->mapHeader(Reader::createFromPath($filePath));
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

    public function saveCsv(string $newCsvString): string
    {
        $csv = Reader::createFromString($newCsvString)
            ->setHeaderOffset(0);

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
