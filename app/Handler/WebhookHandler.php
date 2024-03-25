<?php

namespace App\Handler;

use App\Models\Product;
use App\Models\User;
use App\Models\WebhookUrl;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use Spatie\WebhookServer\WebhookCall;
use Illuminate\Support\Str;
use Exception;

class WebhookHandler extends ProcessWebhookJob
{
    public function handle()
    {
        logger('Webhook call started.');
        $payload = json_decode($this->webhookCall, true)['payload'];
        logger($payload);
        if (isset($payload['to'])) {
            $data = new WebhookUrl;
            $data->from_url = $payload['from'];
            $data->to_url = $payload['to'];
            $data->save();
        } else {
            foreach ($payload as $item) {
                logger('item is');
                logger($item);
                $existingProduct = Product::where('code', $item['code'])->first();
                if ($existingProduct) {
                    if (isset($item['key'])) {
                        $this->deleteProduct($existingProduct);
                    } else {
                        $this->updateProduct($existingProduct, $item);
                    }
                } else {
                    $this->createProduct($item);
                }
            }
        }
    }

    //     // method for creating new product
    protected function createProduct($count)
    {
        logger("create product");
        try {
            if (isset($count['code'])) {
                $data = new Product;

                $data->code = $count['code'];
                $data->name = $count['name'];
                $data->quantity = $count['quantity'];
                $data->price = $count['price'];
                $data->description = $count['description'];
                $data->save();
                // WebhookCall::create()
                //     ->url('http://127.0.0.1:8002/webhooks')
                //     ->payload([$data])
                //     ->useSecret('three')
                //     ->dispatch();
            } else {
                logger("else part");
            }
        } catch (Exception $e) {
            logger("Webhook failed for create product");
            logger($e->getMessage());
        }
    }

    // method for updating existing user
    protected function updateProduct($data, $count)
    {
        logger("update product");
        logger($count);
        try {

            $data->code = $count['code'];
            $data->name = $count['name'];
            $data->quantity = $count['quantity'];
            $data->price = $count['price'];
            $data->description = $count['description'];
            $data->save();
        } catch (Exception $e) {
            logger("webhook fail for update product");
            logger($e->getMessage());
        }
    }
    //     // method for deleting existing user
    protected function deleteProduct($data)
    {
        logger("delete product");
        $result = $data->delete();
        if ($result) {
            logger("data deleted successfully");
        } else {
            logger("Error to deleted the data");
        }
    }
}
