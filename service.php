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
		$anuncios = [];
		$size = 20;
		$from = 0;

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

		foreach ($results as $res) {

			$title = $res['_source']['title'];
			$title = ucfirst(strtolower($title));
			$price = $res['_source']['price'];
			$desc = ucfirst(strtolower(mb_substr($res['_source']['body'], 0, 120)));
			$desc_full = ucfirst(strtolower($res['_source']['body']));
			$url = $res['_source']['url'];
			$date = $res['_source']['published_at'];
			$img = $res['_source']['image_url'];
			$name = $res['_source']['name'];
			$email = $res['_source']['email'];
			$phone = $res['_source']['phones'];
			//$this->short_url($url);

			//$title =mb_strimwidth($title, 0, 30, "...");

			$anuncios[] = [
				'title' => $title,
				'desc' => $desc,
				'desc_full' => $desc_full,
				'price' => number_format($price),
				'url' => $url,
				'date' => $this->convert_date($date),
				'short_url' => $this->short_url($url),
				'img' => $img,
				'name' => $name,
				'email' => $email,
				'phone' => $phone
			];

		}

		// Data de prueba
		// save image file
		$filePath = Utils::getTempDir() . Utils::generateRandomHash() . ".jpg";
		$imgContent = file_get_contents("https://www.logaster.com/blog/wp-content/plugins/wp-amp-ninja/images/placeholder.png");
		file_put_contents($filePath, $imgContent);

		$images[] = $filePath;

		$anuncios = [[
			'title' => "iPhone 7 como nuevo",
			'desc' => " Lorem ipsum dolor sit amet Lorem ipsum dolor sit amet Lorem ipsum dolor sit amet",
			'desc_full' => "",
			'price' => 350,
			'url' => "www.revolico.com/blablabla",
			'date' => date('d M, Y'),
			'short_url' => "www.revolico.com",
			'img' => $filePath,
			'name' => "Blabla",
			'email' => "blabla@gmail.com",
			'phone' => "53555555"
		]];

		$content = [
			"q" => $q,
			"anuncios" => $anuncios,
			"page" => 1,
			"total" => $total,
			"page_count" => $page_count
		];

		// $this->short_url

		$response->setTemplate("search.ejs", $content, $images);

	}


	public function _searchurl(Request $request, Response $response)
	{

		$anuncios = [];
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


		foreach ($results as $res) {

			$title = $res['_source']['title'];
			$title = ucfirst(strtolower($title));
			$price = $res['_source']['price'];
			$desc = ucfirst(strtolower(mb_substr($res['_source']['body'], 0, 120)));
			$desc_full = ucfirst(strtolower($res['_source']['body']));
			$url = $res['_source']['url'];
			$date = $res['_source']['published_at'];
			$img = $res['_source']['image_url'];
			$name = $res['_source']['name'];
			$email = $res['_source']['email'];
			$phone = $res['_source']['phones'];
			//$this->short_url($url);

			//$title =mb_strimwidth($title, 0, 30, "...");

			$anuncios[] = [
				'title' => $title,
				'desc' => $desc,
				'desc_full' => $desc_full,
				'price' => number_format($price),
				'url' => $url,
				'date' => $this->convert_date($date),
				'short_url' => $this->short_url($url),
				'img' => $img,
				'name' => $name,
				'email' => $email,
				'phone' => $phone
			];
		}

		// print_r(count($anuncios));

		$content = [

			"anuncios" => $anuncios,
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

		$anuncios = [];
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


		foreach ($results as $res) {

			$title = $res['_source']['title'];
			$title = ucfirst(strtolower($title));
			$price = $res['_source']['price'];
			$desc = ucfirst(strtolower(mb_substr($res['_source']['body'], 0, 120)));
			$desc_full = ucfirst(strtolower($res['_source']['body']));
			$url = $res['_source']['url'];
			$date = $res['_source']['published_at'];
			$img = $res['_source']['image_url'];
			$name = $res['_source']['name'];
			$email = $res['_source']['email'];
			$phone = $res['_source']['phones'];
			//$this->short_url($url);

			//$title =mb_strimwidth($title, 0, 30, "...");

			$anuncios[] = [
				'title' => $title,
				'desc' => $desc,
				'desc_full' => $desc_full,
				'price' => number_format($price),
				'url' => $url,
				'date' => $this->convert_date($date),
				'short_url' => $this->short_url($url),
				'img' => $img,
				'name' => $name,
				'email' => $email,
				'phone' => $phone
			];

		}

		// print_r(count($anuncios));

		$content = [
			"anuncios" => $anuncios,
			"page" => $page,
			"q" => $q,
			"total" => $total,
			"page_count" => $page_count
		];

		// $this->short_url

		$response->setTemplate("search.ejs", $content);
		//$response->setTemplate("main.ejs",$content);

	}

	private function short_url($url)
	{

		$values = parse_url($url);
		$host = "https://www." . $values['host'];
		$path_parts = explode('/', trim($values['path']));
		$path_parts = array_filter($path_parts);

		$path = $path_parts[count($path_parts) - 1];

		return $host . "/.../" . $path;

	}

	private function convert_date($date)
	{

		date_default_timezone_set('America/Havana');

		return date('d M, Y', $date);

	}

	private function search($params)
	{

		$header = array(
			"content-type: application/json"
		);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "http://67.205.179.37:9200/ads/ad/_search");
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

	public function _showdetail(Request $request, Response $response)
	{

		$title = "";
		$body = "";
		$price = 0;
		$body = "";
		$img_url = "";
		$name = "";
		$email = "";
		$phone = "";

		// print_r($request->input->data->anuncio);
		$anuncio = $request->input->data->anuncio;

		$title = html_entity_decode(ucfirst($anuncio->title));
		$price = $anuncio->price;
		//$body = htmlentities($anuncio->desc_full);
		$body = html_entity_decode(ucfirst($anuncio->desc_full));
		$date = $anuncio->date;
		$email = $anuncio->email;
		$name = $anuncio->name;
		$phone = $anuncio->phone;
		$img_url = $anuncio->img;
		//echo $img_url;
		//print_r($anuncio->img);


		$ad = [
			'title' => $title,
			'price' => $price,
			'date' => $date,
			'body' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
			'calificacion' => 4,
			'reviews' => 13,
			'img_url' => Utils::getTempDir().$img_url,
			'name' => $name,
			'email' => $email,
			'phone' => $phone,
			'q' => 'iPhone' // la busqueda previa
		];

		$content = [
			'anuncio' => $ad
		];

		$response->setTemplate("detail.ejs", $content, [Utils::getTempDir().$img_url]);
	}


}
