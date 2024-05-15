<?php

class RequestApi {

    public $url;
    public $version;

    public function api($request_data) {
        $url = $this->url;

        if (empty($request_data['no_version'])) {
            $url .= '/' . $this->version;
        }

        $url .= '/' . $request_data['endpoint'];

        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        );

        if (!empty($request_data['content_type'])) {
            $content_type = $request_data['content_type'];
        } else {
            $content_type = 'application/json';
        }

        // handle method and parameters
        if (isset($request_data['parameters']) && is_array($request_data['parameters']) && count($request_data['parameters'])) {
            $params = $this->encodeParameters($request_data['parameters'], $content_type);
        }elseif(isset($request_data['parameters']) && !empty($request_data['parameters']) && $request_data['method'] == "GET"){
            $params = $request_data['parameters'];
        } else {
            $params = null;
        }

        switch ($request_data['method']) {
            case 'GET' :
                $curl_options[CURLOPT_POST] = false;
                
                if (is_string($params)) {
                    $curl_options[CURLOPT_URL] .= ((strpos($url, '?') === false) ? '?' : '&') . $params;
                }
                
                break;
            case 'POST' :
                $curl_options[CURLOPT_POST] = true;

                if ($params !== null) {
                    $curl_options[CURLOPT_POSTFIELDS] = $params;
                }

                break;
            default : 
                $curl_options[CURLOPT_CUSTOMREQUEST] = $request_data['method'];

                if ($params !== null) {
                    $curl_options[CURLOPT_POSTFIELDS] = $params;
                }

                break;
        }

        // handle headers
        $added_headers = array();

        if (!empty($request_data['auth_type'])) {
            if (empty($request_data['token'])) {
                if ($this->config->get('payment_squareup_enable_sandbox')) {
                    $token = $this->config->get('payment_squareup_sandbox_token');
                } else {
                    $token = $this->config->get('payment_squareup_access_token');
                }
            } else {
                // custom token trumps sandbox/regular one
                $token = $request_data['token'];
            }
            
            $added_headers[] = 'Authorization: ' . $request_data['auth_type'] . ' ' . $token;
        }

        if (!is_array($params)) {
            // curl automatically adds Content-Type: multipart/form-data when we provide an array
            $added_headers[] = 'Content-Type: ' . $content_type;
        }

        if (isset($request_data['headers']) && is_array($request_data['headers'])) {
            $curl_options[CURLOPT_HTTPHEADER] = array_merge($added_headers, $request_data['headers']);
        } else {
            $curl_options[CURLOPT_HTTPHEADER] = $added_headers;
        }

        /*$this->debug("SQUAREUP DEBUG START...");
        $this->debug("SQUAREUP ENDPOINT: " . $curl_options[CURLOPT_URL]);
        $this->debug("SQUAREUP HEADERS: " . print_r($curl_options[CURLOPT_HTTPHEADER], true));
        $this->debug("SQUAREUP PARAMS: " . $params);*/

        // Fire off the request
        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        $result = curl_exec($ch);
        
        if ($result) {
            //$this->debug("SQUAREUP RESULT: " . $result);

            curl_close($ch);

            $return = json_decode($result, true);

            return $return;
        } else {
            $info = curl_getinfo($ch);

            curl_close($ch);

            throw new \Exception("CURL error. Info: " . print_r($info, true), true);
        }
    }

    protected function encodeParameters($params, $content_type) {
        switch ($content_type) {
            case 'application/json' :
                return json_encode($params);
            case 'application/x-www-form-urlencoded' :
                return http_build_query($params);
            default :
            case 'multipart/form-data' :
                // curl will handle the params as multipart form data if we just leave it as an array
                return $params;
        }
    }
}