<?php

class KuvapankkiAPI
{
    private static $instance;

    public $baseUrl;
    private $apiUrl = '/api/v2/';
    private $token;

    public static function get_instance()
    {
        if (!static::$instance) {
            static::$instance = new static;
        }
        
        return static::$instance;
    }

    public function __construct()
    {
        // Get Base URL from settings
        $this->baseUrl = kuvapankki_url();
        
        // Authenticate to get API Token
        $data = $this->authenticate(kuvapankki_username(), kuvapankki_password());
        if ($data && isset($data['data']) && isset($data['data']['token'])) {
            $this->token = $data['data']['token'];
        }
    }

    public function authed()
    {
        return $this->token !== null;
    }

    public function call($url, $params, $method)
    {
        // Set headers
        $headers = [
            'Content-type: application/json',
            'Accept: application/json'
        ];

        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        // Set cURL options
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
            CURLOPT_ENCODING       => "",     // handle compressed
            CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT        => 120
        ];

        $method = strtolower($method);
        
        if ($method === 'get') {
            $url = $url . '?' . http_build_query($params);
        } else {
            $options[CURLOPT_POST] = true;
            $params = json_encode($params);
            $options[CURLOPT_POSTFIELDS] = $params;
            $headers[] = 'Content-Length: ' . strlen($params);
        }

        $options[CURLOPT_URL] = $this->baseUrl . $this->apiUrl . $url;
        $options[CURLOPT_HTTPHEADER] = $headers;
        
        $ch = curl_init();
        curl_setopt_array($ch, $options);

        // Get call contents
        $result = curl_exec($ch);
        curl_close($ch);
        
        if ($result === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        
        try {
            $result = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
        }

        return $result;
    }

    public function get($url, $params=[])
    {
        return $this->call($url, $params, 'GET');
    }

    public function post($url, $params=[])
    {
        return $this->call($url, $params, 'POST');
    }

    public function authenticate($username, $password)
    {
        return $this->post('authenticate', [
            'email' => $username,
            'password'  => $password
        ]);
    }

    public function search($params=[])
    {
        $defaults = [
            'categories' => [],
            'direction' => "desc",
            'extended' => false,
            'filterString' => "",
            'language' => "en",

            'orderBy' => "created_at",
            'page' => 1,
            'per_page' => 16,
            'products' => [
                'id' => "",
                'name' => "",
                'description' => ""
            ],
            'showArchived' => false
        ];

        $params = array_merge($defaults, $params);
        return $this->post('search', $params);
    }

    public function fields()
    {
        return $this->get('fields');
    }

    public function languages()
    {
        return $this->get('languages');
    }

    public function categories()
    {
        return $this->get('category/list');
    }

    public function file($id)
    {
        return $this->get('file/' . $id);
    }

    public function file_thumbnail($id)
    {
        return $this->get('file/' . $id . '/thumbnail');
    }

    public function products()
    {
        return $this->get('products');
    }

    public function user()
    {
        return $this->get('user');
    }
}
