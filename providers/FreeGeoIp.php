<?php 
    namespace jmurrell\DynMailto;
    
    /**
    *
    * Basic PHP wrapper for the FreeGeoIp Geolocation API <https://freegeoip.net/>
    *
    * @author Daan De Smedt <daan.desmedt@sdp.be>
    * @date 29/03/2017
    *
    */
    class FreeGeoIp {
        
        /**
        * @const string API url
        */
        const API_BASE_URL = 'http://freegeoip.app';
        
        /**
        * @var string responseFormat
        * API call request format - csv, xml, json
        */
        private $responseFormat;
    
        
        
        /**
        * Constructor
        *
        * @param string $responseFormat (csv, xml, json)
        */
        public function __construct($responseFormat){
            $this->responseFormat = $responseFormat;
        }
        
        
        /**
        * Fetch GeoLocation data for IP
        *
        * @param string $ip - IP to retrieve GeoLocation data
        *
        * @return string/bool - data
        */
        public function fetch($ip){
            
            // check valid IP
            if(!filter_var($ip, FILTER_VALIDATE_IP)){
                throw new Exception('Invalid IP');
            }
            
            // build API url
            $url = $this::API_BASE_URL . '/' . $this->responseFormat . '/' . $ip;
            var_dump($url);
            
            // CURL handler
            $curl = curl_init();
            // Options
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            // fetch
            $response = curl_exec($curl);
            // response
            $errorCode = curl_errno($curl);
            $errorMessage = curl_error($curl);
            $HTTPCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            // close curl handler
            curl_close($curl);
            
            // check response
            if ($HTTPCode == '200'){
                if ($errorCode == 0){
                    return $response;
                }else{
                    throw new \Exception('CURL ERROR <' . $errorMessage. '> for url <' . $url . '>');
                    return false;
                }
            }else{
                throw new \Exception('HTTP RESPONSE <' . $HTTPCode. '> for url <' . $url . '>');
                return false;
            }
            
        }       
        
    }
    
?>
