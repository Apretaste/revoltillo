<?php

class Service
{
	/**
	 * Displays box to start a search
	 *
	 * @author Daniel
	 * @param Request
	 * @param Response
	 */
	public function _main(Request $request, Response $response)
	{
		$response->setCache("year");
		$response->setTemplate("index.ejs", []);
	}

	/**
	 * Performs a search
	 *
	 * @author Daniel
	 * @param Request
	 * @param Response
	 */
	public function _search(Request $request, Response $response)
	{
		// get the query to run
		$q = $request->input->data->q;

		// get data from tha backend
		$query = '{"size":"20","from":"0","query":{"match":{"title_keywords":"'.$q.'"}}}';
		$results = $this->search($query);

		// clean the data to send to the view
		$ads = [];
		foreach($results as $res) {
			$item = $res['_source'];
			$ads[] = [
				'id' => $item['external_id'],
				'title' => $this->cleanString($item['title']),
				'shortDesc' => mb_substr($this->cleanString($item['description']), 0, 120),
				'price' => number_format($item['price']),
				'site' => $this->getSiteName($item['url']),
				'publishedDate' =>$this->convertDate($item['publish_date']),
				'hasImages' => !empty($item['image_urls'])
			];
		}

		// create content for the view
		$content = [
			"q" => $q,
			"results" => $ads
		];

		// send data to the view
		$response->setCache("day");
		$response->setTemplate("search.ejs", $content);
	}

	/**
	 * Get the details for a classied
	 *
	 * @author Daniel
	 * @param Request
	 * @param Response
	 */
	public function _details(Request $request, Response $response)
	{
		// get the ID of the classified
		$id = $request->input->data->id;
		$q = $request->input->data->q;

		// search the info based on ID
		$data = $this->getAdDetailById($id);
		$result = $data[0]['_source'];

		// save the first image locally for the view
		$images = [];
		if(!empty($result['image_urls'][0])) {
			// save image file if not in the cache
			$img = Utils::getTempDir() . md5($result['image_urls'][0]) . ".jpg";
			if(!file_exists($img)) {
				file_put_contents($img, file_get_contents($result['image_urls'][0]));
			}
			$images[] = $img;
		}

		// prepare info for the view
		$ad = [
			'title' => $this->cleanString($result['title']),
			'description' => $this->cleanString($result['description']),
			'price' => number_format($result['price']),
			'publishDate' => $this->convertDate($result['publish_date']),
			'image' => empty($images) ? "" : basename($images[0]),
			'name' => $result['advertiser_name'],
			'email' => $result['advertiser_emails'],
			'phone' => $result['advertiser_phones']
		];

		// create content for the view
		$content = [
			"q" => $q,
			"ad" => $ad
		];

		// send data to the view
		$response->setCache("year");
		$response->setTemplate("detail.ejs", $content, $images);
	}

	/**
	 * Cleans a string so that is can be converted to JSON
	 *
	 * @author @salvipascual
	 * @param String $str
	 * @return String
	 */
	private function cleanString($str)
	{
		return mb_convert_encoding(
			html_entity_decode(
				ucfirst(
					strtolower($str)
				)
			), 'UTF-8', 'UTF-8'
		);
	}

	/**
	 * Formats a date to be sent back
	 *
	 * @author Daniel
	 * @param String $date
	 * @return String
	 */
	private function convertDate($date)
	{
		date_default_timezone_set('America/Havana');
		return date('d M, Y', $date);
	}

	/**
	 * Get a classified based on the ID
	 *
	 * @author Daniel
	 * @param Int $str
	 * @return Array
	 */
	private function getAdDetailById($id)
	{
		$params = '{"query":{"match":{"external_id":"'.$id.'"}}}';
		return $this->search($params);
	}

	/**
	 * Get the name of the site based on a URL
	 *
	 * @author Daniel
	 * @param String $url
	 * @return String
	 */
	private function getSiteName($url)
	{
		$parse = parse_url($url);
		return $parse['host'];
	}

	/**
	 * Perform a search in Elastic Search
	 *
	 * @author Daniel
	 * @param JSON $params
	 * @return Array
	 */
	private function search($params)
	{
		// get content from cache
		$cache = Utils::getTempDir() . "cache/revoltillo_" . md5($params) . date("Ym") . ".cache";
		if(file_exists($cache)) return unserialize(file_get_contents($cache));

		// send request via CURL
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "http://167.99.147.172:9200/ads/_search");
		curl_setopt($curl, CURLOPT_HTTPHEADER, ["content-type: application/json"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		$result = curl_exec($curl);
		curl_close($curl);

		// get results
		$results = json_decode($result, true);
		$content = $results['hits']['hits'];

		// create the cache and return
		file_put_contents($cache, serialize($content));
		return $content;
	}
}
