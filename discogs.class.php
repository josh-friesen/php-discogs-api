<?php
/**
* discogs.com API Class
* Query the discogs API for information
*
* @author	Josh F.
*/
class discogsNFO {

	private $apiKey = '';
	private $releaseUrl = '?f=xml&api_key=%k';
	private $searchUrl =  'http://www.discogs.com/search?type=all&q=%s&f=xml&api_key=%k';

  /**
	* class construct
	* Establishes API key or returns false
	*
	* @return boolean
	*/
	public function __construct() {

		if (!isset($this->apikey) || $this->apikey = '') return false;
			else return true;
	}

	/**
	* getImages
	* Retrieves image information from discogs release object
	*
	* @return object
	*/
	public function getImages ($RIN) {

		if (!isset($RIN)) throw new Exception('RIN not set.');

		$res = $this->getRelease($RIN);

		if ($res) {

				$images = $res->release->images->image;

				if (is_array($images) && count($images) > 1) {
					$ret =  $images[0]['uri150'];
				} else $ret = $images['uri150'];

				if ($ret) {
						return $retc;
					} else return false;
		} else return false;

	}

	/**
	* getRelease
	* Retrieves album release information
	*
	* @return object
	*/
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

	/**
	* getReleaseNum
	* Retrieves release number from provided release identifier
	*
	* @return string
	*/
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

	/**
	* getReleaseNumFromData
	* Extracts release number from release object
	*
	* @return string
	*/
	private function getReleaseNumFromData ($data) {

		if (!isset($data)) throw new Exception('Did not get data from gRN method.');

		$xmlObject = new SimpleXMLElement($data);

		if ($xmlObject && isset($xmlObject->searchresults->result->uri)) {

			$uri = $xmlObject->searchresults->result->uri;

			if (isset($uri)) return $uri;
				else return false;

		} else throw new Exception('Didn\'t get xml object for gRN.');

	}

	/**
	* pingDiscogs
	* Makes connection to discogs.com API using cURL
	*
	* @return buffer
	*/
	private function pingDiscogs ($url) {

		if (isset($url)) {

			$curl_handle=curl_init();
			curl_setopt($curl_handle,CURLOPT_URL, $url);
			curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl_handle,CURLOPT_ENCODING, "gzip");
			$buffer = curl_exec($curl_handle);
			curl_close($curl_handle);

			if ($buffer) return $buffer;
				else throw new Exception('Discogs API connection error.');

		} else throw new Exception('Discogs URL not set.');
	}

}

?>
