<?php

class Service
{
	/**
	 * Display the list of musics
	 *
	 * @param Request
	 * @param Response
	 * @author salvipascual
	 */
	public function _main(Request $request, Response $response)
	{
		$path = Utils::getPathToService('revoltillo');
		$response->setTemplate("index.ejs", [], ["$path/images/logo.png"]);

	}

	public function _search(Request $request, Response $response)
	{
		$size = 20;
		$from = 0;
		$ads = array();

		$q = $request->input->data->q;


		//$params = '{"size":"'.$size.'","from":"'.$from.'","sort":{"created_at":{"order":"desc"}},"query":{"term":{"category":"' .$q. '"}}}';

		$params = '{"size":"' . $size . '","from":"' . $from . '","query":{"match":{"title_keywords":"' . $q . '"}}}';


		$data = $this->search($params);


		$results = $data['results'];
		$total = $data['total'];        


		$page_count = floor($total / $size);

		if ($page_count > 10) {

			$page_count = 10;
		}

		foreach($results as $res){

			$id = $res['_source']['external_id'];
			$publish_date = $res['_source']['publish_date'];
			$title = html_entity_decode(ucfirst(strtolower($res['_source']['title'])));
			$description = $res['_source']['description'];
			$price = $res['_source']['price'];
			$url = $res['_source']['url'];
			$image_url = $res['_source']['image_urls'];

			$ads[] = [
				'id'=>$id,
				'title' => $title,
				'short_description' => $this->getShortDesc($description),			
				'price' => number_format($price),
				'url' => $url,
				'site'=>$this->getSiteName($url),
				'publish_date' =>$this->convertDate($publish_date),				
				'image_urls' => $image_url
							
			];
		}	
		
		

		$content = [
			"q" => $q,
			"results" => $ads,
			"page" => 1,
			"total" => $total,
			"page_count" => $page_count
		];

		// $this->short_url

		$response->setTemplate("search.ejs", $content);

	}


	public function _searchurl(Request $request, Response $response)
	{

		$title = "";
		$image_url = "";
		$to = 0;
		$size = 20;
		//$pages_count = 10;
		$q = $request->input->data->q;
		$page = $request->input->data->page;

		$to = $page * 20;

		if ($to > 20) {
			$from = $to - 20;
		} else {
			$from = 0;
		}

		// $params = '{"query": {"match": {"title_keywords":"'.$q.'"}},"sort":{"created_at":{"order":"asc"}}}';
		$params = '{"size":"' . $size . '","from":"' . $from . '","query":{"match":{"title_keywords":"' . $q . '"}}}';

		$data = $this->search($params);

		$results = $data['results'];
		$total = $data['total'];

		$page_count = floor($total / $size);

		if ($page_count > 10) {

			$page_count = 10;
		}
		

		$content = [

			"results" => $results,
			"page" => $page,
			"q" => $q,
			"total" => $total,
			"page_count" => $page_count
		];

		// $this->short_url

		$response->setTemplate("search.ejs", $content);
		//$response->setTemplate("main.ejs",$content);

	}

	public function _searchCategory(Request $request, Response $response)
	{

		$title = "";
		$image_url = "";
		$to = 0;
		$size = 20;
		//$pages_count = 10;
		$q = $request->input->data->q;
		$page = $request->input->data->page;

		$to = $page * 20;

		if ($to > 20) {
			$from = $to - 20;
		} else {
			$from = 0;
		}

		// $params = '{"query": {"match": {"title_keywords":"'.$q.'"}},"sort":{"created_at":{"order":"asc"}}}';

		$params = '{"size":"' . $size . '","from":"' . $from . '","query":{"term":{"category":"' . $q . '"}},"sort":{"published_at":{"order":"desc"}}}';

		$data = $this->search($params);

		$results = $data['results'];
		$total = $data['total'];

		$page_count = floor($total / $size);

		if ($page_count > 10) {

			$page_count = 10;
		}
		

		// print_r(count($anuncios));

		$content = [
			"ads" => $results,
			"page" => $page,
			"q" => $q,
			"total" => $total,
			"page_count" => $page_count
		];

		// $this->short_url

		$response->setTemplate("search.ejs", $content);
		//$response->setTemplate("main.ejs",$content);

	}

	public function _showdetail(Request $request, Response $response){

		$ad = array();

		$id = $request->input->data->id;

        $data = $this->getAdDetailById($id);

        $result = $data['results'][0]['_source'];

        $ad = [
            'title'=>html_entity_decode(ucfirst(strtolower($result['title']))),
            'description'=> html_entity_decode(ucfirst(strtolower($result['description']))),
            'price'=>number_format($result['price']),
            'publish_date' =>$this->convertDate($result['publish_date']),				
			'image_urls' => $result['image_urls'],
			'name'=>$result['advertiser_name'],
			'email'=>$result['advertiser_emails'],
			'phone'=>$result['advertiser_phones']

        ];



		$content = [
               'ad'=>$ad
		];

		$response->setTemplate("detail.ejs", $content);
	}

	private function getAdDetailById($id){
      
      $params = '{"query":{"term":{"external_id":"' . $id . '"}}}';

	  $data = $this->search($params);

	  return $data;

	}

	private function getSiteName($url)
	{

		$parse = parse_url($url);		

		return $parse['host'];

	}

	private function convertDate($date)
	{

		date_default_timezone_set('America/Havana');

		return date('d M, Y', $date);

	}

	private function getShortDesc($description){

		return html_entity_decode(ucfirst(strtolower(mb_substr($description, 0, 120))));
	}

	private function search($params)
	{

		$header = array(
			"content-type: application/json"
		);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "http://167.99.147.172:9200/ads/_search");
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		$result = curl_exec($curl);
		curl_close($curl);

		$results = json_decode($result, true);
		$total = $results['hits']['total'];
		$results = $results['hits']['hits'];

		$data = [
			'results' => $results,
			'total' => $total

		];

		return $data;

	}

	

}
