<?php

namespace TARGOBANK\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;

class ReturnController extends Controller
{
    private $orderRepository;

    public function __construct(OrderRepositoryContract $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function handleReturn(Request $request, Response $response)
    {
        $paymentStatus = $request->get('paid');
        $orderNumber = $request->get('orderNumber');

        if ($paymentStatus == "PAID") {
            $this->updateOrderStatus($orderNumber, 'approved');
        } elseif ($paymentStatus == "NO") {
            $this->updateOrderStatus($orderNumber, 'rejected');
        }

        return $response->redirect('/thank-you');
    }

    private function updateOrderStatus($orderNumber, $status)
    {
        $order = $this->orderRepository->findByNumber($orderNumber);
        if ($order instanceof Order) {
            $order->status = $status;
            $this->orderRepository->updateOrder($order);
            $this->sendNotification($order, $status);
        }
    }

    private function sendNotification($order, $status)
    {
        $email = $order->ownerContact->email;
        $subject = "Order Status Update";
        $message = "The status of your order has been updated:" . $status;
        mail($email, $subject, $message);
    }
}
