<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Angel\Test\Model\QoH;

use Angel\QoH\Model\RandomNumberGenerate;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Angel\QoH\Model\Card\Options;

/**
 * Class ConvertToCsv
 */
class ConvertToCsv
{
    private $filesystem;
    protected $directory;

    public function __construct(
        Filesystem $filesystem
    ){
        $this->filesystem = $filesystem;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * @param $params
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCsvFile($params)
    {
        $name = md5(microtime());
        $file = 'export/random_numbers_' . $name . '.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $total_time = (int)$params['total_time'];
        $total_ticket = (int)$params['total_ticket'];
        $stream->writeCsv([__('Total Tickets'), $total_time]);
        $stream->writeCsv([__('Total Tickets per time'), $total_ticket]);

        for ($i = 0; $i < $total_time; $i++){
            $stream->writeCsv($this->massDrawCard($total_ticket));
        }

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }

    /**
     * @param int $total_ticket
     * @return array
     */
    public function massDrawCard($total_ticket){
        $start = 1;
        $end = $total_ticket;
        $cards = Options::getOptionArray();
        $drawnCards = [];

        try {
            $winningNumber = RandomNumberGenerate::getWinningNumber($start, $end);
            $card = RandomNumberGenerate::drawCard($drawnCards);
            $result[] = $winningNumber;
            $result[] = $cards[$card];
            $drawnCards[] = $card;

            while (!Options::isQoH($card)) {
                $start += $total_ticket;
                $end += $total_ticket;

                $winningNumber = RandomNumberGenerate::getWinningNumber($start, $end);
                $card = RandomNumberGenerate::drawCard($drawnCards);
                $result[] = $winningNumber;
                $result[] = $cards[$card];
//                $result[] = $winningNumber . ' - ' . $cards[$card];
                $drawnCards[] = $card;
            }
        } catch (\Exception $e){
            return [];
        }
        return $result;
    }

}
