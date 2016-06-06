<?php

namespace AlanKent\AlexaStoreOnline\App;

use AlanKent\Alexa\App\AlexaApplicationInterface;
use AlanKent\Alexa\App\CustomerDataInterface;
use AlanKent\Alexa\App\ResponseData;
use AlanKent\Alexa\App\ResponseDataFactory;
use AlanKent\Alexa\App\SessionDataInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Application to check if store is online.
 */
class StoreOnlineAlexApp implements AlexaApplicationInterface
{
    /** @var ResponseDataFactory */
    private $responseDataFactory;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /**
     * StoreOnlineAlexApp constructor.
     * @param ResponseDataFactory $responseDataFactory
     */
    public function __construct(
        ResponseDataFactory $responseDataFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->responseDataFactory = $responseDataFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     */
    public function launchRequest(SessionDataInterface $sessionData,
                                  CustomerDataInterface $customerData)
    {
        $response = $this->responseDataFactory->create();
        $response->setShouldEndSession(true);
        return $response;
    }

    /**
     * @inheritdoc
     */
    public function intentRequest(SessionDataInterface $sessionData,
                                  CustomerDataInterface $customerData,
                                  $intentName,
                                  $slots)
    {
        $response = $this->responseDataFactory->create();

        if ($intentName == 'ReportOrderCount') {
            $search = new \Magento\Framework\Api\SearchCriteria();
            $orders = $this->orderRepository->getList($search);
            $numOrders = $orders->getTotalCount();
            $response->setResponseText("Your have $numOrders orders.");
            $response->setCardSimple("Store Status", "You currently have $numOrders orders.");
        }
        $response->setShouldEndSession(true);
        return $response;
    }

    /**
     * @inheritdoc
     */
    public function endSession(SessionDataInterface $sessionData,
                               CustomerDataInterface $customerData,
                               $reason)
    {
        $response = $this->responseDataFactory->create();
        $response->setShouldEndSession(true);
        return $response;
    }
}