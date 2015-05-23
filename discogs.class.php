<?php
class discogsNFO {

	private $apiKey = '';
	private $releaseUrl = '?f=xml&api_key=%k';
	private $searchUrl =  'http://www.discogs.com/search?type=all&q=%s&f=xml&api_key=%k';
	private $albumArtDir = '/';

	public function __construct() {

		if (!isset($this->apikey) || $this->apikey = '') return false;
			else return true;
	}

	public function getImages ($RIN) {

		if (!isset($RIN)) throw new Exception('RIN not set.');

		$res = $this->getRelease($RIN);

		if (isset($_GET['debug'])) {
			echo '<pre>';
			var_dump($res);
			echo '</pre>';
		}

		if ($res) {

				$images = $res->release->images->image;

				if (is_array($images) && count($images) > 1) {
					$ret =  $images[0]['uri150'];
				} else $ret = $images['uri150'];

				if ($ret) {
					$retc = $this->copyAlbumArt($ret);
					if ($retc) return $retc;
						else return false;
				} else return false;

		} else return false;

	}

	private function getRelease($RIN) {

		$res = $this->getReleaseNum($RIN);

		if ($res) {

			if ($res == "150" || $res == 150) return false;

			$urlTmp = str_replace('%k', $this->apiKey, $this->releaseUrl);
			$url = 'http://www.discogs.com/release' . $res . $urlTmp;

			$res2 = $this->pingDiscogs($url);
			$xmlObject = new SimpleXMLElement($res2);

			return $xmlObject;

		} else throw new Exception('Not found.');
	}

	private function getReleaseNum ($RIN) {

		if (!isset($RIN)) throw new Exception('RIN not specified.');

		$urlTmp = str_replace('%k', $this->apiKey, $this->searchUrl);
		$url = str_replace('%s', urlencode($RIN), $urlTmp);

		$data = $this->pingDiscogs($url);

		if (!simplexml_load_string($data)) return false;

		$res =  $this->getReleaseNumFromData($data);

		$res = strrchr($res, '/');

		return $res;

	}

	private function getReleaseNumFromData ($data) {

		if (!isset($data)) throw new Exception('Did not get data from gRN method.');

		$xmlObject = new SimpleXMLElement($data);

		if ($xmlObject && isset($xmlObject->searchresults->result->uri)) {

			//var_dump($xmlObject);

			$uri = $xmlObject->searchresults->result->uri;

			if (isset($uri)) return $uri;
				else return false;

		} else throw new Exception('Didn\'t get xml object for gRN.');

	}

	private function pingDiscogs ($url) {

		if (isset($url)) {

			$curl_handle=curl_init();
			curl_setopt($curl_handle,CURLOPT_URL, $url);
			curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl_handle,CURLOPT_ENCODING, "gzip");
			$buffer = curl_exec($curl_handle);
			curl_close($curl_handle);

			// $buffer = file_get_contents($url);

			if ($buffer) return $buffer;
				else throw new Exception('Discogs API connection error.');

		} else throw new Exception('Discogs URL not set.');
	}

	private function copyAlbumArt($url) {

		if (!isset($url)) throw new Exception('No URL passed to copy.');

		$baseDir = $this->albumArtDir;
		$fileName = md5(time().$url).'.jpg';

		$dest = $baseDir.$fileName;

		$res = copy($url, $dest);

		$newUrl = $fileName;

		if ($res) return $newUrl;
			else throw new Exception ('Could not copy.');
	}

}

?>
