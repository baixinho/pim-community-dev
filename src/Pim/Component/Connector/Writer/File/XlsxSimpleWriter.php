<?php

namespace Pim\Component\Connector\Writer\File;

use Pim\Component\Connector\ArchiveDirectory;

/**
 * Write simple data into a XLSX file on the local filesystem
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XlsxSimpleWriter extends AbstractFileWriter implements ArchivableWriterInterface
{
    /** @var FlatItemBuffer */
    protected $flatRowBuffer;

    /** @var FlatItemBufferFlusher */
    protected $flusher;

    /** @var array */
    protected $writtenFiles;

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param ArchiveDirectory          $archiveDirectory
     * @param FlatItemBuffer            $flatRowBuffer
     * @param FlatItemBufferFlusher     $flusher
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        ArchiveDirectory $archiveDirectory,
        FlatItemBuffer $flatRowBuffer,
        FlatItemBufferFlusher $flusher
    ) {
        parent::__construct($filePathResolver, $archiveDirectory);

        $this->flatRowBuffer = $flatRowBuffer;
        $this->flusher       = $flusher;
        $this->writtenFiles  = [];
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $exportFolder = dirname($this->getPath());
        if (!is_dir($exportFolder)) {
            $this->localFs->mkdir($exportFolder);
        }

        $parameters = $this->stepExecution->getJobParameters();
        $withHeader = $parameters->get('withHeader');
        $this->flatRowBuffer->write($items, $withHeader);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->flusher->setStepExecution($this->stepExecution);

        $fileDirectory = $this->archiveDirectory->getAbsolute($this->stepExecution->getJobExecution());
        $filePath = $fileDirectory . $this->getFilename();

        $writtenFiles = $this->flusher->flush(
            $this->flatRowBuffer,
            $this->stepExecution->getJobParameters()->get('linesPerFile'),
            $filePath,
            $this->filePathResolverOptions
        );

        foreach ($writtenFiles as $writtenFile) {
            $this->writtenFiles[$writtenFile] = basename($writtenFile);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWrittenFiles()
    {
        return $this->writtenFiles;
    }
}
