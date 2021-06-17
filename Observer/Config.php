<?php
namespace Magenizr\Faker\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;

class Config implements ObserverInterface
{
    private $request;

    private $configWriter;

    private $fileSystemIo;

    private $dir;

    /**
     * Config constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param \Magento\Framework\Filesystem\Io\File $fileSystemIo
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     */
    public function __construct(
        RequestInterface $request,
        WriterInterface $configWriter,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Framework\Filesystem\Io\File $fileSystemIo,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->request = $request;
        $this->configWriter = $configWriter;
        $this->driverFile = $driverFile;
        $this->fileSystemIo = $fileSystemIo;
        $this->dir = $dir;
    }

    public function execute(EventObserver $observer)
    {
        $params = $this->request->getParam('groups');
        $fields = $params['magenizr_faker']['fields'];

        if (! empty($fields['csv_customers'])) {

            $file = $fields['csv_customers']['value'];

            $file = $this->driverFile->getAbsolutePath(
                $this->dir->getRoot() . DIRECTORY_SEPARATOR,
                $file
            );

            if (!$this->driverFile->isReadable($file)) {

                throw new LocalizedException(__('The provided CSV file <strong>%1</strong> does not exist.', $file));
            }
        }

        return $this;
    }
}
