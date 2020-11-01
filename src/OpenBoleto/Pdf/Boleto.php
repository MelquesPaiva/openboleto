<?php


namespace Source\OpenBoleto\Pdf;

use Exception;
use JasperPHP\Instructions;
use JasperPHP\PdfProcessor;
use JasperPHP\Report;
use Source\OpenBoleto\BoletoAbstract;
use TCPDF;

/**
 * Class Files
 * @package Source\OpenBoleto\Pdf
 */
class Boleto
{
    /** @var string */
    protected static string $pdfDestinyPath = __DIR__ . "/../../../resources/files";

    /** @var string */
    protected static string $xmlPath = __DIR__ . "/../../../samples/xml/bol01Files/boletoA4.jrxml";

    /** @var string */
    protected string $filename;

    /** @var BoletoAbstract */
    protected BoletoAbstract $billet;

    /** @var Exception|null */
    protected ?Exception $error = null;

    /** @var string|null */
    protected ?string $message = null;

    /** @var string */
    protected string $filePath;

    /** @var array */
    private array $data = [];

    /**
     * Boleto constructor.
     * @param string $filename
     * @param BoletoAbstract $billet
     */
    public function __construct(string $filename, BoletoAbstract $billet)
    {
        $filename = str_replace(".pdf", "", $filename);

        $this->filename = $filename . ".pdf";
        $this->filePath = self::$pdfDestinyPath . "/{$this->filename}";

        $billet->getOutput();

        $this->data = $billet->getData();
    }

    /**
     * @param string $destination
     * @return string|null
     */
    public function pdf(string $destination): ?string
    {
        if (!$this->required()) {
            return null;
        }

        try {
            $report = new Report(self::$xmlPath, []);

            Instructions::prepare($report);

            $report->dbData = [$this->billet];
            $report->generate([]);

            $report->out();

            /** @var TCPDF $pdf */
            $pdf = PdfProcessor::get();
            $pdf->Output($this->filename, $destination);
        } catch (\JasperPHP\Exception $e) {
            $this->error = $e;
            $this->message = $e->getMessage();
            return null;
        }

        return $this->filePath;
    }

    /**
     * @return Exception|null
     */
    public function failure(): ?Exception
    {
        return $this->error;
    }

    /**
     * @return string|null
     */
    public function message(): ?string
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    protected function required(): bool
    {
        if (empty($this->filename) || empty($this->billet)) {
            $this->message = "É necessário informar o nome do arquivo e o template html";
            return false;
        }

        return true;
    }
}
