<?php namespace Divinityfound\CraigslistApi;

	class Reader {
	    const SOCKET_TIMEOUT = 10;
	    const USER_AGENT = "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 GTB5 (.NET CLR 3.5.30729)";

	    public function getSearchResults($city, $category) {
	        $contents = null;
	        $url = $url_main = "http://".$city.".craigslist.org/search/".$category."/";
	        $contents = $this->ReadPage($url);
	        $contents = explode('<div class="rows">', $contents)[1];
	        $contents = explode('close fullscreen', $contents)[0];


	        $cleaned_results = $this->withdate($contents,$city);
	        
	        return $cleaned_results;
	    }

	    private function withdate($contents,$city) {
	    	$cleaned_results = array();
	    	
	    	$rows = explode('<p class="row" data-pid="', $contents);

	    	foreach ($rows as $key => $row) {
	    		if (strpos($row, '<') === false) {
	    			continue;
	    		}
	    		$cleanRows = array_unique(array_filter($this->sanitize(explode('"',$row))));

	    		if (isset($cleanRows[25])) {
		    		$submission = $cleanRows[18];
		    		$url = $city.'.craigslist.org'.$cleanRows[2];
	    			$title = $cleanRows[25];
	    		} else if (isset($cleanRows[27])) {
	    			if (!isset($cleanRows[20])) {
	    				continue;
	    			}
	    			$submission = $cleanRows[20];
		    		$url = $city.'.craigslist.org'.$cleanRows[4];
	    			$title = $cleanRows[27];
	    		} else {
	    			$submission = $cleanRows[20];
		    		$url = $city.'.craigslist.org'.$cleanRows[4];
	    			$title = $cleanRows[29];
	    		}

	    		$clean_array = array(
	        			'submission' => $submission,
	        			'url'		 => $url,
	        			'title'		 => $title
		        	);
	        	array_push($cleaned_results, $clean_array);
	    	}
	        return $cleaned_results;
	    }

	    private function sanitize($data) {
	    	$data = str_replace('<', '', $data);
	    	$data = str_replace('>', '', $data);
	    	$data = str_replace('//', '', $data);
	    	$data = str_replace('a href=', '', $data);
	    	$data = str_replace('/a', '', $data);
	    	$data = str_replace('/span', '', $data);
	    	$data = str_replace('span class=', '', $data);
	    	$data = str_replace('  ', '', $data);

	    	foreach($data as $key => $one) {
			    if(strpos($one, '=') !== false)
			        unset($data[$key]);
			}
	    	
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