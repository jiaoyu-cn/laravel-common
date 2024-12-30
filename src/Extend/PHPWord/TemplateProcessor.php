<?php

namespace Githen\LaravelCommon\Extend\PHPWord;

use Githen\LaravelCommon\Extend\PHPWord\Writer\Word2007\Part\Chart;
use PhpOffice\PhpWord\Shared\XMLWriter;
use PhpOffice\PhpWord\Element\AbstractElement;

class TemplateProcessor extends \PhpOffice\PhpWord\TemplateProcessor
{
    /**
     * @param string $search
     */
    public function setChart($search, AbstractElement $chart): void
    {
        $elementName = substr(get_class($chart), strrpos(get_class($chart), '\\') + 1);
        $objectClass = 'PhpOffice\\PhpWord\\Writer\\Word2007\\Element\\' . $elementName;

        // Get the next relation id
        $rId = $this->getNextRelationsIndex($this->getMainPartName());
        $chart->setRelationId($rId);

        // Define the chart filename
        $filename = "charts/chart{$rId}.xml";

        // Get the part writer
        $writerPart = new Chart();
        $writerPart->setElement($chart);

        // ContentTypes.xml
        $this->zipClass->addFromString("word/{$filename}", $writerPart->write());

        // add chart to content type
        $xmlRelationsType = "<Override PartName=\"/word/{$filename}\" ContentType=\"application/vnd.openxmlformats-officedocument.drawingml.chart+xml\"/>";
        $this->tempDocumentContentTypes = str_replace('</Types>', $xmlRelationsType, $this->tempDocumentContentTypes) . '</Types>';

        // Add the chart to relations
        $xmlChartRelation = "<Relationship Id=\"rId{$rId}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/chart\" Target=\"charts/chart{$rId}.xml\"/>";
        $this->tempDocumentRelations[$this->getMainPartName()] = str_replace('</Relationships>', $xmlChartRelation, $this->tempDocumentRelations[$this->getMainPartName()]) . '</Relationships>';

        // Write the chart
        $xmlWriter = new XMLWriter();
        $elementWriter = new $objectClass($xmlWriter, $chart, true);
        $elementWriter->write();

        // Place it in the template
        $this->replaceXmlBlock($search, '<w:p>' . $xmlWriter->getData() . '</w:p>', 'w:p');
    }
}
