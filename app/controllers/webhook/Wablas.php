<?php defined('BASEPATH') or exit('No direct script access allowed');

use Wablas\WablasClient;

class Wablas extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function webhook()
    {
        $this->load->model('integration_model', 'integration');
        $data_log = [
            'method' => $this->input->method(true),
            'url' => current_url(),
            'headers' => json_encode($this->input->request_headers()),
            'body' => json_encode($this->input->post()),
            'parameters' => json_encode($this->input->get()),
            'io_type' => 'in',
            'ssl_status' => true,
            'response' => null,
            'note' => null
        ];
        $this->integration->insertApiLog($data_log);

        header('Content-Type: application/json');
        try {
            if(!$this->isPost()) {
                throw new Exception("Gunakan metode `POST`");
            }

            $apiToken = 'Fhkhb4t9MRxt26YIHo3L9mYpR0NDflSKYK2dqyhAsHQGVomgIKlxhqS2WP6CwoRQ';
            $endpoint = 'https://sambi.wablas.com';
            $wablasClient = new WablasClient($endpoint, $apiToken);

            // add recipient (support multiple recipient)
            $wablasClient->addRecipient($this->input->post('phone'));

            // send message
            $message = $this->input->post('message');
            $wablasClient->sendMessage($message);

            $code = 200;
        } catch (\Throwable $th) {
            $code = $th->getCode();
            $response = [
                'code' => $code,
                'message' => $th->getMessage()
            ];
        }
        http_response_code ($code);
        if ($response) {
            echo json_encode($response);
        } else {
            echo "Pesan Diterima : " . $this->input->post('message');
        }
    }
}
