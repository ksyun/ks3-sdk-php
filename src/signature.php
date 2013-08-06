<?php

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\QueryString;
use Guzzle\Http\Url;

class signature 
{
    /**
     * @var array Query string values that must be signed
     */
    protected $signableQueryString = array(
        'acl', 'delete', 'lifecycle', 'location', 'logging', 'notification',
        'partNumber', 'policy', 'requestPayment', 'torrent', 'uploadId',
        'uploads', 'versionId', 'versioning', 'versions', 'website',
        'response-cache-control', 'response-content-disposition',
        'response-content-encoding', 'response-content-language',
        'response-content-type', 'response-expires', 'restore', 'tagging', 'cors'
    );

    /**
     * @var array Sorted headers that must be signed
     */
    protected $signableHeaders = array('Content-MD5', 'Content-Type');

    /**
     * {@inheritdoc}
     */
    public function signRequest(RequestInterface $request, array $credentials)
    {
        // Add a date header if one is not set
        if (!$request->hasHeader('date') && !$request->hasHeader('x-kss-date')) {
            $request->setHeader('Date', gmdate(DateTime::RFC2822));
        }

        $stringToSign = $this->createCanonicalizedString($request, $credentials);
        $request->getParams()->set('aws.string_to_sign', $stringToSign);
        $request->setHeader(
            'Authorization',
            'KSS ' . $credentials["crendit"]["access_id"] . ':' . $this->signString($stringToSign, $credentials["crendit"]["access_key"])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function signString($string, $credentials)
    {
        return base64_encode(hash_hmac('sha1', $string, $credentials, true));
    }

    /**
     * {@inheritdoc}
     */
    public function createCanonicalizedString(RequestInterface $request, array $req, $expires = null)
    {
        $buffer = $request->getMethod() . "\n";

        // Add the interesting headers
        foreach ($this->signableHeaders as $header) {
            $buffer .= (string) $request->getHeader($header) . "\n";
        }

        // Choose dates from left to right based on what's set
        $date = $expires ?: (string) $request->getHeader('date');

        $buffer .= "{$date}\n"
            . $this->createCanonicalizedAmzHeaders($request)
            . $this->createCanonicalizedResource($request, $req);

        return $buffer;
    }

    /**
     * Create a canonicalized AmzHeaders string for a signature.
     *
     * @param RequestInterface $request Request from which to gather headers
     *
     * @return string Returns canonicalized AMZ headers.
     */
    protected function createCanonicalizedAmzHeaders(RequestInterface $request)
    {
        $headers = array();
        foreach ($request->getHeaders(true) as $header) {
            /** @var $header \Guzzle\Http\Message\Header */
            $name = strtolower($header->getName());
            if (strpos($name, 'x-kss-') === 0) {
                $value = trim((string) $header);
                if ($value || $value === '0') {
                    $headers[$name] = $name . ':' . $value;
                }
            }
        }

        if (empty($headers)) {
            return '';
        } else {
            ksort($headers);

            return implode("\n", $headers) . "\n";
        }
    }
    
    protected function encodeKey($key)
    {
        return str_replace('%2F', '/', rawurlencode($key));
    }
    

    /**
     * Create a canonicalized resource for a request
     *
     * @param RequestInterface $request Request for the resource
     *
     * @return string
     */
    protected function createCanonicalizedResource(RequestInterface $request, array $req)
    {
        $buffer = $request->getParams()->get('query');
       
        // When sending a raw HTTP request (e.g. $client->get())
        if (null === $buffer) {
            $bucket = array_key_exists("bucket", $req) ? $req["bucket"]:null;
            $buffer = $bucket ? "/{$bucket}" : '';
            $path = $this->encodeKey(rawurldecode($request->getPath()));
        }
		
        // Remove double slashes
        $buffer = str_replace('//', '/', $buffer);
        $object = array_key_exists("object", $req) ? $req["object"]:null;
        if ($object != null){
        	$object = rawurlencode($object);
        }
        $buffer .= $object ? "/{$object}" : '';
      
        // Add sub resource parameters
        $query = $req["query"];
        $first = true;
        if ($query!=null){ 
        foreach ($this->signableQueryString as $key) {
        	
            if ( array_key_exists($key, $query)  ) {
            	$value = $query[$key];
                $buffer .= $first ? '?' : '&';
                $first = false;
                $buffer .= $key;
                if ($value !== "") {
                    $buffer .= "={$value}";
                }
            }
        }
        }
        return $buffer;
    }

}