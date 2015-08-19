<?php namespace Divinityfound\CraigslistApi;

	class Reader {
	    const SOCKET_TIMEOUT = 10;
	    const USER_AGENT = "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 GTB5 (.NET CLR 3.5.30729)";

	    public function getSearchResults($city, $category) {
	        $contents = null;
	        $url = $url_main = "http://".$city.".craigslist.org/search/".$category."/";
	        $contents = $this->ReadPage($url);

	        $cleaned_results = $this->withdate($contents,$city);
	        
	        return $cleaned_results;
	    }

	    private function withdate($contents,$city) {
	    	$cleaned_results = array();
	    	$pattern = "<span class=\x22pl\x22\>(.+?)\<\/span>";
	        preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER);
	        foreach ($matches as $key => $match) {
	        	$explode = explode('"', $this->sanitize($match[0]));
	        	$url = str_replace('//', '', $explode[7]);
		        if (strpos($url, 'craigslist') === false) {
		            $url = $city.".craigslist.org".$url;
		        }
		        $clean_array = array(
	        			'submission' => $explode[5],
	        			'url'		 => $url,
	        			'title'		 => str_replace('/a /span', '', $explode[12])
		        	);
	        	array_push($cleaned_results, $clean_array);
	        }
	        return $cleaned_results;
	    }

	    private function sanitize($data) {
	    	$data = str_replace('<', '', $data);
	    	$data = str_replace('>', '', $data);
	    	return $data;
	    }

	    private function ReadPage($url) {
	        $pattern = "|http://(.*)/|U";
	        preg_match_all($pattern, $url, $matches1, PREG_SET_ORDER);
	        $host = $matches1[0][1];

	        $pattern = "|http://.*/(.*)$|U";
	        preg_match_all($pattern, $url, $matches2, PREG_SET_ORDER);
	        $uri = "/" . $matches2[0][1];       
	        
	        $contents = '';
	        
	        $header  = "GET " . $uri . " HTTP/1.1\r\n";
	        $header .= "Host: " . $host . "\r\n";
	        $header .= "User-Agent: " . self::USER_AGENT . "\r\n";
	        $header .= "Connection: Close\r\n\r\n";

	        $fp = @fsockopen($host, 80, $errNo, $errorMsg, self::SOCKET_TIMEOUT);
	        
	        // if failed to open socket, return false;
	        if (!$fp) {
	            throw new Exception($errorMsg, 1);
	        } else {
	            fputs($fp, $header);
	            while (($buffer = fgets($fp, 4096)) != null) {
	                $contents .= $buffer;
	            }
	            fclose($fp);
	            return $contents;
	        }
	    }

	}
?>