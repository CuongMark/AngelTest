<?php


namespace Angel\Test\Controller\Adminhtml\Raffle;

use Angel\Test\Model\Test\Type;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Angel\Test\Model\Raffle\ConvertToCsv;
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
        if ($params['type'] == Type::DISTRIBUTION){
            return $this->fileFactory->create('distribution_'.time().'.csv', $this->converter->getCsvFile($params), 'var');
        }
        return $this->fileFactory->create('random_number_'.time().'.csv', $this->converter->getCsvFile($params), 'var');
    }
}
