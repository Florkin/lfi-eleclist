<?php

/**
 * PdfHandler
 *
 * @author    Tristan Florin <tristan.florin@smile.fr>
 * @copyright 2022 Smile
 */

namespace App\Handler;

use Knp\Snappy\Pdf;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class PdfHandler
{
    private Pdf $knpSnappyPdf;
    private array $pdfConfig;
    private Environment $twig;
    private Filesystem $filesystem;

    public function __construct(Pdf $knpSnappyPdf, Environment $twig, Filesystem $filesystem, array $pdfConfig = [])
    {
        $this->knpSnappyPdf = $knpSnappyPdf;
        $this->pdfConfig = $pdfConfig;
        $this->twig = $twig;
        $this->filesystem = $filesystem;
    }

    public function generatePdf(iterable $streets, string $city, string $filename)
    {
        if ($this->filesystem->exists($filename)) {
            $this->filesystem->remove($filename);
        }

        $this->knpSnappyPdf->setOption('footer-right','[page]');
        $this->knpSnappyPdf->setTimeout(300);
        $this->knpSnappyPdf->setOption('lowquality', false);
        $this->knpSnappyPdf->generateFromHtml(
            $this->twig->render('pdf.html.twig', [
                'streets' => $streets,
                'city' => strtoupper($city)
            ]),
            $filename
        );
    }
}
