<?php
/** @noinspection PhpComposerExtensionStubsInspection */

use Apretaste\Request;
use Apretaste\Response;
use Framework\Alert;
use Framework\Config;
use Framework\Revoltillo;

class Service
{
	/**
	 * Displays box to start a search
	 *
	 * @param Request $request
	 * @param Response $response
	 * @throws Alert
	 * @author Daniel
	 */
	public function _main(Request $request, Response $response)
	{
		$response->setCache("year");
		$response->setTemplate("index.ejs");
	}

	/**
	 * Performs a search
	 *
	 * @param Request $request
	 * @param Response $response
	 * @throws Alert
	 * @author Daniel
	 */
	public function _search(Request $request, Response $response)
	{
		// get the query to run
		$query = $request->input->data->q;

		$isCategory = in_array($query, ['casas', 'autos', 'electronica', 'servicios', 'ventas', 'empleos']);

		// get data from the backend
		$results = $isCategory ? Revoltillo::searchByCategory($query) : Revoltillo::searchByKeyword($query);

		// clean the data to send to the view
		$ads = [];
		usort($results, fn($a, $b) => strcmp($b->_source->created_at, $a->_source->created_at));

		foreach ($results as $res) {
			$item = $res->_source;
			$item->price = str_replace(',', '.', $item->price);
			$price = $item->price * 1;

			$ads[] = [
				'id' => $item->external_id,
				'title' => $this->cleanString($item->title),
				'shortDesc' => mb_substr($this->cleanString($item->description ?? ""), 0, 120),
				'price' => number_format($price, 2),
				'site' => $this->getSiteName($item->url),
				'publishedDate' => $this->convertDate($item->created_at),
				'hasImages' => !empty($item->image_urls)
			];
		}

		// create content for the view
		$content = [
			"q" => $query,
			"results" => $ads
		];

		// send data to the view
		$response->setCache("day");
		$response->setTemplate("search.ejs", $content);
	}

	/**
	 * Get the details for a classified
	 *
	 * @param Request $request
	 * @param Response $response
	 * @throws Alert
	 * @author Daniel
	 */
	public function _details(Request $request, Response $response)
	{
		// get the ID of the classified
		$id = $request->input->data->id;
		$q = $request->input->data->q;

		// search the info based on ID
		$data = Revoltillo::searchById($id);
		$result = $data->_source;

		// save the first image locally for the view
		$images = [];
		if (!empty($result->image_urls[0])) {
			// save image file if not in the cache
			$img = TEMP_PATH . md5($result->image_urls[0]) . ".jpg";
			if (!file_exists($img)) {
				file_put_contents($img, file_get_contents($result->image_urls[0]));
			}
			$images[] = $img;
		}

		// prepare info for the view
		$ad = [
			'title' => $this->cleanString($result->title),
			'description' => $this->cleanString($result->description),
			'price' => number_format($result->price),
			'publishDate' => $this->convertDate($result->created_at),
			'image' => empty($images) ? "" : basename($images[0]),
			'name' => $result->advertiser_name ?? false,
			'email' => $result->advertiser_emails ?? [],
			'phone' => $result->advertiser_phones ?? []
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
	 * @param String $str
	 * @return String
	 * @author @salvipascual
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
	 * @param String $date
	 * @return String
	 * @author Daniel
	 */
	private function convertDate($date)
	{
		date_default_timezone_set('America/Havana');
		$date = strtotime($date);
		return date('d M, Y', $date);
	}

	/**
	 * Get a classified based on the ID
	 *
	 * @param $id
	 * @return array
	 * @throws Alert
	 * @author Daniel
	 */
	private function getAdDetailById($id)
	{
		$params = '{"query":{"match":{"external_id":"' . $id . '"}}}';
		return $this->search($params);
	}

	/**
	 * Get the name of the site based on a URL
	 *
	 * @param String $url
	 * @return String
	 * @author Daniel
	 */
	private function getSiteName($url)
	{
		$parse = parse_url($url);
		return $parse['host'];
	}

	/**
	 * Perform a search in Elastic Search
	 *
	 * @param string $params
	 * @return array
	 * @author Daniel
	 */
	private function search($params)
	{
		// get content from cache
		$cache = TEMP_PATH . "cache/revoltillo_" . md5($params) . date("Ym") . ".cache";
		if (file_exists($cache)) return unserialize(file_get_contents($cache));

		$key = Config::pick('revoltillo')['api_key'];
		$key = base64_decode($key);

		// send request via CURL
		$curl = curl_init();

		curl_setopt_array($curl, array(
			// Request URL containing app, type and id or method
			CURLOPT_URL => "https://arc-cluster-revoltillo-cluster-bpexkh.searchbase.io/{{app}}/{{type}}/{{id/method}}",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			// Request HTTP method ( GET | POST | PUT | DELETE )
			CURLOPT_CUSTOMREQUEST => "POST",
			// Request body/payload
			CURLOPT_POSTFIELDS => $params,
			CURLOPT_HTTPHEADER => array(
				// App credentials base64 encoded
				"Authorization: Basic $key",
				"Content-Type: application/json"
			),
		));

		$response = curl_exec($curl);
		die(var_dump($response));


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
