<?php


namespace Angel\Test\Controller\Adminhtml\Qoh;

use Magento\Backend\App\Action\Context;
use Angel\Test\Model\QoH\ConvertToCsv;
use Magento\Framework\App\Response\Http\FileFactory;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @var ConvertToCsv
     */
    protected $converter;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @param Context $context
     * @param ConvertToCsv $converter
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        ConvertToCsv $converter,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Export data provider to CSV
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        return $this->fileFactory->create('queen_of_hearts_'.time().'.csv', $this->converter->getCsvFile($params), 'var');
    }
}
