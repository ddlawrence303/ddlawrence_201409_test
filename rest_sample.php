<?

//seting new rest request

$request = new Request();
$request->url_elements = array();
if(isset($_SERVER['PATH_INFO'])){
	$request->url_elements = explode('/', $_SERVER['PATH_INFO']);
}

//find the verb 
$request->verb = $_SERVER['REQUEST_METHOD'];
swtich($request->verb){
	case 'GET':
		$request->parameters = $_GET;
	break;

	case 'POST':
	case 'PUT':
		//get the datas from the php://input
	/*
	post put 處理的是 json 資料的主體而不是表單內容
	所以我門直接使用php://input 
	就如同稍早在使用串流來發出web request 一樣
	get the response from request 
	and exec json_decode 解析成 key - value 
	like $POST $GET
	*/

		$request->parameters = json_decode(file_get_contents('php://input'), true);
	break;

	case 'DELETE':
	default:
		$request->parameters = array();
}




// implement the curl initialize
$ch = curl_init('http://localhost/rest/events');
curl_setopt(CURLOPT_RETERUNTRANSFER, 1);
$response = curl_exec($ch);
$events = json_decode($response, true);






// 路徑導向請求

if($request->url_elements){
	//get controller name
	$controller_name = ucfirst($request->url_elements) . 'Controller';
	//check the controller is exists or not
	if(class_exists($controller_name)){
		$controller = new $controller_name();
		$controller_name = ....

	}else{
		//not fuound this event controller
		header('HTTP/1.0 400 bad request');
		$response = 'unkown request for' . $request->url_elements[1];
	}

}else{
	//output error
	header('HTTP/1.0 400 Bad Request');
	$response = 'Unknown request';exit;
}


?>
