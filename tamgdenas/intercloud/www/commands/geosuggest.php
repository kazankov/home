<?require_once $_SERVER['DOCUMENT_ROOT'].'/modules/command.php';function lookup($string){    $string = str_replace (" ", "+", urlencode($string));   $details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$string."&sensor=false&region=ru";    $ch = curl_init();   curl_setopt($ch, CURLOPT_URL, $details_url);   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-Language: ru-ru"));   $response = json_decode(curl_exec($ch), true);    // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST   if ($response['status'] != 'OK') {    return null;   }      //print_r($response);  // die();    $out = array();   foreach($response['results'] as $iter)   {		$geometry = $iter['geometry'];	 		$out[]= array(			'bounds' => $geometry['bounds'],			'name' =>  $iter['formatted_address']		);			}    return $out;}/*** Гео-подсказка*/class Geosuggest{	/**	*	получить список объектов	*/	function get($query)	{		global $db;		return lookup($query);	}}?>