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


當介紹restful 服務的想法時，主要看到它包括資源和集合

GETAction() 處理集合及特定資源的請求

期待的請求可能是
http://example.com/events (event controller)
http://example.com/events/32 (resrouce)


請求只有 url 會改變，取決於你是要求控制器 event controller or 資源 

public function GETAction($request)
{
	$event = $this->readEvents();
	if(isset($request->url_elements[2] && is_numeric($request->url_elements[2]))){
		//return event 
		return $event[ $request->url_elements[2]];

	}else{
		//return event controller
		return $event;
	}
}



/*************************************/

要在 restful 服務中建立 post request 發送資料欄來填入新的紀錄

$item = array(
	'title' => 'silent auction',
	'date' => date('U', mktime(0, 0, 0, 4, 17, 2015)),
	'capacity' => 210
);

$data = json_encode($item);
$ch = curl_init('http://localhost/rest/events'); //rest dir / events controller
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1); // 設定以POST 的動作實行發送 request
curl_setopt($ch, CURLOPT_POSTFIELD, $data); // 將資料做 json_encode 動作再以 post 作為方式送出request 到達 localhost/rest/events
$response = curl_exec($ch);
var_dump(json_decode($response, true));
$infos = curl_getinfo($ch);var_dump($infos);


// post request handle
請求跑到了集合, 而服務本身會分配一個 識別碼id, 並且返回相關的資訊

將使用者重新導向redirect 到新的資源位置相當普遍

public function POSTAction($request)
{
	//get the event data
	$events = $this->readEvents();

	//get the datas from the reguest event
	$event = array();
	$event = array(
		'title' => $request->parameters['title'],
		'date' => $request->parameters['date'],
		'capacity' => $request->parameters['capacity']
	);

	
	$events[] = $event;

	//write the event
	$this->writeEvents($events); 

	//setting resource id
	$id = max(array_keys($events));
	header('HTTP/1.1 201 created');
	return '';

}


請求資料以 json 格式來到服務，並解析他
但在解析及output header infos 時候要做 sanitation 過濾及消毒的動作
如此才能夠免除 攻籍資料





put 的方式:
第一: 透過抓取特定的事件 (event id = 4)
再透過修改特定的欄位 
然後再以 put 的方式作為發送給localhost / server 去更新資源的 URL

ex:
$ch = curl_init('http://localhost/rest/events/4');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//send out the msg
$response = curl_exec($ch);
$item = json_decode($response, true);

//dml vs dcl
$item['title'] = 'inproved event';


//re-send the msg to the localhost
//put the data into the json format
$data = json_encode($item);

//create the curl obj
$ch = curl_init('http://localhost/rest/events/4');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELD, $data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // setting the custom request method => put
$res = curl_exec($ch);



// server side sample
public function PUTAction($request)
{
	//get the event
	$events = $this->readEvents();

	$event = array();
	$event = array(
			'title' => $request->parameters['title'],
			'date' => $request->parameters['date'],
			'capacity' => $request->parameters['capacity']
		);

	//setting the id
	$id = max(array_keys($events));

	//put the event id into list
	$events[$id] = $event;

	//write into the event
	$this->writeEvents($events);

	//send headers
	header('HTTP/1.1 201, create');
	header('Location: /event/' . $id );
	return '';

}







$url = 'http://api.flickr.com/services/';
$ch = curl_init($url);
//setting the curl opt
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELD, $xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//SEND THE MSG
$response = curl_exec($ch);
$infos = curl_getinfo($ch);

//save as a xml
$responsexml = new SimpleMLElement($response);
$photosxml = new SimpleXMLElement(
(string)$responsexml->params->param->value->string
);

print_r($photosxml);




?>


