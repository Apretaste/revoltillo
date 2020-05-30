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
				'publishedDate' => $item->created_at,
				'hasImages' => !empty($item->image_urls)
			];
		}

		if (empty($ads)) {
			$response->setTemplate("message.ejs", [
				'header' => 'Lo sentimos', 'icon' => 'error_outline',
				'text' => 'No tenemos resultados para esta busqueda',
				'button' => ['caption' => 'Atras', 'href' => 'REVOLTILLO']
			]);

			return;
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

		if (!isset($data->_source)) {
			$response->setTemplate("message.ejs", [
				'header' => 'Lo sentimos', 'icon' => 'error_outline',
				'text' => 'El articulo que busca no pudo ser encontrado',
				'button' => ['caption' => 'Atras', 'href' => 'REVOLTILLO BUSCAR'],
				'query' => $q
			]);

			return;
		}

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
			'description' => $this->cleanString($result->description ?? ''),
			'price' => number_format($result->price),
			'publishDate' => $result->created_at,
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
}
