<?php

$API_KEY = '1yIdvlge_UX7RUVaj9TmDD7A6il8vCp3IDjvb1G6DEezW2vDBTY6zp9YFD2GwmCj85VCkP0B0ouxgqqqToX00opUlasv2RdrhvkIdoNEG3sbeKeHMLSwSkQ6hRSMXnYx';
$API_HOST = "https://api.yelp.com";
$SEARCH_PATH = "/v3/businesses/search";
$BUSINESS_PATH = "/v3/businesses/";  // Business ID will come after slash.
$SEARCH_LIMIT = 10;
require_once 'models/simple_model.php';
class map extends Controller {
    public function __construct() {
        parent::__construct();
        $this->tpl_dir = "views/map/";
        $this->Model = new Simple('favorites');
        session_start();
    }

    function main(){
        $this->map();
    }
    
    public function map(){
        if(!isset($_SESSION['user_id'])) {
            header("Location:/login.php");
        }
        $this->tpl = $this->tpl_dir.'map.php';
    }
    public function map2(){
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(array('success' => false, 'code' => 'logout'));
            exit;
        }
        $this->data['stores'] = $this->search($_GET['zipcode']);
        echo json_encode(array('success' => true, 'msg' => $this->load_view($this->tpl_dir.'map2.php', $this->data)));
    }
    
    public function save(){
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(array('success' => false, 'code' => 'logout'));
            exit;
        }
        $checkBus = $this->Model->findbyCon(array('user_id' => $_SESSION['user_id'], 'business_id' =>$_REQUEST['busid']));
        if (count($checkBus) == 0){
            $toSave['user_id'] = $_SESSION['user_id'];
            $toSave['business_id'] = $_REQUEST['busid'];
            $toSave['business_info'] = json_encode($_SESSION['businesses'][$_REQUEST['busid']]);
            $this->Model->save($toSave);
        }
        echo json_encode(array('success' => true));
    }
    
    public function getfav(){
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(array('success' => false, 'code' => 'logout'));
            exit;
        }
        $checkBus = $this->Model->findbyCon(array('user_id' => $_SESSION['user_id']));
        $list = '<br>';
        foreach ($checkBus as $b) {
            $info = $this->format_business(json_decode($b['business_info'], TRUE), FALSE);
            $p = $info['properties'];
            
            $list .= '<p><b>' . $p['name'] .'</b>' .
                '<br/>' . $p['address'] .'<br/>' .
                $p['phone'] .'<br/>' .
                $p['hours'] .'</p>';
        }
        echo json_encode(array('success' => true, 'msg' => $list));
    }
    /** 
     * Makes a request to the Yelp API and returns the response
     * 
     * @param    $host    The domain host of the API 
     * @param    $path    The path of the API after the domain.
     * @param    $url_params    Array of query-string parameters.
     * @return   The JSON response from the request      
     */
    private function request($host, $path, $url_params = array()) {
        // Send Yelp API Call
        try {
            $curl = curl_init();
            if (FALSE === $curl)
                throw new Exception('Failed to initialize');

            $url = $host . $path . "?" . http_build_query($url_params);
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,  // Capture response.
                CURLOPT_ENCODING => "",  // Accept gzip/deflate/whatever.
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "authorization: Bearer " . $GLOBALS['API_KEY'],
                    "cache-control: no-cache",
                ),
            ));

            $response = curl_exec($curl);

            if (FALSE === $response)
                throw new Exception(curl_error($curl), curl_errno($curl));
            $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (200 != $http_status)
                throw new Exception($response, $http_status);

            curl_close($curl);
        } catch(Exception $e) {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);
        }

        return $response;
    }

    /**
     * Query the Search API by a search term and location 
     * 
     * @param    $location    The search location passed to the API 
     * @return   The array response from the request 
     */
    private function search($location) {
        $term = 'restaurant';
        $url_params = array();

        $url_params['term'] = $term;
        $url_params['location'] = $location;
        $url_params['limit'] = $GLOBALS['SEARCH_LIMIT'];
        
        $url_params['radius'] = 8000;

        $businesses = json_decode($this->request($GLOBALS['API_HOST'], $GLOBALS['SEARCH_PATH'], $url_params), true);
        $ouput= array();
        if (isset($businesses['businesses'])) {
            foreach($businesses['businesses'] as $b) {
                $newB = $this->format_business($b);
                if($newB !== false) {
                    $ouput[] = $newB;
                }
            }
        }
        return $ouput;
    }
    
    private function format_business($b, $checkOpen = true) {
        $detail = isset($b['detail'])? $b['detail'] : json_decode($this->get_business($b['id']), true);
        $b['detail'] = $detail;
        $_SESSION['businesses'][$b['id']] = $b;
        if(!$detail['hours'][0]['is_open_now'] && $checkOpen){
            return false;
        }
        $newB = array();
        $newB['id'] = $b['id'];
        $newB['name'] = $b['name'];
        $newB['address'] = implode("\t\n", $b['location']['display_address']);
        $newB['phone'] = $b['display_phone'];

        $newB['hours'] = '';
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        foreach($detail['hours'][0]['open'] as $h){
            $time = ' ' . date('h:i A', strtotime(substr($h['start'], 0, 2) . ':' . substr($h['start'], -2))) . ' - ' 
                    . date('h:i A', strtotime(substr($h['end'], 0, 2) . ':' . substr($h['end'], -2)));
            if(isset($hours[$h['day']])) {
                $hours[$h['day']] = $hours[$h['day']];
            }else{
                $hours[$h['day']] .= $time;
            }
        }
        
        $sameTime = '';
        $sameDays = array();
        for($i= 0; $i < 7; $i++) {
            if (!isset($hours[$i])){
                if(count($sameDays) == 1) {
                    $newB['hours'] .= $days[$sameDays[0]] . ' ' . $sameTime . '<br/>';
                }elseif(count($sameDays) > 1){
                    $last = count($sameDays) - 1;
                    $newB['hours'] .= $days[$sameDays[0]] . ' - '. $days[$sameDays[$last]].' ' . $sameTime . '<br/>';
                }
                continue;
            }
            if(empty($sameTime)) {
                $sameTime = $hours[$i];
                $sameDays[] = $i;
            }else{
                if($sameTime == $hours[$i]) {
                    $sameDays[] = $i;
                }else{
                    if(count($sameDays) == 1) {
                        $newB['hours'] .= $days[$sameDays[0]] . ' ' . $sameTime . '<br/>';
                    }elseif(count($sameDays) > 1){
                        //print_r($sameDays);
                        $last = count($sameDays) - 1;
                        $newB['hours'] .= $days[$sameDays[0]] . ' - '. $days[$sameDays[$last]] .' ' . $sameTime . '<br/>';
                    }
                    $sameTime = $hours[$i];
                    $sameDays = array();
                    $sameDays[] = $i;
                }
            }
            if ($i ==6) {
                if(count($sameDays) == 1) {
                    $newB['hours'] .= $days[$sameDays[0]] . ' ' . $sameTime . '<br/>';
                }elseif(count($sameDays) > 1){
                    $last = count($sameDays) - 1;
                    $newB['hours'] .= $days[$sameDays[0]] . ' - '. $days[$sameDays[$last]] .' ' . $sameTime . '<br/>';
                }
            }
        }
        /*
        foreach($hours as $day => $hour) {
            $newB['hours'] .= $days[$day] . $hour . '<br/>';
        }
        */

        $newB['marker-color'] ='#AA0000';
        $newB['marker-size'] = 'large';
        $newB['marker-symbol'] = 'restaurant';

        $newB2['type'] = 'Feature';
        $newB2['properties'] = $newB;
        $newB2['geometry'] = array('type' => 'Point', 'coordinates' => array($b['coordinates']['longitude'], $b['coordinates']['latitude']));
        return $newB2;
    }
    /**
     * Query the Business API by business_id
     * 
     * @param    $business_id    The ID of the business to query
     * @return   The JSON response from the request 
     */
    private function get_business($business_id) {
        $business_path = $GLOBALS['BUSINESS_PATH'] . urlencode($business_id);

        return $this->request($GLOBALS['API_HOST'], $business_path);
    }
    
}