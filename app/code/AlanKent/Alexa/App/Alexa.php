<?php
namespace Akent\Alexa\App;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
class FrontController implements FrontControllerInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * FrontController constructor.
     * @param ResultFactory $resultFactory
     */
    public function __construct(ResultFactory $resultFactory)
    {
        $this->resultFactory = $resultFactory;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData(['alexa_response' => "I'm launched"]);
        return $result;
    }
}
