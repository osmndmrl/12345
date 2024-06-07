<?php

namespace TARGOBANK\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use TARGOBANK\Services\HashService;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Frontend\Services\AccountService;

class PAYMENTController extends Controller
{
    private $hashService;
    private $orderRepository;
    private $accountService;

    public function __construct(HashService $hashService, OrderRepositoryContract $orderRepository, AccountService $accountService)
    {
        $this->hashService = $hashService;
        $this->orderRepository = $orderRepository;
        $this->accountService = $accountService;
    }

    public function initiatePayment(Request $request, Response $response)
    {
        $amount = $request->input('amount');
        $orderNumber = $request->input('orderNumber');
        $sessionID = session_id();
        $returnURL = $request->input('returnURL');
        $hash = $this->hashService->generateHash(
            "amount={$amount}&koop_id=villastore241&dealerText={$returnURL}&documentno={$orderNumber}&dealerShopURL=&dealerAbortURL=&dealerID=804625",
            'YOUR_SECRET_KEY'
        );

        return $response->json([
            'koop_id' => 'villastore241',
            'sessionID' => $sessionID,
            'amount' => $amount,
            'dealerID' => '804625',
            'dealerText' => $returnURL,
            'documentno' => $orderNumber,
            'hash' => $hash
        ]);
    }

    public function handleReturn(Request $request, Response $response)
    {
        $paymentStatus = $request->get('paid');
        $orderNumber = $request->get('orderNumber');

        // Siparişi al
        $order = $this->orderRepository->findOrderById($orderNumber);

        if ($paymentStatus == "PAID") {
            // Onaylanan veya beklemede olan durumu işleyin
            $order->statusId = 4; // Örneğin, sipariş durumunu 'Tamamlandı' olarak güncelle
            $this->orderRepository->updateOrder($order);

            // Kullanıcıya bildirim gönderin
            $customer = $this->accountService->getAccountByOrderId($orderNumber);
            $this->sendNotification($customer->contactEmail, 'Siparişiniz onaylandı', 'Siparişiniz başarıyla onaylanmıştır.');
        } elseif ($paymentStatus == "NO") {
            // Reddedilme durumunu işleyin
            $order->statusId = 6; // Örneğin, sipariş durumunu 'Reddedildi' olarak güncelle
            $this->orderRepository->updateOrder($order);

            // Kullanıcıya bildirim gönderin
            $customer = $this->accountService->getAccountByOrderId($orderNumber);
            $this->sendNotification($customer->contactEmail, 'Siparişiniz reddedildi', 'Siparişiniz maalesef reddedilmiştir.');
        }

        return $response->redirect('/thank-you');
    }

    private function sendNotification($to, $subject, $message)
    {
        // E-posta gönderim işlemleri
        mail($to, $subject, $message);
    }
}
